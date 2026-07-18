@extends('layouts.app')
@section('title', 'Fatura #' . $invoice->id)
@section('content')

<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.invoices.index') }}" class="text-zinc-400 hover:text-zinc-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-semibold text-zinc-900">Fatura #{{ $invoice->id }}</h1>
            <p class="text-zinc-500 text-sm">{{ $invoice->tenant->subdomain }} — {{ $invoice->plan->name }}</p>
        </div>
    </div>
    <div class="flex items-center gap-2">
        @if($invoice->status !== 'paid')
            <form method="POST" action="{{ route('admin.invoices.confirm-payment', $invoice) }}" onsubmit="return confirm('Confirmar pagamento manual desta fatura?')">
                @csrf
                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition">Confirmar Pagamento</button>
            </form>
            <form method="POST" action="{{ route('admin.invoices.generate-charge', $invoice) }}">
                @csrf
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">{{ $invoice->pix_copy_paste ? 'Regerar PIX' : 'Gerar PIX' }}</button>
            </form>
        @endif
        <a href="{{ route('admin.invoices.edit', $invoice) }}" class="px-4 py-2 bg-zinc-200 rounded-lg hover:bg-zinc-300 text-sm font-medium text-zinc-700 transition">Editar</a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Detalhes --}}
    <div class="rounded-xl border border-zinc-50 bg-zinc-50 p-6">
        <h2 class="text-sm font-semibold text-zinc-700 mb-4">Detalhes da Fatura</h2>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between">
                <dt class="text-zinc-500">Status</dt>
                <dd>
                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                        {{ $invoice->status === 'paid' ? 'bg-emerald-100 text-emerald-700' : '' }}
                        {{ $invoice->status === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}
                        {{ $invoice->status === 'overdue' ? 'bg-red-100 text-red-700' : '' }}
                        {{ $invoice->status === 'cancelled' ? 'bg-zinc-200 text-zinc-600' : '' }}
                    ">{{ ucfirst($invoice->status) }}</span>
                </dd>
            </div>
            <div class="flex justify-between"><dt class="text-zinc-500">Valor</dt><dd class="font-bold text-zinc-900 text-lg">R$ {{ $invoice->amountFormatted() }}</dd></div>
            <div class="flex justify-between"><dt class="text-zinc-500">Vencimento</dt><dd class="text-zinc-700">{{ $invoice->due_date->format('d/m/Y') }}</dd></div>
            <div class="flex justify-between"><dt class="text-zinc-500">Período</dt><dd class="text-zinc-700">{{ $invoice->period_start?->format('d/m/Y') }} — {{ $invoice->period_end?->format('d/m/Y') }}</dd></div>
            @if($invoice->paid_at)
                <div class="flex justify-between"><dt class="text-zinc-500">Pago em</dt><dd class="text-emerald-700 font-medium">{{ $invoice->paid_at->format('d/m/Y H:i') }}</dd></div>
            @endif
            @if($invoice->gateway_charge_id)
                <div class="flex justify-between"><dt class="text-zinc-500">ID Gateway</dt><dd class="font-mono text-xs text-zinc-600">{{ $invoice->gateway_charge_id }}</dd></div>
            @endif
        </dl>
    </div>

    {{-- Cliente --}}
    <div class="rounded-xl border border-zinc-50 bg-zinc-50 p-6">
        <h2 class="text-sm font-semibold text-zinc-700 mb-4">Cliente</h2>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between"><dt class="text-zinc-500">Subdomínio</dt><dd><a href="{{ route('admin.tenants.show', $invoice->tenant) }}" class="font-medium hover:underline" style="color: var(--color-primary);">{{ $invoice->tenant->subdomain }}</a></dd></div>
            <div class="flex justify-between"><dt class="text-zinc-500">Nome</dt><dd class="text-zinc-700">{{ $invoice->tenant->name }}</dd></div>
            <div class="flex justify-between"><dt class="text-zinc-500">E-mail</dt><dd class="text-zinc-700">{{ $invoice->tenant->email }}</dd></div>
            <div class="flex justify-between"><dt class="text-zinc-500">CPF/CNPJ</dt><dd class="text-zinc-700">{{ $invoice->tenant->document }}</dd></div>
            <div class="flex justify-between"><dt class="text-zinc-500">Plano</dt><dd class="text-zinc-700">{{ $invoice->plan->name }} — R$ {{ $invoice->plan->priceFormatted() }}/mês</dd></div>
        </dl>
    </div>
</div>

{{-- Itens --}}
@if($invoice->items->count())
<div class="mt-6 rounded-xl border border-zinc-50 bg-zinc-50 p-6">
    <h2 class="text-sm font-semibold text-zinc-700 mb-4">Itens da Fatura</h2>
    <div class="bg-white rounded-lg border border-zinc-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-zinc-100 text-zinc-600">
                <tr>
                    <th class="text-left px-4 py-2.5 font-medium">Descricao</th>
                    <th class="text-center px-4 py-2.5 font-medium w-20">Qtd</th>
                    <th class="text-right px-4 py-2.5 font-medium w-32">Valor Unit.</th>
                    <th class="text-right px-4 py-2.5 font-medium w-28">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                    <tr class="border-t border-zinc-100">
                        <td class="px-4 py-2.5 text-zinc-800">{{ $item->description }}</td>
                        <td class="px-4 py-2.5 text-center text-zinc-600">{{ $item->quantity }}</td>
                        <td class="px-4 py-2.5 text-right text-zinc-600">R$ {{ $item->unitPriceFormatted() }}</td>
                        <td class="px-4 py-2.5 text-right font-medium text-zinc-800">R$ {{ $item->totalFormatted() }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-zinc-50 border-t border-zinc-200">
                <tr>
                    <td colspan="3" class="px-4 py-3 text-right font-semibold text-zinc-700">Total:</td>
                    <td class="px-4 py-3 text-right font-bold text-zinc-900 text-lg">R$ {{ $invoice->amountFormatted() }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endif

{{-- PIX --}}
@if($invoice->pix_copy_paste || $invoice->pix_qr_code)
<div class="mt-6 rounded-xl border border-zinc-50 bg-zinc-50 p-6">
    <h2 class="text-sm font-semibold text-zinc-700 mb-4">Dados PIX</h2>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @if($invoice->pix_copy_paste)
            <div>
                <p class="text-xs text-zinc-500 mb-2">Código Copia e Cola</p>
                <div class="bg-zinc-100 rounded-lg p-3 font-mono text-xs break-all text-zinc-700">{{ $invoice->pix_copy_paste }}</div>
                <button onclick="navigator.clipboard.writeText('{{ $invoice->pix_copy_paste }}').then(() => { this.textContent = 'Copiado!'; setTimeout(() => this.textContent = 'Copiar', 2000); })"
                    class="mt-2 px-4 py-2 bg-zinc-200 rounded-lg hover:bg-zinc-300 text-sm font-medium text-zinc-700 transition">Copiar</button>
            </div>
        @endif
        @if($invoice->pix_qr_code)
            <div>
                <p class="text-xs text-zinc-500 mb-2">QR Code</p>
                <img src="{{ str_starts_with($invoice->pix_qr_code, 'http') ? $invoice->pix_qr_code : 'data:image/png;base64,' . $invoice->pix_qr_code }}" alt="QR Code PIX" class="w-48 h-48 rounded-lg border border-zinc-200">
            </div>
        @endif
    </div>
</div>
@endif

@endsection
