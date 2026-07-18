@extends('client.layouts.app')
@section('title', 'Fatura #' . $invoice->id)
@section('content')

<div class="flex items-center gap-3 mb-8">
    <a href="{{ route('client.billing.index') }}" class="text-zinc-400 hover:text-zinc-600 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <div>
        <h1 class="text-xl font-semibold text-zinc-900">Fatura #{{ $invoice->id }}</h1>
        <p class="text-zinc-500 text-sm">{{ $invoice->period_start?->format('d/m/Y') }} - {{ $invoice->period_end?->format('d/m/Y') }}</p>
    </div>
</div>

{{-- Estado: Pago --}}
<div id="paidState" class="{{ $invoice->status === 'paid' ? '' : 'hidden' }}">
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-6 mb-6">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <h2 class="text-lg font-bold text-emerald-700">Pagamento Confirmado</h2>
                <p class="text-emerald-600 text-sm">Pago em {{ $invoice->paid_at?->format('d/m/Y \à\s H:i') ?? '—' }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Vencimento proximo --}}
@if($invoice->status !== 'paid' && $invoice->status !== 'cancelled' && $invoice->due_date->isPast())
    <div class="rounded-xl border border-red-200 bg-red-50 p-4 mb-6 flex items-center gap-3">
        <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <div>
            <p class="text-red-700 font-medium text-sm">Fatura vencida</p>
            <p class="text-red-600 text-xs">O vencimento era {{ $invoice->due_date->format('d/m/Y') }}. Efetue o pagamento para evitar a suspensao do servico.</p>
        </div>
    </div>
