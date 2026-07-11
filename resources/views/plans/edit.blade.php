@extends('layouts.app')
@section('title', 'Editar ' . $plan->name)
@section('content')

<h1 class="text-xl font-semibold text-zinc-900 mb-6">Editar Plano: {{ $plan->name }}</h1>

<div class="rounded-xl border border-zinc-50 bg-zinc-50 p-6 max-w-2xl">
    <form method="POST" action="{{ route('admin.plans.update', $plan) }}" class="space-y-4">
        @csrf @method('PUT')

        <div>
            <label class="block text-sm font-medium text-zinc-700 mb-1">Nome</label>
            <input type="text" name="name" value="{{ old('name', $plan->name) }}" required class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">CPU Limit (cores)</label>
                <input type="number" step="0.25" name="cpu_limit" value="{{ old('cpu_limit', $plan->cpu_limit) }}" required class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Memória</label>
                <input type="text" name="memory_limit" value="{{ old('memory_limit', $plan->memory_limit) }}" required class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Storage (GB)</label>
                <input type="number" name="storage_limit_gb" value="{{ old('storage_limit_gb', $plan->storage_limit_gb) }}" required class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Max DB Connections</label>
                <input type="number" name="max_db_connections" value="{{ old('max_db_connections', $plan->max_db_connections) }}" required class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Preço (centavos)</label>
                <input type="number" name="price_cents" value="{{ old('price_cents', $plan->price_cents) }}" required class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
            </div>
        </div>

        <div class="flex items-center">
            <input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $plan->is_active) ? 'checked' : '' }} class="rounded border-zinc-300 text-sky-500 focus:ring-green-600">
            <label for="is_active" class="ml-2 text-sm text-zinc-600">Ativo</label>
        </div>

        <div class="flex gap-3 pt-4">
            <button type="submit" class="px-6 py-2.5 text-white rounded-lg text-sm font-medium transition" style="background: var(--color-primary);">Salvar</button>
            <a href="{{ route('admin.plans.index') }}" class="px-6 py-2.5 bg-zinc-200 rounded-lg hover:bg-zinc-300 text-sm font-medium text-zinc-700 transition">Cancelar</a>
        </div>
    </form>
</div>

@endsection
