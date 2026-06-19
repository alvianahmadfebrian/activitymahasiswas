@extends('layouts.app')

@section('title', $room->title)
@section('page-title', $room->title)
@section('eyebrow', $room->type === 'private' ? 'Chat pribadi' : 'Group diskusi')

@section('content')
@php
    $subtitleFor = function ($r) {
        if (!empty($r->description)) return $r->description;
        if (!empty($r->course)) return $r->course;
        return ($r->type ?? 'group') === 'group' ? 'Group diskusi' : 'Chat pribadi';
    };

    $safeRoomId = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $room->id);
    $callRoomName = 'campushub-discussion-' . $safeRoomId;

    $voiceCallUrl = 'https://meet.jit.si/' . $callRoomName . '#config.startAudioOnly=true&config.startWithVideoMuted=true&config.prejoinPageEnabled=false';
    $videoCallUrl = 'https://meet.jit.si/' . $callRoomName . '#config.startAudioOnly=false&config.startWithVideoMuted=false&config.prejoinPageEnabled=false';

    $lastMessage = $messages->last();
    $initialAfter = $lastMessage && $lastMessage->created_at ? $lastMessage->created_at->toISOString() : null;
@endphp

<style>
    .chat-shell {
        height: calc(100vh - 150px);
        min-height: 620px;
        overflow: hidden;
    }

    .chat-card {
        background: rgba(255, 255, 255, 0.82);
        backdrop-filter: blur(18px) saturate(160%);
        -webkit-backdrop-filter: blur(18px) saturate(160%);
        border: 1px solid rgba(200, 220, 255, 0.45);
        border-radius: 20px;
        box-shadow: 0 1px 3px rgba(30, 80, 200, 0.05), 0 8px 32px rgba(30, 80, 200, 0.06);
    }

    .chat-head {
        border-bottom: 1px solid rgba(200, 220, 255, .35);
        background: rgba(255, 255, 255, .72);
    }

    .chat-input {
        width: 100%;
        padding: 11px 14px;
        font-size: 13px;
        background: rgba(241, 245, 255, 0.70);
        border: 1px solid rgba(200, 220, 255, 0.55);
        border-radius: 12px;
        color: #0f172a;
        outline: none;
        transition: border-color .15s, box-shadow .15s, background .15s;
    }

    .chat-input:focus {
        background: #fff;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, .14);
    }

    .room-list::-webkit-scrollbar,
    .messages-scroll::-webkit-scrollbar {
        width: 4px;
        height: 4px;
    }

    .room-list::-webkit-scrollbar-thumb,
    .messages-scroll::-webkit-scrollbar-thumb {
        background: rgba(147,197,253,.45);
        border-radius: 999px;
    }

    .room-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        border-radius: 14px;
        transition: .15s ease;
    }

    .room-item:hover {
        background: rgba(239, 246, 255, .70);
    }

    .room-item.active {
        background: #2563eb;
        color: #fff;
        box-shadow: 0 12px 28px rgba(37, 99, 235, .18);
    }

    .room-avatar {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        background: #eff6ff;
        color: #2563eb;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 700;
        flex-shrink: 0;
        position: relative;
    }

    .room-item.active .room-avatar {
        background: rgba(255,255,255,.18);
        color: #fff;
    }

    .room-online-dot {
        position: absolute;
        bottom: -1px;
        right: -1px;
        width: 11px;
        height: 11px;
        border-radius: 999px;
        border: 2px solid #fff;
        background: #cbd5e1;
    }

    .room-online-dot.on {
        background: #10b981;
    }

    .room-title {
        font-size: 13px;
        font-weight: 650;
        color: #0f172a;
    }

    .room-item.active .room-title {
        color: #fff;
    }

    .room-sub {
        margin-top: 2px;
        font-size: 12px;
        color: #94a3b8;
    }

    .room-item.active .room-sub {
        color: rgba(255,255,255,.72);
    }

    .call-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 9px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 650;
        transition: .15s ease;
        white-space: nowrap;
    }

    .call-btn.voice {
        color: #047857;
        background: rgba(209, 250, 229, .65);
        border: 1px solid rgba(167, 243, 208, .80);
    }

    .call-btn.voice:hover {
        background: rgba(167, 243, 208, .85);
    }

    .call-btn.video {
        color: #fff;
        background: #2563eb;
        box-shadow: 0 10px 24px rgba(37, 99, 235, .18);
    }

    .call-btn.video:hover {
        background: #1d4ed8;
    }

    .call-btn.neutral {
        color: #475569;
        background: rgba(241, 245, 255, .70);
        border: 1px solid rgba(200, 220, 255, .55);
    }

    .call-btn.neutral:hover {
        background: #fff;
    }

    .msg-bubble {
        border-radius: 18px;
        padding: 11px 14px;
        font-size: 13px;
        line-height: 1.65;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
    }

    .msg-me {
        background: #2563eb;
        color: white;
        border-bottom-right-radius: 6px;
    }

    .msg-other {
        background: rgba(255,255,255,.88);
        border: 1px solid rgba(200, 220, 255, .35);
        color: #334155;
        border-bottom-left-radius: 6px;
    }

    .msg-time {
        margin-top: 5px;
        font-size: 10.5px;
        color: #94a3b8;
        font-weight: 500;
    }

    .msg-sending {
        opacity: .55;
    }

    .day-divider {
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 10px 0;
    }

    .day-divider span {
        font-size: 11px;
        font-weight: 650;
        color: #64748b;
        background: rgba(255,255,255,.85);
        border: 1px solid rgba(200, 220, 255, .45);
        border-radius: 999px;
        padding: 4px 12px;
    }

    .call-log-card {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        max-width: 420px;
        border-radius: 14px;
        border: 1px solid rgba(191, 219, 254, .75);
        background: rgba(239, 246, 255, .78);
        padding: 9px 13px;
        font-size: 12px;
        color: #2563eb;
        font-weight: 650;
    }

    .call-invite-card {
        max-width: 360px;
        border-radius: 18px;
        border: 1px solid rgba(191, 219, 254, .75);
        background: rgba(239, 246, 255, .86);
        padding: 16px;
        text-align: center;
        box-shadow: 0 8px 24px rgba(37, 99, 235, .08);
    }

    .call-invite-title {
        font-size: 13px;
        font-weight: 750;
        color: #1d4ed8;
    }

    .call-invite-sub {
        margin-top: 4px;
        font-size: 11.5px;
        color: #64748b;
    }

    .modal-backdrop {
        background: rgba(15, 23, 42, .76);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }

    .modal-panel {
        background: rgba(255, 255, 255, .96);
        border: 1px solid rgba(200, 220, 255, .55);
        border-radius: 24px;
        box-shadow: 0 20px 70px rgba(15, 23, 42, .20);
    }

    .header-online-dot {
        position: absolute;
        bottom: -1px;
        right: -1px;
        width: 11px;
        height: 11px;
        border-radius: 999px;
        border: 2px solid #fff;
        background: #cbd5e1;
    }

    .header-online-dot.on {
        background: #10b981;
    }
