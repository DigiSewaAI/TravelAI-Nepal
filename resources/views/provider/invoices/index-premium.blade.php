@extends($provider_layout ?? 'layouts.provider')
@section('title', __('messages.invoices_page_title'))
@section('header', __('messages.invoices_header'))
@section('content')
<style>
.pi-hero{background:linear-gradient(135deg,var(--accent,#2563eb) 0%,var(--accent-d,#1d4ed8) 100%);border-radius:18px;padding:26px 30px;margin-bottom:22px;color:#fff;position:relative;overflow:hidden;box-shadow:0 18px 40px -14px rgba(0,0,0,.3)}
.pi-hero::before{content:'';position:absolute;top:-50%;right:-10%;width:55%;height:200%;background:radial-gradient(circle,rgba(255,255,255,.18),transparent 60%);pointer-events:none}
.pi-hero h1{font-size:23px;font-weight:800;margin:0 0 6px;display:flex;align-items:center;gap:10px;color:#fff;position:relative;z-index:1}
.pi-hero p{font-size:13px;margin:0;opacity:.92;color:#fff;position:relative;z-index:1}
.pi-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:22px}
.pi-stat{background:var(--card,#fff);border:1px solid var(--line,#e8edf5);border-radius:16px;padding:18px;position:relative;overflow:hidden;box-shadow:0 2px 8px rgba(15,23,42,.04)}
.pi-stat::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;border-radius:16px 16px 0 0}
.pi-stat.total::before{background:var(--accent,#2563eb)}
.pi-stat.paid::before{background:var(--green,#10b981)}
.pi-stat.pending::before{background:var(--amber,#f59e0b)}
.pi-stat .ic{width:38px;height:38px;border-radius:11px;display:grid;place-items:center;font-size:16px;margin-bottom:12px}
.pi-stat.total .ic{background:var(--accent-soft,rgba(37,99,235,.1));color:var(--accent,#2563eb)}
.pi-stat.paid .ic{background:var(--green-soft,#e7f8f1);color:var(--green,#10b981)}
.pi-stat.pending .ic{background:var(--amber-soft,#fef5e3);color:var(--amber,#f59e0b)}
.pi-stat .lbl{font-size:11px;text-transform:uppercase;letter-spacing:.6px;color:var(--muted,#64748b);font-weight:800;margin-bottom:6px}
.pi-stat .val{font-size:26px;font-weight:800;color:var(--text,#0f172a);line-height:1}
.pi-card{background:var(--card,#fff);border:1px solid var(--line,#e8edf5);border-radius:18px;overflow:hidden;box-shadow:0 4px 24px -8px rgba(15,23,42,.08)}
.pi-toolbar{padding:18px 24px;border-bottom:1px solid var(--line,#e8edf5);background:var(--bg,#f8fafc)}
.pi-toolbar form{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
.pi-select{padding:9px 14px;border-radius:10px;border:1px solid var(--line,#e8edf5);background:var(--card,#fff);color:var(--text,#0f172a);font-family:inherit;font-size:13px;font-weight:600;min-width:160px}
.pi-select:focus{outline:none;border-color:var(--accent,#2563eb);box-shadow:0 0 0 3px var(--accent-soft,rgba(37,99,235,.12))}
.pi-btn-filter{background:var(--accent,#2563eb);color:#fff;border:none;padding:9px 20px;border-radius:10px;font-family:inherit;font-size:13px;font-weight:800;cursor:pointer;box-shadow:0 4px 14px -4px var(--accent,#2563eb);transition:.2s}
.pi-btn-filter:hover{background:var(--accent-d,#1d4ed8);transform:translateY(-1px)}
.pi-table-wrap{overflow-x:auto}
.pi-table{width:100%;border-collapse:collapse}
.pi-table thead th{padding:14px 20px;text-align:left;font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.7px;color:var(--muted,#64748b);background:var(--accent-soft,rgba(37,99,235,.06));border-bottom:1px solid var(--line,#e8edf5)}
.pi-table tbody td{padding:16px 20px;font-size:13.5px;font-weight:600;color:var(--text,#0f172a);border-bottom:1px solid var(--line,#e8edf5);vertical-align:middle}
.pi-table tbody tr:hover td{background:var(--accent-soft,rgba(37,99,235,.04))}
.pi-table tbody tr:last-child td{border-bottom:none}
.pi-num{font-family:'Courier New',monospace;font-weight:700;color:var(--accent,#2563eb)}
.pi-pill{display:inline-flex;padding:4px 11px;border-radius:20px;font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.4px}
.pi-pill.paid{background:var(--green-soft,#e7f8f1);color:var(--green,#10b981)}
.pi-pill.pending{background:var(--amber-soft,#fef5e3);color:var(--amber,#f59e0b)}
.pi-pill.overdue{background:var(--red-soft,#fdecec);color:var(--red,#ef4444)}
.pi-actions{display:flex;gap:8px;flex-wrap:wrap}
.pi-act{display:inline-flex;align-items:center;gap:5px;padding:7px 12px;border-radius:8px;font-size:11.5px;font-weight:700;text-decoration:none;transition:.2s}
.pi-act.view{background:var(--accent-soft,rgba(37,99,235,.1));color:var(--accent,#2563eb)}
.pi-act.view:hover{background:var(--accent,#2563eb);color:#fff}
.pi-act.download{background:var(--green-soft,#e7f8f1);color:var(--green,#10b981)}
.pi-act.download:hover{background:var(--green,#10b981);color:#fff}
.pi-pagination{padding:18px 24px;border-top:1px solid var(--line,#e8edf5);background:var(--bg,#f8fafc)}
.pi-empty{padding:72px 24px;text-align:center}
.pi-empty-ic{width:88px;height:88px;border-radius:50%;background:linear-gradient(135deg,var(--accent-soft,rgba(37,99,235,.12)),rgba(37,99,235,.05));color:var(--accent,#2563eb);display:inline-grid;place-items:center;font-size:38px;margin-bottom:22px;box-shadow:0 14px 34px -12px var(--accent,#2563eb)}
.pi-empty h3{font-size:20px;font-weight:800;color:var(--text,#0f172a);margin:0;letter-spacing:-.4px}
</style>

<div>
  <div class="pi-hero">
    <h1><i class="fas fa-file-invoice"></i> {{ __('messages.invoices_page_title') }}</h1>
    <p>{{ __('messages.invoices_header') }}</p>
  </div>

  <div class="pi-stats">
    <div class="pi-stat total">
      <div class="ic"><i class="fas fa-file-invoice"></i></div>
      <div class="lbl">{{ __('messages.total_invoices') }}</div>
      <div class="val">{{ $stats['total'] }}</div>
    </div>
    <div class="pi-stat paid">
      <div class="ic"><i class="fas fa-check"></i></div>
      <div class="lbl">{{ __('messages.paid') }}</div>
      <div class="val">{{ $stats['paid'] }}</div>
    </div>
    <div class="pi-stat pending">
      <div class="ic"><i class="fas fa-clock"></i></div>
      <div class="lbl">{{ __('messages.pending') }}</div>
      <div class="val">{{ $stats['pending'] }}</div>
    </div>
  </div>

  <div class="pi-card">
    <div class="pi-toolbar">
      <form method="GET">
        <select name="status" class="pi-select">
          <option value="">{{ __('messages.all_status') }}</option>
          <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('messages.pending') }}</option>
          <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>{{ __('messages.paid') }}</option>
          <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>{{ __('messages.overdue') }}</option>
        </select>
        <button type="submit" class="pi-btn-filter"><i class="fas fa-filter"></i> {{ __('messages.filter') }}</button>
      </form>
    </div>

    <div class="pi-table-wrap">
      <table class="pi-table">
        <thead>
          <tr>
            <th>{{ __('messages.invoice_number') }}</th>
            <th>{{ __('messages.amount') }}</th>
            <th>{{ __('messages.status') }}</th>
            <th>{{ __('messages.date') }}</th>
            <th>{{ __('messages.actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse($invoices as $invoice)
            <tr>
              <td><span class="pi-num">{{ $invoice->invoice_number }}</span></td>
              <td><strong>{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</strong></td>
              <td>
                @if($invoice->status === 'paid')
                  <span class="pi-pill paid">{{ __('messages.paid') }}</span>
                @elseif($invoice->status === 'pending')
                  <span class="pi-pill pending">{{ __('messages.pending') }}</span>
                @elseif($invoice->status === 'overdue')
                  <span class="pi-pill overdue">{{ __('messages.overdue') }}</span>
                @else
                  <span class="pi-pill pending">{{ ucfirst($invoice->status) }}</span>
                @endif
              </td>
              <td>{{ $invoice->created_at->format('M d, Y') }}</td>
              <td>
                <div class="pi-actions">
                  <a href="{{ route('provider.invoices.show', $invoice) }}" class="pi-act view"><i class="fas fa-eye"></i> {{ __('messages.view') }}</a>
                  <a href="{{ route('provider.invoices.download', $invoice) }}" class="pi-act download"><i class="fas fa-download"></i> {{ __('messages.download') }}</a>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5">
                <div class="pi-empty">
                  <div class="pi-empty-ic"><i class="fas fa-file-invoice"></i></div>
                  <h3>{{ __('messages.no_invoices_found') }}</h3>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="pi-pagination">{{ $invoices->links() }}</div>
  </div>
</div>
@endsection