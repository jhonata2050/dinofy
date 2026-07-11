<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Tenant;

class ResourceMonitor
{
    public function __construct(
        private readonly DockerManager $docker,
    ) {}

    public function tenantStats(Tenant $tenant): array
    {
        $stats = $this->docker->stats($tenant);
        $running = $this->docker->isRunning($tenant);
        $disk = $this->diskUsage($tenant);

        $plan = $tenant->plan;
        $storageLimitBytes = $plan->storage_limit_gb * 1073741824;
        $storagePercent = $storageLimitBytes > 0 ? round(($disk['total_bytes'] / $storageLimitBytes) * 100, 1) : 0;

        return [
            'running' => $running,
            'containers' => $stats,
            'plan' => [
                'cpu_limit' => $plan->cpu_limit,
                'memory_limit' => $plan->memory_limit,
                'storage_limit_gb' => $plan->storage_limit_gb,
            ],
            'disk' => [
                'app_bytes' => $disk['app_bytes'],
                'db_bytes' => $disk['db_bytes'],
                'total_bytes' => $disk['total_bytes'],
                'total_formatted' => $this->formatBytes($disk['total_bytes']),
                'limit_formatted' => $plan->storage_limit_gb . ' GB',
                'percent' => $storagePercent,
                'status' => $storagePercent >= 100 ? 'exceeded' : ($storagePercent >= 80 ? 'warning' : 'ok'),
            ],
        ];
    }

    public function diskUsage(Tenant $tenant): array
    {
        $project = $tenant->projectName();
        $composePath = $tenant->data_path . '/docker-compose.yml';

        $appBytes = $this->containerDiskUsage($project, $composePath, 'app', '/var/www/html/storage');
        $dbBytes = $this->containerDiskUsage($project, $composePath, 'mysql', '/var/lib/mysql');

        return [
            'app_bytes' => $appBytes,
            'db_bytes' => $dbBytes,
            'total_bytes' => $appBytes + $dbBytes,
        ];
    }

    public function checkAllStorageLimits(): array
    {
        $tenants = Tenant::where('status', 'active')->with('plan')->get();
        $results = [];

        foreach ($tenants as $tenant) {
            $disk = $this->diskUsage($tenant);
            $limitBytes = $tenant->plan->storage_limit_gb * 1073741824;

            if ($limitBytes <= 0) {
                continue;
            }

            $percent = round(($disk['total_bytes'] / $limitBytes) * 100, 1);

            if ($percent >= 100) {
                $results[$tenant->subdomain] = [
                    'status' => 'exceeded',
                    'percent' => $percent,
                    'used' => $this->formatBytes($disk['total_bytes']),
                    'limit' => $tenant->plan->storage_limit_gb . ' GB',
                ];

                ActivityLog::log(
                    'tenant.storage_exceeded',
                    "Tenant {$tenant->subdomain} excedeu limite de disco: {$this->formatBytes($disk['total_bytes'])} / {$tenant->plan->storage_limit_gb}GB ({$percent}%)",
                    $tenant->id,
                    ['percent' => $percent, 'used_bytes' => $disk['total_bytes'], 'limit_bytes' => $limitBytes]
                );
            } elseif ($percent >= 80) {
                $results[$tenant->subdomain] = [
                    'status' => 'warning',
                    'percent' => $percent,
                    'used' => $this->formatBytes($disk['total_bytes']),
                    'limit' => $tenant->plan->storage_limit_gb . ' GB',
                ];

                ActivityLog::log(
                    'tenant.storage_warning',
                    "Tenant {$tenant->subdomain} usando {$percent}% do disco",
                    $tenant->id,
                    ['percent' => $percent, 'used_bytes' => $disk['total_bytes'], 'limit_bytes' => $limitBytes]
                );
            }
        }

        return $results;
    }

    public function allTenantsStats(): array
    {
        $tenants = Tenant::where('status', 'active')->with('plan')->get();
        $result = [];

        foreach ($tenants as $tenant) {
            $result[$tenant->subdomain] = $this->tenantStats($tenant);
        }

        return $result;
    }

    public function summary(): array
    {
        $tenants = Tenant::with('plan')->get();

        return [
            'total' => $tenants->count(),
            'active' => $tenants->where('status', 'active')->count(),
            'suspended' => $tenants->where('status', 'suspended')->count(),
            'provisioning' => $tenants->where('status', 'provisioning')->count(),
            'total_cpu_allocated' => $tenants->where('status', 'active')->sum(fn ($t) => $t->plan->cpu_limit ?? 0),
            'total_memory_allocated' => $tenants->where('status', 'active')->sum(fn ($t) => (float) str_replace(['M', 'G'], '', $t->plan->memory_limit ?? '0')),
            'total_storage_allocated' => $tenants->where('status', 'active')->sum(fn ($t) => $t->plan->storage_limit_gb ?? 0),
        ];
    }

    private function containerDiskUsage(string $project, string $composePath, string $service, string $path): int
    {
        if (!file_exists($composePath)) {
            return 0;
        }

        $process = new \Symfony\Component\Process\Process([
            'docker', 'compose', '-p', $project, '-f', $composePath,
            'exec', '-T', $service, 'du', '-sb', $path,
        ]);
        $process->setTimeout(15);
        $process->run();

        if (!$process->isSuccessful()) {
            return 0;
        }

        $output = trim($process->getOutput());
        $parts = preg_split('/\s+/', $output);

        return (int) ($parts[0] ?? 0);
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2, ',', '.') . ' GB';
        }
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1, ',', '.') . ' MB';
        }

        return number_format($bytes / 1024, 0, ',', '.') . ' KB';
    }
}
