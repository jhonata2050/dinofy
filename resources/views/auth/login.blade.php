<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Dinofy Master</title>
    <link rel="icon" href="/brand/favicon.png" type="image/png" sizes="32x32">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { fontFamily: { sans: ['Instrument Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'] } } }
        }
    </script>
    <style>:root { --color-primary: #2d6a1e; }</style>
</head>
<body class="bg-zinc-100 min-h-screen flex items-center justify-center font-sans text-zinc-900 antialiased">
    <div class="bg-white shadow-sm rounded-2xl p-8 w-full max-w-md">
        <div class="text-center mb-6">
            <img src="/brand/dinofy-icone.png" alt="Dinofy" class="h-14 w-14 rounded-xl mx-auto mb-3">
            <img src="/brand/dinofy-logo.png" alt="Dinofy" class="h-7 mx-auto mb-1">
            <p class="text-zinc-500 text-sm">Painel de administração SaaS</p>
        </div>

        @if($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">E-mail</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full px-4 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent transition">
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Senha</label>
                <input type="password" name="password" required class="w-full px-4 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent transition">
            </div>
            <div class="flex items-center">
                <input type="checkbox" name="remember" id="remember" class="rounded border-zinc-300 text-green-600 focus:ring-green-600">
                <label for="remember" class="ml-2 text-sm text-zinc-600">Lembrar-me</label>
            </div>
            <button type="submit" class="w-full py-2.5 px-4 text-white font-medium rounded-lg text-sm transition" style="background: var(--color-primary);" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">Entrar</button>
        </form>
    </div>
</body>
</html>
