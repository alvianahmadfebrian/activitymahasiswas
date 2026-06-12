@php
    $user = auth()->user();

    $navItems = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => '⌂', 'match' => 'dashboard'],
        ['label' => 'Tugas', 'route' => 'tasks.index', 'icon' => '✓', 'match' => 'tasks.*'],
        ['label' => 'Drive', 'route' => 'drive.index', 'icon' => '▣', 'match' => 'drive.*'],
        ['label' => 'Diskusi', 'route' => 'discussions.index', 'icon' => '◉', 'match' => 'discussions.*'],
        ['label' => 'Riwayat', 'route' => 'activity.index', 'icon' => '↻', 'match' => 'activity.*'],
        ['label' => 'Lunox', 'route' => 'chatbot.index', 'icon' => '✦', 'match' => 'chatbot.*'],
    ];
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'CampusHub')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        tailwind = window.tailwind || {};
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif']
                    },
                    boxShadow: {
                        soft: '0 16px 50px rgba(15, 23, 42, .07)',
                        glow: '0 14px 40px rgba(37, 99, 235, .16)'
                    }
                }
            }
        }
    </script>

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            min-height: 100vh;
        }

        .app-bg {
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, .10), transparent 30rem),
                radial-gradient(circle at top right, rgba(14, 165, 233, .08), transparent 28rem),
                linear-gradient(135deg, #f8fafc 0%, #eef4ff 48%, #f8fafc 100%);
            background-attachment: fixed;
        }

        .glass-card {
            background: rgba(255, 255, 255, .86);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }

        .sidebar-bg {
            background: rgba(255, 255, 255, .92);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .topbar-glass {
            background: rgba(255, 255, 255, .82);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .dropdown-glass {
            background: rgba(255, 255, 255, .74);
            backdrop-filter: blur(24px) saturate(170%);
            -webkit-backdrop-filter: blur(24px) saturate(170%);
            border: 1px solid rgba(255, 255, 255, .75);
            box-shadow: 0 24px 70px rgba(15, 23, 42, .14);
        }

        .sidebar-scroll::-webkit-scrollbar,
        .main-scroll::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .sidebar-scroll::-webkit-scrollbar-thumb,
        .main-scroll::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, .30);
            border-radius: 999px;
        }

        .sidebar-scroll::-webkit-scrollbar-track,
        .main-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .nav-active {
            background: #2563eb;
            color: #ffffff;
            box-shadow: 0 14px 34px rgba(37, 99, 235, .20);
        }

        .nav-inactive {
            color: #475569;
            background: transparent;
        }

        .nav-icon-active {
            background: rgba(255, 255, 255, .18);
            color: #ffffff;
        }

        .nav-icon-inactive {
            background: #eff6ff;
            color: #2563eb;
        }

        .mobile-backdrop {
            background: rgba(15, 23, 42, .45);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }

        .mobile-panel {
            background: rgba(255, 255, 255, .96);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .page-container {
            min-height: calc(100vh - 76px);
        }
    </style>
</head>

<body class="app-bg min-h-screen text-slate-900 antialiased">
@if($user)
    <div class="min-h-screen lg:grid lg:grid-cols-[248px_1fr]">

        {{-- DESKTOP SIDEBAR --}}
        <aside class="sidebar-bg fixed inset-y-0 left-0 z-30 hidden w-[248px] flex-col border-r border-slate-200/80 lg:flex">
            <div class="border-b border-slate-200/80 px-5 py-5">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-blue-600 text-base font-black text-white shadow-glow">
                        CH
                    </div>

                    <div class="min-w-0">
                        <div class="truncate text-lg font-black tracking-tight text-slate-950">
                            CampusHub
                        </div>
                        <div class="truncate text-xs font-semibold text-slate-500">
                            Student workspace
                        </div>
                    </div>
                </a>
            </div>

            <nav class="sidebar-scroll flex-1 overflow-y-auto px-3 py-4">
                <div class="px-3 pb-3 text-[10px] font-black uppercase tracking-[.20em] text-slate-400">
                    Menu
                </div>

                <div class="space-y-1.5">
                    @foreach($navItems as $item)
                        @php $active = request()->routeIs($item['match']); @endphp

                        <a
                            href="{{ route($item['route']) }}"
                            class="{{ $active ? 'nav-active' : 'nav-inactive' }} flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-bold"
                        >
                            <span class="{{ $active ? 'nav-icon-active' : 'nav-icon-inactive' }} flex h-9 w-9 shrink-0 items-center justify-center rounded-xl">
                                {{ $item['icon'] }}
                            </span>

                            <span class="truncate">
                                {{ $item['label'] }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </nav>

            <div class="border-t border-slate-200/80 p-4">
                <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-4">
                    <div class="text-xs font-black uppercase tracking-[.16em] text-blue-500">
                        Assistant
                    </div>

                    <div class="mt-1 text-sm font-black text-slate-900">
                        Lunox
                    </div>

                    <p class="mt-1 text-xs leading-relaxed text-slate-500">
                        Siap membantu tugas, file, diskusi, dan belajar.
                    </p>

                    <a
                        href="{{ route('chatbot.index') }}"
                        class="mt-3 flex w-full items-center justify-center rounded-xl bg-blue-600 px-3 py-2.5 text-sm font-black text-white shadow-glow"
                    >
                        Tanya Lunox
                    </a>
                </div>
            </div>
        </aside>

        {{-- MAIN WRAPPER --}}
        <div class="min-h-screen lg:col-start-2">
            <header class="topbar-glass sticky top-0 z-20 border-b border-white/70">
                <div class="flex items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                    <div class="flex min-w-0 items-center gap-3">
                        <button
                            type="button"
                            onclick="document.getElementById('mobileNav').classList.remove('hidden')"
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-950 text-white lg:hidden"
                        >
                            ☰
                        </button>

                        <div class="min-w-0">
                            <p class="truncate text-xs font-black uppercase tracking-[.22em] text-blue-600">
                                @yield('eyebrow', 'CampusHub')
                            </p>

                            <h1 class="truncate text-xl font-black tracking-tight text-slate-950 sm:text-2xl">
                                @yield('page-title', 'Dashboard')
                            </h1>
                        </div>
                    </div>

                    <div class="relative flex shrink-0 items-center gap-3">
                        <a
                            href="{{ route('chatbot.index') }}"
                            class="hidden rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-black text-white shadow-glow sm:inline-flex"
                        >
                            Tanya Lunox
                        </a>

                        <button
                            type="button"
                            id="profileDropdownBtn"
                            class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white/80 px-3 py-2.5 text-sm font-bold text-slate-700"
                        >
                            @if(!empty($user->photo_url))
                                <img src="{{ $user->photo_url }}" class="h-8 w-8 rounded-xl object-cover" alt="Foto profil">
                            @else
                                <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-blue-600 text-xs font-black text-white">
                                    {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                                </span>
                            @endif

                            <span class="hidden max-w-[120px] truncate sm:block">
                                {{ $user->name }}
                            </span>

                            <span class="text-slate-400">
                                ▾
                            </span>
                        </button>

                        <div
                            id="profileDropdown"
                            class="dropdown-glass hidden absolute right-0 top-[calc(100%+12px)] z-50 w-72 overflow-hidden rounded-[24px] p-3"
                        >
                            <div class="rounded-[20px] bg-white/60 p-4">
                                <div class="flex items-center gap-3">
                                    @if(!empty($user->photo_url))
                                        <img src="{{ $user->photo_url }}" class="h-12 w-12 rounded-2xl object-cover" alt="Foto profil">
                                    @else
                                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-600 text-sm font-black text-white">
                                            {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                                        </div>
                                    @endif

                                    <div class="min-w-0">
                                        <div class="truncate text-sm font-black text-slate-950">
                                            {{ $user->name }}
                                        </div>

                                        <div class="truncate text-xs font-semibold text-slate-500">
                                            {{ $user->nim ?? $user->email ?? 'Mahasiswa' }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3 grid gap-2">
                                <a
                                    href="{{ route('profile.index') }}"
                                    class="flex items-center justify-between rounded-2xl bg-white/65 px-4 py-3 text-sm font-black text-slate-700"
                                >
                                    <span>Profile</span>
                                    <span class="text-blue-600">›</span>
                                </a>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf

                                    <button
                                        type="submit"
                                        class="w-full rounded-2xl bg-red-50/80 px-4 py-3 text-left text-sm font-black text-red-600"
                                    >
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="main-scroll page-container px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                @if(session('success'))
                    <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 shadow-soft">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700 shadow-soft">
                        {{ $errors->first() }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    {{-- MOBILE NAV --}}
    <div id="mobileNav" class="hidden fixed inset-0 z-50 lg:hidden">
        <div class="mobile-backdrop absolute inset-0" onclick="document.getElementById('mobileNav').classList.add('hidden')"></div>

        <aside class="mobile-panel relative flex h-full w-[84%] max-w-sm flex-col border-r border-slate-200 text-slate-900 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-5">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-600 font-black text-white">
                        CH
                    </div>

                    <div>
                        <div class="text-lg font-black text-slate-950">CampusHub</div>
                        <div class="text-xs font-semibold text-slate-500">Menu utama</div>
                    </div>
                </a>

                <button
                    onclick="document.getElementById('mobileNav').classList.add('hidden')"
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-lg font-black text-slate-700"
                >
                    ×
                </button>
            </div>

            <nav class="sidebar-scroll flex-1 overflow-y-auto px-4 py-4">
                <div class="space-y-2">
                    @foreach($navItems as $item)
                        @php $active = request()->routeIs($item['match']); @endphp

                        <a
                            href="{{ route($item['route']) }}"
                            class="{{ $active ? 'bg-blue-600 text-white shadow-glow' : 'bg-white text-slate-700 border border-slate-200' }} flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-bold"
                        >
                            <span class="{{ $active ? 'bg-white/15 text-white' : 'bg-blue-50 text-blue-700' }} flex h-9 w-9 items-center justify-center rounded-xl">
                                {{ $item['icon'] }}
                            </span>

                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </nav>

            <div class="border-t border-slate-200 p-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-3.5">
                    <div class="flex items-center gap-3">
                        @if(!empty($user->photo_url))
                            <img src="{{ $user->photo_url }}" class="h-10 w-10 rounded-xl object-cover" alt="Foto profil">
                        @else
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 font-black text-white">
                                {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                            </div>
                        @endif

                        <div class="min-w-0">
                            <div class="truncate text-sm font-black text-slate-900">{{ $user->name }}</div>
                            <div class="truncate text-xs font-semibold text-slate-500">{{ $user->nim ?? 'Mahasiswa' }}</div>
                        </div>
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-2">
                        <a
                            href="{{ route('profile.index') }}"
                            class="rounded-xl bg-blue-50 px-3 py-2.5 text-center text-xs font-black text-blue-700"
                        >
                            Profile
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <button class="w-full rounded-xl bg-red-50 px-3 py-2.5 text-xs font-black text-red-600">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>
    </div>
@else
    <main class="min-h-screen">
        @if($errors->any())
            <div class="fixed left-1/2 top-5 z-50 w-[92%] max-w-md -translate-x-1/2 rounded-2xl border border-rose-200 bg-white px-4 py-3 text-sm font-semibold text-rose-700 shadow-soft">
                {{ $errors->first() }}
            </div>
        @endif

        @yield('content')
    </main>
@endif

@stack('scripts')

@if($user)
<script>
    const profileDropdownBtn = document.getElementById('profileDropdownBtn');
    const profileDropdown = document.getElementById('profileDropdown');

    profileDropdownBtn?.addEventListener('click', function (event) {
        event.stopPropagation();
        profileDropdown?.classList.toggle('hidden');
    });

    document.addEventListener('click', function (event) {
        if (!profileDropdown?.contains(event.target) && !profileDropdownBtn?.contains(event.target)) {
            profileDropdown?.classList.add('hidden');
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            profileDropdown?.classList.add('hidden');
        }
    });
</script>
@endif
</body>
</html>