</style>

<div class="chat-shell grid overflow-hidden rounded-[24px] xl:grid-cols-[340px_1fr]">
    <aside class="chat-card hidden h-full overflow-hidden rounded-r-none xl:flex xl:flex-col">
        <div class="chat-head p-5">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-[15px] font-bold text-slate-950">Diskusi</h2>
                    <p class="mt-1 text-xs text-slate-400">Pilih room lain.</p>
                </div>

                <a href="{{ route('discussions.index') }}" class="call-btn video">
                    Baru
                </a>
            </div>

            <input
                id="roomSearch"
                type="text"
                placeholder="Cari room..."
                class="chat-input mt-4"
            >
        </div>

        <div class="room-list flex-1 overflow-y-auto p-3">
    @foreach($rooms as $r)
        @php
            $isActive = (string) $room->id === (string) $r->id;
            $initial = strtoupper(substr($r->title ?? 'R', 0, 1));
        @endphp

        <a
            href="{{ route('discussions.show', $r->id) }}"
            class="room-item {{ $isActive ? 'active' : '' }}"
        >
            <div class="room-avatar">
                {{ $initial }}
            </div>

            <div class="min-w-0 flex-1">
                <h3 class="room-title truncate">{{ $r->title }}</h3>
                <p class="room-sub truncate">{{ $subtitleFor($r) }}</p>
            </div>
        </a>
    @endforeach
