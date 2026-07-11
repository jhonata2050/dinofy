@extends('layouts.app')
@section('title', 'Tickets')
@section('content')

<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-xl font-semibold text-zinc-900">Tickets de Suporte</h1>
        <p class="text-zinc-500 text-sm mt-0.5">Gerencie os tickets dos clientes</p>
    </div>
</div>

{{-- Filtros --}}
<div class="rounded-xl border border-zinc-50 bg-zinc-50 p-4 mb-6">
    <form method="GET" action="{{ route('admin.tickets.index') }}" class="flex flex-wrap items-end gap-4">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-medium text-zinc-500 mb-1">Buscar</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Assunto, cliente..."
                class="w-full px-3 py-2 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
        </div>
        <div class="w-48">
            <label class="block text-xs font-medium text-zinc-500 mb-1">Status</label>
            <select name="status" class="w-full px-3 py-2 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
                <option value="">Todos</option>
                <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Aberto</option>
                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>Em andamento</option>
                <option value="waiting_client" {{ request('status') === 'waiting_client' ? 'selected' : '' }}>Aguardando cliente</option>
                <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolvido</option>
                <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Fechado</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 text-white rounded-lg text-sm transition hover:opacity-90" style="background: var(--color-primary);">Filtrar</button>
            <a href="{{ route('admin.tickets.index') }}" class="px-4 py-2 bg-zinc-100 text-zinc-600 rounded-lg text-sm hover:bg-zinc-200 transition">Limpar</a>
        </div>
    </form>
</div>

<div class="rounded-xl border border-zinc-50 bg-zinc-50 overflow-hidden">
    @if($tickets->isEmpty())
        <div class="p-12 text-center">
            <p class="text-zinc-500">Nenhum ticket encontrado</p>
        </div>
    @else
        <div class="divide-y divide-zinc-100">
            @foreach($tickets as $ticket)
                @php
                    $sc = ['open'=>'bg-blue-100 text-blue-700','in_progress'=>'bg-amber-100 text-amber-700','waiting_client'=>'bg-purple-100 text-purple-700','resolved'=>'bg-green-100 text-green-700','closed'=>'bg-zinc-100 text-zinc-600'];
                    $sl = ['open'=>'Aberto','in_progress'=>'Em andamento','waiting_client'=>'Aguardando cliente','resolved'=>'Resolvido','closed'=>'Fechado'];
                    $pc = ['low'=>'text-zinc-500','medium'=>'text-amber-600','high'=>'text-red-600'];
                @endphp
                <a href="{{ route('admin.tickets.show', $ticket) }}" class="flex items-center justify-between px-6 py-4 hover:bg-zinc-100 transition">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-sm font-medium text-zinc-900">{{ $ticket->subject }}</span>
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $sc[$ticket->status] ?? '' }}">{{ $sl[$ticket->status] ?? $ticket->status }}</span>
                            <span class="text-xs font-medium {{ $pc[$ticket->priority] ?? '' }}">{{ ucfirst($ticket->priority) }}</span>
                        </div>
                        <p class="text-xs text-zinc-500">
                            #{{ $ticket->id }} · {{ $ticket->tenant->name ?? '—' }} ({{ $ticket->tenant->subdomain ?? '—' }})
                            · {{ $ticket->created_at->diffForHumans() }}
                        </p>
                    </div>
                    <svg class="w-5 h-5 text-zinc-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
            @endforeach
        </div>

        @if($tickets->hasPages())
            <div class="px-6 py-4 border-t border-zinc-200">{{ $tickets->withQueryString()->links() }}</div>
        @endif
    @endif
</div>

@endsection
