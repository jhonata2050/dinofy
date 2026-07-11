<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Master') — Dinofy Admin</title>
    <link rel="icon" href="/brand/favicon.png" type="image/png" sizes="32x32">
    <link rel="shortcut icon" href="/brand/favicon.png" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Instrument Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#f0fdf0', 100: '#dcfce7', 200: '#bbf7d0', 300: '#86efac',
                            400: '#4ade80', 500: '#2d6a1e', 600: '#256016', 700: '#1e4f12',
                            800: '#166534', 900: '#14532d'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        :root { --color-primary: #2d6a1e; }
        [x-cloak] { display: none !important; }
        * { scrollbar-width: thin; scrollbar-color: rgb(161 161 170) transparent; }
        *::-webkit-scrollbar { width: 8px; height: 8px; }
        *::-webkit-scrollbar-track { background: transparent; }
        *::-webkit-scrollbar-thumb { background: rgb(212 212 216); border-radius: 9999px; }
        *::-webkit-scrollbar-thumb:hover { background: rgb(161 161 170); }

        .menu-item {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
            gap: 0.75rem;
            padding: 0.625rem 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.5rem;
            transition: all 150ms ease;
        }
        .menu-item-active {
            background: rgb(228 228 231); /* zinc-200 */
            font-weight: 600;
            color: rgb(24 24 27); /* zinc-900 */
        }
        .menu-item-active::before {
            content: '';
            position: absolute;
            left: -0.75rem;
            top: 0;
            bottom: 0;
            width: 0.375rem;
            border-radius: 0 9999px 9999px 0;
            background: var(--color-primary);
        }
        .menu-item-inactive {
            color: rgb(82 82 91); /* zinc-600 */
        }
        .menu-item-inactive:hover {
            background: rgb(244 244 245); /* zinc-100 */
            color: rgb(39 39 42); /* zinc-800 */
        }
        .menu-item-icon-active { color: rgb(63 63 70); /* zinc-700 */ }
        .menu-item-icon-inactive { color: rgb(113 113 122); /* zinc-500 */ }
    </style>
</head>
<body class="bg-zinc-100 min-h-screen font-sans text-zinc-900 antialiased">

<div class="flex min-h-screen">
    {{-- Sidebar --}}
    <aside class="fixed left-0 top-0 z-50 flex h-screen w-[260px] flex-col rounded-r-2xl bg-zinc-100">
        <div class="flex items-center gap-2 px-4 py-5">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 text-zinc-900">
                <img src="/brand/dinofy-icone.png" alt="Dinofy" class="h-8 w-8 rounded-lg">
                <img src="/brand/dinofy-logo.png" alt="Dinofy Master" class="h-6 object-contain">
            </a>
        </div>
        <hr class="mx-3 border-t border-zinc-200" />

        <nav class="flex-1 overflow-y-auto px-3 py-4">
            <ul class="flex flex-col gap-1">
                <li>
                    <a href="{{ route('admin.dashboard') }}" class="menu-item {{ request()->routeIs('admin.dashboard') ? 'menu-item-active' : 'menu-item-inactive' }}">
                        <span class="{{ request()->routeIs('admin.dashboard') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        </span>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.tenants.index') }}" class="menu-item {{ request()->routeIs('admin.tenants.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                        <span class="{{ request()->routeIs('admin.tenants.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </span>
                        Clientes
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.plans.index') }}" class="menu-item {{ request()->routeIs('admin.plans.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                        <span class="{{ request()->routeIs('admin.plans.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </span>
                        Planos
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.invoices.index') }}" class="menu-item {{ request()->routeIs('admin.invoices.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                        <span class="{{ request()->routeIs('admin.invoices.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                        </span>
                        Faturas
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.tickets.index') }}" class="menu-item {{ request()->routeIs('admin.tickets.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                        <span class="{{ request()->routeIs('admin.tickets.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                        </span>
                        Tickets
                    </a>
                </li>

                <li><hr class="my-2 border-t border-zinc-200" /></li>

                <li>
                    <a href="{{ route('admin.update.index') }}" class="menu-item {{ request()->routeIs('admin.update.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                        <span class="{{ request()->routeIs('admin.update.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </span>
                        Atualização
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.settings.index') }}" class="menu-item {{ request()->routeIs('admin.settings.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                        <span class="{{ request()->routeIs('admin.settings.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </span>
                        Configurações
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.activity-logs.index') }}" class="menu-item {{ request()->routeIs('admin.activity-logs.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                        <span class="{{ request()->routeIs('admin.activity-logs.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        </span>
                        Logs de Atividade
                    </a>
                </li>
            </ul>
        </nav>

        <div class="px-3 py-4 border-t border-zinc-200">
            <div class="flex items-center justify-between rounded-lg px-3 py-2">
                <div class="min-w-0">
                    <div class="truncate text-sm font-medium text-zinc-900">{{ auth()->user()->name }}</div>
                    <div class="truncate text-xs text-zinc-500">{{ auth()->user()->email }}</div>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="flex h-8 w-8 items-center justify-center rounded-lg text-zinc-500 transition-colors hover:bg-zinc-200 hover:text-zinc-700" title="Sair">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Content --}}
    <div class="flex min-h-screen flex-1 flex-col p-4 lg:p-6 ml-[260px]">
        <div class="flex w-full shrink-0 flex-col gap-2 mb-4">
            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center gap-3 text-sm">
                    <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl flex items-center gap-3 text-sm">
                    <svg class="w-5 h-5 text-red-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <div class="flex min-h-0 flex-1 flex-col overflow-hidden rounded-2xl bg-white shadow-sm">
            <main class="flex-1 min-w-0 overflow-y-auto px-4 pb-8 pt-4 md:px-6 md:pt-6">
                <div class="mx-auto w-full max-w-7xl">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
</div>

@stack('scripts')
</body>
</html>
