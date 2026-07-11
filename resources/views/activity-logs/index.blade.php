@extends('layouts.app')
@section('title', 'Logs de Atividade')
@section('content')

<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-xl font-semibold text-zinc-900">Logs de Atividade</h1>
        <p class="text-zinc-500 text-sm mt-0.5">Histórico completo de ações realizadas no sistema</p>
    </div>
</div>

{{-- Filters --}}
<div class="rounded-xl border border-zinc-50 bg-zinc-50 p-4 mb-6">
    <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="flex flex-wrap items-end gap-4">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-medium text-zinc-500 mb-1">Buscar</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por descrição, tenant..."
                class="w-full px-3 py-2 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
        </div>
        <div class="w-48">
            <label class="block text-xs font-medium text-zinc-500 mb-1">Tipo de Ação</label>
            <select name="action" class="w-full px-3 py-2 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
                <option value="">Todas</option>
                @foreach($actionTypes as $action)
                    <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $action)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 text-white" style="background: var(--color-primary); rounded-lg text-sm hover:opacity-90 transition">Filtrar</button>
            <a href="{{ route('admin.activity-logs.index') }}" class="px-4 py-2 bg-zinc-100 text-zinc-600 rounded-lg text-sm hover:bg-zinc-200 transition">Limpar</a>
        </div>
    </form>
</div>

{{-- Logs --}}
<div class="rounded-xl border border-zinc-50 bg-zinc-50 overflow-hidden">
    @if($logs->isEmpty())
        <div class="p-12 text-center">
            <svg class="w-12 h-12 text-zinc-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <p class="text-zinc-500">Nenhum log encontrado</p>
        </div>
    @else
        <div class="divide-y divide-zinc-100">
            @foreach($logs as $log)
                @php
                    $colors = [
                        'tenant_created' => 'bg-green-100 text-green-700',
                        'tenant_suspended' => 'bg-red-100 text-red-700',
                        'tenant_activated' => 'bg-blue-100 text-blue-700',
                        'tenant_terminated' => 'bg-zinc-100 text-zinc-700',
                        'plan_changed' => 'bg-purple-100 text-purple-700',
                        'invoice_created' => 'bg-amber-100 text-amber-700',
                        'invoice_paid' => 'bg-green-100 text-green-700',
                        'invoice_overdue' => 'bg-red-100 text-red-700',
                        'domain_added' => 'bg-indigo-100 text-indigo-700',
                        'domain_verified' => 'bg-teal-100 text-teal-700',
                        'settings_updated' => 'bg-zinc-100 text-zinc-600',
                    ];
                    $badgeClass = $colors[$log->action] ?? 'bg-zinc-100 text-zinc-600';
                @endphp
                <div class="flex items-start gap-4 px-6 py-4 hover:bg-zinc-100 transition">
                    <div class="mt-0.5">
                        @if(str_contains($log->action, 'created') || str_contains($log->action, 'activated') || str_contains($log->action, 'paid') || str_contains($log->action, 'verified'))
                            <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        @elseif(str_contains($log->action, 'suspended') || str_contains($log->action, 'overdue') || str_contains($log->action, 'terminated'))
                            <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                        @else
                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $badgeClass }}">{{ ucfirst(str_replace('_', ' ', $log->action)) }}</span>
                            @if($log->tenant)
                                <span class="text-xs text-zinc-400">·</span>
                                <span class="text-sm font-medium text-zinc-700">{{ $log->tenant->name }}</span>
                            @endif
                        </div>
                        <p class="text-sm text-zinc-600">{{ $log->description }}</p>
                        @if($log->metadata)
                            <details class="mt-1">
                                <summary class="text-xs text-zinc-400 cursor-pointer hover:text-zinc-600">Detalhes</summary>
                                <pre class="mt-1 text-xs bg-zinc-100 p-2 rounded overflow-x-auto">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </details>
                        @endif
                    </div>
                    <div class="text-xs text-zinc-400 whitespace-nowrap">
                        {{ $log->created_at->diffForHumans() }}
                    </div>
                </div>
            @endforeach
        </div>

        @if($logs->hasPages())
            <div class="px-6 py-4 border-t border-zinc-200">
                {{ $logs->withQueryString()->links() }}
            </div>
        @endif
    @endif
</div>

@endsection