</div>
    </aside>

    <section class="chat-card flex min-w-0 flex-col overflow-hidden rounded-l-none">
        <header class="chat-head flex items-center justify-between gap-3 p-4 sm:p-5">
            <div class="flex min-w-0 items-center gap-3">
                <div class="room-avatar">
                    {{ strtoupper(substr($room->title ?? 'R', 0, 1)) }}

                    @if($otherUser)
                        <span class="header-online-dot {{ !empty($otherUser->is_online) ? 'on' : '' }}"></span>
                    @endif
                </div>

                <div class="min-w-0">
                    <h2 class="truncate text-[15px] font-bold text-slate-950">{{ $room->title }}</h2>
                    <p id="headerSubtitle" class="truncate text-xs text-slate-500">
                        @if($otherUser)
                            {{ !empty($otherUser->is_online) ? 'Online' : 'Offline' }}
                        @else
                            {{ $room->course ?: 'Group diskusi' }}
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex shrink-0 items-center gap-2">

    <button type="button"
            onclick="openCallModal('voice', false)"
            class="call-btn voice">
        Telepon
    </button>

    <button type="button"
            onclick="openCallModal('video', false)"
            class="call-btn video">
        Video
    </button>

    <button type="button"
            onclick="document.getElementById('membersModal').classList.remove('hidden')"
            class="call-btn neutral">
        Anggota
    </button>

    <form method="POST"
          action="{{ route('discussions.leave', $room->id) }}"
          onsubmit="return confirm('Keluar dari group ini?')">
        @csrf

        <button type="submit" class="call-btn neutral">
            Keluar
        </button>
    </form>

    @if((string)$room->user_id === (string)auth()->id())
        <form method="POST"
              action="{{ route('discussions.delete', $room->id) }}"
              onsubmit="return confirm('Hapus group ini? Semua pesan akan hilang.')">
            @csrf
            @method('DELETE')

            <button type="submit"
                    class="call-btn"
                    style="background:#dc2626;color:white;">
                Hapus
            </button>
        </form>
    @endif

    <a href="{{ route('discussions.index') }}"
       class="call-btn neutral">
        Daftar
    </a>

