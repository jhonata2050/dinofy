<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\DockerManager;
use App\Services\ResourceMonitor;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke(ResourceMonitor $monitor)
    {
        $tenant = Auth::guard('tenant')->user()->tenant;
        $tenant->load('plan');

        $stats = $monitor->tenantStats($tenant);
        $openTickets = $tenant->tickets()->whereIn('status', ['open', 'in_progress', 'waiting_client'])->count();
        $pendingInvoices = $tenant->invoices()->where('status', 'pending')->count();
        $overdueInvoices = $tenant->invoices()->where('status', 'overdue')->count();

        return view('client.dashboard', compact('tenant', 'stats', 'openTickets', 'pendingInvoices', 'overdueInvoices'));
    }
}
