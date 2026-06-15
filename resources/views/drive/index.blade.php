@extends('layouts.app')

@section('title', 'Drive Mahasiswa')
@section('page-title', 'Drive Mahasiswa')
@section('eyebrow', 'File manager')

@section('content')

@php
    $fileCount = $files->where('is_folder', false)->count();
    $folderCount = $files->where('is_folder', true)->count();

    $getFileType = function ($file) {

        if ($file->is_folder) {
            return [
                'label' => 'FOLDER',
                'class' => 'bg-amber-50 text-amber-600 border-amber-200',
                'svg' => 'folder',
                'preview' => false,
                'type' => 'folder',
            ];
        }

        $name = $file->name ?? '';
        $mime = $file->mime_type ?? '';

        $ext = strtolower(
            pathinfo($name, PATHINFO_EXTENSION)
        );

        if (!$ext && str_contains($mime, '/')) {
            $parts = explode('/', $mime);
            $ext = strtolower(end($parts));
        }

        return match ($ext) {

            'pdf' => [
                'label' => 'PDF',
                'class' => 'bg-rose-50 text-rose-600 border-rose-200',
                'svg' => 'file',
                'preview' => true,
                'type' => 'pdf',
            ],

            'doc', 'docx' => [
                'label' => 'WORD',
                'class' => 'bg-blue-50 text-blue-600 border-blue-200',
                'svg' => 'file',
                'preview' => true,
                'type' => 'doc',
            ],

            'xls', 'xlsx', 'csv' => [
                'label' => 'EXCEL',
                'class' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                'svg' => 'file',
                'preview' => true,
                'type' => 'sheet',
            ],

            'ppt', 'pptx' => [
                'label' => 'PPT',
                'class' => 'bg-violet-50 text-violet-600 border-violet-200',
                'svg' => 'file',
                'preview' => true,
                'type' => 'ppt',
            ],

            'jpg', 'jpeg', 'png', 'gif', 'webp' => [
                'label' => 'IMG',
                'class' => 'bg-indigo-50 text-indigo-600 border-indigo-200',
                'svg' => 'image',
                'preview' => true,
                'type' => 'image',
            ],

            'zip', 'rar', '7z' => [
                'label' => 'ZIP',
                'class' => 'bg-slate-50 text-slate-500 border-slate-200',
                'svg' => 'file',
                'preview' => false,
                'type' => 'zip',
            ],

            default => [
                'label' => strtoupper($ext ?: 'FILE'),
                'class' => 'bg-slate-50 text-slate-500 border-slate-200',
                'svg' => 'file',
                'preview' => false,
                'type' => 'file',
            ],
        };
    };
@endphp

