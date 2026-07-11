@extends('layouts.app')
@section('title', 'Novo Plano')
@section('content')

<h1 class="text-xl font-semibold text-zinc-900 mb-6">Novo Plano</h1>

<div class="rounded-xl border border-zinc-50 bg-zinc-50 p-6 max-w-2xl">
    <form method="POST" action="{{ route('admin.plans.store') }}" class="space-y-4">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Nome</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Slug</label>
                <input type="text" name="slug" value="{{ old('slug') }}" required class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">CPU Limit (cores)</label>
                <input type="number" step="0.25" name="cpu_limit" value="{{ old('cpu_limit', 1) }}" required class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Memória (ex: 256M, 1G)</label>
                <input type="text" name="memory_limit" value="{{ old('memory_limit', '512M') }}" required class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Storage (GB)</label>
                <input type="number" name="storage_limit_gb" value="{{ old('storage_limit_gb', 10) }}" required class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Max DB Connections</label>
                <input type="number" name="max_db_connections" value="{{ old('max_db_connections', 50) }}" required class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Preço (centavos)</label>
                <input type="number" name="price_cents" value="{{ old('price_cents', 9700) }}" required class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
                <p class="text-xs text-zinc-400 mt-1">Ex: 9700 = R$ 97,00</p>
            </div>
        </div>

        <div class="flex items-center">
            <input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-zinc-300 text-sky-500 focus:ring-green-600">
            <label for="is_active" class="ml-2 text-sm text-zinc-600">Ativo</label>
        </div>

        <div class="flex gap-3 pt-4">
            <button type="submit" class="px-6 py-2.5 text-white rounded-lg text-sm font-medium transition" style="background: var(--color-primary);">Criar Plano</button>
            <a href="{{ route('admin.plans.index') }}" class="px-6 py-2.5 bg-zinc-200 rounded-lg hover:bg-zinc-300 text-sm font-medium text-zinc-700 transition">Cancelar</a>
        </div>
    </form>
</div>

@endsection
