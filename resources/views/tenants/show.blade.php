@extends('layouts.app')
@section('title', $tenant->subdomain)
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-semibold text-zinc-900">{{ $tenant->subdomain }}.{{ config('master.base_domain') }}</h1>
        <p class="text-zinc-500 text-sm">{{ $tenant->name }} — {{ $tenant->email }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.tenants.edit', $tenant) }}" class="px-4 py-2 bg-zinc-200 rounded-lg hover:bg-zinc-300 text-sm font-medium text-zinc-700 transition">Editar</a>
        @if($tenant->status === 'active')
            <form method="POST" action="{{ route('admin.tenants.suspend', $tenant) }}" onsubmit="return confirm('Suspender este tenant?')">
                @csrf
                <button class="px-4 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 text-sm font-medium transition">Suspender</button>
            </form>
        @elseif($tenant->status === 'suspended')
            <form method="POST" action="{{ route('admin.tenants.activate', $tenant) }}">
                @csrf
                <button class="px-4 py-2 text-white rounded-lg text-sm font-medium transition" style="background: var(--color-primary);">Reativar</button>
            </form>
        @elseif(in_array($tenant->status, ['provisioning', 'pending_payment']))
            <form method="POST" action="{{ route('admin.tenants.reprovision', $tenant) }}" onsubmit="return confirm('Reprovisionar este tenant?')">
                @csrf
                <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition">Reprovisionar</button>
            </form>
        @endif
        <form method="POST" action="{{ route('admin.tenants.destroy', $tenant) }}" onsubmit="return confirm('DESTRUIR este tenant? Isso é irreversível!')">
            @csrf @method('DELETE')
            <button class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium transition">Destruir</button>
        </form>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
    <div class="rounded-xl border border-zinc-50 bg-zinc-50 p-5">
        <h2 class="text-xs font-semibold text-zinc-500 uppercase mb-3">Informações</h2>
        <dl class="space-y-2 text-sm">
            <div class="flex justify-between"><dt class="text-zinc-500">Status</dt>
                <dd><span class="px-2 py-0.5 text-xs font-medium rounded-full
                    {{ $tenant->status === 'active' ? 'bg-emerald-100 text-emerald-700' : '' }}
                    {{ $tenant->status === 'suspended' ? 'bg-red-100 text-red-700' : '' }}
                    {{ $tenant->status === 'provisioning' ? 'bg-amber-100 text-amber-700' : '' }}
                ">{{ ucfirst($tenant->status) }}</span></dd>
            </div>
            <div class="flex justify-between"><dt class="text-zinc-500">Plano</dt><dd class="font-medium text-zinc-800">{{ $tenant->plan->name }}</dd></div>
            <div class="flex justify-between"><dt class="text-zinc-500">Preço</dt><dd class="text-zinc-700">R$ {{ $tenant->plan->priceFormatted() }}/mês</dd></div>
            <div class="flex justify-between"><dt class="text-zinc-500">Documento</dt><dd class="text-zinc-700">{{ $tenant->document }}</dd></div>
            <div class="flex justify-between"><dt class="text-zinc-500">Telefone</dt><dd class="text-zinc-700">{{ $tenant->phone ?? '—' }}</dd></div>
            <div class="flex justify-between"><dt class="text-zinc-500">Próx. Cobrança</dt><dd class="text-zinc-700">{{ $tenant->next_billing_date?->format('d/m/Y') ?? '—' }}</dd></div>
            <div class="flex justify-between"><dt class="text-zinc-500">Criado em</dt><dd class="text-zinc-700">{{ $tenant->created_at->format('d/m/Y H:i') }}</dd></div>
        </dl>
    </div>

    <div class="rounded-xl border border-zinc-50 bg-zinc-50 p-5">
        <h2 class="text-xs font-semibold text-zinc-500 uppercase mb-3">Recursos</h2>
        <dl class="space-y-2 text-sm">
            <div class="flex justify-between"><dt class="text-zinc-500">CPU Limit</dt><dd class="font-mono text-zinc-700">{{ $tenant->plan->cpu_limit }} cores</dd></div>
            <div class="flex justify-between"><dt class="text-zinc-500">Memória</dt><dd class="font-mono text-zinc-700">{{ $tenant->plan->memory_limit }}</dd></div>
            <div class="flex justify-between"><dt class="text-zinc-500">Storage</dt><dd class="font-mono text-zinc-700">{{ $tenant->plan->storage_limit_gb }} GB</dd></div>
            <div class="flex justify-between"><dt class="text-zinc-500">Status Docker</dt><dd>
                @if($stats['running'])
                    <span class="text-emerald-600 font-medium">Rodando</span>
                @else
                    <span class="text-red-600 font-medium">Parado</span>
                @endif
            </dd></div>
        </dl>

        {{-- Barra de uso de disco --}}
        @if(isset($stats['disk']))
            @php
                $diskPercent = $stats['disk']['percent'];
                $diskColor = $diskPercent >= 100 ? 'bg-red-500' : ($diskPercent >= 80 ? 'bg-amber-500' : 'bg-green-600');
                $diskTextColor = $diskPercent >= 100 ? 'text-red-700' : ($diskPercent >= 80 ? 'text-amber-700' : 'text-green-700');
            @endphp
            <div class="mt-4 pt-3 border-t border-zinc-200">
                <div class="flex justify-between text-xs mb-1.5">
                    <span class="text-zinc-500">Disco usado</span>
                    <span class="font-medium {{ $diskTextColor }}">{{ $stats['disk']['total_formatted'] }} / {{ $stats['disk']['limit_formatted'] }} ({{ $diskPercent }}%)</span>
                </div>
                <div class="w-full bg-zinc-200 rounded-full h-2">
                    <div class="{{ $diskColor }} h-2 rounded-full transition-all" style="width: {{ min($diskPercent, 100) }}%"></div>
                </div>
                @if($diskPercent >= 80)
                    <p class="text-xs mt-1.5 {{ $diskPercent >= 100 ? 'text-red-600 font-medium' : 'text-amber-600' }}">
                        {{ $diskPercent >= 100 ? 'Limite excedido! Tenant será suspenso automaticamente.' : 'Atenção: uso de disco acima de 80%.' }}
                    </p>
                @endif
                <div class="flex gap-3 mt-2 text-xs text-zinc-400">
                    <span>App: {{ $stats['disk']['app_bytes'] >= 1073741824 ? number_format($stats['disk']['app_bytes'] / 1073741824, 2, ',', '.') . ' GB' : number_format($stats['disk']['app_bytes'] / 1048576, 1, ',', '.') . ' MB' }}</span>
                    <span>DB: {{ $stats['disk']['db_bytes'] >= 1073741824 ? number_format($stats['disk']['db_bytes'] / 1073741824, 2, ',', '.') . ' GB' : number_format($stats['disk']['db_bytes'] / 1048576, 1, ',', '.') . ' MB' }}</span>
                </div>
            </div>
        @endif

        @if(!empty($stats['containers']))
            <div class="mt-4 space-y-2">
                @foreach($stats['containers'] as $c)
                    <div class="text-xs bg-zinc-100 rounded-lg p-2">
                        <div class="font-mono text-zinc-700">{{ $c['name'] }}</div>
                        <div class="text-zinc-500">CPU: {{ $c['cpu'] }} | RAM: {{ $c['memory'] }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="rounded-xl border border-zinc-50 bg-zinc-50 p-5">
        <h2 class="text-xs font-semibold text-zinc-500 uppercase mb-3">Domínios</h2>

        @if(session('dns_instructions'))
            @php $dns = session('dns_instructions'); @endphp
            <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm">
                <p class="font-medium text-blue-800 mb-2">Configure os registros DNS:</p>
                <div class="space-y-2 text-xs font-mono bg-white rounded p-2.5 border border-blue-100">
                    <div>
                        <span class="text-zinc-500">1. TXT</span><br>
                        <span class="text-zinc-700">Host: <strong>{{ $dns['txt']['host'] }}</strong></span><br>
                        <span class="text-zinc-700">Valor: <strong>{{ $dns['txt']['value'] }}</strong></span>
                    </div>
                    <div class="border-t border-blue-100 pt-2">
                        <span class="text-zinc-500">2. {{ $dns['cname']['type'] }}</span><br>
                        <span class="text-zinc-700">Host: <strong>{{ $dns['cname']['host'] }}</strong></span><br>
                        <span class="text-zinc-700">Valor: <strong>{{ $dns['cname']['value'] }}</strong></span>
                    </div>
                </div>
                <p class="text-xs text-blue-600 mt-2">Após configurar, clique em "Verificar".</p>
            </div>
        @endif

        @foreach($tenant->domains as $domain)
            <div class="flex items-center justify-between mb-2 text-sm">
                <div>
                    <span class="font-medium text-zinc-800">{{ $domain->domain }}</span>
                    @if($domain->isVerified())
                        <span class="text-emerald-600 text-xs ml-1">Verificado</span>
                    @else
                        <span class="text-amber-600 text-xs ml-1">Pendente</span>
                    @endif
                </div>
                <div class="flex gap-2">
                    @unless($domain->isVerified())
                        <form method="POST" action="{{ route('admin.domains.verify', $domain) }}">
                            @csrf
                            <button class="text-xs font-medium hover:underline" style="color: var(--color-primary);">Verificar</button>
                        </form>
                    @endunless
                    <form method="POST" action="{{ route('admin.domains.destroy', $domain) }}" onsubmit="return confirm('Remover este domínio?')">
                        @csrf @method('DELETE')
                        <button class="text-red-600 hover:underline text-xs font-medium">Remover</button>
                    </form>
                </div>
            </div>
        @endforeach

        @if($tenant->domains->isEmpty())
            <p class="text-zinc-400 text-sm mb-3">Nenhum domínio customizado.</p>
        @endif

        <form method="POST" action="{{ route('admin.domains.store', $tenant) }}" class="mt-3 flex gap-2">
            @csrf
            <input type="text" name="domain" placeholder="exemplo.com.br" required class="flex-1 px-3 py-1.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
            <button class="px-3 py-1.5 text-white rounded-lg text-sm font-medium" style="background: var(--color-primary);">Adicionar</button>
        </form>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div class="rounded-xl border border-zinc-50 bg-zinc-50 p-5">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-xs font-semibold text-zinc-500 uppercase">Faturas Recentes</h2>
            <a href="{{ route('admin.invoices.create', ['tenant_id' => $tenant->id]) }}" class="text-xs font-medium hover:underline" style="color: var(--color-primary);">+ Nova Fatura</a>
        </div>
        @forelse($tenant->invoices as $invoice)
            <div class="flex items-center justify-between py-2 border-b border-zinc-200 last:border-0 text-sm">
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.invoices.show', $invoice) }}" class="font-medium hover:underline" style="color: var(--color-primary);">R$ {{ $invoice->amountFormatted() }}</a>
                    <span class="text-zinc-400">{{ $invoice->due_date->format('d/m/Y') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 text-xs font-medium rounded-full
                        {{ $invoice->status === 'paid' ? 'bg-emerald-100 text-emerald-700' : '' }}
                        {{ $invoice->status === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}
                        {{ $invoice->status === 'overdue' ? 'bg-red-100 text-red-700' : '' }}
                    ">{{ ucfirst($invoice->status) }}</span>
                    @if($invoice->status !== 'paid')
                        <form method="POST" action="{{ route('admin.invoices.confirm-payment', $invoice) }}" onsubmit="return confirm('Confirmar pagamento?')" class="inline">
                            @csrf
                            <button type="submit" class="text-xs font-medium text-emerald-600 hover:underline">Confirmar</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-zinc-400 text-sm">Nenhuma fatura.</p>
        @endforelse
    </div>

    <div class="rounded-xl border border-zinc-50 bg-zinc-50 p-5">
        <h2 class="text-xs font-semibold text-zinc-500 uppercase mb-3">Logs do Container</h2>
        <pre class="bg-zinc-900 text-zinc-300 text-xs p-4 rounded-lg overflow-auto max-h-64 font-mono">{{ $logs ?: 'Sem logs disponíveis.' }}</pre>
    </div>
</div>

@endsection
