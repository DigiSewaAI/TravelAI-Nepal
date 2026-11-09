@extends('layouts.provider-rescue')

@section('title', 'GPS Tracking')
@section('page-title', 'GPS Tracking')
@section('page-sub', 'Last-known locations · accuracy indicators · source attribution')

@section('content')

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px">
    <div>
        <h2 style="font-size:19px;font-weight:800;letter-spacing:-.02em">GPS Tracking</h2>
        <p style="font-size:12.5px;color:var(--text-muted);margin-top:3px">Last-known locations · accuracy indicators · source attribution</p>
    </div>
</div>

<div class="card" style="background:linear-gradient(160deg,#0a1620,#0b1f2a);border:1px solid #12262f;overflow:hidden;padding:0">
    <div style="position:relative;height:400px;background:#0b1f2a">
        <svg style="width:100%;height:100%;display:block" viewBox="0 0 900 400" preserveAspectRatio="xMidYMid slice">
            <rect width="900" height="400" fill="#0a1620"/>
            <path d="M0 300 Q120 240 260 275 T550 250 T900 290 V400 H0 Z" fill="rgba(45,212,191,0.05)"/>
            <path d="M0 340 Q160 300 330 320 T630 300 T900 340 V400 H0 Z" fill="rgba(45,212,191,0.07)"/>
            <path d="M150 290 L320 220 L510 165 L710 105" stroke="#2dd4bf" stroke-width="2.5" stroke-dasharray="8 8" fill="none" opacity=".6"/>
            
            <circle cx="150" cy="290" r="8" fill="#2dd4bf"/>
            <text x="150" y="325" text-anchor="middle" fill="#5eead4" font-size="11" font-weight="700">LUKLA BASE</text>
            
            <circle cx="510" cy="165" r="24" fill="none" stroke="#fbbf24" stroke-width="1.5" stroke-dasharray="4 4" opacity=".7"/>
            <circle cx="510" cy="165" r="8" fill="#ef4444"/>
            <text x="510" y="210" text-anchor="middle" fill="#fca5a5" font-size="11" font-weight="700">LAST-KNOWN · 4m ago</text>
            
            <circle cx="320" cy="220" r="7" fill="#3b82f6"/>
            <text x="320" y="252" text-anchor="middle" fill="#93c5fd" font-size="11" font-weight="700">9N-AJH · LIVE</text>
            
            <rect x="703" y="98" width="14" height="14" rx="2" fill="#10b981"/>
            <text x="710" y="145" text-anchor="middle" fill="#6ee7b7" font-size="11" font-weight="700">LUKLA HOSP.</text>
        </svg>
        
        <div style="position:absolute;left:10px;bottom:10px;display:flex;gap:8px;flex-wrap:wrap;pointer-events:none">
            <span style="display:inline-flex;align-items:center;gap:4px;font-size:9.5px;font-weight:600;color:#94a3b8;background:rgba(10,22,32,.8);padding:3px 8px;border-radius:99px;border:1px solid rgba(255,255,255,.06);backdrop-filter:blur(6px)"><i style="width:6px;height:6px;border-radius:50%;background:#2dd4bf"></i> Base</span>
            <span style="display:inline-flex;align-items:center;gap:4px;font-size:9.5px;font-weight:600;color:#94a3b8;background:rgba(10,22,32,.8);padding:3px 8px;border-radius:99px;border:1px solid rgba(255,255,255,.06);backdrop-filter:blur(6px)"><i style="width:6px;height:6px;border-radius:50%;background:#3b82f6"></i> Helicopter (live)</span>
            <span style="display:inline-flex;align-items:center;gap:4px;font-size:9.5px;font-weight:600;color:#94a3b8;background:rgba(10,22,32,.8);padding:3px 8px;border-radius:99px;border:1px solid rgba(255,255,255,.06);backdrop-filter:blur(6px)"><i style="width:6px;height:6px;border-radius:50%;background:#ef4444"></i> Last-known location</span>
            <span style="display:inline-flex;align-items:center;gap:4px;font-size:9.5px;font-weight:600;color:#94a3b8;background:rgba(10,22,32,.8);padding:3px 8px;border-radius:99px;border:1px solid rgba(255,255,255,.06);backdrop-filter:blur(6px)"><i style="width:6px;height:6px;border-radius:50%;background:#10b981"></i> Hospital</span>
        </div>
    </div>
    <div style="padding:14px 18px;font-size:11px;color:#94a3b8;border-top:1px solid rgba(255,255,255,.06);display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap">
        <div style="display:flex;gap:14px;flex-wrap:wrap">
            <span><strong style="color:#cbd5e1">Traveler (John A.):</strong> last ping 14:28:14 · ±25m · app GPS</span>
            <span><strong style="color:#cbd5e1">Helicopter 9N-AJH:</strong> last ping 8s ago · ±8m · transponder</span>
        </div>
    </div>
</div>

@endsection

@push('rescue-scripts')
<script>document.addEventListener('DOMContentLoaded', () => { if (window.lucide) lucide.createIcons(); });</script>
@endpush