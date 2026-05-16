<!DOCTYPE html>
<html lang="{{ app()->getLocale() ?? 'sw' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle ?? ($reportTitle ?? db_trans('reports.title')) }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            margin: 10mm 9mm 12mm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            background: #dee9f5;
            color: #1a2e4a;
            font-size: 11px;
        }

        .report {
            width: 100%;
            background: #ffffff;
            border-radius: 12px;
            padding: 16px 16px 20px;
            border: 1px solid #d7e6f7;
        }

        .header-layout {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .header-layout td {
            vertical-align: top;
        }

        .header-col-left,
        .header-col-right {
            width: 72px;
            text-align: center;
        }

        .header-col-center {
            text-align: center;
            padding: 0 8px;
        }

        .logo-img {
            width: 48px;
            height: 48px;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }

        .logo-placeholder {
            width: 48px;
            height: 48px;
            line-height: 48px;
            background: #ecf3fc;
            border: 1px solid #c7daf0;
            border-radius: 8px;
            text-align: center;
            color: #1f4e8c;
            font-size: 9px;
            font-weight: 700;
            margin: 0 auto;
        }

        .logo-text {
            margin-top: 4px;
            font-size: 8px;
            font-weight: 600;
            color: #2b5a8c;
        }

        .diocese-name {
            font-size: 16px;
            font-weight: 700;
            color: #0f62fe;
            margin-bottom: 2px;
            text-transform: uppercase;
            line-height: 1.15;
        }

        .parish-name {
            font-size: 14px;
            font-weight: 700;
            color: #0f62fe;
            margin-bottom: 3px;
            text-transform: uppercase;
            line-height: 1.15;
        }

        .address-line,
        .contact-line {
            font-size: 10px;
            color: #111111;
            margin-bottom: 2px;
            line-height: 1.25;
        }

        .report-title-main {
            font-size: 17px;
            font-weight: 700;
            color: #0f62fe;
            margin-top: 4px;
            margin-bottom: 2px;
            text-transform: uppercase;
            line-height: 1.2;
        }

        .meta-strip {
            width: 100%;
            margin: 10px 0 8px;
            border: 1px solid #d8e8f8;
            background: #f6fafe;
            border-radius: 9px;
            padding: 7px 9px;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-table td {
            width: 25%;
            text-align: center;
            vertical-align: top;
            padding: 4px 3px;
        }

        .meta-label {
            font-size: 8px;
            text-transform: uppercase;
            font-weight: 700;
            color: #3b6e9e;
            margin-bottom: 3px;
        }

        .meta-value {
            font-size: 11px;
            font-weight: 700;
            color: #102b44;
        }

        .divider-light {
            height: 1.2px;
            background: #6f6f6f;
            margin: 9px 0 11px;
        }

        .section-header {
            margin: 14px 0 8px;
        }

        .section-header h3 {
            font-weight: 700;
            color: #1a3d61;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .accent-line {
            height: 3px;
            width: 64px;
            background: #5881b7;
            border-radius: 8px;
        }

        .table-container {
            border-radius: 8px;
            border: 1px solid #dde9f5;
            overflow: hidden;
            background: #ffffff;
            margin-bottom: 14px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
        }

        .data-table th {
            background: #f0f7ff;
            color: #163a5e;
            font-weight: 700;
            padding: 6px 7px;
            text-align: left;
            border: 1px solid #bed3ec;
            vertical-align: top;
            line-height: 1.15;
        }

        .data-table td {
            padding: 6px 7px;
            border: 1px solid #d9e7f4;
            color: #1f334b;
            vertical-align: top;
            line-height: 1.2;
        }

        .data-table tfoot tr {
            background: #e3effb;
            font-weight: 800;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .tabular-nums {
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }

        .content-area {
            margin-top: 8px;
        }

        .footer-note {
            margin-top: 18px;
            padding-top: 10px;
            border-top: 1px solid #cfdff0;
            color: #4a719b;
            font-size: 9px;
            width: 100%;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-table td {
            width: 33.33%;
            vertical-align: middle;
        }

        .footer-left {
            text-align: left;
        }

        .footer-center {
            text-align: center;
        }

        .footer-right {
            text-align: right;
        }

        .muted {
            color: #6b85a3;
        }

        .report-meta-box .data-table th,
        .report-meta-box .data-table td {
            padding: 7px 9px;
        }

        .matrix-table-container {
            overflow: visible;
        }

        .matrix-table .matrix-top-cell {
            vertical-align: top;
        }

        .matrix-table .matrix-no-padding {
            padding: 0;
        }

        .matrix-kanda-name {
            font-weight: 700;
            font-size: 10.5px;
            color: #173a5c;
            margin-bottom: 3px;
            text-transform: uppercase;
        }

        .matrix-kanda-meta {
            font-size: 8.8px;
            color: #5a7a99;
        }

        .nested-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .nested-table th,
        .nested-table td {
            border: 1px solid #d7e4f0;
            padding: 5px 6px;
            font-size: 8.8px;
            vertical-align: top;
            line-height: 1.15;
            word-break: normal;
            overflow-wrap: break-word;
        }

        .nested-table th {
            background: #f7fbff;
            color: #163a5e;
            font-weight: 700;
        }

        .nested-jumuiya-name {
            font-weight: 600;
        }

        .nested-grand-total {
            font-weight: 700;
            background: #f8fbff;
        }

        .nested-kanda-total {
            font-weight: 800;
            background: #eaf3ff;
        }

        .nowrap {
            white-space: nowrap;
        }

        .chart-section {
            margin-top: 18px;
            page-break-inside: avoid;
        }

        .chart-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px 0;
            page-break-inside: avoid;
        }

        .chart-grid td {
            vertical-align: top;
            width: 50%;
        }

        .chart-card {
            border: 1px solid #d9e7f4;
            border-radius: 10px;
            padding: 10px;
            background: #fbfdff;
            min-height: 220px;
            page-break-inside: avoid;
        }

        .chart-card-title {
            font-size: 12px;
            font-weight: 700;
            color: #173a5c;
            margin-bottom: 8px;
        }

        .bar-chart {
            margin-top: 4px;
        }

        .bar-row {
            margin-bottom: 9px;
            page-break-inside: avoid;
        }

        .bar-label-line {
            font-size: 9px;
            color: #173a5c;
            margin-bottom: 3px;
            overflow: hidden;
        }

        .bar-label-name {
            display: inline-block;
            max-width: 70%;
            vertical-align: middle;
        }

        .bar-label-value {
            float: right;
            font-weight: 700;
            color: #173a5c;
        }

        .bar-track {
            width: 100%;
            height: 10px;
            background: #e8f0fb;
            border-radius: 999px;
            overflow: hidden;
        }

        .bar-fill {
            height: 10px;
            border-radius: 999px;
        }

        .bar-fill-kanda {
            background: linear-gradient(90deg, #2563eb 0%, #60a5fa 100%);
        }

        .bar-fill-jumuiya {
            background: linear-gradient(90deg, #16a34a 0%, #4ade80 100%);
        }

        .pie-list {
            margin-top: 6px;
        }

        .pie-item {
            margin-bottom: 10px;
            page-break-inside: avoid;
        }

        .pie-item-top {
            font-size: 9px;
            margin-bottom: 4px;
            color: #173a5c;
        }

        .pie-swatch {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 6px;
            vertical-align: middle;
        }

        .pie-name {
            display: inline-block;
            vertical-align: middle;
        }

        .pie-value {
            float: right;
            font-weight: 700;
            color: #173a5c;
        }

        .pie-track {
            width: 100%;
            height: 9px;
            background: #e8f0fb;
            border-radius: 999px;
            overflow: hidden;
        }

        .pie-fill {
            height: 9px;
            border-radius: 999px;
        }

        .empty-chart-note {
            font-size: 9px;
            color: #6b85a3;
            margin-top: 12px;
        }

        .swatch-1 { background: #2563eb; }
        .swatch-2 { background: #16a34a; }
        .swatch-3 { background: #f59e0b; }
        .swatch-4 { background: #dc2626; }
        .swatch-5 { background: #7c3aed; }
        .swatch-6 { background: #0891b2; }
        .swatch-7 { background: #ea580c; }
        .swatch-8 { background: #65a30d; }
    </style>
</head>
<body>
    @php
        $branding = $branding ?? [];

        $siteName = $branding['site_name'] ?? config('app.name');
        $dioceseName = $branding['diocese_name'] ?? '';
        $email = $branding['email'] ?? '';
        $phone = $branding['phone'] ?? '';
        $address = $branding['address'] ?? '';

        $leftLogo = $branding['left_logo'] ?? null;
        $rightLogo = $branding['right_logo'] ?? null;

        $footerText = $branding['footer_text'] ?? $siteName;
        $footerQuote = $branding['footer_quote'] ?? '';
        $issuedAtText = $issuedAtText ?? now()->translatedFormat('d F Y');

        $locale = app()->getLocale() ?? 'sw';
        $isSwahili = str_starts_with(strtolower($locale), 'sw');

        $dioceseHeading = trim(
            $isSwahili
                ? ('JIMBO KATOLIKI ' . $dioceseName)
                : ('CATHOLIC DIOCESE OF ' . $dioceseName)
        );

        $parishHeading = trim(
            $isSwahili
                ? ('PAROKIA YA ' . $siteName)
                : ('PARISH OF ' . $siteName)
        );

        $addressLabel = $isSwahili ? 'Anwani:' : 'Address:';
        $phoneLabel = $isSwahili ? 'Simu ya mezani:' : 'Phone:';
        $emailLabel = $isSwahili ? 'Barua pepe:' : 'Email:';
    @endphp

    <div class="report">
        <table class="header-layout">
            <tr>
                <td class="header-col-left">
                    @if(!empty($leftLogo) && file_exists($leftLogo))
                        <img src="file:///{{ str_replace('\\', '/', $leftLogo) }}" class="logo-img" alt="Left Logo">
                    @else
                        <div class="logo-placeholder">LOGO</div>
                    @endif

                    @if(!empty($branding['left_logo_label']))
                        <div class="logo-text">{{ $branding['left_logo_label'] }}</div>
                    @endif
                </td>

                <td class="header-col-center">
                    @if(!empty($dioceseName))
                        <div class="diocese-name">{{ $dioceseHeading }}</div>
                    @endif

                    <div class="parish-name">{{ $parishHeading }}</div>

                    @if(!empty($address))
                        <div class="address-line">
                            {{ $addressLabel }} {{ $address }}
                        </div>
                    @endif

                    @if(!empty($phone) || !empty($email))
                        <div class="contact-line">
                            @if(!empty($phone))
                                {{ $phoneLabel }} {{ $phone }}
                            @endif

                            @if(!empty($phone) && !empty($email))
                                &nbsp;||&nbsp;
                            @endif

                            @if(!empty($email))
                                {{ $emailLabel }} {{ $email }}
                            @endif
                        </div>
                    @endif

                    <div class="report-title-main">{{ $reportTitle ?? db_trans('reports.title') }}</div>
                </td>

                <td class="header-col-right">
                    @if(!empty($rightLogo) && file_exists($rightLogo))
                        <img src="file:///{{ str_replace('\\', '/', $rightLogo) }}" class="logo-img" alt="Right Logo">
                    @else
                        <div class="logo-placeholder">LOGO</div>
                    @endif

                    @if(!empty($branding['right_logo_label']))
                        <div class="logo-text">{{ $branding['right_logo_label'] }}</div>
                    @endif
                </td>
            </tr>
        </table>

        @if(!empty($metaItems) && is_array($metaItems))
            <div class="meta-strip">
                <table class="meta-table">
                    <tr>
                        @foreach($metaItems as $item)
                            <td>
                                <div class="meta-label">{{ $item['label'] ?? '' }}</div>
                                <div class="meta-value">{{ $item['value'] ?? '' }}</div>
                            </td>
                        @endforeach
                    </tr>
                </table>
            </div>
        @endif

        <div class="divider-light"></div>

        <div class="content-area">
            @yield('content')
        </div>

        <div class="footer-note">
            <table class="footer-table">
                <tr>
                    <td class="footer-left">{{ $footerText }}</td>
                    <td class="footer-center">{{ $footerQuote }}</td>
                    <td class="footer-right">{{ $issuedAtLabel ?? db_trans('reports.generated_on') }}: {{ $issuedAtText }}</td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>