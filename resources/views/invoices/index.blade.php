@extends('layouts.app')
@section('title', 'Faturas')
@section('content')

<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-semibold text-zinc-900">Faturas</h1>
    <a href="{{ route('admin.invoices.create') }}" class="px-4 py-2 text-white rounded-lg text-sm font-medium transition" style="background: var(--color-primary);">+ Nova Fatura</a>
</div>

<div class="rounded-xl border border-zinc-50 bg-zinc-50 overflow-hidden">
    <div class="p-4 border-b border-zinc-200">
        <form method="GET" class="flex gap-3">
            <select name="status" class="px-3 py-2 border border-zinc-300 rounded-lg text-sm">
                <option value="">Todos</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendente</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Pago</option>
                <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Vencido</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-zinc-200 rounded-lg hover:bg-zinc-300 text-sm font-medium text-zinc-700 transition">Filtrar</button>
            @if(request()->hasAny(['status', 'tenant_id']))
                <a href="{{ route('admin.invoices.index') }}" class="px-4 py-2 bg-zinc-100 text-zinc-500 rounded-lg text-sm hover:bg-zinc-200 transition">Limpar</a>
            @endif
        </form>
    </div>

    <table class="w-full">
        <thead class="bg-zinc-100">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase">#</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Cliente</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Plano</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Valor</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Vencimento</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Pago em</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-zinc-100 bg-white">
            @forelse($invoices as $invoice)
                <tr class="hover:bg-zinc-50 transition">
                    <td class="px-6 py-4 text-sm text-zinc-500">#{{ $invoice->id }}</td>
                    <td class="px-6 py-4">
                        <a href="{{ route('admin.tenants.show', $invoice->tenant_id) }}" class="text-sm font-medium hover:underline" style="color: var(--color-primary);">{{ $invoice->tenant->subdomain }}</a>
                    </td>
                    <td class="px-6 py-4 text-sm text-zinc-600">{{ $invoice->plan->name }}</td>
                    <td class="px-6 py-4 text-sm font-medium text-zinc-800">R$ {{ $invoice->amountFormatted() }}</td>
                    <td class="px-6 py-4 text-sm text-zinc-500">{{ $invoice->due_date->format('d/m/Y') }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full
                            {{ $invoice->status === 'paid' ? 'bg-emerald-100 text-emerald-700' : '' }}
                            {{ $invoice->status === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}
                            {{ $invoice->status === 'overdue' ? 'bg-red-100 text-red-700' : '' }}
                            {{ $invoice->status === 'cancelled' ? 'bg-zinc-200 text-zinc-600' : '' }}
                        ">{{ ucfirst($invoice->status) }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-zinc-500">{{ $invoice->paid_at?->format('d/m/Y H:i') ?? '—' }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.invoices.show', $invoice) }}" class="text-sm font-medium hover:underline" style="color: var(--color-primary);">Ver</a>
                            @if($invoice->status !== 'paid')
                                <form method="POST" action="{{ route('admin.invoices.confirm-payment', $invoice) }}" onsubmit="return confirm('Confirmar pagamento?')" class="inline">
                                    @csrf
                                    <button type="submit" class="text-sm font-medium text-emerald-600 hover:underline">Confirmar</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-6 py-8 text-center text-zinc-400 text-sm">Nenhuma fatura encontrada.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($invoices->hasPages())
        <div class="p-4 border-t border-zinc-200">
            {{ $invoices->withQueryString()->links() }}
        </div>
    @endif
</div>

@endsection
