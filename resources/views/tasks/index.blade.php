@extends('layouts.app')

@section('title', 'Kelola Tugas')
@section('page-title', 'Kelola Tugas')
@section('eyebrow', 'Manajemen akademik')

@section('content')
@php
    $counts = [
        'total'   => $tasks->count(),
        'belum'   => $tasks->where('status', 'belum')->count(),
        'proses'  => $tasks->where('status', 'proses')->count(),
        'selesai' => $tasks->where('status', 'selesai')->count(),
    ];

    $statusBadge = [
        'belum'   => 'border-slate-300 text-slate-500 bg-white',
        'proses'  => 'border-blue-300 text-blue-600 bg-blue-50/60',
        'selesai' => 'border-emerald-300 text-emerald-600 bg-emerald-50/60',
    ];

    $getFileType = function ($url) {
        if (!$url) return null;
        $path = parse_url($url, PHP_URL_PATH);
        $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return match($ext) {
            'pdf'              => ['label' => 'PDF',   'class' => 'bg-rose-50   text-rose-600   border-rose-200'],
            'doc', 'docx'      => ['label' => 'WORD',  'class' => 'bg-blue-50   text-blue-600   border-blue-200'],
            'xls','xlsx','csv' => ['label' => 'EXCEL', 'class' => 'bg-emerald-50 text-emerald-600 border-emerald-200'],
            'ppt', 'pptx'      => ['label' => 'PPT',   'class' => 'bg-violet-50 text-violet-600  border-violet-200'],
            'jpg','jpeg','png','webp' => ['label' => 'IMG', 'class' => 'bg-indigo-50 text-indigo-600 border-indigo-200'],
            'zip','rar','7z'   => ['label' => 'ZIP',   'class' => 'bg-slate-50  text-slate-500   border-slate-200'],
            default            => ['label' => strtoupper($ext ?: 'FILE'), 'class' => 'bg-slate-50 text-slate-500 border-slate-200'],
        };
    };
@endphp

