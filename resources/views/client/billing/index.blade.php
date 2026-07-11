@extends('client.layouts.app')
@section('title', 'Faturas')
@section('content')

<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-xl font-semibold text-zinc-900">Faturas</h1>
        <p class="text-zinc-500 text-sm mt-0.5">Historico de cobrancas e pagamentos</p>
    </div>
</div>

{{-- Resumo --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="rounded-xl border border-zinc-100 bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold text-zinc-400 uppercase">Plano Atual</p>
        <p class="text-lg font-bold text-zinc-900 mt-1">{{ $tenant->plan->name }}</p>
        <p class="text-sm text-zinc-500">R$ {{ $tenant->plan->priceFormatted() }}/mes</p>
    </div>
    <div class="rounded-xl border border-zinc-100 bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold text-zinc-400 uppercase">Proxima Cobranca</p>
        <p class="text-lg font-bold text-zinc-900 mt-1">{{ $tenant->next_billing_date?->format('d/m/Y') ?? '—' }}</p>
        @if($tenant->next_billing_date && $tenant->next_billing_date->diffInDays(now()) <= 5 && $tenant->next_billing_date->isFuture())
            <p class="text-xs text-amber-600 font-medium mt-0.5">Em {{ $tenant->next_billing_date->diffInDays(now()) }} dias</p>
        @endif
    </div>
    <div class="rounded-xl border border-zinc-100 bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold text-zinc-400 uppercase">Status da Conta</p>
        <div class="flex items-center gap-2 mt-1">
            @if($tenant->status === 'active')
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <p class="text-lg font-bold text-emerald-600">Ativa</p>
            @elseif($tenant->status === 'suspended')
                <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                <p class="text-lg font-bold text-red-600">Suspensa</p>
            @elseif($tenant->status === 'pending_payment')
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                <p class="text-lg font-bold text-amber-600">Aguardando pagamento</p>
            @else
                <span class="w-2.5 h-2.5 rounded-full bg-zinc-400"></span>
                <p class="text-lg font-bold text-zinc-600">{{ ucfirst($tenant->status) }}</p>
            @endif
        </div>
    </div>
</div>

{{-- Alerta de suspensao --}}
@if($tenant->status === 'suspended')
    <div class="rounded-xl border border-red-200 bg-red-50 p-4 mb-6 flex items-start gap-3">
        <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <div>
            <p class="text-red-700 font-medium text-sm">Conta suspensa por falta de pagamento</p>
            <p class="text-red-600 text-xs mt-0.5">Efetue o pagamento de uma fatura pendente para reativar sua conta.</p>
        </div>
    </div>
@endif

{{-- Faturas --}}
<div class="rounded-xl border border-zinc-100 bg-white overflow-hidden shadow-sm">
    @if($invoices->isEmpty())
        <div class="p-12 text-center">
            <svg class="w-12 h-12 text-zinc-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
            <p class="text-zinc-500 font-medium">Nenhuma fatura encontrada</p>
            <p class="text-zinc-400 text-sm mt-1">Suas faturas aparecerão aqui quando geradas.</p>
        </div>
    @else
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-zinc-100 text-left text-xs font-semibold text-zinc-400 uppercase">
                    <th class="px-6 py-3">Fatura</th>
                    <th class="px-6 py-3">Vencimento</th>
                    <th class="px-6 py-3">Valor</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-50">
                @foreach($invoices as $invoice)
                    <tr class="hover:bg-zinc-50 transition">
                        <td class="px-6 py-4">
                            <span class="text-zinc-700 font-medium">#{{ $invoice->id }}</span>
                            <span class="text-zinc-400 text-xs ml-1">{{ $invoice->period_start?->format('d/m') }} - {{ $invoice->period_end?->format('d/m/Y') }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-zinc-700">{{ $invoice->due_date->format('d/m/Y') }}</span>
                            @if($invoice->status !== 'paid' && $invoice->due_date->isPast())
                                <span class="text-red-500 text-xs ml-1 font-medium">Vencida</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-semibold text-zinc-900">R$ {{ $invoice->amountFormatted() }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 text-xs font-medium rounded-full
                                {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-700' : '' }}
                                {{ $invoice->status === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}
                                {{ $invoice->status === 'overdue' ? 'bg-red-100 text-red-700' : '' }}
                                {{ $invoice->status === 'cancelled' ? 'bg-zinc-100 text-zinc-600' : '' }}
                            ">{{ $invoice->status === 'paid' ? 'Pago' : ($invoice->status === 'pending' ? 'Pendente' : ($invoice->status === 'overdue' ? 'Vencida' : ucfirst($invoice->status))) }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3 justify-end">
                                @if(in_array($invoice->status, ['pending', 'overdue']))
                                    <a href="{{ route('client.billing.show', $invoice) }}" class="px-3 py-1.5 text-xs font-medium text-white rounded-lg transition hover:opacity-90 flex items-center gap-1" style="background: var(--color-primary);">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        Pagar
                                    </a>
                                @else
                                    <a href="{{ route('client.billing.show', $invoice) }}" class="text-sm font-medium hover:underline" style="color: var(--color-primary);">Detalhes</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if($invoices->hasPages())
            <div class="px-6 py-4 border-t border-zinc-100">{{ $invoices->links() }}</div>
        @endif
    @endif
</div>

@endsection
