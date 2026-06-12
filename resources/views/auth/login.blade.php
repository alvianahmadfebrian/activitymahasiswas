
@extends('layouts.app')

@section('title', 'Login Activity')

@section('content')
<div class="min-h-screen grid lg:grid-cols-[1.08fr_.92fr] bg-slate-100">

    {{-- LEFT SIDE - CAMPUS PHOTO --}}
    <section class="hidden lg:flex relative overflow-hidden text-white items-center justify-center p-12">
        {{-- Background Image --}}
        <div
            class="absolute inset-0 bg-cover bg-center"
            style="background-image: url('{{ asset('images/kampus-login.png') }}');">
        </div>

        {{-- Overlay --}}
        <div class="absolute inset-0 bg-slate-950/60"></div>
        <div class="absolute inset-0 bg-gradient-to-br from-blue-950/65 via-slate-950/35 to-yellow-900/25"></div>

        {{-- Content --}}
        <div class="relative z-10 max-w-2xl text-center">
            <div class="mx-auto mb-6 inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/15 px-5 py-2 text-sm font-semibold text-white/90 backdrop-blur-md shadow-lg">
                ✦ Student Digital Workspace
            </div>

            <h1 class="text-5xl xl:text-6xl font-black leading-tight tracking-tight drop-shadow-lg">
                Activity Mahasiswa
            </h1>

            <p class="mx-auto mt-5 max-w-xl text-lg leading-relaxed text-white/85">
                Kelola aktivitas kuliah, tugas, file, diskusi, dan bantuan AI dalam satu platform yang rapi.
            </p>
        </div>
    </section>

    {{-- RIGHT SIDE - LOGIN FORM --}}
    <section class="relative flex min-h-screen items-center justify-center overflow-hidden px-5 py-10">
        {{-- Background kanan tetap putih tapi selaras dengan kiri --}}
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-blue-50 to-yellow-50"></div>
        <div class="absolute top-0 left-0 h-96 w-96 rounded-full bg-blue-600/10 blur-3xl"></div>
        <div class="absolute bottom-0 right-0 h-96 w-96 rounded-full bg-yellow-400/20 blur-3xl"></div>
        <div class="absolute inset-y-0 left-0 w-24 bg-gradient-to-r from-slate-900/10 to-transparent"></div>

        {{-- Mobile Background --}}
        <div
            class="absolute inset-0 bg-cover bg-center opacity-10 lg:hidden"
            style="background-image: url('{{ asset('images/kampus-login.png') }}');">
        </div>

        <div class="relative z-10 w-full max-w-md">

            {{-- Mobile Title --}}
            <div class="mb-8 text-center lg:hidden">
                <h1 class="text-4xl font-black text-slate-950">
                    Activity Mahasiswa
                </h1>
                <p class="mt-2 text-slate-500">
                    Login untuk melanjutkan aktivitas kuliah kamu.
                </p>
            </div>

            {{-- Login Card --}}
            <div class="rounded-[32px] border border-white/80 bg-white/70 p-6 shadow-2xl shadow-blue-950/10 backdrop-blur-2xl sm:p-8">
                <div class="mb-7 text-center">
                    <h2 class="text-3xl font-black text-slate-950">
                        Selamat Datang
                    </h2>
                    <p class="mt-2 text-sm text-slate-500">
                        Masuk menggunakan akun mahasiswa kamu.
                    </p>
                </div>

                <form method="POST" action="{{ route('login.post') }}">
                    @csrf

                    {{-- Email --}}
                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">
                            Email
                        </label>
                        <input
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            class="w-full rounded-2xl border border-slate-200/80 bg-white/85 px-4 py-3.5 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                            placeholder="nama@email.com"
                            required
                        >
                    </div>

                    {{-- Password --}}
                    <div class="mt-5">
                        <label class="mb-2 block text-sm font-bold text-slate-700">
                            Password
                        </label>
                        <input
                            name="password"
                            type="password"
                            class="w-full rounded-2xl border border-slate-200/80 bg-white/85 px-4 py-3.5 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                            placeholder="Masukkan password"
                            required
                        >
                    </div>

                    {{-- Button --}}
                    <button
                        type="submit"
                        class="mt-7 w-full rounded-2xl bg-blue-600 py-3.5 font-black text-white shadow-lg shadow-blue-500/30 transition hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-blue-500/40">
                        Masuk
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
@endsection
