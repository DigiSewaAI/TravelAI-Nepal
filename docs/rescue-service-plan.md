# Rescue Service — Complete Implementation Plan

**Created:** 2026-11-09
**Status:** Planning → Implementation
**Related:** SOS Full Upgrade (deployed), Rescue Provider Dashboard (design ready), Admin Command Center (design ready)

---

## 🎯 Vision

Nepal's first **AI-native emergency rescue coordination platform** — connecting travelers in distress with helicopter, medical, and ground rescue providers in real-time.

**Core value:**
- Traveler SOS → nearest rescue provider within 5 min SLA
- Real-time mission tracking
- Insurance auto-claim (future)
- Admin oversight for SLA breaches

---

## 📋 Scope

### In Scope (Phase 1-4)

| Phase | Component | Priority |
|---|---|---|
| **1** | Provider type + category + registration | 🔴 Critical |
| **2** | Rescue Provider Dashboard (from HTML design) | 🔴 Critical |
| **3** | Admin Command Center (from HTML design) | 🔴 Critical |
| **4** | SOS ↔ Rescue integration (dispatch) | 🔴 Critical |

### Out of Scope (Post-launch)

| Component | Reason |
|---|---|
| Real-time GPS WebSocket tracking | Complex, use polling first |
| Insurance auto-claim integration | Partnership needed |
| CAAN escalation API | Regulatory approval |
| National Operations Map (full) | Post-launch enhancement |
| Broadcast to all providers | Nice-to-have |

---

## 🗄️ Data Model

### Existing (used by Rescue)

- `providers` — base provider table
- `provider_types` — will add `rescue-service`
- `service_categories` — will add `rescue`
- `services` — rescue services (helicopter, medical, ground)
- `sos_alerts` — existing SOS (from Phase 5)

### New Tables (Phase 1)

#### `rescue_providers`

