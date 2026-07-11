@extends('layouts.app')
@section('title', 'Planos')
@section('content')

<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-semibold text-zinc-900">Planos</h1>
    <a href="{{ route('admin.plans.create') }}" class="px-4 py-2 text-white rounded-lg text-sm font-medium transition" style="background: var(--color-primary);">+ Novo Plano</a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    @foreach($plans as $plan)
        <div class="rounded-xl border border-zinc-50 bg-zinc-50 p-5 {{ !$plan->is_active ? 'opacity-50' : '' }}">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-base font-semibold text-zinc-900">{{ $plan->name }}</h3>
                @unless($plan->is_active)
                    <span class="text-xs bg-zinc-200 text-zinc-600 px-2 py-0.5 rounded-full">Inativo</span>
                @endunless
            </div>
            <div class="text-2xl font-bold text-zinc-900 mb-4">
                R$ {{ $plan->priceFormatted() }}<span class="text-sm text-zinc-400 font-normal">/mês</span>
            </div>
            <dl class="space-y-2 text-sm mb-4">
                <div class="flex justify-between"><dt class="text-zinc-500">CPU</dt><dd class="font-mono text-zinc-700">{{ $plan->cpu_limit }} cores</dd></div>
                <div class="flex justify-between"><dt class="text-zinc-500">RAM</dt><dd class="font-mono text-zinc-700">{{ $plan->memory_limit }}</dd></div>
                <div class="flex justify-between"><dt class="text-zinc-500">Storage</dt><dd class="font-mono text-zinc-700">{{ $plan->storage_limit_gb }} GB</dd></div>
                <div class="flex justify-between"><dt class="text-zinc-500">DB Conn.</dt><dd class="font-mono text-zinc-700">{{ $plan->max_db_connections }}</dd></div>
                <div class="flex justify-between"><dt class="text-zinc-500">Clientes</dt><dd class="font-semibold text-zinc-900">{{ $plan->tenants_count }}</dd></div>
            </dl>
            <a href="{{ route('admin.plans.edit', $plan) }}" class="block text-center px-4 py-2 bg-zinc-200 rounded-lg hover:bg-zinc-300 text-sm font-medium text-zinc-700 transition">Editar</a>
        </div>
    @endforeach
</div>

@endsection
