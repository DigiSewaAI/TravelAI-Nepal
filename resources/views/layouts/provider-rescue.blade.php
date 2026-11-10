@php
    use Illuminate\Support\Facades\Auth;
    $currentUser = Auth::user();
    $initials = collect(explode(' ', $currentUser?->name ?? 'GU'))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('') ?: 'GU';
    $rescueProvider = $rescueProvider ?? null;
    $liveSosCount = $liveSosCount ?? 0;
    $activeMissionsCount = $activeMissionsCount ?? 0;
    $isOnDuty = $rescueProvider?->is_available ?? false;
    $actualFleetCount = $actualFleetCount ?? 0;
    // 🆕 Unread rescue dispatch notifications
    $unreadNotifs = $currentUser ? $currentUser->unreadNotifications()->where('data->type', 'rescue_dispatch')->count() : 0;
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8" />
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="robots" content="noindex, nofollow">
<title>@yield('title', 'Rescue Provider') — TravelAI Nepal</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{
  --bg:#f1f5f9;--surface:#fff;--surface-2:#f8fafc;--surface-3:#f1f5f9;
  --border:#e2e8f0;--border-strong:#cbd5e1;
  --text:#0f172a;--text-muted:#64748b;--text-light:#94a3b8;
  --primary:#0d9488;--primary-dark:#0f766e;--primary-light:#ccfbf1;--accent:#2dd4bf;
  --accent-d:#0d9488;--accent-soft:rgba(13,148,136,.12);--blue-soft:#ccfbf1;
  --danger:#dc2626;--danger-light:#fee2e2;--danger-dark:#991b1b;
  --warning:#f59e0b;--warning-light:#fef3c7;--warning-dark:#b45309;
  --success:#10b981;--success-light:#d1fae5;--success-dark:#047857;
  --info:#3b82f6;--info-light:#dbeafe;--info-dark:#1d4ed8;
  --purple:#8b5cf6;--purple-light:#ede9fe;--purple-dark:#6d28d9;
  --sidebar:#0a1220;--sidebar-2:#0d1a2b;--sidebar-text:#94a3b8;
  --radius:14px;--radius-sm:10px;
  --shadow-xs:0 1px 2px rgba(15,23,42,.04);
  --shadow-sm:0 1px 3px rgba(15,23,42,.06),0 1px 2px rgba(15,23,42,.04);
  --shadow:0 4px 16px rgba(15,23,42,.06);
  --shadow-lg:0 12px 40px rgba(15,23,42,.12);
}
html{scroll-behavior:smooth}
body{font-family:'Inter',system-ui,-apple-system,'Segoe UI',sans-serif;background:var(--bg);color:var(--text);-webkit-font-smoothing:antialiased;font-size:14px;line-height:1.5}
svg{display:block}button{font-family:inherit}
::-webkit-scrollbar{width:8px;height:8px}::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:99px}
.sidebar::-webkit-scrollbar-thumb{background:#1e293b}
.app{display:flex;min-height:100vh}

.sidebar{width:272px;flex-shrink:0;background:linear-gradient(180deg,var(--sidebar) 0%,var(--sidebar-2) 100%);color:var(--sidebar-text);height:100vh;position:sticky;top:0;display:flex;flex-direction:column;overflow-y:auto;border-right:1px solid rgba(255,255,255,.04);z-index:60}
.sidebar-brand{display:flex;align-items:center;gap:12px;padding:20px 18px 16px;border-bottom:1px solid rgba(255,255,255,.05)}
.brand-mark{width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,#dc2626,#f59e0b);display:grid;place-items:center;color:#fff;font-weight:800;font-size:14px;letter-spacing:-.5px;box-shadow:0 6px 18px rgba(220,38,38,.35);flex-shrink:0}
.brand-name{display:block;font-size:15px;font-weight:800;color:#fff;letter-spacing:-.02em;line-height:1.1}
.brand-sub{display:inline-flex;align-items:center;gap:5px;font-size:10px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#fca5a5;margin-top:5px;background:rgba(220,38,38,.15);padding:2px 7px;border-radius:99px;border:1px solid rgba(220,38,38,.25)}
.brand-sub::before{content:'';width:5px;height:5px;border-radius:50%;background:#ef4444;animation:blink 1.4s infinite}
.sidebar-nav{flex:1;padding:14px 12px}
.nav-group{margin-bottom:18px}
.nav-label{display:block;font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#475569;padding:0 12px 7px}
.nav-item{display:flex;align-items:center;gap:11px;padding:9px 12px;border-radius:9px;color:var(--sidebar-text);font-size:12.5px;font-weight:500;cursor:pointer;user-select:none;transition:all .16s ease;text-decoration:none}
.nav-item svg{width:16px;height:16px;flex-shrink:0;color:#64748b;transition:color .16s}
.nav-item:hover{background:rgba(255,255,255,.05);color:#e2e8f0}.nav-item:hover svg{color:#cbd5e1}
.nav-item.active{background:linear-gradient(90deg,rgba(13,148,136,.22),rgba(13,148,136,.04));color:#5eead4;box-shadow:inset 0 0 0 1px rgba(45,212,191,.18)}
.nav-item.active svg{color:#2dd4bf}
.nav-item .nav-badge{margin-left:auto;font-size:10px;font-weight:700;padding:2px 7px;border-radius:99px;background:rgba(220,38,38,.9);color:#fff}
.nav-item .nav-badge.teal{background:rgba(13,148,136,.9)}
.sidebar-footer{padding:12px 12px 18px;border-top:1px solid rgba(255,255,255,.05)}
.plan-card{background:linear-gradient(135deg,rgba(13,148,136,.18),rgba(45,212,191,.06));border:1px solid rgba(45,212,191,.22);border-radius:11px;padding:11px 13px;margin-bottom:10px}
.plan-card .pc-title{font-size:12px;font-weight:700;color:#5eead4;display:flex;align-items:center;gap:6px}
.plan-card .pc-title svg{width:13px;height:13px}
.plan-card .pc-sub{font-size:10.5px;color:#94a3b8;margin-top:2px;line-height:1.4}
.sidebar-net{display:flex;align-items:center;gap:8px;padding:8px 12px;border-radius:9px;background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.2);font-size:11px;color:#6ee7b7;font-weight:600}
.sidebar-net .dot{width:7px;height:7px;border-radius:50%;background:#10b981;animation:blink 2s infinite}

.main{flex:1;min-width:0;display:flex;flex-direction:column}
.topbar{position:sticky;top:0;z-index:40;display:flex;align-items:center;gap:14px;padding:13px 26px;background:rgba(255,255,255,.88);backdrop-filter:blur(14px);border-bottom:1px solid var(--border)}
.menu-btn{display:none}
.topbar-left{display:flex;align-items:center;gap:12px;min-width:0}
.page-title h1{font-size:18px;font-weight:800;letter-spacing:-.02em;line-height:1.2;white-space:nowrap}
.page-title p{font-size:12px;color:var(--text-muted);margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.topbar-center{flex:1;display:flex;justify-content:center;min-width:0}
.search-box{display:flex;align-items:center;gap:8px;background:var(--surface-2);border:1px solid var(--border);border-radius:10px;padding:8px 12px;width:100%;max-width:400px;transition:border-color .16s,box-shadow .16s}
.search-box:focus-within{border-color:var(--primary);box-shadow:0 0 0 3px rgba(13,148,136,.1);background:#fff}
.search-box svg{width:15px;height:15px;color:var(--text-light);flex-shrink:0}
.search-box input{border:none;background:transparent;outline:none;flex:1;font-size:12.5px;color:var(--text);font-family:inherit;min-width:0}
.topbar-right{display:flex;align-items:center;gap:10px}
.network-chip{display:inline-flex;align-items:center;gap:7px;padding:6px 11px;border-radius:99px;font-size:11.5px;font-weight:600;white-space:nowrap;background:var(--success-light);color:var(--success-dark)}
.network-chip .dot{width:7px;height:7px;border-radius:50%;background:currentColor;animation:blink 2s infinite}
.live-sos-badge{display:inline-flex;align-items:center;gap:7px;padding:6px 11px;border-radius:99px;background:#fef2f2;border:1px solid #fecaca;color:var(--danger-dark);font-size:12px;font-weight:700;white-space:nowrap;text-decoration:none}
.live-sos-badge .dot{width:7px;height:7px;border-radius:50%;background:var(--danger);animation:blink 1.2s infinite}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.45}}
.duty-toggle{display:flex;align-items:center;gap:8px;padding:4px 12px 4px 5px;border-radius:99px;background:var(--surface-2);border:1px solid var(--border);cursor:pointer;white-space:nowrap}
.duty-toggle input{display:none}
.duty-toggle .slider{width:32px;height:19px;border-radius:99px;background:#cbd5e1;position:relative;transition:.2s;flex-shrink:0}
.duty-toggle .slider::after{content:'';position:absolute;top:2px;left:2px;width:15px;height:15px;border-radius:50%;background:#fff;transition:.2s;box-shadow:0 1px 2px rgba(0,0,0,.2)}
.duty-toggle input:checked + .slider{background:var(--success)}
.duty-toggle input:checked + .slider::after{transform:translateX(13px)}
.duty-toggle .label{font-size:12px;font-weight:600;color:var(--text)}
.icon-btn{width:36px;height:36px;border-radius:9px;background:var(--surface-2);border:1px solid var(--border);display:grid;place-items:center;cursor:pointer;color:var(--text-muted);transition:all .16s;position:relative;flex-shrink:0}
.icon-btn:hover{background:#fff;color:var(--text);border-color:var(--border-strong)}
.icon-btn svg{width:17px;height:17px}
/* 🆕 Dropdown Styles */
.dropdown-wrap{position:relative}
.dropdown-menu{position:absolute;right:0;top:calc(100% + 8px);background:#fff;border:1px solid var(--border);border-radius:12px;box-shadow:0 10px 30px rgba(15,23,42,.15);z-index:1000;overflow:hidden;display:none}
.dropdown-menu.show{display:block}
.notif-menu{width:320px;max-width:calc(100vw - 40px)}
.notif-menu-head{padding:13px 16px;border-bottom:1px solid var(--border);font-weight:800;font-size:13px;color:var(--text);display:flex;justify-content:space-between;align-items:center}
.notif-list{max-height:300px;overflow-y:auto}
.notif-item{display:block;padding:12px 16px;border-bottom:1px solid var(--border);text-decoration:none;color:var(--text);transition:background .12s}
.notif-item:hover{background:var(--surface-2)}
.notif-item .nt-title{font-size:12.5px;font-weight:600;color:var(--text);line-height:1.4}
.notif-item .nt-time{font-size:11px;color:var(--text-muted);margin-top:3px}
.notif-empty{padding:28px 16px;text-align:center;color:var(--text-muted);font-size:12px}
.notif-footer{display:block;padding:11px 16px;text-align:center;background:var(--surface-2);font-size:12px;font-weight:700;color:var(--primary);text-decoration:none;border-top:1px solid var(--border)}
.notif-footer:hover{background:var(--surface-3)}
/* 🆕 Mobile: Fix dropdown off-screen issue */
@media (max-width: 768px) {
    .notif-menu {
        position: fixed !important;
        top: 68px !important;
        left: 12px !important;
        right: 12px !important;
        width: auto !important;
        max-width: none !important;
        transform: none !important;
    }
    .profile-menu {
        position: fixed !important;
        top: 68px !important;
        right: 12px !important;
        left: auto !important;
        width: 260px !important;
        max-width: calc(100vw - 24px) !important;
        transform: none !important;
    }
}
.profile-menu{width:230px}
.profile-menu-head{padding:14px 16px;border-bottom:1px solid var(--border)}
.profile-menu-head .pm-name{font-size:13px;font-weight:800;color:var(--text)}
.profile-menu-head .pm-email{font-size:11.5px;color:var(--text-muted);margin-top:2px;word-break:break-all}
.profile-menu-item{display:flex;align-items:center;gap:10px;padding:11px 16px;font-size:12.5px;color:var(--text);text-decoration:none;border-bottom:1px solid var(--border);transition:background .12s;background:none;border-left:none;border-right:none;border-top:none;width:100%;text-align:left;cursor:pointer;font-family:inherit}
.profile-menu-item:hover{background:var(--surface-2)}
.profile-menu-item.danger{color:var(--danger);border-bottom:none}
.profile-menu-item svg{width:15px;height:15px;color:var(--text-muted);flex-shrink:0}
.profile-menu-item.danger svg{color:var(--danger)}
/* 🆕 Logout button with text */
.logout-btn{display:inline-flex;align-items:center;gap:7px;padding:7px 12px;border-radius:9px;background:var(--surface-2);border:1px solid var(--border);color:var(--text-muted);font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;transition:all .16s}
.logout-btn:hover{background:#fff;color:var(--danger);border-color:var(--danger)}
.logout-btn svg{width:15px;height:15px}
/* 🆕 Notification count badge (bell icon) */
.notif-count{position:absolute;top:2px;right:2px;min-width:17px;height:17px;padding:0 4px;border-radius:99px;background:var(--danger);color:#fff;font-size:9.5px;font-weight:800;display:flex;align-items:center;justify-content:center;border:2px solid var(--surface-2);line-height:1;animation:blink 1.4s infinite}
.profile-chip{display:flex;align-items:center;gap:9px;padding:4px 9px 4px 4px;border-radius:10px;border:1px solid var(--border);background:var(--surface-2);cursor:pointer;transition:background .16s;text-decoration:none;color:inherit}
.profile-chip:hover{background:#fff}
.avatar{width:32px;height:32px;border-radius:8px;flex-shrink:0;background:linear-gradient(135deg,#0d9488,#2dd4bf);display:grid;place-items:center;color:#fff;font-weight:700;font-size:12px}
.profile-info{display:flex;flex-direction:column;line-height:1.15}
.profile-info .name{font-size:12px;font-weight:700;color:var(--text)}
.profile-info .role{font-size:10px;color:var(--text-muted)}
.profile-chip > svg{width:14px;height:14px;color:var(--text-light)}

.content{padding:20px 26px 60px;flex:1}

/* Shared card / stat styles (from dashboard design) */
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:16px}
.stat-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:16px;display:flex;gap:12px;align-items:flex-start;position:relative;overflow:hidden;box-shadow:var(--shadow-xs);transition:transform .18s,box-shadow .18s}
.stat-card:hover{transform:translateY(-1px);box-shadow:var(--shadow-sm)}
.stat-card::before{content:'';position:absolute;left:0;top:0;bottom:0;width:3px;background:var(--accent-c,var(--primary))}
.stat-icon{width:40px;height:40px;border-radius:11px;display:grid;place-items:center;flex-shrink:0}
.stat-icon svg{width:19px;height:19px}
.stat-body{flex:1;min-width:0}
.stat-label{font-size:11px;font-weight:600;color:var(--text-muted);display:block}
.stat-value{font-size:22px;font-weight:800;letter-spacing:-.02em;margin-top:2px;display:flex;align-items:baseline;gap:7px;line-height:1.15}
.stat-sub{font-size:11px;color:var(--text-muted);margin-top:4px;display:block;line-height:1.35}
.stat-trend{font-size:10px;font-weight:700;padding:2px 6px;border-radius:99px}
.stat-trend.up{background:var(--success-light);color:var(--success-dark)}
.stat-trend.live{background:var(--danger-light);color:var(--danger-dark);animation:blink 1.6s infinite}
.stat-trend.target{background:var(--info-light);color:var(--info-dark)}
.stat-danger{--accent-c:var(--danger)}.stat-danger .stat-icon{background:var(--danger-light);color:var(--danger)}
.stat-teal{--accent-c:var(--primary)}.stat-teal .stat-icon{background:var(--primary-light);color:var(--primary-dark)}
.stat-amber{--accent-c:var(--warning)}.stat-amber .stat-icon{background:var(--warning-light);color:var(--warning-dark)}
.stat-blue{--accent-c:var(--info)}.stat-blue .stat-icon{background:var(--info-light);color:var(--info-dark)}
.stat-green{--accent-c:var(--success)}.stat-green .stat-icon{background:var(--success-light);color:var(--success-dark)}
.stat-purple{--accent-c:var(--purple)}.stat-purple .stat-icon{background:var(--purple-light);color:var(--purple-dark)}

.card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow-xs);overflow:hidden;margin-bottom:16px}
.card-header{display:flex;justify-content:space-between;align-items:center;gap:10px;padding:14px 16px;border-bottom:1px solid var(--border);flex-wrap:wrap}
.card-title{display:flex;align-items:center;gap:8px;min-width:0}
.card-title svg{width:16px;height:16px;color:var(--primary);flex-shrink:0}
.card-title h2{font-size:14px;font-weight:700;letter-spacing:-.01em;white-space:nowrap}
.card-body{padding:16px}
.content-grid{display:grid;grid-template-columns:minmax(0,1.55fr) minmax(0,1fr);gap:16px}
.col-main,.col-side{display:flex;flex-direction:column;gap:16px;min-width:0}
.badge{display:inline-flex;align-items:center;gap:5px;padding:3px 8px;border-radius:99px;font-size:10.5px;font-weight:700;letter-spacing:.02em;white-space:nowrap}
.badge-critical{background:var(--danger-light);color:var(--danger-dark)}
.badge-high{background:#ffedd5;color:#c2410c}
.badge-medium{background:var(--warning-light);color:var(--warning-dark)}
.badge-low{background:var(--info-light);color:var(--info-dark)}
.badge-teal{background:var(--primary-light);color:var(--primary-dark)}
.badge-green{background:var(--success-light);color:var(--success-dark)}
.badge-blue{background:var(--info-light);color:var(--info-dark)}
.badge-gray{background:var(--surface-3);color:var(--text-muted);border:1px solid var(--border)}
.badge .pulse-dot{width:7px;height:7px;border-radius:50%;background:currentColor;animation:blink 1.6s infinite}
.pill{font-size:10px;font-weight:700;padding:2px 7px;border-radius:99px;white-space:nowrap;display:inline-flex;align-items:center;gap:4px}
.pill-danger{background:var(--danger-light);color:var(--danger-dark)}
.pill-teal{background:var(--primary-light);color:var(--primary-dark)}
.pill-amber{background:var(--warning-light);color:var(--warning-dark)}
.pill-green{background:var(--success-light);color:var(--success-dark)}
.pill-blue{background:var(--info-light);color:var(--info-dark)}
.pill-gray{background:var(--surface-3);color:var(--text-muted);border:1px solid var(--border)}

.btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:8px 14px;border-radius:9px;font-size:12px;font-weight:600;cursor:pointer;border:1px solid transparent;transition:all .16s;white-space:nowrap;line-height:1.2;text-decoration:none}
.btn svg{width:14px;height:14px}
.btn-primary{background:var(--primary);color:#fff;box-shadow:0 1px 2px rgba(13,148,136,.35)}
.btn-primary:hover{background:var(--primary-dark);transform:translateY(-1px)}
.btn-danger{background:var(--danger);color:#fff}.btn-danger:hover{background:var(--danger-dark)}
.btn-ghost{background:#fff;border-color:var(--border);color:var(--text)}.btn-ghost:hover{background:var(--surface-2);border-color:var(--border-strong)}
.btn-warn{background:var(--warning);color:#fff}.btn-warn:hover{background:var(--warning-dark)}
.btn-sm{padding:6px 11px;font-size:11px;border-radius:8px}.btn-sm svg{width:12px;height:12px}
.btn-link{background:none;border:none;color:var(--primary);font-weight:600;font-size:12px;cursor:pointer;display:inline-flex;align-items:center;gap:4px;padding:0;text-decoration:none}
.btn-link svg{width:13px;height:13px}.btn-link:hover{color:var(--primary-dark)}

/* Toast */
#toastContainer{position:fixed;bottom:22px;right:22px;z-index:999;display:flex;flex-direction:column;gap:9px}
.toast{display:flex;align-items:center;gap:9px;padding:11px 14px;border-radius:10px;background:#0f172a;color:#fff;font-size:12px;font-weight:500;box-shadow:var(--shadow-lg);border:1px solid rgba(255,255,255,.08);animation:toastIn .25s ease;max-width:360px}
.toast svg{width:15px;height:15px;flex-shrink:0}
.toast-success svg{color:#2dd4bf}
.toast-warning svg{color:#fbbf24}
.toast-danger svg{color:#f87171}
@keyframes toastIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:none}}

/* Modal */
.modal-overlay{position:fixed;inset:0;background:rgba(15,23,42,.55);backdrop-filter:blur(3px);z-index:200;display:none;align-items:center;justify-content:center;padding:20px;overflow-y:auto}
.modal-overlay.show{display:flex}
.modal{background:#fff;border-radius:14px;max-width:500px;width:100%;padding:22px;box-shadow:var(--shadow-lg);animation:modalIn .22s ease;max-height:90vh;overflow-y:auto}
.modal.wide{max-width:640px}
@keyframes modalIn{from{opacity:0;transform:scale(.95)}to{opacity:1;transform:none}}
.modal-head{display:flex;align-items:flex-start;gap:12px;margin-bottom:14px;padding-bottom:14px;border-bottom:1px solid var(--border)}
.modal-icon{width:42px;height:42px;border-radius:11px;display:grid;place-items:center;flex-shrink:0}
.modal-icon.danger{background:var(--danger-light);color:var(--danger)}
.modal-icon.success{background:var(--success-light);color:var(--success-dark)}
.modal-icon.warn{background:var(--warning-light);color:var(--warning-dark)}
.modal-icon.info{background:var(--info-light);color:var(--info-dark)}
.modal-icon svg{width:20px;height:20px}
.modal-title{font-size:15px;font-weight:800;margin-bottom:3px;letter-spacing:-.01em}
.modal-desc{font-size:12px;color:var(--text-muted);line-height:1.5}
.modal-actions{display:flex;gap:8px;justify-content:flex-end;margin-top:18px;padding-top:14px;border-top:1px solid var(--border)}
.sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(15,23,42,.5);z-index:55;opacity:0;transition:opacity .2s}
.sidebar-overlay.show{display:block;opacity:1}
/* Table Styles (from HTML design) */
.table-wrap{overflow-x:auto}
.table{width:100%;border-collapse:collapse;font-size:12.5px}
.table th{text-align:left;padding:10px 16px;font-size:10px;text-transform:uppercase;letter-spacing:.06em;color:var(--text-light);font-weight:700;border-bottom:1px solid var(--border);background:var(--surface-2);white-space:nowrap}
.table td{padding:12px 16px;border-bottom:1px solid var(--border);vertical-align:middle}
.table tbody tr:last-child td{border-bottom:none}
.table tbody tr{transition:background .12s}
.table tbody tr:hover{background:var(--surface-2)}
.table .cell-strong{font-weight:700;color:var(--text);white-space:nowrap}
.table .cell-mono{font-family:'JetBrains Mono',monospace;font-size:11.5px}
.table .cell-muted{color:var(--text-muted);font-size:11.5px}
/* Mission Card & Dual Timeline */
.dual-tl{margin:14px 0 12px;display:flex;flex-direction:column;gap:12px}
.tl-section{background:var(--surface-2);border:1px solid var(--border);border-radius:10px;padding:10px 12px}
.tl-label{font-size:9.5px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-light);margin-bottom:8px;display:flex;align-items:center;gap:6px}
.tl-label-badge{font-size:9px;padding:1px 6px;border-radius:99px;font-weight:700}
.tl-label-badge.sos{background:var(--danger-light);color:var(--danger-dark)}
.tl-label-badge.rescue{background:var(--primary-light);color:var(--primary-dark)}
.tl-steps{display:flex;justify-content:space-between;gap:4px;position:relative;padding-top:14px}
.tl-steps::before{content:'';position:absolute;top:6px;left:5%;right:5%;height:2px;background:var(--border);border-radius:2px;z-index:0}
.tl-step{position:relative;z-index:2;display:flex;flex-direction:column;align-items:center;gap:4px;flex:1;min-width:0}
.tl-dot{width:11px;height:11px;border-radius:50%;background:#fff;border:2px solid var(--border-strong);transition:.2s}
.tl-step.done .tl-dot{background:var(--primary);border-color:var(--primary)}
.tl-step.current .tl-dot{background:#fff;border-color:var(--primary);box-shadow:0 0 0 3px rgba(13,148,136,.18);animation:blink 1.8s infinite}
.tl-step span{font-size:9.5px;font-weight:600;color:var(--text-light);text-align:center;line-height:1.15;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%}
.tl-step.done span,.tl-step.current span{color:var(--text)}
.mission-card{border:1px solid var(--border);border-radius:12px;padding:16px;margin-bottom:16px;background:var(--surface)}
.mission-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;gap:10px;flex-wrap:wrap}
.mission-id{font-family:'JetBrains Mono',monospace;font-size:11px;font-weight:700;color:var(--primary-dark);background:var(--primary-light);padding:3px 8px;border-radius:5px}
/* Person Card & Grid (for Staff) */
.grid-3{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
.grid-2{display:grid;grid-template-columns:repeat(2,1fr);gap:14px}
.person-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:16px;box-shadow:var(--shadow-xs);transition:transform .18s,box-shadow .18s}
.person-card:hover{transform:translateY(-2px);box-shadow:var(--shadow-sm)}
.person-top{display:flex;align-items:center;gap:10px;margin-bottom:12px}
.person-avatar{width:42px;height:42px;border-radius:10px;display:grid;place-items:center;font-weight:700;font-size:13px;color:#fff;flex-shrink:0}
.person-name{font-size:13.5px;font-weight:700;letter-spacing:-.01em}
.person-role{font-size:11px;color:var(--text-muted);margin-top:1px}
.person-meta{display:flex;flex-direction:column;gap:6px;font-size:11.5px;color:var(--text-muted);border-top:1px solid var(--border);padding-top:11px}
.person-meta span{display:flex;align-items:center;gap:6px}
.person-meta svg{width:12px;height:12px;color:var(--text-light);flex-shrink:0}

/* Chart Styles (for Reports) */
.chart-svg{width:100%;height:auto;display:block}
.chart-legend{display:flex;gap:14px;margin-top:8px;font-size:11px;color:var(--text-muted);flex-wrap:wrap}
.chart-legend span{display:inline-flex;align-items:center;gap:5px}
.chart-legend i{width:9px;height:9px;border-radius:2px;display:inline-block}

/* Responsive grid */
@media(max-width:1024px){.grid-3{grid-template-columns:repeat(2,1fr)}}
@media(max-width:720px){.grid-3,.grid-2{grid-template-columns:1fr}}

/* Form & Modal Styles */
.modal label{display:block;font-size:11.5px;font-weight:700;color:var(--text);margin-bottom:5px;margin-top:12px}
.modal label .req{color:var(--danger);margin-left:2px}
.modal select,.modal input[type=text],.modal input[type=number],.modal input[type=tel],.modal input[type=email],.modal input[type=url],.modal textarea{width:100%;border:1px solid var(--border);border-radius:8px;padding:9px 11px;font-size:12.5px;font-family:inherit;outline:none;color:var(--text);background:#fff;transition:border-color .16s,box-shadow .16s}
.modal select:focus,.modal input:focus,.modal textarea:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(13,148,136,.1)}

/* File input (browsers default style is ugly) */
.modal input[type=file]{width:100%;padding:8px 11px;border:1px solid var(--border);border-radius:8px;font-size:12px;font-family:inherit;color:var(--text);background:#fff;cursor:pointer;transition:border-color .16s,box-shadow .16s}
.modal input[type=file]:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px rgba(13,148,136,.1)}
.modal input[type=file]::file-selector-button{padding:6px 12px;margin-right:10px;border:none;border-radius:6px;background:var(--primary-light);color:var(--primary-dark);font-weight:700;font-size:12px;font-family:inherit;cursor:pointer;transition:background .16s}
.modal input[type=file]::file-selector-button:hover{background:var(--primary);color:#fff}

/* Responsive */
@media(max-width:1240px){.content-grid{grid-template-columns:1fr}.stats-grid{grid-template-columns:repeat(2,1fr)}.topbar-center{display:none}}
@media(max-width:900px){.sidebar{position:fixed;left:-290px;transition:left .25s ease;z-index:70}.sidebar.open{left:0}.menu-btn{display:grid}.topbar{padding:11px 16px;gap:10px}.content{padding:16px}.profile-info{display:none}.network-chip{display:none}.page-title h1{font-size:15.5px;white-space:normal}.page-title p{display:none}}
@media(max-width:720px){.stats-grid{grid-template-columns:1fr 1fr;gap:10px}.stat-card{padding:12px}.stat-value{font-size:18px}.duty-toggle .label{display:none}.duty-toggle{padding:4px 6px 4px 4px}}
@media(max-width:520px){.stats-grid{grid-template-columns:1fr}.topbar-right{gap:6px}.icon-btn{width:32px;height:32px}}
</style>
@stack('rescue-head')
</head>
<body>
<div class="app">

<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="brand-mark">TN</div>
    <div style="min-width:0">
      <span class="brand-name">TravelAI Nepal</span>
      <span class="brand-sub">Rescue Provider</span>
    </div>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-group">
      <span class="nav-label">Operations</span>
      <a class="nav-item {{ request()->routeIs('provider.rescue.dashboard') ? 'active' : '' }}" href="{{ route('provider.rescue.dashboard') }}"><i data-lucide="layout-dashboard"></i> Overview</a>
      <a class="nav-item {{ request()->routeIs('provider.rescue.sos*') ? 'active' : '' }}" href="{{ route('provider.rescue.sos') }}"><i data-lucide="siren"></i> Live SOS Queue @if($liveSosCount > 0)<span class="nav-badge">{{ $liveSosCount }}</span>@endif</a>
      <a class="nav-item {{ request()->routeIs('provider.rescue.missions*') ? 'active' : '' }}" href="{{ route('provider.rescue.missions') }}"><i data-lucide="ambulance"></i> Active Missions @if($activeMissionsCount > 0)<span class="nav-badge teal">{{ $activeMissionsCount }}</span>@endif</a>
      <a class="nav-item {{ request()->routeIs('provider.rescue.gps') ? 'active' : '' }}" href="{{ route('provider.rescue.gps') }}"><i data-lucide="map"></i> GPS Tracking</a>
      <a class="nav-item {{ request()->routeIs('provider.rescue.audit') ? 'active' : '' }}" href="{{ route('provider.rescue.audit') }}"><i data-lucide="scroll-text"></i> Audit Log</a>
    </div>
    <div class="nav-group">
      <span class="nav-label">Resources</span>
      <a class="nav-item {{ request()->routeIs('provider.rescue.fleet*') ? 'active' : '' }}" href="{{ route('provider.rescue.fleet') }}"><i data-lucide="plane"></i> Fleet Management</a>
      <a class="nav-item {{ request()->routeIs('provider.rescue.staff*') ? 'active' : '' }}" href="{{ route('provider.rescue.staff') }}"><i data-lucide="users"></i> Staff &amp; Crew</a>
      <a class="nav-item {{ request()->routeIs('provider.rescue.coverage') ? 'active' : '' }}" href="{{ route('provider.rescue.coverage') }}"><i data-lucide="map-pin"></i> Coverage Areas</a>
      <a class="nav-item {{ request()->routeIs('provider.rescue.documents') ? 'active' : '' }}" href="{{ route('provider.rescue.documents') }}"><i data-lucide="file-text"></i> Documents &amp; Licenses</a>
    </div>
    <div class="nav-group">
      <span class="nav-label">Business</span>
      <a class="nav-item {{ request()->routeIs('provider.ads*') ? 'active' : '' }}" href="{{ route('provider.ads.index') }}"><i data-lucide="megaphone"></i> My Ads</a>
      <a class="nav-item {{ request()->routeIs('provider.rescue.pricing') ? 'active' : '' }}" href="{{ route('provider.rescue.pricing') }}"><i data-lucide="wallet"></i> Pricing Config</a>
      <a class="nav-item {{ request()->routeIs('provider.rescue.insurance') ? 'active' : '' }}" href="{{ route('provider.rescue.insurance') }}"><i data-lucide="shield-check"></i> Insurance Partners</a>
      <a class="nav-item {{ request()->routeIs('provider.rescue.revenue') ? 'active' : '' }}" href="{{ route('provider.rescue.revenue') }}"><i data-lucide="banknote"></i> Mission Revenue</a>
      <a class="nav-item {{ request()->routeIs('provider.rescue.reports') ? 'active' : '' }}" href="{{ route('provider.rescue.reports') }}"><i data-lucide="trending-up"></i> Reports &amp; Analytics</a>
    </div>
    <div class="nav-group">
      <span class="nav-label">Account</span>
      <a class="nav-item {{ request()->routeIs('provider.profile') ? 'active' : '' }}" href="{{ route('provider.profile') }}"><i data-lucide="user"></i> Provider Profile</a>
      <a class="nav-item {{ request()->routeIs('provider.verification.*') ? 'active' : '' }}" href="{{ route('provider.verification.index') }}"><i data-lucide="badge-check"></i> Verification</a>
      <a class="nav-item {{ request()->routeIs('provider.subscriptions.*') ? 'active' : '' }}" href="{{ route('provider.subscriptions.index') }}"><i data-lucide="credit-card"></i> Subscription</a>
      <a class="nav-item {{ request()->routeIs('provider.invoices.*') ? 'active' : '' }}" href="{{ route('provider.invoices.index') }}"><i data-lucide="file-text"></i> Invoices</a>
      <a class="nav-item {{ request()->routeIs('provider.payments.*') ? 'active' : '' }}" href="{{ route('provider.payments.index') }}"><i data-lucide="banknote"></i> Payments</a>
      <a class="nav-item {{ request()->routeIs('provider.settings.payment-methods.*') ? 'active' : '' }}" href="{{ route('provider.settings.payment-methods.index') }}"><i data-lucide="wallet"></i> Payment Methods</a>
      <a class="nav-item {{ request()->routeIs('provider.rescue.security') ? 'active' : '' }}" href="{{ route('provider.rescue.security') }}"><i data-lucide="shield"></i> Security &amp; Permissions</a>
    </div>
  </nav>
  <div class="sidebar-footer">
    <div class="plan-card">
      <div class="pc-title"><i data-lucide="zap"></i> Rescue Plan</div>
      <div class="pc-sub">{{ $actualFleetCount }} vehicles · {{ $isOnDuty ? 'On Duty' : 'Off Duty' }}</div>
    </div>
    <div class="sidebar-net"><span class="dot"></span><span>{{ $isOnDuty ? 'Online' : 'Offline' }} · synced just now</span></div>
  </div>
</aside>
<div class="sidebar-overlay"></div>

<div class="main">
<header class="topbar">
  <div class="topbar-left">
    <button class="icon-btn menu-btn" id="menuBtn"><i data-lucide="menu"></i></button>
    <div class="page-title">
      <h1>@yield('page-title', 'Operations Overview')</h1>
      <p>@yield('page-sub', 'Target SLA · dispatch under 5 min · rescue operations live')</p>
    </div>
  </div>
  <div class="topbar-center">
    <div class="search-box">
      <i data-lucide="search"></i>
      <input type="text" placeholder="Search missions, travelers, fleet..." />
    </div>
  </div>
  <div class="topbar-right">
    <div class="network-chip"><span class="dot"></span> Online</div>
    @if($liveSosCount > 0)
    <a href="{{ route('provider.rescue.sos') }}" class="live-sos-badge"><span class="dot"></span> {{ $liveSosCount }} Live SOS</a>
    @endif
    <form method="POST" action="{{ route('provider.rescue.toggle-availability') }}" style="display:inline">
      @csrf
      <label class="duty-toggle">
        <input type="checkbox" id="dutyToggle" {{ $isOnDuty ? 'checked' : '' }} onchange="this.form.submit()" />
        <span class="slider"></span>
        <span class="label" id="dutyLabel">{{ $isOnDuty ? 'On Duty' : 'Off Duty' }}</span>
      </label>
    </form>

    {{-- 🆕 Bell Icon with Dropdown --}}
    <div class="dropdown-wrap">
      <button type="button" class="icon-btn" id="notifBtn" title="Notifications" onclick="toggleNotif(event)">
        <i data-lucide="bell"></i>
        @if($unreadNotifs > 0)
          <span class="notif-count">{{ $unreadNotifs > 9 ? '9+' : $unreadNotifs }}</span>
        @endif
      </button>

      <div class="dropdown-menu notif-menu" id="notifDropdown">
        <div class="notif-menu-head">
          <span>Notifications</span>
          @if($unreadNotifs > 0)
            <span style="background:var(--danger);color:#fff;font-size:10px;padding:2px 7px;border-radius:99px">{{ $unreadNotifs }}</span>
          @endif
        </div>
        <div class="notif-list">
          @forelse($currentUser ? $currentUser->unreadNotifications()->take(5)->get() : collect() as $notif)
            <a href="{{ $notif->data['url'] ?? '#' }}" class="notif-item">
              <div class="nt-title">{{ $notif->data['message'] ?? 'New notification' }}</div>
              <div class="nt-time">{{ $notif->created_at->diffForHumans() }}</div>
            </a>
          @empty
            <div class="notif-empty">No new notifications</div>
          @endforelse
        </div>
        <a href="{{ route('provider.rescue.sos') }}" class="notif-footer">View All SOS Alerts</a>
      </div>
    </div>

    {{-- 🆕 Profile Chip with Dropdown --}}
    <div class="dropdown-wrap">
      <div class="profile-chip" id="profileBtn" onclick="toggleProfile(event)" style="cursor:pointer">
        <div class="avatar">{{ $initials }}</div>
        <div class="profile-info">
          <span class="name">{{ $currentUser?->name ?? 'User' }}</span>
          <span class="role">Rescue Operator</span>
        </div>
        <i data-lucide="chevron-down" style="width:14px;height:14px;color:var(--text-light)"></i>
      </div>

      <div class="dropdown-menu profile-menu" id="profileDropdown">
        <div class="profile-menu-head">
          <div class="pm-name">{{ $currentUser?->name ?? 'User' }}</div>
          <div class="pm-email">{{ $currentUser?->email ?? '' }}</div>
        </div>
        <a href="{{ route('provider.profile') }}" class="profile-menu-item">
          <i data-lucide="user"></i> My Profile
        </a>
        <a href="{{ route('provider.subscriptions.index') }}" class="profile-menu-item">
          <i data-lucide="credit-card"></i> Subscription
        </a>
        <a href="{{ route('provider.verification.index') }}" class="profile-menu-item">
          <i data-lucide="badge-check"></i> Verification
        </a>
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
          @csrf
          <button type="submit" class="profile-menu-item danger">
            <i data-lucide="log-out"></i> Logout
          </button>
        </form>
      </div>
    </div>
  </div>
</header>

<main class="content">
@if(session('success'))
<div style="background:var(--success-light);border:1px solid var(--success);color:var(--success-dark);padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px">
  <i data-lucide="check-circle"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div style="background:var(--danger-light);border:1px solid var(--danger);color:var(--danger-dark);padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px">
  <i data-lucide="alert-circle"></i> {{ session('error') }}
</div>
@endif

@yield('content')
</main>
</div>
</div>

<div id="toastContainer"></div>

<script src="https://cdn.jsdelivr.net/npm/lucide@latest/dist/umd/lucide.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  if (window.lucide) lucide.createIcons();
  const sidebar = document.querySelector('.sidebar');
  const overlay = document.querySelector('.sidebar-overlay');
  const menuBtn = document.getElementById('menuBtn');
  if (menuBtn) {
    menuBtn.addEventListener('click', () => { sidebar.classList.toggle('open'); overlay.classList.toggle('show'); });
    overlay.addEventListener('click', () => { sidebar.classList.remove('open'); overlay.classList.remove('show'); });
  }
  window.toast = (msg, type='success') => {
    const c = document.getElementById('toastContainer');
    const el = document.createElement('div');
    const icons = { success:'check', warning:'alert-triangle', danger:'siren', info:'bell' };
    el.className = `toast toast-${type}`;
    el.innerHTML = `<i data-lucide="${icons[type]||'check'}"></i><span>${msg}</span>`;
    c.appendChild(el);
    if (window.lucide) lucide.createIcons();
    setTimeout(() => el.remove(), 3400);
  };

  // 🆕 Close dropdowns when clicking outside
  document.addEventListener('click', function(e) {
    const notifWrap = document.getElementById('notifBtn')?.parentElement;
    const profileWrap = document.getElementById('profileBtn')?.parentElement;
    const notifMenu = document.getElementById('notifDropdown');
    const profileMenu = document.getElementById('profileDropdown');
    if (notifMenu && notifWrap && !notifWrap.contains(e.target)) notifMenu.classList.remove('show');
    if (profileMenu && profileWrap && !profileWrap.contains(e.target)) profileMenu.classList.remove('show');
  });
});

// 🆕 Toggle functions
function toggleNotif(e) {
  e.stopPropagation();
  document.getElementById('notifDropdown')?.classList.toggle('show');
  document.getElementById('profileDropdown')?.classList.remove('show');
}

function toggleProfile(e) {
  e.stopPropagation();
  document.getElementById('profileDropdown')?.classList.toggle('show');
  document.getElementById('notifDropdown')?.classList.remove('show');
}
</script>
@stack('rescue-scripts')
</body>
</html>