@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Mahasiswa')
@section('eyebrow', 'Ringkasan aktivitas')

@section('content')
@php
    $progress = $totalTasks > 0 ? round(($doneTasks / $totalTasks) * 100) : 0;

    $statCards = [
        [
            'label' => 'Total Tugas',
            'value' => $totalTasks,
            'hint' => 'Semua tugas tersimpan',
            'tone' => 'slate',
        ],
        [
            'label' => 'Tugas Selesai',
            'value' => $doneTasks,
            'hint' => $progress . '% progres selesai',
            'tone' => 'green',
        ],
        [
            'label' => 'File Drive',
            'value' => $totalFiles,
            'hint' => 'File kuliah pribadi',
            'tone' => 'blue',
        ],
        [
            'label' => 'Room Diskusi',
            'value' => $totalRooms,
            'hint' => 'Group dan chat aktif',
            'tone' => 'violet',
        ],
    ];

    $statusBadge = [
        'belum' => 'border-slate-300 text-slate-500 bg-white',
        'proses' => 'border-blue-300 text-blue-600 bg-blue-50/60',
        'selesai' => 'border-emerald-300 text-emerald-600 bg-emerald-50/60',
    ];

    $statusLabel = [
        'belum' => 'Belum',
        'proses' => 'Proses',
        'selesai' => 'Selesai',
    ];

    $formatDate = function ($date) {
        if (!$date) return '-';

        try {
            return \Carbon\Carbon::parse($date)->translatedFormat('d M Y');
        } catch (\Throwable $e) {
            return $date;
        }
    };

    $formatDateTime = function ($date) {
        if (!$date) return '-';

        try {
            return \Carbon\Carbon::parse($date)->translatedFormat('d M Y, H:i');
        } catch (\Throwable $e) {
            return $date;
        }
    };
@endphp

