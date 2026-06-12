@extends('layouts.app')

@section('title', 'Register Mahasiswa')

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
                Buat akun mahasiswa untuk mengelola tugas, file, diskusi, riwayat aktivitas, dan bantuan Lunox dalam satu platform.
            </p>

            <div class="mx-auto mt-8 grid max-w-xl gap-3 text-left">
                <div class="rounded-2xl border border-white/20 bg-white/15 px-5 py-4 backdrop-blur-md">
                    <div class="text-sm font-black text-white">Dashboard akademik pribadi</div>
                    <div class="mt-1 text-sm text-white/70">Pantau tugas, progres, file, dan aktivitas kuliah.</div>
                </div>

                <div class="rounded-2xl border border-white/20 bg-white/15 px-5 py-4 backdrop-blur-md">
                    <div class="text-sm font-black text-white">Lunox siap membantu</div>
                    <div class="mt-1 text-sm text-white/70">Bantu belajar, membuat PDF/Word, dan memahami materi.</div>
                </div>
            </div>
        </div>
    </section>

    {{-- RIGHT SIDE - REGISTER FORM --}}
    <section class="relative flex min-h-screen items-center justify-center overflow-hidden px-5 py-10">
        {{-- Background kanan selaras dengan login --}}
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-blue-50 to-yellow-50"></div>
        <div class="absolute top-0 left-0 h-96 w-96 rounded-full bg-blue-600/10 blur-3xl"></div>
        <div class="absolute bottom-0 right-0 h-96 w-96 rounded-full bg-yellow-400/20 blur-3xl"></div>
        <div class="absolute inset-y-0 left-0 w-24 bg-gradient-to-r from-slate-900/10 to-transparent"></div>

        {{-- Mobile Background --}}
        <div
            class="absolute inset-0 bg-cover bg-center opacity-10 lg:hidden"
            style="background-image: url('{{ asset('images/kampus-login.png') }}');">
        </div>

        <div class="relative z-10 w-full max-w-2xl">

            {{-- Mobile Title --}}
            <div class="mb-8 text-center lg:hidden">
                <h1 class="text-4xl font-black text-slate-950">
                    Activity Mahasiswa
                </h1>
                <p class="mt-2 text-slate-500">
                    Buat akun untuk mulai mengelola aktivitas kuliah kamu.
                </p>
            </div>

            {{-- Register Card --}}
            <div class="rounded-[32px] border border-white/80 bg-white/70 p-6 shadow-2xl shadow-blue-950/10 backdrop-blur-2xl sm:p-8">
                <div class="mb-7 text-center">
                    <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-600 text-xl font-black text-white shadow-lg shadow-blue-500/30">
                        CH
                    </div>

                    <h2 class="text-3xl font-black text-slate-950">
                        Buat Akun
                    </h2>

                    <p class="mt-2 text-sm text-slate-500">
                        Lengkapi data mahasiswa untuk membuat akun baru.
                    </p>
                </div>

                <form method="POST" action="{{ route('register.post') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="grid gap-5 md:grid-cols-2">
                        {{-- Nama --}}
                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-700">
                                Nama lengkap
                            </label>
                            <input
                                name="name"
                                value="{{ old('name') }}"
                                class="w-full rounded-2xl border border-slate-200/80 bg-white/85 px-4 py-3.5 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                                placeholder="Nama lengkap"
                                required
                            >
                        </div>

                        {{-- NIM --}}
                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-700">
                                NIM
                            </label>
                            <input
                                name="nim"
                                value="{{ old('nim') }}"
                                class="w-full rounded-2xl border border-slate-200/80 bg-white/85 px-4 py-3.5 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                                placeholder="Nomor induk mahasiswa"
                                required
                            >
                        </div>

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

                        {{-- Program Studi --}}
                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-700">
                                Program studi
                            </label>
                            <input
                                name="prodi"
                                value="{{ old('prodi') }}"
                                class="w-full rounded-2xl border border-slate-200/80 bg-white/85 px-4 py-3.5 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                                placeholder="Contoh: Sistem Informasi"
                            >
                        </div>

                        {{-- Kelas --}}
                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-700">
                                Kelas
                            </label>
                            <input
                                name="kelas"
                                value="{{ old('kelas') }}"
                                class="w-full rounded-2xl border border-slate-200/80 bg-white/85 px-4 py-3.5 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                                placeholder="Contoh: PAH 2026"
                            >
                        </div>

                        {{-- Foto Profil --}}
                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-700">
                                Foto profil
                            </label>

                            <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-dashed border-blue-200 bg-white/80 px-4 py-3.5 transition hover:border-blue-500 hover:bg-blue-50/70">
                                <div id="photoPreview" class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-blue-50 text-sm font-black text-blue-700">
                                    CH
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div id="photoName" class="truncate text-sm font-black text-slate-700">
                                        Pilih foto profil
                                    </div>
                                    <div class="text-xs font-semibold text-slate-400">
                                        JPG, PNG, WEBP
                                    </div>
                                </div>

                                <input
                                    id="photoInput"
                                    name="photo"
                                    type="file"
                                    accept="image/*"
                                    class="hidden"
                                >
                            </label>
                        </div>

                        {{-- Password --}}
                        <div>
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

                        {{-- Konfirmasi Password --}}
                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-700">
                                Konfirmasi password
                            </label>
                            <input
                                name="password_confirmation"
                                type="password"
                                class="w-full rounded-2xl border border-slate-200/80 bg-white/85 px-4 py-3.5 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                                placeholder="Ulangi password"
                                required
                            >
                        </div>
                    </div>

                    {{-- Button --}}
                    <button
                        type="submit"
                        class="mt-7 w-full rounded-2xl bg-blue-600 py-3.5 font-black text-white shadow-lg shadow-blue-500/30 transition hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-blue-500/40">
                        Buat Akun
                    </button>

                    {{-- Login Link --}}
                    <p class="mt-6 text-center text-sm text-slate-500">
                        Sudah punya akun?
                        <a href="{{ route('login') }}" class="font-black text-blue-600 hover:text-blue-700">
                            Login sekarang
                        </a>
                    </p>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
const photoInput = document.getElementById('photoInput');
const photoPreview = document.getElementById('photoPreview');
const photoName = document.getElementById('photoName');

photoInput?.addEventListener('change', function () {
    const file = this.files?.[0];

    if (!file) {
        photoPreview.innerHTML = 'CH';
        photoName.textContent = 'Pilih foto profil';
        return;
    }

    photoName.textContent = file.name;

    if (file.type && file.type.startsWith('image/')) {
        const url = URL.createObjectURL(file);

        photoPreview.innerHTML = `
            <img src="${url}" alt="Preview foto" class="h-full w-full object-cover">
        `;
    }
});
</script>
@endpush
