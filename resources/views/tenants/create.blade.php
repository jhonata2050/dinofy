@extends('layouts.app')
@section('title', 'Novo Cliente')
@section('content')

<h1 class="text-xl font-semibold text-zinc-900 mb-6">Novo Cliente</h1>

<div class="rounded-xl border border-zinc-50 bg-zinc-50 p-6 max-w-2xl">
    <form method="POST" action="{{ route('admin.tenants.store') }}" class="space-y-4">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Subdomínio</label>
                <div class="flex">
                    <input type="text" name="subdomain" value="{{ old('subdomain') }}" required class="flex-1 px-3 py-2.5 border border-zinc-300 rounded-l-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
                    <span class="px-3 py-2.5 bg-zinc-200 border border-l-0 border-zinc-300 rounded-r-lg text-zinc-500 text-sm">.dinofy.app</span>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Plano</label>
                <select name="plan_id" required class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm">
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}" {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
                            {{ $plan->name }} — R$ {{ $plan->priceFormatted() }}/mês
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-zinc-700 mb-1">Nome</label>
            <input type="text" name="name" value="{{ old('name') }}" required class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">E-mail</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Telefone</label>
                <input type="text" name="phone" value="{{ old('phone') }}" class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-zinc-700 mb-1">CPF/CNPJ</label>
            <input type="text" name="document" value="{{ old('document') }}" required class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
        </div>

        <div>
            <label class="block text-sm font-medium text-zinc-700 mb-1">Senha do Cliente</label>
            <input type="password" name="password" required minlength="8" class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
            <p class="text-xs text-zinc-400 mt-1">Mínimo 8 caracteres. O cliente usará este e-mail e senha para acessar o painel.</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-zinc-700 mb-1">Observações</label>
            <textarea name="notes" rows="3" class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">{{ old('notes') }}</textarea>
        </div>

        <div class="flex gap-3 pt-4">
            <button type="submit" class="px-6 py-2.5 text-white rounded-lg text-sm font-medium transition" style="background: var(--color-primary);">Criar e Provisionar</button>
            <a href="{{ route('admin.tenants.index') }}" class="px-6 py-2.5 bg-zinc-200 rounded-lg hover:bg-zinc-300 text-sm font-medium text-zinc-700 transition">Cancelar</a>
        </div>
    </form>
</div>

@endsection
