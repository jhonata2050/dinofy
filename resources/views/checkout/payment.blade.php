<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento PIX — Dinofy</title>
    <link rel="icon" href="/brand/favicon.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>:root { --color-primary: #2d6a1e; } body { font-family: 'Instrument Sans', sans-serif; }</style>
</head>
<body class="bg-zinc-100 min-h-screen">

<header class="bg-white border-b border-zinc-200">
    <div class="max-w-4xl mx-auto px-6 py-4 flex items-center">
        <img src="/brand/dinofy-icone.png" alt="Dinofy" class="h-10 w-10 rounded-xl mr-3">
        <img src="/brand/dinofy-logo.png" alt="Dinofy" class="h-6 object-contain">
    </div>
</header>

{{-- Stepper --}}
<div class="max-w-lg mx-auto px-6 pt-8">
    <div class="flex items-center justify-center gap-3 text-sm">
        <div class="flex items-center gap-2">
            <span class="w-7 h-7 rounded-full bg-emerald-500 flex items-center justify-center text-white text-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </span>
            <span class="font-medium text-emerald-600">Dados</span>
        </div>
        <div class="w-12 h-px bg-emerald-400"></div>
        <div class="flex items-center gap-2" id="stepPayment">
            <span class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold" style="background: var(--color-primary);">2</span>
            <span class="font-medium text-zinc-900">Pagamento</span>
        </div>
        <div class="w-12 h-px bg-zinc-300" id="stepLine3"></div>
        <div class="flex items-center gap-2" id="stepActive">
            <span class="w-7 h-7 rounded-full bg-zinc-200 flex items-center justify-center text-zinc-500 text-xs font-bold" id="step3Circle">3</span>
            <span class="text-zinc-400" id="step3Text">Ativo</span>
        </div>
    </div>
</div>

