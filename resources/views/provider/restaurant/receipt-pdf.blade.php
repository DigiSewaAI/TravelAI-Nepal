<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Receipt {{ $order->order_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; padding: 30px; }
        .header { text-align: center; border-bottom: 2px solid #2563eb; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { color: #2563eb; font-size: 22px; margin-bottom: 5px; }
        .header p { color: #6b7280; font-size: 11px; }
        .order-info { display: table; width: 100%; margin-bottom: 20px; }
        .order-info > div { display: table-cell; width: 50%; }
        .order-info .label { font-size: 10px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; }
        .order-info .value { font-size: 13px; font-weight: 700; color: #111827; }
        .receipt-code-box { 
            background: #fef3c7; 
            border: 3px dashed #f59e0b; 
            border-radius: 12px; 
            padding: 20px; 
            text-align: center; 
            margin: 25px 0;
        }
        .receipt-code-box .label { 
            font-size: 11px; 
            color: #92400e; 
            text-transform: uppercase; 
            letter-spacing: 1px; 
            margin-bottom: 8px;
        }
        .receipt-code-box .code { 
            font-size: 32px; 
            font-weight: 900; 
            color: #7c2d12; 
            letter-spacing: 6px;
            font-family: 'Courier New', monospace;
        }
        .receipt-code-box .hint { 
            font-size: 10px; 
            color: #92400e; 
            margin-top: 10px;
        }
        table.items { width: 100%; border-collapse: collapse; margin: 20px 0; }
        table.items th { 
            background: #f3f4f6; 
            padding: 8px; 
            text-align: left; 
            font-size: 11px; 
            text-transform: uppercase;
            color: #374151;
        }
        table.items td { 
            padding: 8px; 
            border-bottom: 1px solid #e5e7eb; 
            font-size: 12px;
        }
        table.items .total-row td { 
            background: #f9fafb; 
            font-weight: 700; 
            border-bottom: none;
        }
        .footer { 
            margin-top: 30px; 
            padding-top: 15px; 
            border-top: 1px solid #e5e7eb; 
            text-align: center; 
            font-size: 10px; 
            color: #6b7280;
        }
        .customer-info { 
            background: #f0f9ff; 
            border-left: 4px solid #0284c7; 
            padding: 10px 15px; 
            margin-bottom: 15px;
        }
        .customer-info .label { font-size: 10px; color: #0369a1; text-transform: uppercase; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $provider->business_name ?? 'TravelAI Nepal' }}</h1>
        <p>{{ $provider->address ?? 'Kathmandu, Nepal' }}</p>
    </div>

    <div class="order-info">
        <div>
            <div class="label">Order Number</div>
            <div class="value">{{ $order->order_number }}</div>
        </div>
        <div>
            <div class="label">Date</div>
            <div class="value">{{ $order->created_at->format('M d, Y H:i') }}</div>
        </div>
    </div>

    @if($order->customer_name || $order->customer_phone)
    <div class="customer-info">
        <div class="label">Customer</div>
        <div>{{ $order->customer_name ?? 'Walk-in' }}{{ $order->customer_phone ? ' · ' . $order->customer_phone : '' }}</div>
    </div>
    @endif

    <div class="receipt-code-box">
        <div class="label">Your Claim Code</div>
        <div class="code">{{ $plainCode }}</div>
        <div class="hint">
            Claim this order at travelainepal.com/traveler/restaurant/claim<br>
            Keep this receipt safe. This code will not be shown again.
        </div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>Item</th>
                <th style="text-align:center">Qty</th>
                <th style="text-align:right">Price</th>
                <th style="text-align:right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>{{ $item->item_name }}</td>
                <td style="text-align:center">{{ $item->quantity }}</td>
                <td style="text-align:right">NPR {{ number_format($item->unit_price, 2) }}</td>
                <td style="text-align:right">NPR {{ number_format($item->line_total, 2) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3" style="text-align:right">Total</td>
                <td style="text-align:right">NPR {{ number_format($order->total, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Thank you for dining with us!</p>
        <p style="margin-top:5px;">Issued by TravelAI Nepal · travelainepal.com</p>
    </div>
</body>
</html>