# Rescue Service — Complete Implementation Plan & Progress Tracker

**Created:** 2026-11-09
**Last Updated:** 2026-11-09 (Night)
**Status:** Phase 1, 2 & 4 Core Complete ✅ | Phase 3 (Admin) Mostly Complete ⏳
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
- [x] Created `rescue_providers` table migration + JSON columns (`staff_members`, `insurance_partners`, `documents`).
- [x] Created `RescueProvider` model with casts.
- [x] Updated Registration UI (`auth.register.blade.php`) with conditional rescue fields.
- [x] Updated `RegisterController` to validate and save rescue provider data.
- [x] Added EN/NP translations for rescue fields.
- [x] Added SEO `noindex` meta to register page.
- [x] Updated `DashboardController` and `LoginController` to redirect rescue providers to the new dashboard.

#### Phase 2: Provider Dashboard & Core UI (100% DONE)
- [x] Created `provider-rescue.blade.php` layout (Exact match: Red/Emergency theme, sidebar, topbar).
- [x] Updated `getProviderLayout()` map in all 7 relevant controllers.
- [x] Created `RescueDashboardController` with routes for 14 sections.
- [x] **Overview Dashboard (`dashboard.blade.php`)**: Stat cards, Live SOS Queue, Active Missions, Weather, GPS map, Audit log, Fleet status.
- [x] **Live SOS Queue (`sos.blade.php`)**: Displays active SOS with 60s Lock mechanism, Accept/Confirm buttons, and timer.
- [x] **Active Missions (`missions.blade.php`)**: Dual-timeline UI (SOS lifecycle + Rescue lifecycle) + Hide completed missions filter.
- [x] **Fleet Management (`fleet.blade.php`)**: Full CRUD.
- [x] **Staff & Crew (`staff.blade.php`)**: Full CRUD (Contact Phone mandatory).
- [x] **Coverage Areas (`coverage.blade.php`)**: Multi-select districts, radius slider.
- [x] **Pricing Config (`pricing.blade.php`)**: Rate card configuration.
- [x] **Documents & Licenses (`documents.blade.php`)**: Upload UI (Status forced to 'pending').
- [x] **Insurance Partners (`insurance.blade.php`)**: Full CRUD.
- [x] **Mission Revenue (`revenue.blade.php`)**, **Reports (`reports.blade.php`)**, **GPS Tracking (`gps.blade.php`)**, **Audit Log (`audit.blade.php`)**, **Security (`security.blade.php`)**: UI complete.

#### Phase 3: Admin Command Center (Mostly DONE)
- [x] Created `Admin\RescueCommandCenterController`.
- [x] Built **Rescue Command Center** (`/admin/rescue`): Stats, Escalation queue, All active SOS, Providers live status.
- [x] Built Admin SOS Detail page (`/admin/rescue/sos/{id}`) with manual dispatch capabilities.
- [x] Created `Admin\RescueDocumentsController`.
- [x] Built **Document Verification UI** (`/admin/rescue/documents`): Download, Approve, Reject with reason.
- [x] Added sidebar links in Admin Layout for both Command Center and Document Verification.
- [x] Added "Pending Rescue Docs" widget to main Admin Dashboard.

#### Phase 4: SOS ↔ Rescue Integration (Core DONE)
- [x] Created `RescueDispatchService.php` (Find nearest online/verified providers, broadcast).
- [x] Updated `EscalateSosAlerts` command: At 10 min mark, triggers `RescueDispatchService::broadcast()`.
- [x] Created `rescue_dispatch_locks` table + Model.
- [x] Implemented 60s Locking Mechanism in `sos.blade.php` (Accept -> Lock -> Confirm -> Create Mission).

---

### ⏳ Remaining Works (For Tomorrow - Nov 10, 2026)

#### Phase 3: Admin Command Center (Remaining)
- [ ] Admin Active Missions page (system-wide view of all ongoing rescue missions).
- [ ] Admin Rescue Providers verification page (list of all rescue providers and their status).
- [ ] Admin Dispatch History page (logs of past dispatch requests).

#### Phase 4: SOS ↔ Rescue Integration (Safety Features)
- [ ] **Idempotency Check**: Prevent duplicate mission creation if multiple providers try to confirm.
- [ ] **Cron Command**: `AutoReleaseRescueLocks.php` (To release expired locks if a provider holds a lock but doesn't confirm within 60s, preventing SOS from getting stuck).
- [ ] **Notifications**: Implement actual SMS/Push notifications to providers when an SOS is broadcasted.

---

## 🗄️ Data Model (Target State)

### Existing (used by Rescue)
- `providers`, `provider_types`, `service_categories`, `sos_alerts`

### New Tables (Created)
- `rescue_providers` ✅
- `rescue_fleets` ✅
- `rescue_missions` ✅
- `rescue_mission_updates` ✅
- `rescue_dispatch_locks` ✅

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
- **2026-11-09 (Night):** Phase 4 (Auto-Dispatch & 60s Lock) Core features COMPLETE ✅.

---

**End of Document** | Living document — update as implementation progresses