</div>
        </header>

        <div id="messagesBox" class="messages-scroll flex-1 space-y-4 overflow-y-auto bg-slate-50/80 p-4 sm:p-6">
            @php $lastDateLabel = null; @endphp

            @forelse($messages as $message)
                @php
                    $isMe = (string) $message->user_id === (string) auth()->id();
                    $chatTime = $message->created_at ? $message->created_at->timezone('Asia/Jakarta')->format('H:i') : '';
                    $dateLabel = $message->created_at ? $message->created_at->timezone('Asia/Jakarta')->translatedFormat('d F Y') : '';

                    $rawMessage = $message->message ?? '';
                    $isCallLog = str_starts_with($rawMessage, 'CALL_LOG::');
                    $isCallInvite = str_starts_with($rawMessage, 'CALL_INVITE::');

                    $cleanMessage = str_replace('CALL_LOG::', '', $rawMessage);

                    $callPayload = null;
                    $callType = 'video';
                    $callUser = $message->user_name ?? 'Pengguna lain';
                    $callLabel = 'Video call';
                    $isMyInvite = false;

                    if ($isCallInvite) {
                        $callPayload = json_decode(str_replace('CALL_INVITE::', '', $rawMessage), true);
                        $callType = $callPayload['type'] ?? 'video';
                        $callUser = $callPayload['from_user_name'] ?? $message->user_name ?? 'Pengguna lain';
                        $callLabel = $callType === 'voice' ? 'Telepon suara' : 'Video call';
                        $isMyInvite = (string) $message->user_id === (string) auth()->id();
                        $cleanMessage = $callUser . ' memulai ' . strtolower($callLabel) . '.';
                    }

                    $showDateDivider = $dateLabel && $dateLabel !== $lastDateLabel;
                    $lastDateLabel = $dateLabel ?: $lastDateLabel;
                @endphp

                @if($showDateDivider)
                    <div class="day-divider"><span>{{ $dateLabel }}</span></div>
                @endif

                @if($isCallInvite)
                    <div class="flex justify-center">
                        <div class="call-invite-card">
                            <div class="call-invite-title">
                                @if($isMyInvite)
                                    Kamu memulai {{ strtolower($callLabel) }}.
                                @else
                                    {{ $callLabel }} masuk dari {{ $callUser }}.
                                @endif
                            </div>

                            @if($chatTime)
                                <div class="call-invite-sub">{{ $chatTime }}</div>
                            @endif

                            @if(!$isMyInvite)
                                <div class="mt-4 flex gap-2">
                                    <button
                                        type="button"
                                        onclick="rejectCallFromChat(@js($callUser))"
                                        class="flex-1 rounded-xl bg-red-600 px-3 py-2 text-xs font-bold text-white hover:bg-red-700"
                                    >
                                        Tolak
                                    </button>

                                    <button
                                        type="button"
                                        onclick="acceptCallFromChat(@js($callType), @js($callUser))"
                                        class="flex-1 rounded-xl bg-emerald-600 px-3 py-2 text-xs font-bold text-white hover:bg-emerald-700"
                                    >
                                        Terima
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @elseif($isCallLog)
                    <div class="flex justify-center">
                        <div>
                            <div class="call-log-card">{{ $cleanMessage }}</div>
                            @if($chatTime)
                                <div class="mt-1 text-center text-[10px] font-semibold text-slate-400">{{ $chatTime }}</div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="flex {{ $isMe ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[82%] sm:max-w-[70%]">
                            @if(!$isMe)
                                <div class="mb-1 ml-2 text-xs font-semibold text-slate-500">
                                    {{ $message->user_name }}
                                </div>
                            @endif

                            <div class="msg-bubble {{ $isMe ? 'msg-me' : 'msg-other' }}">
                                <p class="whitespace-pre-wrap">{{ $message->message }}</p>
                            </div>

                            <div class="msg-time {{ $isMe ? 'text-right mr-2' : 'ml-2' }}">
                                {{ $chatTime }}
                            </div>
                        </div>
                    </div>
                @endif
            @empty
                <div class="flex h-full items-center justify-center text-center">
                    <div class="max-w-sm">
                        <h3 class="text-lg font-black text-slate-900">Belum ada pesan</h3>
                        <p class="mt-2 text-sm text-slate-500">Mulai percakapan pertama di room ini.</p>
                    </div>
                </div>
            @endforelse
        </div>

        <footer class="border-t border-blue-100/40 bg-white/80 p-4 sm:p-5">
            <form id="sendMessageForm" class="flex gap-3">
                @csrf

                <input
                    id="messageInput"
                    name="message"
                    placeholder="Tulis pesan..."
                    required
                    autocomplete="off"
                    class="chat-input"
                >

                <button
                    type="submit"
                    id="sendMessageBtn"
                    class="call-btn video px-5"
                >
                    Kirim
                </button>
            </form>
        </footer>
    </section>
</div>

