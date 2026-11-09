# Rescue Service — Complete Implementation Plan & Progress Tracker

**Created:** 2026-11-09
**Last Updated:** 2026-11-09
**Status:** Phase 1 Complete ✅ | Phase 2 In Progress ⏳
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

## 📈 Implementation Progress (As of Nov 09, 2026)

### ✅ Completed Works

#### Phase 1: Provider Type, Category & Registration (100% DONE)
- [x] Added `rescue-service` to `provider_types` (ID: 19).
- [x] Added `rescue` to `service_categories` (ID: 48).
- [x] Created pivot mapping (`RescueProviderTypeMappingSeeder`).
- [x] Created `rescue_providers` table migration (aviation license, medical cert, coverage radius, fleet size, is_24_7, etc.).
- [x] Created `RescueProvider` model.
- [x] Updated Registration UI (`auth.register.blade.php`) with conditional rescue fields.
- [x] Updated `RegisterController` to validate and save rescue provider data.
- [x] Added EN/NP translations for rescue fields.
- [x] Added SEO `noindex` meta to register page.
- [x] Updated `DashboardController` and `LoginController` to redirect rescue providers to the new dashboard.

#### Phase 2: Provider Dashboard Layout & Core UI (Partially DONE)
- [x] Created `provider-rescue.blade.php` layout (exact match with HTML design: Red/Emergency theme, sidebar, topbar, dynamic duty toggle).
- [x] Updated `getProviderLayout()` map in all 7 relevant controllers (SOS, Ad, Invoice, Payment, PaymentMethod, Service, Supplier).
- [x] Created `RescueDashboardController` with routes for 14 sections.
- [x] **Overview Dashboard (`dashboard.blade.php`)**: Exact HTML design implemented (4 stat cards, Live SOS Queue empty state, Active Missions empty state, Weather panel, GPS map SVG, Audit log static, Fleet status static).
- [x] **Live SOS Queue (`sos.blade.php`)**: Basic list view created (displays active SOS with traveler details). 

---

### ⏳ Remaining Works (Pending Steps)

#### Phase 2: Provider Dashboard (Remaining Views & DB Tables)
- [ ] **Missing DB Tables for Rescue**: Need to create migrations for `rescue_missions`, `rescue_fleets`, `rescue_mission_updates`, and `rescue_dispatch_locks`.
- [ ] **SOS Queue (Accept/Decline UI)**: Update `sos.blade.php` to include the 60s lock mechanism, Accept/Decline buttons, and Priority Rules modal from HTML design.
- [ ] **Active Missions (`missions.blade.php`)**: Build dual-timeline UI (SOS lifecycle + Rescue lifecycle), cost panels, and status advancement buttons.
- [ ] **Fleet Management (`fleet.blade.php`)**: Build CRUD for Helicopters, Ground Ambulances, Medical Teams (requires `rescue_fleets` table).
- [ ] **Staff & Crew (`staff.blade.php`)**: Integrate with existing `ProviderStaff` model for Pilots, Medics, and Coordinators.
- [ ] **Coverage Areas (`coverage.blade.php`)**: Multi-select districts, radius slider, and active toggle.
- [ ] **Pricing Config (`pricing.blade.php`)**: Rate card for base fee, per km, per hour, night/altitude surcharges.
- [ ] **Documents & Licenses (`documents.blade.php`)**: UI for CAAN license, Medical cert, Insurance policy, Pilot licenses.
- [ ] **Insurance Partners (`insurance.blade.php`)**: UI for accepted insurance providers.
- [ ] **Mission Revenue (`revenue.blade.php`)**: Revenue stats and table.
- [ ] **Reports & Analytics (`reports.blade.php`)**: SVG charts for missions/month and response time trends.
- [ ] **GPS Tracking (`gps.blade.php`)**: Full-page map layout.
- [ ] **Audit Log (`audit.blade.php`)**: Full list of rescue actions.
- [ ] **Security & Permissions (`security.blade.php`)**: Toggles for 2FA, Session Timeout, SOS Siren, etc.

#### Phase 3: Admin Command Center (Not Started)
- [ ] Add SOS summary widgets to existing Admin Dashboard.
- [ ] Create Admin SOS Alerts section (Safety menu).
- [ ] Build **Rescue Command Center** (`/admin/sos`): Escalation queue, All active SOS, Providers live status, Admin actions log.
- [ ] Build Admin SOS Detail page with manual dispatch capabilities.
- [ ] Build Admin Active Missions page (system-wide).
- [ ] Build Admin Rescue Providers verification page.
- [ ] Build Admin Dispatch History page.

#### Phase 4: SOS ↔ Rescue Integration (Not Started)
- [ ] Create `RescueDispatchService.php` (find nearest providers, broadcast, 60s lock, assign mission).
- [ ] Update `EscalateSosAlerts` command (at 10 min mark, trigger `RescueDispatchService::broadcast()`).
- [ ] Implement idempotency (prevent duplicate missions).
- [ ] Build cron command: `AutoReleaseRescueLocks.php`.
- [ ] Implement SMS/Push notifications for dispatch.

---

## 🗄️ Data Model (Target State)

### Existing (used by Rescue)
- `providers` — base provider table
- `provider_types` — includes `rescue-service`
- `service_categories` — includes `rescue`
- `services` — rescue services
- `sos_alerts` — existing SOS (from Phase 5)

### New Tables (To Be Created / Partially Created)
- `rescue_providers` ✅ (Done)
- `rescue_missions` ⏳ (Pending)
- `rescue_mission_updates` ⏳ (Pending)
- `rescue_dispatch_locks` ⏳ (Pending)
- `rescue_fleets` ⏳ (Pending - needed for Fleet Management)

---

## 🚦 Safety & Compliance
- **CAAN compliance:** Rescue providers must upload valid CAAN licenses.
- **Insurance:** Medical cert + insurance policy required.
- **SLA tracking:** 5 min dispatch, 10 min to scene.
- **Audit trail:** All actions logged (immutable).
- **Manual override:** Admin can always intervene.

---

## 🔗 Related Files
| File | Purpose | Status |
|---|---|---|
| `app/Models/RescueProvider.php` | Provider-specific data | ✅ Done |
| `app/Models/RescueMission.php` | Mission lifecycle | ⏳ Pending |
| `app/Models/RescueFleet.php` | Fleet data | ⏳ Pending |
| `app/Services/RescueDispatchService.php` | Core dispatch logic | ⏳ Pending |
| `app/Http/Controllers/Provider/RescueDashboardController.php` | Provider UI | ✅ Done |
| `app/Http/Controllers/Admin/SosController.php` | Admin Command Center | ⏳ Pending |
| `resources/views/provider/rescue/*` | Provider views (14 files) | ⏳ 2/14 Done |
| `resources/views/admin/sos/*` | Admin views | ⏳ Pending |

---

## 📝 Changelog
- **2026-11-09:** Plan created.
- **2026-11-09:** Phase 1 (Registration & DB) COMPLETE ✅.
- **2026-11-09:** Phase 2 started — Layout, Controller, Routing, and Dashboard UI COMPLETE ✅. SOS List UI started ⏳.

---

**End of Document** | Living document — update as implementation progresses