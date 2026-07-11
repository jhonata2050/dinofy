<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') — Dinofy</title>
    <link rel="icon" href="/brand/favicon.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>:root{--color-primary:#2d6a1e}body{font-family:'Instrument Sans',sans-serif}</style>
</head>
<body class="bg-zinc-100 min-h-screen flex items-center justify-center px-4">

<div class="w-full max-w-md text-center">
    <div class="mb-6">
        <img src="/brand/dinofy-icone.png" alt="Dinofy" class="h-14 w-14 mx-auto rounded-xl mb-4" onerror="this.style.display='none'">
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-8">
        <div class="w-16 h-16 mx-auto mb-5 rounded-full flex items-center justify-center @yield('icon-bg', 'bg-red-100')">
            @yield('icon')
        </div>

        <h1 class="text-5xl font-bold text-zinc-900 mb-2">@yield('code')</h1>
        <h2 class="text-lg font-semibold text-zinc-700 mb-3">@yield('title')</h2>
        <p class="text-sm text-zinc-500 mb-6">@yield('message')</p>

        <div class="flex items-center justify-center gap-3">
            <a href="/"
                class="px-5 py-2.5 text-white rounded-lg font-medium text-sm transition hover:opacity-90"
                style="background:var(--color-primary);">
                Pagina inicial
            </a>
            <button onclick="history.back()"
                class="px-5 py-2.5 bg-white border border-zinc-300 text-zinc-700 rounded-lg font-medium text-sm hover:bg-zinc-50 transition">
                Voltar
            </button>
        </div>
    </div>

    <p class="mt-6 text-xs text-zinc-400">&copy; {{ date('Y') }} Dinofy</p>
</div>

</body>
</html>