<div id="membersModal" class="modal-backdrop hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0" onclick="document.getElementById('membersModal').classList.add('hidden')"></div>

    <div class="modal-panel relative w-full max-w-md p-6">
        <button
            type="button"
            onclick="document.getElementById('membersModal').classList.add('hidden')"
            class="absolute right-5 top-5 h-9 w-9 rounded-xl bg-slate-100 text-sm font-black text-slate-600 hover:bg-slate-200"
        >
            ×
        </button>

        <h2 class="text-lg font-black text-slate-950">Anggota</h2>
        <p class="mt-1 text-xs text-slate-500">{{ $room->title }}</p>

        <div class="mt-5 max-h-72 space-y-2 overflow-y-auto">
            @php
                $memberIds = array_map('strval', $room->member_ids ?? []);
                $isOwner = (string) $room->user_id === (string) auth()->id();
            @endphp

            @foreach($users->whereIn('id', $memberIds)->push(auth()->user()) as $m)
                @continue(!in_array((string) $m->id, $memberIds, true))

                <div class="flex items-center justify-between rounded-xl bg-slate-50 p-3">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold text-slate-700">{{ $m->name }}</span>
                        @if((string) $room->user_id === (string) $m->id)
                            <span class="rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-bold text-blue-700">Owner</span>
                        @endif
                    </div>

                    @if($isOwner && (string) $m->id !== (string) auth()->id())
                        <form method="POST" action="{{ route('discussions.kick', [$room->id, $m->id]) }}" onsubmit="return confirm('Keluarkan {{ $m->name }}?')">
                            @csrf
                            <button type="submit" class="text-xs font-bold text-red-600 hover:text-red-700">Keluarkan</button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>

<div id="callModal" class="modal-backdrop hidden fixed inset-0 z-[9999] p-3 sm:p-6">
    <div class="modal-panel mx-auto flex h-full max-w-6xl flex-col overflow-hidden">
        <div class="flex items-center justify-between gap-3 border-b border-blue-100/50 bg-white/85 p-4 sm:p-5">
            <div class="min-w-0">
                <h2 id="callTitle" class="truncate text-lg font-black text-slate-950">Video Call</h2>
                <p class="truncate text-sm text-slate-500">Room: {{ $room->title }}</p>
            </div>

            <div class="flex shrink-0 items-center gap-2">
                <button type="button" onclick="switchCallMode('voice', true)" class="call-btn voice">
                    Audio
                </button>

                <button type="button" onclick="switchCallMode('video', true)" class="call-btn neutral">
                    Video
                </button>

                <button type="button" onclick="closeCallModal()" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-bold text-white hover:bg-red-700">
                    Tutup
                </button>
            </div>
        </div>

        <div class="relative flex-1 bg-slate-950">
            <iframe
                id="callFrame"
                src=""
                allow="camera; microphone; fullscreen; display-capture; autoplay"
                class="h-full w-full border-0"
            ></iframe>

            <div id="callLoader" class="absolute inset-0 flex items-center justify-center bg-slate-950 text-white">
                <div class="text-center">
                    <h3 class="text-xl font-black">Menyiapkan panggilan...</h3>
                    <p class="mt-2 text-sm text-slate-300">Izinkan akses kamera dan mikrofon jika browser meminta izin.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="incomingCallModal" class="modal-backdrop hidden fixed inset-0 z-[10000] flex items-center justify-center p-4">
    <div class="modal-panel w-full max-w-md p-6 text-center">
        <h2 id="incomingCallTitle" class="text-2xl font-black text-slate-950">
            Panggilan Masuk
        </h2>

        <p id="incomingCallText" class="mt-2 text-sm text-slate-500">
            Ada panggilan masuk.
        </p>

        <div class="mt-6 flex gap-3">
            <button
                type="button"
                onclick="rejectIncomingCall()"
                class="flex-1 rounded-xl bg-red-600 px-4 py-3 font-black text-white hover:bg-red-700"
            >
                Tolak
            </button>

            <button
                type="button"
                onclick="acceptIncomingCall()"
                class="flex-1 rounded-xl bg-emerald-600 px-4 py-3 font-black text-white hover:bg-emerald-700"
            >
                Terima
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const box = document.getElementById('messagesBox');

if (box) {
    box.scrollTop = box.scrollHeight;
}

document.getElementById('roomSearch')?.addEventListener('input', function () {
    const keyword = this.value.toLowerCase();

    document.querySelectorAll('.room-item').forEach(item => {
        item.style.display = item.innerText.toLowerCase().includes(keyword) ? '' : 'none';
    });
});

