<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>

    <style>
        @page {
            margin: 1.8cm 1.7cm 1.8cm 1.7cm;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 11.5px;
            line-height: 1.65;
            color: #1f2937;
            background: #ffffff;
        }

        .cover {
            margin-bottom: 24px;
            padding-bottom: 18px;
            border-bottom: 3px solid #2563eb;
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 999px;
            background: #dbeafe;
            color: #1d4ed8;
            font-size: 9px;
            font-weight: bold;
            letter-spacing: .6px;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        .document-title {
            font-size: 25px;
            line-height: 1.25;
            font-weight: bold;
            color: #0f172a;
            margin: 0 0 8px 0;
        }

        .document-subtitle {
            font-size: 10px;
            color: #64748b;
        }

        .meta-box {
            width: 100%;
            border-collapse: collapse;
            margin: 0 0 24px 0;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
        }

        .meta-box td {
            padding: 7px 10px;
            font-size: 10px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }

        .meta-box tr:last-child td {
            border-bottom: none;
        }

        .meta-label {
            width: 105px;
            color: #334155;
            font-weight: bold;
        }

        .meta-value {
            color: #475569;
        }

        .content {
            width: 100%;
        }

        .h1 {
            font-size: 17px;
            line-height: 1.35;
            font-weight: bold;
            color: #0f172a;
            margin: 20px 0 10px 0;
            padding-bottom: 6px;
            border-bottom: 1px solid #e5e7eb;
        }

        .h2 {
            font-size: 14px;
            line-height: 1.4;
            font-weight: bold;
            color: #111827;
            margin: 16px 0 8px 0;
        }

        .h3 {
            font-size: 12.5px;
            line-height: 1.4;
            font-weight: bold;
            color: #1f2937;
            margin: 13px 0 6px 0;
        }

        .paragraph {
            font-size: 11.5px;
            line-height: 1.68;
            margin: 0 0 9px 0;
            text-align: justify;
        }

        .list {
            margin: 4px 0 12px 20px;
            padding: 0;
        }

        .list li {
            margin-bottom: 6px;
            line-height: 1.6;
            text-align: justify;
        }

        .number-list {
            margin: 4px 0 12px 21px;
            padding: 0;
        }

        .number-list li {
            margin-bottom: 7px;
            line-height: 1.6;
            text-align: justify;
        }

        .quote-box {
            margin: 12px 0;
            padding: 10px 13px;
            background: #f8fafc;
            border-left: 4px solid #94a3b8;
            color: #475569;
            font-size: 11px;
            font-style: italic;
        }

        .note-box {
            margin: 12px 0;
            padding: 10px 13px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-left: 4px solid #2563eb;
            color: #1e3a8a;
            font-size: 11px;
            line-height: 1.6;
        }

        .small-space {
            height: 4px;
        }

        .footer {
            margin-top: 28px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            font-size: 9px;
            color: #6b7280;
            text-align: center;
        }
    </style>
</head>
<body>
@php
    $raw = $content ?? '';
    $raw = str_replace(["\r\n", "\r"], "\n", $raw);
    $raw = preg_replace('/\[([^\]]+)\]\((.*?)\)/', '$1', $raw);
    $raw = preg_replace('/https?:\/\/\S+/i', '', $raw);
    $raw = str_replace(['**', '__', '`'], '', $raw);
    $raw = preg_replace("/\n{3,}/", "\n\n", $raw);

    $lines = explode("\n", $raw);

    $bulletItems = [];
    $numberItems = [];

    $flushBulletList = function () use (&$bulletItems) {
        if (empty($bulletItems)) {
            return '';
        }

        $html = '<ul class="list">';
        foreach ($bulletItems as $item) {
            $html .= '<li>' . e($item) . '</li>';
        }
        $html .= '</ul>';

        $bulletItems = [];
        return $html;
    };

    $flushNumberList = function () use (&$numberItems) {
        if (empty($numberItems)) {
            return '';
        }

        $html = '<ol class="number-list">';
        foreach ($numberItems as $item) {
            $html .= '<li>' . e($item) . '</li>';
        }
        $html .= '</ol>';

        $numberItems = [];
        return $html;
    };
@endphp

<div class="cover">
    <div class="badge">Generated by CampusHub AI</div>

    <div class="document-title">
        {{ $title }}
    </div>

    <div class="document-subtitle">
        Dokumen ini dibuat otomatis oleh sistem AI CampusHub.
    </div>
</div>

<table class="meta-box">
    <tr>
        <td class="meta-label">Judul</td>
        <td class="meta-value">{{ $title }}</td>
    </tr>
    <tr>
        <td class="meta-label">Tanggal</td>
        <td class="meta-value">{{ now()->format('d M Y H:i') }}</td>
    </tr>
    <tr>
        <td class="meta-label">Sumber</td>
        <td class="meta-value">CampusHub AI</td>
    </tr>
</table>

<div class="content">
    @foreach($lines as $line)
        @php
            $trimmed = trim($line);
            $trimmed = preg_replace('/\s+/', ' ', $trimmed);
        @endphp

        @if($trimmed === '')
            {!! $flushBulletList() !!}
            {!! $flushNumberList() !!}
            <div class="small-space"></div>
            @continue
        @endif

        @if(preg_match('/^[-•*]\s+(.+)/u', $trimmed, $matches))
            @php $bulletItems[] = $matches[1]; @endphp
            @continue
        @endif

        @if(preg_match('/^\d+[\.\)]\s+(.+)/u', $trimmed, $matches))
            @php $numberItems[] = $matches[1]; @endphp
            @continue
        @endif

        {!! $flushBulletList() !!}
        {!! $flushNumberList() !!}

        @if(preg_match('/^###\s+(.+)/u', $trimmed, $matches))
            <div class="h3">{{ e($matches[1]) }}</div>
        @elseif(preg_match('/^##\s+(.+)/u', $trimmed, $matches))
            <div class="h2">{{ e($matches[1]) }}</div>
        @elseif(preg_match('/^#\s+(.+)/u', $trimmed, $matches))
            <div class="h1">{{ e($matches[1]) }}</div>
        @elseif(preg_match('/^>\s+(.+)/u', $trimmed, $matches))
            <div class="quote-box">{{ e($matches[1]) }}</div>
        @elseif(preg_match('/^(catatan|note)\s*:?\s*(.+)$/i', $trimmed, $matches))
            <div class="note-box">
                <strong>{{ ucfirst($matches[1]) }}:</strong> {{ e($matches[2]) }}
            </div>
        @else
            <div class="paragraph">{{ e($trimmed) }}</div>
        @endif
    @endforeach

    {!! $flushBulletList() !!}
    {!! $flushNumberList() !!}
</div>

<div class="footer">
    Generated by CampusHub AI · {{ now()->format('d M Y H:i') }}
</div>

</body>
</html>
