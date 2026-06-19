@extends('layouts.app')

@section('title', 'Login Activity')

@section('content')
<div class="h-screen overflow-hidden grid lg:grid-cols-[1.08fr_.92fr] bg-slate-100">

    {{-- LEFT SIDE - CAMPUS PHOTO --}}
    <section class="hidden lg:flex relative overflow-hidden text-white items-center justify-center p-12">
        <div class="absolute inset-0 bg-cover bg-center"
            style="background-image: url('{{ asset('images/kampus-login.png') }}');"></div>
        <div class="absolute inset-0 bg-slate-950/60"></div>
        <div class="absolute inset-0 bg-gradient-to-br from-blue-950/70 via-slate-950/40 to-yellow-900/20"></div>

        <div class="relative z-10 max-w-2xl text-center">
            <div class="mx-auto mb-6 inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/15 px-5 py-2 text-sm font-semibold text-white/90 backdrop-blur-md">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 1l2.5 6.5L19 9l-6.5 1.5L10 17l-2.5-6.5L1 9l6.5-1.5L10 1z"/></svg>
                Student Digital Workspace
            </div>

            <h1 class="text-5xl xl:text-6xl font-black leading-tight tracking-tight drop-shadow-lg">
                Activity Mahasiswa
            </h1>

            <p class="mx-auto mt-5 max-w-xl text-lg leading-relaxed text-white/85">
                Kelola aktivitas kuliah, tugas, file, diskusi, dan bantuan AI dalam satu platform yang rapi.
            </p>

            {{-- Feature list --}}
            <div class="mt-8 grid gap-3 text-left max-w-sm mx-auto">
                <div class="flex items-center gap-3 rounded-xl border border-white/15 bg-white/10 px-4 py-3 text-sm">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 11l3 3L22 4M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                    Pantau tugas dan deadline
                </div>
                <div class="flex items-center gap-3 rounded-xl border border-white/15 bg-white/10 px-4 py-3 text-sm">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4-.8L3 21l1.9-3.8A8.94 8.94 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    Bantuan AI 24 jam
                </div>
                <div class="flex items-center gap-3 rounded-xl border border-white/15 bg-white/10 px-4 py-3 text-sm">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                    Akses file dan diskusi
                </div>
            </div>
        </div>
    </section>

    {{-- RIGHT SIDE - LOGIN FORM --}}
    <section class="relative flex h-full items-center justify-center overflow-hidden px-5 py-6">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-blue-50 to-yellow-50"></div>
        <div class="absolute top-0 left-0 h-96 w-96 rounded-full bg-blue-600/10 blur-3xl"></div>
        <div class="absolute bottom-0 right-0 h-96 w-96 rounded-full bg-yellow-400/20 blur-3xl"></div>
        <div class="absolute inset-y-0 left-0 w-24 bg-gradient-to-r from-slate-900/10 to-transparent hidden lg:block"></div>

        <div class="absolute inset-0 bg-cover bg-center opacity-10 lg:hidden"
            style="background-image: url('{{ asset('images/kampus-login.png') }}');"></div>

        <div class="relative z-10 w-full max-w-md">

            {{-- Mobile Title --}}
            <div class="mb-6 text-center lg:hidden">
                <h1 class="text-3xl font-black text-slate-950">Activity Mahasiswa</h1>
                <p class="mt-2 text-slate-500 text-sm">Login untuk melanjutkan aktivitas kuliah kamu.</p>
            </div>

            {{-- Login Card --}}
            <div class="rounded-[32px] border border-white/80 bg-white/70 p-6 shadow-2xl shadow-blue-950/10 backdrop-blur-2xl sm:p-8">

                {{-- Icon badge --}}
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-600/10">
                    <svg class="h-7 w-7 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422A12.083 12.083 0 0118 14.5M12 14l-6.16-3.422A12.083 12.083 0 006 14.5m6-0.5v6m0-6L6 17.5m6-3.5l6 3.5"/>
                    </svg>
                </div>

                <div class="mb-6 text-center">
                    <h2 class="text-3xl font-black text-slate-950">Selamat Datang</h2>
                    <p class="mt-2 text-sm text-slate-500">Masuk menggunakan akun mahasiswa kamu.</p>
                </div>

                {{-- Alert error/sukses --}}
                @if (session('error'))
                    <div class="mb-5 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                        {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}">
                    @csrf

                    {{-- Email --}}
                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Email</label>
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <input
                                name="email"
                                type="email"
                                value="{{ old('email') }}"
                                class="w-full rounded-2xl border {{ $errors->has('email') ? 'border-red-500 bg-red-50/20 focus:ring-red-100 focus:border-red-500' : 'border-slate-200/80 bg-white/85 focus:ring-blue-100 focus:border-blue-500' }} pl-11 pr-4 py-3.5 text-slate-800 outline-none transition placeholder:text-slate-400 focus:bg-white focus:ring-4"
                                placeholder="nama@email.com"
                                required
                                autofocus
                            >
                        </div>
                        @error('email')
                            <p class="mt-1.5 flex items-center gap-1.5 text-xs font-semibold text-red-500">
                                <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="mt-5">
                        <div class="mb-2 flex items-center justify-between">
                            <label class="block text-sm font-bold text-slate-700">Password</label>
                            <a href="#" class="text-xs font-semibold text-blue-600 hover:text-blue-700">Lupa password?</a>
                        </div>
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 10-8 0v4h8z"/>
                            </svg>
                            <input
                                name="password"
                                type="password"
                                id="password"
                                class="w-full rounded-2xl border {{ $errors->has('password') ? 'border-red-500 bg-red-50/20 focus:ring-red-100 focus:border-red-500' : 'border-slate-200/80 bg-white/85 focus:ring-blue-100 focus:border-blue-500' }} pl-11 pr-12 py-3.5 text-slate-800 outline-none transition placeholder:text-slate-400 focus:bg-white focus:ring-4"
                                placeholder="Masukkan password"
                                required
                            >
                            <button type="button" onclick="togglePassword()" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                <svg id="eyeIcon" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1.5 flex items-center gap-1.5 text-xs font-semibold text-red-500">
                                <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Remember me --}}
                    <div class="mt-4 flex items-center gap-2">
                        <input type="checkbox" name="remember" id="remember" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <label for="remember" class="text-sm text-slate-600">Ingat saya</label>
                    </div>

                    {{-- Button --}}
                    <button
                        type="submit"
                        class="mt-6 flex w-full items-center justify-center gap-2 rounded-2xl bg-blue-600 py-3.5 font-black text-white shadow-lg shadow-blue-500/30 transition hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-blue-500/40 active:translate-y-0">
                        Masuk
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>

                    {{-- Register Link --}}
                    <p class="mt-6 text-center text-sm text-slate-500">
                        Belum punya akun?
                        <a href="{{ route('register') }}" class="font-black text-blue-600 hover:text-blue-700">
                            Daftar sekarang
                        </a>
                    </p>
                </form>
            </div>
        </div>
    </section>
</div>

<script>
    function togglePassword() {
        const input = document.getElementById('password');
        const icon = document.getElementById('eyeIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.477 10.477a3 3 0 104.243 4.243M9.88 9.88a3 3 0 014.243 4.243M6.61 6.61C4.06 8.36 2.46 11 2.46 12c0 0 3.54 7 9.54 7 1.87 0 3.6-.55 5.07-1.39M17.39 17.39C19.94 15.64 21.54 13 21.54 12c0-.4-.27-1.05-.78-1.84"/>';
        } else {
            input.type = 'password';
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>';
        }
    }
</script>
@endsection
