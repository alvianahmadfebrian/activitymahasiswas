@extends('layouts.chat')

@section('title', 'Lunox AI')

@section('content')
<div class="chatgpt-page">
    <div class="chatgpt-shell">

        <aside class="chat-sidebar">
            <div class="sidebar-top">
                <div class="brand-row">
                    <div>
                        <div class="brand-title">Lunox AI</div>
                        <div class="brand-subtitle">Asisten Mahasiswa</div>
                    </div>
                </div>

                <a href="{{ route('chatbot.new') }}" class="new-chat-btn">
                    <span>✎</span>
                    <span>Obrolan baru</span>
                </a>

                <div class="history-title">Riwayat Chat</div>

                <div class="history-list">
                    @forelse($sessions ?? [] as $session)
                        <div class="history-row">
                            <a href="{{ route('chatbot.show', $session->id) }}"
                               class="history-item {{ isset($currentSession) && $currentSession && (string) $currentSession->id === (string) $session->id ? 'active' : '' }}">
                                <span class="history-text">
                                    {{ \Illuminate\Support\Str::limit($session->title, 32) }}
                                </span>
                            </a>

                            <form method="POST"
                                  action="{{ route('chatbot.destroy', $session->id) }}"
                                  class="delete-chat-form">
                                @csrf
                                @method('DELETE')

                                <button type="button" class="delete-chat-btn" title="Hapus chat">
                                    ×
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="empty-history">
                            Belum ada riwayat chat.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="sidebar-bottom">
                <div class="profile-mini">
                    <div class="profile-avatar">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>

                    <div>
                        <div class="profile-name">{{ auth()->user()->name ?? 'Mahasiswa' }}</div>
                        <div class="profile-plan">{{ auth()->user()->nim ?? 'CampusHub' }}</div>
                    </div>
                </div>
            </div>
        </aside>

        <main class="chat-main">
            <header class="chat-topbar">
                <div class="chat-title-wrap">
                    <div class="chat-title">Lunox AI</div>
                    <div id="aiStatus" class="chat-subtitle">Siap membantu tugas, file, diskusi, dan belajar</div>
                </div>

                <div class="top-actions">
                    <button id="stopSpeakBtn" type="button" class="stop-speak hidden">
                        Stop suara
                    </button>

                    <a href="{{ route('dashboard') }}" class="exit-door-btn" title="Keluar dari Chatbot">
                        <span class="door-icon">↩</span>
                        <span class="door-text">Keluar</span>
                    </a>
                </div>
            </header>

            <section id="chatBox" class="chat-scroll">
                <div id="chatContent" class="chat-content">

                    @if(($messages ?? collect())->count() === 0)
                        <div id="welcomeBox" class="welcome-box">
                            <div class="welcome-avatar-wrap">
                                <div id="avatarPulse" class="avatar-pulse hidden"></div>
                                <div id="aiAvatar" class="welcome-avatar">
                                    <img src="https://api.dicebear.com/7.x/bottts/svg?seed=CampusBot" alt="AI">
                                </div>
                            </div>

                            <h1>Anda sedang mengerjakan apa saat ini?</h1>

                            <div class="prompt-grid">
                                <button type="button" class="quick-btn" data-prompt="Buatkan PDF tentang perkembangan kurikulum di Indonesia dengan bahasa manusia yang baik dan benar">
                                    Buat PDF otomatis
                                </button>

                                <button type="button" class="quick-btn" data-prompt="Buatkan Word tentang fungsi kurikulum bagi masyarakat">
                                    Buat Word otomatis
                                </button>

                                <button type="button" class="quick-btn" data-prompt="Bantu saya menyusun daftar tugas minggu ini">
                                    Susun tugas minggu ini
                                </button>

                                <button type="button" class="quick-btn" data-prompt="Saya mau upload file atau foto, tolong bantu jelaskan">
                                    Tanya file atau foto
                                </button>
                            </div>
                        </div>
                    @else
                        <div id="welcomeBox" class="welcome-box hidden">
                            <div class="welcome-avatar-wrap">
                                <div id="avatarPulse" class="avatar-pulse hidden"></div>
                                <div id="aiAvatar" class="welcome-avatar">
                                    <img src="https://api.dicebear.com/7.x/bottts/svg?seed=CampusBot" alt="AI">
                                </div>
                            </div>

                            <h1>Anda sedang mengerjakan apa saat ini?</h1>
                        </div>

                        @foreach($messages as $message)
                            @php
                                $cleanMessage = preg_replace([
                                    '/\[([^\]]+)\]\((https?:\/\/[^)]+)\)/i',
                                    '/https?:\/\/\S+/i',
                                    '/\*\*(.*?)\*\*/',
                                    '/\*(.*?)\*/',
                                    '/__(.*?)__/',
                                    '/_(.*?)_/',
                                    '/`(.*?)`/',
                                    '/^#{1,6}\s+/m'
                                ], [
                                    '$1',
                                    '',
                                    '$1',
                                    '$1',
                                    '$1',
                                    '$1',
                                    '$1',
                                    ''
                                ], $message->message ?? '');

                                $chatTime = $message->created_at
                                    ? $message->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i')
                                    : '';

                                $fileName = $message->file_name ?? '';
                                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                $isImage = in_array($fileExt, ['jpg', 'jpeg', 'png', 'webp']);

                                $aiFileType = $message->file_type ?? '';
                                $aiFileName = $message->file_name ?? 'campushub-ai-file';
                                $aiIcon = '📎';
                                $aiLabel = 'Download File';

                                if ($aiFileType === 'pdf') {
                                    $aiIcon = '📄';
                                    $aiLabel = 'Download PDF';
                                }

                                if ($aiFileType === 'word' || $aiFileType === 'docx') {
                                    $aiIcon = '📝';
                                    $aiLabel = 'Download Word';
                                }

                                if ($aiFileType === 'image') {
                                    $aiIcon = '🖼️';
                                    $aiLabel = 'Download Gambar';
                                }
                            @endphp

                            @if($message->role === 'user')
                                <div class="msg-row user">
                                    <div>
                                        <div class="msg-bubble user-bubble">
                                            {!! nl2br(e($cleanMessage)) !!}

                                            @if(!empty($message->file_url))
                                                @if($isImage)
                                                    <div class="image-preview-chip">
                                                        <a href="{{ $message->file_url }}" download="{{ $message->file_name }}">
                                                            <img src="{{ $message->file_url }}" alt="{{ $message->file_name }}">
                                                        </a>
                                                        <div class="image-preview-name">
                                                            🖼️ {{ $message->file_name }}
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="file-chip">
                                                        📎 <a href="{{ $message->file_url }}" download="{{ $message->file_name }}">{{ $message->file_name }}</a>
                                                    </div>
                                                @endif
                                            @endif
                                        </div>

                                        <div class="chat-time user-time">
                                            {{ $chatTime }}
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="msg-row ai">
                                    <div class="ai-mini-avatar">AI</div>

                                    <div>
                                        <div class="msg-bubble ai-bubble">
                                            {!! nl2br(e($cleanMessage)) !!}

                                            @if(!empty($message->file_url))
                                                <div class="generated-file-card">
                                                    <div class="generated-file-icon">{{ $aiIcon }}</div>

                                                    <div class="generated-file-info">
                                                        <div class="generated-file-title">{{ $aiFileName }}</div>
                                                        <div class="generated-file-subtitle">
                                                            File siap diunduh
                                                        </div>
                                                    </div>

                                                    <a href="{{ $message->file_url }}" download="{{ $aiFileName }}" class="generated-file-btn">
                                                        {{ $aiLabel }}
                                                    </a>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="chat-time ai-time">
                                            {{ $chatTime }}
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endif

                </div>
            </section>

            <footer class="chat-input-area">
                <div id="filePreviewWrap" class="file-preview hidden">
                    <div class="file-preview-left">
                        <div id="fileThumb" class="file-thumb hidden"></div>
                        <div>
                            <div class="file-preview-label">Lampiran dipilih</div>
                            <div id="filePreviewName" class="file-preview-name"></div>
                        </div>
                    </div>

                    <button id="removeFileBtn" type="button">Hapus</button>
                </div>

                <form id="chatForm" class="chat-form">
                    <input type="hidden" id="chatSessionId" value="{{ $currentSession ? $currentSession->id : '' }}">

                    <input
                        id="fileInput"
                        type="file"
                        accept=".pdf,.doc,.docx,.txt,.csv,.json,.md,.jpg,.jpeg,.png,.webp,image/*"
                        class="hidden"
                    >

                    <button id="fileBtn" type="button" class="circle-btn" title="Upload file atau foto">
                        +
                    </button>

                    <textarea id="messageInput"
                        rows="1"
                        placeholder="Tanyakan apa saja, atau minta AI buat PDF/Word..."
                        class="chat-textarea"></textarea>

                    <button id="voiceBtn" type="button" class="voice-icon-btn" title="Voice">
                        <svg class="mic-icon" viewBox="0 0 24 24" fill="none">
                            <path d="M12 14C13.66 14 15 12.66 15 11V6C15 4.34 13.66 3 12 3C10.34 3 9 4.34 9 6V11C9 12.66 10.34 14 12 14Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M19 11C19 14.87 15.87 18 12 18C8.13 18 5 14.87 5 11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 18V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M8 21H16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>

                    <button id="sendBtn" type="submit" class="send-arrow-btn" title="Kirim">
                        <svg class="arrow-icon" viewBox="0 0 24 24" fill="none">
                            <path d="M12 19V5" stroke="currentColor" stroke-width="2.7" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M5.5 11.5L12 5L18.5 11.5" stroke="currentColor" stroke-width="2.7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </form>
            </footer>
        </main>
    </div>
