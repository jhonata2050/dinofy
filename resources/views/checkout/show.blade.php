<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro — {{ $plan->name }} — Dinofy</title>
    <link rel="icon" href="/brand/favicon.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>:root { --color-primary: #2d6a1e; } body { font-family: 'Instrument Sans', sans-serif; }</style>
</head>
<body class="bg-zinc-100 min-h-screen">

<header class="bg-white border-b border-zinc-200">
    <div class="max-w-4xl mx-auto px-6 py-4 flex items-center justify-between">
        <a href="{{ route('checkout.index') }}" class="flex items-center gap-3">
            <img src="/brand/dinofy-icone.png" alt="Dinofy" class="h-10 w-10 rounded-xl">
            <img src="/brand/dinofy-logo.png" alt="Dinofy" class="h-6 object-contain">
        </a>
        <a href="{{ route('checkout.index') }}" class="text-sm text-zinc-500 hover:text-zinc-700 transition">&larr; Voltar aos planos</a>
    </div>
</header>

{{-- Stepper --}}
<div class="max-w-4xl mx-auto px-6 pt-8">
    <div class="flex items-center justify-center gap-3 text-sm">
        <div class="flex items-center gap-2">
            <span class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold" style="background: var(--color-primary);">1</span>
            <span class="font-medium text-zinc-900">Dados</span>
        </div>
        <div class="w-12 h-px bg-zinc-300"></div>
        <div class="flex items-center gap-2">
            <span class="w-7 h-7 rounded-full bg-zinc-200 flex items-center justify-center text-zinc-500 text-xs font-bold">2</span>
            <span class="text-zinc-400">Pagamento</span>
        </div>
        <div class="w-12 h-px bg-zinc-300"></div>
        <div class="flex items-center gap-2">
            <span class="w-7 h-7 rounded-full bg-zinc-200 flex items-center justify-center text-zinc-500 text-xs font-bold">3</span>
            <span class="text-zinc-400">Ativo</span>
        </div>
    </div>
</div>

