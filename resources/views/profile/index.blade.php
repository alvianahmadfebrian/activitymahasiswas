@extends('layouts.app')

@section('title', 'Profile')
@section('page-title', 'Profile Mahasiswa')
@section('eyebrow', 'Akun pengguna')

@section('content')
@php
    $initial = strtoupper(substr($user->name ?? 'U', 0, 1));

    $formatDate = function ($date) {
        if (!$date) return '-';

        try {
            return \Carbon\Carbon::parse($date)->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i');
        } catch (\Throwable $e) {
            return '-';
        }
    };
@endphp

<style>
    .profile-page {
        min-height: calc(100vh - 150px);
    }

    .profile-card {
        background: rgba(255, 255, 255, .84);
        backdrop-filter: blur(18px) saturate(160%);
        -webkit-backdrop-filter: blur(18px) saturate(160%);
        border: 1px solid rgba(200, 220, 255, .48);
        border-radius: 24px;
        box-shadow: 0 16px 50px rgba(15, 23, 42, .07);
    }

    .profile-hero {
        background:
            radial-gradient(circle at 18% 10%, rgba(37, 99, 235, .20), transparent 24rem),
            radial-gradient(circle at 90% 0%, rgba(14, 165, 233, .14), transparent 22rem),
            rgba(255, 255, 255, .82);
    }

    .profile-input {
        width: 100%;
        border-radius: 16px;
        border: 1px solid #dbe3ef;
        background: rgba(248, 250, 252, .9);
        padding: 13px 15px;
        font-size: 14px;
        color: #0f172a;
        outline: none;
        transition: .16s ease;
    }

    .profile-input:focus {
        background: white;
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, .12);
    }

    .profile-label {
        display: block;
        margin-bottom: 7px;
        font-size: 12px;
        font-weight: 800;
        color: #475569;
    }

    .profile-photo {
        width: 110px;
        height: 110px;
        border-radius: 30px;
        background: #2563eb;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 34px;
        font-weight: 900;
        overflow: hidden;
        box-shadow: 0 18px 45px rgba(37, 99, 235, .25);
    }

    .profile-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .photo-picker {
        display: flex;
        align-items: center;
        gap: 12px;
        border-radius: 18px;
        border: 1px dashed #93c5fd;
        background: rgba(239, 246, 255, .72);
        padding: 14px;
        cursor: pointer;
        transition: .16s ease;
    }

    .photo-picker:hover {
        border-color: #2563eb;
        background: rgba(219, 234, 254, .82);
    }

    .info-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        border-bottom: 1px solid rgba(226, 232, 240, .85);
        padding: 14px 0;
    }

    .info-row:last-child {
        border-bottom: 0;
    }

    .info-label {
        font-size: 12px;
        font-weight: 800;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: .08em;
    }

    .info-value {
        margin-top: 4px;
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
        word-break: break-word;
    }

    .save-btn {
        border-radius: 18px;
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: white;
        padding: 14px 20px;
        font-size: 14px;
        font-weight: 900;
        box-shadow: 0 18px 48px rgba(37, 99, 235, .24);
        transition: .16s ease;
    }

    .save-btn:hover {
        transform: translateY(-1px);
        filter: brightness(1.04);
    }
</style>

