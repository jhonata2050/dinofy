<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Services\BillingGatewayFactory;
use App\Services\TenantProvisioner;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function index()
    {
        $plans = Plan::where('is_active', true)->orderBy('price_cents')->get();
        return view('checkout.index', compact('plans'));
    }

    public function show(string $slug)
    {
        $plan = Plan::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $plans = Plan::where('is_active', true)->orderBy('price_cents')->get();
        return view('checkout.show', compact('plan', 'plans'));
    }

    public function process(Request $request, string $slug)
    {
        $plan = Plan::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:tenants,email',
            'phone' => 'required|string|max:20',
            'document' => 'required|string|max:20',
            'subdomain' => 'required|alpha_dash|min:5|max:32|unique:tenants,subdomain',
            'password' => 'required|min:8|confirmed',
        ], [
            'subdomain.unique' => 'Este subdomínio já está em uso.',
            'subdomain.alpha_dash' => 'O subdomínio só pode conter letras, números, hífens e underscores.',
            'email.unique' => 'Este e-mail já está cadastrado.',
        ]);

        $credentials = TenantProvisioner::generateCredentials();
        $basePath = config('master.tenant_data_path');
        $trialDays = (int) (\App\Models\Setting::get('billing_trial_days') ?? 7);

        $tenant = Tenant::create([
            'plan_id' => $plan->id,
            'subdomain' => $validated['subdomain'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'document' => $validated['document'],
            'status' => 'pending_payment',
            'compose_project' => "dinofy-{$validated['subdomain']}",
            'data_path' => "{$basePath}/{$validated['subdomain']}",
            'db_password' => $credentials['db_password'],
            'app_key' => $credentials['app_key'],
            'trial_ends_at' => $trialDays > 0 ? now()->addDays($trialDays) : null,
        ]);

        TenantUser::create([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'is_owner' => true,
        ]);

        $token = Str::random(48);
        $invoice = Invoice::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'amount_cents' => $plan->price_cents,
            'period_start' => now(),
            'period_end' => now()->addMonth(),
            'due_date' => now()->addDays(3),
            'status' => 'pending',
            'idempotency_key' => $token,
        ]);

        ActivityLog::log('tenant.signup', "Novo cadastro: {$tenant->subdomain} - {$plan->name}", $tenant->id);

        try {
            BillingGatewayFactory::make()->createPixCharge($invoice, $tenant);
        } catch (\Throwable $e) {
            ActivityLog::log('billing.charge_failed', "Falha ao gerar PIX no checkout: {$e->getMessage()}", $tenant->id);
        }

        return redirect()->route('checkout.payment', $token);
    }

    public function payment(string $token)
    {
        $invoice = Invoice::where('idempotency_key', $token)->with(['tenant.plan'])->firstOrFail();
        return view('checkout.payment', compact('invoice'));
    }

    public function checkPayment(string $token)
    {
        $invoice = Invoice::where('idempotency_key', $token)->firstOrFail();

        return response()->json([
            'status' => $invoice->status,
            'paid' => $invoice->status === 'paid',
        ]);
    }

    private const RESERVED_SUBDOMAINS = [
        // Infra / DNS
        'www', 'mail', 'ftp', 'ssh', 'vpn', 'ns1', 'ns2', 'ns3', 'dns',
        'smtp', 'pop', 'pop3', 'imap', 'mx', 'relay', 'proxy', 'gateway',
        'cdn', 'static', 'assets', 'media', 'img', 'images', 'files',
        'cpanel', 'whm', 'webmail', 'phpmyadmin', 'mysql', 'redis',
        'mongo', 'postgres', 'db', 'database', 'backup', 'backups',
        // Plataforma
        'admin', 'master', 'api', 'app', 'painel', 'panel', 'console',
        'dashboard', 'portal', 'hub', 'central', 'sistema', 'system',
        'checkout', 'login', 'register', 'signup', 'signin', 'auth',
        'client', 'cliente', 'clients', 'clientes', 'account', 'conta',
        'billing', 'payment', 'pagamento', 'pagamentos', 'pay', 'pix',
        'fatura', 'faturas', 'invoice', 'invoices',
        'cobranca', 'cobrancas', 'assinatura', 'assinaturas',
        'webhook', 'webhooks', 'callback', 'oauth', 'sso',
        // Conteudo
        'blog', 'shop', 'store', 'loja', 'docs', 'doc', 'wiki',
        'help', 'support', 'suporte', 'ticket', 'tickets', 'faq',
        'status', 'monitor', 'health', 'ping', 'uptime',
        'news', 'updates', 'changelog', 'release', 'releases',
        // Ambiente
        'test', 'testing', 'staging', 'dev', 'development', 'local',
        'sandbox', 'demo', 'preview', 'beta', 'alpha', 'canary',
        'prod', 'production', 'live',
        // Marca
        'dinofy', 'dinofycloud', 'dinofy-cloud', 'dinofy-app',
        'delivery', 'food', 'pedido', 'pedidos', 'cardapio',
        // Generico sensivel
        'root', 'super', 'sudo', 'god', 'null', 'undefined',
        'info', 'contato', 'contact', 'about', 'sobre',
        'terms', 'termos', 'privacy', 'privacidade', 'legal',
        'download', 'downloads', 'update', 'upgrade',
    ];

    public function checkSubdomain(Request $request)
    {
        $subdomain = Str::lower(trim($request->input('subdomain', '')));

        if (strlen($subdomain) < 5) {
            return response()->json([
                'available' => false,
                'message' => 'Minimo de 5 caracteres.',
                'suggestions' => [],
            ]);
        }

        if (strlen($subdomain) > 32) {
            return response()->json([
                'available' => false,
                'message' => 'Maximo de 32 caracteres.',
                'suggestions' => [],
            ]);
        }

        if (!preg_match('/^[a-z0-9][a-z0-9\-]*[a-z0-9]$/', $subdomain) && strlen($subdomain) >= 5) {
            return response()->json([
                'available' => false,
                'message' => 'Use apenas letras minusculas, numeros e hifens. Nao pode comecar ou terminar com hifen.',
                'suggestions' => [],
            ]);
        }

        if (in_array($subdomain, self::RESERVED_SUBDOMAINS, true)) {
            return response()->json([
                'available' => false,
                'message' => 'Este subdominio e reservado.',
                'suggestions' => $this->generateSuggestions($subdomain),
            ]);
        }

        $taken = Tenant::where('subdomain', $subdomain)->exists();

        if ($taken) {
            return response()->json([
                'available' => false,
                'message' => 'Este subdominio ja esta em uso.',
                'suggestions' => $this->generateSuggestions($subdomain),
            ]);
        }

        return response()->json([
            'available' => true,
            'message' => 'Disponivel!',
            'suggestions' => [],
        ]);
    }

    private function generateSuggestions(string $base): array
    {
        $base = preg_replace('/[\-]+$/', '', $base);
        $base = preg_replace('/\d+$/', '', $base);

        $candidates = [
            $base . '-delivery',
            $base . '-app',
            $base . '-food',
            $base . '-' . rand(10, 99),
            $base . '-' . date('Y'),
            'meu-' . $base,
            $base . '-online',
            $base . '-pedidos',
        ];

        $existing = Tenant::whereIn('subdomain', $candidates)->pluck('subdomain')->toArray();
        $reserved = self::RESERVED_SUBDOMAINS;

        $available = [];
        foreach ($candidates as $c) {
            if (!in_array($c, $existing) && !in_array($c, $reserved) && strlen($c) <= 32) {
                $available[] = $c;
            }
            if (count($available) >= 4) break;
        }

        return $available;
    }
}