<main class="max-w-4xl mx-auto px-6 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
        {{-- Formulario --}}
        <div class="lg:col-span-3">
            <h1 class="text-2xl font-bold text-zinc-900 mb-6">Criar sua conta</h1>

            <div class="bg-white rounded-2xl shadow-sm p-6">
                @if($errors->any())
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg">
                        <ul class="list-disc pl-4 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('checkout.process', $plan->slug) }}" class="space-y-4" id="checkoutForm">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-zinc-700 mb-1">Nome completo <span class="text-red-400">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                placeholder="Seu nome completo"
                                class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent transition">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-zinc-700 mb-1">E-mail <span class="text-red-400">*</span></label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                placeholder="voce@exemplo.com"
                                class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent transition">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-zinc-700 mb-1">Telefone <span class="text-red-400">*</span></label>
                            <input type="text" name="phone" id="phoneInput" value="{{ old('phone') }}" required placeholder="(00) 00000-0000"
                                maxlength="15"
                                class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent transition">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-zinc-700 mb-1">CPF / CNPJ <span class="text-red-400">*</span></label>
                            <input type="text" name="document" id="documentInput" value="{{ old('document') }}" required
                                placeholder="000.000.000-00"
                                maxlength="18"
                                class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent transition">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-zinc-700 mb-1">Subdominio <span class="text-red-400">*</span></label>
                            <div class="flex items-center">
                                <div class="relative flex-1">
                                    <input type="text" name="subdomain" id="subdomainInput" value="{{ old('subdomain') }}" required placeholder="meu-restaurante"
                                        autocomplete="off" minlength="5" maxlength="32"
                                        class="w-full px-3 py-2.5 pr-9 border border-zinc-300 rounded-l-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent lowercase transition">
                                    <div id="subdomainIcon" class="absolute right-2.5 top-1/2 -translate-y-1/2 hidden"></div>
                                </div>
                                <span class="px-3 py-2.5 bg-zinc-100 border border-l-0 border-zinc-300 rounded-r-lg text-sm text-zinc-500 whitespace-nowrap">.dinofy.app</span>
                            </div>

                            <div id="subdomainFeedback" class="mt-1.5 text-sm hidden"></div>

                            <div id="subdomainSuggestions" class="mt-2 hidden">
                                <p class="text-xs text-zinc-500 mb-1.5">Sugestoes disponiveis:</p>
                                <div id="suggestionsList" class="flex flex-wrap gap-1.5"></div>
                            </div>

                            <div class="mt-2 flex items-start gap-2 p-2.5 bg-blue-50 border border-blue-200 rounded-lg">
                                <svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p class="text-xs text-blue-700">Deseja usar seu proprio dominio? (ex: meurestaurante.com.br) Apos ativar sua conta, abra um <strong>ticket de suporte</strong> na area do cliente e nossa equipe configura para voce sem custo adicional.</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-zinc-700 mb-1">Senha <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <input type="password" name="password" id="passwordInput" required minlength="8"
                                    placeholder="Minimo 8 caracteres"
                                    class="w-full px-3 py-2.5 pr-10 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent transition">
                                <button type="button" onclick="togglePwd('passwordInput')" class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600">
                                    <svg class="w-4 h-4 eye-on" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg class="w-4 h-4 eye-off hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                </button>
                            </div>
                            <div id="passwordStrength" class="mt-2 hidden">
                                <div class="flex gap-1">
                                    <div class="h-1 flex-1 rounded-full bg-zinc-200 transition-colors" id="str1"></div>
                                    <div class="h-1 flex-1 rounded-full bg-zinc-200 transition-colors" id="str2"></div>
                                    <div class="h-1 flex-1 rounded-full bg-zinc-200 transition-colors" id="str3"></div>
                                    <div class="h-1 flex-1 rounded-full bg-zinc-200 transition-colors" id="str4"></div>
                                </div>
                                <p id="strengthText" class="text-xs mt-1 text-zinc-400"></p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-zinc-700 mb-1">Confirmar senha <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <input type="password" name="password_confirmation" id="passwordConfirm" required minlength="8"
                                    placeholder="Repita a senha"
                                    class="w-full px-3 py-2.5 pr-10 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent transition">
                                <button type="button" onclick="togglePwd('passwordConfirm')" class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600">
                                    <svg class="w-4 h-4 eye-on" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg class="w-4 h-4 eye-off hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                </button>
                            </div>
                            <p id="matchFeedback" class="text-xs mt-1 hidden"></p>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" id="submitBtn"
                            class="w-full py-3 text-white rounded-lg font-medium text-sm transition hover:opacity-90 flex items-center justify-center gap-2"
                            style="background: var(--color-primary);">
                            <span id="submitText">Continuar para pagamento</span>
                            <svg id="submitSpinner" class="hidden animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        </button>
                    </div>

                    <p class="text-center text-xs text-zinc-400 mt-2">
                        Ja tem conta? <a href="{{ route('login') }}" class="font-medium hover:underline" style="color: var(--color-primary);">Fazer login</a>
                    </p>
                </form>
            </div>
        </div>

        {{-- Resumo do plano --}}
        <div class="lg:col-span-2">
            <h2 class="text-lg font-bold text-zinc-900 mb-4">Resumo</h2>

            <div class="bg-white rounded-2xl shadow-sm p-6 sticky top-6">
                <div class="flex items-center justify-between mb-4 pb-4 border-b border-zinc-100">
                    <div>
                        <h3 class="font-semibold text-zinc-900">{{ $plan->name }}</h3>
                        <p class="text-sm text-zinc-500">Mensal</p>
                    </div>
                    <div class="text-right">
                        <span class="text-2xl font-bold text-zinc-900">R$ {{ number_format($plan->price_cents / 100, 0, ',', '.') }}</span>
                        <span class="text-zinc-500 text-sm">/mes</span>
                    </div>
                </div>

                <ul class="space-y-2 mb-4 text-sm text-zinc-600">
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" style="color: var(--color-primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ $plan->cpu_limit }} vCPU
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" style="color: var(--color-primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ $plan->memory_limit }} RAM
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" style="color: var(--color-primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ $plan->storage_limit_gb }}GB armazenamento
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" style="color: var(--color-primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Subdominio gratuito
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" style="color: var(--color-primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Suporte via ticket
                    </li>
                </ul>

                <div class="flex items-center justify-between pt-4 border-t border-zinc-100 font-semibold text-zinc-900">
                    <span>Total hoje</span>
                    <span>R$ {{ number_format($plan->price_cents / 100, 2, ',', '.') }}</span>
                </div>

                <div class="mt-3 flex items-center gap-2 text-xs text-zinc-400">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Pagamento seguro via PIX. Sem fidelidade.
                </div>
            </div>

            {{-- Trocar plano --}}
            <div class="mt-4">
                <p class="text-sm text-zinc-500 mb-2">Trocar plano:</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($plans as $p)
                        @if($p->id !== $plan->id)
                            <a href="{{ route('checkout.show', $p->slug) }}"
                               class="px-3 py-1.5 text-xs font-medium border border-zinc-300 rounded-lg text-zinc-600 hover:bg-zinc-50 transition">
                                {{ $p->name }} — R$ {{ number_format($p->price_cents / 100, 0, ',', '.') }}
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</main>

<script>
// ── Input masks ──
const phoneInput = document.getElementById('phoneInput');
const docInput = document.getElementById('documentInput');

phoneInput.addEventListener('input', function() {
    let v = this.value.replace(/\D/g, '').slice(0, 11);
    if (v.length > 6) v = '(' + v.slice(0,2) + ') ' + v.slice(2,7) + '-' + v.slice(7);
    else if (v.length > 2) v = '(' + v.slice(0,2) + ') ' + v.slice(2);
    else if (v.length > 0) v = '(' + v;
    this.value = v;
});

docInput.addEventListener('input', function() {
    let v = this.value.replace(/\D/g, '');
    if (v.length <= 11) {
        if (v.length > 9) v = v.slice(0,3) + '.' + v.slice(3,6) + '.' + v.slice(6,9) + '-' + v.slice(9);
        else if (v.length > 6) v = v.slice(0,3) + '.' + v.slice(3,6) + '.' + v.slice(6);
        else if (v.length > 3) v = v.slice(0,3) + '.' + v.slice(3);
    } else {
        v = v.slice(0, 14);
        if (v.length > 12) v = v.slice(0,2) + '.' + v.slice(2,5) + '.' + v.slice(5,8) + '/' + v.slice(8,12) + '-' + v.slice(12);
        else if (v.length > 8) v = v.slice(0,2) + '.' + v.slice(2,5) + '.' + v.slice(5,8) + '/' + v.slice(8);
        else if (v.length > 5) v = v.slice(0,2) + '.' + v.slice(2,5) + '.' + v.slice(5);
        else if (v.length > 2) v = v.slice(0,2) + '.' + v.slice(2);
    }
    this.value = v;
});

// ── Password toggle ──
function togglePwd(id) {
    const el = document.getElementById(id);
    const btn = el.nextElementSibling;
    if (el.type === 'password') {
        el.type = 'text';
        btn.querySelector('.eye-on').classList.add('hidden');
        btn.querySelector('.eye-off').classList.remove('hidden');
    } else {
        el.type = 'password';
        btn.querySelector('.eye-on').classList.remove('hidden');
        btn.querySelector('.eye-off').classList.add('hidden');
    }
}

// ── Password strength ──
const pwdInput = document.getElementById('passwordInput');
const pwdConfirm = document.getElementById('passwordConfirm');
const strengthWrap = document.getElementById('passwordStrength');
const bars = [document.getElementById('str1'), document.getElementById('str2'), document.getElementById('str3'), document.getElementById('str4')];
const strengthText = document.getElementById('strengthText');
const matchFb = document.getElementById('matchFeedback');

const levels = [
    { min: 0, color: 'bg-red-400', text: 'Muito fraca', textColor: 'text-red-500' },
    { min: 1, color: 'bg-orange-400', text: 'Fraca', textColor: 'text-orange-500' },
    { min: 2, color: 'bg-amber-400', text: 'Razoavel', textColor: 'text-amber-500' },
    { min: 3, color: 'bg-green-400', text: 'Boa', textColor: 'text-green-600' },
    { min: 4, color: 'bg-emerald-500', text: 'Forte', textColor: 'text-emerald-600' },
];

function calcStrength(pw) {
    let s = 0;
    if (pw.length >= 8) s++;
    if (/[a-z]/.test(pw) && /[A-Z]/.test(pw)) s++;
    if (/\d/.test(pw)) s++;
    if (/[^a-zA-Z0-9]/.test(pw)) s++;
    return s;
}

pwdInput.addEventListener('input', function() {
    const v = this.value;
    if (!v) { strengthWrap.classList.add('hidden'); return; }
    strengthWrap.classList.remove('hidden');
    const s = calcStrength(v);
    const lvl = levels[s];
    bars.forEach((b, i) => {
        b.className = 'h-1 flex-1 rounded-full transition-colors ' + (i < s + (s === 0 && v.length > 0 ? 1 : 0) ? lvl.color : 'bg-zinc-200');
    });
    strengthText.textContent = lvl.text;
    strengthText.className = 'text-xs mt-1 ' + lvl.textColor;
    checkMatch();
});

pwdConfirm.addEventListener('input', checkMatch);

function checkMatch() {
    const pw = pwdInput.value, pc = pwdConfirm.value;
    if (!pc) { matchFb.classList.add('hidden'); return; }
    matchFb.classList.remove('hidden');
    if (pw === pc) {
        matchFb.textContent = 'Senhas conferem';
        matchFb.className = 'text-xs mt-1 text-green-600';
    } else {
        matchFb.textContent = 'Senhas nao conferem';
        matchFb.className = 'text-xs mt-1 text-red-500';
    }
}

// ── Subdomain validation ──
const input = document.getElementById('subdomainInput');
const icon = document.getElementById('subdomainIcon');
const feedback = document.getElementById('subdomainFeedback');
const suggestionsWrap = document.getElementById('subdomainSuggestions');
const suggestionsList = document.getElementById('suggestionsList');
const form = document.getElementById('checkoutForm');
const submitBtn = document.getElementById('submitBtn');

let debounceTimer = null;
let subdomainValid = false;

const spinnerSvg = '<svg class="w-4 h-4 animate-spin text-zinc-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>';
const checkSvg = '<svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
const xSvg = '<svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';

input.addEventListener('input', function() {
    this.value = this.value.toLowerCase().replace(/[^a-z0-9\-]/g, '');
    clearTimeout(debounceTimer);
    subdomainValid = false;
    const val = this.value.trim();
    if (val.length === 0) { hideAll(); resetBorder(); return; }
    if (val.length < 5) {
        showFeedback('Minimo de 5 caracteres.', 'text-zinc-400');
        icon.innerHTML = ''; icon.classList.add('hidden');
        resetBorder(); suggestionsWrap.classList.add('hidden');
        return;
    }
    icon.innerHTML = spinnerSvg; icon.classList.remove('hidden');
    debounceTimer = setTimeout(() => checkSubdomain(val), 400);
});

async function checkSubdomain(val) {
    try {
        const res = await fetch('{{ route("checkout.check-subdomain") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify({ subdomain: val }),
        });
        const data = await res.json();
        if (data.available) {
            subdomainValid = true;
            icon.innerHTML = checkSvg;
            showFeedback(data.message, 'text-green-600');
            input.classList.remove('border-red-400'); input.classList.add('border-green-400');
            suggestionsWrap.classList.add('hidden');
        } else {
            subdomainValid = false;
            icon.innerHTML = xSvg;
            showFeedback(data.message, 'text-red-500');
            input.classList.remove('border-green-400'); input.classList.add('border-red-400');
            if (data.suggestions && data.suggestions.length > 0) renderSuggestions(data.suggestions);
            else suggestionsWrap.classList.add('hidden');
        }
        icon.classList.remove('hidden');
    } catch (e) { icon.classList.add('hidden'); feedback.classList.add('hidden'); }
}

function showFeedback(msg, cls) { feedback.textContent = msg; feedback.className = 'mt-1.5 text-sm ' + cls; feedback.classList.remove('hidden'); }
function hideAll() { feedback.classList.add('hidden'); icon.classList.add('hidden'); suggestionsWrap.classList.add('hidden'); }
function resetBorder() { input.classList.remove('border-green-400', 'border-red-400'); }

function renderSuggestions(items) {
    suggestionsList.innerHTML = '';
    items.forEach(s => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'px-2.5 py-1 text-xs font-medium border border-zinc-300 rounded-lg text-zinc-600 hover:bg-green-50 hover:border-green-400 hover:text-green-700 transition cursor-pointer';
        btn.textContent = s + '.dinofy.app';
        btn.addEventListener('click', () => { input.value = s; subdomainValid = false; hideAll(); resetBorder(); icon.innerHTML = spinnerSvg; icon.classList.remove('hidden'); checkSubdomain(s); });
        suggestionsList.appendChild(btn);
    });
    suggestionsWrap.classList.remove('hidden');
}

form.addEventListener('submit', function(e) {
    if (!subdomainValid) {
        e.preventDefault(); input.focus();
        if (!feedback.classList.contains('hidden') && feedback.classList.contains('text-red-500')) return;
        showFeedback('Verifique a disponibilidade do subdominio antes de continuar.', 'text-amber-600');
        return;
    }
    if (pwdInput.value !== pwdConfirm.value) {
        e.preventDefault(); pwdConfirm.focus();
        return;
    }
    submitBtn.disabled = true;
    document.getElementById('submitText').textContent = 'Processando...';
    document.getElementById('submitSpinner').classList.remove('hidden');
});
</script>
</body>
</html>
