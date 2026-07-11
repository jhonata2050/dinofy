@extends('client.layouts.app')
@section('title', 'Configurações')
@section('content')

<div class="mb-8">
    <h1 class="text-xl font-semibold text-zinc-900">Configurações</h1>
    <p class="text-zinc-500 text-sm mt-0.5">Gerencie seu perfil e senha</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Perfil --}}
    <div class="rounded-xl border border-zinc-50 bg-zinc-50 p-6">
        <h2 class="text-sm font-semibold text-zinc-700 mb-4">Perfil</h2>
        <form method="POST" action="{{ route('client.settings.profile') }}" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Nome</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                    class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">E-mail</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                    class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
            </div>
            <button type="submit" class="px-5 py-2.5 text-white rounded-lg text-sm font-medium transition hover:opacity-90" style="background: var(--color-primary);">Salvar</button>
        </form>
    </div>

    {{-- Senha --}}
    <div class="rounded-xl border border-zinc-50 bg-zinc-50 p-6">
        <h2 class="text-sm font-semibold text-zinc-700 mb-4">Alterar Senha</h2>
        <form method="POST" action="{{ route('client.settings.password') }}" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Senha Atual</label>
                <input type="password" name="current_password" required
                    class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Nova Senha</label>
                <input type="password" name="password" required
                    class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Confirmar Nova Senha</label>
                <input type="password" name="password_confirmation" required
                    class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
            </div>
            <button type="submit" class="px-5 py-2.5 text-white rounded-lg text-sm font-medium transition hover:opacity-90" style="background: var(--color-primary);">Alterar Senha</button>
        </form>
    </div>
</div>

{{-- Info da conta --}}
<div class="rounded-xl border border-zinc-50 bg-zinc-50 p-6 mt-6">
    <h2 class="text-sm font-semibold text-zinc-700 mb-4">Informações da Conta</h2>
    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
        <div><dt class="text-zinc-500">Subdomínio</dt><dd class="font-medium text-zinc-900 mt-0.5">{{ $tenant->subdomain }}.{{ config('master.base_domain') }}</dd></div>
        <div><dt class="text-zinc-500">Plano</dt><dd class="font-medium text-zinc-900 mt-0.5">{{ $tenant->plan->name }} — R$ {{ $tenant->plan->priceFormatted() }}/mês</dd></div>
        <div><dt class="text-zinc-500">Documento</dt><dd class="font-medium text-zinc-900 mt-0.5">{{ $tenant->document }}</dd></div>
        <div><dt class="text-zinc-500">Cliente desde</dt><dd class="font-medium text-zinc-900 mt-0.5">{{ $tenant->created_at->format('d/m/Y') }}</dd></div>
    </dl>
</div>

@endsection
