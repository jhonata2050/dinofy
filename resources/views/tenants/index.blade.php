@extends('layouts.app')
@section('title', 'Clientes')
@section('content')

<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-semibold text-zinc-900">Clientes</h1>
    <a href="{{ route('admin.tenants.create') }}" class="px-4 py-2 text-white rounded-lg text-sm font-medium transition" style="background: var(--color-primary);">+ Novo Cliente</a>
</div>

<div class="rounded-xl border border-zinc-50 bg-zinc-50 overflow-hidden">
    <div class="p-4 border-b border-zinc-200">
        <form method="GET" class="flex gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar..." class="flex-1 px-3 py-2 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
            <select name="status" class="px-3 py-2 border border-zinc-300 rounded-lg text-sm">
                <option value="">Todos</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Ativo</option>
                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspenso</option>
                <option value="provisioning" {{ request('status') === 'provisioning' ? 'selected' : '' }}>Provisionando</option>
                <option value="terminated" {{ request('status') === 'terminated' ? 'selected' : '' }}>Terminado</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-zinc-200 rounded-lg hover:bg-zinc-300 text-sm font-medium text-zinc-700 transition">Filtrar</button>
        </form>
    </div>

    <table class="w-full">
        <thead class="bg-zinc-100">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Subdomínio</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Cliente</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Plano</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Próx. Cobrança</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 uppercase">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-zinc-100 bg-white">
            @forelse($tenants as $tenant)
                <tr class="hover:bg-zinc-50 transition">
                    <td class="px-6 py-4">
                        <a href="{{ route('admin.tenants.show', $tenant) }}" class="font-medium hover:underline text-sm" style="color: var(--color-primary);">{{ $tenant->subdomain }}</a>
                        @if($tenant->custom_domain)
                            <div class="text-xs text-zinc-400">{{ $tenant->custom_domain }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-zinc-800">{{ $tenant->name }}</div>
                        <div class="text-xs text-zinc-400">{{ $tenant->email }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-zinc-600">{{ $tenant->plan->name }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full
                            {{ $tenant->status === 'active' ? 'bg-emerald-100 text-emerald-700' : '' }}
                            {{ $tenant->status === 'suspended' ? 'bg-red-100 text-red-700' : '' }}
                            {{ $tenant->status === 'provisioning' ? 'bg-amber-100 text-amber-700' : '' }}
                            {{ $tenant->status === 'terminated' ? 'bg-zinc-200 text-zinc-600' : '' }}
                        ">{{ ucfirst($tenant->status) }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-zinc-500">{{ $tenant->next_billing_date?->format('d/m/Y') ?? '—' }}</td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.tenants.show', $tenant) }}" class="text-sm font-medium hover:underline" style="color: var(--color-primary);">Ver</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-zinc-400 text-sm">Nenhum cliente encontrado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="p-4 border-t border-zinc-200">
        {{ $tenants->withQueryString()->links() }}
    </div>
</div>

@endsection
