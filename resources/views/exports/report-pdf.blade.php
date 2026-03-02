<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
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
            margin-bottom: 0;
        }
        .header-table td {
            vertical-align: top;
            padding: 0;
        }
        .brand-cell {
            width: 60%;
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
            width: 40%;
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

        /* ── Accent Line ───────────────────────── */
        .accent-line {
            width: 100%;
            height: 3px;
            background-color: #28c76f;
            margin-bottom: 24px;
        }

        /* ── Summary Cards ─────────────────────── */
        .summary-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 12px 0;
            margin-bottom: 28px;
        }
        .summary-card {
            background-color: #f8f9fe;
            border: 1px solid #e8e6fc;
            padding: 16px 20px;
            text-align: center;
            vertical-align: top;
        }
        .summary-label {
            font-size: 7.5px;
            font-weight: 600;
            color: #8a8a9e;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 4px;
        }
        .summary-value {
            font-size: 16px;
            font-weight: 700;
            color: #7367f0;
        }

        /* ── Truncation Warning ────────────────── */
        .truncation-bar {
            background-color: #fff8e1;
            border-left: 4px solid #ff9f43;
            padding: 8px 16px;
            margin-bottom: 16px;
            font-size: 9px;
            font-weight: 600;
            color: #7a5d1e;
        }

        /* ── Section Label ─────────────────────── */
        .section-label-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        .section-label-cell {
            padding: 10px 0 8px 0;
            border-bottom: 2px solid #7367f0;
        }
        .section-label-text {
            font-size: 11px;
            font-weight: 700;
            color: #4a4458;
            letter-spacing: 0.3px;
        }
        .section-count {
            font-size: 9px;
            font-weight: 400;
            color: #8a8a9e;
        }

        /* ── Data Table ────────────────────────── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            margin-bottom: 16px;
        }
        .data-table thead th {
            background-color: #f1f0ff;
            color: #5e5873;
            font-size: 7.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            padding: 10px 14px;
            border-bottom: 2px solid #d8d5f7;
            text-align: left;
        }
        .data-table thead th.row-num {
            width: 32px;
            text-align: center;
            padding: 10px 8px;
            color: #a09cbe;
        }
        .data-table tbody td {
            padding: 9px 14px;
            border-bottom: 1px solid #eef0f5;
            font-size: 9px;
            color: #4a4458;
        }
        .data-table tbody td.row-num {
            text-align: center;
            padding: 9px 8px;
            color: #a09cbe;
            font-size: 8px;
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
            padding: 30px 10px;
            font-style: italic;
            font-size: 10px;
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
                    &nbsp;&nbsp;{{ $appName }}
                </td>
                <td class="footer-right">
                    Generated on {{ $generatedAt }}
                </td>
            </tr>
        </table>
    </div>

    {{-- ── Header ───────────────────────────────── --}}
    <table class="header-table">
        <tr>
            <td class="brand-cell">
                <div class="brand-name">{{ $appName }}</div>
                <div class="brand-tagline">Business Intelligence Report</div>
            </td>
            <td class="report-info-cell">
                <div class="report-type">{{ $title }}</div>
                <div class="report-meta">
                    Period: {{ $rangeLabel }}<br>
                    Generated: {{ $generatedAt }}
                </div>
            </td>
        </tr>
    </table>
    <div class="accent-line"></div>

    {{-- ── Summary KPIs ─────────────────────────── --}}
    @if(!empty($summary))
    <table class="summary-table">
        <tr>
            @foreach($summary as $label => $value)
            <td class="summary-card">
                <div class="summary-label">{{ $label }}</div>
                <div class="summary-value">{{ $value }}</div>
            </td>
            @endforeach
        </tr>
    </table>
    @endif

    {{-- ── Truncation Warning ───────────────────── --}}
    @if(!empty($truncated) && !empty($truncatedMsg))
    <div class="truncation-bar">
        &#9888; {{ $truncatedMsg }}
    </div>
    @endif

    {{-- ── Section Label ────────────────────────── --}}
    <table class="section-label-table">
        <tr>
            <td class="section-label-cell">
                <span class="section-label-text">{{ $title }} Details</span>
                <span class="section-count">&nbsp;&mdash;&nbsp;{{ number_format(count($rows)) }} record{{ count($rows) !== 1 ? 's' : '' }}</span>
            </td>
        </tr>
    </table>

    {{-- ── Data Table ───────────────────────────── --}}
    <table class="data-table">
        <thead>
            <tr>
                <th class="row-num">#</th>
                @foreach($headings as $h)
                <th>{{ $h }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $idx => $row)
            <tr class="{{ $idx % 2 === 1 ? 'even-row' : '' }}">
                <td class="row-num">{{ $idx + 1 }}</td>
                @foreach($row as $cell)
                <td>{{ $cell }}</td>
                @endforeach
            </tr>
            @empty
            <tr>
                <td colspan="{{ count($headings) + 1 }}" class="empty-cell">
                    No data available for the selected period
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
