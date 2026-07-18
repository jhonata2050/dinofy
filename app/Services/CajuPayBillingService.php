<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Invoice;
use App\Models\Tenant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CajuPayBillingService implements BillingGatewayInterface
{
    private string $baseUrl;
    private string $apiKey;
    private string $secretKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(\App\Models\Setting::get('cajupay_base_url') ?: config('master.cajupay.base_url'), '/');
        $this->apiKey = \App\Models\Setting::get('cajupay_api_key') ?: (config('master.cajupay.api_key') ?? '');
        $this->secretKey = \App\Models\Setting::get('cajupay_api_secret') ?: (config('master.cajupay.api_secret') ?? '');
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
        if (empty($this->apiKey)) {
            throw new \RuntimeException('Gateway CajuPay não configurado. Cadastre a API Key em Configurações > Gateway.');
        }

        try {
            $response = Http::timeout(15)->withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'X-Secret-Key' => $this->secretKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/api/v1/pix/charges", [
                'amount' => $invoice->amount_cents,
                'description' => "Dinofy {$tenant->plan->name} - {$tenant->subdomain}",
                'customer' => [
                    'name' => $tenant->name,
                    'email' => $tenant->email,
                    'document' => $tenant->document,
                ],
                'idempotency_key' => $invoice->idempotency_key,
            ]);
        } catch (\Throwable $e) {
            ActivityLog::log('billing.charge_failed', "Erro de conexão CajuPay: {$e->getMessage()}", $tenant->id);
            throw new \RuntimeException("Falha na conexão com CajuPay: {$e->getMessage()}");
        }

        if ($response->successful()) {
            $data = $response->json();
            $invoice->update([
                'gateway_charge_id' => $data['id'] ?? null,
                'pix_copy_paste' => $data['pix_copy_paste'] ?? null,
                'pix_qr_code' => $data['pix_qr_code'] ?? null,
            ]);
            ActivityLog::log('billing.charge_created', "Cobranca PIX criada via CajuPay: R$ " . number_format($invoice->amount_cents / 100, 2, ',', '.'), $tenant->id);
        } else {
            $msg = $response->body();
            ActivityLog::log('billing.charge_failed', "Falha ao criar cobranca CajuPay ({$response->status()}): {$msg}", $tenant->id);
            throw new \RuntimeException("Falha ao gerar cobrança PIX (CajuPay {$response->status()}): {$msg}");
        }
    }

    public function handleWebhook(array $payload): void
    {
        $event = $payload['event'] ?? '';
        $paymentId = $payload['data']['id'] ?? null;

        if ($event !== 'pix.payment.paid' || !$paymentId) {
            return;
        }

        $invoice = Invoice::where('gateway_charge_id', $paymentId)->first();
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

        ActivityLog::log('billing.payment_received', "Pagamento recebido via CajuPay: R$ " . $invoice->amountFormatted(), $tenant->id);
    }

    public function reconcile(): int
    {
        $pending = Invoice::where('status', 'pending')
            ->where('created_at', '>=', now()->subDays(30))
            ->get();

        $confirmed = 0;

        foreach ($pending as $invoice) {
            if (!$invoice->gateway_charge_id) {
                continue;
            }

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'X-Secret-Key' => $this->secretKey,
            ])->get("{$this->baseUrl}/api/v1/pix/charges/{$invoice->gateway_charge_id}");

            if (!$response->successful()) {
                continue;
            }

            $data = $response->json();
            if (($data['status'] ?? '') === 'paid') {
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
}
