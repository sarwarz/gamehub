<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Delivered</title>

    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f6f8;
            font-family: Arial, Helvetica, sans-serif;
            color: #333333;
        }

        .wrapper {
            width: 100%;
            padding: 30px 0;
            background-color: #f4f6f8;
        }

        .container {
            width: 600px;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .header {
            background: #059669;
            padding: 20px 30px;
            color: #ffffff;
        }

        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: bold;
        }

        .content {
            padding: 30px;
            font-size: 14px;
            line-height: 1.6;
        }

        .content p {
            margin: 0 0 15px;
        }

        .divider {
            border-top: 1px solid #e5e7eb;
            margin: 25px 0;
        }

        .product-title {
            font-size: 16px;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .license-box {
            width: 100%;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .license-box-header {
            background: #f0fdf4;
            color: #065f46;
            font-size: 13px;
            padding: 10px;
            font-weight: bold;
        }

        .license-key {
            font-family: "Courier New", monospace;
            font-size: 14px;
            padding: 8px 10px;
            border-top: 1px solid #e5e7eb;
        }

        .cta {
            margin: 30px 0;
            text-align: center;
        }

        .cta a {
            background: #2563eb;
            color: #ffffff;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            display: inline-block;
        }

        .footer {
            background: #f9fafb;
            padding: 15px 30px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }
    </style>
</head>

<body>

<table class="wrapper" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center">

            <table class="container" cellpadding="0" cellspacing="0">

                <!-- Header -->
                <tr>
                    <td class="header">
                        <h1>✅ Order Delivered</h1>
                    </td>
                </tr>

                <!-- Content -->
                <tr>
                    <td class="content">

                        <p>
                            Hello <strong>{{ $order->addresses->first()?->name ?? 'Customer' }}</strong>,
                        </p>

                        <p>
                            Great news! Your order <strong>{{ $order->order_number }}</strong> has been successfully delivered.
                        </p>

                        <div class="divider"></div>

                        <!-- Delivered Items -->
                        @foreach($order->items as $item)
                            <div class="product-title">
                                {{ $item->product->title }}
                            </div>

                            @foreach($item->deliveries as $delivery)
                                @if(($delivery->payload['type'] ?? null) === 'license')

                                    <table class="license-box" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td class="license-box-header">
                                                License Key{{ count($delivery->payload['keys'] ?? []) > 1 ? 's' : '' }}
                                            </td>
                                        </tr>

                                        @foreach($delivery->payload['keys'] as $key)
                                        <tr>
                                            <td class="license-key">
                                                {{ $key }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </table>

                                @endif
                            @endforeach
                        @endforeach

                        <p>
                            If you need help activating your product, please visit our support center or reply to this email — we’re happy to assist.
                        </p>

                        <!-- CTA -->
                        <div class="cta">
                            <a href="{{ config('app.frontend_url') }}/orders/{{ $order->id }}">
                                View Order Details
                            </a>
                        </div>

                        <p>
                            Thank you for choosing <strong>{{ config('app.name') }}</strong> 🚀
                        </p>

                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td class="footer">
                        © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>
