@extends($provider_layout ?? 'layouts.provider')
@section('title', __('messages.invoice_detail_title', ['number' => $invoice->invoice_number]))
@section('header', __('messages.invoice_detail_header'))
@section('content')
<style>
.ps-wrap{max-width:960px;margin:0 auto;padding:0 0 24px}
.ps-back{display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--muted,#64748b);text-decoration:none;font-weight:700;margin-bottom:16px;transition:.2s}
.ps-back:hover{color:var(--accent,#2563eb)}
.ps-hero{background:linear-gradient(135deg,var(--accent,#2563eb) 0%,var(--accent-d,#1d4ed8) 100%);border-radius:18px;padding:26px 30px;margin-bottom:22px;color:#fff;position:relative;overflow:hidden;box-shadow:0 18px 40px -14px rgba(0,0,0,.3)}
.ps-hero::before{content:'';position:absolute;top:-50%;right:-10%;width:55%;height:200%;background:radial-gradient(circle,rgba(255,255,255,.18),transparent 60%);pointer-events:none}
.ps-hero.danger{background:linear-gradient(135deg,#dc2626 0%,#b91c1c 100%)}
.ps-hero.ok{background:linear-gradient(135deg,#10b981 0%,#059669 100%)}
.ps-hero h1{font-size:23px;font-weight:800;margin:0 0 6px;display:flex;align-items:center;gap:12px;color:#fff;position:relative;z-index:1;flex-wrap:wrap}
.ps-hero p{font-size:13px;margin:0;opacity:.92;color:#fff;position:relative;z-index:1}
.ps-card{background:var(--card,#fff);border:1px solid var(--line,#e8edf5);border-radius:18px;box-shadow:0 4px 24px -8px rgba(15,23,42,.08);overflow:hidden;margin-bottom:18px}
.ps-card-head{padding:18px 24px;border-bottom:1px solid var(--line,#e8edf5);background:var(--bg,#f8fafc)}
.ps-card-head h3{font-size:15px;font-weight:800;color:var(--text,#0f172a);margin:0;display:flex;align-items:center;gap:10px}
.ps-card-body{padding:22px 24px}
.ps-row{display:flex;align-items:flex-start;gap:16px;padding:16px 0;border-bottom:1px solid var(--line,#e8edf5)}
.ps-row:first-child{padding-top:0}
.ps-row:last-child{border-bottom:none;padding-bottom:0}
.ps-row-icon{width:44px;height:44px;border-radius:12px;display:grid;place-items:center;font-size:17px;flex-shrink:0;background:var(--accent-soft,rgba(37,99,235,.1));color:var(--accent,#2563eb)}
.ps-row-icon.red{background:var(--red-soft,#fdecec);color:var(--red,#ef4444)}
.ps-row-icon.green{background:var(--green-soft,#e7f8f1);color:var(--green,#10b981)}
.ps-row-icon.amber{background:var(--amber-soft,#fef5e3);color:var(--amber,#f59e0b)}
.ps-row-icon.purple{background:rgba(139,92,246,.1);color:#8b5cf6}
.ps-row-body{flex:1;min-width:0}
.ps-row-label{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:var(--muted,#64748b);margin-bottom:6px}
.ps-row-val{font-size:14px;font-weight:700;color:var(--text,#0f172a);word-break:break-word}
.ps-row-sub{font-size:12px;color:var(--muted,#64748b);margin-top:4px;font-weight:600}
.ps-row-link{display:inline-flex;align-items:center;gap:6px;margin-top:6px;font-size:12.5px;font-weight:700;color:var(--accent,#2563eb);text-decoration:none}
.ps-row-link:hover{text-decoration:underline}
.ps-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;padding:20px 24px}
.ps-stat{background:var(--bg,#f8fafc);border:1px solid var(--line,#e8edf5);border-radius:14px;padding:16px;text-align:center}
.ps-stat .val{font-size:24px;font-weight:800;color:var(--accent,#2563eb);line-height:1}
.ps-stat.green .val{color:var(--green,#10b981)}
.ps-stat.amber .val{color:var(--amber,#f59e0b)}
.ps-stat .lbl{font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:var(--muted,#64748b);margin-top:6px}
.ps-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:13px 24px;border-radius:12px;font-size:14px;font-weight:800;text-decoration:none;border:none;cursor:pointer;font-family:inherit;transition:.2s}
.ps-btn.primary{background:var(--accent,#2563eb);color:#fff;box-shadow:0 10px 26px -8px var(--accent,#2563eb)}
.ps-btn.primary:hover{background:var(--accent-d,#1d4ed8);transform:translateY(-1px)}
.ps-btn.green{background:var(--green,#10b981);color:#fff;box-shadow:0 10px 26px -8px var(--green,#10b981)}
.ps-btn.green:hover{background:#0e9f6e;transform:translateY(-1px)}
.ps-btn.ghost{background:var(--bg,#f4f6fb);color:var(--muted,#64748b);border:1px solid var(--line,#e8edf5)}
.ps-btn.ghost:hover{background:var(--line,#e8edf5);color:var(--text,#0f172a)}
.ps-btn.w-full{width:100%}
.ps-alert{padding:14px 18px;border-radius:12px;font-size:13px;font-weight:600;margin-bottom:18px;display:flex;align-items:center;gap:10px}
.ps-alert.success{background:var(--green-soft,#e7f8f1);color:var(--green,#10b981);border-left:4px solid var(--green,#10b981)}
.ps-alert.error{background:var(--red-soft,#fdecec);color:var(--red,#ef4444);border-left:4px solid var(--red,#ef4444)}
.ps-alert.info{background:var(--blue-soft,#e9f1fe);color:var(--accent,#2563eb);border-left:4px solid var(--accent,#2563eb)}
.ps-alert.warn{background:var(--amber-soft,#fef5e3);color:var(--amber,#f59e0b);border-left:4px solid var(--amber,#f59e0b)}
.ps-badge{display:inline-flex;padding:5px 12px;border-radius:20px;font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.4px}
.ps-badge.green{background:var(--green-soft,#e7f8f1);color:var(--green,#10b981)}
.ps-badge.red{background:var(--red-soft,#fdecec);color:var(--red,#ef4444)}
.ps-badge.amber{background:var(--amber-soft,#fef5e3);color:var(--amber,#f59e0b)}
.ps-badge.blue{background:var(--blue-soft,#e9f1fe);color:var(--accent,#2563eb)}
.ps-badge.gray{background:var(--bg,#f4f6fb);color:var(--muted,#64748b)}
.ps-img{width:100%;height:260px;object-fit:cover;display:block}
.ps-dl{display:grid;grid-template-columns:1fr 1fr;gap:16px}
@media(max-width:640px){.ps-dl{grid-template-columns:1fr}}
.ps-dl>div{padding:12px 0}
.ps-dl dt{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:var(--muted,#64748b);margin-bottom:4px}
.ps-dl dd{font-size:14px;font-weight:700;color:var(--text,#0f172a);margin:0}
.ps-field{margin-bottom:16px}
.ps-field label{display:block;font-size:12.5px;font-weight:800;color:var(--text,#0f172a);margin-bottom:6px}
.ps-field input[type=text],.ps-field input[type=file]{width:100%;padding:10px 14px;border:1px solid var(--line,#e8edf5);border-radius:10px;background:var(--card,#fff);color:var(--text,#0f172a);font-family:inherit;font-size:13px}
.ps-field input[type=file]{padding:8px 12px}
.ps-field input:focus{outline:none;border-color:var(--accent,#2563eb);box-shadow:0 0 0 3px var(--accent-soft,rgba(37,99,235,.12))}
.ps-error-text{color:var(--red,#ef4444);font-size:12px;margin-top:4px;font-weight:600}
.ps-message{background:var(--bg,#f8fafc);border-left:4px solid var(--accent,#2563eb);border-radius:10px;padding:14px 18px;font-size:13.5px;color:var(--text,#0f172a);font-style:italic;line-height:1.6;margin-top:8px}
</style>
<div class="ps-wrap" style="max-width:820px">
    <div class="ps-hero">
        <h1><i class="fas fa-file-invoice"></i> {{ __('messages.invoice_hash', ['number' => $invoice->invoice_number]) }}</h1>
        <p>{{ __('messages.created_at') }}: {{ $invoice->created_at->format('M d, Y H:i') }}</p>
    </div>

    <div class="ps-card">
        <div class="ps-card-body">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;padding-bottom:16px;border-bottom:1px solid var(--line)">
                <div>
                    <div class="ps-row-label">{{ __('messages.status') }}</div>
                    @if($invoice->status === 'paid')
                        <span class="ps-badge green">{{ __('messages.paid') }}</span>
                    @elseif($invoice->status === 'pending')
                        <span class="ps-badge amber">{{ __('messages.pending') }}</span>
                    @elseif($invoice->status === 'overdue')
                        <span class="ps-badge red">{{ __('messages.overdue') }}</span>
                    @else
                        <span class="ps-badge gray">{{ ucfirst($invoice->status) }}</span>
                    @endif
                </div>
                <div style="text-align:right">
                    <div class="ps-row-label">{{ __('messages.total_amount') }}</div>
                    <div style="font-size:26px;font-weight:800;color:var(--accent)">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</div>
                </div>
            </div>

            <dl class="ps-dl" style="padding-top:16px">
                <div>
                    <dt>{{ __('messages.provider') }}</dt>
                    <dd>{{ $invoice->provider->name ?? __('messages.na') }}</dd>
                </div>
                <div>
                    <dt>{{ __('messages.invoice_number') }}</dt>
                    <dd>{{ $invoice->invoice_number }}</dd>
                </div>
                <div>
                    <dt>{{ __('messages.receipt_number') }}</dt>
                    <dd>{{ $invoice->receipt_number ?? __('messages.na') }}</dd>
                </div>
                <div>
                    <dt>{{ __('messages.payment_method') }}</dt>
                    <dd>{{ ucfirst($invoice->payment_method ?? __('messages.na')) }}</dd>
                </div>
                <div>
                    <dt>{{ __('messages.paid_at') }}</dt>
                    <dd>{{ $invoice->paid_at ? $invoice->paid_at->format('M d, Y H:i') : __('messages.na') }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <div style="display:flex;gap:12px;flex-wrap:wrap">
        <a href="{{ route('provider.invoices.download', $invoice) }}" class="ps-btn primary">
            <i class="fas fa-download"></i> {{ __('messages.download_pdf') }}
        </a>
        <a href="{{ route('provider.invoices.index') }}" class="ps-btn ghost">
            <i class="fas fa-arrow-left"></i> {{ __('messages.back_to_invoices') }}
        </a>
    </div>
</div>
@endsection