<style>
    /* ── page shell ─────────────────────────────── */
    .tp-page {
        height: calc(100vh - 145px);
        min-height: 620px;
    }

    /* ── glass card ──────────────────────────────── */
    .tp-card {
        background: rgba(255, 255, 255, 0.82);
        backdrop-filter: blur(18px) saturate(160%);
        -webkit-backdrop-filter: blur(18px) saturate(160%);
        border: 1px solid rgba(200, 220, 255, 0.45);
        border-radius: 20px;
        box-shadow: 0 1px 3px rgba(30, 80, 200, 0.05), 0 8px 32px rgba(30, 80, 200, 0.06);
    }

    /* ── stat cards ──────────────────────────────── */
    .tp-stat {
        background: rgba(255, 255, 255, 0.75);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(200, 220, 255, 0.40);
        border-radius: 16px;
        padding: 16px 20px;
    }

    .tp-stat-label { font-size: 12px; font-weight: 500; color: #64748b; margin-bottom: 4px; }
    .tp-stat-num   { font-size: 28px; font-weight: 600; color: #0f172a; line-height: 1; }
    .tp-stat-num.blue  { color: #2563eb; }
    .tp-stat-num.amber { color: #d97706; }
    .tp-stat-num.green { color: #059669; }

    /* ── panel header ────────────────────────────── */
    .tp-panel-head {
        padding: 18px 22px 14px;
        border-bottom: 1px solid rgba(200, 220, 255, 0.35);
    }
    .tp-panel-head h2  { font-size: 15px; font-weight: 600; color: #0f172a; }
    .tp-panel-head p   { font-size: 12px; color: #94a3b8; margin-top: 2px; }

    /* ── form fields ─────────────────────────────── */
    .tp-field label {
        display: block;
        font-size: 12px;
        font-weight: 500;
        color: #475569;
        margin-bottom: 5px;
    }
    .tp-input {
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
    .tp-input:focus {
        background: #fff;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, .14);
    }

    /* ── file drop zone ──────────────────────────── */
    .tp-file-zone {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 11px 14px;
        background: rgba(241, 245, 255, 0.60);
        border: 1px dashed rgba(147, 197, 253, 0.70);
        border-radius: 10px;
        cursor: pointer;
        transition: border-color .15s, background .15s;
        font-size: 13px;
        color: #64748b;
    }
    .tp-file-zone:hover {
        background: rgba(219, 234, 254, 0.50);
        border-color: #3b82f6;
    }

    /* ── save button ─────────────────────────────── */
    .tp-btn-save {
        width: 100%;
        padding: 11px;
        background: #2563eb;
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        margin-top: 4px;
        transition: background .15s, transform .1s;
        letter-spacing: .01em;
    }
    .tp-btn-save:hover   { background: #1d4ed8; }
    .tp-btn-save:active  { transform: scale(.98); }

    /* ── table controls ──────────────────────────── */
    .tp-controls {
        padding: 12px 18px;
        border-bottom: 1px solid rgba(200, 220, 255, 0.35);
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
    }
    .tp-ctrl-input {
        padding: 7px 11px;
        font-size: 12px;
        background: rgba(241, 245, 255, 0.70);
        border: 1px solid rgba(200, 220, 255, 0.55);
        border-radius: 8px;
        color: #0f172a;
        outline: none;
        transition: border-color .15s, box-shadow .15s;
    }
    .tp-ctrl-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,.12);
    }
    .tp-search { width: 220px; }

    /* ── select menu ─────────────────────────────── */
    .tp-sel-btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 7px 11px;
        font-size: 12px;
        font-weight: 500;
        background: rgba(241, 245, 255, 0.70);
        border: 1px solid rgba(200, 220, 255, 0.55);
        border-radius: 8px;
        color: #475569;
        cursor: pointer;
    }
    .tp-sel-menu {
        display: none;
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        width: 150px;
        background: #fff;
        border: 1px solid rgba(200, 220, 255, 0.60);
        border-radius: 12px;
        box-shadow: 0 6px 24px rgba(30, 80, 200, .10);
        z-index: 40;
        overflow: hidden;
    }
    .tp-sel-menu button {
        display: block;
        width: 100%;
        padding: 9px 13px;
        font-size: 12px;
        text-align: left;
        background: none;
        border: none;
        color: #334155;
        cursor: pointer;
    }
    .tp-sel-menu button:hover { background: rgba(241, 245, 255, .80); }

    /* ── selected count pill ─────────────────────── */
    .tp-sel-count {
        font-size: 12px;
        font-weight: 500;
        color: #2563eb;
        background: rgba(219, 234, 254, .65);
        padding: 5px 10px;
        border-radius: 8px;
        display: none;
    }
    .tp-sel-count.visible { display: block; }

    /* ── custom checkbox ─────────────────────────── */
    .tp-chk       { display: none; }
    .tp-chk-box {
        width: 16px;
        height: 16px;
        border: 1.5px solid #cbd5e1;
        border-radius: 5px;
        background: rgba(241, 245, 255, .70);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: .15s;
    }
    .tp-chk-box svg { width: 10px; height: 10px; opacity: 0; transform: scale(.6); transition: .15s; }
    .tp-chk:checked + .tp-chk-box {
        background: #2563eb;
        border-color: #2563eb;
    }
    .tp-chk:checked + .tp-chk-box svg { opacity: 1; transform: scale(1); }

    /* ── table ───────────────────────────────────── */
    .tp-table-wrap {
        flex: 1;
        overflow: auto;
    }
    .tp-table-wrap::-webkit-scrollbar        { width: 4px; height: 4px; }
    .tp-table-wrap::-webkit-scrollbar-thumb  { background: rgba(147,197,253,.45); border-radius: 999px; }

    table.tp-tbl {
        width: 100%;
        min-width: 760px;
        border-collapse: collapse;
    }
    table.tp-tbl thead tr {
        background: rgba(239, 246, 255, 0.70);
        position: sticky;
        top: 0;
        z-index: 20;
    }
    table.tp-tbl th {
        padding: 10px 14px;
        font-size: 10.5px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #94a3b8;
        text-align: left;
        border-bottom: 1px solid rgba(200, 220, 255, 0.35);
        white-space: nowrap;
    }
    table.tp-tbl td {
        padding: 12px 14px;
        font-size: 13px;
        border-bottom: 1px solid rgba(200, 220, 255, 0.22);
        vertical-align: middle;
    }
    .tp-row:hover td { background: rgba(239, 246, 255, 0.55); }

    .tp-task-title { font-weight: 500; color: #0f172a; }
    .tp-task-desc  { font-size: 11px; color: #94a3b8; margin-top: 2px; max-width: 210px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    /* deadline chips */
    .tp-dl         { font-size: 12px; color: #64748b; display: inline-flex; align-items: center; gap: 5px; }
    .tp-dl-warn    { color: #d97706; }
    .tp-dl-over    { color: #dc2626; }

    /* status select */
    .tp-status-sel {
        appearance: none;
        border: 1px solid;
        border-radius: 999px;
        padding: 4px 11px;
        font-size: 11px;
        font-weight: 500;
        cursor: pointer;
        outline: none;
        transition: box-shadow .15s;
    }
    .tp-status-sel:focus { box-shadow: 0 0 0 3px rgba(59,130,246,.14); }

    /* file badge */
    .tp-fbadge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 10px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 5px;
        border: 1px solid;
        letter-spacing: .03em;
    }

    /* delete button */
    .tp-del {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: transparent;
        border: 1px solid rgba(200, 220, 255, 0.45);
        border-radius: 8px;
        cursor: pointer;
        color: #94a3b8;
        transition: background .12s, color .12s, border-color .12s;
    }
    .tp-del:hover { background: rgba(254, 226, 226, .70); color: #dc2626; border-color: #fca5a5; }

    /* empty state */
    .tp-empty { text-align: center; padding: 56px 20px; }
    .tp-empty svg { width: 36px; height: 36px; margin: 0 auto 12px; display: block; color: #cbd5e1; }
    .tp-empty p   { font-size: 13px; color: #94a3b8; }

    /* alert banners */
    .tp-alert-ok  { border: 1px solid rgba(167, 243, 208, .70); background: rgba(236, 253, 245, .80); color: #065f46; }
    .tp-alert-err { border: 1px solid rgba(252, 165, 165, .70); background: rgba(254, 242, 242, .80); color: #991b1b; }
</style>

<div class="tp-page space-y-4 overflow-hidden">

    {{-- ── alerts ────────────────────────────────── --}}
    @if(session('success'))
        <div class="tp-card tp-alert-ok rounded-2xl px-5 py-3 text-sm font-semibold">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="tp-card tp-alert-err rounded-2xl px-5 py-3 text-sm font-semibold">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    {{-- ── stat row ───────────────────────────────── --}}
    <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="tp-stat">
            <p class="tp-stat-label">Total tugas</p>
            <div class="tp-stat-num">{{ $counts['total'] }}</div>
        </div>
        <div class="tp-stat">
            <p class="tp-stat-label">Belum dikerjakan</p>
            <div class="tp-stat-num">{{ $counts['belum'] }}</div>
        </div>
        <div class="tp-stat">
            <p class="tp-stat-label">Sedang berjalan</p>
            <div class="tp-stat-num amber">{{ $counts['proses'] }}</div>
        </div>
        <div class="tp-stat">
            <p class="tp-stat-label">Selesai</p>
            <div class="tp-stat-num green">{{ $counts['selesai'] }}</div>
        </div>
    </section>

    {{-- ── main grid ──────────────────────────────── --}}
    <div class="grid h-[calc(100%-120px)] gap-4 overflow-hidden xl:grid-cols-[320px_1fr]">

        {{-- aside: form --}}
        <aside class="tp-card flex h-full flex-col overflow-hidden">
            <div class="tp-panel-head">
                <h2>Tambah tugas baru</h2>
                <p>Isi form lalu simpan ke daftar.</p>
            </div>

            <div class="flex-1 overflow-y-auto px-5 py-5 space-y-4">
                <form method="POST" action="{{ route('tasks.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="tp-field">
                        <label>Judul tugas</label>
                        <input name="title" value="{{ old('title') }}" placeholder="Contoh: Makalah Kurikulum"
                            class="tp-input" required>
                    </div>

                    <div class="tp-field">
                        <label>Deadline</label>
                        <input name="deadline" type="date" value="{{ old('deadline') }}" class="tp-input">
                    </div>

                    <div class="tp-field">
                        <label>Status awal</label>
                        <select name="status" class="tp-input">
                            <option value="belum"   @selected(old('status') === 'belum')>Belum</option>
                            <option value="proses"  @selected(old('status') === 'proses')>Proses</option>
                            <option value="selesai" @selected(old('status') === 'selesai')>Selesai</option>
                        </select>
                    </div>

                    <div class="tp-field">
                        <label>Lampiran</label>
                        <label class="tp-file-zone" for="taskFileInput">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66L9.64 17.2a2 2 0 01-2.83-2.83l8.49-8.48"
                                    stroke="#3b82f6" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span id="fileNameText" class="truncate">Pilih file lampiran</span>
                        </label>
                        <input id="taskFileInput" name="file" type="file" class="hidden">
                    </div>

                    <div class="tp-field">
                        <label>Deskripsi</label>
                        <textarea name="description" rows="5" placeholder="Catatan singkat tugas..."
                            class="tp-input resize-none">{{ old('description') }}</textarea>
                    </div>

                    <button type="submit" class="tp-btn-save">Simpan Tugas</button>
                </form>
            </div>
        </aside>

        {{-- main: table --}}
        <section class="tp-card flex h-full min-w-0 flex-col overflow-hidden">

            {{-- controls --}}
            <div class="tp-controls">
                {{-- select menu --}}
                <div class="relative" id="selWrap">
                    <button type="button" id="selectMenuBtn" class="tp-sel-btn">
                        Select
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                    <div id="selectMenu" class="tp-sel-menu">
                        <button type="button" id="selectAllBtn">Pilih semua</button>
                        <button type="button" id="clearSelectBtn">Hapus pilihan</button>
                    </div>
                </div>

                <span id="selectedInfo" class="tp-sel-count">0 dipilih</span>

                <select id="statusFilter" class="tp-ctrl-input" style="margin-left:auto">
                    <option value="all">Semua status</option>
                    <option value="belum">Belum</option>
                    <option value="proses">Proses</option>
                    <option value="selesai">Selesai</option>
                </select>

                <input id="taskSearch" type="text" placeholder="Cari tugas…"
                    class="tp-ctrl-input tp-search">
            </div>

            {{-- table --}}
            <div class="tp-table-wrap">
                <table class="tp-tbl">
                    <thead>
                        <tr>
                            <th style="width:44px"></th>
                            <th>Nama</th>
                            <th style="width:130px">Deadline</th>
                            <th style="width:150px">Status</th>
                            <th style="width:100px">Lampiran</th>
                            <th style="width:52px"></th>
                        </tr>
                    </thead>

                    <tbody id="taskList" class="bg-white/30">
                        @forelse($tasks as $task)
                            @php
                                $status  = $task->status ?? 'belum';
                                $badge   = $statusBadge[$status] ?? $statusBadge['belum'];
                                $file    = $getFileType($task->file_url ?? null);

                                $dl = '-';
                                if (!empty($task->deadline)) {
                                    try {
                                        $dlDate   = \Carbon\Carbon::parse($task->deadline);
                                        $dl       = $dlDate->translatedFormat('d M Y');
                                        $diffDays = now()->startOfDay()->diffInDays($dlDate->startOfDay(), false);
                                    } catch (\Throwable) {
                                        $dl = $task->deadline;
                                        $diffDays = null;
                                    }
                                } else {
                                    $diffDays = null;
                                }

                                $dlClass = '';
                                if ($diffDays !== null) {
                                    if ($diffDays < 0)      $dlClass = 'tp-dl-over';
                                    elseif ($diffDays <= 3) $dlClass = 'tp-dl-warn';
                                }
                            @endphp

                            <tr class="tp-row task-row"
                                data-status="{{ $status }}"
                                data-search="{{ strtolower($task->title . ' ' . ($task->description ?? '') . ' ' . ($task->deadline ?? '') . ' ' . $status) }}">

                                {{-- checkbox --}}
                                <td>
                                    <label class="inline-flex cursor-pointer items-center">
                                        <input type="checkbox" class="tp-chk task-checkbox">
                                        <span class="tp-chk-box">
                                            <svg viewBox="0 0 20 20" fill="none">
                                                <path d="M4.5 10.5L8.2 14L15.5 6" stroke="white" stroke-width="2.2"
                                                    stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                    </label>
                                </td>

                                {{-- title + desc --}}
                                <td>
                                    <div class="tp-task-title">{{ $task->title }}</div>
                                    @if(!empty($task->description))
                                        <div class="tp-task-desc">{{ $task->description }}</div>
                                    @endif
                                </td>

                                {{-- deadline --}}
                                <td>
                                    @if($dl !== '-')
                                        <span class="tp-dl {{ $dlClass }}">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <rect x="3" y="4" width="18" height="18" rx="3" stroke="currentColor" stroke-width="1.8"/>
                                                <path d="M16 2v4M8 2v4M3 10h18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                            </svg>
                                            {{ $dl }}
                                        </span>
                                    @else
                                        <span style="color:#cbd5e1;font-size:12px">—</span>
                                    @endif
                                </td>

                                {{-- status --}}
                                <td>
                                    <form method="POST" action="{{ route('tasks.status', $task->_id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" onchange="this.form.submit()"
                                            class="tp-status-sel {{ $badge }}">
                                            <option value="belum"   @selected($status === 'belum')>Belum</option>
                                            <option value="proses"  @selected($status === 'proses')>Proses</option>
                                            <option value="selesai" @selected($status === 'selesai')>Selesai</option>
                                        </select>
                                    </form>
                                </td>

                                {{-- file --}}
                                <td>
                                    @if($task->file_url && $file)
                                        <a href="{{ $task->file_url }}" target="_blank"
                                            class="tp-fbadge {{ $file['class'] }} hover:opacity-80 transition-opacity">
                                            {{ $file['label'] }}
                                        </a>
                                    @else
                                        <span style="color:#cbd5e1;font-size:12px">—</span>
                                    @endif
                                </td>

                                {{-- delete --}}
                                <td class="text-right">
                                    <form method="POST" action="{{ route('tasks.destroy', $task->_id) }}"
                                        onsubmit="return confirm('Hapus tugas ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="tp-del" title="Hapus">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                                                <path d="M9 3H15M4 7H20M18 7L17.4 18.2C17.3 19.8 16 21 14.4 21H9.6C8 21 6.7 19.8 6.6 18.2L6 7M10 11V17M14 11V17"
                                                    stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="tp-empty">
                                    <svg viewBox="0 0 24 24" fill="none">
                                        <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                            stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                    </svg>
                                    <p>Belum ada tugas.<br>Gunakan form di samping untuk membuat tugas pertama.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div id="emptyFilterState" class="tp-empty hidden">
                    <svg viewBox="0 0 24 24" fill="none" style="width:36px;height:36px;margin:0 auto 12px;display:block;color:#cbd5e1">
                        <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="1.6"/>
                        <path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                        <path d="M8 11h6M11 8v6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                    </svg>
                    <p>Tugas tidak ditemukan.<br>Coba ubah kata kunci atau filter status.</p>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
const taskSearch      = document.getElementById('taskSearch');
const statusFilter    = document.getElementById('statusFilter');
const taskRows        = document.querySelectorAll('.task-row');
const emptyFilter     = document.getElementById('emptyFilterState');
const taskFileInput   = document.getElementById('taskFileInput');
const fileNameText    = document.getElementById('fileNameText');
const selectMenuBtn   = document.getElementById('selectMenuBtn');
const selectMenu      = document.getElementById('selectMenu');
const selectAllBtn    = document.getElementById('selectAllBtn');
const clearSelectBtn  = document.getElementById('clearSelectBtn');
const selectedInfo    = document.getElementById('selectedInfo');

function filterTasks() {
    const kw     = (taskSearch?.value || '').toLowerCase();
    const status = statusFilter?.value || 'all';
    let visible  = 0;

    taskRows.forEach(row => {
        const text    = row.dataset.search || row.innerText.toLowerCase();
        const rowSt   = row.dataset.status || '';
        const matchKw = text.includes(kw);
        const matchSt = status === 'all' || rowSt === status;

        row.style.display = (matchKw && matchSt) ? '' : 'none';
        if (matchKw && matchSt) visible++;
    });

    updateSelectedInfo();

    if (emptyFilter) {
        emptyFilter.classList.toggle('hidden', visible !== 0 || taskRows.length === 0);
    }
}

function updateSelectedInfo() {
    const n = document.querySelectorAll('.task-checkbox:checked').length;
    if (!selectedInfo) return;
    if (n > 0) {
        selectedInfo.textContent = `${n} dipilih`;
        selectedInfo.classList.add('visible');
    } else {
        selectedInfo.classList.remove('visible');
    }
}

taskSearch?.addEventListener('input', filterTasks);
statusFilter?.addEventListener('change', filterTasks);

taskFileInput?.addEventListener('change', function () {
    const f = this.files?.[0];
    if (fileNameText) fileNameText.textContent = f ? f.name : 'Pilih file lampiran';
});

document.querySelectorAll('.task-checkbox').forEach(c => {
    c.addEventListener('change', updateSelectedInfo);
});

selectMenuBtn?.addEventListener('click', e => {
    e.stopPropagation();
    selectMenu?.classList.toggle('hidden');
});

selectAllBtn?.addEventListener('click', () => {
    document.querySelectorAll('.task-row').forEach(row => {
        if (row.style.display !== 'none') {
            const cb = row.querySelector('.task-checkbox');
            if (cb) cb.checked = true;
        }
    });
    selectMenu?.classList.add('hidden');
    updateSelectedInfo();
});

clearSelectBtn?.addEventListener('click', () => {
    document.querySelectorAll('.task-checkbox').forEach(c => c.checked = false);
    selectMenu?.classList.add('hidden');
    updateSelectedInfo();
});

document.addEventListener('click', () => selectMenu?.classList.add('hidden'));
</script>
@endpush
