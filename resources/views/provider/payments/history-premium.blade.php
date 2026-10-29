@extends($provider_layout ?? 'layouts.provider')
@section('title', __('messages.payment_history_title'))
@section('header', __('messages.payment_history_header'))
@section('content')
<style>
.pp-hero{background:linear-gradient(135deg,var(--accent,#2563eb) 0%,var(--accent-d,#1d4ed8) 100%);border-radius:18px;padding:26px 30px;margin-bottom:22px;color:#fff;position:relative;overflow:hidden;box-shadow:0 18px 40px -14px rgba(0,0,0,.3);display:flex;justify-content:space-between;align-items:center;gap:20px;flex-wrap:wrap}
.pp-hero::before{content:'';position:absolute;top:-50%;right:-10%;width:55%;height:200%;background:radial-gradient(circle,rgba(255,255,255,.18),transparent 60%);pointer-events:none}
.pp-hero>div,.pp-hero>a{position:relative;z-index:1}
.pp-hero h1{font-size:23px;font-weight:800;margin:0 0 6px;display:flex;align-items:center;gap:10px;color:#fff}
.pp-hero p{font-size:13px;margin:0;opacity:.92;color:#fff}
.pp-hero-btn{display:inline-flex;align-items:center;gap:7px;background:rgba(255,255,255,.22);color:#fff;padding:10px 18px;border-radius:11px;font-size:12.5px;font-weight:800;text-decoration:none;transition:.2s}
.pp-hero-btn:hover{background:rgba(255,255,255,.32)}
.pp-card{background:var(--card,#fff);border:1px solid var(--line,#e8edf5);border-radius:18px;overflow:hidden;box-shadow:0 4px 24px -8px rgba(15,23,42,.08)}
.pp-card-head{padding:20px 24px;border-bottom:1px solid var(--line,#e8edf5)}
.pp-card-head h2{font-size:16px;font-weight:800;color:var(--text,#0f172a);margin:0}
.pp-table-wrap{overflow-x:auto}
.pp-table{width:100%;border-collapse:collapse}
.pp-table thead th{padding:14px 20px;text-align:left;font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.7px;color:var(--muted,#64748b);background:var(--accent-soft,rgba(37,99,235,.06));border-bottom:1px solid var(--line,#e8edf5)}
.pp-table tbody td{padding:16px 20px;font-size:13.5px;font-weight:600;color:var(--text,#0f172a);border-bottom:1px solid var(--line,#e8edf5);vertical-align:middle}
.pp-table tbody tr:hover td{background:var(--accent-soft,rgba(37,99,235,.04))}
.pp-table tbody tr:last-child td{border-bottom:none}
.pp-link{color:var(--accent,#2563eb);text-decoration:none;font-weight:700;font-family:'Courier New',monospace;font-size:12.5px}
.pp-link:hover{text-decoration:underline}
.pp-pill{display:inline-flex;padding:4px 11px;border-radius:20px;font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.4px}
.pp-pill.success{background:var(--green-soft,#e7f8f1);color:var(--green,#10b981)}
.pp-pill.pending{background:var(--amber-soft,#fef5e3);color:var(--amber,#f59e0b)}
.pp-pill.failed{background:var(--red-soft,#fdecec);color:var(--red,#ef4444)}
.pp-pill.other{background:var(--bg,#f4f6fb);color:var(--muted,#64748b)}
.pp-pagination{padding:18px 24px;border-top:1px solid var(--line,#e8edf5);background:var(--bg,#f8fafc)}
.pp-empty{padding:72px 24px;text-align:center}
.pp-empty-ic{width:88px;height:88px;border-radius:50%;background:linear-gradient(135deg,var(--accent-soft,rgba(37,99,235,.12)),rgba(37,99,235,.05));color:var(--accent,#2563eb);display:inline-grid;place-items:center;font-size:38px;margin-bottom:22px;box-shadow:0 14px 34px -12px var(--accent,#2563eb)}
.pp-empty h3{font-size:20px;font-weight:800;color:var(--text,#0f172a);margin:0;letter-spacing:-.4px}
</style>

<div>
  <div class="pp-hero">
    <div>
      <h1><i class="fas fa-credit-card"></i> {{ __('messages.payment_history_title') }}</h1>
      <p>{{ __('messages.all_payments') }}</p>
    </div>
    <a href="{{ route('provider.subscriptions.index') }}" class="pp-hero-btn">
      <i class="fas fa-arrow-left"></i> {{ __('messages.back_to_subscription') }}
    </a>
  </div>

  <div class="pp-card">
    <div class="pp-card-head">
      <h2>{{ __('messages.all_payments') }}</h2>
    </div>

    @if($payments->count() > 0)
      <div class="pp-table-wrap">
        <table class="pp-table">
          <thead>
            <tr>
              <th>{{ __('messages.date') }}</th>
              <th>{{ __('messages.payment_id') }}</th>
              <th>{{ __('messages.plan') }}</th>
              <th>{{ __('messages.amount') }}</th>
              <th>{{ __('messages.status') }}</th>
              <th>{{ __('messages.gateway') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach($payments as $payment)
              <tr>
                <td>{{ $payment->created_at->format('Y-m-d H:i') }}</td>
                <td>
                  <a href="{{ route('provider.payments.show', $payment->id) }}" class="pp-link">
                    {{ substr($payment->payment_id, 0, 12) }}...
                  </a>
                </td>
                <td>{{ $payment->payable->plan->name ?? __('messages.na') }}</td>
                <td><strong>Rs. {{ number_format($payment->amount, 2) }}</strong></td>
                <td>
                  @if($payment->status === 'success')
                    <span class="pp-pill success">{{ __('messages.success') }}</span>
                  @elseif($payment->status === 'failed')
                    <span class="pp-pill failed">{{ __('messages.failed') }}</span>
                  @elseif($payment->status === 'pending')
                    <span class="pp-pill pending">{{ __('messages.pending') }}</span>
                  @else
                    <span class="pp-pill other">{{ ucfirst($payment->status) }}</span>
                  @endif
                </td>
                <td>{{ strtoupper($payment->gateway) }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="pp-pagination">{{ $payments->links() }}</div>
    @else
      <div class="pp-empty">
        <div class="pp-empty-ic"><i class="fas fa-credit-card"></i></div>
        <h3>{{ __('messages.no_payments_yet') }}</h3>
      </div>
    @endif
  </div>
</div>
@endsection