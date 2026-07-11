@extends('layouts.app')
@section('title', 'Editar Fatura #' . $invoice->id)
@section('content')

<h1 class="text-xl font-semibold text-zinc-900 mb-6">Editar Fatura #{{ $invoice->id }}</h1>

<div class="rounded-xl border border-zinc-50 bg-zinc-50 p-6 max-w-2xl">
    <div class="mb-4 p-3 bg-zinc-100 rounded-lg text-sm text-zinc-600">
        Cliente: <strong>{{ $invoice->tenant->subdomain }}</strong> — {{ $invoice->tenant->name }}
    </div>

    <form method="POST" action="{{ route('admin.invoices.update', $invoice) }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Plano</label>
                <select name="plan_id" required class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm">
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}" {{ old('plan_id', $invoice->plan_id) == $plan->id ? 'selected' : '' }}>
                            {{ $plan->name }} — R$ {{ $plan->priceFormatted() }}/mês
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Status</label>
                <select name="status" required class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm">
                    <option value="pending" {{ old('status', $invoice->status) === 'pending' ? 'selected' : '' }}>Pendente</option>
                    <option value="paid" {{ old('status', $invoice->status) === 'paid' ? 'selected' : '' }}>Pago</option>
                    <option value="overdue" {{ old('status', $invoice->status) === 'overdue' ? 'selected' : '' }}>Vencido</option>
                    <option value="cancelled" {{ old('status', $invoice->status) === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-zinc-700 mb-1">Valor (centavos)</label>
            <div class="flex items-center gap-2">
                <input type="number" name="amount_cents" id="amountCents" value="{{ old('amount_cents', $invoice->amount_cents) }}" required min="1" class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
                <span class="text-sm text-zinc-500 whitespace-nowrap" id="amountPreview">R$ {{ $invoice->amountFormatted() }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Início do Período</label>
                <input type="date" name="period_start" value="{{ old('period_start', $invoice->period_start?->format('Y-m-d')) }}" required class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Fim do Período</label>
                <input type="date" name="period_end" value="{{ old('period_end', $invoice->period_end?->format('Y-m-d')) }}" required class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Vencimento</label>
                <input type="date" name="due_date" value="{{ old('due_date', $invoice->due_date->format('Y-m-d')) }}" required class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
            </div>
        </div>

        <div class="flex gap-3 pt-4">
            <button type="submit" class="px-6 py-2.5 text-white rounded-lg text-sm font-medium transition" style="background: var(--color-primary);">Salvar</button>
            <a href="{{ route('admin.invoices.show', $invoice) }}" class="px-6 py-2.5 bg-zinc-200 rounded-lg hover:bg-zinc-300 text-sm font-medium text-zinc-700 transition">Cancelar</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
    const amountCents = document.getElementById('amountCents');
    const amountPreview = document.getElementById('amountPreview');
    amountCents.addEventListener('input', function() {
        const v = parseInt(this.value) || 0;
        amountPreview.textContent = 'R$ ' + (v / 100).toFixed(2).replace('.', ',');
    });
</script>
@endpush

@endsection
