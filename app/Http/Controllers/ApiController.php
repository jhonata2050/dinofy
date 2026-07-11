<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{
    public function revenueChart(): JsonResponse
    {
        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months->push([
                'label' => $date->translatedFormat('M/Y'),
                'month' => $date->month,
                'year' => $date->year,
            ]);
        }

        $invoices = Invoice::where('status', 'paid')
            ->where('paid_at', '>=', now()->subMonths(6)->startOfMonth())
            ->selectRaw('MONTH(paid_at) as month, YEAR(paid_at) as year, SUM(amount_cents) as total')
            ->groupByRaw('YEAR(paid_at), MONTH(paid_at)')
            ->get()
            ->keyBy(fn ($i) => $i->year . '-' . $i->month);

        $labels = $months->pluck('label');
        $data = $months->map(function ($m) use ($invoices) {
            $key = $m['year'] . '-' . $m['month'];
            return ($invoices[$key]->total ?? 0) / 100;
        });

        return response()->json([
            'labels' => $labels,
            'data' => $data,
        ]);
    }

    public function tenantGrowthChart(): JsonResponse
    {
        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months->push([
                'label' => $date->translatedFormat('M/Y'),
                'end' => $date->endOfMonth()->toDateString(),
            ]);
        }

        $data = $months->map(function ($m) {
            return Tenant::where('created_at', '<=', $m['end'])
                ->whereNotIn('status', ['terminated'])
                ->count();
        });

        return response()->json([
            'labels' => $months->pluck('label'),
            'data' => $data,
        ]);
    }

    public function planDistributionChart(): JsonResponse
    {
        $plans = Tenant::whereNotIn('status', ['terminated'])
            ->join('plans', 'tenants.plan_id', '=', 'plans.id')
            ->selectRaw('plans.name, COUNT(*) as total')
            ->groupBy('plans.name')
            ->pluck('total', 'name');

        return response()->json([
            'labels' => $plans->keys(),
            'data' => $plans->values(),
        ]);
    }

    public function invoiceStatusChart(): JsonResponse
    {
        $statuses = Invoice::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $map = [
            'paid' => ['label' => 'Pago', 'color' => '#22c55e'],
            'pending' => ['label' => 'Pendente', 'color' => '#eab308'],
            'overdue' => ['label' => 'Vencido', 'color' => '#ef4444'],
            'cancelled' => ['label' => 'Cancelado', 'color' => '#6b7280'],
        ];

        $labels = [];
        $data = [];
        $colors = [];

        foreach ($map as $status => $info) {
            if ($statuses->has($status)) {
                $labels[] = $info['label'];
                $data[] = $statuses[$status];
                $colors[] = $info['color'];
            }
        }

        return response()->json(compact('labels', 'data', 'colors'));
    }

    public function systemHealth(): JsonResponse
    {
        $tenants = Tenant::where('status', 'active')->count();
        $suspended = Tenant::where('status', 'suspended')->count();
        $pendingInvoices = Invoice::where('status', 'pending')->count();
        $overdueInvoices = Invoice::where('status', 'overdue')->count();
        $monthlyRevenue = Invoice::where('status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount_cents');

        return response()->json([
            'active_tenants' => $tenants,
            'suspended_tenants' => $suspended,
            'pending_invoices' => $pendingInvoices,
            'overdue_invoices' => $overdueInvoices,
            'monthly_revenue_cents' => $monthlyRevenue,
        ]);
    }
}
