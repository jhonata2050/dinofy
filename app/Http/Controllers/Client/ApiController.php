<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\ResourceMonitor;
use Illuminate\Support\Facades\Auth;

class ApiController extends Controller
{
    public function resources(ResourceMonitor $monitor)
    {
        $tenant = Auth::guard('tenant')->user()->tenant;
        $tenant->load('plan');

        $stats = $monitor->tenantStats($tenant);

        $cpuUsed = 0;
        $memUsedMb = 0;
        $memTotalMb = (float) str_replace(['M', 'G'], ['', '000'], $tenant->plan->memory_limit);

        foreach ($stats['containers'] ?? [] as $c) {
            $cpuUsed += (float) str_replace('%', '', $c['cpu'] ?? '0');

            $memParts = explode(' / ', $c['memory'] ?? '0MiB / 0MiB');
            if (isset($memParts[0])) {
                $val = trim($memParts[0]);
                if (str_contains($val, 'GiB')) {
                    $memUsedMb += (float) str_replace('GiB', '', $val) * 1024;
                } else {
                    $memUsedMb += (float) str_replace('MiB', '', $val);
                }
            }
        }

        $cpuLimit = $tenant->plan->cpu_limit * 100;

        return response()->json([
            'timestamp' => now()->format('H:i:s'),
            'running' => $stats['running'],
            'cpu' => [
                'used_percent' => round($cpuUsed, 1),
                'limit_percent' => round($cpuLimit, 0),
                'cores' => $tenant->plan->cpu_limit,
            ],
            'memory' => [
                'used_mb' => round($memUsedMb, 1),
                'limit_mb' => round($memTotalMb, 0),
                'percent' => $memTotalMb > 0 ? round(($memUsedMb / $memTotalMb) * 100, 1) : 0,
            ],
            'disk' => [
                'used_bytes' => $stats['disk']['total_bytes'] ?? 0,
                'limit_bytes' => ($tenant->plan->storage_limit_gb ?? 0) * 1073741824,
                'used_formatted' => $stats['disk']['total_formatted'] ?? '0 KB',
                'limit_formatted' => $stats['disk']['limit_formatted'] ?? '0 GB',
                'percent' => $stats['disk']['percent'] ?? 0,
                'app_bytes' => $stats['disk']['app_bytes'] ?? 0,
                'db_bytes' => $stats['disk']['db_bytes'] ?? 0,
            ],
            'containers' => collect($stats['containers'] ?? [])->map(fn ($c) => [
                'name' => $c['name'] ?? '',
                'cpu' => $c['cpu'] ?? '0%',
                'memory' => $c['memory'] ?? '0MiB',
                'mem_percent' => $c['mem_percent'] ?? '0%',
            ])->values(),
        ]);
    }
}
