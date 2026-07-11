<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planos — Dinofy</title>
    <link rel="icon" href="/brand/favicon.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>:root { --color-primary: #2d6a1e; } body { font-family: 'Instrument Sans', sans-serif; }</style>
</head>
<body class="bg-zinc-100 min-h-screen">

<header class="bg-white border-b border-zinc-200">
    <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <img src="/brand/dinofy-icone.png" alt="Dinofy" class="h-10 w-10 rounded-xl">
            <img src="/brand/dinofy-logo.png" alt="Dinofy" class="h-6 object-contain">
        </div>
        <a href="{{ route('login') }}" class="text-sm font-medium text-zinc-600 hover:text-zinc-900 transition">Ja tenho conta &rarr;</a>
    </div>
</header>

<main class="max-w-6xl mx-auto px-6 py-16">
    <div class="text-center mb-12">
        <h1 class="text-3xl font-bold text-zinc-900 mb-3">Escolha o plano ideal para o seu negocio</h1>
        <p class="text-zinc-500 text-lg max-w-xl mx-auto">Plataforma completa de delivery. Sem fidelidade, cancele quando quiser.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($plans as $plan)
            @php
                $popular = $plan->slug === 'pro';
                $features = [
                    'starter' => ['Ate 50 pedidos/dia', $plan->cpu_limit . ' vCPU', $plan->memory_limit . ' RAM', $plan->storage_limit_gb . 'GB disco', 'Suporte por ticket'],
                    'pro' => ['Pedidos ilimitados', $plan->cpu_limit . ' vCPU', $plan->memory_limit . ' RAM', $plan->storage_limit_gb . 'GB disco', 'Suporte prioritario', 'Dominio customizado'],
                    'business' => ['Pedidos ilimitados', $plan->cpu_limit . ' vCPU', $plan->memory_limit . ' RAM', $plan->storage_limit_gb . 'GB disco', 'Suporte premium', 'Dominio customizado', 'Relatorios avancados'],
                    'enterprise' => ['Pedidos ilimitados', $plan->cpu_limit . ' vCPU', $plan->memory_limit . ' RAM', $plan->storage_limit_gb . 'GB disco', 'Suporte dedicado', 'Dominios ilimitados', 'Relatorios avancados', 'SLA 99.9%'],
                ];
                $planFeatures = $features[$plan->slug] ?? [];
            @endphp
            <div class="relative bg-white rounded-2xl shadow-sm p-6 flex flex-col {{ $popular ? 'ring-2 ring-green-600' : 'border border-zinc-200' }}">
                @if($popular)
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-0.5 text-xs font-semibold text-white rounded-full" style="background: var(--color-primary);">POPULAR</div>
                @endif

                <h3 class="text-lg font-bold text-zinc-900 mb-1">{{ $plan->name }}</h3>
                <div class="mb-4">
                    <span class="text-3xl font-bold text-zinc-900">R$ {{ number_format($plan->price_cents / 100, 0, ',', '.') }}</span>
                    <span class="text-zinc-500 text-sm">/mes</span>
                </div>

                <ul class="space-y-2 mb-6 flex-1">
                    @foreach($planFeatures as $feature)
                        <li class="flex items-start gap-2 text-sm text-zinc-600">
                            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" style="color: var(--color-primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            {{ $feature }}
                        </li>
                    @endforeach
                </ul>

                <a href="{{ route('checkout.show', $plan->slug) }}"
                   class="block text-center py-2.5 rounded-lg font-medium text-sm transition {{ $popular ? 'text-white hover:opacity-90' : 'border border-zinc-300 text-zinc-700 hover:bg-zinc-50' }}"
                   style="{{ $popular ? 'background: var(--color-primary);' : '' }}">
                    Comecar agora
                </a>
            </div>
        @endforeach
    </div>
</main>

<footer class="border-t border-zinc-200 mt-16 py-8 text-center text-sm text-zinc-400">
    &copy; {{ date('Y') }} Dinofy. Todos os direitos reservados.
</footer>

</body>
</html>
