@extends('client.layouts.app')
@section('title', 'Planos')
@section('content')

<div class="mb-8">
    <h1 class="text-xl font-semibold text-zinc-900">Planos</h1>
    <p class="text-zinc-500 text-sm mt-0.5">Seu plano atual e opções de upgrade</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    @foreach($plans as $plan)
        @php $isCurrent = $plan->id === $tenant->plan_id; @endphp
        <div class="rounded-xl border {{ $isCurrent ? 'border-green-300 bg-green-50 ring-2 ring-green-200' : 'border-zinc-50 bg-zinc-50' }} p-6 flex flex-col">
            @if($isCurrent)
                <span class="self-start px-2 py-0.5 text-xs font-bold rounded-full bg-green-200 text-green-800 mb-3">PLANO ATUAL</span>
            @endif
            <h3 class="text-lg font-bold text-zinc-900">{{ $plan->name }}</h3>
            <div class="mt-2 mb-4">
                <span class="text-3xl font-bold text-zinc-900">R$ {{ $plan->priceFormatted() }}</span>
                <span class="text-zinc-500 text-sm">/mês</span>
            </div>

            <ul class="space-y-2 text-sm text-zinc-600 flex-1">
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    {{ $plan->cpu_limit }} CPU cores
                </li>
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    {{ $plan->memory_limit }} RAM
                </li>
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    {{ $plan->storage_limit_gb }} GB storage
                </li>
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    {{ $plan->max_db_connections }} conexões DB
                </li>
            </ul>

            <div class="mt-5">
                @if($isCurrent)
                    <button disabled class="w-full py-2.5 bg-zinc-200 text-zinc-500 rounded-lg text-sm font-medium cursor-not-allowed">Plano Atual</button>
                @else
                    <form method="POST" action="{{ route('client.plans.upgrade') }}" onsubmit="return confirm('Solicitar mudança para o plano {{ $plan->name }}?');">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                        <button type="submit" class="w-full py-2.5 text-white rounded-lg text-sm font-medium transition hover:opacity-90" style="background: var(--color-primary);">
                            {{ $plan->price_cents > $tenant->plan->price_cents ? 'Fazer Upgrade' : 'Mudar Plano' }}
                        </button>
                    </form>
                @endif
            </div>
        </div>
    @endforeach
</div>

@endsection
