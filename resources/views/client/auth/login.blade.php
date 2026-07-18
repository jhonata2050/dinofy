<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Dinofy</title>
    <link rel="icon" href="/brand/favicon.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>:root { --color-primary: #2d6a1e; } body { font-family: 'Instrument Sans', sans-serif; }</style>
</head>
<body class="bg-zinc-100 min-h-screen flex items-center justify-center px-4">

<div class="w-full max-w-sm">
    <div class="text-center mb-8">
        <img src="/brand/dinofy-icone.png" alt="Dinofy" class="h-14 w-14 mx-auto rounded-xl mb-4">
        <img src="/brand/dinofy-logo.png" alt="Dinofy" class="h-7 mx-auto mb-2 object-contain">
        <p class="text-zinc-500 text-sm">Acesse seu painel de cliente</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-8">
        @if(session('success'))
            <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-lg flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4" id="loginForm">
            @csrf
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">E-mail</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </span>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                        placeholder="voce@exemplo.com"
                        class="w-full pl-9 pr-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent transition">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Senha</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </span>
                    <input type="password" name="password" required id="passwordInput"
                        placeholder="Sua senha"
                        class="w-full pl-9 pr-10 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent transition">
                    <button type="button" onclick="togglePassword()" class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600 transition">
                        <svg id="eyeIcon" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <svg id="eyeOffIcon" class="w-4 h-4 hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                    </button>
                </div>
            </div>
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded border-zinc-300 text-green-600 focus:ring-green-600">
                    <span class="text-sm text-zinc-600">Lembrar-me</span>
                </label>
                <a href="{{ route('client.password.request') }}" class="text-sm hover:underline" style="color: var(--color-primary);">Esqueceu a senha?</a>
            </div>
            <button type="submit" id="loginBtn" class="w-full py-2.5 text-white rounded-lg font-medium text-sm transition hover:opacity-90 flex items-center justify-center gap-2" style="background: var(--color-primary);">
                <span id="loginText">Entrar</span>
                <svg id="loginSpinner" class="hidden animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
            </button>
        </form>
    </div>

    <div class="mt-6 text-center">
        <p class="text-sm text-zinc-500">Ainda nao tem conta?</p>
        <a href="{{ route('checkout.index') }}" class="inline-flex items-center gap-1.5 mt-2 text-sm font-medium transition hover:opacity-80" style="color: var(--color-primary);">
            Criar conta agora
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
        </a>
    </div>

    <div class="mt-4 text-center">
        <a href="{{ route('admin.login') }}" class="text-xs text-zinc-400 hover:text-zinc-500 transition">Administrador? Acesse aqui</a>
    </div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('passwordInput');
    const eyeOn = document.getElementById('eyeIcon');
    const eyeOff = document.getElementById('eyeOffIcon');
    if (input.type === 'password') {
        input.type = 'text';
        eyeOn.classList.add('hidden');
        eyeOff.classList.remove('hidden');
    } else {
        input.type = 'password';
        eyeOn.classList.remove('hidden');
        eyeOff.classList.add('hidden');
    }
}

document.getElementById('loginForm').addEventListener('submit', function() {
    document.getElementById('loginBtn').disabled = true;
    document.getElementById('loginText').textContent = 'Entrando...';
    document.getElementById('loginSpinner').classList.remove('hidden');
});
</script>
</body>
</html>
