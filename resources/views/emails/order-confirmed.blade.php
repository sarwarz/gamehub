<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Confirmed</title>

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
            background: #1e293b;
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

        .order-info {
            margin: 20px 0;
            font-size: 14px;
        }

        .divider {
            border-top: 1px solid #e5e7eb;
            margin: 25px 0;
        }

        .summary-title {
            font-size: 16px;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .table th {
            background: #f9fafb;
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .table td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .total {
            margin-top: 20px;
            font-size: 16px;
            font-weight: bold;
            text-align: right;
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

        .muted {
            font-size: 13px;
            color: #6b7280;
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
                        <h1>🎉 Order Confirmed</h1>
                    </td>
                </tr>

                <!-- Content -->
                <tr>
                    <td class="content">

                        <p>
                            Hello <strong>{{ $order->addresses->first()?->name ?? 'Customer' }}</strong>,
                        </p>

                        <p>
                            Thank you for your purchase! Your order has been successfully confirmed.
                        </p>

                        <!-- Order Info -->
                        <div class="order-info">
                            <strong>Order Number:</strong> {{ $order->order_number }}<br>
                            <strong>Order Date:</strong> {{ $order->created_at->format('d M Y') }}
                        </div>

                        <div class="divider"></div>

                        <!-- Order Summary -->
                        <div class="summary-title">Order Summary</div>

                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                <tr>
                                    <td>{{ $item->product->title }}</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-right">
                                        {{ $order->currency }} {{ number_format($item->subtotal, 2) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="total">
                            Total: {{ $order->currency }} {{ number_format($order->total_amount, 2) }}
                        </div>

                        <p style="margin-top:25px;">
                            We are now processing your order. Delivery will begin shortly, and you’ll receive another email once your order is delivered.
                        </p>

                        <!-- CTA -->
                        <div class="cta">
                            <a href="{{ config('app.frontend_url') }}/orders/{{ $order->id }}">
                                View Order
                            </a>
                        </div>

                        <p class="muted">
                            If you have any questions, just reply to this email — we’re always happy to help.
                        </p>

                        <p>
                            Thanks again,<br>
                            <strong>{{ config('app.name') }}</strong>
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
