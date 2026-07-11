@extends('client.layouts.app')
@section('title', 'Ticket #' . $ticket->id)
@section('content')

<div class="flex items-center justify-between mb-8">
    <div class="flex items-center gap-3">
        <a href="{{ route('client.tickets.index') }}" class="text-zinc-400 hover:text-zinc-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-semibold text-zinc-900">{{ $ticket->subject }}</h1>
            <p class="text-zinc-500 text-sm">#{{ $ticket->id }} · {{ ucfirst($ticket->category) }} · {{ ucfirst($ticket->priority) }}</p>
        </div>
    </div>
    @php
        $sc = ['open'=>'bg-blue-100 text-blue-700','in_progress'=>'bg-amber-100 text-amber-700','waiting_client'=>'bg-purple-100 text-purple-700','resolved'=>'bg-green-100 text-green-700','closed'=>'bg-zinc-100 text-zinc-600'];
        $sl = ['open'=>'Aberto','in_progress'=>'Em andamento','waiting_client'=>'Aguardando você','resolved'=>'Resolvido','closed'=>'Fechado'];
    @endphp
    <span class="px-3 py-1 text-sm font-medium rounded-full {{ $sc[$ticket->status] ?? '' }}">{{ $sl[$ticket->status] ?? $ticket->status }}</span>
</div>

{{-- Mensagens --}}
<div class="space-y-4 mb-6">
    @foreach($ticket->messages as $msg)
        <div class="flex {{ $msg->isAdmin() ? 'justify-start' : 'justify-end' }}">
            <div class="max-w-[75%] rounded-xl p-4 {{ $msg->isAdmin() ? 'bg-zinc-50 border border-zinc-100' : 'bg-green-50 border border-green-100' }}">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold text-white {{ $msg->isAdmin() ? 'bg-zinc-500' : 'bg-green-600' }}">
                        {{ strtoupper(substr($msg->sender_name, 0, 1)) }}
                    </span>
                    <span class="text-sm font-medium text-zinc-900">{{ $msg->sender_name }}</span>
                    <span class="text-xs text-zinc-400">{{ $msg->created_at->format('d/m H:i') }}</span>
                    @if($msg->isAdmin())
                        <span class="px-1.5 py-0.5 text-[10px] font-bold rounded bg-zinc-200 text-zinc-600">SUPORTE</span>
                    @endif
                </div>
                <div class="text-sm text-zinc-700 whitespace-pre-wrap">{{ $msg->message }}</div>
            </div>
        </div>
    @endforeach
</div>

{{-- Responder --}}
@if($ticket->isOpen())
    <div class="rounded-xl border border-zinc-50 bg-zinc-50 p-6">
        <h3 class="text-sm font-semibold text-zinc-700 mb-3">Responder</h3>
        <form method="POST" action="{{ route('client.tickets.reply', $ticket) }}">
            @csrf
            <textarea name="message" rows="4" required
                class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent resize-y mb-3"
                placeholder="Escreva sua resposta..."></textarea>
            <button type="submit" class="px-5 py-2.5 text-white rounded-lg text-sm font-medium transition hover:opacity-90" style="background: var(--color-primary);">Enviar</button>
        </form>
    </div>
@else
    <div class="rounded-xl bg-zinc-50 p-6 text-center">
        <p class="text-zinc-500 text-sm">Este ticket está {{ $sl[$ticket->status] ?? $ticket->status }}.</p>
    </div>
@endif

@endsection
