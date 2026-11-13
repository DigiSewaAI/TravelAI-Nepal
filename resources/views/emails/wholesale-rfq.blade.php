<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px;">
<div style="max-width: 600px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">

    @php
        $__headings = [
            'submitted'    => '📨 New RFQ Received',
            'confirmation' => '✅ RFQ Submitted',
            'quoted'       => '💰 Quote Received',
            'accepted'     => '✅ Quote Accepted',
            'rejected'     => '❌ Quote Rejected',
            'message'      => '💬 New Message',
        ];
        $__heading = $__headings[$type] ?? '📋 RFQ Update';

        $__ctaUrl = $role === 'provider'
            ? route('provider.wholesale-rfq.show', $rfq)
            : route('wholesale.rfq.show', $rfq);

        $__ctaLabel = match($type) {
            'confirmation' => 'View RFQ',
            'quoted'       => 'View Quote',
            'accepted'     => 'View RFQ',
            'rejected'     => 'View RFQ',
            'message'      => 'View Message',
            default        => 'View RFQ',
        };

        $__otherParty = $role === 'provider' ? $rfq->buyer : $rfq->provider;
        $__otherLabel = $role === 'provider' ? 'Buyer' : 'Provider';
    @endphp

    {{-- Header --}}
    <div style="background: linear-gradient(135deg, #6366f1, #4f46e5); padding: 30px 20px; text-align: center;">
        <h1 style="color: #fff; margin: 0; font-size: 24px;">{{ $__heading }}</h1>
        <p style="color: #e0e7ff; margin: 8px 0 0 0; font-size: 14px;">TravelAI Nepal</p>
    </div>

    <div style="padding: 30px 25px;">

        {{-- Greeting --}}
        <p style="font-size: 15px; color: #333; margin: 0 0 20px 0;">
            @if($role === 'provider')
                Hello {{ $rfq->provider->name ?? 'Provider' }},
            @else
                Hi {{ $rfq->buyer->name ?? 'Buyer' }},
            @endif
        </p>

        {{-- Body per type --}}
        @if($type === 'submitted')
            <p style="font-size: 14px; color: #555; line-height: 1.6; margin: 0 0 20px 0;">
                <strong>{{ $rfq->buyer->name ?? 'A buyer' }}</strong> has submitted a new RFQ for your product. Please review and respond with a quote.
            </p>
        @elseif($type === 'confirmation')
            <p style="font-size: 14px; color: #555; line-height: 1.6; margin: 0 0 20px 0;">
                Your RFQ has been submitted successfully. <strong>{{ $rfq->provider->name ?? 'The provider' }}</strong> will review it and respond with a quote shortly.
            </p>
        @elseif($type === 'quoted')
            <p style="font-size: 14px; color: #555; line-height: 1.6; margin: 0 0 20px 0;">
                <strong>{{ $rfq->provider->name ?? 'Provider' }}</strong> has provided a quote for your RFQ. Please review and accept or reject.
            </p>
            @if($rfq->provider_response)
                <div style="background: #f9fafb; border: 1px solid #e5e7eb; padding: 14px; border-radius: 8px; margin: 14px 0; font-size: 13px; color: #374151; white-space: pre-line; line-height: 1.6;">
                    {{ $rfq->provider_response }}
                </div>
            @endif
        @elseif($type === 'accepted')
            <p style="font-size: 14px; color: #555; line-height: 1.6; margin: 0 0 20px 0;">
                <strong>{{ $rfq->buyer->name ?? 'Buyer' }}</strong> has accepted your quote. Please proceed with the order.
            </p>
        @elseif($type === 'rejected')
            <p style="font-size: 14px; color: #555; line-height: 1.6; margin: 0 0 20px 0;">
                <strong>{{ $rfq->buyer->name ?? 'Buyer' }}</strong> has rejected your quote for this RFQ.
            </p>
        @elseif($type === 'message')
            <p style="font-size: 14px; color: #555; line-height: 1.6; margin: 0 0 20px 0;">
                A new message has been posted on this RFQ.
            </p>
            @if(isset($messageText) && $messageText)
                <div style="background: #f9fafb; border: 1px solid #e5e7eb; padding: 14px; border-radius: 8px; margin: 14px 0; font-size: 13px; color: #374151; white-space: pre-line; line-height: 1.6;">
                    {{ $messageText }}
                </div>
            @endif
        @endif

        {{-- RFQ ID Box --}}
        <div style="background: #e0e7ff; border: 2px solid #6366f1; border-radius: 12px; padding: 20px; margin: 20px 0;">
            <div style="font-size: 12px; color: #3730a3; text-transform: uppercase; font-weight: bold; margin-bottom: 8px;">
                RFQ Number
            </div>
            <div style="font-size: 24px; font-weight: bold; color: #3730a3; letter-spacing: 1px;">
                {{ $rfq->rfq_number }}
            </div>
        </div>

        {{-- Details Table --}}
        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <tr>
                <td style="padding: 8px 0; color: #666; font-size: 13px;">Product</td>
                <td style="padding: 8px 0; color: #111; font-weight: bold; text-align: right; font-size: 13px;">{{ $rfq->product->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #666; font-size: 13px;">Quantity</td>
                <td style="padding: 8px 0; color: #111; font-weight: bold; text-align: right; font-size: 13px;">{{ $rfq->quantity }} units</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #666; font-size: 13px;">{{ $__otherLabel }}</td>
                <td style="padding: 8px 0; color: #111; font-weight: bold; text-align: right; font-size: 13px;">{{ $__otherParty->name ?? 'N/A' }}</td>
            </tr>
            @if($rfq->quoted_price)
            <tr>
                <td style="padding: 8px 0; color: #666; font-size: 13px;">Unit Price</td>
                <td style="padding: 8px 0; color: #111; font-weight: bold; text-align: right; font-size: 13px;">NPR {{ number_format($rfq->quoted_price, 2) }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #666; font-size: 13px;">Total</td>
                <td style="padding: 8px 0; color: #059669; font-weight: bold; text-align: right; font-size: 15px;">NPR {{ number_format($rfq->quoted_total, 2) }}</td>
            </tr>
            @endif
            @if($rfq->valid_until)
            <tr>
                <td style="padding: 8px 0; color: #666; font-size: 13px;">Valid Until</td>
                <td style="padding: 8px 0; color: #111; font-weight: bold; text-align: right; font-size: 13px;">{{ $rfq->valid_until->format('M d, Y') }}</td>
            </tr>
            @endif
            <tr>
                <td style="padding: 8px 0; color: #666; font-size: 13px;">Status</td>
                <td style="padding: 8px 0; color: #111; font-weight: bold; text-align: right; font-size: 13px;">{{ ucfirst($rfq->status ?? 'pending') }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #666; font-size: 13px;">Submitted</td>
                <td style="padding: 8px 0; color: #111; font-weight: bold; text-align: right; font-size: 13px;">{{ $rfq->created_at->format('M d, Y H:i') }}</td>
            </tr>
        </table>

        {{-- Amber "What happens next?" for pending states --}}
        @if(in_array($type, ['confirmation', 'submitted']))
            <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 12px 15px; margin: 15px 0; border-radius: 4px;">
                <p style="font-size: 13px; color: #92400e; margin: 0; line-height: 1.5;">
                    ⏳ <strong>What happens next?</strong>
                    @if($type === 'confirmation')
                        The provider will review your RFQ and respond with a quote shortly. You'll receive an email as soon as the quote is ready.
                    @else
                        Please review and respond with a competitive quote. The buyer will be notified when you submit.
                    @endif
                </p>
            </div>
        @endif

        {{-- CTA Button --}}
        <div style="text-align: center; margin: 25px 0;">
            <a href="{{ $__ctaUrl }}"
               style="display: inline-block; background: #6366f1; color: #fff; padding: 12px 30px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 14px;">
                {{ $__ctaLabel }}
            </a>
        </div>

        {{-- Info box --}}
        <div style="background: #eff6ff; border-left: 4px solid #3b82f6; padding: 12px 15px; margin: 25px 0; border-radius: 4px;">
            <p style="font-size: 13px; color: #1e40af; margin: 0; line-height: 1.5;">
                <strong>Tip:</strong> You can message the {{ $role === 'provider' ? 'buyer' : 'provider' }} directly from the RFQ page to clarify any details.
            </p>
        </div>
    </div>

    <div style="background: #f9fafb; padding: 20px; text-align: center; border-top: 1px solid #e5e7eb;">
        <p style="font-size: 11px; color: #9ca3af; margin: 0;">© {{ date('Y') }} TravelAI Nepal · All rights reserved.</p>
    </div>
</div>
</body>
</html>