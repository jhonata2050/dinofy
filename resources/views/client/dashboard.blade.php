@extends('client.layouts.app')
@section('title', 'Dashboard')
@section('content')

<div class="mb-8">
    <h1 class="text-xl font-semibold text-zinc-900">Olá, {{ auth('tenant')->user()->name }}</h1>
    <p class="text-zinc-500 text-sm mt-0.5">{{ $tenant->subdomain }}.{{ config('master.base_domain') }} · Plano {{ $tenant->plan->name }}
        <span id="last-update" class="ml-2 text-xs text-zinc-400"></span>
    </p>
</div>

{{-- KPIs --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="rounded-xl border border-zinc-50 bg-zinc-50 p-5">
        <p class="text-xs font-semibold text-zinc-500 uppercase">Plano Atual</p>
        <p class="text-2xl font-bold text-zinc-900 mt-1">{{ $tenant->plan->name }}</p>
        <p class="text-sm text-zinc-500 mt-0.5">R$ {{ $tenant->plan->priceFormatted() }}/mês</p>
    </div>
    <div class="rounded-xl border border-zinc-50 bg-zinc-50 p-5">
        <p class="text-xs font-semibold text-zinc-500 uppercase">Status</p>
        <p id="kpi-status" class="text-2xl font-bold mt-1 {{ $tenant->status === 'active' ? 'text-green-700' : 'text-red-600' }}">{{ ucfirst($tenant->status) }}</p>
        <p id="kpi-running" class="text-sm text-zinc-500 mt-0.5">Verificando...</p>
    </div>
    <div class="rounded-xl border border-zinc-50 bg-zinc-50 p-5">
        <p class="text-xs font-semibold text-zinc-500 uppercase">Faturas Pendentes</p>
        <p class="text-2xl font-bold text-zinc-900 mt-1">{{ $pendingInvoices }}</p>
        @if($overdueInvoices > 0)
            <p class="text-sm text-red-600 font-medium mt-0.5">{{ $overdueInvoices }} vencida(s)</p>
        @else
            <p class="text-sm text-zinc-500 mt-0.5">Tudo em dia</p>
        @endif
    </div>
    <div class="rounded-xl border border-zinc-50 bg-zinc-50 p-5">
        <p class="text-xs font-semibold text-zinc-500 uppercase">Tickets Abertos</p>
        <p class="text-2xl font-bold text-zinc-900 mt-1">{{ $openTickets }}</p>
        <a href="{{ route('client.tickets.create') }}" class="text-sm font-medium mt-0.5 hover:underline" style="color: var(--color-primary);">Abrir ticket</a>
    </div>
</div>

{{-- Gráficos de Recursos em Tempo Real --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
    {{-- CPU --}}
    <div class="rounded-xl border border-zinc-50 bg-zinc-50 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-zinc-700">CPU</h2>
            <span id="cpu-current" class="text-sm font-mono text-zinc-500">—</span>
        </div>
        <div style="height: 180px;">
            <canvas id="cpuChart"></canvas>
        </div>
    </div>

    {{-- Memória --}}
    <div class="rounded-xl border border-zinc-50 bg-zinc-50 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-zinc-700">Memória</h2>
            <span id="mem-current" class="text-sm font-mono text-zinc-500">—</span>
        </div>
        <div style="height: 180px;">
            <canvas id="memChart"></canvas>
        </div>
    </div>
</div>

{{-- Disco + Containers --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
    {{-- Disco --}}
    <div class="rounded-xl border border-zinc-50 bg-zinc-50 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-zinc-700">Armazenamento</h2>
            <span id="disk-current" class="text-sm font-mono text-zinc-500">—</span>
        </div>
        <div style="height: 180px;">
            <canvas id="diskChart"></canvas>
        </div>
        <div id="disk-detail" class="flex gap-4 mt-3 text-xs text-zinc-400"></div>
    </div>

    {{-- Containers --}}
    <div class="rounded-xl border border-zinc-50 bg-zinc-50 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-zinc-700">Containers</h2>
            <span id="containers-status" class="text-xs font-medium px-2 py-0.5 rounded-full bg-zinc-200 text-zinc-600">—</span>
        </div>
        <div id="containers-list" class="space-y-3">
            <div class="text-center py-8 text-zinc-400 text-sm">Carregando...</div>
        </div>
    </div>
</div>

{{-- Quick Links --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <a href="{{ route('client.billing.index') }}" class="rounded-xl border border-zinc-50 bg-zinc-50 p-5 hover:bg-zinc-100 transition group">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-green-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-zinc-900 group-hover:underline">Ver Faturas</p>
                <p class="text-xs text-zinc-500">Histórico de pagamentos</p>
            </div>
        </div>
    </a>
    <a href="{{ route('client.plans.index') }}" class="rounded-xl border border-zinc-50 bg-zinc-50 p-5 hover:bg-zinc-100 transition group">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-zinc-900 group-hover:underline">Fazer Upgrade</p>
                <p class="text-xs text-zinc-500">Mais recursos para crescer</p>
            </div>
        </div>
    </a>
    <a href="{{ route('client.tickets.create') }}" class="rounded-xl border border-zinc-50 bg-zinc-50 p-5 hover:bg-zinc-100 transition group">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-zinc-900 group-hover:underline">Abrir Ticket</p>
                <p class="text-xs text-zinc-500">Precisa de ajuda?</p>
            </div>
        </div>
    </a>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
const MAX_POINTS = 30;
const REFRESH_MS = 60000;
const API_URL = '{{ route("client.api.resources") }}';
const PRIMARY = '#2d6a1e';
const PRIMARY_LIGHT = 'rgba(45,106,30,0.15)';
const BLUE = '#3b82f6';
const BLUE_LIGHT = 'rgba(59,130,246,0.15)';
const AMBER = '#f59e0b';
const AMBER_LIGHT = 'rgba(245,158,11,0.15)';

const commonOpts = {
    responsive: true,
    maintainAspectRatio: false,
    animation: { duration: 400 },
    interaction: { intersect: false, mode: 'index' },
    plugins: {
        legend: { display: false },
        tooltip: { backgroundColor: '#18181b', titleFont: { size: 11 }, bodyFont: { size: 11 }, padding: 8, cornerRadius: 8 }
    },
    scales: {
        x: { grid: { display: false }, ticks: { font: { size: 10 }, color: '#a1a1aa', maxRotation: 0, maxTicksLimit: 6 }, border: { display: false } },
        y: { min: 0, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { font: { size: 10 }, color: '#a1a1aa' }, border: { display: false } }
    }
};

const labels = [];
const cpuData = [];
const memData = [];

const cpuChart = new Chart(document.getElementById('cpuChart'), {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'CPU %',
            data: cpuData,
            borderColor: PRIMARY,
            backgroundColor: PRIMARY_LIGHT,
            borderWidth: 2,
            fill: true,
            tension: 0.3,
            pointRadius: 0,
            pointHitRadius: 10
        }]
    },
    options: {
        ...commonOpts,
        scales: {
            ...commonOpts.scales,
            y: { ...commonOpts.scales.y, max: {{ $tenant->plan->cpu_limit * 100 }},
                ticks: { ...commonOpts.scales.y.ticks, callback: v => v + '%' }
            }
        }
    }
});

const memChart = new Chart(document.getElementById('memChart'), {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Memória (MB)',
            data: memData,
            borderColor: BLUE,
            backgroundColor: BLUE_LIGHT,
            borderWidth: 2,
            fill: true,
            tension: 0.3,
            pointRadius: 0,
            pointHitRadius: 10
        }]
    },
    options: {
        ...commonOpts,
        scales: {
            ...commonOpts.scales,
            y: { ...commonOpts.scales.y,
                max: {{ (float) str_replace(['M','G'], ['', '000'], $tenant->plan->memory_limit) }},
                ticks: { ...commonOpts.scales.y.ticks, callback: v => v + ' MB' }
            }
        }
    }
});

const diskChart = new Chart(document.getElementById('diskChart'), {
    type: 'doughnut',
    data: {
        labels: ['App', 'Banco de Dados', 'Livre'],
        datasets: [{
            data: [0, 0, 100],
            backgroundColor: [PRIMARY, BLUE, '#e4e4e7'],
            borderWidth: 0,
            hoverOffset: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '70%',
        plugins: {
            legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 12, usePointStyle: true, pointStyleWidth: 8 } },
            tooltip: { backgroundColor: '#18181b', padding: 8, cornerRadius: 8 }
        }
    }
});

function formatBytes(bytes) {
    if (bytes >= 1073741824) return (bytes / 1073741824).toFixed(2) + ' GB';
    if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' MB';
    return (bytes / 1024).toFixed(0) + ' KB';
}

async function fetchResources() {
    try {
        const res = await fetch(API_URL);
        if (!res.ok) return;
        const d = await res.json();

        // Timeline
        if (labels.length >= MAX_POINTS) {
            labels.shift();
            cpuData.shift();
            memData.shift();
        }
        labels.push(d.timestamp);
        cpuData.push(d.cpu.used_percent);
        memData.push(d.memory.used_mb);

        cpuChart.update('none');
        memChart.update('none');

        // Current values
        document.getElementById('cpu-current').textContent = d.cpu.used_percent + '% / ' + d.cpu.limit_percent + '% (' + d.cpu.cores + ' cores)';
        document.getElementById('mem-current').textContent = d.memory.used_mb + ' MB / ' + d.memory.limit_mb + ' MB (' + d.memory.percent + '%)';

        // Disk
        const diskLimitBytes = d.disk.limit_bytes || 1;
        const appPct = (d.disk.app_bytes / diskLimitBytes) * 100;
        const dbPct = (d.disk.db_bytes / diskLimitBytes) * 100;
        const freePct = Math.max(0, 100 - appPct - dbPct);

        diskChart.data.datasets[0].data = [appPct.toFixed(1), dbPct.toFixed(1), freePct.toFixed(1)];
        diskChart.update('none');

        document.getElementById('disk-current').textContent = d.disk.used_formatted + ' / ' + d.disk.limit_formatted + ' (' + d.disk.percent + '%)';
        document.getElementById('disk-detail').innerHTML =
            '<span>App: ' + formatBytes(d.disk.app_bytes) + '</span>' +
            '<span>DB: ' + formatBytes(d.disk.db_bytes) + '</span>' +
            '<span>Livre: ' + formatBytes(Math.max(0, d.disk.limit_bytes - d.disk.used_bytes)) + '</span>';

        // Status
        const runEl = document.getElementById('kpi-running');
        const statusBadge = document.getElementById('containers-status');
        if (d.running) {
            runEl.textContent = 'Containers rodando';
            runEl.className = 'text-sm text-green-600 mt-0.5';
            statusBadge.textContent = d.containers.length + ' rodando';
            statusBadge.className = 'text-xs font-medium px-2 py-0.5 rounded-full bg-green-100 text-green-700';
        } else {
            runEl.textContent = 'Containers parados';
            runEl.className = 'text-sm text-red-600 mt-0.5';
            statusBadge.textContent = 'Parado';
            statusBadge.className = 'text-xs font-medium px-2 py-0.5 rounded-full bg-red-100 text-red-700';
        }

        // Containers list
        const list = document.getElementById('containers-list');
        if (d.containers.length > 0) {
            list.innerHTML = d.containers.map(c => {
                const cpuVal = parseFloat(c.cpu) || 0;
                const memVal = parseFloat(c.mem_percent) || 0;
                const cpuColor = cpuVal > 80 ? '#ef4444' : cpuVal > 50 ? '#f59e0b' : '#2d6a1e';
                const memColor = memVal > 80 ? '#ef4444' : memVal > 50 ? '#f59e0b' : '#3b82f6';
                return '<div class="bg-zinc-100 rounded-lg p-3">' +
                    '<div class="flex justify-between items-center mb-2">' +
                        '<span class="font-mono text-xs text-zinc-700">' + c.name + '</span>' +
                        '<span class="w-2 h-2 rounded-full bg-green-500"></span>' +
                    '</div>' +
                    '<div class="grid grid-cols-2 gap-2">' +
                        '<div><p class="text-[10px] text-zinc-400 mb-1">CPU ' + c.cpu + '</p>' +
                            '<div class="w-full bg-zinc-200 rounded-full h-1.5"><div class="h-1.5 rounded-full" style="width:' + Math.min(cpuVal, 100) + '%;background:' + cpuColor + '"></div></div></div>' +
                        '<div><p class="text-[10px] text-zinc-400 mb-1">RAM ' + c.mem_percent + '</p>' +
                            '<div class="w-full bg-zinc-200 rounded-full h-1.5"><div class="h-1.5 rounded-full" style="width:' + Math.min(memVal, 100) + '%;background:' + memColor + '"></div></div></div>' +
                    '</div>' +
                '</div>';
            }).join('');
        } else {
            list.innerHTML = '<div class="text-center py-8 text-zinc-400 text-sm">' +
                (d.running ? 'Dados indisponíveis' : 'Containers não estão rodando') + '</div>';
        }

        // Last update
        document.getElementById('last-update').textContent = 'Atualizado: ' + d.timestamp;

    } catch (e) {
        console.error('Erro ao buscar recursos:', e);
    }
}

fetchResources();
setInterval(fetchResources, REFRESH_MS);
</script>
@endpush
