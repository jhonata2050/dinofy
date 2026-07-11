@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-semibold text-zinc-900">Dashboard</h1>
        <p class="text-zinc-500 text-sm mt-0.5">Visão geral da plataforma Dinofy SaaS</p>
    </div>
    <div class="text-sm text-zinc-400">{{ now()->translatedFormat('d M Y, H:i') }}</div>
</div>

{{-- KPI Cards --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="rounded-xl border border-zinc-50 bg-zinc-50 p-5">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-sm text-zinc-500 mb-1">Tenants Ativos</div>
                <div class="text-2xl font-bold text-zinc-900">{{ $stats['active'] }}</div>
                <div class="text-xs text-zinc-400 mt-1">de {{ $stats['total'] }} total</div>
            </div>
            <div class="flex h-10 w-10 shrink-0 items-center justify-center" style="color: var(--color-primary);">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-zinc-50 bg-zinc-50 p-5">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-sm text-zinc-500 mb-1">Receita do Mês</div>
                <div class="text-2xl font-bold text-zinc-900">R$ {{ number_format($revenue / 100, 2, ',', '.') }}</div>
            </div>
            <div class="flex h-10 w-10 shrink-0 items-center justify-center" style="color: var(--color-primary);">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-zinc-50 bg-zinc-50 p-5">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-sm text-zinc-500 mb-1">Pendente</div>
                <div class="text-2xl font-bold text-amber-600">R$ {{ number_format($pendingRevenue / 100, 2, ',', '.') }}</div>
            </div>
            <div class="flex h-10 w-10 shrink-0 items-center justify-center text-amber-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-zinc-50 bg-zinc-50 p-5">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-sm text-zinc-500 mb-1">Suspensos / Inadimplentes</div>
                <div class="text-2xl font-bold text-red-600">{{ $stats['suspended'] }} / {{ $overdueCount }}</div>
            </div>
            <div class="flex h-10 w-10 shrink-0 items-center justify-center text-red-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
        </div>
    </div>
</div>

{{-- Charts Row --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
    <div class="rounded-xl border border-zinc-50 bg-zinc-50 p-5">
        <h2 class="text-sm font-semibold text-zinc-700 mb-4">Receita Mensal</h2>
        <div class="h-56">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <div class="rounded-xl border border-zinc-50 bg-zinc-50 p-5">
        <h2 class="text-sm font-semibold text-zinc-700 mb-4">Crescimento de Tenants</h2>
        <div class="h-56">
            <canvas id="tenantGrowthChart"></canvas>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
    <div class="rounded-xl border border-zinc-50 bg-zinc-50 p-5">
        <h2 class="text-sm font-semibold text-zinc-700 mb-4">Distribuição por Plano</h2>
        <div class="h-48 flex items-center justify-center">
            <canvas id="planChart"></canvas>
        </div>
    </div>

    <div class="rounded-xl border border-zinc-50 bg-zinc-50 p-5">
        <h2 class="text-sm font-semibold text-zinc-700 mb-4">Status das Faturas</h2>
        <div class="h-48 flex items-center justify-center">
            <canvas id="invoiceStatusChart"></canvas>
        </div>
    </div>

    <div class="rounded-xl border border-zinc-50 bg-zinc-50 p-5">
        <h2 class="text-sm font-semibold text-zinc-700 mb-4">Recursos Alocados</h2>
        <div class="space-y-4 pt-2">
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-zinc-500">CPUs</span>
                    <span class="font-mono font-semibold text-zinc-700">{{ $stats['total_cpu_allocated'] }} cores</span>
                </div>
                <div class="w-full bg-zinc-200 rounded-full h-2">
                    <div class="h-2 rounded-full" style="width: {{ min(100, $stats['total_cpu_allocated'] * 10) }}%; background: var(--color-primary);"></div>
                </div>
            </div>
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-zinc-500">Memória</span>
                    <span class="font-mono font-semibold text-zinc-700">{{ $stats['total_memory_allocated'] }} MB</span>
                </div>
                <div class="w-full bg-zinc-200 rounded-full h-2">
                    <div class="bg-violet-500 h-2 rounded-full" style="width: {{ min(100, $stats['total_memory_allocated'] / 80) }}%"></div>
                </div>
            </div>
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-zinc-500">Provisionando</span>
                    <span class="font-mono font-semibold text-zinc-700">{{ $stats['provisioning'] }}</span>
                </div>
            </div>
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-zinc-500">Tenants Ativos</span>
                    <span class="font-mono font-semibold text-emerald-600">{{ $stats['active'] }}</span>
                </div>
            </div>
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-zinc-500">Suspensos</span>
                    <span class="font-mono font-semibold text-red-600">{{ $stats['suspended'] }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Recent Tenants & Activity --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div class="rounded-xl border border-zinc-50 bg-zinc-50 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-zinc-700">Clientes Recentes</h2>
            <a href="{{ route('admin.tenants.index') }}" class="text-sm font-medium hover:underline" style="color: var(--color-primary);">Ver todos</a>
        </div>
        @if($recentTenants->isEmpty())
            <div class="text-center py-8">
                <p class="text-zinc-400 text-sm">Nenhum cliente cadastrado ainda.</p>
                <a href="{{ route('admin.tenants.create') }}" class="mt-3 inline-block text-sm font-medium hover:underline" style="color: var(--color-primary);">Criar primeiro cliente</a>
            </div>
        @else
            <div class="space-y-2">
                @foreach($recentTenants as $tenant)
                    <div class="flex items-center justify-between p-3 rounded-lg hover:bg-zinc-100 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-zinc-200 flex items-center justify-center text-zinc-700 font-semibold text-xs">
                                {{ strtoupper(substr($tenant->subdomain, 0, 2)) }}
                            </div>
                            <div>
                                <a href="{{ route('admin.tenants.show', $tenant) }}" class="text-sm text-zinc-800 font-medium hover:underline">{{ $tenant->subdomain }}</a>
                                <div class="text-xs text-zinc-400">{{ $tenant->name }} — {{ $tenant->plan->name }}</div>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full
                            {{ $tenant->status === 'active' ? 'bg-emerald-100 text-emerald-700' : '' }}
                            {{ $tenant->status === 'suspended' ? 'bg-red-100 text-red-700' : '' }}
                            {{ $tenant->status === 'provisioning' ? 'bg-amber-100 text-amber-700' : '' }}
                            {{ $tenant->status === 'terminated' ? 'bg-zinc-200 text-zinc-600' : '' }}
                        ">{{ ucfirst($tenant->status) }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="rounded-xl border border-zinc-50 bg-zinc-50 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-zinc-700">Atividade Recente</h2>
            <a href="{{ route('admin.activity-logs.index') }}" class="text-sm font-medium hover:underline" style="color: var(--color-primary);">Ver tudo</a>
        </div>
        @if($recentLogs->isEmpty())
            <div class="text-center py-8">
                <p class="text-zinc-400 text-sm">Nenhuma atividade registrada.</p>
            </div>
        @else
            <div class="space-y-2">
                @foreach($recentLogs as $log)
                    <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-zinc-100 transition">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 mt-0.5 bg-zinc-200 text-zinc-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm text-zinc-700 truncate">{{ $log->description }}</p>
                            <p class="text-xs text-zinc-400 mt-0.5">{{ $log->created_at->diffForHumans() }} — <span class="font-mono">{{ $log->action }}</span></p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
    const chartDefaults = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
    };

    async function loadCharts() {
        const headers = { 'X-Requested-With': 'XMLHttpRequest' };

        try {
            const rev = await fetch('/admin/api/charts/revenue', { headers }).then(r => r.json());
            new Chart(document.getElementById('revenueChart'), {
                type: 'bar',
                data: {
                    labels: rev.labels,
                    datasets: [{
                        label: 'Receita (R$)',
                        data: rev.data,
                        backgroundColor: '#2d6a1e',
                        borderRadius: 6,
                        barPercentage: 0.6,
                    }]
                },
                options: {
                    ...chartDefaults,
                    scales: {
                        y: { beginAtZero: true, ticks: { callback: v => 'R$ ' + v.toLocaleString('pt-BR') }, grid: { color: '#e4e4e7' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        } catch(e) {}

        try {
            const growth = await fetch('/admin/api/charts/tenant-growth', { headers }).then(r => r.json());
            new Chart(document.getElementById('tenantGrowthChart'), {
                type: 'line',
                data: {
                    labels: growth.labels,
                    datasets: [{
                        label: 'Tenants',
                        data: growth.data,
                        borderColor: '#2d6a1e',
                        backgroundColor: 'rgba(45, 106, 30, 0.08)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#2d6a1e',
                        pointHoverRadius: 6,
                    }]
                },
                options: {
                    ...chartDefaults,
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#e4e4e7' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        } catch(e) {}

        try {
            const plans = await fetch('/admin/api/charts/plan-distribution', { headers }).then(r => r.json());
            const hasData = plans.data && plans.data.some(v => v > 0);
            new Chart(document.getElementById('planChart'), {
                type: 'doughnut',
                data: {
                    labels: hasData ? plans.labels : ['Sem dados'],
                    datasets: [{
                        data: hasData ? plans.data : [1],
                        backgroundColor: hasData ? ['#2d6a1e', '#8b5cf6', '#f59e0b', '#10b981'] : ['#e4e4e7'],
                        borderWidth: 0,
                    }]
                },
                options: {
                    ...chartDefaults,
                    cutout: '60%',
                    plugins: { legend: { display: true, position: 'bottom', labels: { boxWidth: 10, padding: 12, font: { size: 11 } } } }
                }
            });
        } catch(e) {}

        try {
            const inv = await fetch('/admin/api/charts/invoice-status', { headers }).then(r => r.json());
            const hasInvData = inv.data && inv.data.some(v => v > 0);
            new Chart(document.getElementById('invoiceStatusChart'), {
                type: 'doughnut',
                data: {
                    labels: hasInvData ? inv.labels : ['Sem faturas'],
                    datasets: [{
                        data: hasInvData ? inv.data : [1],
                        backgroundColor: hasInvData ? inv.colors : ['#e4e4e7'],
                        borderWidth: 0,
                    }]
                },
                options: {
                    ...chartDefaults,
                    cutout: '60%',
                    plugins: { legend: { display: true, position: 'bottom', labels: { boxWidth: 10, padding: 12, font: { size: 11 } } } }
                }
            });
        } catch(e) {}
    }

    loadCharts();
</script>
@endpush