@elseif($invoice->status !== 'paid' && $invoice->status !== 'cancelled' && $invoice->due_date->diffInDays(now()) <= 3)
    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 mb-6 flex items-center gap-3">
        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div>
            <p class="text-amber-700 font-medium text-sm">Vencimento proximo</p>
            <p class="text-amber-600 text-xs">Esta fatura vence em {{ $invoice->due_date->format('d/m/Y') }}.</p>
        </div>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Detalhes --}}
    <div class="rounded-xl border border-zinc-100 bg-white p-6 shadow-sm">
        <h2 class="text-sm font-semibold text-zinc-700 mb-4">Detalhes da Fatura</h2>

        @if($invoice->items->count())
            <div class="mb-4 -mx-2">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-zinc-100">
                            <th class="text-left px-2 py-2 font-medium text-zinc-500 text-xs uppercase">Servico</th>
                            <th class="text-center px-2 py-2 font-medium text-zinc-500 text-xs uppercase w-12">Qtd</th>
                            <th class="text-right px-2 py-2 font-medium text-zinc-500 text-xs uppercase w-24">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $item)
                            <tr class="border-b border-zinc-50">
                                <td class="px-2 py-2 text-zinc-800">{{ $item->description }}</td>
                                <td class="px-2 py-2 text-center text-zinc-500">{{ $item->quantity }}</td>
                                <td class="px-2 py-2 text-right font-medium text-zinc-800">R$ {{ $item->totalFormatted() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-zinc-200">
                            <td colspan="2" class="px-2 py-2.5 text-right font-semibold text-zinc-700">Total:</td>
                            <td class="px-2 py-2.5 text-right font-bold text-zinc-900">R$ {{ $invoice->amountFormatted() }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <div class="flex justify-between mb-3">
                <span class="text-zinc-500">Valor</span>
                <span class="font-bold text-zinc-900 text-lg">R$ {{ $invoice->amountFormatted() }}</span>
            </div>
        @endif

        <dl class="space-y-3 text-sm">
            <div class="flex justify-between">
                <dt class="text-zinc-500">Plano</dt>
                <dd class="text-zinc-700 font-medium">{{ $invoice->plan->name ?? '—' }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-zinc-500">Vencimento</dt>
                <dd class="text-zinc-700">{{ $invoice->due_date->format('d/m/Y') }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-zinc-500">Periodo</dt>
                <dd class="text-zinc-700">{{ $invoice->period_start?->format('d/m') }} - {{ $invoice->period_end?->format('d/m/Y') }}</dd>
            </div>
            <div class="flex justify-between items-center">
                <dt class="text-zinc-500">Status</dt>
                <dd>
                    <span id="statusBadge" class="px-2.5 py-1 text-xs font-medium rounded-full
                        {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $invoice->status === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}
                        {{ $invoice->status === 'overdue' ? 'bg-red-100 text-red-700' : '' }}
                        {{ $invoice->status === 'cancelled' ? 'bg-zinc-100 text-zinc-600' : '' }}
                    ">{{ ucfirst($invoice->status) }}</span>
                </dd>
            </div>
            @if($invoice->paid_at)
                <div class="flex justify-between">
                    <dt class="text-zinc-500">Pago em</dt>
                    <dd class="text-green-700 font-medium">{{ $invoice->paid_at->format('d/m/Y H:i') }}</dd>
                </div>
            @endif
        </dl>
    </div>

    {{-- Pagamento PIX --}}
    <div id="pixPanel" class="{{ $invoice->status === 'paid' || $invoice->status === 'cancelled' ? 'hidden' : '' }}">
        <div class="rounded-xl border border-zinc-100 bg-white p-6 shadow-sm">
            <h2 class="text-sm font-semibold text-zinc-700 mb-4">Pagamento via PIX</h2>

            @if($invoice->pix_copy_paste)
                {{-- QR Code --}}
                @if($invoice->pix_qr_code)
                    <div class="text-center mb-4 p-4 bg-zinc-50 rounded-xl">
                        <p class="text-xs text-zinc-500 mb-3 font-medium">Escaneie com seu app bancario</p>
                        <img src="{{ str_starts_with($invoice->pix_qr_code, 'http') ? $invoice->pix_qr_code : 'data:image/png;base64,' . $invoice->pix_qr_code }}" alt="QR Code PIX" class="w-44 h-44 mx-auto rounded-lg border border-zinc-200">
                    </div>
                @endif

                {{-- Copia e Cola --}}
                <div class="mb-4">
                    <p class="text-xs text-zinc-500 mb-2 font-medium">Copie o codigo PIX:</p>
                    <div class="bg-zinc-50 rounded-lg p-3 font-mono text-xs break-all text-zinc-600 border border-zinc-200 max-h-20 overflow-y-auto">{{ $invoice->pix_copy_paste }}</div>
                    <button onclick="copyPix()" id="copyBtn"
                        class="mt-2 w-full py-2.5 text-white rounded-lg text-sm font-medium transition hover:opacity-90 flex items-center justify-center gap-2" style="background: var(--color-primary);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        <span id="copyText">Copiar Codigo PIX</span>
                    </button>
                </div>

                <div id="pollingIndicator" class="flex items-center gap-3 p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-700 text-sm">
                    <svg class="animate-spin h-4 w-4 text-amber-500 flex-shrink-0" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    Aguardando confirmacao do pagamento...
                </div>
            @else
                <div class="text-center py-6">
                    <div class="w-14 h-14 rounded-full mx-auto mb-3 flex items-center justify-center bg-zinc-100">
                        <svg class="w-7 h-7 text-zinc-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <p class="text-zinc-700 font-medium mb-1">Gerar cobranca PIX</p>
                    <p class="text-zinc-500 text-sm mb-4">Clique abaixo para gerar o codigo PIX desta fatura.</p>
                    <form method="POST" action="{{ route('client.billing.generate-pix', $invoice) }}" id="genPixForm">
                        @csrf
                        <button type="submit" id="genPixBtn" class="px-6 py-2.5 text-white rounded-lg text-sm font-medium transition hover:opacity-90 flex items-center justify-center gap-2 mx-auto" style="background: var(--color-primary);">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span id="genPixText">Gerar Cobranca PIX</span>
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    {{-- Ajuda --}}
    <div class="{{ $invoice->status === 'paid' || $invoice->status === 'cancelled' ? '' : 'lg:col-span-2' }}">
        <div class="rounded-xl border border-zinc-100 bg-white p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-zinc-700 mb-3">Precisa de ajuda?</h3>
            <p class="text-sm text-zinc-500 mb-3">Se tiver dificuldades com o pagamento ou duvidas sobre a fatura, abra um ticket de suporte.</p>
            <a href="{{ route('client.tickets.create') }}" class="inline-flex items-center gap-1.5 text-sm font-medium transition hover:opacity-80" style="color: var(--color-primary);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                Abrir ticket de suporte
            </a>
        </div>
    </div>
</div>

@if($invoice->status !== 'paid' && $invoice->status !== 'cancelled' && $invoice->pix_copy_paste)
@push('scripts')
<script>
function copyPix() {
    const code = '{{ $invoice->pix_copy_paste }}';
    navigator.clipboard.writeText(code).then(() => {
        const txt = document.getElementById('copyText');
        txt.textContent = 'Copiado!';
        setTimeout(() => txt.textContent = 'Copiar Codigo PIX', 2000);
    });
}

let polling;
function checkPayment() {
    fetch('{{ route("client.billing.check-payment", $invoice->id) }}')
        .then(r => r.json())
        .then(data => {
            if (data.paid) {
                clearInterval(polling);
                document.getElementById('paidState').classList.remove('hidden');
                document.getElementById('pixPanel').classList.add('hidden');
                document.getElementById('statusBadge').className = 'px-2.5 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700';
                document.getElementById('statusBadge').textContent = 'Paid';
            }
        })
        .catch(() => {});
}
polling = setInterval(checkPayment, 10000);
</script>
@endpush
@elseif($invoice->status !== 'paid' && $invoice->status !== 'cancelled' && !$invoice->pix_copy_paste)
@push('scripts')
<script>
const genForm = document.getElementById('genPixForm');
if (genForm) {
    genForm.addEventListener('submit', function() {
        const btn = document.getElementById('genPixBtn');
        btn.disabled = true;
        document.getElementById('genPixText').textContent = 'Gerando...';
        btn.insertAdjacentHTML('afterbegin', '<svg class="animate-spin h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>');
    });
}
</script>
@endpush
@endif

@endsection
