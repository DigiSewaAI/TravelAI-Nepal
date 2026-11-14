@extends('layouts.public') {{-- यदि तपाईंको पब्लिक लेआउटको नाम फरक छ भने यहाँ परिवर्तन गर्नुस् --}}

@section('title', $service->name)

@section('content')
<style>
    .rest-hero { background: linear-gradient(135deg, var(--accent, #f97316), var(--accent-d, #ea580c)); padding: 60px 20px; text-align: center; color: #fff; border-radius: 0 0 30px 30px; margin-bottom: 30px; }
    .rest-hero img { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 4px solid #fff; margin-bottom: 15px; background: #fff; box-shadow: 0 10px 20px rgba(0,0,0,0.15); }
    .rest-hero h1 { font-size: 32px; font-weight: 800; margin: 0 0 10px; }
    .rest-hero p { font-size: 16px; opacity: 0.9; margin: 0; }
    .rest-container { max-width: 1200px; margin: 0 auto; padding: 0 20px; display: grid; grid-template-columns: 2fr 1fr; gap: 30px; }
    @media (max-width: 900px) { .rest-container { grid-template-columns: 1fr; } }
    
    .section-title { font-size: 22px; font-weight: 800; color: var(--text, #111); margin-bottom: 20px; border-bottom: 2px solid var(--line, #eee); padding-bottom: 10px; }
    
    .menu-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
    .menu-card { background: var(--card, #fff); border: 1px solid var(--line, #eee); border-radius: 16px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between; transition: 0.2s; }
    .menu-card:hover { box-shadow: 0 10px 25px rgba(0,0,0,0.05); transform: translateY(-3px); border-color: var(--accent, #f97316); }
    .menu-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; }
    .menu-name { font-size: 16px; font-weight: 800; color: var(--text, #111); margin: 0; }
    .menu-price { font-size: 16px; font-weight: 800; color: var(--accent, #f97316); }
    .menu-category { font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--muted, #666); background: var(--bg, #f9f9f9); padding: 4px 10px; border-radius: 20px; display: inline-block; }

    .reservation-card { background: var(--card, #fff); border: 1px solid var(--line, #eee); border-radius: 16px; padding: 25px; position: sticky; top: 100px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    .form-group { margin-bottom: 15px; }
    .form-label { display: block; font-size: 12px; font-weight: 700; color: var(--text, #111); margin-bottom: 6px; text-transform: uppercase; }
    .form-input { width: 100%; padding: 12px; border: 1px solid var(--line, #eee); border-radius: 10px; font-family: inherit; font-size: 14px; background: var(--bg, #f9f9f9); outline: none; }
    .form-input:focus { border-color: var(--accent, #f97316); }
    .btn-reserve { width: 100%; padding: 14px; background: var(--accent, #f97316); color: #fff; border: none; border-radius: 10px; font-size: 16px; font-weight: 800; cursor: pointer; transition: 0.2s; margin-top: 10px; }
    .btn-reserve:hover { filter: brightness(0.9); }
    .alert-success { background: var(--green-soft, #d1fae5); color: var(--green, #065f46); padding: 15px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; border: 1px solid var(--green, #065f46); }
</style>

<div class="rest-hero">
    @if($service->provider->logo_url)
        <img src="{{ Storage::url($service->provider->logo_url) }}" alt="{{ $service->provider->name }}">
    @else
        <div style="width:100px; height:100px; border-radius:50%; background:#fff; display:inline-flex; align-items:center; justify-content:center; font-size:40px; margin-bottom:15px;">🍽️</div>
    @endif
    <h1>{{ $service->provider->name }}</h1>
    <p>{{ $service->provider->address ?? 'Kathmandu, Nepal' }}</p>
    <div style="margin-top:15px; display:inline-flex; align-items:center; gap:8px; background:rgba(255,255,255,0.2); padding:6px 16px; border-radius:20px; font-size:13px; font-weight:700;">
        ✓ Verified Provider
    </div>
</div>

<div class="rest-container">
    {{-- Left Side: Menu --}}
    <div>
        <h2 class="section-title">Our Menu</h2>
        
        @if($menuItems->isEmpty())
            <p style="color:var(--muted);">Menu is being updated. Please check back later.</p>
        @else
            <div class="menu-grid">
                @foreach($menuItems as $item)
                <div class="menu-card">
                    <div>
                        <div class="menu-header">
                            <h3 class="menu-name">{{ $item->name }}</h3>
                            <span class="menu-price">Rs. {{ number_format($item->price, 2) }}</span>
                        </div>
                        <span class="menu-category">{{ $item->category }}</span>
                        @if($item->description)
                            <p style="font-size:13px; color:var(--muted); margin-top:10px;">{{ $item->description }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Right Side: Reservation Form --}}
    <div>
        <h2 class="section-title">Reserve a Table</h2>
        
        <div class="reservation-card">
            @if(session('success'))
                <div class="alert-success">
                    ✓ {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('public.restaurant.reserve', $service->slug) }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label class="form-label">Select Table</label>
                    <select name="table_id" required class="form-input">
                        <option value="">Choose a table...</option>
                        @foreach($tables as $table)
                            <option value="{{ $table->id }}">Table {{ $table->table_number }} ({{ $table->seats }} Seats)</option>
                        @endforeach
                    </select>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                    <div class="form-group">
                        <label class="form-label">Date</label>
                        <input type="date" name="reservation_date" required class="form-input" min="{{ date('Y-m-d') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Time</label>
                        <input type="time" name="reservation_time" required class="form-input">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Number of Guests</label>
                    <input type="number" name="guests" required min="1" max="50" class="form-input" placeholder="e.g. 2">
                </div>

                <div class="form-group">
                    <label class="form-label">Your Name</label>
                    <input type="text" name="customer_name" required class="form-input" placeholder="John Doe">
                </div>

                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="customer_phone" required class="form-input" placeholder="+977-98XXXXXXXX">
                </div>

                <div class="form-group">
                    <label class="form-label">Email (Optional)</label>
                    <input type="email" name="customer_email" class="form-input" placeholder="john@example.com">
                </div>

                <div class="form-group">
                    <label class="form-label">Special Requests (Optional)</label>
                    <textarea name="special_requests" rows="2" class="form-input" placeholder="Window seat, birthday setup, etc."></textarea>
                </div>

                <button type="submit" class="btn-reserve">Confirm Reservation</button>
            </form>
        </div>
    </div>
</div>
@endsection