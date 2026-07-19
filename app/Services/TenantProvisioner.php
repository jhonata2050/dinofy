<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Tenant;
use App\Models\TenantUser;
use Illuminate\Support\Str;

class TenantProvisioner
{
    public function __construct(
        private readonly DockerComposeGenerator $generator,
        private readonly DockerManager $docker,
        private readonly TraefikManager $traefik,
    ) {}

    public function provision(Tenant $tenant): void
    {
        $tenant->update(['status' => 'provisioning']);
        ActivityLog::log('tenant.provisioning', "Iniciando provisioning de {$tenant->subdomain}", $tenant->id);

        // Step 1: Gerar docker-compose.yml
        try {
            $composePath = $this->generator->saveToDisk($tenant);
            ActivityLog::log('tenant.provision_step', "Compose gerado em {$composePath}", $tenant->id);
        } catch (\Throwable $e) {
            $msg = "Falha ao gerar compose: {$e->getMessage()}";
            ActivityLog::log('tenant.provision_failed', $msg, $tenant->id);
            throw new \RuntimeException($msg);
        }

        // Step 2: Docker compose up
        ActivityLog::log('tenant.provision_step', "Executando docker compose up para {$tenant->compose_project}...", $tenant->id);
        $result = $this->docker->up($tenant);

        if (!$result['success']) {
            $output = trim($result['output'] ?: 'Sem output do Docker');
            $msg = "Docker compose up falhou (exit {$result['exit_code']}): {$output}";
            ActivityLog::log('tenant.provision_failed', $msg, $tenant->id);
            throw new \RuntimeException($msg);
        }

        ActivityLog::log('tenant.provision_step', "Containers criados com sucesso", $tenant->id);

        // Conectar o container app do tenant à rede dinofy-net para o Nginx proxy do Master
        try {
            $containerName = "{$tenant->compose_project}-app-1";
            @exec("docker network connect dinofy-net {$containerName} 2>&1");
        } catch (\Throwable $e) {
            ActivityLog::log('tenant.provision_warning', "Falha ao conectar container na dinofy-net: {$e->getMessage()}", $tenant->id);
        }

        // Step 2.1: Finalizar setup no container do cliente para pular /docker-setup
        try {
            $url = $tenant->fullUrl();
            $host = parse_url($url, PHP_URL_HOST) ?: $tenant->subdomain;

            $this->docker->exec($tenant, 'app', [
                'sh', '-c',
                "mkdir -p /var/www/html/.docker && " .
                "echo \"{$url}\" > /var/www/html/.docker/app.url && " .
                "echo \"true\" > /var/www/html/.docker/setup.done && " .
                "echo \"true\" > /var/www/html/.docker/app.installed && " .
                "echo \"{$host} {\n\treverse_proxy app:80\n}\" > /var/www/html/.docker/Caddyfile.domains"
            ]);
            ActivityLog::log('tenant.provision_step', "Setup automático gravado no container", $tenant->id);
        } catch (\Throwable $e) {
            ActivityLog::log('tenant.provision_warning', "Falha no setup automático: {$e->getMessage()}", $tenant->id);
        }

        // Step 2.2: Criar o usuário Admin do container do cliente para pular /criar-admin
        try {
            $owner = TenantUser::where('tenant_id', $tenant->id)->where('is_owner', true)->first();
            if ($owner) {
                $email = addslashes($owner->email);
                $name = addslashes($owner->name);
                $password = addslashes($owner->password);

                $cmd = "require 'vendor/autoload.php'; " .
                       "\$app = require_once 'bootstrap/app.php'; " .
                       "\$app->make('Illuminate\\\\Contracts\\\\Console\\\\Kernel')->bootstrap(); " .
                       "\\App\\Models\\User::firstOrCreate(['email' => '{$email}'], ['name' => '{$name}', 'password' => '{$password}', 'role' => 'admin']);";

                $this->docker->exec($tenant, 'app', ['php', '-r', $cmd]);
                ActivityLog::log('tenant.provision_step', "Usuário administrador inicial criado no container do cliente", $tenant->id);
            }
        } catch (\Throwable $e) {
            ActivityLog::log('tenant.provision_warning', "Falha ao criar admin no container do cliente: {$e->getMessage()}", $tenant->id);
        }

        // Step 3: Traefik config
        try {
            $this->traefik->writeTenantConfig($tenant);
            ActivityLog::log('tenant.provision_step', "Config Traefik gravada", $tenant->id);
        } catch (\Throwable $e) {
            ActivityLog::log('tenant.provision_warning', "Traefik config falhou (tenant funcional): {$e->getMessage()}", $tenant->id);
        }

        // Step 4: Ativar
        $tenant->update([
            'status' => 'active',
            'next_billing_date' => $tenant->trial_ends_at ?? now()->addMonth(),
        ]);

        TenantUser::firstOrCreate(
            ['email' => $tenant->email],
            [
                'tenant_id' => $tenant->id,
                'name' => $tenant->name,
                'password' => bcrypt(Str::random(12)),
                'is_owner' => true,
            ]
        );

        ActivityLog::log('tenant.provisioned', "Tenant {$tenant->subdomain} ativo", $tenant->id);
    }

    public function suspend(Tenant $tenant): void
    {
        $this->docker->stop($tenant);
        $this->traefik->removeTenantConfig($tenant);
        $tenant->update(['status' => 'suspended']);
        ActivityLog::log('tenant.suspended', "Tenant {$tenant->subdomain} suspenso", $tenant->id);
    }

    public function activate(Tenant $tenant): void
    {
        $this->docker->start($tenant);
        $this->traefik->writeTenantConfig($tenant);
        $tenant->update(['status' => 'active']);
        ActivityLog::log('tenant.activated', "Tenant {$tenant->subdomain} reativado", $tenant->id);
    }

    public function terminate(Tenant $tenant): void
    {
        $tenant->update(['status' => 'terminating']);
        try {
            $this->docker->destroy($tenant);
        } catch (\Throwable $e) {
            ActivityLog::log('tenant.terminate_warning', "Docker destroy falhou: {$e->getMessage()}", $tenant->id);
        }
        try {
            $this->traefik->removeTenantConfig($tenant);
        } catch (\Throwable $e) {
            // ignore
        }
        $tenant->update(['status' => 'terminated']);
        ActivityLog::log('tenant.terminated', "Tenant {$tenant->subdomain} destruído", $tenant->id);
    }

    public function updatePlan(Tenant $tenant): void
    {
        $this->generator->saveToDisk($tenant);
        $this->docker->up($tenant);
        ActivityLog::log('tenant.plan_updated', "Plano atualizado para {$tenant->plan->name}", $tenant->id);
    }

    public static function generateCredentials(): array
    {
        return [
            'db_password' => Str::random(24),
            'app_key' => 'base64:' . base64_encode(random_bytes(32)),
        ];
    }
}
