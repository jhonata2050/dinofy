@extends('layouts.app')
@section('title', 'Nova Fatura')
@section('content')

<h1 class="text-xl font-semibold text-zinc-900 mb-6">Nova Fatura</h1>

<div class="rounded-xl border border-zinc-50 bg-zinc-50 p-6 max-w-3xl">
    <form method="POST" action="{{ route('admin.invoices.store') }}" class="space-y-4" id="invoiceForm">
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
                            data-plan-name="{{ $tenant->plan->name }}"
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
                        <option value="{{ $plan->id }}" data-price="{{ $plan->price_cents }}" data-name="{{ $plan->name }}" {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
                            {{ $plan->name }} — R$ {{ $plan->priceFormatted() }}/mes
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Inicio do Periodo</label>
                <input type="date" name="period_start" value="{{ old('period_start', now()->format('Y-m-d')) }}" required class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Fim do Periodo</label>
                <input type="date" name="period_end" value="{{ old('period_end', now()->addMonth()->format('Y-m-d')) }}" required class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Vencimento</label>
                <input type="date" name="due_date" value="{{ old('due_date', now()->addDays(3)->format('Y-m-d')) }}" required class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
            </div>
        </div>

        {{-- Itens da Fatura --}}
        <div>
            <div class="flex items-center justify-between mb-2">
                <label class="block text-sm font-medium text-zinc-700">Itens / Servicos</label>
                <button type="button" onclick="addItem()" class="text-xs font-medium px-2.5 py-1 rounded-lg border border-zinc-300 hover:bg-zinc-100 transition text-zinc-600">+ Adicionar item</button>
            </div>

            <div class="bg-white rounded-lg border border-zinc-200 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-zinc-100 text-zinc-600">
                        <tr>
                            <th class="text-left px-3 py-2 font-medium">Descricao</th>
                            <th class="text-center px-3 py-2 font-medium w-20">Qtd</th>
                            <th class="text-right px-3 py-2 font-medium w-32">Valor Unit. (R$)</th>
                            <th class="text-right px-3 py-2 font-medium w-28">Subtotal</th>
                            <th class="w-10"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                    </tbody>
                    <tfoot class="bg-zinc-50 border-t border-zinc-200">
                        <tr>
                            <td colspan="3" class="px-3 py-2.5 text-right font-semibold text-zinc-700">Total:</td>
                            <td class="px-3 py-2.5 text-right font-bold text-zinc-900" id="totalDisplay">R$ 0,00</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="flex items-center gap-2 pt-2">
            <input type="checkbox" name="generate_pix" value="1" id="generatePix" checked class="rounded border-zinc-300 text-green-600 focus:ring-green-600">
            <label for="generatePix" class="text-sm text-zinc-700">Gerar cobranca PIX automaticamente</label>
        </div>

        <div class="flex gap-3 pt-4">
            <button type="submit" class="px-6 py-2.5 text-white rounded-lg text-sm font-medium transition" style="background: var(--color-primary);">Criar Fatura</button>
            <a href="{{ route('admin.invoices.index') }}" class="px-6 py-2.5 bg-zinc-200 rounded-lg hover:bg-zinc-300 text-sm font-medium text-zinc-700 transition">Cancelar</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
let itemIndex = 0;

function addItem(desc = '', qty = 1, price = 0) {
    const tbody = document.getElementById('itemsBody');
    const tr = document.createElement('tr');
    tr.className = 'border-t border-zinc-100';
    tr.dataset.index = itemIndex;
    tr.innerHTML = `
        <td class="px-2 py-1.5">
            <input type="text" name="items[${itemIndex}][description]" value="${desc}" required placeholder="Ex: Plano Pro - Mensal"
                class="w-full px-2 py-1.5 border border-zinc-300 rounded text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
        </td>
        <td class="px-2 py-1.5">
            <input type="number" name="items[${itemIndex}][quantity]" value="${qty}" required min="1"
                class="w-full px-2 py-1.5 border border-zinc-300 rounded text-sm text-center focus:ring-2 focus:ring-green-600 focus:border-transparent"
                onchange="recalc()" oninput="recalc()">
        </td>
        <td class="px-2 py-1.5">
            <input type="number" name="items[${itemIndex}][unit_price_cents]" value="${price}" required min="0"
                class="w-full px-2 py-1.5 border border-zinc-300 rounded text-sm text-right focus:ring-2 focus:ring-green-600 focus:border-transparent"
                onchange="recalc()" oninput="recalc()" placeholder="centavos">
        </td>
        <td class="px-3 py-1.5 text-right text-sm text-zinc-700 font-medium subtotal">R$ 0,00</td>
        <td class="px-1 py-1.5 text-center">
            <button type="button" onclick="removeItem(this)" class="text-red-400 hover:text-red-600 transition p-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
    itemIndex++;
    recalc();
}

function removeItem(btn) {
    const tbody = document.getElementById('itemsBody');
    if (tbody.rows.length <= 1) return;
    btn.closest('tr').remove();
    recalc();
}

function recalc() {
    let total = 0;
    document.querySelectorAll('#itemsBody tr').forEach(tr => {
        const qty = parseInt(tr.querySelector('[name$="[quantity]"]')?.value) || 0;
        const price = parseInt(tr.querySelector('[name$="[unit_price_cents]"]')?.value) || 0;
        const sub = qty * price;
        total += sub;
        tr.querySelector('.subtotal').textContent = 'R$ ' + (sub / 100).toFixed(2).replace('.', ',');
    });
    document.getElementById('totalDisplay').textContent = 'R$ ' + (total / 100).toFixed(2).replace('.', ',');
}

const tenantSelect = document.getElementById('tenantSelect');
const planSelect = document.getElementById('planSelect');

tenantSelect.addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    if (opt.dataset.plan) {
        planSelect.value = opt.dataset.plan;
        autoAddPlanItem();
    }
});

planSelect.addEventListener('change', autoAddPlanItem);

function autoAddPlanItem() {
    const opt = planSelect.options[planSelect.selectedIndex];
    const price = parseInt(opt.dataset.price) || 0;
    const name = opt.dataset.name || opt.textContent.split('—')[0].trim();
    const tbody = document.getElementById('itemsBody');
    tbody.innerHTML = '';
    itemIndex = 0;
    addItem(name + ' - Mensal', 1, price);
}

// init
if (tenantSelect.value) {
    tenantSelect.dispatchEvent(new Event('change'));
} else {
    addItem('', 1, 0);
}
</script>
@endpush

@endsection
