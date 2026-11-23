<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; background: #f9fafb; padding: 20px; margin: 0;">
    <div style="max-width: 600px; margin: 0 auto; background: #fff; border-radius: 12px; padding: 30px;">

        {{-- Provider header --}}
        <div style="text-align: center; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 2px solid #e5e7eb;">
            @if($provider->logo_url)
                <img src="{{ Storage::url($provider->logo_url) }}" alt="{{ $provider->name }}" style="max-height: 60px; margin-bottom: 8px;">
            @endif
            <h1 style="font-size: 20px; color: #111; margin: 0;">{{ $provider->name }}</h1>
            <p style="font-size: 12px; color: #6b7280; margin: 4px 0 0;">ArtCraft Quotation</p>
        </div>

        <h2 style="color: #7c3aed; margin-top: 0; font-size: 20px;">📩 Your Quote is Ready</h2>

        <p style="color: #4b5563; font-size: 14px;">Hello <strong>{{ $order->client_name }}</strong>,</p>
        <p style="color: #4b5563; font-size: 14px;">
            <strong>{{ $provider->name }}</strong> has sent you a quotation for your custom ArtCraft order.
        </p>

        {{-- Order info --}}
        <div style="background: #faf5ff; border-left: 4px solid #7c3aed; padding: 16px; border-radius: 8px; margin: 20px 0;">
            <p style="margin: 4px 0; font-size: 13px; color: #111;"><strong>Order:</strong> {{ $order->booking_number }}</p>
            <p style="margin: 4px 0; font-size: 13px; color: #111;"><strong>Quote Version:</strong> #{{ $quote->version }}</p>
            <p style="margin: 4px 0; font-size: 13px; color: #111;"><strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $order->type)) }}</p>
        </div>

        {{-- Quote details --}}
        <div style="background: #f9fafb; border-radius: 10px; padding: 20px; margin: 20px 0;">
            <h3 style="font-size: 14px; font-weight: 800; color: #111; margin: 0 0 14px;">Quotation Details</h3>

            <table style="width: 100%; font-size: 13px; color: #374151; border-collapse: collapse;">
                <tr>
                    <td style="padding: 6px 0; color: #6b7280;">Amount</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 800; font-size: 18px; color: #7c3aed;">
                        {{ $quote->currency }} {{ number_format($quote->quoted_amount, 2) }}
                    </td>
                </tr>
                @if($quote->materials)
                <tr>
                    <td style="padding: 6px 0; color: #6b7280;">Materials</td>
                    <td style="padding: 6px 0; text-align: right;">{{ $quote->materials }}</td>
                </tr>
                @endif
                @if($quote->dimensions)
                <tr>
                    <td style="padding: 6px 0; color: #6b7280;">Dimensions</td>
                    <td style="padding: 6px 0; text-align: right;">{{ $quote->dimensions }}</td>
                </tr>
                @endif
                <tr>
                    <td style="padding: 6px 0; color: #6b7280;">Quantity</td>
                    <td style="padding: 6px 0; text-align: right;">{{ $quote->quantity }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #6b7280;">Production Time</td>
                    <td style="padding: 6px 0; text-align: right;">{{ $quote->production_days }} days</td>
                </tr>
                @if($quote->delivery_terms)
                <tr>
                    <td style="padding: 6px 0; color: #6b7280;">Delivery</td>
                    <td style="padding: 6px 0; text-align: right;">{{ $quote->delivery_terms }}</td>
                </tr>
                @endif
                @if($quote->advance_proposal)
                <tr>
                    <td style="padding: 6px 0; color: #6b7280;">Advance (proposed)</td>
                    <td style="padding: 6px 0; text-align: right;">{{ $quote->currency }} {{ number_format($quote->advance_proposal, 2) }}</td>
                </tr>
                @endif
                <tr>
                    <td style="padding: 6px 0; color: #6b7280;">Valid Until</td>
                    <td style="padding: 6px 0; text-align: right;">{{ $quote->valid_until?->format('M d, Y') }}</td>
                </tr>
            </table>

            @if($quote->provider_message)
            <div style="margin-top: 16px; padding: 12px; background: #fff; border-radius: 8px; border: 1px solid #e5e7eb;">
                <p style="font-size: 11px; font-weight: 800; color: #6b7280; text-transform: uppercase; margin: 0 0 6px;">Message from Provider</p>
                <p style="font-size: 13px; color: #374151; margin: 0; line-height: 1.5;">{{ $quote->provider_message }}</p>
            </div>
            @endif
        </div>

        {{-- CTA --}}
        <div style="text-align: center; margin: 28px 0;">
            <a href="{{ $respondUrl }}" style="display: inline-block; background: #7c3aed; color: #fff; padding: 14px 32px; border-radius: 10px; text-decoration: none; font-weight: 800; font-size: 14px;">
                Review & Respond →
            </a>
            <p style="font-size: 11px; color: #9ca3af; margin: 10px 0 0;">
                You can accept, reject, or request a revision from this link. (Valid 30 days)
            </p>
        </div>

        <p style="color: #6b7280; font-size: 12px; text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
            Questions? Contact {{ $provider->contact_phone ?? $provider->contact_email ?? $provider->name }}
        </p>
    </div>
</body>
</html>