<main class="max-w-lg mx-auto px-6 py-8">
    {{-- Status: Aguardando --}}
    <div id="pendingState">
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center" style="background: #e8f5e3;">
                <svg class="w-8 h-8" style="color: var(--color-primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-zinc-900 mb-2">Aguardando pagamento</h1>
            <p class="text-zinc-500">Efetue o pagamento via PIX para ativar sua conta.</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4 pb-4 border-b border-zinc-100">
                <div>
                    <p class="text-xs text-zinc-400 uppercase font-semibold">Plano</p>
                    <p class="font-semibold text-zinc-900">{{ $invoice->tenant->plan->name }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-zinc-400 uppercase font-semibold">Valor</p>
                    <p class="text-xl font-bold text-zinc-900">R$ {{ number_format($invoice->amount_cents / 100, 2, ',', '.') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-5 text-sm">
                <div>
                    <p class="text-xs text-zinc-400 uppercase font-semibold mb-1">Subdominio</p>
                    <p class="text-zinc-700 font-medium">{{ $invoice->tenant->subdomain }}.dinofy.cloud</p>
                </div>
                <div>
                    <p class="text-xs text-zinc-400 uppercase font-semibold mb-1">Vencimento</p>
                    <p class="text-zinc-700 font-medium">{{ $invoice->due_date->format('d/m/Y') }}</p>
                </div>
            </div>

            @if($invoice->pix_copy_paste)
                {{-- QR Code --}}
                @if($invoice->pix_qr_code)
                    <div class="text-center mb-5 p-4 bg-zinc-50 rounded-xl">
                        <p class="text-xs text-zinc-500 mb-3 font-medium">Escaneie o QR Code com seu app bancario</p>
                        <img src="{{ $invoice->pix_qr_code }}" alt="QR Code PIX" class="mx-auto w-52 h-52 rounded-lg border border-zinc-200">
                    </div>
                @endif

                {{-- PIX Copia e Cola --}}
                <div class="mb-5">
                    <p class="text-xs text-zinc-500 mb-2 font-medium">Ou copie o codigo PIX:</p>
                    <div class="flex items-center gap-2">
                        <input type="text" id="pixCode" value="{{ $invoice->pix_copy_paste }}" readonly
                            class="flex-1 px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-lg text-xs text-zinc-600 font-mono truncate">
                        <button onclick="copyPix()" id="copyBtn"
                            class="px-4 py-2.5 text-white rounded-lg text-sm font-medium transition hover:opacity-90 whitespace-nowrap flex items-center gap-1.5"
                            style="background: var(--color-primary);">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <span id="copyText">Copiar</span>
                        </button>
                    </div>
                </div>

                {{-- Polling indicator --}}
                <div class="flex items-center gap-3 p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-700 text-sm">
                    <svg class="animate-spin h-5 w-5 flex-shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <span>A pagina atualiza automaticamente quando o pagamento for confirmado.</span>
                </div>
            @else
                {{-- PIX nao gerado — mostrar erro e retry --}}
                <div class="text-center py-6">
                    <div class="w-14 h-14 rounded-full mx-auto mb-3 flex items-center justify-center bg-amber-50">
                        <svg class="w-7 h-7 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <p class="text-zinc-700 font-medium mb-1">Nao foi possivel gerar o PIX</p>
                    <p class="text-zinc-500 text-sm mb-4">O gateway de pagamento pode estar temporariamente indisponivel.</p>
                    <p class="text-zinc-500 text-sm mb-5">Sua conta foi criada com sucesso. Voce pode tentar gerar o PIX novamente ou acessar o painel do cliente para pagar depois.</p>

                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <button onclick="retryPix()" id="retryBtn"
                            class="px-5 py-2.5 text-white rounded-lg text-sm font-medium transition hover:opacity-90 flex items-center justify-center gap-2"
                            style="background: var(--color-primary);">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Tentar novamente</span>
                        </button>
                        <a href="{{ route('login') }}"
                            class="px-5 py-2.5 bg-zinc-100 text-zinc-700 rounded-lg text-sm font-medium transition hover:bg-zinc-200 text-center">
                            Ir para o login
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Status: Pago --}}
    <div id="paidState" class="hidden">
        <div class="text-center mb-8">
            <div class="w-20 h-20 rounded-full mx-auto mb-4 flex items-center justify-center" style="background: #e8f5e3;">
                <svg class="w-10 h-10" style="color: var(--color-primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-zinc-900 mb-2">Pagamento confirmado!</h1>
            <p class="text-zinc-500">Sua conta esta sendo ativada. Voce recebera um e-mail em breve.</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-6 text-center">
            <div class="mb-4 p-4 bg-emerald-50 rounded-xl border border-emerald-200">
                <p class="text-xs text-emerald-600 font-semibold uppercase mb-1">Seu painel</p>
                <p class="text-lg font-bold text-emerald-700">{{ $invoice->tenant->subdomain }}.dinofy.cloud</p>
            </div>

            <p class="text-sm text-zinc-500 mb-5">O provisionamento pode levar alguns minutos. Use o login abaixo para acessar quando estiver pronto.</p>

            <a href="{{ route('login') }}"
                class="inline-flex items-center gap-2 px-6 py-2.5 text-white rounded-lg font-medium text-sm transition hover:opacity-90"
                style="background: var(--color-primary);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                Acessar meu painel
            </a>
        </div>
    </div>
</main>

<script>
function copyPix() {
    const code = document.getElementById('pixCode');
    if (code) {
        navigator.clipboard.writeText(code.value).then(() => {
            const txt = document.getElementById('copyText');
            txt.textContent = 'Copiado!';
            setTimeout(() => txt.textContent = 'Copiar', 2000);
        });
    }
}

function retryPix() {
    const btn = document.getElementById('retryBtn');
    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg><span>Gerando PIX...</span>';
    setTimeout(() => window.location.reload(), 500);
}

const token = '{{ $invoice->idempotency_key }}';
let checking = false;

async function checkPayment() {
    if (checking) return;
    checking = true;
    try {
        const res = await fetch(`/checkout/payment/${token}/check`);
        const data = await res.json();
        if (data.paid) {
            document.getElementById('pendingState').classList.add('hidden');
            document.getElementById('paidState').classList.remove('hidden');
            // Update stepper
            const s2 = document.getElementById('stepPayment');
            s2.querySelector('span:first-child').className = 'w-7 h-7 rounded-full bg-emerald-500 flex items-center justify-center text-white text-xs';
            s2.querySelector('span:first-child').innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>';
            s2.querySelector('span:last-child').className = 'font-medium text-emerald-600';
            document.getElementById('stepLine3').className = 'w-12 h-px bg-emerald-400';
            document.getElementById('step3Circle').className = 'w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold';
            document.getElementById('step3Circle').style.background = 'var(--color-primary)';
            document.getElementById('step3Circle').textContent = '';
            document.getElementById('step3Circle').innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>';
            document.getElementById('step3Text').className = 'font-medium text-zinc-900';
            document.getElementById('step3Text').textContent = 'Ativo!';
            return;
        }
    } catch (e) {}
    checking = false;
}

@if($invoice->status === 'paid')
    document.getElementById('pendingState').classList.add('hidden');
    document.getElementById('paidState').classList.remove('hidden');
@elseif($invoice->pix_copy_paste)
    setInterval(checkPayment, 10000);
@endif
</script>
</body>
</html>
