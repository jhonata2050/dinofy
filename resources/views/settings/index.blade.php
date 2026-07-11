@extends('layouts.app')
@section('title', 'Configurações')
@section('content')

<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-xl font-semibold text-zinc-900">Configurações</h1>
        <p class="text-zinc-500 text-sm mt-0.5">Gerencie gateway de pagamento, billing e configurações da plataforma</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.settings.update') }}">
    @csrf
    @method('PUT')

    {{-- Tabs --}}
    <div class="flex border-b border-zinc-200 mb-6 gap-1" id="settings-tabs">
        <button type="button" onclick="switchTab('gateway')" class="tab-btn active px-5 py-3 text-sm font-medium border-b-2 transition-colors" data-tab="gateway">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                Gateway de Pagamento
            </span>
        </button>
        <button type="button" onclick="switchTab('billing')" class="tab-btn px-5 py-3 text-sm font-medium border-b-2 border-transparent text-zinc-500 hover:text-zinc-700 transition-colors" data-tab="billing">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                Cobrança
            </span>
        </button>
        <button type="button" onclick="switchTab('platform')" class="tab-btn px-5 py-3 text-sm font-medium border-b-2 border-transparent text-zinc-500 hover:text-zinc-700 transition-colors" data-tab="platform">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/></svg>
                Plataforma
            </span>
        </button>
        <button type="button" onclick="switchTab('notifications')" class="tab-btn px-5 py-3 text-sm font-medium border-b-2 border-transparent text-zinc-500 hover:text-zinc-700 transition-colors" data-tab="notifications">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                Notificações
            </span>
        </button>
    </div>

    {{-- Gateway Tab --}}
    <div class="tab-panel" id="tab-gateway">
        @php
            $activeGw = collect($groups['gateway'] ?? [])->firstWhere('key', 'gateway_active');
            $gwValue = $activeGw->value ?? 'woovi';
        @endphp

        {{-- Seletor de Gateway --}}
        <div class="rounded-xl border border-zinc-50 bg-zinc-50 p-6 mb-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-zinc-800">Gateway de Pagamento PIX</h2>
                    <p class="text-sm text-zinc-500">Selecione e configure o gateway ativo para cobranças PIX</p>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-zinc-700 mb-2">Gateway Ativo</label>
                <div class="flex gap-3">
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="settings[gateway_active]" value="woovi" {{ $gwValue === 'woovi' ? 'checked' : '' }}
                            class="sr-only peer" onchange="toggleGatewayPanels()">
                        <div class="p-4 border-2 rounded-xl transition-all peer-checked:border-green-600 peer-checked:bg-green-50 border-zinc-200 hover:border-zinc-300">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-semibold text-zinc-900">Woovi / OpenPix</span>
                                <span class="text-xs px-2 py-0.5 rounded-full {{ $gwValue === 'woovi' ? 'bg-green-100 text-green-700' : 'bg-zinc-100 text-zinc-500' }}" id="badge-woovi">{{ $gwValue === 'woovi' ? 'ATIVO' : 'INATIVO' }}</span>
                            </div>
                            <p class="text-xs text-zinc-500">API moderna, dashboard intuitivo, cobranças PIX instantâneas</p>
                        </div>
                    </label>
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="settings[gateway_active]" value="cajupay" {{ $gwValue === 'cajupay' ? 'checked' : '' }}
                            class="sr-only peer" onchange="toggleGatewayPanels()">
                        <div class="p-4 border-2 rounded-xl transition-all peer-checked:border-green-600 peer-checked:bg-green-50 border-zinc-200 hover:border-zinc-300">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-semibold text-zinc-900">CajuPay</span>
                                <span class="text-xs px-2 py-0.5 rounded-full {{ $gwValue === 'cajupay' ? 'bg-green-100 text-green-700' : 'bg-zinc-100 text-zinc-500' }}" id="badge-cajupay">{{ $gwValue === 'cajupay' ? 'ATIVO' : 'INATIVO' }}</span>
                            </div>
                            <p class="text-xs text-zinc-500">Gateway PIX com API Key + Secret, suporte a QR code e copia-cola</p>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        {{-- Painel Woovi --}}
        <div class="rounded-xl border border-zinc-50 bg-zinc-50 p-6 mb-6" id="panel-woovi">
            <div class="flex items-center gap-2 mb-4">
                <h3 class="text-base font-semibold text-zinc-800">Credenciais Woovi / OpenPix</h3>
            </div>

            @php $wooviSandbox = collect($groups['gateway'] ?? [])->firstWhere('key', 'woovi_sandbox'); @endphp

            {{-- Sandbox toggle --}}
            <div class="mb-5 p-4 rounded-lg {{ ($wooviSandbox->value ?? '0') ? 'bg-amber-50 border border-amber-300' : 'bg-green-50 border border-green-200' }}" id="woovi-env-banner">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="text-lg">{{ ($wooviSandbox->value ?? '0') ? '🧪' : '🟢' }}</span>
                        <div>
                            <p class="text-sm font-semibold {{ ($wooviSandbox->value ?? '0') ? 'text-amber-800' : 'text-green-800' }}" id="woovi-env-label">
                                {{ ($wooviSandbox->value ?? '0') ? 'Ambiente de TESTE (Sandbox)' : 'Ambiente de PRODUCAO' }}
                            </p>
                            <p class="text-xs {{ ($wooviSandbox->value ?? '0') ? 'text-amber-600' : 'text-green-600' }}" id="woovi-env-url">
                                {{ ($wooviSandbox->value ?? '0') ? 'api.woovi-sandbox.com — Nenhuma cobranca real sera gerada' : 'api.openpix.com.br — Cobranças reais ativas' }}
                            </p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="settings[woovi_sandbox]" value="0">
                        <input type="checkbox" name="settings[woovi_sandbox]" value="1"
                            {{ ($wooviSandbox->value ?? '0') ? 'checked' : '' }}
                            class="sr-only peer" id="wooviSandboxToggle" onchange="updateSandboxBanner()">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-amber-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                        <span class="ms-2 text-sm font-medium text-zinc-700">Sandbox</span>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @foreach($groups['gateway'] as $setting)
                    @if(str_starts_with($setting->key, 'woovi_') && !in_array($setting->key, ['woovi_sandbox', 'woovi_base_url']))
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 mb-1">{{ str_replace('[Woovi] ', '', $setting->label) }}</label>
                            @if($setting->type === 'password')
                                <div class="relative">
                                    <input type="password" name="settings[{{ $setting->key }}]"
                                        value=""
                                        placeholder="{{ $setting->is_encrypted && $setting->value ? 'Configurado ✓ (deixe vazio para manter)' : $setting->description }}"
                                        class="w-full px-4 py-2.5 border {{ $setting->is_encrypted && $setting->value ? 'border-green-300 bg-green-50/30' : 'border-zinc-300' }} rounded-lg focus:ring-2 focus:ring-green-600 focus:border-transparent pr-10">
                                    <button type="button" onclick="togglePassword(this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                </div>
                            @else
                                <input type="text" name="settings[{{ $setting->key }}]"
                                    value="{{ $setting->value }}"
                                    placeholder="{{ $setting->description }}"
                                    class="w-full px-4 py-2.5 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-green-600 focus:border-transparent">
                            @endif
                            <p class="text-xs text-zinc-400 mt-1">{{ $setting->description }}</p>
                        </div>
                    @endif
                @endforeach
            </div>

            <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <h4 class="text-sm font-semibold text-blue-800 mb-2">Webhook URL — Woovi</h4>
                <div class="flex items-center gap-2">
                    <code class="flex-1 bg-white px-3 py-2 rounded border border-blue-200 text-sm font-mono text-blue-700">{{ url('/webhooks/woovi') }}</code>
                    <button type="button" onclick="copyToClipboard('{{ url('/webhooks/woovi') }}')" class="px-3 py-2 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 text-sm">Copiar</button>
                </div>
                <p class="text-xs text-blue-600 mt-2">Configure no painel Woovi → Webhooks. Evento: <strong>OPENPIX:CHARGE_COMPLETED</strong></p>
            </div>
        </div>

        {{-- Painel CajuPay --}}
        <div class="rounded-xl border border-zinc-50 bg-zinc-50 p-6 mb-6" id="panel-cajupay">
            <div class="flex items-center gap-2 mb-4">
                <h3 class="text-base font-semibold text-zinc-800">Credenciais CajuPay</h3>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @foreach($groups['gateway'] as $setting)
                    @if(str_starts_with($setting->key, 'cajupay_'))
                        <div class="{{ $setting->key === 'cajupay_base_url' ? 'lg:col-span-2' : '' }}">
                            <label class="block text-sm font-medium text-zinc-700 mb-1">{{ str_replace('[CajuPay] ', '', $setting->label) }}</label>
                            @if($setting->type === 'password')
                                <div class="relative">
                                    <input type="password" name="settings[{{ $setting->key }}]"
                                        value=""
                                        placeholder="{{ $setting->is_encrypted && $setting->value ? 'Configurado ✓ (deixe vazio para manter)' : $setting->description }}"
                                        class="w-full px-4 py-2.5 border {{ $setting->is_encrypted && $setting->value ? 'border-green-300 bg-green-50/30' : 'border-zinc-300' }} rounded-lg focus:ring-2 focus:ring-green-600 focus:border-transparent pr-10">
                                    <button type="button" onclick="togglePassword(this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                </div>
                            @else
                                <input type="text" name="settings[{{ $setting->key }}]"
                                    value="{{ $setting->value }}"
                                    placeholder="{{ $setting->description }}"
                                    class="w-full px-4 py-2.5 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-green-600 focus:border-transparent">
                            @endif
                            <p class="text-xs text-zinc-400 mt-1">{{ $setting->description }}</p>
                        </div>
                    @endif
                @endforeach
            </div>

            <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <h4 class="text-sm font-semibold text-blue-800 mb-2">Webhook URL — CajuPay</h4>
                <div class="flex items-center gap-2">
                    <code class="flex-1 bg-white px-3 py-2 rounded border border-blue-200 text-sm font-mono text-blue-700">{{ url('/webhooks/cajupay') }}</code>
                    <button type="button" onclick="copyToClipboard('{{ url('/webhooks/cajupay') }}')" class="px-3 py-2 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 text-sm">Copiar</button>
                </div>
                <p class="text-xs text-blue-600 mt-2">Configure no painel CajuPay → Webhooks. Evento: <strong>pix.payment.paid</strong></p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="px-6 py-2.5 text-white rounded-lg" style="background: var(--color-primary); transition font-medium">Salvar Configuracoes</button>
            <button type="button" onclick="document.getElementById('test-form').submit()" class="px-6 py-2.5 bg-white border border-zinc-300 text-zinc-700 rounded-lg hover:bg-gray-50 transition font-medium">Testar Conexao</button>
        </div>
    </div>

    {{-- Billing Tab --}}
    <div class="tab-panel hidden" id="tab-billing">
        <div class="rounded-xl border border-zinc-50 bg-zinc-50 p-6 mb-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 10v1"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-zinc-800">Configurações de Cobrança</h2>
                    <p class="text-sm text-zinc-500">Defina regras de billing, grace period e trial</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @foreach($groups['billing'] as $setting)
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">{{ $setting->label }}</label>
                        @if($setting->type === 'boolean')
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="settings[{{ $setting->key }}]" value="0">
                                <input type="checkbox" name="settings[{{ $setting->key }}]" value="1"
                                    {{ $setting->value ? 'checked' : '' }}
                                    class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                <span class="ms-3 text-sm text-zinc-500">{{ $setting->description }}</span>
                            </label>
                        @elseif($setting->type === 'integer')
                            <input type="number" name="settings[{{ $setting->key }}]"
                                value="{{ $setting->value }}"
                                class="w-full px-4 py-2.5 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-green-600 focus:border-transparent">
                            <p class="text-xs text-zinc-400 mt-1">{{ $setting->description }}</p>
                        @else
                            <input type="text" name="settings[{{ $setting->key }}]"
                                value="{{ $setting->value }}"
                                class="w-full px-4 py-2.5 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-green-600 focus:border-transparent">
                            <p class="text-xs text-zinc-400 mt-1">{{ $setting->description }}</p>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-6 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                <h3 class="text-sm font-semibold text-amber-800 mb-1">Fluxo de Cobrança</h3>
                <ol class="text-xs text-amber-700 space-y-1 list-decimal list-inside">
                    <li>Cron diário gera faturas para tenants com <code>next_billing_date = hoje</code></li>
                    <li>Cobranca PIX e criada via Woovi/OpenPix com QR code e copia-cola</li>
                    <li>Webhook <code>pix.payment.paid</code> confirma pagamento automaticamente</li>
                    <li>Reconciliação a cada 2min como fallback para webhooks perdidos</li>
                    <li>Após o grace period, tenant é suspenso (containers parados, dados mantidos)</li>
                </ol>
            </div>
        </div>

        <button type="submit" class="px-6 py-2.5 text-white rounded-lg" style="background: var(--color-primary); transition font-medium">Salvar Configurações</button>
    </div>

    {{-- Platform Tab --}}
    <div class="tab-panel hidden" id="tab-platform">
        <div class="rounded-xl border border-zinc-50 bg-zinc-50 p-6 mb-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-zinc-800">Configurações da Plataforma</h2>
                    <p class="text-sm text-zinc-500">Domínio base, imagem Docker e limites gerais</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @foreach($groups['platform'] as $setting)
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">{{ $setting->label }}</label>
                        @if($setting->type === 'integer')
                            <input type="number" name="settings[{{ $setting->key }}]"
                                value="{{ $setting->value }}"
                                class="w-full px-4 py-2.5 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-green-600 focus:border-transparent">
                        @else
                            <input type="text" name="settings[{{ $setting->key }}]"
                                value="{{ $setting->value }}"
                                class="w-full px-4 py-2.5 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-green-600 focus:border-transparent">
                        @endif
                        <p class="text-xs text-zinc-400 mt-1">{{ $setting->description }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <button type="submit" class="px-6 py-2.5 text-white rounded-lg" style="background: var(--color-primary); transition font-medium">Salvar Configurações</button>
    </div>

    {{-- Notifications Tab --}}
    <div class="tab-panel hidden" id="tab-notifications">
        <div class="rounded-xl border border-zinc-50 bg-zinc-50 p-6 mb-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-zinc-800">Notificações</h2>
                    <p class="text-sm text-zinc-500">Configure alertas por e-mail para eventos do sistema</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @foreach($groups['notifications'] as $setting)
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">{{ $setting->label }}</label>
                        @if($setting->type === 'boolean')
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="settings[{{ $setting->key }}]" value="0">
                                <input type="checkbox" name="settings[{{ $setting->key }}]" value="1"
                                    {{ $setting->value ? 'checked' : '' }}
                                    class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                <span class="ms-3 text-sm text-zinc-500">{{ $setting->description }}</span>
                            </label>
                        @else
                            <input type="text" name="settings[{{ $setting->key }}]"
                                value="{{ $setting->value }}"
                                placeholder="{{ $setting->description }}"
                                class="w-full px-4 py-2.5 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-green-600 focus:border-transparent">
                            <p class="text-xs text-zinc-400 mt-1">{{ $setting->description }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <button type="submit" class="px-6 py-2.5 text-white rounded-lg" style="background: var(--color-primary); transition font-medium">Salvar Configurações</button>
    </div>
</form>

<form id="test-form" method="POST" action="{{ route('admin.settings.test-gateway') }}" class="hidden">@csrf</form>

@endsection

@push('scripts')
<script>
function switchTab(tab) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(b => {
        b.classList.remove('active', 'border-green-700', 'text-green-800');
        b.classList.add('border-transparent', 'text-zinc-500');
    });
    document.getElementById('tab-' + tab).classList.remove('hidden');
    const btn = document.querySelector('[data-tab="' + tab + '"]');
    btn.classList.add('active', 'border-green-700', 'text-green-800');
    btn.classList.remove('border-transparent', 'text-zinc-500');
}
switchTab('gateway');

function toggleGatewayPanels() {
    const active = document.querySelector('input[name="settings[gateway_active]"]:checked')?.value || 'woovi';
    const panelWoovi = document.getElementById('panel-woovi');
    const panelCajupay = document.getElementById('panel-cajupay');
    const badgeWoovi = document.getElementById('badge-woovi');
    const badgeCajupay = document.getElementById('badge-cajupay');

    if (active === 'woovi') {
        panelWoovi.style.opacity = '1';
        panelCajupay.style.opacity = '0.5';
        badgeWoovi.textContent = 'ATIVO';
        badgeWoovi.className = 'text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700';
        badgeCajupay.textContent = 'INATIVO';
        badgeCajupay.className = 'text-xs px-2 py-0.5 rounded-full bg-zinc-100 text-zinc-500';
    } else {
        panelWoovi.style.opacity = '0.5';
        panelCajupay.style.opacity = '1';
        badgeCajupay.textContent = 'ATIVO';
        badgeCajupay.className = 'text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700';
        badgeWoovi.textContent = 'INATIVO';
        badgeWoovi.className = 'text-xs px-2 py-0.5 rounded-full bg-zinc-100 text-zinc-500';
    }
}
toggleGatewayPanels();

function updateSandboxBanner() {
    const on = document.getElementById('wooviSandboxToggle').checked;
    const banner = document.getElementById('woovi-env-banner');
    const label = document.getElementById('woovi-env-label');
    const url = document.getElementById('woovi-env-url');

    if (on) {
        banner.className = 'mb-5 p-4 rounded-lg bg-amber-50 border border-amber-300';
        label.className = 'text-sm font-semibold text-amber-800';
        label.textContent = '🧪 Ambiente de TESTE (Sandbox)';
        url.className = 'text-xs text-amber-600';
        url.textContent = 'api.woovi-sandbox.com — Nenhuma cobranca real sera gerada';
    } else {
        banner.className = 'mb-5 p-4 rounded-lg bg-green-50 border border-green-200';
        label.className = 'text-sm font-semibold text-green-800';
        label.textContent = '🟢 Ambiente de PRODUCAO';
        url.className = 'text-xs text-green-600';
        url.textContent = 'api.openpix.com.br — Cobranças reais ativas';
    }
}

function togglePassword(btn) {
    const input = btn.parentElement.querySelector('input');
    input.type = input.type === 'password' ? 'text' : 'password';
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text);
    const btn = event.target.closest('button');
    const orig = btn.textContent;
    btn.textContent = 'Copiado!';
    setTimeout(() => btn.textContent = orig, 2000);
}
</script>
@endpush
