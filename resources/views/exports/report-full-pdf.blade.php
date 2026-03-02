<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Full Business Report</title>
    <style>
        @page {
            margin: 50px 50px 70px 50px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            color: #2d3748;
            line-height: 1.4;
        }

        /* ── Header ────────────────────────────── */
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: top;
            padding: 0;
        }
        .brand-cell {
            width: 55%;
            padding: 24px 32px;
            background-color: #7367f0;
        }
        .brand-name {
            font-size: 22px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .brand-tagline {
            font-size: 9px;
            color: #d4d0fc;
            letter-spacing: 0.3px;
        }
        .report-info-cell {
            width: 45%;
            padding: 24px 32px;
            background-color: #6358e0;
            text-align: right;
        }
        .report-type {
            font-size: 14px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .report-meta {
            font-size: 8px;
            color: #c9c4f7;
            line-height: 1.6;
        }
        .accent-line {
            width: 100%;
            height: 3px;
            background-color: #28c76f;
            margin-bottom: 24px;
        }

        /* ── Table of Contents ─────────────────── */
        .toc-table {
            width: 60%;
            border-collapse: collapse;
            margin: 0 auto 16px auto;
        }
        .toc-heading {
            font-size: 13px;
            font-weight: 700;
            color: #4a4458;
            text-align: center;
            padding: 14px 0 14px 0;
            border-bottom: 2px solid #7367f0;
        }
        .toc-item td {
            padding: 9px 16px;
            border-bottom: 1px solid #eef0f5;
            font-size: 10px;
            color: #5e5873;
        }
        .toc-num {
            width: 30px;
            font-weight: 700;
            color: #7367f0;
            text-align: center;
        }
        .toc-count {
            text-align: right;
            color: #a09cbe;
            font-size: 9px;
        }

        /* ── Section Header ────────────────────── */
        .section-header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .section-number-cell {
            width: 40px;
            padding: 10px 0;
            vertical-align: middle;
        }
        .section-number {
            display: inline-block;
            width: 28px;
            height: 28px;
            background-color: #7367f0;
            color: #ffffff;
            font-size: 13px;
            font-weight: 700;
            text-align: center;
            line-height: 28px;
        }
        .section-title-cell {
            padding: 10px 0 10px 10px;
            vertical-align: middle;
            border-bottom: 2px solid #7367f0;
        }
        .section-title-text {
            font-size: 13px;
            font-weight: 700;
            color: #4a4458;
            letter-spacing: 0.3px;
        }
        .section-row-count {
            font-size: 8px;
            font-weight: 400;
            color: #a09cbe;
            margin-left: 6px;
        }

        /* ── Truncation Warning ────────────────── */
        .truncation-bar {
            background-color: #fff8e1;
            border-left: 4px solid #ff9f43;
            padding: 6px 14px;
            margin-bottom: 8px;
            font-size: 8px;
            font-weight: 600;
            color: #7a5d1e;
        }

        /* ── Data Table ────────────────────────── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 16px;
        }
        .data-table thead th {
            background-color: #f1f0ff;
            color: #5e5873;
            font-size: 7px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            padding: 9px 12px;
            border-bottom: 2px solid #d8d5f7;
            text-align: left;
        }
        .data-table thead th.row-num {
            width: 28px;
            text-align: center;
            padding: 9px 6px;
            color: #a09cbe;
        }
        .data-table tbody td {
            padding: 8px 12px;
            border-bottom: 1px solid #eef0f5;
            font-size: 8px;
            color: #4a4458;
        }
        .data-table tbody td.row-num {
            text-align: center;
            padding: 8px 6px;
            color: #a09cbe;
            font-size: 7px;
            font-weight: 600;
        }
        .data-table tbody tr.even-row td {
            background-color: #fafaff;
        }
        .data-table tbody tr:last-child td {
            border-bottom: 2px solid #d8d5f7;
        }
        .empty-cell {
            text-align: center;
            color: #b0adcc;
            padding: 20px 10px;
            font-style: italic;
            font-size: 9px;
        }

        /* ── Footer ────────────────────────────── */
        .footer {
            position: fixed;
            bottom: -40px;
            left: 0;
            right: 0;
        }
        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }
        .footer-line {
            height: 2px;
            background-color: #7367f0;
        }
        .footer-left {
            padding: 10px 0;
            font-size: 7.5px;
            color: #a09cbe;
        }
        .footer-right {
            padding: 10px 0;
            font-size: 7.5px;
            color: #a09cbe;
            text-align: right;
        }
        .confidential {
            display: inline;
            background-color: #f1f0ff;
            padding: 2px 6px;
            font-size: 7px;
            font-weight: 700;
            color: #7367f0;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    {{-- ── Footer (fixed on every page) ─────────── --}}
    <div class="footer">
        <table class="footer-table">
            <tr><td colspan="2" class="footer-line"></td></tr>
            <tr>
                <td class="footer-left">
                    <span class="confidential">Confidential</span>
                    &nbsp;&nbsp;{{ $appName }} &mdash; Full Business Report
                </td>
                <td class="footer-right">
                    Generated on {{ $generatedAt }}
                </td>
            </tr>
        </table>
    </div>

    {{-- ── Cover / Header ───────────────────────── --}}
    <table class="header-table">
        <tr>
            <td class="brand-cell">
                <div class="brand-name">{{ $appName }}</div>
                <div class="brand-tagline">Business Intelligence Report</div>
            </td>
            <td class="report-info-cell">
                <div class="report-type">Full Report</div>
                <div class="report-meta">
                    Period: {{ $rangeLabel }}<br>
                    Generated: {{ $generatedAt }}<br>
                    Sections: {{ count($sections) }}
                </div>
            </td>
        </tr>
    </table>
    <div class="accent-line"></div>

    {{-- ── Table of Contents ────────────────────── --}}
    <table class="toc-table">
        <tr><td colspan="3" class="toc-heading">Report Contents</td></tr>
        @foreach($sections as $idx => $section)
        <tr class="toc-item">
            <td class="toc-num">{{ $idx + 1 }}</td>
            <td>{{ $section['title'] }}</td>
            <td class="toc-count">{{ number_format(count($section['rows'])) }} records</td>
        </tr>
        @endforeach
    </table>

    <div class="page-break"></div>

    {{-- ── Sections ─────────────────────────────── --}}
    @foreach($sections as $idx => $section)
        {{-- Section header --}}
        <table class="section-header-table">
            <tr>
                <td class="section-number-cell">
                    <span class="section-number">{{ $idx + 1 }}</span>
                </td>
                <td class="section-title-cell">
                    <span class="section-title-text">{{ $section['title'] }}</span>
                    <span class="section-row-count">{{ number_format(count($section['rows'])) }} record{{ count($section['rows']) !== 1 ? 's' : '' }}</span>
                </td>
            </tr>
        </table>

        {{-- Truncation warning --}}
        @if(!empty($section['truncated']) && !empty($section['truncatedMsg']))
        <div class="truncation-bar">
            &#9888; {{ $section['truncatedMsg'] }}
        </div>
        @endif

        {{-- Data table --}}
        <table class="data-table">
            <thead>
                <tr>
                    <th class="row-num">#</th>
                    @foreach($section['headings'] as $h)
                    <th>{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($section['rows'] as $rIdx => $row)
                <tr class="{{ $rIdx % 2 === 1 ? 'even-row' : '' }}">
                    <td class="row-num">{{ $rIdx + 1 }}</td>
                    @foreach($row as $cell)
                    <td>{{ $cell }}</td>
                    @endforeach
                </tr>
                @empty
                <tr>
                    <td colspan="{{ count($section['headings']) + 1 }}" class="empty-cell">
                        No data available for this section
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if(!$loop->last)
        <div class="page-break"></div>
        @endif
    @endforeach
</body>
</html>
