@extends('layouts.app')

@section('title', 'Diskusi Mahasiswa')
@section('page-title', 'Diskusi Mahasiswa')
@section('eyebrow', 'Kolaborasi')

@section('content')
@php
    $userCount = $users->count();
    $onlineCount = $users->where('is_online', true)->count();
    $roomCount = $rooms->count();
@endphp

<style>
    .ds-page {
        height: calc(100vh - 145px);
        min-height: 620px;
        overflow: hidden;
    }

    .ds-card {
        background: rgba(255, 255, 255, 0.82);
        backdrop-filter: blur(18px) saturate(160%);
        -webkit-backdrop-filter: blur(18px) saturate(160%);
        border: 1px solid rgba(200, 220, 255, 0.45);
        border-radius: 20px;
        box-shadow: 0 1px 3px rgba(30, 80, 200, 0.05), 0 8px 32px rgba(30, 80, 200, 0.06);
    }

    .ds-stat {
        background: rgba(255, 255, 255, 0.75);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(200, 220, 255, 0.40);
        border-radius: 16px;
        padding: 16px 20px;
    }

    .ds-stat-label {
        font-size: 12px;
        font-weight: 500;
        color: #64748b;
        margin-bottom: 4px;
    }

    .ds-stat-num {
        font-size: 28px;
        font-weight: 600;
        color: #0f172a;
        line-height: 1;
    }

    .ds-panel-head {
        padding: 18px 22px 14px;
        border-bottom: 1px solid rgba(200, 220, 255, 0.35);
    }

    .ds-panel-head h2 {
        font-size: 15px;
        font-weight: 650;
        color: #0f172a;
    }

    .ds-panel-head p {
        font-size: 12px;
        color: #94a3b8;
        margin-top: 2px;
    }

    .ds-input {
        width: 100%;
        padding: 9px 13px;
        font-size: 13px;
        background: rgba(241, 245, 255, 0.70);
        border: 1px solid rgba(200, 220, 255, 0.55);
        border-radius: 10px;
        color: #0f172a;
        outline: none;
        transition: border-color .15s, box-shadow .15s, background .15s;
    }

    .ds-input:focus {
        background: #fff;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, .14);
    }

    .ds-btn-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 9px 13px;
        background: #2563eb;
        color: #fff;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        transition: .15s ease;
    }

    .ds-btn-primary:hover {
        background: #1d4ed8;
    }

    .ds-list {
        overflow-y: auto;
    }

    .ds-list::-webkit-scrollbar {
        width: 4px;
        height: 4px;
    }

    .ds-list::-webkit-scrollbar-thumb {
        background: rgba(147,197,253,.45);
        border-radius: 999px;
    }

    .ds-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        border-radius: 14px;
        transition: .15s ease;
    }

    .ds-item:hover {
        background: rgba(239, 246, 255, .70);
    }

    .ds-avatar {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        background: #eff6ff;
        color: #2563eb;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 700;
        flex-shrink: 0;
        overflow: hidden;
        position: relative;
    }

    .ds-avatar.private {
        background: #f1f5f9;
        color: #475569;
    }

    .ds-online-dot {
        position: absolute;
        bottom: -1px;
        right: -1px;
        width: 12px;
        height: 12px;
        border-radius: 999px;
        border: 2px solid #fff;
        background: #cbd5e1;
    }

    .ds-online-dot.on {
        background: #10b981;
    }

    .ds-title {
        font-size: 13.5px;
        font-weight: 650;
        color: #0f172a;
    }

    .ds-sub {
        margin-top: 2px;
        font-size: 12px;
        color: #94a3b8;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ds-time {
        font-size: 11px;
        font-weight: 500;
        color: #94a3b8;
        white-space: nowrap;
    }

    .ds-unread-dot {
        flex-shrink: 0;
        min-width: 18px;
        height: 18px;
        padding: 0 5px;
        border-radius: 999px;
        background: #2563eb;
        color: #fff;
        font-size: 10px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .ds-divider {
        padding: 14px 12px 8px;
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: #94a3b8;
    }

    .ds-hero {
        background:
            radial-gradient(circle at 20% 20%, rgba(37, 99, 235, .18), transparent 28rem),
            radial-gradient(circle at 82% 0%, rgba(14, 165, 233, .14), transparent 25rem),
            rgba(255, 255, 255, .84);
    }

    .ds-hero-title {
        font-size: clamp(28px, 4vw, 50px);
        line-height: 1.05;
        letter-spacing: -0.045em;
        color: #0f172a;
        font-weight: 750;
    }

    .ds-modal {
        background: rgba(15, 23, 42, .62);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }

    .ds-modal-box {
        background: rgba(255, 255, 255, .96);
        border: 1px solid rgba(200, 220, 255, .55);
        border-radius: 24px;
        box-shadow: 0 20px 70px rgba(15, 23, 42, .18);
    }

    .ds-member-list::-webkit-scrollbar {
        width: 4px;
    }

    .ds-member-list::-webkit-scrollbar-thumb {
        background: rgba(147,197,253,.45);
        border-radius: 999px;
    }

    @media (max-width: 1024px) {
        .ds-page {
            height: auto;
            min-height: auto;
            overflow: visible;
        }
    }
</style>

<div class="ds-page space-y-4">
    <section class="grid gap-3 sm:grid-cols-3">
        <div class="ds-stat">
            <p class="ds-stat-label">Percakapan</p>
            <div class="ds-stat-num">{{ $roomCount }}</div>
        </div>

        <div class="ds-stat">
            <p class="ds-stat-label">User tersedia</p>
            <div class="ds-stat-num">{{ $userCount }}</div>
        </div>

        <div class="ds-stat">
            <p class="ds-stat-label">Online</p>
            <div class="ds-stat-num text-emerald-600">{{ $onlineCount }}</div>
        </div>
    </section>

    <div class="grid h-[calc(100%-100px)] gap-4 overflow-hidden xl:grid-cols-[380px_1fr]">
        <aside class="ds-card flex h-full flex-col overflow-hidden">
            <div class="ds-panel-head">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2>Percakapan</h2>
                        <p>Group dan chat pribadi.</p>
                    </div>

                    <button
                        type="button"
                        onclick="document.getElementById('groupModal').classList.remove('hidden')"
                        class="ds-btn-primary"
                    >
                        Buat
                    </button>
                </div>

                <input
                    id="chatSearch"
                    type="text"
                    placeholder="Cari room atau user..."
                    class="ds-input mt-4"
                >
            </div>

            <div id="chatList" class="ds-list flex-1 p-3">
                @forelse($rooms as $room)
                    @php
                        $isGroup = ($room->type ?? 'group') === 'group';
                        $initial = strtoupper(substr($room->title ?? 'R', 0, 1));
                    @endphp

                    <a href="{{ route('discussions.show', $room->id) }}" class="chat-item ds-item" data-room-id="{{ $room->id }}" data-last-time="{{ optional($room->updated_at)->timestamp }}">
                        <div class="ds-avatar {{ $isGroup ? '' : 'private' }}">
                            {{ $initial }}
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-3">
                                <h3 class="ds-title truncate">{{ $room->title }}</h3>
                                <span class="ds-time">{{ $room->preview_time }}</span>
                            </div>

                            <div class="flex items-center justify-between gap-2">
                                <p class="ds-sub">{{ $room->preview_text }}</p>
                                <span class="ds-unread-dot hidden"></span>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white/70 p-6 text-center text-sm text-slate-500">
                        Belum ada room diskusi.
                    </div>
                @endforelse

                @if($users->count())
                    <div class="ds-divider">Mulai chat pribadi</div>
                @endif

                @foreach($users as $user)
                    <form method="POST" action="{{ route('discussions.private') }}" class="chat-item">
                        @csrf
                        <input type="hidden" name="target_user_id" value="{{ $user->id }}">

                        <button type="submit" class="ds-item w-full text-left">
                            <div class="relative shrink-0">
                                @if(!empty($user->photo_url))
                                    <div class="ds-avatar private" style="padding:0;">
                                        <img src="{{ $user->photo_url }}" class="h-full w-full object-cover" alt="{{ $user->name }}">
                                        <span class="ds-online-dot {{ !empty($user->is_online) ? 'on' : '' }}"></span>
                                    </div>
                                @else
                                    <div class="ds-avatar private">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                        <span class="ds-online-dot {{ !empty($user->is_online) ? 'on' : '' }}"></span>
                                    </div>
                                @endif
                            </div>

                            <div class="min-w-0 flex-1">
                                <h3 class="ds-title truncate">{{ $user->name }}</h3>
                                <p class="ds-sub">{{ !empty($user->is_online) ? 'Online' : 'Offline' }}</p>
                            </div>
                        </button>
                    </form>
                @endforeach
            </div>
        </aside>

        <section class="ds-card ds-hero flex h-full min-h-0 items-center justify-center overflow-hidden p-8">
            <div class="max-w-2xl text-center">
                <div class="mx-auto mb-5 h-20 w-20 rounded-[24px] border border-blue-100 bg-white/70 flex items-center justify-center">
                    <svg width="34" height="34" viewBox="0 0 24 24" fill="none">
                        <path d="M21 12C21 16.4 17 20 12 20C10.8 20 9.7 19.8 8.7 19.4L4 20.5L5.3 16.4C3.9 15.1 3 13.2 3 11C3 6.6 7 3 12 3C17 3 21 6.6 21 12Z" stroke="#2563eb" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M8 10.5H16M8 13.5H13" stroke="#2563eb" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </div>

                <h2 class="ds-hero-title">
                    Pilih percakapan untuk mulai diskusi.
                </h2>

                <p class="mt-5 text-sm leading-relaxed text-slate-500 sm:text-base">
                    Buat group mata kuliah, mulai chat pribadi dengan teman, dan simpan alur diskusi agar mudah dilacak.
                </p>

                <button
                    type="button"
                    onclick="document.getElementById('groupModal').classList.remove('hidden')"
                    class="ds-btn-primary mt-6 px-5 py-3"
                >
                    Buat Group Baru
                </button>
            </div>
        </section>
    </div>
</div>

<div id="groupModal" class="ds-modal hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0" onclick="document.getElementById('groupModal').classList.add('hidden')"></div>

    <div class="ds-modal-box relative w-full max-w-2xl p-6">
        <button
            type="button"
            onclick="document.getElementById('groupModal').classList.add('hidden')"
            class="absolute right-5 top-5 h-9 w-9 rounded-xl bg-slate-100 text-sm font-black text-slate-600 hover:bg-slate-200"
        >
            ×
        </button>

        <h2 class="text-xl font-black text-slate-950">Buat group diskusi</h2>
        <p class="mt-1 text-sm text-slate-500">
            Isi informasi group dan pilih anggota yang ingin ditambahkan.
        </p>

        <form method="POST" action="{{ route('discussions.store') }}" class="mt-6 space-y-4">
            @csrf

            <input
                name="title"
                placeholder="Nama group"
                class="ds-input"
                required
            >

            <input
                name="course"
                placeholder="Mata kuliah (opsional)"
                class="ds-input"
            >

            <textarea
                name="description"
                rows="3"
                placeholder="Deskripsi group (opsional)"
                class="ds-input resize-none"
            ></textarea>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Anggota</label>

                <div class="ds-member-list grid max-h-56 gap-2 overflow-y-auto rounded-2xl border border-slate-200 bg-slate-50 p-3 sm:grid-cols-2">
                    @forelse($users as $user)
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl bg-white p-3 transition hover:bg-blue-50">
                            <input
                                type="checkbox"
                                name="member_ids[]"
                                value="{{ $user->id }}"
                                class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            >

                            <span class="text-sm font-semibold text-slate-700">{{ $user->name }}</span>
                        </label>
                    @empty
                        <p class="text-sm text-slate-500">Belum ada user lain.</p>
                    @endforelse
                </div>
            </div>

            <button class="ds-btn-primary w-full py-3" type="submit">
                Buat Group
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('chatSearch')?.addEventListener('input', function () {
    const keyword = this.value.toLowerCase();

    document.querySelectorAll('.chat-item').forEach(item => {
        item.style.display = item.innerText.toLowerCase().includes(keyword) ? '' : 'none';
    });
});

// ===== Unread indicator sederhana berbasis localStorage =====
document.querySelectorAll('a[data-room-id]').forEach(item => {
    const roomId = item.dataset.roomId;
    const lastTime = parseInt(item.dataset.lastTime || '0', 10);
    const readKey = `campushub_last_read_${roomId}`;
    const lastRead = parseInt(localStorage.getItem(readKey) || '0', 10);

    const dot = item.querySelector('.ds-unread-dot');

    if (lastTime > 0 && lastTime > lastRead && dot) {
        dot.textContent = '●';
        dot.classList.remove('hidden');
    }

    item.addEventListener('click', () => {
        localStorage.setItem(readKey, String(lastTime));
    });
});
</script>
@endpush
