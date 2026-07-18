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
        ActivityLog::log('tenant.provisioning', "Provisionando {$tenant->subdomain}", $tenant->id);

        $this->generator->saveToDisk($tenant);

        $result = $this->docker->up($tenant);

        if (!$result['success']) {
            ActivityLog::log('tenant.provision_failed', "Falha ao provisionar: {$result['output']}", $tenant->id);
            throw new \RuntimeException("Falha ao provisionar tenant: {$result['output']}");
        }

        $this->traefik->writeTenantConfig($tenant);

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
        $this->docker->destroy($tenant);
        $this->traefik->removeTenantConfig($tenant);
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