const callModal = document.getElementById('callModal');
const callFrame = document.getElementById('callFrame');
const callLoader = document.getElementById('callLoader');
const callTitle = document.getElementById('callTitle');

const incomingCallModal = document.getElementById('incomingCallModal');
const incomingCallTitle = document.getElementById('incomingCallTitle');
const incomingCallText = document.getElementById('incomingCallText');

const voiceCallUrl = @json($voiceCallUrl);
const videoCallUrl = @json($videoCallUrl);

const discussionMessageUrl = @json(route('discussions.message', $room->id));
const pollMessagesUrl = @json(route('discussions.poll', $room->id));
const callStartUrl = @json(route('discussions.call.start', $room->id));
const callCheckUrl = @json(route('discussions.call.check', $room->id));
const csrfToken = @json(csrf_token());

const roomId = @json((string) $room->id);
const lastSeenKey = `campushub_last_seen_call_${roomId}`;

let activeCallType = null;
let callStartedAt = null;
let callIsActive = false;
let pendingIncomingCall = null;
let pollingCall = true;

let lastMessageTimestamp = @json($initialAfter);
let lastRenderedDateLabel = @json($lastDateLabel ?? null);

// ===== Kirim pesan via AJAX (tanpa reload, mirip WA) =====
const sendForm = document.getElementById('sendMessageForm');
const messageInput = document.getElementById('messageInput');
const sendBtn = document.getElementById('sendMessageBtn');

sendForm?.addEventListener('submit', async function (e) {
    e.preventDefault();

    const text = messageInput.value.trim();
    if (!text) return;

    messageInput.value = '';
    sendBtn.disabled = true;

    const formData = new FormData();
    formData.append('_token', csrfToken);
    formData.append('message', text);

    try {
        const response = await fetch(discussionMessageUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: formData
        });

        const data = await response.json();

        if (data.success && data.message) {
            appendChatMessage(data.message);
            if (data.message.created_at) {
                lastMessageTimestamp = data.message.created_at;
            }
        }
    } catch (error) {
        console.error('Gagal mengirim pesan:', error);
        messageInput.value = text;
    } finally {
        sendBtn.disabled = false;
        messageInput.focus();
    }
});

function dayDividerLabel(isoString) {
    if (!isoString) return '';
    const d = new Date(isoString);
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
}

function appendChatMessage(msg) {
    if (!box) return;

    const label = dayDividerLabel(msg.created_at);

    if (label && label !== lastRenderedDateLabel) {
        const divider = document.createElement('div');
        divider.className = 'day-divider';
        divider.innerHTML = `<span>${escapeHtml(label)}</span>`;
        box.appendChild(divider);
        lastRenderedDateLabel = label;
    }

    const wrapper = document.createElement('div');
    wrapper.className = `flex ${msg.is_me ? 'justify-end' : 'justify-start'}`;

    const inner = document.createElement('div');
    inner.className = 'max-w-[82%] sm:max-w-[70%]';

    let nameHtml = '';
    if (!msg.is_me) {
        nameHtml = `<div class="mb-1 ml-2 text-xs font-semibold text-slate-500">${escapeHtml(msg.user_name || 'Pengguna')}</div>`;
    }

    inner.innerHTML = `
        ${nameHtml}
        <div class="msg-bubble ${msg.is_me ? 'msg-me' : 'msg-other'}">
            <p class="whitespace-pre-wrap"></p>
        </div>
        <div class="msg-time ${msg.is_me ? 'text-right mr-2' : 'ml-2'}">${escapeHtml(msg.time || '')}</div>
    `;

    inner.querySelector('.msg-bubble p').textContent = msg.message;

    wrapper.appendChild(inner);
    box.appendChild(wrapper);
    box.scrollTop = box.scrollHeight;
}

