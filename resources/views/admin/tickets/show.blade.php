@extends('layouts.app')
@section('title', 'Ticket #' . $ticket->id)
@section('content')

<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.tickets.index') }}" class="text-zinc-400 hover:text-zinc-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-semibold text-zinc-900">{{ $ticket->subject }}</h1>
            <p class="text-zinc-500 text-sm">#{{ $ticket->id }} · {{ $ticket->tenant->name }} ({{ $ticket->tenant->subdomain }}) · {{ $ticket->tenant->plan->name ?? '' }}</p>
        </div>
    </div>

    {{-- Status --}}
    <form method="POST" action="{{ route('admin.tickets.status', $ticket) }}" class="flex items-center gap-2">
        @csrf @method('PATCH')
        <select name="status" class="px-3 py-1.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
            @foreach(['open'=>'Aberto','in_progress'=>'Em andamento','waiting_client'=>'Aguardando cliente','resolved'=>'Resolvido','closed'=>'Fechado'] as $val => $label)
                <option value="{{ $val }}" {{ $ticket->status === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-3 py-1.5 text-white rounded-lg text-sm font-medium transition hover:opacity-90" style="background: var(--color-primary);">Atualizar</button>
    </form>
</div>

{{-- Mensagens --}}
<div class="space-y-4 mb-6">
    @foreach($ticket->messages as $msg)
        <div class="flex {{ $msg->isAdmin() ? 'justify-end' : 'justify-start' }}">
            <div class="max-w-[75%] rounded-xl p-4 {{ $msg->isAdmin() ? 'bg-green-50 border border-green-100' : 'bg-zinc-50 border border-zinc-100' }}">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold text-white {{ $msg->isAdmin() ? 'bg-green-600' : 'bg-zinc-500' }}">
                        {{ strtoupper(substr($msg->sender_name, 0, 1)) }}
                    </span>
                    <span class="text-sm font-medium text-zinc-900">{{ $msg->sender_name }}</span>
                    <span class="text-xs text-zinc-400">{{ $msg->created_at->format('d/m H:i') }}</span>
                    @if($msg->isAdmin())
                        <span class="px-1.5 py-0.5 text-[10px] font-bold rounded bg-green-200 text-green-700">ADMIN</span>
                    @else
                        <span class="px-1.5 py-0.5 text-[10px] font-bold rounded bg-zinc-200 text-zinc-600">CLIENTE</span>
                    @endif
                </div>
                <div class="text-sm text-zinc-700 whitespace-pre-wrap">{{ $msg->message }}</div>
            </div>
        </div>
    @endforeach
</div>

{{-- Responder --}}
<div class="rounded-xl border border-zinc-50 bg-zinc-50 p-6">
    <h3 class="text-sm font-semibold text-zinc-700 mb-3">Responder</h3>
    <form method="POST" action="{{ route('admin.tickets.reply', $ticket) }}">
        @csrf
        <textarea name="message" rows="4" required
            class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent resize-y mb-3"
            placeholder="Responda ao cliente..."></textarea>
        <div class="flex gap-2">
            <button type="submit" class="px-5 py-2.5 text-white rounded-lg text-sm font-medium transition hover:opacity-90" style="background: var(--color-primary);">Enviar Resposta</button>
        </div>
    </form>
</div>

@endsection
