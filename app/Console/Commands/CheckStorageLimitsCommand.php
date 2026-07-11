<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\ResourceMonitor;
use App\Services\TenantProvisioner;
use Illuminate\Console\Command;

class CheckStorageLimitsCommand extends Command
{
    protected $signature = 'tenants:check-storage {--suspend : Auto-suspende tenants que excederam o limite}';
    protected $description = 'Verifica uso de disco de todos os tenants ativos';

    public function handle(ResourceMonitor $monitor, TenantProvisioner $provisioner): int
    {
        $this->info('Verificando uso de disco...');

        $results = $monitor->checkAllStorageLimits();

        if (empty($results)) {
            $this->info('Todos os tenants dentro do limite.');
            return 0;
        }

        foreach ($results as $subdomain => $data) {
            $icon = $data['status'] === 'exceeded' ? '🔴' : '🟡';
            $this->warn("{$icon} {$subdomain}: {$data['used']} / {$data['limit']} ({$data['percent']}%)");

            if ($data['status'] === 'exceeded' && $this->option('suspend')) {
                $tenant = Tenant::where('subdomain', $subdomain)->first();
                if ($tenant && $tenant->status === 'active') {
                    $provisioner->suspend($tenant);
                    $this->error("  → Tenant {$subdomain} suspenso por exceder limite de disco.");
                }
            }
        }

        $exceeded = collect($results)->where('status', 'exceeded')->count();
        $warnings = collect($results)->where('status', 'warning')->count();

        $this->info("Resultado: {$exceeded} excedidos, {$warnings} em alerta.");

        return $exceeded > 0 ? 1 : 0;
    }
}
