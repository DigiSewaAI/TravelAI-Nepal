@extends($provider_layout ?? 'layouts.provider')
@section('title', 'Complete Your Payment')
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
<div class="ps-wrap" style="max-width:1080px">
    <a href="{{ route('provider.subscriptions.index') }}" class="ps-back">
        <i class="fas fa-arrow-left"></i> Back to Subscriptions
    </a>

    <div class="ps-hero">
        <h1><i class="fas fa-credit-card"></i> Complete Your Payment</h1>
        <p>{{ $subscription->plan->name }} Plan — Rs. {{ number_format($subscription->plan->price_monthly ?? 0, 2) }} / month</p>
    </div>

    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;padding:16px 20px;background:var(--card);border:1px solid var(--line);border-radius:14px;margin-bottom:18px">
        <span style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:700;color:var(--accent)">
            <span style="width:26px;height:26px;border-radius:50%;background:var(--accent);color:#fff;display:grid;place-items:center;font-size:12px">1</span>
            Make Payment
        </span>
        <span style="flex:1;height:2px;background:var(--line);max-width:40px"></span>
        <span style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:700;color:{{ $pendingPayment ? 'var(--accent)' : 'var(--muted)' }}">
            <span style="width:26px;height:26px;border-radius:50%;background:{{ $pendingPayment ? 'var(--accent)' : 'var(--bg)' }};color:{{ $pendingPayment ? '#fff' : 'var(--muted)' }};display:grid;place-items:center;font-size:12px;border:1px solid var(--line)">2</span>
            Submit Proof
        </span>
        <span style="flex:1;height:2px;background:var(--line);max-width:40px"></span>
        <span style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:700;color:var(--muted)">
            <span style="width:26px;height:26px;border-radius:50%;background:var(--bg);color:var(--muted);display:grid;place-items:center;font-size:12px;border:1px solid var(--line)">3</span>
            Verification
        </span>
    </div>

    @if(session('success'))
        <div class="ps-alert success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="ps-alert error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
    @endif

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px" class="ps-two-col">
        <div>
            <div class="ps-card">
                <div class="ps-card-body">
                    <div style="display:flex;gap:16px;align-items:flex-start">
                        <div class="ps-row-icon" style="width:56px;height:56px;font-size:22px"><i class="fas fa-box"></i></div>
                        <div style="flex:1">
                            <div style="font-size:18px;font-weight:800;color:var(--text);margin-bottom:4px">{{ $subscription->plan->name }} Plan</div>
                            <div style="font-size:20px;font-weight:800;color:var(--accent)">Rs. {{ number_format($subscription->plan->price_monthly ?? 0, 2) }}<span style="font-size:13px;color:var(--muted);font-weight:600"> / month</span></div>
                            @if(!empty($subscription->plan->limits))
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-top:14px;font-size:12.5px">
                                    @foreach((array) $subscription->plan->limits as $key => $value)
                                        <div style="display:flex;align-items:center;gap:6px;color:var(--muted)">
                                            <i class="fas fa-check" style="color:var(--green)"></i>
                                            <span>{{ ucfirst(str_replace('_', ' ', $key)) }}: <strong style="color:var(--text)">{{ $value }}</strong></span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="ps-card">
                <div class="ps-card-head">
                    <h3><i class="fas fa-hand-holding-usd"></i> Choose how to pay TravelAI</h3>
                </div>
                <div class="ps-card-body">
                    <p style="font-size:13px;color:var(--muted);margin:0 0 16px">Pay to one of the accounts below. Then submit your proof for verification.</p>

                    @if($platformMethods->isEmpty())
                        <div class="ps-alert warn"><i class="fas fa-info-circle"></i> Payment methods are being configured. Please contact support.</div>
                    @else
                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px">
                            @foreach($platformMethods as $method)
                                <div style="border:1px solid var(--line);border-radius:14px;padding:16px;display:flex;flex-direction:column;background:var(--bg)">
                                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px">
                                        @if($method->type === 'bank')
                                            <i class="fas fa-university" style="color:var(--accent);font-size:20px"></i>
                                        @elseif($method->type === 'esewa')
                                            <i class="fas fa-mobile-alt" style="color:var(--green);font-size:20px"></i>
                                        @elseif($method->type === 'khalti')
                                            <i class="fas fa-wallet" style="color:#8b5cf6;font-size:20px"></i>
                                        @else
                                            <i class="fas fa-credit-card" style="color:var(--muted);font-size:20px"></i>
                                        @endif
                                        <span style="font-weight:800;color:var(--text)">{{ $method->label }}</span>
                                    </div>

                                    @if($method->bank_name)
                                        <div style="font-size:11px;color:var(--muted);font-weight:700;text-transform:uppercase;margin-top:6px">Bank</div>
                                        <div style="font-size:13px;font-weight:700;color:var(--text)">{{ $method->bank_name }}</div>
                                    @endif
                                    @if($method->account_name)
                                        <div style="font-size:11px;color:var(--muted);font-weight:700;text-transform:uppercase;margin-top:6px">Account Name</div>
                                        <div style="font-size:13px;font-weight:700;color:var(--text)">{{ $method->account_name }}</div>
                                    @endif
                                    @if($method->account_number)
                                        <div style="font-size:11px;color:var(--muted);font-weight:700;text-transform:uppercase;margin-top:6px">Account Number</div>
                                        <div style="font-size:13px;font-weight:700;color:var(--text);font-family:'Courier New',monospace">{{ $method->account_number }}</div>
                                    @endif
                                    @if($method->identifier)
                                        <div style="font-size:11px;color:var(--muted);font-weight:700;text-transform:uppercase;margin-top:6px">{{ $method->type === 'esewa' ? 'eSewa ID' : ($method->type === 'khalti' ? 'Khalti Number' : 'ID') }}</div>
                                        <div style="font-size:13px;font-weight:700;color:var(--text);font-family:'Courier New',monospace">{{ $method->identifier }}</div>
                                    @endif

                                    @if($method->qr_image_path)
                                        <div style="margin-top:12px;text-align:center">
                                            <img src="{{ Storage::url($method->qr_image_path) }}" alt="{{ $method->label }} QR" style="width:128px;height:128px;object-fit:contain;border:1px solid var(--line);border-radius:10px;background:#fff;padding:4px;margin:0 auto;display:block">
                                        </div>
                                    @endif

                                    <button type="button" onclick="copyText(this, '{{ $method->account_number ?? $method->identifier }}')" style="margin-top:14px;width:100%;font-size:12.5px;background:var(--card);border:1px solid var(--line);border-radius:10px;padding:9px;display:flex;align-items:center;justify-content:center;gap:6px;cursor:pointer;font-weight:700;color:var(--muted);font-family:inherit;transition:.2s">
                                        <i class="fas fa-copy"></i>
                                        <span>Copy {{ $method->type === 'bank' ? 'Details' : 'ID' }}</span>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            @if($pendingPayment)
                <div class="ps-card">
                    <div class="ps-card-body">
                        <div class="ps-alert warn" style="margin:0">
                            <i class="fas fa-hourglass-half" style="font-size:20px"></i>
                            <div>
                                <div style="font-size:14px;font-weight:800">Awaiting Verification</div>
                                <div style="font-size:13px;margin-top:4px;font-weight:600">Your payment proof was submitted on {{ $pendingPayment->created_at->format('M d, Y g:i A') }}. Reference: <strong>{{ $pendingPayment->reference_number }}</strong></div>
                                <div style="font-size:11.5px;margin-top:6px">We verify payments within 24 hours. You'll be notified once approved.</div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <form method="POST" action="{{ route('provider.payments.create', $subscription->id) }}" enctype="multipart/form-data" class="ps-card" style="padding:0;border:1px solid var(--line);background:var(--card);border-radius:18px;box-shadow:0 4px 24px -8px rgba(15,23,42,.08)">
                    @csrf
                    <div class="ps-card-head">
                        <h3><i class="fas fa-file-upload"></i> Submit payment proof</h3>
                    </div>
                    <div class="ps-card-body">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px" class="ps-two-col">
                            <div class="ps-field">
                                <label>Reference Number <span style="color:var(--red)">*</span></label>
                                <input type="text" name="reference_number" required value="{{ old('reference_number') }}" placeholder="e.g., TXN-1234567890">
                                @error('reference_number')
                                    <p class="ps-error-text">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="ps-field">
                                <label>Upload Receipt <span style="color:var(--red)">*</span></label>
                                <input type="file" name="receipt" accept="image/*,application/pdf" required>
                                <p style="font-size:11px;color:var(--muted);margin-top:4px">JPG, PNG, or PDF — Max 5 MB</p>
                                @error('receipt')
                                    <p class="ps-error-text">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <button type="submit" class="ps-btn primary w-full" style="margin-top:8px">
                            <i class="fas fa-paper-plane"></i>
                            Submit for Verification
                        </button>
                    </div>
                </form>
            @endif
        </div>

        <div>
            <div class="ps-card">
                <div class="ps-card-head">
                    <h3><i class="fas fa-receipt"></i> Payment Summary</h3>
                </div>
                <div class="ps-card-body">
                    <dl style="margin:0">
                        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--line)">
                            <dt style="font-size:13px;color:var(--muted)">Plan</dt>
                            <dd style="font-size:13px;font-weight:700;color:var(--text);margin:0">{{ $subscription->plan->name }}</dd>
                        </div>
                        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--line)">
                            <dt style="font-size:13px;color:var(--muted)">Amount</dt>
                            <dd style="font-size:13px;font-weight:700;color:var(--text);margin:0">Rs. {{ number_format($subscription->plan->price_monthly ?? 0, 2) }} / mo</dd>
                        </div>
                        <div style="display:flex;justify-content:space-between;padding:8px 0">
                            <dt style="font-size:13px;color:var(--muted)">Billing</dt>
                            <dd style="font-size:13px;font-weight:700;color:var(--text);margin:0">{{ ucfirst($subscription->billing_interval ?? 'monthly') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="ps-alert info" style="margin-bottom:0">
                <i class="fas fa-info-circle"></i>
                <div style="font-size:12.5px;line-height:1.6"><strong>Important:</strong> This payment is for your TravelAI subscription and will be manually verified by our team. Once verified, your subscription will be activated and you'll get full access to {{ $subscription->plan->name }} features.</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyText(btn, text) {
    if (!text) return;
    navigator.clipboard.writeText(text).then(function () {
        var original = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i><span>Copied!</span>';
        btn.classList.add('bg-green-50', 'border-green-300', 'text-green-700');
        setTimeout(function () {
            btn.innerHTML = original;
            btn.classList.remove('bg-green-50', 'border-green-300', 'text-green-700');
        }, 1500);
    });
}
</script>
@endpush