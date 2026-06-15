@extends('layouts.app')

@section('title', 'Register Mahasiswa')

@section('content')
<div class="h-screen overflow-hidden grid lg:grid-cols-[1.08fr_.92fr] bg-slate-100">

    {{-- LEFT SIDE - CAMPUS PHOTO --}}
    <section class="hidden lg:flex relative overflow-hidden text-white items-center justify-center p-8">
        <div class="absolute inset-0 bg-cover bg-center"
            style="background-image: url('{{ asset('images/kampus-login.png') }}');"></div>
        <div class="absolute inset-0 bg-slate-950/60"></div>
        <div class="absolute inset-0 bg-gradient-to-br from-blue-950/70 via-slate-950/40 to-yellow-900/20"></div>

        <div class="relative z-10 max-w-2xl text-center">
            <div class="mx-auto mb-5 inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/15 px-5 py-2 text-sm font-semibold text-white/90 backdrop-blur-md">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 1l2.5 6.5L19 9l-6.5 1.5L10 17l-2.5-6.5L1 9l6.5-1.5L10 1z"/></svg>
                Student Digital Workspace
            </div>

            <h1 class="text-4xl xl:text-5xl font-black leading-tight tracking-tight drop-shadow-lg">
                Activity Mahasiswa
            </h1>

            <p class="mx-auto mt-4 max-w-xl text-base leading-relaxed text-white/85">
                Buat akun mahasiswa untuk mengelola tugas, file, diskusi, riwayat aktivitas, dan bantuan Lunox dalam satu platform.
            </p>

            <div class="mx-auto mt-6 grid max-w-xl gap-3 text-left">
                <div class="rounded-2xl border border-white/20 bg-white/15 px-5 py-3.5 backdrop-blur-md">
                    <div class="flex items-center gap-2 text-sm font-black text-white">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5h16M4 12h16M4 19h7"/></svg>
                        Dashboard akademik pribadi
                    </div>
                    <div class="mt-1 text-sm text-white/70">Pantau tugas, progres, file, dan aktivitas kuliah.</div>
                </div>

                <div class="rounded-2xl border border-white/20 bg-white/15 px-5 py-3.5 backdrop-blur-md">
                    <div class="flex items-center gap-2 text-sm font-black text-white">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4-.8L3 21l1.9-3.8A8.94 8.94 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        Lunox siap membantu
                    </div>
                    <div class="mt-1 text-sm text-white/70">Bantu belajar, membuat PDF/Word, dan memahami materi.</div>
                </div>
            </div>
        </div>
    </section>

    {{-- RIGHT SIDE - REGISTER FORM --}}
    <section class="relative flex h-full items-center justify-center overflow-hidden px-5 py-4">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-blue-50 to-yellow-50"></div>
        <div class="absolute top-0 left-0 h-96 w-96 rounded-full bg-blue-600/10 blur-3xl"></div>
        <div class="absolute bottom-0 right-0 h-96 w-96 rounded-full bg-yellow-400/20 blur-3xl"></div>
        <div class="absolute inset-y-0 left-0 w-24 bg-gradient-to-r from-slate-900/10 to-transparent hidden lg:block"></div>

        <div class="absolute inset-0 bg-cover bg-center opacity-10 lg:hidden"
            style="background-image: url('{{ asset('images/kampus-login.png') }}');"></div>

        <div class="relative z-10 w-full max-w-2xl max-h-full overflow-y-auto lg:overflow-visible">

            {{-- Mobile Title --}}
            <div class="mb-4 text-center lg:hidden">
                <h1 class="text-3xl font-black text-slate-950">Activity Mahasiswa</h1>
                <p class="mt-2 text-slate-500 text-sm">Buat akun untuk mulai mengelola aktivitas kuliah kamu.</p>
            </div>

            {{-- Register Card --}}
            <div class="rounded-[32px] border border-white/80 bg-white/70 p-5 shadow-2xl shadow-blue-950/10 backdrop-blur-2xl sm:p-6">
                <div class="mb-4 text-center">
                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-600 text-lg font-black text-white shadow-lg shadow-blue-500/30">
                        CH
                    </div>

                    <h2 class="text-2xl font-black text-slate-950">Buat Akun</h2>
                    <p class="mt-1 text-sm text-slate-500">Lengkapi data mahasiswa untuk membuat akun baru.</p>
                </div>

                {{-- Alert error global --}}
                @if ($errors->any())
                    <div class="mb-4 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-2.5 text-sm text-red-700">
                        <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                        <div>
                            <p class="font-bold">Periksa kembali data kamu</p>
                            <p class="mt-0.5 text-red-600/80">Beberapa field belum sesuai, silakan cek di bawah.</p>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('register.post') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="grid gap-3 md:grid-cols-2">
                        {{-- Nama --}}
                        <div>
                            <label class="mb-1.5 block text-xs font-bold text-slate-700">Nama lengkap</label>
                            <div class="relative">
                                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <input
                                    name="name"
                                    value="{{ old('name') }}"
                                    class="w-full rounded-xl border border-slate-200/80 bg-white/85 pl-10 pr-3 py-2.5 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                                    placeholder="Nama lengkap"
                                    required
                                    autofocus
                                >
                            </div>
                            @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>

                        {{-- NIM --}}
                        <div>
                            <label class="mb-1.5 block text-xs font-bold text-slate-700">NIM</label>
                            <div class="relative">
                                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6M5 5h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2zm2-2v4m10-4v4"/>
                                </svg>
                                <input
                                    name="nim"
                                    value="{{ old('nim') }}"
                                    class="w-full rounded-xl border border-slate-200/80 bg-white/85 pl-10 pr-3 py-2.5 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                                    placeholder="Nomor induk mahasiswa"
                                    required
                                >
                            </div>
                            @error('nim')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label class="mb-1.5 block text-xs font-bold text-slate-700">Email</label>
                            <div class="relative">
                                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <input
                                    name="email"
                                    type="email"
                                    value="{{ old('email') }}"
                                    class="w-full rounded-xl border border-slate-200/80 bg-white/85 pl-10 pr-3 py-2.5 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                                    placeholder="nama@email.com"
                                    required
                                >
                            </div>
                            @error('email')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>

                        {{-- Program Studi --}}
                        <div>
                            <label class="mb-1.5 block text-xs font-bold text-slate-700">Program studi</label>
                            <div class="relative">
                                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422A12.083 12.083 0 0118 14.5M12 14l-6.16-3.422A12.083 12.083 0 006 14.5m6-0.5v6m0-6L6 17.5m6-3.5l6 3.5"/>
                                </svg>
                                <input
                                    name="prodi"
                                    value="{{ old('prodi') }}"
                                    class="w-full rounded-xl border border-slate-200/80 bg-white/85 pl-10 pr-3 py-2.5 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                                    placeholder="Contoh: Sistem Informasi"
                                >
                            </div>
                            @error('prodi')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>

                        {{-- Kelas --}}
                        <div>
                            <label class="mb-1.5 block text-xs font-bold text-slate-700">Kelas</label>
                            <div class="relative">
                                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m5-2.13a4 4 0 100-8 4 4 0 000 8zm6 1a4 4 0 100-8 4 4 0 000 8z"/>
                                </svg>
                                <input
                                    name="kelas"
                                    value="{{ old('kelas') }}"
                                    class="w-full rounded-xl border border-slate-200/80 bg-white/85 pl-10 pr-3 py-2.5 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                                    placeholder="Contoh: PAH 2026"
                                >
                            </div>
                            @error('kelas')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>

                        {{-- Foto Profil --}}
                        <div class="md:col-span-2">
                            <label class="mb-1.5 block text-xs font-bold text-slate-700">Foto profil</label>

                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-dashed border-blue-200 bg-white/80 px-3 py-2.5 transition hover:border-blue-500 hover:bg-blue-50/70">
                                <div id="photoPreview" class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-blue-50 text-xs font-black text-blue-700">
                                    CH
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div id="photoName" class="truncate text-sm font-black text-slate-700">
                                        Pilih foto profil
                                    </div>
                                    <div class="text-xs font-semibold text-slate-400">
                                        JPG, PNG, WEBP — maks 2MB
                                    </div>
                                </div>

                                <svg class="h-5 w-5 shrink-0 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2a2 2 0 002 2h14a2 2 0 002-2v-2M16 8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>

                                <input id="photoInput" name="photo" type="file" accept="image/*" class="hidden">
                            </label>
                            @error('photo')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>

                        {{-- Password --}}
                        <div>
                            <label class="mb-1.5 block text-xs font-bold text-slate-700">Password</label>
                            <div class="relative">
                                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 10-8 0v4h8z"/>
                                </svg>
                                <input
                                    name="password"
                                    type="password"
                                    id="password"
                                    class="w-full rounded-xl border border-slate-200/80 bg-white/85 pl-10 pr-10 py-2.5 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                                    placeholder="Masukkan password"
                                    required
                                >
                                <button type="button" onclick="togglePassword('password','eyeIcon1')" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                    <svg id="eyeIcon1" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </button>
                            </div>
                            @error('password')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>

                        {{-- Konfirmasi Password --}}
                        <div>
                            <label class="mb-1.5 block text-xs font-bold text-slate-700">Konfirmasi password</label>
                            <div class="relative">
                                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <input
                                    name="password_confirmation"
                                    type="password"
                                    id="password_confirmation"
                                    class="w-full rounded-xl border border-slate-200/80 bg-white/85 pl-10 pr-10 py-2.5 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                                    placeholder="Ulangi password"
                                    required
                                >
                                <button type="button" onclick="togglePassword('password_confirmation','eyeIcon2')" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                    <svg id="eyeIcon2" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Button --}}
                    <button
                        type="submit"
                        class="mt-5 flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 py-3 font-black text-white shadow-lg shadow-blue-500/30 transition hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-blue-500/40 active:translate-y-0">
                        Buat Akun
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>

                    {{-- Login Link --}}
                    <p class="mt-4 text-center text-sm text-slate-500">
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
        photoPreview.innerHTML = `<img src="${url}" alt="Preview foto" class="h-full w-full object-cover">`;
    }
});

function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.477 10.477a3 3 0 104.243 4.243M9.88 9.88a3 3 0 014.243 4.243M6.61 6.61C4.06 8.36 2.46 11 2.46 12c0 0 3.54 7 9.54 7 1.87 0 3.6-.55 5.07-1.39M17.39 17.39C19.94 15.64 21.54 13 21.54 12c0-.4-.27-1.05-.78-1.84"/>';
    } else {
        input.type = 'password';
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>';
    }
}
</script>
@endpush
@endsection