// ===== Polling pesan baru (real-time ala WA) =====
async function pollNewMessages() {
    if (!lastMessageTimestamp) return;

    try {
        const response = await fetch(`${pollMessagesUrl}?after=${encodeURIComponent(lastMessageTimestamp)}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        });

        const data = await response.json();

        if (!data.success || !Array.isArray(data.messages) || data.messages.length === 0) return;

        const wasNearBottom = box && (box.scrollHeight - box.scrollTop - box.clientHeight < 120);

        data.messages.forEach(msg => {
            // pesan CALL_LOG / CALL_INVITE biarkan ditangani oleh polling panggilan terpisah,
            // tapi tetap tampilkan sebagai bubble teks biasa kalau bukan tipe call
            if (typeof msg.message === 'string' && (msg.message.startsWith('CALL_LOG::') || msg.message.startsWith('CALL_INVITE::'))) {
                lastMessageTimestamp = msg.created_at || lastMessageTimestamp;
                return;
            }

            appendChatMessage(msg);
            lastMessageTimestamp = msg.created_at || lastMessageTimestamp;
        });

        if (wasNearBottom && box) {
            box.scrollTop = box.scrollHeight;
        }
    } catch (error) {
        console.error('Gagal polling pesan baru:', error);
    }
}

// ===== Panggilan (logic asli dipertahankan) =====
function openCallModal(type = 'video', incoming = false) {
    callModal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    activeCallType = type;
    callStartedAt = new Date();
    callIsActive = true;

    if (!incoming) {
        startCallOnServer(type);
    }

    switchCallMode(type, false);
}

async function startCallOnServer(type) {
    const formData = new FormData();
    formData.append('_token', csrfToken);
    formData.append('type', type);

    try {
        const response = await fetch(callStartUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: formData
        });

        const data = await response.json();

        if (data.success && data.invite_message_id) {
            localStorage.setItem(lastSeenKey, data.invite_message_id);
        }

        const label = type === 'voice' ? 'telepon suara' : 'video call';

        appendCallLog(`Memulai ${label}...`);

    } catch (error) {
        console.error('Gagal memulai panggilan:', error);
        appendCallLog('Gagal mengirim notifikasi panggilan.');
    }
}

function switchCallMode(type = 'video', withLog = false) {
    callLoader.classList.remove('hidden');
    activeCallType = type;

    if (type === 'voice') {
        callTitle.textContent = 'Telepon Suara';
        callFrame.src = voiceCallUrl;

        if (withLog && callIsActive) {
            sendCallLog('Mode panggilan diubah ke telepon suara.');
        }
    } else {
        callTitle.textContent = 'Video Call';
        callFrame.src = videoCallUrl;

        if (withLog && callIsActive) {
            sendCallLog('Mode panggilan diubah ke video call.');
        }
    }

    setTimeout(() => {
        callLoader.classList.add('hidden');
    }, 1800);
}

function closeCallModal() {
    if (callIsActive) {
        const duration = getCallDuration();
        const label = activeCallType === 'voice' ? 'Telepon suara' : 'Video call';

        sendCallLog(`${label} selesai. Durasi: ${duration}`);
    }

    callFrame.src = '';
    callModal.classList.add('hidden');
    callLoader.classList.remove('hidden');
    document.body.style.overflow = '';

    activeCallType = null;
    callStartedAt = null;
    callIsActive = false;
}

function getCallDuration() {
    if (!callStartedAt) {
        return '00:00';
    }

    const now = new Date();
    const diffSeconds = Math.max(0, Math.floor((now - callStartedAt) / 1000));

    const minutes = Math.floor(diffSeconds / 60).toString().padStart(2, '0');
    const seconds = (diffSeconds % 60).toString().padStart(2, '0');

    return `${minutes}:${seconds}`;
}

async function sendCallLog(message) {
    appendCallLog(message);

    const formData = new FormData();
    formData.append('_token', csrfToken);
    formData.append('message', 'CALL_LOG::' + message);

    try {
        const response = await fetch(discussionMessageUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: formData
        });

        const data = await response.json();
        if (data.success && data.message && data.message.created_at) {
            lastMessageTimestamp = data.message.created_at;
        }
    } catch (error) {
        console.error('Gagal menyimpan log panggilan:', error);
    }
}

function appendCallLog(message) {
    if (!box) return;

    const wrapper = document.createElement('div');
    wrapper.className = 'flex justify-center';

    const bubble = document.createElement('div');
    bubble.className = 'call-log-card';

    const time = new Date().toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit'
    });

    bubble.innerHTML = `
        ${escapeHtml(message)}
        <span class="ml-2 text-[10px] text-blue-400">${time}</span>
    `;

    wrapper.appendChild(bubble);
    box.appendChild(wrapper);
    box.scrollTop = box.scrollHeight;
}

async function checkIncomingCall() {
    if (!pollingCall) return;
    if (callIsActive) return;
    if (!incomingCallModal.classList.contains('hidden')) return;

    const lastSeen = localStorage.getItem(lastSeenKey) || '';

    try {
        const response = await fetch(`${callCheckUrl}?last_seen=${encodeURIComponent(lastSeen)}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        });

        const data = await response.json();

        if (!data.success || !data.has_call || !data.call) return;

        localStorage.setItem(lastSeenKey, data.invite_message_id);
        showIncomingCall(data.call, data.invite_message_id);

    } catch (error) {
        console.error('Gagal cek panggilan masuk:', error);
    }
}

