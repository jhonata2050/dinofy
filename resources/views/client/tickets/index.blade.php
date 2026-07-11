@extends('client.layouts.app')
@section('title', 'Suporte')
@section('content')

<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-xl font-semibold text-zinc-900">Suporte</h1>
        <p class="text-zinc-500 text-sm mt-0.5">Seus tickets de atendimento</p>
    </div>
    <a href="{{ route('client.tickets.create') }}" class="px-5 py-2.5 text-white rounded-lg text-sm font-medium transition hover:opacity-90" style="background: var(--color-primary);">Novo Ticket</a>
</div>

<div class="rounded-xl border border-zinc-50 bg-zinc-50 overflow-hidden">
    @if($tickets->isEmpty())
        <div class="p-12 text-center">
            <svg class="w-12 h-12 text-zinc-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
            <p class="text-zinc-500 mb-3">Nenhum ticket</p>
            <a href="{{ route('client.tickets.create') }}" class="text-sm font-medium hover:underline" style="color: var(--color-primary);">Abrir primeiro ticket</a>
        </div>
    @else
        <div class="divide-y divide-zinc-100">
            @foreach($tickets as $ticket)
                @php
                    $statusColors = [
                        'open' => 'bg-blue-100 text-blue-700',
                        'in_progress' => 'bg-amber-100 text-amber-700',
                        'waiting_client' => 'bg-purple-100 text-purple-700',
                        'resolved' => 'bg-green-100 text-green-700',
                        'closed' => 'bg-zinc-100 text-zinc-600',
                    ];
                    $statusLabels = [
                        'open' => 'Aberto',
                        'in_progress' => 'Em andamento',
                        'waiting_client' => 'Aguardando você',
                        'resolved' => 'Resolvido',
                        'closed' => 'Fechado',
                    ];
                    $priorityColors = ['low' => 'text-zinc-500', 'medium' => 'text-amber-600', 'high' => 'text-red-600'];
                @endphp
                <a href="{{ route('client.tickets.show', $ticket) }}" class="flex items-center justify-between px-6 py-4 hover:bg-zinc-100 transition">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-sm font-medium text-zinc-900">{{ $ticket->subject }}</span>
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $statusColors[$ticket->status] ?? 'bg-zinc-100 text-zinc-600' }}">{{ $statusLabels[$ticket->status] ?? $ticket->status }}</span>
                        </div>
                        <p class="text-xs text-zinc-500">
                            #{{ $ticket->id }} · {{ ucfirst($ticket->category) }} · <span class="{{ $priorityColors[$ticket->priority] ?? '' }}">{{ ucfirst($ticket->priority) }}</span>
                            @if($ticket->latestMessage) · Última msg: {{ $ticket->latestMessage->created_at->diffForHumans() }} @endif
                        </p>
                    </div>
                    <svg class="w-5 h-5 text-zinc-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
            @endforeach
        </div>

        @if($tickets->hasPages())
            <div class="px-6 py-4 border-t border-zinc-200">{{ $tickets->links() }}</div>
        @endif
    @endif
</div>

@endsection