<style>
    .db-page {
        height: calc(100vh - 145px);
        min-height: 620px;
        overflow: hidden;
    }

    .db-card {
        background: rgba(255, 255, 255, 0.82);
        backdrop-filter: blur(18px) saturate(160%);
        -webkit-backdrop-filter: blur(18px) saturate(160%);
        border: 1px solid rgba(200, 220, 255, 0.45);
        border-radius: 20px;
        box-shadow: 0 1px 3px rgba(30, 80, 200, 0.05), 0 8px 32px rgba(30, 80, 200, 0.06);
    }

    .db-hero {
        background:
            radial-gradient(circle at 16% 18%, rgba(37, 99, 235, .22), transparent 28rem),
            radial-gradient(circle at 90% 0%, rgba(14, 165, 233, .18), transparent 25rem),
            rgba(255, 255, 255, .84);
    }

    .db-hero-title {
        font-size: clamp(26px, 3vw, 42px);
        line-height: 1.05;
        letter-spacing: -0.04em;
        color: #0f172a;
        font-weight: 700;
    }

    .db-muted {
        color: #64748b;
    }

    .db-soft-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 14px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 600;
        transition: .15s ease;
    }

    .db-primary-btn {
        background: #2563eb;
        color: white;
        box-shadow: 0 10px 24px rgba(37, 99, 235, .20);
    }

    .db-primary-btn:hover {
        background: #1d4ed8;
        transform: translateY(-1px);
    }

    .db-secondary-btn {
        background: rgba(241, 245, 255, .75);
        color: #334155;
        border: 1px solid rgba(200, 220, 255, .55);
    }

    .db-secondary-btn:hover {
        background: #ffffff;
        border-color: rgba(59, 130, 246, .45);
    }

    .db-progress-track {
        height: 8px;
        background: rgba(226, 232, 240, .85);
        border-radius: 999px;
        overflow: hidden;
    }

    .db-progress-fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #2563eb, #38bdf8);
        transition: width .35s ease;
    }

    .db-stat {
        background: rgba(255, 255, 255, 0.75);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(200, 220, 255, 0.40);
        border-radius: 16px;
        padding: 16px 18px;
    }

    .db-stat-label {
        font-size: 12px;
        font-weight: 500;
        color: #64748b;
        margin-bottom: 5px;
    }

    .db-stat-num {
        font-size: 30px;
        font-weight: 650;
        color: #0f172a;
        line-height: 1;
    }

    .db-stat-hint {
        margin-top: 7px;
        font-size: 11.5px;
        color: #94a3b8;
        font-weight: 500;
    }

    .db-stat.green .db-stat-num { color: #059669; }
    .db-stat.blue .db-stat-num { color: #2563eb; }
    .db-stat.violet .db-stat-num { color: #7c3aed; }

    .db-panel-head {
        padding: 18px 22px 14px;
        border-bottom: 1px solid rgba(200, 220, 255, 0.35);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .db-panel-head h2 {
        font-size: 15px;
        font-weight: 650;
        color: #0f172a;
    }

    .db-panel-head p {
        font-size: 12px;
        color: #94a3b8;
        margin-top: 2px;
    }

    .db-link {
        font-size: 12px;
        font-weight: 600;
        color: #2563eb;
        padding: 7px 10px;
        border-radius: 10px;
        background: rgba(219, 234, 254, .60);
        transition: .15s ease;
        white-space: nowrap;
    }

    .db-link:hover {
        background: rgba(191, 219, 254, .80);
    }

    .db-scroll {
        overflow-y: auto;
    }

    .db-scroll::-webkit-scrollbar {
        width: 4px;
        height: 4px;
    }

    .db-scroll::-webkit-scrollbar-thumb {
        background: rgba(147,197,253,.45);
        border-radius: 999px;
    }

    .db-task-row {
        padding: 13px 16px;
        border-bottom: 1px solid rgba(200, 220, 255, .25);
        transition: .15s ease;
    }

    .db-task-row:hover {
        background: rgba(239, 246, 255, .55);
    }

    .db-task-title {
        font-size: 13px;
        font-weight: 600;
        color: #0f172a;
    }

    .db-task-date {
        margin-top: 3px;
        font-size: 11.5px;
        color: #94a3b8;
    }

    .db-status {
        border: 1px solid;
        border-radius: 999px;
        padding: 4px 9px;
        font-size: 10.5px;
        font-weight: 600;
        white-space: nowrap;
    }

    .db-log-item {
        display: flex;
        gap: 12px;
        padding: 14px 16px;
        border-bottom: 1px solid rgba(200, 220, 255, .25);
    }

    .db-log-dot {
        margin-top: 5px;
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, .12);
        flex-shrink: 0;
    }

    .db-log-title {
        font-size: 13px;
        font-weight: 550;
        color: #334155;
        line-height: 1.45;
    }

    .db-log-date {
        margin-top: 4px;
        font-size: 11px;
        color: #94a3b8;
        font-weight: 500;
    }

    .db-empty {
        text-align: center;
        padding: 44px 20px;
        color: #94a3b8;
        font-size: 13px;
    }

    .db-mini-table {
        width: 100%;
        border-collapse: collapse;
    }

    .db-mini-table th {
        padding: 10px 16px;
        font-size: 10.5px;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #94a3b8;
        font-weight: 650;
        text-align: left;
        background: rgba(239, 246, 255, .70);
        border-bottom: 1px solid rgba(200, 220, 255, .35);
    }

    .db-mini-table td {
        padding: 13px 16px;
        border-bottom: 1px solid rgba(200, 220, 255, .25);
        font-size: 13px;
        vertical-align: middle;
    }

    .db-mini-table tr:hover td {
        background: rgba(239, 246, 255, .55);
    }

    @media (max-width: 1024px) {
        .db-page {
            height: auto;
            min-height: auto;
            overflow: visible;
        }
    }
</style>

<div class="db-page space-y-4">
    <section class="db-card db-hero overflow-hidden p-6 lg:p-7">
        <div class="grid gap-6 lg:grid-cols-[1fr_330px] lg:items-center">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[.22em] text-blue-600">
                    Selamat datang
                </p>

                <h2 class="db-hero-title mt-3">
                    Halo, {{ auth()->user()->name }}
                </h2>

                <p class="db-muted mt-3 max-w-2xl text-sm leading-relaxed">
                    Pantau tugas, file, diskusi, dan riwayat aktivitas kamu dari satu dashboard yang lebih ringkas.
                </p>

                <div class="mt-5 flex flex-wrap gap-3">
                    <a href="{{ route('tasks.index') }}" class="db-soft-btn db-primary-btn">
                        Tambah Tugas
                    </a>

                    <a href="{{ route('drive.index') }}" class="db-soft-btn db-secondary-btn">
                        Upload File
                    </a>
                </div>
            </div>

            <div class="rounded-[18px] border border-blue-100/70 bg-white/65 p-5">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold text-slate-600">Progres tugas</span>
                    <span class="text-sm font-semibold text-blue-600">{{ $progress }}%</span>
                </div>

                <div class="db-progress-track mt-4">
                    <div class="db-progress-fill" style="width: {{ $progress }}%"></div>
                </div>

                <p class="mt-4 text-xs leading-relaxed text-slate-500">
                    {{ $doneTasks }} dari {{ $totalTasks }} tugas sudah selesai. Lanjutkan tugas yang masih berjalan agar tidak menumpuk.
                </p>
            </div>
        </div>
    </section>

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($statCards as $card)
            <div class="db-stat {{ $card['tone'] }}">
                <p class="db-stat-label">{{ $card['label'] }}</p>
                <div class="db-stat-num">{{ $card['value'] }}</div>
                <p class="db-stat-hint">{{ $card['hint'] }}</p>
            </div>
        @endforeach
    </section>

    <section class="grid h-[calc(100%-310px)] min-h-[310px] gap-4 overflow-hidden xl:grid-cols-[1.15fr_.85fr]">
        <div class="db-card flex min-h-0 flex-col overflow-hidden">
            <div class="db-panel-head">
                <div>
                    <h2>Tugas terbaru</h2>
                    <p>Prioritas pekerjaan kuliah kamu.</p>
                </div>

                <a href="{{ route('tasks.index') }}" class="db-link">
                    Lihat semua
                </a>
            </div>

            <div class="db-scroll flex-1">
                @if($tasks->count())
                    <table class="db-mini-table">
                        <thead>
                            <tr>
                                <th>Nama tugas</th>
                                <th style="width: 140px;">Deadline</th>
                                <th style="width: 110px;">Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($tasks as $task)
                                @php
                                    $status = $task->status ?? 'belum';
                                    $badge = $statusBadge[$status] ?? $statusBadge['belum'];
                                @endphp

                                <tr>
                                    <td>
                                        <div class="db-task-title truncate">
                                            {{ $task->title }}
                                        </div>
                                    </td>

                                    <td>
                                        <span class="text-xs text-slate-500">
                                            {{ $formatDate($task->deadline ?? null) }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="db-status {{ $badge }}">
                                            {{ $statusLabel[$status] ?? ucfirst($status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="db-empty">
                        Belum ada tugas. Mulai buat tugas pertama kamu.
                    </div>
                @endif
            </div>
        </div>

        <div class="db-card flex min-h-0 flex-col overflow-hidden">
            <div class="db-panel-head">
                <div>
                    <h2>Riwayat terbaru</h2>
                    <p>Aktivitas terakhir di sistem.</p>
                </div>

                <a href="{{ route('activity.index') }}" class="db-link">
                    Detail
                </a>
            </div>

            <div class="db-scroll flex-1">
                @forelse($logs as $log)
                    <div class="db-log-item">
                        <div class="db-log-dot"></div>

                        <div class="min-w-0 flex-1">
                            <p class="db-log-title">
                                {{ $log->description }}
                            </p>

                            <p class="db-log-date">
                                {{ $formatDateTime($log->created_at ?? null) }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="db-empty">
                        Belum ada riwayat aktivitas.
                    </div>
                @endforelse
            </div>
        </div>
    </section>
</div>
@endsection
