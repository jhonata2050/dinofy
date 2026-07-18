<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha — Dinofy</title>
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
        <p class="text-zinc-500 text-sm">Informe seu e-mail para receber o link de redefinição</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-8">
        @if(session('status'))
            <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-lg flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('client.password.email') }}" class="space-y-4">
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
            <button type="submit" class="w-full py-2.5 text-white rounded-lg font-medium text-sm transition hover:opacity-90" style="background: var(--color-primary);">Enviar Link</button>
        </form>
    </div>

    <div class="mt-6 text-center">
        <a href="{{ route('client.login') }}" class="inline-flex items-center gap-1.5 text-sm font-medium transition hover:opacity-80" style="color: var(--color-primary);">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 17l-5-5m0 0l5-5m-5 5h12"/></svg>
            Voltar ao login
        </a>
    </div>
</div>

</body>
</html>
