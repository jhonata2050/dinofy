<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Invoice;
use App\Models\Tenant;
use App\Services\ResourceMonitor;

class DashboardController extends Controller
{
    public function __invoke(ResourceMonitor $monitor)
    {
        $stats = $monitor->summary();

        $revenue = Invoice::where('status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount_cents');

        $pendingRevenue = Invoice::where('status', 'pending')->sum('amount_cents');
        $overdueCount = Invoice::where('status', 'overdue')->count();

        $recentTenants = Tenant::with('plan')
            ->latest()
            ->take(5)
            ->get();

        $recentLogs = ActivityLog::with('tenant')
            ->latest()
            ->take(8)
            ->get();

        return view('dashboard', compact(
            'stats',
            'revenue',
            'pendingRevenue',
            'overdueCount',
            'recentTenants',
            'recentLogs',
        ));
    }
}
