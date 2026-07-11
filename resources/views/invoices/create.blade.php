@extends('layouts.app')
@section('title', 'Nova Fatura')
@section('content')

<h1 class="text-xl font-semibold text-zinc-900 mb-6">Nova Fatura</h1>

<div class="rounded-xl border border-zinc-50 bg-zinc-50 p-6 max-w-2xl">
    <form method="POST" action="{{ route('admin.invoices.store') }}" class="space-y-4">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Cliente</label>
                <select name="tenant_id" required class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm" id="tenantSelect">
                    <option value="">Selecione...</option>
                    @foreach($tenants as $tenant)
                        <option value="{{ $tenant->id }}"
                            data-plan="{{ $tenant->plan_id }}"
                            data-price="{{ $tenant->plan->price_cents }}"
                            {{ (old('tenant_id', $selectedTenant) == $tenant->id) ? 'selected' : '' }}>
                            {{ $tenant->subdomain }} — {{ $tenant->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Plano</label>
                <select name="plan_id" required class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm" id="planSelect">
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}" data-price="{{ $plan->price_cents }}" {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
                            {{ $plan->name }} — R$ {{ $plan->priceFormatted() }}/mês
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-zinc-700 mb-1">Valor (centavos)</label>
            <div class="flex items-center gap-2">
                <input type="number" name="amount_cents" id="amountCents" value="{{ old('amount_cents') }}" required min="1" class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
                <span class="text-sm text-zinc-500 whitespace-nowrap" id="amountPreview">R$ 0,00</span>
            </div>
            <p class="text-xs text-zinc-400 mt-1">Ex: 9700 = R$ 97,00</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Início do Período</label>
                <input type="date" name="period_start" value="{{ old('period_start', now()->format('Y-m-d')) }}" required class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Fim do Período</label>
                <input type="date" name="period_end" value="{{ old('period_end', now()->addMonth()->format('Y-m-d')) }}" required class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Vencimento</label>
                <input type="date" name="due_date" value="{{ old('due_date', now()->addDays(3)->format('Y-m-d')) }}" required class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
            </div>
        </div>

        <div class="flex items-center gap-2 pt-2">
            <input type="checkbox" name="generate_pix" value="1" id="generatePix" checked class="rounded border-zinc-300 text-green-600 focus:ring-green-600">
            <label for="generatePix" class="text-sm text-zinc-700">Gerar cobrança PIX automaticamente</label>
        </div>

        <div class="flex gap-3 pt-4">
            <button type="submit" class="px-6 py-2.5 text-white rounded-lg text-sm font-medium transition" style="background: var(--color-primary);">Criar Fatura</button>
            <a href="{{ route('admin.invoices.index') }}" class="px-6 py-2.5 bg-zinc-200 rounded-lg hover:bg-zinc-300 text-sm font-medium text-zinc-700 transition">Cancelar</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
    const tenantSelect = document.getElementById('tenantSelect');
    const planSelect = document.getElementById('planSelect');
    const amountCents = document.getElementById('amountCents');
    const amountPreview = document.getElementById('amountPreview');

    tenantSelect.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (opt.dataset.plan) {
            planSelect.value = opt.dataset.plan;
            amountCents.value = opt.dataset.price;
            updatePreview();
        }
    });

    planSelect.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        amountCents.value = opt.dataset.price;
        updatePreview();
    });

    amountCents.addEventListener('input', updatePreview);

    function updatePreview() {
        const v = parseInt(amountCents.value) || 0;
        amountPreview.textContent = 'R$ ' + (v / 100).toFixed(2).replace('.', ',');
    }

    if (tenantSelect.value) tenantSelect.dispatchEvent(new Event('change'));
    updatePreview();
</script>
@endpush

@endsection