<div class="profile-page space-y-6">
    <section class="profile-card profile-hero overflow-hidden p-6 sm:p-7">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                <div id="mainPhotoPreview" class="profile-photo">
                    @if(!empty($user->photo_url))
                        <img src="{{ $user->photo_url }}" alt="Foto profile">
                    @else
                        {{ $initial }}
                    @endif
                </div>

                <div>
                    <p class="text-xs font-black uppercase tracking-[.22em] text-blue-600">
                        Profile mahasiswa
                    </p>

                    <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                        {{ $user->name }}
                    </h2>

                    <p class="mt-2 text-sm leading-relaxed text-slate-500">
                        Kelola informasi akun, foto profile, dan password akun CampusHub kamu.
                    </p>
                </div>
            </div>

            <div class="rounded-2xl border border-blue-100 bg-white/70 px-5 py-4">
                <div class="text-xs font-black uppercase tracking-[.14em] text-slate-400">
                    Bergabung
                </div>

                <div class="mt-1 text-sm font-black text-slate-800">
                    {{ $formatDate($user->created_at ?? null) }}
                </div>
            </div>
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-[360px_1fr]">
        <aside class="profile-card p-6">
            <h2 class="text-lg font-black text-slate-950">Informasi akun</h2>
            <p class="mt-1 text-sm text-slate-500">Ringkasan data akun yang sedang aktif.</p>

            <div class="mt-6">
                <div class="info-row">
                    <div>
                        <div class="info-label">Nama</div>
                        <div class="info-value">{{ $user->name ?? '-' }}</div>
                    </div>
                </div>

                <div class="info-row">
                    <div>
                        <div class="info-label">NIM</div>
                        <div class="info-value">{{ $user->nim ?? '-' }}</div>
                    </div>
                </div>

                <div class="info-row">
                    <div>
                        <div class="info-label">Email</div>
                        <div class="info-value">{{ $user->email ?? '-' }}</div>
                    </div>
                </div>

                <div class="info-row">
                    <div>
                        <div class="info-label">Program studi</div>
                        <div class="info-value">{{ $user->prodi ?? '-' }}</div>
                    </div>
                </div>

                <div class="info-row">
                    <div>
                        <div class="info-label">Kelas</div>
                        <div class="info-value">{{ $user->kelas ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </aside>

        <section class="profile-card p-6 sm:p-7">
            <div class="mb-6">
                <h2 class="text-xl font-black text-slate-950">Edit profile</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Perbarui data akun kamu. Kosongkan password jika tidak ingin mengubah password.
                </p>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <div class="mb-6">
                    <label class="profile-label">Foto profile</label>

                    <label class="photo-picker" for="photoInput">
                        <div id="photoPreview" class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-blue-600 text-sm font-black text-white">
                            @if(!empty($user->photo_url))
                                <img src="{{ $user->photo_url }}" alt="Foto profile" class="h-full w-full object-cover">
                            @else
                                {{ $initial }}
                            @endif
                        </div>

                        <div class="min-w-0 flex-1">
                            <div id="photoName" class="truncate text-sm font-black text-slate-700">
                                Pilih foto baru
                            </div>

                            <div class="mt-1 text-xs font-semibold text-slate-400">
                                JPG, PNG, WEBP maksimal 2MB
                            </div>
                        </div>

                        <input id="photoInput" type="file" name="photo" accept="image/*" class="hidden">
                    </label>
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="profile-label">Nama lengkap</label>
                        <input name="name" value="{{ old('name', $user->name) }}" class="profile-input" required>
                    </div>

                    <div>
                        <label class="profile-label">NIM</label>
                        <input name="nim" value="{{ old('nim', $user->nim) }}" class="profile-input">
                    </div>

                    <div>
                        <label class="profile-label">Email</label>
                        <input name="email" type="email" value="{{ old('email', $user->email) }}" class="profile-input" required>
                    </div>

                    <div>
                        <label class="profile-label">Program studi</label>
                        <input name="prodi" value="{{ old('prodi', $user->prodi) }}" class="profile-input" placeholder="Contoh: Sistem Informasi">
                    </div>

                    <div>
                        <label class="profile-label">Kelas</label>
                        <input name="kelas" value="{{ old('kelas', $user->kelas) }}" class="profile-input" placeholder="Contoh: PAH 2026">
                    </div>

                    <div>
                        <label class="profile-label">Password baru</label>
                        <input name="password" type="password" class="profile-input" placeholder="Kosongkan jika tidak diganti">
                    </div>

                    <div>
                        <label class="profile-label">Konfirmasi password baru</label>
                        <input name="password_confirmation" type="password" class="profile-input" placeholder="Ulangi password baru">
                    </div>

                    <div class="md:col-span-2">
                        <label class="profile-label">Bio</label>
                        <textarea name="bio" rows="4" class="profile-input resize-none" placeholder="Tulis bio singkat kamu...">{{ old('bio', $user->bio) }}</textarea>
                    </div>
                </div>

                <div class="mt-7 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-slate-500">
                        Perubahan akan tersimpan ke akun kamu.
                    </p>

                    <button type="submit" class="save-btn">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
const photoInput = document.getElementById('photoInput');
const photoPreview = document.getElementById('photoPreview');
const mainPhotoPreview = document.getElementById('mainPhotoPreview');
const photoName = document.getElementById('photoName');

photoInput?.addEventListener('change', function () {
    const file = this.files?.[0];

    if (!file) {
        return;
    }

    photoName.textContent = file.name;

    if (file.type && file.type.startsWith('image/')) {
        const url = URL.createObjectURL(file);

        const imageHtml = `<img src="${url}" alt="Preview foto" class="h-full w-full object-cover">`;

        if (photoPreview) {
            photoPreview.innerHTML = imageHtml;
        }

        if (mainPhotoPreview) {
            mainPhotoPreview.innerHTML = imageHtml;
        }
    }
});
</script>
@endpush