<style>
    .drive-page {
        height: calc(100vh - 145px);
        min-height: 620px;
        overflow: hidden;
    }

    .drive-card {
        background: rgba(255, 255, 255, 0.82);
        backdrop-filter: blur(18px) saturate(160%);
        -webkit-backdrop-filter: blur(18px) saturate(160%);
        border: 1px solid rgba(200, 220, 255, 0.45);
        border-radius: 20px;
        box-shadow: 0 1px 3px rgba(30, 80, 200, 0.05), 0 8px 32px rgba(30, 80, 200, 0.06);
    }

    .drive-stat {
        background: rgba(255, 255, 255, 0.75);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(200, 220, 255, 0.40);
        border-radius: 16px;
        padding: 16px 20px;
    }

    .drive-stat-label {
        font-size: 12px;
        font-weight: 500;
        color: #64748b;
        margin-bottom: 4px;
    }

    .drive-stat-num {
        font-size: 28px;
        font-weight: 600;
        color: #0f172a;
        line-height: 1;
    }

    .drive-panel-head {
        padding: 18px 22px 14px;
        border-bottom: 1px solid rgba(200, 220, 255, 0.35);
    }

    .drive-panel-head h2 {
        font-size: 15px;
        font-weight: 650;
        color: #0f172a;
    }

    .drive-panel-head p {
        font-size: 12px;
        color: #94a3b8;
        margin-top: 2px;
    }

    .drive-field label {
        display: block;
        font-size: 12px;
        font-weight: 500;
        color: #475569;
        margin-bottom: 5px;
    }

    .drive-input {
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

    .drive-input:focus {
        background: #fff;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, .14);
    }

    .drive-file-zone {
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

    .drive-file-zone:hover {
        background: rgba(219, 234, 254, 0.50);
        border-color: #3b82f6;
    }

    .drive-btn-primary {
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
    }

    .drive-btn-primary:hover {
        background: #1d4ed8;
    }

    .drive-btn-primary:active {
        transform: scale(.98);
    }

    .drive-btn-dark {
        width: 100%;
        padding: 11px;
        background: #0f172a;
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        margin-top: 4px;
        transition: background .15s, transform .1s;
    }

    .drive-btn-dark:hover {
        background: #1e293b;
    }

    .drive-controls {
        padding: 12px 18px;
        border-bottom: 1px solid rgba(200, 220, 255, 0.35);
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
    }

    .drive-ctrl-input {
        padding: 7px 11px;
        font-size: 12px;
        background: rgba(241, 245, 255, 0.70);
        border: 1px solid rgba(200, 220, 255, 0.55);
        border-radius: 8px;
        color: #0f172a;
        outline: none;
        transition: border-color .15s, box-shadow .15s;
    }

    .drive-ctrl-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,.12);
    }

    .drive-search {
        width: 230px;
    }

    .drive-back {
        display: inline-flex;
        align-items: center;
        padding: 7px 11px;
        font-size: 12px;
        font-weight: 500;
        background: rgba(241, 245, 255, 0.70);
        border: 1px solid rgba(200, 220, 255, 0.55);
        border-radius: 8px;
        color: #475569;
        transition: .15s;
    }

    .drive-back:hover {
        background: #ffffff;
        border-color: #3b82f6;
    }

    .drive-table-wrap {
        flex: 1;
        overflow: auto;
    }

    .drive-table-wrap::-webkit-scrollbar {
        width: 4px;
        height: 4px;
    }

    .drive-table-wrap::-webkit-scrollbar-thumb {
        background: rgba(147,197,253,.45);
        border-radius: 999px;
    }

    table.drive-table {
        width: 100%;
        min-width: 760px;
        border-collapse: collapse;
    }

    table.drive-table thead tr {
        background: rgba(239, 246, 255, 0.70);
        position: sticky;
        top: 0;
        z-index: 20;
    }

    table.drive-table th {
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

    table.drive-table td {
        padding: 12px 14px;
        font-size: 13px;
        border-bottom: 1px solid rgba(200, 220, 255, 0.22);
        vertical-align: middle;
    }

    .drive-row:hover td {
        background: rgba(239, 246, 255, 0.55);
    }

    .drive-name {
        font-weight: 500;
        color: #0f172a;
        max-width: 360px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .drive-sub {
        font-size: 11px;
        color: #94a3b8;
        margin-top: 2px;
        max-width: 360px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .drive-badge {
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

    .drive-open {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 12px;
        font-weight: 600;
        color: #2563eb;
        background: rgba(219, 234, 254, .65);
        padding: 6px 10px;
        border-radius: 8px;
        transition: .15s;
    }

    .drive-open:hover {
        background: rgba(191, 219, 254, .85);
    }

    .drive-del {
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

    .drive-del:hover {
        background: rgba(254, 226, 226, .70);
        color: #dc2626;
        border-color: #fca5a5;
    }

    .drive-empty {
        text-align: center;
        padding: 56px 20px;
    }

    .drive-empty svg {
        width: 36px;
        height: 36px;
        margin: 0 auto 12px;
        display: block;
        color: #cbd5e1;
    }

    .drive-empty p {
        font-size: 13px;
        color: #94a3b8;
    }

    .drive-path {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 500;
        color: #64748b;
        background: rgba(241, 245, 255, .70);
        border: 1px solid rgba(200, 220, 255, .45);
        padding: 6px 10px;
        border-radius: 8px;
    }
</style>

<div class="drive-page space-y-4">

    <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">

        <div class="drive-stat">
            <p class="drive-stat-label">Lokasi</p>

            <div class="mt-1 text-lg font-semibold text-slate-950 truncate">
                {{ $folder === 'root' ? 'Root' : $folder }}
            </div>
        </div>

        <div class="drive-stat">
            <p class="drive-stat-label">Total item</p>
            <div class="drive-stat-num">
                {{ $files->count() }}
            </div>
        </div>

        <div class="drive-stat">
            <p class="drive-stat-label">Folder</p>
            <div class="drive-stat-num">
                {{ $folderCount }}
            </div>
        </div>

        <div class="drive-stat">
            <p class="drive-stat-label">File</p>
            <div class="drive-stat-num">
                {{ $fileCount }}
            </div>
        </div>

    </section>

     <div
        class="grid h-[calc(100%-100px)] gap-4 overflow-hidden xl:grid-cols-[320px_1fr]"<aside class="drive-card flex h-full flex-col overflow-hidden">

    <div class="drive-panel-head">
        <h2>Upload file</h2>
        <p>Simpan materi, tugas, atau dokumen kuliah.</p>
    </div>

    <div class="border-b border-blue-100/40 px-5 py-5">

        <form
            method="POST"
            action="{{ route('drive.upload') }}"
            enctype="multipart/form-data"
            class="space-y-4"
        >
            @csrf

            <input
                type="hidden"
                name="folder"
                value="{{ $folder }}"
            >

            <div class="drive-field">

                <label>Lampiran file</label>

                <label
                    class="drive-file-zone"
                    for="driveFileInput"
                >
                    <svg
                        width="15"
                        height="15"
                        viewBox="0 0 24 24"
                        fill="none"
                    >
                        <path
                            d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66L9.64 17.2a2 2 0 01-2.83-2.83l8.49-8.48"
                            stroke="#3b82f6"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>

                    <span
                        id="fileNameText"
                        class="truncate"
                    >
                        Pilih file untuk upload
                    </span>
                </label>

                <input
                    id="driveFileInput"
                    type="file"
                    name="file"
                    class="hidden"
                    required
                >
            </div>

            <button
                class="drive-btn-primary"
                type="submit"
            >
                Upload File
            </button>

        </form>

    </div>

    <div class="drive-panel-head">
        <h2>Buat folder</h2>
        <p>Kelompokkan file berdasarkan mata kuliah.</p>
    </div>

    <div class="flex-1 overflow-y-auto px-5 py-5">

        <form
            method="POST"
            action="{{ route('drive.folder') }}"
            class="space-y-4"
        >
            @csrf

            <input
                type="hidden"
                name="current_folder"
                value="{{ $folder }}"
            >

            <div class="drive-field">
                <label>Nama folder</label>

                <input
                    name="folder_name"
                    placeholder="Contoh: Semester 4"
                    class="drive-input"
                    required
                >
            </div>

            <button
                class="drive-btn-dark"
                type="submit"
            >
                Buat Folder
            </button>

        </form>

    </div>

</aside>

<section
    class="drive-card flex h-full min-w-0 flex-col overflow-hidden"
>

    <div class="drive-panel-head">

        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h2>Isi folder</h2>
                <p>Kelola file dan folder kamu.</p>
            </div>

            <div class="drive-path">
                <span>Lokasi:</span>

                <strong
                    class="font-semibold text-slate-700"
                >
                    {{ $folder === 'root' ? 'Root' : $folder }}
                </strong>
            </div>

        </div>

    </div>

    <div class="drive-controls">

        @if($folder !== 'root')
            <a
                href="{{ route('drive.index') }}"
                class="drive-back"
            >
                Kembali ke Root
            </a>
        @endif

        <select
            id="typeFilter"
            class="drive-ctrl-input"
            style="margin-left:auto"
        >
            <option value="all">
                Semua item
            </option>

            <option value="folder">
                Folder
            </option>

            <option value="file">
                File
            </option>
        </select>

        <input
            id="fileSearch"
            type="text"
            placeholder="Cari file atau folder..."
            class="drive-ctrl-input drive-search"
        >

    </div>

    <div class="drive-table-wrap">

        <table class="drive-table">

            <thead>
                <tr>
                    <th>Nama</th>
                    <th style="width:110px">Tipe</th>
                    <th style="width:180px">Keterangan</th>
                    <th style="width:95px">Buka</th>
                    <th style="width:52px"></th>
                </tr>
            </thead>

            <tbody
                id="fileList"
                class="bg-white/30">
                        @forelse($files as $file)

    @php
        $type = $getFileType($file);

        $itemType = $file->is_folder
            ? 'folder'
            : 'file';

        $searchText = strtolower(
            trim(
                ($file->name ?? '') . ' ' .
                ($file->mime_type ?? '') . ' ' .
                ($type['label'] ?? '')
            )
        );

        $folderPath = $folder === 'root'
            ? $file->name
            : $folder.'/'.$file->name;
    @endphp

    <tr
        class="drive-row file-row"
        data-type="{{ $itemType }}"
        data-search="{{ $searchText }}"
    >
        <td>

            @if($file->is_folder)

                <a
                    href="{{ route('drive.index', ['folder' => $folderPath]) }}"
                    class="drive-name block hover:text-blue-600"
                >
                    {{ $file->name }}
                </a>

                <div class="drive-sub">
                    Folder
                </div>

            @else

                <div class="drive-name">
                    {{ $file->name }}
                </div>

                <div class="drive-sub">
                    {{ $file->mime_type ?? 'File' }}
                </div>

            @endif

        </td>

        <td>
            <span
                class="drive-badge {{ $type['class'] }}"
            >
                {{ $type['label'] }}
            </span>
        </td>

        <td>
            <span class="text-xs text-slate-500">
                {{
                    $file->is_folder
                        ? 'Folder dalam drive'
                        : ($file->mime_type ?? 'File tersimpan')
                }}
            </span>
        </td>

        <td>

            @if($file->is_folder)

                <a
                    href="{{ route('drive.index', ['folder' => $folderPath]) }}"
                    class="drive-open"
                >
                    Buka
                </a>

            @else

                @if($type['preview'] ?? false)

                    <button
                        type="button"
                        class="drive-open previewBtn"
                        data-url="{{ $file->url }}"
                        data-type="{{ $type['type'] ?? 'file' }}"
                        data-name="{{ $file->name }}"
                    >
                        Lihat
                    </button>

                @else

                    <a
                        href="{{ $file->url }}"
                        target="_blank"
                        rel="noopener"
                        download
                        class="drive-open"
                    >
                        Download
                    </a>

                @endif

            @endif

        </td>

        <td class="text-right">

            <form
                method="POST"
                action="{{ route('drive.destroy', $file->id) }}"
                onsubmit="return confirm('Hapus item ini?')"
            >
                @csrf
                @method('DELETE')

                <button
                    type="submit"
                    class="drive-del"
                    title="Hapus"
                >
                    <svg
                        width="15"
                        height="15"
                        viewBox="0 0 24 24"
                        fill="none"
                    >
                        <path
                            d="M9 3H15M4 7H20M18 7L17.4 18.2C17.3 19.8 16 21 14.4 21H9.6C8 21 6.7 19.8 6.6 18.2L6 7M10 11V17M14 11V17"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </button>

            </form>

        </td>

    </tr>

@empty

    <tr>

        <td colspan="5" class="drive-empty">

            <svg viewBox="0 0 24 24" fill="none">
                <path
                    d="M3 7.8C3 6.1 4.3 4.8 6 4.8H9.2L11.1 6.7H18C19.7 6.7 21 8 21 9.7V17.2C21 18.9 19.7 20.2 18 20.2H6C4.3 20.2 3 18.9 3 17.2V7.8Z"
                    stroke="currentColor"
                    stroke-width="1.6"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>

            <p>
                Folder masih kosong.
                <br>
                Upload file atau buat folder baru.
            </p>

        </td>

    </tr>

@endforelse
    </tbody>
        </table>

        <div
            id="emptyFilterState"
            class="drive-empty hidden"
        >

            <svg viewBox="0 0 24 24" fill="none">
                <circle
                    cx="11"
                    cy="11"
                    r="8"
                    stroke="currentColor"
                    stroke-width="1.6"
                />

                <path
                    d="M21 21l-4.35-4.35"
                    stroke="currentColor"
                    stroke-width="1.6"
                    stroke-linecap="round"
                />
            </svg>

            <p>
                Item tidak ditemukan.
                <br>
                Coba ubah kata kunci atau filter tipe.
            </p>

        </div>

    </div>

</section>

</div>
</div>

@endsection

@push('scripts')
<script>

const fileSearch =
    document.getElementById('fileSearch');

const typeFilter =
    document.getElementById('typeFilter');

const fileRows =
    document.querySelectorAll('.file-row');

const emptyFilterState =
    document.getElementById('emptyFilterState');

const driveFileInput =
    document.getElementById('driveFileInput');

const fileNameText =
    document.getElementById('fileNameText');

function filterFiles() {

    const keyword =
        (fileSearch?.value || '')
            .toLowerCase()
            .trim();

    const type =
        typeFilter?.value || 'all';

    let visibleCount = 0;

    fileRows.forEach(row => {

        const rowText =
            (row.dataset.search || '')
                .toLowerCase();

        const rowType =
            row.dataset.type || '';

        const matchKeyword =
            rowText.includes(keyword);

        const matchType =
            type === 'all' ||
            rowType === type;

        if (matchKeyword && matchType) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }

    });

    if (emptyFilterState) {

        const hasRows =
            fileRows.length > 0;

        emptyFilterState.classList.toggle(
            'hidden',
            !(hasRows && visibleCount === 0)
        );

    }
}

fileSearch?.addEventListener(
    'input',
    filterFiles
);

typeFilter?.addEventListener(
    'change',
    filterFiles
);

driveFileInput?.addEventListener(
    'change',
    function () {

        const file =
            this.files?.[0];

        if (fileNameText) {

            fileNameText.textContent =
                file
                    ? file.name
                    : 'Pilih file untuk upload';

        }
    }
);

document.addEventListener(
    'DOMContentLoaded',
    filterFiles
);

</script>
@endpush
