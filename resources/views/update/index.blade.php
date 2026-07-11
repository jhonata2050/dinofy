@extends('layouts.app')
@section('title', 'Atualização')
@section('content')

<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-xl font-semibold text-zinc-900">Atualização da Plataforma</h1>
        <p class="text-zinc-500 text-sm mt-0.5">Atualize a imagem Docker e faça deploy para todos os tenants</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">{{ session('error') }}</div>
@endif
@if(session('warning'))
    <div class="mb-6 p-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-sm">{{ session('warning') }}</div>
@endif

{{-- Step 1: Pull Image --}}
<div class="rounded-xl border border-zinc-50 bg-zinc-50 p-6 mb-6">
    <div class="flex items-start justify-between">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <span class="w-7 h-7 rounded-full text-white text-xs font-bold flex items-center justify-center" style="background: var(--color-primary);">1</span>
                <h2 class="text-lg font-semibold text-zinc-900">Baixar Nova Imagem</h2>
            </div>
            <p class="text-sm text-zinc-500 ml-10">Executa <code class="bg-zinc-200 px-1.5 py-0.5 rounded text-xs">docker pull {{ $image }}</code> no servidor</p>
        </div>
        <form method="POST" action="{{ route('admin.update.pull') }}">
            @csrf
            <button type="submit" class="px-5 py-2.5 text-white rounded-lg text-sm font-medium transition hover:opacity-90" style="background: var(--color-primary);">
                Baixar Imagem
            </button>
        </form>
    </div>
</div>

{{-- Step 2: Deploy All --}}
<div class="rounded-xl border border-zinc-50 bg-zinc-50 p-6 mb-6">
    <div class="flex items-start justify-between">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <span class="w-7 h-7 rounded-full text-white text-xs font-bold flex items-center justify-center" style="background: var(--color-primary);">2</span>
                <h2 class="text-lg font-semibold text-zinc-900">Deploy para Todos os Tenants</h2>
            </div>
            <p class="text-sm text-zinc-500 ml-10">Re-cria os containers de todos os tenants ativos com a nova imagem. Migrations rodam automaticamente.</p>
        </div>
        <form method="POST" action="{{ route('admin.update.deploy') }}" onsubmit="return confirm('Isso vai atualizar TODOS os tenants ativos. Continuar?');">
            @csrf
            <button type="submit" class="px-5 py-2.5 bg-amber-500 text-white rounded-lg text-sm font-medium transition hover:bg-amber-600">
                Deploy Todos
            </button>
        </form>
    </div>
</div>

{{-- Tenant list for individual updates --}}
<div class="rounded-xl border border-zinc-50 bg-zinc-50 overflow-hidden">
    <div class="px-6 py-4 border-b border-zinc-100">
        <h2 class="text-lg font-semibold text-zinc-900">Tenants ({{ $tenants->count() }})</h2>
        <p class="text-sm text-zinc-500">Ou atualize individualmente</p>
    </div>

    @if($tenants->isEmpty())
        <div class="p-12 text-center">
            <p class="text-zinc-500">Nenhum tenant ativo</p>
        </div>
    @else
        <div class="divide-y divide-zinc-100">
            @foreach($tenants as $tenant)
                <div class="flex items-center justify-between px-6 py-4 hover:bg-zinc-100 transition">
                    <div class="flex items-center gap-4">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold text-white {{ $tenant->status === 'active' ? 'bg-green-600' : 'bg-zinc-400' }}">
                            {{ strtoupper(substr($tenant->subdomain, 0, 2)) }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-zinc-900">{{ $tenant->name }}</p>
                            <p class="text-xs text-zinc-500">{{ $tenant->subdomain }}.{{ config('master.base_domain') }} · {{ $tenant->plan->name ?? '—' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $tenant->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-zinc-100 text-zinc-600' }}">
                            {{ ucfirst($tenant->status) }}
                        </span>
                        @if($tenant->status === 'active')
                            <form method="POST" action="{{ route('admin.update.tenant', $tenant) }}">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 text-xs font-medium rounded-lg border border-zinc-300 text-zinc-700 hover:bg-zinc-200 transition">
                                    Atualizar
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

@endsection
