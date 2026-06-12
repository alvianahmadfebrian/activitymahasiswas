@extends('layouts.app')

@section('title', 'Riwayat Aktivitas')
@section('page-title', 'Riwayat Aktivitas')
@section('eyebrow', 'Log sistem')

@section('content')
@php
    $totalLogs = method_exists($logs, 'total') ? $logs->total() : $logs->count();

    $logItems = collect(method_exists($logs, 'items') ? $logs->items() : $logs);

    $typeCounts = $logItems->groupBy('type')->map->count();

    $formatDateTime = function ($date) {
        if (!$date) return '-';

        try {
            return \Carbon\Carbon::parse($date)->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i');
        } catch (\Throwable $e) {
            return $date;
        }
    };

    $formatTime = function ($date) {
        if (!$date) return '-';

        try {
            return \Carbon\Carbon::parse($date)->timezone('Asia/Jakarta')->format('H:i');
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $formatDay = function ($date) {
        if (!$date) return '-';

        try {
            return \Carbon\Carbon::parse($date)->timezone('Asia/Jakarta')->translatedFormat('d M Y');
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $typeStyle = function ($type) {
        $type = strtolower((string) $type);

        if (str_contains($type, 'task') || str_contains($type, 'tugas')) {
            return 'bg-blue-50 text-blue-600 border-blue-200';
        }

        if (str_contains($type, 'drive') || str_contains($type, 'file')) {
            return 'bg-emerald-50 text-emerald-600 border-emerald-200';
        }

        if (str_contains($type, 'discussion') || str_contains($type, 'diskusi') || str_contains($type, 'message')) {
            return 'bg-violet-50 text-violet-600 border-violet-200';
        }

        if (str_contains($type, 'delete') || str_contains($type, 'hapus')) {
            return 'bg-rose-50 text-rose-600 border-rose-200';
        }

        if (str_contains($type, 'login') || str_contains($type, 'auth')) {
            return 'bg-amber-50 text-amber-600 border-amber-200';
        }

        return 'bg-slate-50 text-slate-500 border-slate-200';
    };

    $shortType = function ($type) {
        $type = str_replace(['_', '-'], ' ', (string) $type);
        return \Illuminate\Support\Str::title($type ?: 'Aktivitas');
    };
@endphp

<style>
    .act-page {
        height: calc(100vh - 145px);
        min-height: 620px;
        overflow: hidden;
    }

    .act-card {
        background: rgba(255, 255, 255, 0.82);
        backdrop-filter: blur(18px) saturate(160%);
        -webkit-backdrop-filter: blur(18px) saturate(160%);
        border: 1px solid rgba(200, 220, 255, 0.45);
        border-radius: 20px;
        box-shadow: 0 1px 3px rgba(30, 80, 200, 0.05), 0 8px 32px rgba(30, 80, 200, 0.06);
    }

    .act-stat {
        background: rgba(255, 255, 255, 0.75);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(200, 220, 255, 0.40);
        border-radius: 16px;
        padding: 16px 20px;
    }

    .act-stat-label {
        font-size: 12px;
        font-weight: 500;
        color: #64748b;
        margin-bottom: 4px;
    }

    .act-stat-num {
        font-size: 28px;
        font-weight: 600;
        color: #0f172a;
        line-height: 1;
    }

    .act-panel-head {
        padding: 18px 22px 14px;
        border-bottom: 1px solid rgba(200, 220, 255, 0.35);
    }

    .act-panel-head h2 {
        font-size: 15px;
        font-weight: 650;
        color: #0f172a;
    }

    .act-panel-head p {
        font-size: 12px;
        color: #94a3b8;
        margin-top: 2px;
    }

    .act-input {
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

    .act-input:focus {
        background: #fff;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, .14);
    }

    .act-scroll {
        overflow-y: auto;
    }

    .act-scroll::-webkit-scrollbar {
        width: 4px;
        height: 4px;
    }

    .act-scroll::-webkit-scrollbar-thumb {
        background: rgba(147,197,253,.45);
        border-radius: 999px;
    }

    .act-filter-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 7px 11px;
        font-size: 12px;
        font-weight: 500;
        background: rgba(241, 245, 255, 0.70);
        border: 1px solid rgba(200, 220, 255, 0.55);
        border-radius: 8px;
        color: #475569;
        transition: .15s ease;
        white-space: nowrap;
    }

    .act-filter-btn:hover,
    .act-filter-btn.active {
        background: #2563eb;
        color: #ffffff;
        border-color: #2563eb;
    }

    .act-table-wrap {
        flex: 1;
        overflow: auto;
    }

    .act-table-wrap::-webkit-scrollbar {
        width: 4px;
        height: 4px;
    }

    .act-table-wrap::-webkit-scrollbar-thumb {
        background: rgba(147,197,253,.45);
        border-radius: 999px;
    }

    table.act-table {
        width: 100%;
        min-width: 820px;
        border-collapse: collapse;
    }

    table.act-table thead tr {
        background: rgba(239, 246, 255, 0.70);
        position: sticky;
        top: 0;
        z-index: 20;
    }

    table.act-table th {
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

    table.act-table td {
        padding: 13px 14px;
        font-size: 13px;
        border-bottom: 1px solid rgba(200, 220, 255, 0.22);
        vertical-align: middle;
    }

    .act-row:hover td {
        background: rgba(239, 246, 255, 0.55);
    }

    .act-desc {
        font-weight: 500;
        color: #0f172a;
        line-height: 1.45;
    }

    .act-time {
        font-size: 12px;
        color: #64748b;
        white-space: nowrap;
    }

    .act-day {
        font-size: 11px;
        color: #94a3b8;
        margin-top: 2px;
        white-space: nowrap;
    }

    .act-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 10.5px;
        font-weight: 600;
        padding: 4px 9px;
        border-radius: 999px;
        border: 1px solid;
        letter-spacing: .02em;
        white-space: nowrap;
    }

    .act-timeline {
        position: relative;
        padding: 4px 6px 4px 18px;
    }

    .act-timeline::before {
        content: "";
        position: absolute;
        left: 7px;
        top: 10px;
        bottom: 10px;
        width: 1px;
        background: rgba(147, 197, 253, .45);
    }

    .act-line-item {
        position: relative;
        padding: 12px 12px 12px 18px;
        border-radius: 14px;
        transition: .15s ease;
    }

    .act-line-item:hover {
        background: rgba(239, 246, 255, .65);
    }

    .act-line-item::before {
        content: "";
        position: absolute;
        left: -14px;
        top: 19px;
        width: 9px;
        height: 9px;
        border-radius: 999px;
        background: #3b82f6;
        border: 2px solid #ffffff;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, .12);
    }

    .act-empty {
        text-align: center;
        padding: 56px 20px;
    }

    .act-empty svg {
        width: 36px;
        height: 36px;
        margin: 0 auto 12px;
        display: block;
        color: #cbd5e1;
    }

    .act-empty p {
        font-size: 13px;
        color: #94a3b8;
    }

    .act-pagination {
        border-top: 1px solid rgba(200, 220, 255, 0.35);
        padding: 12px 18px;
        background: rgba(255,255,255,.65);
    }

    @media (max-width: 1024px) {
        .act-page {
            height: auto;
            min-height: auto;
            overflow: visible;
        }
    }
</style>

<div class="act-page space-y-4">
    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="act-stat">
            <p class="act-stat-label">Total riwayat</p>
            <div class="act-stat-num">{{ $totalLogs }}</div>
        </div>

        <div class="act-stat">
            <p class="act-stat-label">Halaman ini</p>
            <div class="act-stat-num text-blue-600">{{ $logs->count() }}</div>
        </div>

        <div class="act-stat">
            <p class="act-stat-label">Jenis aktivitas</p>
            <div class="act-stat-num text-violet-600">{{ $typeCounts->count() }}</div>
        </div>

        <div class="act-stat">
            <p class="act-stat-label">Terbaru</p>
            <div class="mt-1 truncate text-lg font-semibold text-slate-950">
                {{ $logs->count() ? $formatTime($logs->first()->created_at ?? null) : '-' }}
            </div>
        </div>
    </section>

    <section class="act-card flex h-[calc(100%-100px)] min-h-0 flex-col overflow-hidden">
        <div class="act-panel-head">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <h2>Timeline aktivitas</h2>
                    <p>Semua aktivitas penting yang kamu lakukan di CampusHub.</p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <select id="typeFilter" class="act-input sm:w-48">
                        <option value="all">Semua tipe</option>
                        @foreach($typeCounts as $type => $count)
                            <option value="{{ strtolower($type) }}">{{ $shortType($type) }}</option>
                        @endforeach
                    </select>

                    <input
                        id="activitySearch"
                        type="text"
                        placeholder="Cari riwayat..."
                        class="act-input sm:w-72"
                    >
                </div>
            </div>
        </div>

        <div class="border-b border-blue-100/40 px-5 py-3">
            <div class="flex gap-2 overflow-x-auto">
                <button type="button" class="act-filter-btn active" data-type-btn="all">
                    Semua
                </button>

                @foreach($typeCounts->take(6) as $type => $count)
                    <button type="button" class="act-filter-btn" data-type-btn="{{ strtolower($type) }}">
                        {{ $shortType($type) }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="grid min-h-0 flex-1 overflow-hidden xl:grid-cols-[1fr_340px]">
            <div class="act-table-wrap">
                <table class="act-table">
                    <thead>
                        <tr>
                            <th>Aktivitas</th>
                            <th style="width: 160px;">Tipe</th>
                            <th style="width: 170px;">Waktu</th>
                        </tr>
                    </thead>

                    <tbody id="activityList" class="bg-white/30">
                        @forelse($logs as $log)
                            @php
                                $type = strtolower((string) $log->type);
                            @endphp

                            <tr
                                class="act-row activity-card"
                                data-type="{{ $type }}"
                                data-search="{{ strtolower(($log->description ?? '') . ' ' . ($log->type ?? '') . ' ' . ($log->created_at ?? '')) }}"
                            >
                                <td>
                                    <div class="act-desc">
                                        {{ $log->description }}
                                    </div>
                                </td>

                                <td>
                                    <span class="act-badge {{ $typeStyle($log->type) }}">
                                        {{ $shortType($log->type) }}
                                    </span>
                                </td>

                                <td>
                                    <div class="act-time">
                                        {{ $formatTime($log->created_at ?? null) }}
                                    </div>

                                    <div class="act-day">
                                        {{ $formatDay($log->created_at ?? null) }}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="act-empty">
                                    <svg viewBox="0 0 24 24" fill="none">
                                        <path d="M12 8V12L14.5 14.5M21 12A9 9 0 113 12A9 9 0 0121 12Z"
                                            stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>

                                    <p>
                                        Belum ada riwayat.<br>
                                        Aktivitas kamu akan tampil di sini.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div id="emptyFilterState" class="act-empty hidden">
                    <svg viewBox="0 0 24 24" fill="none">
                        <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="1.6"/>
                        <path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                    </svg>

                    <p>
                        Riwayat tidak ditemukan.<br>
                        Coba ubah kata kunci atau filter tipe.
                    </p>
                </div>
            </div>

            <aside class="hidden border-l border-blue-100/40 bg-slate-50/50 p-4 xl:block">
                <div class="mb-3 text-xs font-black uppercase tracking-[.14em] text-slate-400">
                    Timeline cepat
                </div>

                <div class="act-scroll h-[calc(100%-28px)]">
                    <div class="act-timeline">
                        @forelse($logs->take(12) as $log)
                            <div class="act-line-item activity-line" data-type="{{ strtolower((string) $log->type) }}">
                                <p class="line-clamp-2 text-sm font-semibold leading-relaxed text-slate-700">
                                    {{ $log->description }}
                                </p>

                                <p class="mt-1 text-[11px] font-semibold text-slate-400">
                                    {{ $formatDateTime($log->created_at ?? null) }}
                                </p>
                            </div>
                        @empty
                            <div class="py-8 text-center text-sm text-slate-400">
                                Belum ada aktivitas.
                            </div>
                        @endforelse
                    </div>
                </div>
            </aside>
        </div>

        <div class="act-pagination">
            {{ $logs->links() }}
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
const activitySearch = document.getElementById('activitySearch');
const typeFilter = document.getElementById('typeFilter');
const activityRows = document.querySelectorAll('.activity-card');
const activityLines = document.querySelectorAll('.activity-line');
const emptyFilterState = document.getElementById('emptyFilterState');
const typeButtons = document.querySelectorAll('[data-type-btn]');

function applyActivityFilter(typeFromButton = null) {
    const keyword = (activitySearch?.value || '').toLowerCase();
    const selectedType = typeFromButton || typeFilter?.value || 'all';
    let visibleCount = 0;

    activityRows.forEach(row => {
        const rowText = row.dataset.search || row.innerText.toLowerCase();
        const rowType = row.dataset.type || '';

        const matchKeyword = rowText.includes(keyword);
        const matchType = selectedType === 'all' || rowType === selectedType;

        if (matchKeyword && matchType) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    activityLines.forEach(line => {
        const lineType = line.dataset.type || '';
        line.style.display = selectedType === 'all' || lineType === selectedType ? '' : 'none';
    });

    if (emptyFilterState) {
        emptyFilterState.classList.toggle('hidden', visibleCount !== 0 || activityRows.length === 0);
    }
}

activitySearch?.addEventListener('input', () => applyActivityFilter());
typeFilter?.addEventListener('change', () => {
    const current = typeFilter.value;

    typeButtons.forEach(btn => {
        btn.classList.toggle('active', btn.dataset.typeBtn === current);
    });

    applyActivityFilter(current);
});

typeButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        const type = btn.dataset.typeBtn || 'all';

        if (typeFilter) {
            typeFilter.value = type;
        }

        typeButtons.forEach(item => item.classList.remove('active'));
        btn.classList.add('active');

        applyActivityFilter(type);
    });
});
</script>
@endpush
