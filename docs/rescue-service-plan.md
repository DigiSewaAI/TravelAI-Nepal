# Rescue Service — Complete Implementation Plan & Progress Tracker

**Created:** 2026-11-09
**Last Updated:** 2026-11-09 (Evening)
**Status:** Phase 1 & 2 Complete ✅ | Phase 3 (Admin) In Progress ⏳
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
- [x] Created `rescue_providers` table migration + added JSON columns for `staff_members`, `insurance_partners`, `documents`.
- [x] Created `RescueProvider` model with casts.
- [x] Updated Registration UI (`auth.register.blade.php`) with conditional rescue fields.
- [x] Updated `RegisterController` to validate and save rescue provider data.
- [x] Added EN/NP translations for rescue fields.
- [x] Added SEO `noindex` meta to register page.
- [x] Updated `DashboardController` and `LoginController` to redirect rescue providers to the new dashboard.

#### Phase 2: Provider Dashboard Layout & Core UI (100% DONE)
- [x] Created `provider-rescue.blade.php` layout (Exact match: Red/Emergency theme, sidebar, topbar, dynamic duty toggle).
- [x] Updated `getProviderLayout()` map in all 7 relevant controllers.
- [x] Created `RescueDashboardController` with routes for 14 sections.
- [x] **Overview Dashboard (`dashboard.blade.php`)**: 4 stat cards, Live SOS Queue, Active Missions, Weather panel, GPS map SVG, Audit log, Fleet status.
- [x] **Live SOS Queue (`sos.blade.php`)**: Displays active SOS with traveler details.
- [x] **Active Missions (`missions.blade.php`)**: Dual-timeline UI (SOS lifecycle + Rescue lifecycle).
- [x] **Fleet Management (`fleet.blade.php`)**: Full CRUD (Helicopters, Ground Ambulances, Medical Teams).
- [x] **Staff & Crew (`staff.blade.php`)**: Full CRUD (Pilots, Medics, Coordinators) with Contact Phone mandatory.
- [x] **Coverage Areas (`coverage.blade.php`)**: Multi-select districts, radius slider, and update functionality.
- [x] **Pricing Config (`pricing.blade.php`)**: Rate card for base fee, per km, per hour, surcharges.
- [x] **Documents & Licenses (`documents.blade.php`)**: Upload UI (Status forced to 'pending' for admin review).
- [x] **Insurance Partners (`insurance.blade.php`)**: Full CRUD for insurance providers.
- [x] **Mission Revenue (`revenue.blade.php`)**: Revenue stats and table.
- [x] **Reports & Analytics (`reports.blade.php`)**: SVG charts for missions/month and response time trends.
- [x] **GPS Tracking (`gps.blade.php`)**: Full-page dark map layout.
- [x] **Audit Log (`audit.blade.php`)**: Timeline UI.
- [x] **Security & Permissions (`security.blade.php`)**: Toggle switches UI.

#### Phase 3: Admin Command Center (Partially DONE)
- [x] Created `Admin\RescueCommandCenterController`.
- [x] Built **Rescue Command Center** (`/admin/rescue`): Stats, Escalation queue, All active SOS, Providers live status.
- [x] Built Admin SOS Detail page (`/admin/rescue/sos/{id}`) with manual dispatch capabilities.
- [x] Created `Admin\RescueDocumentsController`.
- [x] Built **Document Verification UI** (`/admin/rescue/documents`): Download, Approve, Reject with reason.
- [x] Added sidebar links in Admin Layout for both Command Center and Document Verification.

---

### ⏳ Remaining Works (Pending Steps)

#### Phase 3: Admin Command Center (Remaining)
- [ ] **Admin Dashboard Widget**: Add "Pending Rescue Documents" count card to main Admin Dashboard.
- [ ] Admin Active Missions page (system-wide view).
- [ ] Admin Rescue Providers verification page (list of all rescue providers).
- [ ] Admin Dispatch History page.

#### Phase 4: SOS ↔ Rescue Integration (Auto-Dispatch)
- [ ] Create `RescueDispatchService.php` (find nearest providers, broadcast, 60s lock, assign mission).
- [ ] Update `EscalateSosAlerts` command (at 10 min mark, trigger `RescueDispatchService::broadcast()`).
- [ ] Implement idempotency (prevent duplicate missions).
- [ ] Build cron command: `AutoReleaseRescueLocks.php`.
- [ ] Implement SMS/Push notifications for dispatch.

#### Minor Fixes
- [ ] **Provider Missions Filter**: Update `RescueDashboardController@missions` to hide completed/cancelled missions from the "Active" list.

---

## 🗄️ Data Model (Target State)

### Existing (used by Rescue)
- `providers` — base provider table
- `provider_types` — includes `rescue-service`
- `service_categories` — includes `rescue`
- `sos_alerts` — existing SOS (from Phase 5)

### New Tables (Created)
- `rescue_providers` ✅ (Done)
- `rescue_fleets` ✅ (Done)
- `rescue_missions` ✅ (Done)
- `rescue_mission_updates` ✅ (Done)
- `rescue_dispatch_locks` ⏳ (Pending - Phase 4)

---

## 🚦 Safety & Compliance
- **CAAN compliance:** Rescue providers must upload valid CAAN licenses.
- **Insurance:** Medical cert + insurance policy required.
- **SLA tracking:** 5 min dispatch, 10 min to scene.
- **Audit trail:** All actions logged (immutable).
- **Manual override:** Admin can always intervene.

---

## 📝 Changelog
- **2026-11-09 (Morning):** Plan created. Phase 1 (Registration & DB) COMPLETE ✅.
- **2026-11-09 (Afternoon):** Phase 2 (Provider Dashboard & CRUD) COMPLETE ✅.
- **2026-11-09 (Evening):** Phase 3 (Admin Command Center & Document Verification) Core features COMPLETE ✅.

---

**End of Document** | Living document — update as implementation progresses