@extends('layouts.provider-individual')
@section('title', __('messages.my_bookings'))
@section('page-title', __('messages.my_bookings'))
@section('page-subtitle', __('messages.manage_assignments_subtitle'))

@push('ind-head')
<style>
  .card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:20px;}
  .tbl-wrap{overflow-x:auto;margin-top:10px}
  .tbl{width:100%;border-collapse:collapse;}
  .tbl th{text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.8px;color:#9ca3af;font-weight:800;padding:0 15px 15px;border-bottom:1px solid var(--line);}
  .tbl td{padding:15px;font-size:13px;font-weight:600;border-bottom:1px solid #f3f5fb;vertical-align:middle;}
  .tbl tbody tr:hover{background:#fafbff}
  .pill{font-size:11px;font-weight:800;padding:5px 10px;border-radius:20px;display:inline-block;}
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-head" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
        <div><h3 style="font-size:16px; font-weight:800;">{{ __('messages.my_bookings') }}</h3></div>
        <div style="display:flex; gap:8px;">
            <button type="button" onclick="openAssignmentModal()" style="background:var(--emerald); color:#fff; padding:8px 15px; border-radius:8px; border:none; cursor:pointer; font-family:inherit; font-size:12px; font-weight:700;">+ {{ __('messages.add_assignment') }}</button>
            <a href="{{ route('provider.individual.dashboard') }}" class="btn-primary" style="background:var(--card); color:var(--text); border:1px solid var(--line); padding:8px 15px; border-radius:8px; text-decoration:none; font-size:12px; font-weight:700;">{{ __('messages.back_to_dashboard') }}</a>
        </div>
    </div>
    <div class="tbl-wrap">
        <table class="tbl">
            <thead>
                <tr>
                    <th>{{ __('messages.assignment_title') }}</th>
                    <th>{{ __('messages.client_name') }}</th>
                    <th>{{ __('messages.service_type') }}</th>
                    <th>{{ __('messages.start_date') }}</th>
                    <th>{{ __('messages.amount') }}</th>
                    <th>{{ __('messages.status') }}</th>
                    <th style="text-align:right;">{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $b)
                <tr>
                    <td><strong>{{ $b->title }}</strong></td>
                    <td>{{ $b->client_name }}</td>
                    <td><span class="pill" style="background:var(--emerald-soft); color:var(--emerald-d);">{{ __('messages.' . $b->service_type) ?? ucfirst($b->service_type) }}</span></td>
                    <td>{{ $b->start_date->format('M d, Y') }}</td>
                    <td style="font-weight:800">Rs {{ number_format($b->amount) }}</td>
                    <td><span class="pill" style="background:var(--amber-soft); color:#b45309;">{{ __('messages.' . $b->status) ?? ucfirst($b->status) }}</span></td>
                    <td style="text-align:right;white-space:nowrap;">
                        <a href="{{ route('provider.individual.bookings.show', $b) }}" style="padding:6px 10px;border-radius:7px;border:none;background:var(--green-soft);color:#047857;font-size:11px;font-weight:800;text-decoration:none;display:inline-block;margin-right:4px;"><i class="fas fa-eye"></i> {{ __('messages.view') }}</a>
                        <button type="button" onclick="openEditAssignmentModal({{ $b->id }}, '{{ addslashes($b->title) }}', '{{ addslashes($b->client_name) }}', '{{ $b->service_type }}', '{{ $b->start_date->format("Y-m-d") }}', '{{ addslashes($b->duration) }}', {{ (float) $b->amount }}, '{{ $b->status }}')" style="padding:6px 10px;border-radius:7px;border:none;background:var(--blue-soft);color:#1d4ed8;font-size:11px;font-weight:800;cursor:pointer;font-family:inherit;margin-right:4px;"><i class="fas fa-edit"></i> {{ __('messages.edit') }}</button>
                        <form action="{{ route('provider.individual.assignments.destroy', $b) }}" method="POST" style="display:inline;margin:0;" onsubmit="return confirm('{{ __('messages.delete_assignment_confirm') }}')">
                            @csrf @method('DELETE')
                            <button type="submit" style="padding:6px 10px;border-radius:7px;border:none;background:var(--red-soft);color:var(--red);font-size:11px;font-weight:800;cursor:pointer;font-family:inherit;"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center; padding:20px; color:var(--muted);">{{ __('messages.no_bookings_yet') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Add Assignment Modal --}}
<div id="assignmentModal" style="display:none;position:fixed;inset:0;background:rgba(15,36,25,.6);z-index:9999;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(3px)">
    <div style="background:var(--card,#fff);border-radius:18px;width:100%;max-width:500px;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,.2);max-height:90vh;overflow-y:auto">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h3 style="font-size:18px;font-weight:800;color:var(--text);margin:0">{{ __('messages.new_assignment') }}</h3>
            <button type="button" onclick="closeAssignmentModal()" style="background:none;border:none;font-size:24px;cursor:pointer;color:var(--muted);line-height:1">&times;</button>
        </div>
        <form action="{{ route('provider.individual.assignments.store') }}" method="POST">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
                <div style="grid-column:span 2">
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.assignment_title') }}</label>
                    <input type="text" name="title" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.client_name') }}</label>
                    <input type="text" name="client_name" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.service_type') }}</label>
                    <select name="service_type" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                        <option value="guide">{{ __('messages.guide') }}</option>
                        <option value="porter">{{ __('messages.porter') }}</option>
                        <option value="photographer">{{ __('messages.photographer') }}</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.start_date') }}</label>
                    <input type="date" name="start_date" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.duration') }}</label>
                    <input type="text" name="duration" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="10 days">
                </div>
                <div style="grid-column:span 2">
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.amount') }} (Rs)</label>
                    <input type="number" name="amount" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                </div>
            </div>
            <button type="submit" style="width:100%;padding:12px;border:none;border-radius:11px;background:linear-gradient(135deg,var(--emerald,#10b981),var(--emerald-d,#059669));color:#fff;font-family:inherit;font-size:13px;font-weight:800;cursor:pointer;">{{ __('messages.create_assignment') }}</button>
        </form>
    </div>
</div>

{{-- Edit Assignment Modal --}}
<div id="editAssignmentModal" style="display:none;position:fixed;inset:0;background:rgba(15,36,25,.6);z-index:9999;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(3px)">
    <div style="background:var(--card,#fff);border-radius:18px;width:100%;max-width:500px;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,.2);max-height:90vh;overflow-y:auto">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h3 style="font-size:18px;font-weight:800;color:var(--text);margin:0">{{ __('messages.edit_assignment') }}</h3>
            <button type="button" onclick="closeEditAssignmentModal()" style="background:none;border:none;font-size:24px;cursor:pointer;color:var(--muted);line-height:1">&times;</button>
        </div>
        <form id="editAssignmentForm" method="POST">
            @csrf @method('PUT')
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
                <div style="grid-column:span 2">
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.assignment_title') }}</label>
                    <input type="text" name="title" id="ea_title" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.client_name') }}</label>
                    <input type="text" name="client_name" id="ea_client" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.service_type') }}</label>
                    <select name="service_type" id="ea_type" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                        <option value="guide">{{ __('messages.guide') }}</option>
                        <option value="porter">{{ __('messages.porter') }}</option>
                        <option value="photographer">{{ __('messages.photographer') }}</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.start_date') }}</label>
                    <input type="date" name="start_date" id="ea_start" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.duration') }}</label>
                    <input type="text" name="duration" id="ea_duration" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.amount') }} (Rs)</label>
                    <input type="number" name="amount" id="ea_amount" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.status') }}</label>
                    <select name="status" id="ea_status" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                        <option value="pending">{{ __('messages.pending') }}</option>
                        <option value="confirmed">{{ __('messages.confirmed') }}</option>
                        <option value="completed">{{ __('messages.completed') }}</option>
                    </select>
                </div>
            </div>
            <button type="submit" style="width:100%;padding:12px;border:none;border-radius:11px;background:linear-gradient(135deg,var(--emerald,#10b981),var(--emerald-d,#059669));color:#fff;font-family:inherit;font-size:13px;font-weight:800;cursor:pointer;">{{ __('messages.update_assignment') }}</button>
        </form>
    </div>
</div>

<script>
function openAssignmentModal() { document.getElementById('assignmentModal').style.display = 'flex'; }
function closeAssignmentModal() { document.getElementById('assignmentModal').style.display = 'none'; }
document.getElementById('assignmentModal').addEventListener('click', function(e) { if (e.target === this) closeAssignmentModal(); });

function openEditAssignmentModal(id, title, client, type, start, duration, amount, status) {
    document.getElementById('editAssignmentForm').action = '/provider/individual/assignments/' + id;
    document.getElementById('ea_title').value = title;
    document.getElementById('ea_client').value = client;
    document.getElementById('ea_type').value = type;
    document.getElementById('ea_start').value = start;
    document.getElementById('ea_duration').value = duration;
    document.getElementById('ea_amount').value = amount;
    document.getElementById('ea_status').value = status;
    document.getElementById('editAssignmentModal').style.display = 'flex';
}
function closeEditAssignmentModal() { document.getElementById('editAssignmentModal').style.display = 'none'; }
document.getElementById('editAssignmentModal').addEventListener('click', function(e) { if (e.target === this) closeEditAssignmentModal(); });
</script>

{{-- ═══ Individual AI ═══ --}}
<div class="card" style="background:var(--card);border:1px solid var(--line);border-radius:16px;padding:22px;margin-top:20px;">
  <h3 style="font-size:16px;font-weight:800;margin:0 0 12px;">🎯 {{ __('messages.individual_ai_matching_title') }}</h3>
  <p style="font-size:13px;color:var(--muted);margin:0 0 14px;">{{ __('messages.individual_ai_matching_sub') }}</p>
  <button id="individualAiMatchingBtn" onclick="loadGuideMatching()" style="background:var(--accent,#c9a227);color:#fff;border:0;border-radius:10px;padding:10px 20px;font-weight:800;cursor:pointer;font-size:13px;">{{ __('messages.individual_ai_run_matching') }}</button>
  <div id="individualAiMatchingOut" style="margin-top:14px;font-size:13px;"></div>
</div>

<div class="card" style="background:var(--card);border:1px solid var(--line);border-radius:16px;padding:22px;margin-top:20px;">
  <h3 style="font-size:16px;font-weight:800;margin:0 0 12px;">👤 {{ __('messages.individual_ai_profile_title') }}</h3>
  <p style="font-size:13px;color:var(--muted);margin:0 0 14px;">{{ __('messages.individual_ai_profile_sub') }}</p>
  <button id="individualAiProfileBtn" onclick="loadProfileBuilder()" style="background:var(--accent,#9b6dff);color:#fff;border:0;border-radius:10px;padding:10px 20px;font-weight:800;cursor:pointer;font-size:13px;">{{ __('messages.individual_ai_run_profile') }}</button>
  <div id="individualAiProfileOut" style="margin-top:14px;font-size:13px;"></div>
</div>

<script>
async function indAiPost(url){
  const r = await fetch(url, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}, body:'{}' });
  return await r.json();
}
function indAiErr(el,msg){ el.innerHTML='<div style="padding:10px;border-radius:8px;background:#fee;color:#900;font-weight:700;">'+msg+'</div>'; }
async function loadGuideMatching(){
  const btn=document.getElementById('individualAiMatchingBtn'), out=document.getElementById('individualAiMatchingOut');
  btn.disabled=true; btn.style.opacity=.6; out.innerHTML='⏳ {{ __("messages.individual_ai_loading") }}';
  try{
    const data=await indAiPost('{{ route("provider.individual.ai.matching") }}');
    if(data.error){ indAiErr(out, data.message||data.error); return; }
    let html='';
    if(data.data_warning) html+='<div style="background:#fef3c7;border:1px solid #f59e0b;padding:8px;border-radius:6px;margin-bottom:8px;font-size:11px;">⚠️ '+data.data_warning+'</div>';
    html+='<div style="font-weight:800;margin-bottom:8px;">'+(data.summary||'')+'</div>';
    html+='<div style="font-size:11px;margin:10px 0 6px;font-weight:700;">Guides:</div>';
    (data.guides||[]).forEach(g=>{
      var _avail = g.availability==='available';
      html+='<div style="display:flex;justify-content:space-between;padding:6px 10px;background:var(--bg,#f5f7fb);border-radius:8px;margin-bottom:6px;"><div><strong>Guide #'+g.user_id+'</strong><div style="font-size:10px;color:var(--muted)">'+g.skills+' · '+g.languages+'</div></div><div style="text-align:right;"><span style="background:'+(_avail?'#10b981':'#f59e0b')+';color:#fff;padding:2px 8px;border-radius:4px;font-size:10px;">'+g.availability+'</span><div style="font-size:10px;color:var(--muted)">'+g.experience_years+'y exp</div></div></div>';
    });
    if(data.matches && data.matches.length){
      html+='<div style="font-size:11px;margin:10px 0 6px;font-weight:700;">Upcoming Assignments:</div>'; if(data.assumptions&&data.assumptions.match_rule) html+='<div style="font-size:10px;color:var(--muted);margin-bottom:8px;">📋 '+data.assumptions.match_rule+' · '+data.assumptions.availability+'</div>';
      data.matches.forEach(m=>{
        var _sugg = m.suggested_guide_user_id || '—'; var _conf = (m.date_overlap_conflicts&&m.date_overlap_conflicts.length)?' <span style="color:#f59e0b;font-size:10px;">('+m.date_overlap_conflicts.length+' alternative guide(s) skipped (date conflict)</span>':''; html+='<div style="padding:6px 10px;background:#eff6ff;border:1px solid #3b82f6;border-radius:8px;margin-bottom:6px;font-size:12px;"><strong>'+m.client+'</strong> → Guide #'+_sugg+_conf+'<div style="font-size:10px;color:var(--muted)">'+m.booking+' · '+m.start_date+' to '+m.end_date+' ('+m.duration_days+'d, '+m.pax+' pax)</div></div>';
      });
    }
    out.innerHTML=html;
  }catch(e){ indAiErr(out,e.message); } finally{ btn.disabled=false; btn.style.opacity=1; }
}
async function loadProfileBuilder(){
  const btn=document.getElementById('individualAiProfileBtn'), out=document.getElementById('individualAiProfileOut');
  btn.disabled=true; btn.style.opacity=.6; out.innerHTML='⏳ {{ __("messages.individual_ai_loading") }}';
  try{
    const data=await indAiPost('{{ route("provider.individual.ai.profile") }}');
    if(data.error){ indAiErr(out, data.message||data.error); return; }
    let html='<div style="font-weight:800;margin-bottom:8px;">'+(data.summary||'')+'</div>';
    (data.profiles||[]).forEach(p=>{
      html+='<div style="padding:8px 10px;background:var(--bg,#f5f7fb);border-radius:8px;margin-bottom:6px;"><strong>'+p.name+'</strong><div style="font-size:12px;color:var(--muted);margin-top:4px;">'+(p.bio||'')+'</div></div>';
    });
    out.innerHTML=html;
  }catch(e){ indAiErr(out,e.message); } finally{ btn.disabled=false; btn.style.opacity=1; }
}
</script>
@endsection