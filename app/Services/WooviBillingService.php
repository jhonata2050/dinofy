<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Invoice;
use App\Models\Tenant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WooviBillingService implements BillingGatewayInterface
{
    private const URL_PRODUCTION = 'https://api.openpix.com.br';
    private const URL_SANDBOX = 'https://api.woovi-sandbox.com';

    private string $baseUrl;
    private string $appId;
    private bool $sandbox;

    public function __construct()
    {
        $this->sandbox = (bool) (\App\Models\Setting::get('woovi_sandbox', '0'));
        $this->baseUrl = $this->sandbox ? self::URL_SANDBOX : self::URL_PRODUCTION;
        $this->appId = \App\Models\Setting::get('woovi_app_id') ?: (config('master.woovi.app_id') ?? '');
    }

    public function generateMonthlyInvoice(Tenant $tenant): Invoice
    {
        $plan = $tenant->plan;
        $now = now();

        $invoice = Invoice::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'amount_cents' => $plan->price_cents,
            'period_start' => $now->copy()->startOfMonth(),
            'period_end' => $now->copy()->endOfMonth(),
            'due_date' => $tenant->next_billing_date,
            'status' => 'pending',
            'idempotency_key' => Str::uuid()->toString(),
        ]);

        $invoice->items()->create([
            'description' => $plan->name . ' - Mensal',
            'quantity' => 1,
            'unit_price_cents' => $plan->price_cents,
            'total_cents' => $plan->price_cents,
        ]);

        $this->createPixCharge($invoice, $tenant);

        $tenant->update(['next_billing_date' => $now->copy()->addMonth()]);

        return $invoice;
    }

    public function createPixCharge(Invoice $invoice, Tenant $tenant): void
    {
        if (empty($this->appId)) {
            throw new \RuntimeException('Gateway Woovi não configurado. Cadastre o AppID em Configurações > Gateway.');
        }

        $correlationID = $invoice->idempotency_key;

        $payload = [
            'correlationID' => $correlationID,
            'value' => $invoice->amount_cents,
            'comment' => "Dinofy {$tenant->plan->name} - {$tenant->subdomain}",
        ];

        $customer = [
            'name' => $tenant->name,
            'email' => $tenant->email,
            'phone' => preg_replace('/\D/', '', $tenant->phone ?? ''),
        ];

        $doc = preg_replace('/\D/', '', $tenant->document ?? '');
        if ($doc && $this->isValidCpfCnpj($doc)) {
            $customer['taxID'] = $doc;
        }

        $payload['customer'] = $customer;

        if ($invoice->due_date) {
            $payload['expiresDate'] = $invoice->due_date->endOfDay()->toISOString();
        }

        try {
            $response = Http::timeout(15)->withHeaders([
                'Authorization' => $this->appId,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/api/v1/charge", $payload);
        } catch (\Throwable $e) {
            ActivityLog::log('billing.charge_failed', "Erro de conexão Woovi: {$e->getMessage()}", $tenant->id);
            throw new \RuntimeException("Falha na conexão com Woovi: {$e->getMessage()}");
        }

        if ($response->successful()) {
            $data = $response->json('charge') ?? $response->json();

            if (empty($data['brCode'])) {
                ActivityLog::log('billing.charge_failed', "Woovi retornou resposta sem brCode: " . $response->body(), $tenant->id);
                throw new \RuntimeException('Woovi retornou resposta inválida — sem código PIX.');
            }

            $qrImage = null;
            $qrUrl = $data['qrCodeImage'] ?? null;
            if ($qrUrl) {
                try {
                    $imgResponse = Http::timeout(10)->get($qrUrl);
                    if ($imgResponse->successful()) {
                        $qrImage = base64_encode($imgResponse->body());
                    }
                } catch (\Throwable $e) {
                    // fallback: store URL
                    $qrImage = $qrUrl;
                }
            }

            $invoice->update([
                'gateway_charge_id' => $data['correlationID'] ?? $correlationID,
                'pix_copy_paste' => $data['brCode'],
                'pix_qr_code' => $qrImage,
            ]);
            ActivityLog::log('billing.charge_created', "Cobranca PIX criada via Woovi: R$ " . number_format($invoice->amount_cents / 100, 2, ',', '.'), $tenant->id);
        } else {
            $error = $response->json('error') ?? $response->body();
            $msg = is_array($error) ? json_encode($error) : $error;
            ActivityLog::log('billing.charge_failed', "Falha ao criar cobranca Woovi ({$response->status()}): {$msg}", $tenant->id);

            if ($response->status() === 403 && str_contains((string) $msg, 'escopo')) {
                throw new \RuntimeException("O aplicativo Woovi não possui as permissões necessárias. Acesse o painel Woovi → API/Plugins e habilite os escopos CHARGE_CREATE e CHARGE_READ no seu aplicativo.");
            }

            if ($response->status() === 401) {
                throw new \RuntimeException("AppID Woovi inválido. Verifique o AppID em Configurações → Gateway.");
            }

            throw new \RuntimeException("Falha ao gerar cobrança PIX (Woovi {$response->status()}): {$msg}");
        }
    }

    public function handleWebhook(array $payload): void
    {
        $event = $payload['event'] ?? '';

        if ($event === 'OPENPIX:CHARGE_COMPLETED') {
            $charge = $payload['charge'] ?? [];
            $correlationID = $charge['correlationID'] ?? null;

            if (!$correlationID) {
                return;
            }

            $invoice = Invoice::where('idempotency_key', $correlationID)
                ->orWhere('gateway_charge_id', $correlationID)
                ->first();

            if (!$invoice || $invoice->status === 'paid') {
                return;
            }

            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            $tenant = $invoice->tenant;
            if ($tenant->status === 'pending_payment') {
                app(TenantProvisioner::class)->provision($tenant);
            } elseif ($tenant->status === 'suspended') {
                app(TenantProvisioner::class)->activate($tenant);
            }

            ActivityLog::log('billing.payment_received', "Pagamento recebido via Woovi: R$ " . $invoice->amountFormatted(), $tenant->id);
        }

        if ($event === 'OPENPIX:CHARGE_EXPIRED') {
            $charge = $payload['charge'] ?? [];
            $correlationID = $charge['correlationID'] ?? null;

            if (!$correlationID) {
                return;
            }

            $invoice = Invoice::where('idempotency_key', $correlationID)
                ->orWhere('gateway_charge_id', $correlationID)
                ->first();

            if ($invoice && $invoice->status === 'pending') {
                $invoice->update(['status' => 'overdue']);
                ActivityLog::log('billing.charge_expired', "Cobranca expirada: R$ " . $invoice->amountFormatted(), $invoice->tenant_id);
            }
        }
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $secret = config('master.woovi.webhook_secret');
        if (!$secret) {
            return true;
        }

        $expected = hash_hmac('sha256', $payload, $secret);
        return hash_equals($expected, $signature);
    }

    public function getCharge(string $correlationID): ?array
    {
        $response = Http::withHeaders([
            'Authorization' => $this->appId,
        ])->get("{$this->baseUrl}/api/v1/charge/{$correlationID}");

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    public function reconcile(): int
    {
        $pending = Invoice::where('status', 'pending')
            ->where('created_at', '>=', now()->subDays(30))
            ->get();

        $confirmed = 0;

        foreach ($pending as $invoice) {
            $id = $invoice->gateway_charge_id ?? $invoice->idempotency_key;
            if (!$id) {
                continue;
            }

            $data = $this->getCharge($id);
            if (!$data) {
                continue;
            }

            $status = $data['charge']['status'] ?? $data['status'] ?? '';

            if ($status === 'COMPLETED') {
                $invoice->update(['status' => 'paid', 'paid_at' => now()]);
                $confirmed++;

                $tenant = $invoice->tenant;
                if ($tenant->status === 'pending_payment') {
                    app(TenantProvisioner::class)->provision($tenant);
                } elseif ($tenant->status === 'suspended') {
                    app(TenantProvisioner::class)->activate($tenant);
                }
            }
        }

        return $confirmed;
    }

    public function suspendOverdue(): int
    {
        $graceDays = config('master.grace_period_days', 3);

        $overdue = Invoice::where('status', 'pending')
            ->where('due_date', '<', now()->subDays($graceDays))
            ->with('tenant')
            ->get();

        $suspended = 0;

        foreach ($overdue as $invoice) {
            $invoice->update(['status' => 'overdue']);
            $tenant = $invoice->tenant;

            if ($tenant->status === 'active') {
                app(TenantProvisioner::class)->suspend($tenant);
                $suspended++;
            }
        }

        return $suspended;
    }

    private function isValidCpfCnpj(string $doc): bool
    {
        $doc = preg_replace('/\D/', '', $doc);

        if (strlen($doc) === 11) {
            if (preg_match('/^(\d)\1{10}$/', $doc)) return false;
            for ($t = 9; $t < 11; $t++) {
                $sum = 0;
                for ($i = 0; $i < $t; $i++) {
                    $sum += $doc[$i] * (($t + 1) - $i);
                }
                $digit = ((10 * $sum) % 11) % 10;
                if ((int) $doc[$t] !== $digit) return false;
            }
            return true;
        }

        if (strlen($doc) === 14) {
            if (preg_match('/^(\d)\1{13}$/', $doc)) return false;
            $weights1 = [5,4,3,2,9,8,7,6,5,4,3,2];
            $weights2 = [6,5,4,3,2,9,8,7,6,5,4,3,2];
            foreach ([$weights1, $weights2] as $idx => $w) {
                $sum = 0;
                $len = 12 + $idx;
                for ($i = 0; $i < $len; $i++) {
                    $sum += $doc[$i] * $w[$i];
                }
                $digit = $sum % 11 < 2 ? 0 : 11 - ($sum % 11);
                if ((int) $doc[$len] !== $digit) return false;
            }
            return true;
        }

        return false;
    }
}