function showIncomingCall(call, inviteMessageId) {
    pendingIncomingCall = {
        inviteMessageId: inviteMessageId,
        ...call
    };

    const type = call.type || 'video';
    const caller = call.from_user_name || 'Pengguna lain';

    if (type === 'voice') {
        incomingCallTitle.textContent = 'Telepon Masuk';
        incomingCallText.textContent = `${caller} sedang menelepon kamu.`;
    } else {
        incomingCallTitle.textContent = 'Video Call Masuk';
        incomingCallText.textContent = `${caller} mengundang kamu ke video call.`;
    }

    incomingCallModal.classList.remove('hidden');
    playIncomingRing();
}

function acceptIncomingCall() {
    if (!pendingIncomingCall) return;

    const type = pendingIncomingCall.type || 'video';
    const caller = pendingIncomingCall.from_user_name || 'Pengguna lain';

    incomingCallModal.classList.add('hidden');
    sendCallLog(`Menerima panggilan dari ${caller}.`);
    openCallModal(type, true);

    pendingIncomingCall = null;
}

function rejectIncomingCall() {
    if (!pendingIncomingCall) {
        incomingCallModal.classList.add('hidden');
        return;
    }

    const caller = pendingIncomingCall.from_user_name || 'Pengguna lain';

    sendCallLog(`Menolak panggilan dari ${caller}.`);

    incomingCallModal.classList.add('hidden');
    pendingIncomingCall = null;
}

function acceptCallFromChat(type = 'video', caller = 'Pengguna lain') {
    sendCallLog(`Menerima panggilan dari ${caller}.`);
    openCallModal(type, true);
}

function rejectCallFromChat(caller = 'Pengguna lain') {
    sendCallLog(`Menolak panggilan dari ${caller}.`);
}

function playIncomingRing() {
    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();

        const beep = () => {
            const oscillator = audioContext.createOscillator();
            const gain = audioContext.createGain();

            oscillator.connect(gain);
            gain.connect(audioContext.destination);

            oscillator.frequency.value = 880;
            gain.gain.value = 0.06;

            oscillator.start();
            oscillator.stop(audioContext.currentTime + 0.18);
        };

        beep();
        setTimeout(beep, 350);
        setTimeout(beep, 700);
    } catch (error) {
        console.log('Audio ring tidak aktif:', error);
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        if (!incomingCallModal.classList.contains('hidden')) {
            rejectIncomingCall();
        }

        if (!callModal.classList.contains('hidden')) {
            closeCallModal();
        }
    }
});

setInterval(checkIncomingCall, 2500);
setInterval(pollNewMessages, 2000);
</script>
@endpush