</div>

<div id="deleteModal" class="delete-modal hidden">
    <div class="delete-modal-card">
        <div class="delete-modal-icon">!</div>

        <h3>Hapus riwayat chat?</h3>
        <p>Chat ini akan dihapus dari riwayat. Tindakan ini tidak bisa dibatalkan.</p>

        <div class="delete-modal-actions">
            <button type="button" id="cancelDeleteBtn" class="modal-cancel-btn">
                Batal
            </button>

            <button type="button" id="confirmDeleteBtn" class="modal-delete-btn">
                Hapus
            </button>
        </div>
    </div>
</div>

<style>
    :root {
        --bg-main: #000000;
        --bg-panel: #070707;
        --bg-panel-soft: #0b0b0b;
        --bg-card: #111111;
        --bg-card-2: #171717;
        --bg-hover: #1f1f1f;
        --border: #222222;
        --border-soft: #2f2f2f;
        --text-main: #f8fafc;
        --text-soft: #cbd5e1;
        --text-muted: #8b8b8b;
        --blue: #2563eb;
        --blue-soft: rgba(37, 99, 235, .18);
        --red: #ef4444;
        --red-soft: rgba(239, 68, 68, .15);
        --green: #22c55e;
        --shadow: 0 20px 80px rgba(0, 0, 0, .45);
    }

    * {
        box-sizing: border-box;
    }

    .hidden {
        display: none !important;
    }

    .chatgpt-page {
        height: 100vh;
        width: 100vw;
        background:
            radial-gradient(circle at 18% 0%, rgba(37, 99, 235, .18), transparent 34rem),
            radial-gradient(circle at 88% 12%, rgba(59, 130, 246, .10), transparent 30rem),
            #000;
        color: var(--text-main);
        overflow: hidden;
    }

    .chatgpt-shell {
        height: 100vh;
        width: 100vw;
        display: grid;
        grid-template-columns: 310px 1fr;
        overflow: hidden;
    }

    /* SIDEBAR */
    .chat-sidebar {
        position: relative;
        height: 100vh;
        background: rgba(7, 7, 7, .88);
        backdrop-filter: blur(22px) saturate(150%);
        -webkit-backdrop-filter: blur(22px) saturate(150%);
        border-right: 1px solid rgba(255, 255, 255, .08);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .chat-sidebar::before {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        background:
            radial-gradient(circle at 25% 0%, rgba(37, 99, 235, .18), transparent 18rem),
            linear-gradient(to bottom, rgba(255,255,255,.035), transparent 20%);
    }

    .sidebar-top {
        position: relative;
        z-index: 2;
        padding: 18px 12px;
        flex: 1;
        min-height: 0;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .brand-row {
        padding: 6px 12px 18px;
    }

    .brand-title {
        font-weight: 900;
        font-size: 24px;
        letter-spacing: -.04em;
        color: #fff;
    }

    .brand-subtitle {
        font-size: 12px;
        color: #9ca3af;
        margin-top: 4px;
        font-weight: 600;
    }

    .new-chat-btn {
        min-height: 50px;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 11px;
        width: 100%;
        padding: 0 14px;
        border-radius: 16px;
        background:
            linear-gradient(135deg, rgba(37, 99, 235, .95), rgba(29, 78, 216, .95));
        font-size: 14px;
        font-weight: 800;
        text-decoration: none;
        margin-bottom: 20px;
        box-shadow: 0 14px 34px rgba(37, 99, 235, .28);
        border: 1px solid rgba(255, 255, 255, .12);
        transition: .18s ease;
    }

    .new-chat-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 18px 44px rgba(37, 99, 235, .38);
        filter: brightness(1.05);
    }

    .new-chat-btn span:first-child {
        width: 28px;
        height: 28px;
        border-radius: 999px;
        background: rgba(255,255,255,.15);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .history-title {
        margin: 0 10px 10px;
        font-weight: 900;
        color: #fff;
        font-size: 12px;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .history-list {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        padding-right: 4px;
    }

    .history-row {
        display: grid;
        grid-template-columns: 1fr 34px;
        align-items: center;
        gap: 6px;
        margin-bottom: 5px;
    }

    .history-item {
        display: flex;
        align-items: center;
        min-height: 42px;
        color: #d4d4d4;
        padding: 0 12px;
        border-radius: 13px;
        font-size: 13px;
        text-decoration: none;
        overflow: hidden;
        border: 1px solid transparent;
        transition: .15s ease;
    }

    .history-item:hover {
        background: rgba(255,255,255,.055);
        border-color: rgba(255,255,255,.07);
        color: #fff;
    }

    .history-item.active {
        background: rgba(37, 99, 235, .17);
        border-color: rgba(59, 130, 246, .35);
        color: #fff;
        box-shadow: inset 0 0 0 1px rgba(37,99,235,.10);
    }

    .history-text {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .delete-chat-btn {
        width: 32px;
        height: 32px;
        border-radius: 11px;
        border: 1px solid transparent;
        background: transparent;
        color: #666;
        font-size: 21px;
        line-height: 1;
        cursor: pointer;
        transition: .15s ease;
    }

    .delete-chat-btn:hover {
        background: rgba(239, 68, 68, .12);
        color: #ff8b8b;
        border-color: rgba(239, 68, 68, .25);
    }

    .empty-history {
        color: #777;
        padding: 10px 12px;
        font-size: 13px;
        border-radius: 14px;
        background: rgba(255,255,255,.03);
        border: 1px dashed rgba(255,255,255,.08);
    }

    .sidebar-bottom {
        position: relative;
        z-index: 2;
        border-top: 1px solid rgba(255, 255, 255, .08);
        padding: 14px;
        background: rgba(0,0,0,.20);
    }

    .profile-mini {
        display: flex;
        align-items: center;
        gap: 12px;
        color: #fff;
        padding: 10px;
        border-radius: 17px;
        background: rgba(255,255,255,.035);
        border: 1px solid rgba(255,255,255,.07);
    }

    .profile-avatar {
        width: 38px;
        height: 38px;
        border-radius: 14px;
        background: linear-gradient(135deg, #ffffff, #cbd5e1);
        color: #111;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        font-size: 13px;
        box-shadow: 0 10px 24px rgba(255,255,255,.08);
    }

    .profile-name {
        font-size: 14px;
        font-weight: 800;
        max-width: 180px;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }

    .profile-plan {
        font-size: 12px;
        color: #9ca3af;
        margin-top: 2px;
    }

    /* MAIN */
    .chat-main {
        height: 100vh;
        min-height: 0;
        display: flex;
        flex-direction: column;
        background:
            radial-gradient(circle at 80% 0%, rgba(37, 99, 235, .10), transparent 28rem),
            #000;
        overflow: hidden;
        position: relative;
    }

    .chat-topbar {
        height: 66px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 30px;
        background: rgba(0,0,0,.70);
        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);
        border-bottom: 1px solid rgba(255,255,255,.07);
        z-index: 10;
    }

    .chat-title {
        font-size: 17px;
        font-weight: 900;
        letter-spacing: -.02em;
        color: #fff;
    }

    .chat-subtitle {
        font-size: 12px;
        color: #888;
        margin-top: 3px;
        font-weight: 500;
    }

    .top-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .stop-speak {
        background: rgba(239, 68, 68, .12);
        color: #ff8b8b;
        border: 1px solid rgba(239, 68, 68, .28);
        padding: 9px 13px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
    }

    .stop-speak:hover {
        background: rgba(239, 68, 68, .18);
    }

    .exit-door-btn {
        height: 42px;
        padding: 0 15px 0 9px;
        border-radius: 999px;
        background: rgba(239, 68, 68, .12);
        color: #fecaca;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 900;
        border: 1px solid rgba(239, 68, 68, .28);
        transition: .18s ease;
    }

    .exit-door-btn:hover {
        background: linear-gradient(135deg, #ef4444, #b91c1c);
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 14px 30px rgba(239, 68, 68, .28);
    }

    .door-icon {
        width: 26px;
        height: 26px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .12);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
    }

    .door-text {
        line-height: 1;
    }

    /* CHAT AREA */
    .chat-scroll {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        padding: 34px 26px 155px;
        scroll-behavior: smooth;
    }

    .chat-content {
        max-width: 920px;
        margin: 0 auto;
        min-height: 100%;
    }

    .welcome-box {
        min-height: calc(100vh - 285px);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .welcome-avatar-wrap {
        position: relative;
        width: 84px;
        height: 84px;
        margin-bottom: 22px;
    }

    .avatar-pulse {
        position: absolute;
        inset: 0;
        border-radius: 999px;
        background: rgba(37, 99, 235, .30);
        animation: pulseAvatar 1.35s infinite;
    }

    .welcome-avatar {
        position: relative;
        z-index: 2;
        width: 84px;
        height: 84px;
        border-radius: 26px;
        background:
            linear-gradient(135deg, rgba(255,255,255,.96), rgba(191,219,254,.96));
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.22);
        box-shadow:
            0 20px 55px rgba(37, 99, 235, .22),
            inset 0 0 0 1px rgba(255,255,255,.40);
    }

    .welcome-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .welcome-avatar.talking {
        animation: talkAvatar .45s infinite alternate;
        box-shadow:
            0 0 0 8px rgba(37,99,235,.10),
            0 20px 55px rgba(37, 99, 235, .25);
    }

    .welcome-box h1 {
        font-size: clamp(28px, 4vw, 46px);
        line-height: 1.05;
        font-weight: 650;
        letter-spacing: -.055em;
        color: #f8fafc;
        margin-bottom: 34px;
        text-align: center;
        max-width: 760px;
    }

    .prompt-grid {
        width: min(790px, 100%);
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .quick-btn {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.09);
        background:
            linear-gradient(180deg, rgba(255,255,255,.060), rgba(255,255,255,.025));
        color: #e5e7eb;
        border-radius: 20px;
        padding: 16px 18px;
        min-height: 66px;
        font-size: 14px;
        line-height: 1.35;
        text-align: left;
        cursor: pointer;
        transition: .18s ease;
        box-shadow: 0 12px 38px rgba(0,0,0,.18);
    }

    .quick-btn::after {
        content: "";
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 20% 10%, rgba(37,99,235,.16), transparent 15rem);
        opacity: 0;
        transition: .18s ease;
    }

    .quick-btn:hover {
        transform: translateY(-2px);
        background:
            linear-gradient(180deg, rgba(255,255,255,.085), rgba(255,255,255,.035));
        border-color: rgba(59, 130, 246, .35);
    }

    .quick-btn:hover::after {
        opacity: 1;
    }

    /* MESSAGES */
    .msg-row {
        display: flex;
        gap: 13px;
        margin-bottom: 26px;
        animation: msgIn .18s ease-out;
    }

    .msg-row.user {
        justify-content: flex-end;
    }

    .msg-row.ai {
        justify-content: flex-start;
    }

    .ai-mini-avatar {
        width: 34px;
        height: 34px;
        border-radius: 13px;
        background: linear-gradient(135deg, #ffffff, #cbd5e1);
        color: #000;
        font-weight: 900;
        font-size: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-top: 5px;
        box-shadow: 0 10px 24px rgba(255,255,255,.06);
    }

    .msg-bubble {
        max-width: min(720px, calc(100vw - 430px));
        line-height: 1.7;
        font-size: 15px;
        white-space: normal;
    }

    .user-bubble {
        background:
            linear-gradient(180deg, rgba(47,47,47,1), rgba(36,36,36,1));
        color: #fff;
        padding: 13px 17px;
        border-radius: 22px;
        border-bottom-right-radius: 7px;
        border: 1px solid rgba(255,255,255,.08);
        box-shadow: 0 14px 36px rgba(0,0,0,.24);
    }

    .ai-bubble {
        color: #f4f4f5;
        padding: 7px 0;
    }

    .ai-bubble br {
        line-height: 1.9;
    }

    .chat-time {
        margin-top: 7px;
        font-size: 10.5px;
        color: #707070;
        font-weight: 500;
    }

    .user-time {
        text-align: right;
        padding-right: 8px;
    }

    .ai-time {
        text-align: left;
    }

    /* FILES */
    .file-chip {
        margin-top: 11px;
        background: rgba(255,255,255,.055);
        border: 1px solid rgba(255,255,255,.10);
        border-radius: 15px;
        padding: 10px 12px;
        font-size: 13px;
        color: #e5e7eb;
    }

    .file-chip a {
        color: #fff;
        text-decoration: underline;
        text-decoration-color: rgba(255,255,255,.35);
    }

    .image-preview-chip {
        margin-top: 13px;
        background: rgba(255,255,255,.055);
        border: 1px solid rgba(255,255,255,.10);
        border-radius: 18px;
        overflow: hidden;
        width: 250px;
        box-shadow: 0 14px 34px rgba(0,0,0,.25);
    }

    .image-preview-chip img {
        width: 100%;
        height: 165px;
        object-fit: cover;
        display: block;
    }

    .image-preview-name {
        padding: 10px 12px;
        font-size: 13px;
        color: #e5e5e5;
        word-break: break-word;
    }

    .generated-file-card {
        margin-top: 17px;
        width: min(540px, 100%);
        background:
            linear-gradient(180deg, rgba(255,255,255,.065), rgba(255,255,255,.035));
        border: 1px solid rgba(255,255,255,.12);
        border-radius: 20px;
        padding: 14px;
        display: grid;
        grid-template-columns: 48px 1fr auto;
        gap: 13px;
        align-items: center;
        box-shadow: 0 15px 42px rgba(0,0,0,.26);
    }

    .generated-file-icon {
        width: 48px;
        height: 48px;
        border-radius: 16px;
        background: rgba(255,255,255,.08);
        border: 1px solid rgba(255,255,255,.12);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }

    .generated-file-info {
        min-width: 0;
    }

    .generated-file-title {
        color: #fff;
        font-size: 14px;
        font-weight: 900;
        word-break: break-word;
    }

    .generated-file-subtitle {
        color: #9ca3af;
        font-size: 12px;
        margin-top: 3px;
    }

    .generated-file-btn {
        min-height: 40px;
        padding: 0 15px;
        border-radius: 999px;
        background: #fff;
        color: #000;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        font-size: 13px;
        font-weight: 900;
        white-space: nowrap;
        transition: .16s ease;
    }

    .generated-file-btn:hover {
        background: #e5e7eb;
        transform: translateY(-1px);
    }

    /* INPUT AREA */
    .chat-input-area {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        padding: 18px 26px 30px;
        background:
            linear-gradient(to top, #000 72%, rgba(0,0,0,.86) 86%, rgba(0,0,0,0));
        z-index: 20;
    }

    .file-preview {
        width: min(900px, 100%);
        margin: 0 auto 10px;
        background: rgba(24,24,24,.92);
        border: 1px solid rgba(255,255,255,.10);
        border-radius: 20px;
        padding: 12px 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 15px 45px rgba(0,0,0,.25);
    }

    .file-preview-left {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .file-thumb {
        width: 54px;
        height: 54px;
        border-radius: 15px;
        overflow: hidden;
        background: #111;
        border: 1px solid rgba(255,255,255,.12);
        flex-shrink: 0;
    }

    .file-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .file-preview-label {
        color: #aaa;
        font-size: 12px;
        font-weight: 600;
    }

    .file-preview-name {
        color: #fff;
        font-weight: 800;
        font-size: 14px;
        word-break: break-word;
    }

    .file-preview button {
        color: #ff8585;
        font-weight: 900;
        border: 0;
        background: transparent;
        cursor: pointer;
        padding: 8px 10px;
        border-radius: 10px;
    }

    .file-preview button:hover {
        background: rgba(239, 68, 68, .12);
    }

    .chat-form {
        width: min(900px, 100%);
        min-height: 64px;
        margin: 0 auto;
        background: rgba(36, 36, 36, .92);
        border: 1px solid rgba(255,255,255,.10);
        border-radius: 999px;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 10px;
        box-shadow:
            0 24px 75px rgba(0,0,0,.55),
            inset 0 1px 0 rgba(255,255,255,.06);
        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);
        transition: .18s ease;
    }

    .chat-form:focus-within {
        border-color: rgba(59, 130, 246, .55);
        box-shadow:
            0 24px 75px rgba(0,0,0,.55),
            0 0 0 4px rgba(37,99,235,.12),
            inset 0 1px 0 rgba(255,255,255,.06);
    }

    .circle-btn,
    .voice-icon-btn {
        width: 42px;
        height: 42px;
        border-radius: 999px;
        border: 0;
        background: transparent;
        color: #f1f1f1;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: .18s ease;
        flex-shrink: 0;
    }

    .circle-btn {
        font-size: 30px;
        line-height: 1;
    }

    .circle-btn:hover,
    .voice-icon-btn:hover {
        background: rgba(255,255,255,.10);
    }

    .voice-icon-btn.listening {
        background: var(--red);
        color: #fff;
        box-shadow: 0 0 0 5px rgba(239, 68, 68, .18);
    }

    .mic-icon {
        width: 23px;
        height: 23px;
    }

    .chat-textarea {
        flex: 1;
        min-height: 42px;
        max-height: 130px;
        background: transparent;
        color: #fff;
        border: 0;
        outline: none;
        resize: none;
        padding: 10px 8px;
        font-size: 15.5px;
        line-height: 1.45;
    }

    .chat-textarea::placeholder {
        color: #9ca3af;
    }

    .send-arrow-btn {
        width: 52px;
        height: 52px;
        border-radius: 999px;
        border: 0;
        background: linear-gradient(135deg, #ffffff, #dbeafe);
        color: #000;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: .18s ease;
        flex-shrink: 0;
        box-shadow: 0 14px 28px rgba(255,255,255,.10);
    }

    .send-arrow-btn:hover {
        background: #ffffff;
        transform: scale(1.04);
    }

    .send-arrow-btn:active {
        transform: scale(.96);
    }

    .arrow-icon {
        width: 27px;
        height: 27px;
    }

    /* DELETE MODAL */
    .delete-modal {
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: rgba(0, 0, 0, .74);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
    }

    .delete-modal-card {
        width: min(420px, 100%);
        background:
            linear-gradient(180deg, rgba(23,23,23,.98), rgba(12,12,12,.98));
        border: 1px solid rgba(255,255,255,.10);
        border-radius: 26px;
        padding: 28px;
        color: white;
        box-shadow: 0 35px 100px rgba(0, 0, 0, .65);
        text-align: center;
        animation: modalPop .18s ease-out;
    }

    .delete-modal-icon {
        width: 54px;
        height: 54px;
        border-radius: 999px;
        background: rgba(239, 68, 68, .12);
        color: #f87171;
        border: 1px solid rgba(239, 68, 68, .35);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
        font-size: 26px;
        font-weight: 900;
    }

    .delete-modal-card h3 {
        font-size: 22px;
        font-weight: 900;
        margin: 0 0 8px;
        letter-spacing: -.02em;
    }

    .delete-modal-card p {
        color: #a3a3a3;
        font-size: 14px;
        line-height: 1.6;
        margin: 0;
    }

    .delete-modal-actions {
        display: flex;
        gap: 12px;
        margin-top: 24px;
    }

    .modal-cancel-btn,
    .modal-delete-btn {
        flex: 1;
        height: 46px;
        border-radius: 999px;
        border: 0;
        font-weight: 900;
        cursor: pointer;
        transition: .16s ease;
    }

    .modal-cancel-btn {
        background: rgba(255,255,255,.08);
        color: #fff;
    }

    .modal-cancel-btn:hover {
        background: rgba(255,255,255,.13);
    }

    .modal-delete-btn {
        background: linear-gradient(135deg, #ef4444, #b91c1c);
        color: white;
    }

    .modal-delete-btn:hover {
        filter: brightness(1.08);
        transform: translateY(-1px);
    }

    /* SCROLLBAR */
    .chat-scroll::-webkit-scrollbar,
    .history-list::-webkit-scrollbar {
        width: 8px;
    }

    .chat-scroll::-webkit-scrollbar-thumb,
    .history-list::-webkit-scrollbar-thumb {
        background: #2f2f2f;
        border-radius: 999px;
        border: 2px solid transparent;
        background-clip: padding-box;
    }

    .chat-scroll::-webkit-scrollbar-track,
    .history-list::-webkit-scrollbar-track {
        background: transparent;
    }

    /* ANIMATION */
    @keyframes modalPop {
        from {
            opacity: 0;
            transform: scale(.95) translateY(8px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    @keyframes pulseAvatar {
        from {
            transform: scale(1);
            opacity: .75;
        }
        to {
            transform: scale(1.45);
            opacity: 0;
        }
    }

    @keyframes talkAvatar {
        from {
            transform: scale(1) translateY(0);
        }
        to {
            transform: scale(1.07) translateY(-3px);
        }
    }

    @keyframes msgIn {
        from {
            opacity: 0;
            transform: translateY(8px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* RESPONSIVE */
    @media (max-width: 1100px) {
        .chatgpt-shell {
            grid-template-columns: 280px 1fr;
        }

        .msg-bubble {
            max-width: min(680px, calc(100vw - 380px));
        }
    }

    @media (max-width: 900px) {
        .chatgpt-shell {
            grid-template-columns: 1fr;
        }

        .chat-sidebar {
            display: none;
        }

        .prompt-grid {
            grid-template-columns: 1fr;
        }

        .chat-topbar {
            padding: 0 16px;
        }

        .chat-scroll {
            padding-left: 16px;
            padding-right: 16px;
        }

        .chat-input-area {
            padding-left: 16px;
            padding-right: 16px;
        }

        .msg-bubble {
            max-width: calc(100vw - 58px);
        }

        .image-preview-chip {
            width: 210px;
        }

        .exit-door-btn {
            padding-right: 11px;
        }

        .door-text {
            display: none;
        }
    }

    @media (max-width: 640px) {
        .generated-file-card {
            grid-template-columns: 42px 1fr;
        }

        .generated-file-btn {
            grid-column: 1 / -1;
            width: 100%;
        }

        .welcome-box h1 {
            font-size: 28px;
        }

        .chat-form {
            border-radius: 24px;
            align-items: flex-end;
        }

        .send-arrow-btn {
            width: 46px;
            height: 46px;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const chatForm = document.getElementById('chatForm');
    const chatBox = document.getElementById('chatBox');
    const chatContent = document.getElementById('chatContent');
    const messageInput = document.getElementById('messageInput');
    const fileInput = document.getElementById('fileInput');
    const fileBtn = document.getElementById('fileBtn');
    const filePreviewWrap = document.getElementById('filePreviewWrap');
    const filePreviewName = document.getElementById('filePreviewName');
    const fileThumb = document.getElementById('fileThumb');
    const removeFileBtn = document.getElementById('removeFileBtn');
    const voiceBtn = document.getElementById('voiceBtn');
    const aiStatus = document.getElementById('aiStatus');
    const aiAvatar = document.getElementById('aiAvatar');
    const avatarPulse = document.getElementById('avatarPulse');
    const stopSpeakBtn = document.getElementById('stopSpeakBtn');
    const welcomeBox = document.getElementById('welcomeBox');
    const chatSessionIdInput = document.getElementById('chatSessionId');

    const deleteModal = document.getElementById('deleteModal');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

    let selectedFile = null;
    let recognition = null;
    let isListening = false;
    let selectedVoice = null;
    let shouldSpeakReply = false;
    let pendingDeleteForm = null;
    let previewUrl = null;

    function scrollBottom() {
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.innerText = text || '';
        return div.innerHTML;
    }

    function cleanMarkdown(text) {
        return (text || '')
            .replace(/\[([^\]]+)\]\((https?:\/\/[^)]+)\)/g, '$1')
            .replace(/https?:\/\/\S+/g, '')
            .replace(/\*\*(.*?)\*\*/g, '$1')
            .replace(/\*(.*?)\*/g, '$1')
            .replace(/__(.*?)__/g, '$1')
            .replace(/_(.*?)_/g, '$1')
            .replace(/`(.*?)`/g, '$1')
            .replace(/^#{1,6}\s+/gm, '')
            .replace(/^\s*[-*]\s+/gm, '• ')
            .replace(/\n{3,}/g, '\n\n')
            .trim();
    }

    function formatDateTime() {
        const now = new Date();

        return now.toLocaleString('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function hideWelcome() {
        if (welcomeBox) {
            welcomeBox.classList.add('hidden');
        }
    }

    function makeGeneratedFileHtml(fileUrl = null, fileType = null, fileName = null) {
    if (!fileUrl) return '';

    let icon = '📎';
    let label = 'Download File';

    if (fileType === 'pdf') {
        icon = '📄';
        label = 'Download PDF';
    }

    if (fileType === 'word' || fileType === 'docx') {
        icon = '📝';
        label = 'Download Word';
    }

    if (fileType === 'image') {
        icon = '🖼️';
        label = 'Download Gambar';
    }

    const safeFileName = fileName || 'campushub-ai-file';

    return `
        <div class="generated-file-card">
            <div class="generated-file-icon">${icon}</div>

            <div class="generated-file-info">
                <div class="generated-file-title">${escapeHtml(safeFileName)}</div>
                <div class="generated-file-subtitle">File siap diunduh</div>
            </div>

            <a href="${fileUrl}" class="generated-file-btn">
                ${label}
            </a>
        </div>
    `;
}

    function appendUser(text, file = null) {
        hideWelcome();

        const row = document.createElement('div');
        row.className = 'msg-row user';

        let fileHtml = '';

        if (file) {
            const isImage = file.type && file.type.startsWith('image/');

            if (isImage && previewUrl) {
                fileHtml = `
                    <div class="image-preview-chip">
                        <img src="${previewUrl}" alt="${escapeHtml(file.name)}">
                        <div class="image-preview-name">🖼️ ${escapeHtml(file.name)}</div>
                    </div>
                `;
            } else {
                fileHtml = `<div class="file-chip">📎 ${escapeHtml(file.name)}</div>`;
            }
        }

        row.innerHTML = `
            <div>
                <div class="msg-bubble user-bubble">
                    ${escapeHtml(text || 'Saya mengupload lampiran ini.')}
                    ${fileHtml}
                </div>
                <div class="chat-time user-time">${formatDateTime()}</div>
            </div>
        `;

        chatContent.appendChild(row);
        scrollBottom();
    }

    function appendAi(text, fileUrl = null, fileType = null, fileName = null) {
        hideWelcome();

        const row = document.createElement('div');
        row.className = 'msg-row ai';

        const cleanText = cleanMarkdown(text);
        const fileHtml = makeGeneratedFileHtml(fileUrl, fileType, fileName);

        row.innerHTML = `
            <div class="ai-mini-avatar">AI</div>
            <div>
                <div class="msg-bubble ai-bubble">
                    ${escapeHtml(cleanText).replace(/\n/g, '<br>')}
                    ${fileHtml}
                </div>
                <div class="chat-time ai-time">${formatDateTime()}</div>
            </div>
        `;

        chatContent.appendChild(row);
        scrollBottom();
    }

    function appendTyping() {
        hideWelcome();

        const row = document.createElement('div');
        row.className = 'msg-row ai';
        row.id = 'typingRow';

        row.innerHTML = `
            <div class="ai-mini-avatar">AI</div>
            <div>
                <div class="msg-bubble ai-bubble">AI sedang berpikir...</div>
                <div class="chat-time ai-time">${formatDateTime()}</div>
            </div>
        `;

        chatContent.appendChild(row);
        scrollBottom();
    }

    function removeTyping() {
        const row = document.getElementById('typingRow');
        if (row) row.remove();
    }

    function setTalking(state) {
        if (state) {
            if (aiAvatar) aiAvatar.classList.add('talking');
            if (avatarPulse) avatarPulse.classList.remove('hidden');
            aiStatus.textContent = 'AI sedang berbicara...';
            stopSpeakBtn.classList.remove('hidden');
        } else {
            if (aiAvatar) aiAvatar.classList.remove('talking');
            if (avatarPulse) avatarPulse.classList.add('hidden');
            aiStatus.textContent = 'Siap membantu tugas, file, diskusi, dan belajar';
            stopSpeakBtn.classList.add('hidden');
        }
    }

    function loadVoices() {
        if (!('speechSynthesis' in window)) return;

        const voices = speechSynthesis.getVoices();

        selectedVoice =
            voices.find(v => v.lang === 'id-ID') ||
            voices.find(v => v.lang.startsWith('id')) ||
            voices.find(v => v.name.toLowerCase().includes('indonesia')) ||
            voices[0] ||
            null;
    }

    loadVoices();

    if ('speechSynthesis' in window) {
        speechSynthesis.onvoiceschanged = loadVoices;
    }

    function speak(text) {
        if (!('speechSynthesis' in window)) return;

        speechSynthesis.cancel();

        const cleanText = cleanMarkdown(text);

        const utterance = new SpeechSynthesisUtterance(cleanText);
        utterance.lang = selectedVoice?.lang || 'id-ID';
        utterance.rate = 0.95;
        utterance.pitch = 1.1;

        if (selectedVoice) {
            utterance.voice = selectedVoice;
        }

        utterance.onstart = () => setTalking(true);
        utterance.onend = () => setTalking(false);
        utterance.onerror = () => setTalking(false);

        speechSynthesis.speak(utterance);
    }

    stopSpeakBtn.addEventListener('click', () => {
        speechSynthesis.cancel();
        setTalking(false);
        shouldSpeakReply = false;
    });

    async function sendMessage(fromVoice = false) {
    const text = messageInput.value.trim();

    if (!text && !selectedFile) {
        return;
    }

    shouldSpeakReply = fromVoice;

    const fileToSend = selectedFile;
    const previewToClear = previewUrl;

    appendUser(text, fileToSend);
    appendTyping();

    const formData = new FormData();
    formData.append('message', text);

    if (chatSessionIdInput && chatSessionIdInput.value) {
        formData.append('chat_session_id', chatSessionIdInput.value);
    }

    if (fileToSend) {
        formData.append('file', fileToSend);
    }

    messageInput.value = '';
    messageInput.style.height = 'auto';
    selectedFile = null;
    fileInput.value = '';
    filePreviewWrap.classList.add('hidden');
    fileThumb.classList.add('hidden');
    fileThumb.innerHTML = '';

    try {
        const response = await fetch("{{ route('chatbot.send') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                'Accept': 'application/json'
            },
            body: formData
        });

        const data = await response.json();
        removeTyping();

        if (!response.ok) {
            appendAi(data.reply || data.message || 'Terjadi kesalahan saat menghubungi AI.');
            return;
        }

        if (data.chat_session_id && chatSessionIdInput && !chatSessionIdInput.value) {
            chatSessionIdInput.value = data.chat_session_id;
        }

        const reply = data.reply || 'Maaf, saya belum bisa menjawab.';

        appendAi(
            reply,
            data.file_url || null,
            data.file_type || null,
            data.file_name || null
        );

        if (shouldSpeakReply) {
            speak(reply);
        }

        if (data.redirect_url) {
            setTimeout(() => {
                window.location.href = data.redirect_url;
            }, 700);
        }

    } catch (error) {
        removeTyping();
        appendAi('Terjadi kesalahan koneksi ke AI.');
        console.error(error);
    } finally {
        if (previewToClear) {
            setTimeout(() => URL.revokeObjectURL(previewToClear), 1000);
        }

        previewUrl = null;
    }
}

    chatForm.addEventListener('submit', (e) => {
        e.preventDefault();
        sendMessage(false);
    });

    messageInput.addEventListener('input', () => {
        messageInput.style.height = 'auto';
        messageInput.style.height = Math.min(messageInput.scrollHeight, 130) + 'px';
    });

    messageInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage(false);
        }
    });

    fileBtn.addEventListener('click', () => {
        fileInput.click();
    });

    fileInput.addEventListener('change', () => {
        selectedFile = fileInput.files[0] || null;

        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
            previewUrl = null;
        }

        fileThumb.classList.add('hidden');
        fileThumb.innerHTML = '';

        if (selectedFile) {
            const isImage = selectedFile.type && selectedFile.type.startsWith('image/');

            filePreviewName.textContent = isImage
                ? 'Foto: ' + selectedFile.name
                : selectedFile.name;

            if (isImage) {
                previewUrl = URL.createObjectURL(selectedFile);
                fileThumb.innerHTML = `<img src="${previewUrl}" alt="Preview">`;
                fileThumb.classList.remove('hidden');
            }

            filePreviewWrap.classList.remove('hidden');
        }
    });

    removeFileBtn.addEventListener('click', () => {
        selectedFile = null;
        fileInput.value = '';
        filePreviewWrap.classList.add('hidden');
        fileThumb.classList.add('hidden');
        fileThumb.innerHTML = '';

        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
            previewUrl = null;
        }
    });

    document.querySelectorAll('.quick-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            messageInput.value = btn.dataset.prompt;
            sendMessage(false);
        });
    });

    document.querySelectorAll('.delete-chat-form').forEach(form => {
        const btn = form.querySelector('.delete-chat-btn');

        btn.addEventListener('click', () => {
            pendingDeleteForm = form;
            deleteModal.classList.remove('hidden');
        });
    });

    cancelDeleteBtn.addEventListener('click', () => {
        pendingDeleteForm = null;
        deleteModal.classList.add('hidden');
    });

    deleteModal.addEventListener('click', (e) => {
        if (e.target === deleteModal) {
            pendingDeleteForm = null;
            deleteModal.classList.add('hidden');
        }
    });

    confirmDeleteBtn.addEventListener('click', () => {
        if (pendingDeleteForm) {
            pendingDeleteForm.submit();
        }
    });

    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

    if (SpeechRecognition) {
        recognition = new SpeechRecognition();
        recognition.lang = 'id-ID';
        recognition.continuous = false;
        recognition.interimResults = false;

        recognition.onstart = () => {
            isListening = true;
            voiceBtn.classList.add('listening');
            voiceBtn.innerHTML = '■';
            aiStatus.textContent = 'Mendengarkan suara kamu...';
        };

        recognition.onend = () => {
            isListening = false;
            voiceBtn.classList.remove('listening');
            voiceBtn.innerHTML = `
                <svg class="mic-icon" viewBox="0 0 24 24" fill="none">
                    <path d="M12 14C13.66 14 15 12.66 15 11V6C15 4.34 13.66 3 12 3C10.34 3 9 4.34 9 6V11C9 12.66 10.34 14 12 14Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M19 11C19 14.87 15.87 18 12 18C8.13 18 5 14.87 5 11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12 18V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M8 21H16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            `;

            if (!speechSynthesis.speaking) {
                aiStatus.textContent = 'Siap membantu tugas, file, diskusi, dan belajar';
            }
        };

        recognition.onresult = (event) => {
            const transcript = event.results[0][0].transcript;
            messageInput.value = transcript;
            sendMessage(true);
        };

        recognition.onerror = () => {
            isListening = false;
            voiceBtn.classList.remove('listening');
            aiStatus.textContent = 'Voice gagal digunakan';
        };

        voiceBtn.addEventListener('click', () => {
            if (isListening) {
                recognition.stop();
            } else {
                recognition.start();
            }
        });
    } else {
        voiceBtn.disabled = true;
        voiceBtn.innerHTML = '×';
        aiStatus.textContent = 'Browser tidak support voice';
    }

    scrollBottom();
});
</script>
@endsection
