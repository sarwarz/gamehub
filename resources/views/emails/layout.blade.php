<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', config('app.name'))</title>
    <!--[if mso]><noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript><![endif]-->
    <style type="text/css">
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        body { margin: 0; padding: 0; width: 100% !important; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f0f2f5; color: #2d3748; }
        .email-wrapper { width: 100%; background-color: #f0f2f5; padding: 40px 0; }
        .email-container { max-width: 620px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .email-header { padding: 32px 40px 24px; text-align: center; }
        .email-header .logo { font-size: 24px; font-weight: 800; color: #7367f0; letter-spacing: -0.5px; text-decoration: none; }
        .status-banner { padding: 20px 40px; text-align: center; }
        .status-icon { width: 56px; height: 56px; border-radius: 50%; display: inline-block; line-height: 56px; font-size: 24px; margin-bottom: 12px; }
        .status-title { font-size: 22px; font-weight: 700; margin: 0 0 4px; }
        .status-sub { font-size: 14px; color: #718096; margin: 0; }
        .email-body { padding: 0 40px 32px; font-size: 14px; line-height: 1.7; }
        .greeting { font-size: 15px; margin-bottom: 16px; }
        .meta-card, .order-meta { background: #f7f7fb; border-radius: 10px; padding: 16px 20px; margin: 20px 0; }
        .meta-card table, .order-meta table { width: 100%; }
        .meta-card td, .order-meta td { padding: 4px 0; font-size: 13px; }
        .meta-card .meta-label, .order-meta .meta-label { color: #a0aec0; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; }
        .meta-card .meta-value, .order-meta .meta-value { font-weight: 600; color: #2d3748; text-align: right; }
        .section-title { font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #7367f0; margin: 24px 0 12px; padding-bottom: 8px; border-bottom: 2px solid #f0f0f5; }
        .items-table { width: 100%; border-collapse: collapse; }
        .items-table th { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #a0aec0; padding: 8px 0; border-bottom: 2px solid #f0f0f5; text-align: left; }
        .items-table th:last-child { text-align: right; }
        .items-table td { padding: 12px 0; border-bottom: 1px solid #f5f5f8; font-size: 14px; vertical-align: top; }
        .items-table td:last-child { text-align: right; font-weight: 600; }
        .item-name { font-weight: 600; color: #2d3748; }
        .item-meta { font-size: 12px; color: #a0aec0; margin-top: 2px; }
        .totals-table { width: 100%; margin-top: 16px; }
        .totals-table td { padding: 6px 0; font-size: 13px; }
        .totals-table .total-label { color: #718096; }
        .totals-table .total-value { text-align: right; font-weight: 600; }
        .totals-table .grand-total td { padding-top: 12px; border-top: 2px solid #7367f0; font-size: 16px; font-weight: 700; }
        .totals-table .grand-total .total-value { color: #7367f0; }
        .delivery-card { background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%); border: 1px solid #bbf7d0; border-radius: 10px; padding: 16px 20px; margin: 8px 0 16px; }
        .delivery-card .dc-product { font-weight: 700; font-size: 14px; color: #166534; margin-bottom: 8px; }
        .delivery-card .dc-key { font-family: 'Courier New', Courier, monospace; font-size: 15px; font-weight: 700; color: #15803d; background: #ffffff; border: 1px dashed #86efac; border-radius: 6px; padding: 8px 12px; margin: 4px 0; letter-spacing: 1px; }
        .delivery-card .dc-status { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #16a34a; }
        .cta-wrapper { text-align: center; margin: 28px 0 8px; }
        .cta-btn { display: inline-block; background: linear-gradient(135deg, #7367f0 0%, #9b8cf8 100%); color: #ffffff !important; padding: 14px 36px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 700; letter-spacing: 0.3px; box-shadow: 0 4px 14px rgba(115,103,240,0.35); }
        .info-box { background: #f7f7fb; border-radius: 8px; padding: 14px 18px; margin: 16px 0; font-size: 13px; color: #718096; line-height: 1.6; }
        .message-bubble { background: #f7f7fb; border-left: 4px solid #7367f0; border-radius: 0 8px 8px 0; padding: 14px 18px; margin: 16px 0; font-size: 14px; color: #4a5568; line-height: 1.7; }
        .email-footer { background: #f7f7fb; padding: 24px 40px; text-align: center; border-top: 1px solid #edf2f7; }
        .footer-links { margin-bottom: 12px; }
        .footer-links a { color: #7367f0; text-decoration: none; font-size: 12px; font-weight: 600; margin: 0 10px; }
        .footer-copy { font-size: 11px; color: #a0aec0; margin: 0; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef9c3; color: #854d0e; }
        .badge-info { background: #dbeafe; color: #1e40af; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-purple { background: #ede9fe; color: #5b21b6; }
        .badge-secondary { background: #f1f5f9; color: #475569; }
        .divider { border: none; border-top: 1px solid #f0f0f5; margin: 20px 0; }
        .priority-low { color: #64748b; } .priority-medium { color: #2563eb; } .priority-high { color: #ea580c; } .priority-urgent { color: #dc2626; }
        @media only screen and (max-width: 640px) {
            .email-container { width: 100% !important; border-radius: 0 !important; }
            .email-header, .email-body, .email-footer, .status-banner { padding-left: 24px !important; padding-right: 24px !important; }
        }
        @yield('extra-css')
    </style>
</head>
<body>
<table class="email-wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td align="center">
            <table class="email-container" width="620" cellpadding="0" cellspacing="0" role="presentation">

                {{-- Header --}}
                <tr>
                    <td class="email-header">
                        <a href="{{ url('/') }}" class="logo">{{ config('app.name', 'GameHub') }}</a>
                    </td>
                </tr>

                {{-- Status Banner --}}
                @hasSection('banner')
                <tr>
                    <td class="status-banner">
                        @yield('banner')
                    </td>
                </tr>
                @endif

                {{-- Body --}}
                <tr>
                    <td class="email-body">
                        @yield('content')
                    </td>
                </tr>

                {{-- Footer --}}
                <tr>
                    <td class="email-footer">
                        <div class="footer-links">
                            @yield('footer-links')
                        </div>
                        <p class="footer-copy">&copy; {{ date('Y') }} {{ config('app.name', 'GameHub') }}. All rights reserved.</p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>
</body>
</html>
