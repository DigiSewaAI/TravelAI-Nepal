<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f3f4f6; padding: 20px; }
        .card { background: #ffffff; padding: 30px; border-radius: 10px; max-width: 600px; margin: auto; }
        .btn { display: inline-block; padding: 10px 20px; background-color: #2563eb; color: #ffffff; text-decoration: none; border-radius: 5px; margin-top: 20px; }
        .details { background: #f9fafb; padding: 15px; border-radius: 5px; margin-top: 20px; border: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="card">
        <h2>🎨 New ArtCraft Enquiry!</h2>
        <p>Hello <strong>{{ $provider->name ?? 'Artist' }}</strong>,</p>
        <p>You have received a new enquiry/order request.</p>

        <div class="details">
            <p><strong>Type:</strong> {{ $order->request_type === 'custom' ? 'Custom Commission' : 'Ready-Made Artwork' }}</p>
            <p><strong>Client Name:</strong> {{ $order->client_name }}</p>
            <p><strong>Client Email:</strong> {{ $order->client_email }}</p>
            @if($order->artwork_name_snapshot)
                <p><strong>Artwork:</strong> {{ $order->artwork_name_snapshot }} ({{ $order->artwork_currency_snapshot }} {{ $order->artwork_price_snapshot }})</p>
            @endif
            @if($order->type)
                <p><strong>Category:</strong> {{ $order->type }}</p>
            @endif
            @if($order->quantity)
                <p><strong>Quantity:</strong> {{ $order->quantity }}</p>
            @endif
            @if($order->budget)
                <p><strong>Budget:</strong> NPR {{ number_format($order->budget, 2) }}</p>
            @endif
            @if($order->deadline)
                <p><strong>Deadline:</strong> {{ \Carbon\Carbon::parse($order->deadline)->format('M d, Y') }}</p>
            @endif
            @if($order->description)
                <p><strong>Description:</strong> {{ $order->description }}</p>
            @endif
            @if($order->materials)
                <p><strong>Materials:</strong> {{ $order->materials }}</p>
            @endif
            @if($order->dimensions)
                <p><strong>Dimensions:</strong> {{ $order->dimensions }}</p>
            @endif
            @if($order->delivery_address)
                <p><strong>Delivery Address:</strong> {{ $order->delivery_address }}</p>
            @endif
        </div>

        <p>Please log in to your provider dashboard to review and respond.</p>

        <a href="{{ url('/provider/artcraft/orders') }}" class="btn">View in Dashboard</a>

        <p style="margin-top: 30px; font-size: 12px; color: #6b7280;">TravelAI Nepal — AI + data-driven trekking ecosystem.</p>
    </div>
</body>
</html>