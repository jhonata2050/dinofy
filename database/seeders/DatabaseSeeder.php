<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $adminEmail = env('ADMIN_EMAIL', 'admin@dinofy.cloud');
        $adminPassword = env('ADMIN_PASSWORD', 'admin123');

        User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Admin',
                'password' => bcrypt($adminPassword),
            ]
        );

        $plans = [
            ['name' => 'Starter', 'slug' => 'starter', 'cpu_limit' => 0.5, 'memory_limit' => '256M', 'storage_limit_gb' => 5, 'max_db_connections' => 20, 'price_cents' => 4700],
            ['name' => 'Pro', 'slug' => 'pro', 'cpu_limit' => 1, 'memory_limit' => '512M', 'storage_limit_gb' => 15, 'max_db_connections' => 50, 'price_cents' => 9700],
            ['name' => 'Business', 'slug' => 'business', 'cpu_limit' => 2, 'memory_limit' => '1G', 'storage_limit_gb' => 30, 'max_db_connections' => 100, 'price_cents' => 19700],
            ['name' => 'Enterprise', 'slug' => 'enterprise', 'cpu_limit' => 4, 'memory_limit' => '2G', 'storage_limit_gb' => 50, 'max_db_connections' => 200, 'price_cents' => 49700],
        ];

        foreach ($plans as $plan) {
            Plan::firstOrCreate(['slug' => $plan['slug']], $plan);
        }

        $settings = [
            // Gateway — Seletor
            ['group' => 'gateway', 'key' => 'gateway_active', 'value' => 'woovi', 'type' => 'select', 'label' => 'Gateway Ativo', 'description' => 'Selecione o gateway de pagamento PIX ativo'],

            // CajuPay
            ['group' => 'gateway', 'key' => 'cajupay_base_url', 'value' => 'https://api.cajupay.com.br', 'type' => 'text', 'label' => '[CajuPay] URL Base', 'description' => 'Endpoint base da API CajuPay'],
            ['group' => 'gateway', 'key' => 'cajupay_api_key', 'value' => '', 'type' => 'password', 'label' => '[CajuPay] API Key', 'description' => 'Chave de API do CajuPay', 'is_encrypted' => true],
            ['group' => 'gateway', 'key' => 'cajupay_api_secret', 'value' => '', 'type' => 'password', 'label' => '[CajuPay] API Secret', 'description' => 'Secret da API do CajuPay', 'is_encrypted' => true],
            ['group' => 'gateway', 'key' => 'cajupay_webhook_secret', 'value' => '', 'type' => 'password', 'label' => '[CajuPay] Webhook Secret', 'description' => 'Secret para validar webhooks do CajuPay', 'is_encrypted' => true],

            // Woovi (OpenPix)
            ['group' => 'gateway', 'key' => 'woovi_sandbox', 'value' => '1', 'type' => 'boolean', 'label' => '[Woovi] Modo Teste', 'description' => 'Usar ambiente sandbox da Woovi (nenhuma cobranca real sera gerada)'],
            ['group' => 'gateway', 'key' => 'woovi_app_id', 'value' => '', 'type' => 'password', 'label' => '[Woovi] AppID', 'description' => 'Chave AppID gerada no painel Woovi (Api/Plugins)', 'is_encrypted' => true],
            ['group' => 'gateway', 'key' => 'woovi_webhook_secret', 'value' => '', 'type' => 'password', 'label' => '[Woovi] Webhook Secret', 'description' => 'Secret para validar webhooks da Woovi', 'is_encrypted' => true],

            // Billing
            ['group' => 'billing', 'key' => 'billing_grace_days', 'value' => '3', 'type' => 'integer', 'label' => 'Dias de Carência', 'description' => 'Dias após vencimento antes de suspender'],
            ['group' => 'billing', 'key' => 'billing_auto_suspend', 'value' => '1', 'type' => 'boolean', 'label' => 'Suspensão Automática', 'description' => 'Suspender automaticamente tenants inadimplentes'],
            ['group' => 'billing', 'key' => 'billing_generate_hour', 'value' => '06:00', 'type' => 'text', 'label' => 'Horário de Geração', 'description' => 'Hora para gerar faturas diárias (formato HH:MM)'],
            ['group' => 'billing', 'key' => 'billing_trial_days', 'value' => '7', 'type' => 'integer', 'label' => 'Dias de Trial', 'description' => 'Período de teste gratuito para novos tenants'],

            // Platform
            ['group' => 'platform', 'key' => 'platform_name', 'value' => 'Dinofy', 'type' => 'text', 'label' => 'Nome da Plataforma', 'description' => 'Nome exibido no painel e comunicações'],
            ['group' => 'platform', 'key' => 'base_domain', 'value' => 'dinofy.cloud', 'type' => 'text', 'label' => 'Domínio Base', 'description' => 'Domínio para subdomínios dos tenants (ex: dinofy.cloud)'],
            ['group' => 'platform', 'key' => 'dinofy_image', 'value' => 'dinofy_app:latest', 'type' => 'text', 'label' => 'Imagem Docker', 'description' => 'Nome da imagem Docker do Dinofy'],
            ['group' => 'platform', 'key' => 'tenant_data_path', 'value' => '/srv/tenants', 'type' => 'text', 'label' => 'Path dos Dados', 'description' => 'Diretório base para dados dos tenants no servidor'],
            ['group' => 'platform', 'key' => 'max_tenants', 'value' => '0', 'type' => 'integer', 'label' => 'Limite de Tenants', 'description' => '0 = ilimitado. Limite máximo de tenants ativos'],

            // Notifications
            ['group' => 'notifications', 'key' => 'notify_email', 'value' => '', 'type' => 'text', 'label' => 'E-mail de Notificação', 'description' => 'E-mail para receber alertas do sistema'],
            ['group' => 'notifications', 'key' => 'notify_on_new_tenant', 'value' => '1', 'type' => 'boolean', 'label' => 'Novo Tenant', 'description' => 'Notificar quando um novo tenant for criado'],
            ['group' => 'notifications', 'key' => 'notify_on_payment', 'value' => '1', 'type' => 'boolean', 'label' => 'Pagamento Recebido', 'description' => 'Notificar quando um pagamento for confirmado'],
            ['group' => 'notifications', 'key' => 'notify_on_overdue', 'value' => '1', 'type' => 'boolean', 'label' => 'Fatura Vencida', 'description' => 'Notificar quando uma fatura vencer'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(['key' => $setting['key']], $setting);
        }

        $demoPlan = Plan::where('slug', 'pro')->first();
        if ($demoPlan) {
            $tenant = Tenant::firstOrCreate(
                ['subdomain' => 'demo'],
                [
                    'plan_id' => $demoPlan->id,
                    'name' => 'Empresa Demo',
                    'email' => 'demo@dinofy.cloud',
                    'phone' => '11999999999',
                    'document' => '12345678000100',
                    'status' => 'active',
                    'compose_project' => 'dinofy-demo',
                    'data_path' => '/srv/tenants/demo',
                    'db_password' => 'demo_password_123',
                    'app_key' => 'base64:dGVzdGtleQ==',
                    'next_billing_date' => now()->addMonth(),
                ]
            );

            TenantUser::firstOrCreate(
                ['email' => 'demo@dinofy.cloud'],
                [
                    'tenant_id' => $tenant->id,
                    'name' => 'Demo User',
                    'password' => bcrypt('demo123'),
                    'is_owner' => true,
                ]
            );
        }
    }
}