```php
- id (PK)
- provider_id (FK → providers, unique)
- aviation_license_number (nullable)
- medical_cert_number (nullable)
- insurance_policy_number (nullable)
- coverage_districts (JSON — ['Solukhumbu', 'Mustang'])
- coverage_radius_km (int, default 50)
- base_rate_npr (decimal 10,2)
- per_km_rate_npr (decimal 8,2)
- per_hour_rate_npr (decimal 8,2)
- night_surcharge_pct (int, default 30)
- high_altitude_surcharge_pct (int, default 50)
- max_altitude_ft (int)
- fleet_size (int)
- is_24_7 (boolean)
- is_available (boolean, real-time toggle)
- created_at, updated_at
rescue_missions
php
- id (PK)
- mission_number (string, unique, format: RSC-2026-XXXX)
- sos_alert_id (FK → sos_alerts)
- rescue_provider_id (FK → providers)
- traveler_id (FK → users)
- dispatch_type (enum: helicopter, ground_ambulance, medical_team, search_rescue)
- status (enum: dispatched, accepted, en_route, on_scene, extracting, completed, cancelled)
- dispatched_at (timestamp)
- accepted_at (timestamp, nullable)
- en_route_at (timestamp, nullable)
- on_scene_at (timestamp, nullable)
- extracted_at (timestamp, nullable)
- completed_at (timestamp, nullable)
- pickup_lat (decimal 10,8)
- pickup_lng (decimal 11,8)
- dropoff_lat (decimal 10,8, nullable)
- dropoff_lng (decimal 11,8, nullable)
- crew_notes (text, nullable)
- cost_estimate (decimal 12,2, nullable)
- final_cost (decimal 12,2, nullable)
- weather_conditions (JSON, nullable)
- created_at, updated_at
rescue_mission_updates
php
- id (PK)
- rescue_mission_id (FK → rescue_missions, cascade delete)
- user_id (FK → users, nullable — system = null)
- action (string, 30)
- notes (text, nullable)
- lat (decimal 10,8, nullable)
- lng (decimal 11,8, nullable)
- created_at (timestamp)
rescue_dispatch_locks
(For 60s lock mechanism in SOS queue)

php
- id (PK)
- sos_alert_id (FK → sos_alerts)
- provider_id (FK → providers)
- locked_at (timestamp)
- expires_at (timestamp)
- released_at (timestamp, nullable)
🔧 Phase 1 — Provider Type + Category + Registration
Tasks
Seed new provider type

provider_types: rescue-service (name: "Rescue Service")

Extend getProviderLayout() map in all controllers

New layout: layouts.provider-rescue

Seed new category

service_categories: rescue

Sub-categories (via services): helicopter, medical, ground, search

Registration flow

Extend register/provider page dropdown

On select rescue-service, show extra fields:

Aviation license number

Medical cert number

Insurance policy

Coverage districts (multi-select)

Max altitude capability

Fleet size

Create RescueProvider record alongside Provider

Migration + Models

rescue_providers table

RescueProvider model (belongsTo Provider, hasMany Missions)

Verification flow

Admin verification for rescue providers (upload CAAN license, etc.)

Extend VerificationDocument for rescue-specific docs

Time: ~1 day
Tests: Registration + provider type + category

🎨 Phase 2 — Rescue Provider Dashboard
Reference design: rescue-dashboard.html (TN.txt)

Sections
2.1 Dashboard Home (Operations Overview)
Stat cards:

Live SOS count (3)

Active Missions (1)

Avg Response Time (4m 12s)

Revenue (completed only)

Live SOS Queue (3 pending, priority sorted)

Active Mission card (1 in progress)

Weather & Flight Safety (DHM API integration)

GPS Map (Leaflet — last-known locations)

Audit Log (last 4 entries)

Fleet Status (4 vehicles)

2.2 SOS Queue Detail
SOS cards with:

Priority (critical/high/medium)

Traveler info (name, nationality, blood type, allergies)

Location (GPS + trailhead + distance)

Time (2 min ago, SLA 5m)

Accept (60s lock) / Decline / Details

Emergency contact

Agency info

Weather at location

2.3 Active Missions
Mission card with lifecycle:

Dispatched → Accepted → En Route → On Scene → Extracting → Completed

Button per status:

Accepted → "Mark En Route"

En Route → "Mark On Scene"

On Scene → "Mark Extracting"

Extracting → "Complete" (+ final cost + notes)

Live GPS map

Crew assignment

ETA

Cost estimate

2.4 Fleet Management
CRUD for:

Helicopters (9N-XXX, H125, AS350)

Ground ambulances

Medical teams

Status: Available / On Mission / Standby / No GPS

Last ping, GPS status

2.5 Staff
Pilots (license number, hours)

Medics (certification)

Coordinators

Reuse existing ProviderStaff model

2.6 Coverage Areas
Districts (multi-select)

Radius (km)

Active toggle (weather-based off)

2.7 Pricing Config
Base dispatch fee

Per km

Per hour

Night surcharge

High-altitude surcharge

2.8 Documents
CAAN license

Medical cert

Insurance policy

Pilot licenses

2.9 Reports
Response time (avg, SLA met %)

Missions completed

Success rate

Revenue

Time: ~2-3 days
Tests: Fleet CRUD + mission lifecycle + SOS queue

🛡️ Phase 3 — Admin Command Center
Reference design: rescue-dashboard-admin.html (TravelAI Nepal.txt)

Structure (Existing Admin मा integrate)
text
Existing TravelAI Admin
├── Dashboard (existing) + SOS summary widgets
├── Safety (existing)
│   ├── Incidents
│   ├── Sources
│   ├── Audit Log
│   └── 🚨 SOS Alerts (NEW)
│        ├── Rescue Command Center (main)
│        ├── All SOS
│        ├── Active Missions
│        ├── Rescue Providers
│        └── Dispatch History
├── Providers, Users, Bookings, etc. (existing)
Sections
3.1 Dashboard Widgets (existing dashboard मा थप)
Active SOS: 5

SLA Breaches: 2

Active Missions: 7

Providers Online: 8/12

3.2 Rescue Command Center (/admin/sos)
Escalation Queue:

2 active (SLA breach)

John Anderson — Phakding (12 min open, no ack)

Sarah Kessler — Namche (lock expired)

Actions: Manual Dispatch, Escalate to CAAN

All Active SOS (5 system-wide):

Filter tabs (All / Critical / High / Medium)

Full list:

SOS-2026-0142 (pending, unassigned, 12 min, Phakding)

SOS-2026-0146 (responding, 9N-AJH, Tengboche)

SOS-2026-0143 (acknowledged, re-broadcast, Namche)

SOS-2026-0145 (responding, 9N-BMR, Dole)

SOS-2026-0147 (assigned, 9N-AKL, Pheriche)

Actions: Dispatch / View

Providers Live Status:

8 online / 12 total

SkyRescue (2 heli, accept 98%, avg 3m 20s, Busy)

Mountain Heli Rescue (1 heli, accept 94%, avg 4m 05s, Online)

Air Dynasty (2 heli, accept 91%, avg 5m 12s, Online)

Simrik Air (1 heli, accept 89%, avg 5m 48s, Online)

Nepal Eco Rescue (2 ground, Offline)

Admin Actions Log:

14:32 — dispatch — Bikash A. manually dispatched SOS-2026-0142 → SkyRescue

14:30 — escalate — System auto-escalated 2 SOS (SLA breach)

14:28 — verify — Bikash A. verified Air Dynasty license

3.3 SOS Detail (/admin/sos/{id})
Full info

Audit log (sos_alert_actions from Phase 5)

Admin actions:

Manual dispatch (assign provider)

Force resolve

Mark false alarm

Escalate to CAAN (future)

3.4 Active Missions (/admin/missions)
All missions across providers

Filter by status

Live map (post-launch)

3.5 Rescue Providers (/admin/rescue-providers)
List all rescue providers

Verification status

Performance metrics

3.6 Dispatch History (/admin/dispatch-history)
All past dispatches

Filter by date, provider, outcome

Time: ~2-3 days
Tests: Escalation + manual dispatch + provider status

🔄 Phase 4 — SOS ↔ Rescue Integration
Flow
text
Traveler SOS
    ↓
Provider notified (existing)
    ↓
5 min — no ack → Admin escalation (existing)
    ↓
10 min — no respond → Rescue dispatch
    ↓
RescueDispatchService::dispatch(sos)
    ↓
Nearest rescue providers (GPS radius + coverage)
    ↓
Notify via SMS + push + dashboard
    ↓
First to accept (60s lock) → Mission created
    ↓
SOS status = responding
    ↓
Mission lifecycle: en_route → on_scene → extracting → completed
    ↓
SOS auto-resolved on mission completed
Components
RescueDispatchService

findNearestProviders(SosAlert $sos) — GPS + coverage districts

broadcast(SosAlert $sos) — notify all eligible

lock(SosAlert $sos, Provider $provider) — 60s lock

assign(SosAlert $sos, Provider $provider) — create mission

autoReleaseExpiredLocks() — command/cron

Idempotency

Prevent duplicate mission creation

rescue_dispatch_locks with expires_at

Unique constraint on rescue_missions.sos_alert_id

Integration with EscalateSosAlerts command

Stage 2 (10 min) currently placeholder

Replace with RescueDispatchService::broadcast($sos)

Time: ~1-2 days
Tests: Dispatch + lock + idempotency

📊 Testing Strategy
Layer	Coverage
Unit	Model state machine, dispatch service, lock logic
Feature	Registration, dashboard CRUD, admin actions
Integration	SOS → dispatch → mission lifecycle → resolve
Manual	Browser flow (provider + admin)
Target: 20-30 new tests, all passing.

🚦 Rollout Phases
Phase	Deploy	Dependencies
1	Provider type + registration	None
2	Provider dashboard	Phase 1
3	Admin Command Center	Phase 1
4	Integration	Phase 1+2+3
Push rule: सबै phase सकिएपछि एकैचोटि push (तपाईंको rule)।

⚠️ Safety & Compliance
CAAN compliance: Rescue providers must upload valid CAAN licenses

Insurance: Medical cert + insurance policy required

SLA tracking: 5 min dispatch, 10 min to scene

Audit trail: All actions logged (immutable)

Manual override: Admin can always intervene

🔗 Related Files
File	Purpose
app/Models/RescueProvider.php	Provider-specific data
app/Models/RescueMission.php	Mission lifecycle
app/Models/RescueMissionUpdate.php	Audit trail
app/Models/RescueDispatchLock.php	60s lock
app/Services/RescueDispatchService.php	Core dispatch logic
app/Http/Controllers/Provider/RescueDashboardController.php	Provider UI
app/Http/Controllers/Provider/RescueMissionController.php	Mission management
app/Http/Controllers/Admin/SosController.php	Admin Command Center
resources/views/provider/rescue/*	Provider views
resources/views/admin/sos/*	Admin views
app/Console/Commands/AutoReleaseRescueLocks.php	Cron
📝 Changelog
2026-11-09 — Plan created

Provider type + category identified

Data model defined (4 new tables)

4-phase rollout planned

Integration flow mapped

End of Plan | Living document — update as implementation progresses

