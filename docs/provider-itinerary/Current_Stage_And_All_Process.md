# 📘 TravelAI Nepal — Provider Itinerary System
## Current Stage & Continuity Document

**Version:** 2.1 (Clean Rewrite + Phase 4M Foundation)
**Updated:** 2026-09-25
**Reference:** Master Plan v1.0 (`docs/provider-itinerary/Master_Plan_v1.0.md`)

> **Single source of truth** for the Provider Itinerary workstream.

---

# 0. MASTER RULES

### Workflow
```
Discover → Report → Master Review → Scope Lock → Implement
→ Verify → Commit Gate → Commit → Push Gate → Push → Close
```

### Mandatory Rules (R1–R24)
| # | Rule |
|---|---|
| R1 | Inspect before code |
| R2 | Discovery = read-only |
| R3 | Never invent data |
| R4 | Never expand a locked phase |
| R5 | AI Planner SACRED |
| R6 | GLOBE-01..07 PROTECTED |
| R7 | Existing Explore page protected |
| R8 | DB safety — no dup tables, no renames |
| R9 | No PII exposure |
| R10 | No fake data |
| R11 | No secrets in code |
| R12 | Report out-of-scope findings |
| R13 | Never `git add .` |
| R14 | Never force-push |
| R15 | Never amend/rebase without approval |
| R16 | Commit only after COMMIT GO |
| R17 | Push only after PUSH GO |
| R18 | Runtime evidence required |
| R19 | Migration only after explicit authorization |
| R20 | Preserve existing service/booking/subscription/safety |
| R21 | No paid service without Master approval |
| R22 | Free alternatives must be exhausted first |
| R23 | Production must run on free-tier infrastructure |
| R24 | Any future paid service requires explicit product decision |

### Git Safety
- **Allowed:** `git status`, `git diff`, `git add <file>`, `git commit -F <msg>`, `git push origin <branch>`, `git log`, `git rev-parse`
- **FORBIDDEN:** `git add .`, `git add -A`, `reset --hard`, `checkout .`, `clean -fd`, `commit --amend`, `rebase`, `push --force`

### DB Safety
- Additive migrations only
- FK with `onDelete('cascade')` / `onDelete('set null')`
- MySQL 8.0.30 InnoDB (verified)

---

# 1. CURRENT STATE (2026-09-25)

### Phase Ledger
| Phase | Status |
|---|---|
| PROVIDER-ITINERARY-01 Discovery | ✅ CLOSED |
| PROVIDER-ITINERARY-02 Deep Audit | ✅ CLOSED |
| PROVIDER-ITINERARY-03 Spec Verification | ✅ CLOSED |
| PROVIDER-ITINERARY-04 DB Foundation | ✅ CLOSED + PUSHED (`9a3dcab`) |
| PROVIDER-ITINERARY-05 Provider CRUD | ✅ CLOSED + PUSHED (`1948022`) |
| PROVIDER-ITINERARY-06 Traveler Renderer | ✅ CLOSED + PUSHED (`e3d8388`) |
| PROVIDER-ITINERARY-07 Preview/Publish | ✅ CLOSED + PUSHED (`d82ca8b`) |
| PROVIDER-ITINERARY-08 Geographic | ✅ CLOSED + PUSHED (`39f9a11`) |
| PROVIDER-ITINERARY-09A Reviews + Related | ✅ CLOSED + PUSHED (`fc75f5f`) |
| PROVIDER-ITINERARY-09C Dashboard Link | ✅ CLOSED + PUSHED (`dcf7be8`) |
| PROVIDER-ITINERARY-09B-01 DB Foundation | ✅ CLOSED + PUSHED (`d5f36e4`) |
| PROVIDER-ITINERARY-09B-02 Provider UI | ✅ CLOSED + PUSHED (`eb5ce8a`) |
| PROVIDER-ITINERARY-09B-03 Public UI | ✅ CLOSED + PUSHED (`3e73db0`) |
| PROVIDER-ITINERARY-09B-04 Booking Integration | ✅ CLOSED + PUSHED (`c9764ae`) |
| ENUM HOTFIX (rejected) | ✅ CLOSED + PUSHED (`3970163`) |
| Ticket A (SSL Verify) | ✅ CLOSED + PUSHED (`fba6b3d`) |
| Ticket B (Analysis Fix) | ✅ CLOSED + PUSHED (`e6fab12`) |
| Phase X-01 (AI Draft) | ✅ CLOSED + PUSHED (`9c97683`) |
| F1 Warning | ✅ CLOSED + PUSHED (`f786780`) |
| MERGE TO MAIN | ✅ CLOSED + PUSHED |
| Phase 4M Foundation (2026-09-25) | ✅ CLOSED + PUSHED |
| **Phase 4M-3 (Provider Type Constraint)** | 🟢 **IN PROGRESS** |
| PROVIDER-ITINERARY-10 Reviews | 🔒 HOLD |
| PROVIDER-ITINERARY-11 Versioning | 🔒 FUTURE |
| PROVIDER-ITINERARY-12 AI-Assisted | 🔒 FUTURE |

### Git State
```
Branch:      main
Local HEAD:  c0cc382  (Phase 4M-2-3+4)
Remote HEAD: c0cc382  (synced)
Tests:       41 passed / 1 failed (pre-existing Safety)
Working:     clean
```

---

# 2. LOCKED ARCHITECTURE

```
Provider → Service (Package) → Service Itinerary
                                    ↓
                          service_itinerary_days
                                    ↓
                     ┌──────────────┴──────────────┐
                     ↓                             ↓
           service_itinerary_items      service_itinerary_day_media
```

**Planner World (SEPARATE, PROTECTED):**
```
PlannerRequest → PlannerResult → itinerary_days → itinerary_items
```

### Master Decisions Q1-Q10
| Q | Decision |
|---|---|
| Q1 | Package = existing `services` |
| Q2 | Planner `itinerary_days` NOT reused |
| Q3 | New tables: `service_itinerary_days` + `service_itinerary_items` |
| Q4 | Draft/Preview/Publish 🔒 deferred to Phase 07 |
| Q5 | Day-level currency = NO |
| Q6 | Draft/Preview/Publish = YES (Phase 07) |
| Q7 | Authorization = ServicePolicy pattern |
| Q8-Q10 | Legacy cleanup = DEFER |

### Master Decisions D1-D5 (Phase 04)
| D | Decision |
|---|---|
| D1 | NO status column in Phase 04 (Phase 07 finalize) |
| D2 | NO soft deletes |
| D3 | `sort_order` = unsigned int, default 0 |
| D4 | UNIQUE(service_id, day_number) |
| D5 | Booking snapshot → Phase 07 |

### Master Decisions SL1-SL9 (Phase 05)
| SL | Decision |
|---|---|
| SL1 | Split controllers (3 files) |
| SL2 | Explicit routes only (no `Route::resource`) |
| SL3 | Bulk JSON reorder + two-phase update |
| SL4 | Individual endpoints + inline validation |
| SL5 | `files[]` multi-file array |
| SL6 | Views in dedicated folder |
| SL7 | Day + Item reorder separate endpoints |
| SL8 | Authorization chain: service → day → item/media |
| SL9 | Acceptance tests T1-T23 defined |

### Clarifications C1-C2
| C | Decision |
|---|---|
| C1 | Server-assigned `day_number = MAX+1`, renumber-on-delete (two-phase) |
| C2 | 25 MB/file, max 10 files/request, uniform limit |

---

# 3. PROTECTED SYSTEMS

**❌ NEVER modify without explicit authorization:**

**AI / Planner:**
`PlannerService`, `ItineraryGenerator`, `ItineraryValidator`, `ItineraryDay`, `ItineraryItem`, `PlannerRequest`, `PlannerResult`, AI quota/subscription

**GLOBE-01..07:**
`MapDataController` (+ .bak), `routes/api.php` GLOBE endpoints, `resources/views/public/services/index.blade.php`, `public/map/nepal-districts.topojson`

**Other:**
Safety system, Booking security/share tokens, Payment/subscription, JourneyReplay

---

# 4. DB BASELINE (Verified)

| Table | Count |
|---|---|
| locations | 294 |
| waypoints | 752 (716 active) |
| routes | 143 (138 active) |
| route_segments | 1,355 (812 active) |
| services | 1,169 (1,160 active) |
| service_categories | 7 |
| planner_requests | 5,125 |
| planner_results | 5,125 |
| itinerary_days | 25,200 |
| itinerary_items | 25,545 |
| bookings | 31 |
| service_itinerary_days | 0 |
| service_itinerary_items | 0 |
| service_itinerary_day_media | 0 |

**Verify:**
```cmd
php artisan tinker --execute="echo json_encode([DB::table('locations')->count(), DB::table('waypoints')->count(), DB::table('routes')->count(), DB::table('services')->count(), DB::table('service_categories')->count()]);"
```
Expected: `[294,752,143,1169,7]`

---

# 5. PHASE SUMMARIES

## Phase 01 — Discovery
Provider architecture mapped. Legacy `treks` (0 rows) discovered. Planner `itinerary_days` confirmed protected. No provider package / structured itinerary / builder UI existed.

## Phase 02 — Deep Audit
`services` = 14 cols, status enum(active,inactive). ServicePolicy = `ownProvider()`. TrekDetail/TourDetail itinerary = unstructured JSON. Public show page = no timeline. Bookings = no snapshot.

## Phase 03 — Spec Verification
Architecture CLEAN. MySQL 8.0.30 InnoDB. Laravel 11+ FK syntax valid. ServicePolicy reuse valid. Legacy JSON untouched.

## Phase 04 — DB Foundation ✅ PUSHED
**Commit:** `9a3dcab`

**Tables created:**
- `service_itinerary_days` (service_id FK, day_number, title, description, 3 nullable waypoints, distance/time/elevation/altitude, meals JSON, accommodation, UNIQUE(service_id, day_number))
- `service_itinerary_items` (day_id FK, title, description, time_of_day, sort_order, is_optional, metadata)
- `service_itinerary_day_media` (day_id FK, file_path, thumbnail_path, media_type, alt_text, sort_order)

**Models:** `ServiceItineraryDay`, `ServiceItineraryItem`, `ServiceItineraryDayMedia`
**Relation:** `Service::itineraryDays()`

## Phase 05 — Provider CRUD ✅ PUSHED
**Commit:** `1948022` (9 files, 892 insertions, 0 deletions)

**Controllers (3):**
- `ItineraryDayController` — index, store (race-safe), update, destroy (renumber), reorder (two-phase)
- `ItineraryItemController` — store, update, destroy, reorder
- `ItineraryDayMediaController` — store (multi-file), destroy (file cleanup)

**Views (5):**
- `index.blade.php` — main editor
- `_day_card.blade.php`, `_item_row.blade.php`, `_media_row.blade.php`, `_media_upload.blade.php`

**Routes (11, additive in `routes/web.php`):**
```
GET    /provider/services/{service}/itinerary                 → index
POST   /provider/services/{service}/itinerary/days            → days.store
POST   /provider/services/{service}/itinerary/days/reorder    → days.reorder
PUT    /provider/services/{service}/itinerary/days/{day}      → days.update
DELETE /provider/services/{service}/itinerary/days/{day}      → days.destroy
POST   /provider/services/{service}/itinerary/days/{day}/items → items.store
PUT    /provider/services/{service}/itinerary/items/{item}    → items.update
DELETE /provider/services/{service}/itinerary/items/{item}    → items.destroy
POST   /provider/services/{service}/itinerary/items/reorder   → items.reorder
POST   /provider/services/{service}/itinerary/days/{day}/media → media.store
DELETE /provider/services/{service}/itinerary/media/{media}   → media.destroy
```

**Key safeguards:**
- Race-safe `day_number` (lockForUpdate + transaction)
- Two-phase renumber (avoids UNIQUE collision)
- SL8 chain: `$day->service_id === $service->id` → 404 on mismatch
- File cleanup on transaction failure
- Server-authoritative sort_order

**Verification:**
- Syntax ✅ | Routes (11, correct order) ✅ | Tinker integration ✅
- DB baseline `[0,0,0]` before/after ✅ | Full suite: 21 pass, 1 pre-existing Safety failure ✅
- GLOBE regression: zero touch ✅ | Diff: +29/−0 ✅

**T1–T23 Matrix (honest):**
- Fully PASS: T22, T23 (regression)
- PARTIAL (code/Tinker): T1, T4, T11, T12, T13
- NOT RUN: T2, T3, T5–T10, T14–T21
- **No dedicated test suite exists. Runtime tests pending. Not claimed as PASS.**

**Known verification debt:** T1–T21 runtime coverage pending.

---

# 6. OPEN TICKETS (Not Phase 05)

| Ticket | Description |
|---|---|
| `PROVIDER-ROUTES-HYGIENE-01` | `provider/services/{service}` route without `show()` |
| `MEDIA-THUMBNAIL-01` | Thumbnail generation absent |
| `PROVIDER-LAYOUT-VITE-01` | Tailwind CDN → Vite migration |
| `PROVIDER-ITINERARY-DATA-MIGRATION-01` | TrekDetail/TourDetail JSON → structured |
| `LEGACY-CLEANUP-01` | `treks` / `Agency` cleanup |
| `LEGACY-HYGIENE-01` | Duplicate `create_treks` migrations |
| `MapDataController.php.bak` | Hygiene cleanup |
| `AI-FIX` | PlannerService LLM integration |
| `DATA-AUDIT` | planner_results count origin |

---

# 7. NEXT PHASE — PROVIDER-ITINERARY-06

**Status:** ✅ CLOSED + PUSHED (`e3d8388`) — kept for reference

---

# 8. HOW TO USE THIS FILE (New AI Handoff)

```
TRAVELAI NEPAL — PROVIDER ITINERARY CONTINUITY BRIEFING

You are the co-master/implementer AI for TravelAI Nepal's
Provider Itinerary workstream.

READ BOTH FILES:
1. docs/provider-itinerary/Master_Plan_v1.0.md (vision)
2. docs/provider-itinerary/Current_Stage_And_All_Process.md (this file)

Current state (2026-09-25):
- PROVIDER-ITINERARY-01..09C = CLOSED + PUSHED
- PROVIDER-ITINERARY-09B-01..04 = CLOSED + PUSHED
- X-01 AI Draft = CLOSED + PUSHED
- Phase 4M Foundation = CLOSED + PUSHED
- Phase 4M-3 = IN PROGRESS

Git:
- Branch: main
- HEAD: c0cc382 (synced with remote)

Your role:
- Follow workflow (Section 0)
- Follow R1-R24 rules
- Never `git add .`, `--force`, `--amend`
- Wait for explicit Master approval before commit/push

PROTECTED: AI Planner, GLOBE-01..07, Booking, Safety (Section 3)

Do NOT:
- Modify code without Master approval
- Commit without COMMIT GO
- Push without PUSH GO
- Touch AI Planner or GLOBE code

Confirm understanding, then wait for Master directive.
```

---

# 9. DEVELOPMENT LOG

**2026-09-18**
- Phase 01 Discovery → Complete
- Phase 02 Deep Audit → Complete
- Phase 03 Spec Verification → Complete
- Phase 04 DB Foundation → CLOSED + PUSHED (`9a3dcab`)
- Phase 05 Provider CRUD → CLOSED + PUSHED (`1948022`)

---

# 10. FINAL STATUS

```
PROVIDER-ITINERARY-01 → 09C:   ✅ ALL CLOSED + PUSHED
PROVIDER-ITINERARY-09B-01 → 04: ✅ ALL CLOSED + PUSHED
PHASE X-01 (AI Draft):          ✅ CLOSED + PUSHED
MERGE TO MAIN:                  ✅ CLOSED + PUSHED
PHASE 4M FOUNDATION:            ✅ CLOSED + PUSHED
PHASE 4M-3:                     🟢 IN PROGRESS

Remote HEAD: c0cc382 (synced)
Tests:       41p / 1f (pre-existing Safety)

Known debt:
- T1–T21 runtime verification (Phase 05)
- Various ticket backlog (Section 6 + tickets below)

AWAITING PHASE 4M-3-1 MIGRATION IMPLEMENTATION
```

---

**Document End — Provider Itinerary Continuity v2.1**

---

## 📌 PROVIDER-ITINERARY-06 DISCOVERY — COMPLETE (2026-09-18)

**Status:** Report submitted. Awaiting Master Scope Lock.
**Git:** `feature/globe-system` @ `1948022` — unchanged.

### Key Findings
- Phase 05 tables empty `[0,0,0]` — no runtime data
- Public show.blade.php (213 lines) — no itinerary/timeline/accordion
- Alpine.js NOT installed — vanilla JS needed
- Tailwind CDN (not Vite) in layout
- JSON-LD TouristTrip exists
- 716 active waypoints with 100% coords
- Safest insertion: TrekDetail box → Description (between)
- Legacy TrekDetail.itinerary = placeholder string, not rendered

### Renderer Options (Master decision)
- A: Itinerary only (recommended basis)
- B: + Waypoint context
- C: + Map
- D: + Route visualization

### Future File Scope (smallest)
- `resources/views/public/services/show.blade.php` (modify)
- `app/Http/Controllers/Public/ServiceController.php` (modify — eager load)
- Optional: `_itinerary.blade.php` partial

### Master Decisions Required (M1–M10)
Layout, accordion default, legacy JSON, media presentation, waypoint display, empty state, JSON-LD extension, video handling, test scope

---

## 📌 PROVIDER-ITINERARY-06 — IMPLEMENTATION COMPLETE (2026-09-18)

**Status:** Implementation complete. Awaiting Master COMMIT GATE.
**Git:** `feature/globe-system` @ `1948022` — 2 files modified (uncommitted)

### Files Modified (2)
- `app/Http/Controllers/Public/ServiceController.php` (+5 eager-loads)
- `resources/views/public/services/show.blade.php` (+170 itinerary section)

**Total:** 175 insertions, 0 deletions

### Master Decisions Applied (M1–M10)
- M1 ✅ Itinerary + Waypoint Context
- M2 ✅ Full-width below existing grid
- M3 ✅ First day open + Expand/Collapse All
- M4 ✅ Ignore legacy JSON
- M5 ✅ Grid + lightbox (images)
- M6 ✅ Waypoint chips (Start/Overnight/End)
- M7 ✅ Hide section if no days
- M8 ✅ JSON-LD untouched
- M9 ✅ Native video player
- M10 ⏳ T1–T20 pending post-commit

### Key Safeguards
- Existing 6 eager-loads preserved
- Waypoint selects include `id` (required for relation)
- No Alpine.js (vanilla JS only)
- CRLF normalization avoided (clean diff)
- Legacy TrekDetail JSON not referenced

---

## 📌 PROVIDER-ITINERARY-06 — TEST GATE PASS (2026-09-18)

**Status:** T1–T20 runtime audit COMPLETE, all PASS. Awaiting Master COMMIT GO.
**Git:** `feature/globe-system` @ `1948022` — 2 files modified (uncommitted).

### Test Results — 20/20 PASS
- T1–T20 runtime acceptance executed
- C.9 Expand/Collapse All buttons verified
- Test data: created → tested → cleaned up
- Final DB baseline: `[0,0,0]` (verified)
- Browser re-check: itinerary section hidden when empty (M7 confirmed)

### Key Evidence
- Day 1 with metadata chips + 2 items renders correctly
- XSS strings escaped (literal text, no execution)
- N+1: only 9 queries total (properly eager loaded)
- Mobile 375px: no horizontal overflow
- JSON-LD unchanged
- No new console errors

---

## 📌 PROVIDER-ITINERARY-06 — COMMIT COMPLETE (2026-09-18)

**Commit:** `e3d8388` — `feat(provider-itinerary): add public itinerary renderer`
**Full hash:** `e3d83887d7c621a942f6a84ba20090916191e9dd`
**Branch:** `feature/globe-system`
**Status:** Committed locally. Push HOLD.

### Files Committed (2)
- `app/Http/Controllers/Public/ServiceController.php` (+5 eager-loads)
- `resources/views/public/services/show.blade.php` (+170 itinerary section)

**Total:** 175 insertions, 0 deletions

### Test Gate
- T1–T20 runtime audit: 20/20 PASS
- Test data cleanup: `[0,0,0]` baseline restored
- Browser re-check: itinerary hidden when empty (M7 verified)

---

## 📌 PROVIDER-ITINERARY-06 — CLOSED + PUSHED (2026-09-18)

**Commit:** `e3d8388` — `feat(provider-itinerary): add public itinerary renderer`
**Full hash:** `e3d83887d7c621a942f6a84ba20090916191e9dd`
**Push range:** `1948022` → `e3d8388`
**Remote:** `origin/feature/globe-system` (DigiSewaAI/TravelAI-Nepal)
**Sync:** Local == Remote ✅ (0 ahead / 0 behind)

### What Shipped
- Public itinerary timeline section on `/explore/service/{slug}`
- Day accordion (native `<details>`) — first open by default
- Expand All / Collapse All buttons (vanilla JS)
- Day metadata chips (distance, time, elevation, meals, accommodation)
- Waypoint chips (Start / Overnight / End) — null-safe
- Item list with optional marker
- Media grid (images) + native video player
- Empty state: section hidden if no itinerary days

### Test Evidence
- Test data created → tested → cleaned (final DB `[0,0,0]`)
- XSS escaped (literal text render)
- N+1: only 9 queries (eager-loaded)
- Mobile 375px: no horizontal overflow
- No new console errors
- Pre-existing: `sw.js addAll`, logo 403, Tailwind CDN warning

---

## 📌 PROVIDER-ITINERARY-07 DISCOVERY — COMPLETE (2026-09-19)

**Status:** Report submitted. Awaiting Master Scope Lock.
**Git:** `feature/globe-system` @ `e3d8388` — unchanged.

### Key Findings
- `services.status` = `active/inactive` (visibility control)
- `quotation_status` = `draft/reviewed/edited/sent` — **workflow precedent exists**
- Preview route pattern: `provider/quotation-requests/{id}/preview`
- Itinerary days currently have NO status — immediately public
- No cache on public ServiceController
- No draft/published patterns elsewhere

### Recommended Architecture (Option B)
- Add `itinerary_status ENUM('draft','published')` to `services` (default 'draft')
- Add `itinerary_published_at` timestamp
- Provider preview route (provider-only)
- Publish/unpublish actions
- Public gate: `active AND itinerary_status='published' AND days->isNotEmpty()`
- `services.status` untouched
- NO day-level status (Master rule respected)

### Files Likely to Change (7)
- 1 migration (additive)
- `Service.php`, `ItineraryDayController.php`, `routes/web.php`
- `itinerary/index.blade.php`, `itinerary/preview.blade.php` (new), `show.blade.php` (gate only)

---

## 📌 PROVIDER-ITINERARY-07 — COMMIT COMPLETE (2026-09-19)

**Commit:** `d82ca8b` — `feat(provider-itinerary): add itinerary draft publish lifecycle`
**Full hash:** `d82ca8b75cc33478c152afcefb777656b75570ac`
**Branch:** `feature/globe-system`
**Status:** Committed locally. Push HOLD.

### Files Committed (7)
- Migration (new): `2026_09_19_100001_add_itinerary_status_to_services_table.php` (+27)
- View (new): `preview.blade.php` (+131)
- `app/Models/Service.php` (+16)
- `app/Http/Controllers/Provider/ItineraryDayController.php` (+69)
- `resources/views/provider/services/itinerary/index.blade.php` (+56/-6)
- `resources/views/public/services/show.blade.php` (+1/-1)
- `routes/web.php` (+5)

**Total:** 300 insertions, 6 deletions

### Test Gate
- T1–T20: 18 PASS, 2 N/A (T8 = no 2nd provider, T17 = optional rollback)
- DB cleanup: `[0,0,0]` restored, 1169 draft

### Cosmetic Notes
- Commit subject has BOM (U+FEFF) — commit_msg.txt encoding
- Multi-line commit body collapsed to single line — newline loss

---

## 📌 PROVIDER-ITINERARY-07 — CLOSED + PUSHED (2026-09-19)

**Commit:** `d82ca8b` — `feat(provider-itinerary): add itinerary draft publish lifecycle`
**Full hash:** `d82ca8b75cc33478c152afcefb777656b75570ac`
**Push range:** `e3d8388` → `d82ca8b`
**Remote:** `origin/feature/globe-system` (DigiSewaAI/TravelAI-Nepal)
**Sync:** Local == Remote ✅ (0 ahead / 0 behind)

### What Shipped
- `services.itinerary_status` enum('draft','published') — migration
- `Service::isItineraryPublished()` / `isItineraryDraft()` helpers
- `ItineraryDayController::publish/unpublish/preview()` (authorize-first)
- 3 provider routes (auth group)
- Provider UI: status badge + lifecycle buttons
- Public gate: `active + published + days`
- Provider-only preview view

---

## 📌 PROVIDER-ITINERARY-08 DISCOVERY — COMPLETE (2026-09-19)

**Status:** Report submitted. Awaiting Master Scope Lock.
**Git:** `feature/globe-system` @ `d82ca8b` — unchanged.

### Master Plan Phase Status
- I–V: ✅ CLOSED
- **VI (Geographic): 🔴 MISSING**
- **VII (Availability): 🔴 MISSING**
- **VIII (Reviews): 🔴 MISSING**
- IX: ✅ CLOSED (Phase 07)
- X (AI): 🔒 FUTURE

### Actual Gaps
1. **Geographic:** Provider day form मा waypoint dropdown नहुने; public page मा कोई map/route reference
2. **Availability:** `availabilities` + `departures` tables MISSING
3. **Reviews:** 27 rows exist but public page मा full list नदेखिने; submission UI नहुने
4. **Booking Snapshot:** D5 deferred — कोई snapshot columns
5. **Media:** Lightbox/thumbnails/carousel नहुने (F6)

### 5 Phase 08 Candidates (no ranking)
- **A — Geographic Integration** (Phase VI)
- **B — Reviews + Related Packages** (Phase VIII)
- **C — Availability/Departure** (Phase VII)
- **D — Booking Snapshot** (D5)
- **E — Media Enhancement** (F6)

---

## 📌 PROVIDER-ITINERARY-08 — CLOSED + PUSHED (2026-09-19)

**Commit:** `39f9a11` — `feat(provider-itinerary): add geographic integration (waypoints + mini map)`
**Full hash:** `39f9a119ca632307a2b6f202a52bc44cdedd69a3`
**Push range:** `d82ca8b` → `39f9a11`
**Remote:** `origin/feature/globe-system` (DigiSewaAI/TravelAI-Nepal)
**Sync:** Local == Remote ✅ (0 ahead / 0 behind)

### Files Committed (7)
**New:**
- `app/Http/Controllers/Provider/ItineraryWaypointSearchController.php` (+52)

**Modified:**
- `app/Http/Controllers/Provider/ItineraryDayController.php` (+5)
- `app/Http/Controllers/Public/ServiceController.php` (+6)
- `resources/views/provider/services/itinerary/_day_card.blade.php` (+50)
- `resources/views/provider/services/itinerary/index.blade.php` (+85)
- `resources/views/public/services/show.blade.php` (+135)
- `routes/web.php` (+12)

**Total:** 323 insertions, 22 deletions

### What Shipped
- Provider day card: 3 searchable waypoint pickers (Start / Overnight / End)
- Server-side waypoint search endpoint (debounced, auth-protected)
- Public page: mini Leaflet map with itinerary waypoints
- Waypoint markers: Start (blue), Overnight (green), End (red)
- Map legend + popups with day info
- No polyline (waypoint-only visualization)
- No migrations, no model changes

### Test Gate (T1-T20)
- All PASS

### Protected Systems
- AI Planner / GLOBE-01..07 / Booking / Safety: ZERO touch ✅

---

## 📌 PROVIDER-ITINERARY-09 DISCOVERY — COMPLETE (2026-09-19)

**Status:** Report submitted. Awaiting Master Scope Lock.
**Git:** `feature/globe-system` @ `39f9a11` — unchanged.

### Master Plan Phase Status
- I–VI: ✅ CLOSED
- VII (Availability/Booking): 🔴 MISSING
- VIII (Reviews/Related): 🟡 PARTIAL
- IX: ✅ CLOSED (Phase 07)
- X (AI): 🔒 FUTURE

### 5 Phase 09 Candidates
- **A — Reviews + Related Enhancement:** data ready, UI gap, low-medium
- **B — Availability/Departure:** biggest, needs DB design
- **C — Provider Dashboard:** small, safe, UI-only
- **D — Booking Snapshot:** D5 resurface, 1 column
- **E — Media Enhancement:** F6 resurface, lightbox or thumbnails

---

## 📌 PROVIDER-ITINERARY-09 SCOPE LOCK (2026-09-19)

**Master decision:** A + C (sequential)
- **09A — Public Reviews + Related Enhancement** 🔒 LOCKED
- **09C — Provider Dashboard Integration** 🔒 LOCKED (after 09A)

### Deferred (Not Phase 09)
- B (Availability/Departure) → design discovery only
- D (Booking Snapshot) → HOLD, separate design
- E (Media Enhancement) → HOLD, separate ticket
- Journey Abstraction → HOLD

### Baseline
- HEAD: `39f9a11` (synced)
- Next: Phase 09A Read-Only Discovery

---

## 📌 PROVIDER-ITINERARY-09A — IMPLEMENTATION COMPLETE (2026-09-19)

**Status:** Implementation complete + tested. Awaiting Master COMMIT GO.
**Git:** `feature/globe-system` @ `39f9a11` — 7 files modified (uncommitted)

### Files Changed (7)
**New:**
- `resources/views/public/services/_reviews.blade.php`

**Modified:**
- `app/Http/Controllers/Public/ServiceController.php` (+23)
- `resources/views/public/services/show.blade.php` (+6/-2)
- `resources/lang/en/messages.php` (+8)
- `resources/lang/hi/messages.php` (+7)
- `resources/lang/np/messages.php` (+10)
- `resources/lang/zh/messages.php` (+7)

### What Shipped
- Paginated public reviews list (5/page, approved-only)
- Safe user display (name only, no PII)
- Related: same-provider OR same-category, deduped, active-only
- Hidden when 0 reviews
- Translation keys for 4 languages

### Test Gate T1-T12
- All PASS

---

## 📌 PROVIDER-ITINERARY-09A — CLOSED + PUSHED (2026-09-19)

**Commit:** `fc75f5f` — `feat(provider-itinerary): add public reviews and related services`
**Full hash:** `fc75f5f81d0f0f5fcac58677a8a34e45ad11df4d`
**Push range:** `39f9a11` → `fc75f5f`
**Remote:** `origin/feature/globe-system` (DigiSewaAI/TravelAI-Nepal)
**Sync:** Local == Remote ✅ (0 ahead / 0 behind)

### Files Committed (7)
**New:**
- `resources/views/public/services/_reviews.blade.php` (+45)

**Modified:**
- `app/Http/Controllers/Public/ServiceController.php` (+23)
- `resources/views/public/services/show.blade.php` (+6/-2)
- `resources/lang/en/messages.php` (+8)
- `resources/lang/hi/messages.php` (+7)
- `resources/lang/np/messages.php` (+10/-1)
- `resources/lang/zh/messages.php` (+7)

**Total:** 99 insertions, 7 deletions

### What Shipped
- Public paginated reviews list (5/page, approved-only)
- Safe user display (name only, no PII)
- Related services: same-provider OR same-category, deduped, active-only
- Hidden when 0 reviews (P3)
- Translation keys for 4 languages (en / hi / np / zh)

### Push Verification
- push: `39f9a11..fc75f5f  feature/globe-system`
- local == remote ✅ (0/0)
- main untouched: `110a54854dad46e59d163da008e165b54af7f89e`
- No force push, no amend

---

## 📌 PROVIDER-ITINERARY-09C DISCOVERY — COMPLETE (2026-09-19)

**Status:** Report submitted. Awaiting Master Scope Lock.
**Git:** `feature/globe-system` @ `fc75f5f` — unchanged.
**Mode:** READ-ONLY audit — zero file modifications.

### Key Findings
- **A. Provider Dashboard:** No itinerary shortcut exists.
- **B. Provider Services List:** No itinerary column, no link.
- **C. Authorization:** ServicePolicy `update` gate already protects itinerary.
- **D. Itinerary State:** `services.itinerary_status` (draft/published) exists.
- **E. Routes:** `provider.services.itinerary.index` already exists.
- **F. i18n:** Missing: `itinerary`, `manage_itinerary`, `draft`, `no_itinerary_yet`.
- **G. Responsive:** Existing table horizontal scroll = accepted behavior.
- **H. Query Impact:** `withCount('itineraryDays')` = 1 aggregate query. No N+1.

### Recommended Minimal Scope
- 6 files (ServiceController, services/index.blade.php, 4 translation files)
- No new routes, no migrations, no new authorization

---

## 📌 PROVIDER-ITINERARY-09C — MASTER SCOPE LOCK (2026-09-19)

**Master decision:** Provider Services List only.

### Master Decisions Applied (M1–M10)
- M1: Modify Provider Services List only. No dashboard/sidebar.
- M2: Itinerary column logic (No itinerary / Draft / Published)
- M3: Use `withCount('itineraryDays')`
- M4: Manage Itinerary link reuses existing route
- M5: No new authorization logic
- M6: Add 4 keys to all 4 locales
- M7: Reuse existing table/badge/link conventions
- M8: Preserve existing `overflow-x-auto`
- M9: OUT OF SCOPE — Dashboard, Sidebar, Pagination, etc.
- M10: Acceptance gate T1–T12 mandatory.

### Expected Files (6)
1. `app/Http/Controllers/Provider/ServiceController.php`
2. `resources/views/provider/services/index.blade.php`
3. `resources/lang/en/messages.php`
4. `resources/lang/np/messages.php`
5. `resources/lang/hi/messages.php`
6. `resources/lang/zh/messages.php`

---

## 📌 PROVIDER-ITINERARY-09C — IMPLEMENTATION COMPLETE (2026-09-19)

**Status:** Implementation complete + verified. Awaiting Master COMMIT GO.
**Git:** `feature/globe-system` @ `fc75f5f` — 6 files modified (uncommitted)

### Files Changed (6)
- `app/Http/Controllers/Provider/ServiceController.php` (+10/-6)
- `resources/views/provider/services/index.blade.php` (+39/-13)
- `resources/lang/{en,np,hi,zh}/messages.php` (+4 each)

**Total:** 54 insertions, 16 deletions

### Test Gate T1–T12
- 11 PASS + 1 SKIP (T10 zero-services — code guard verified)
- IDOR: foreign service → 403, own service → 200 OK
- Query: 4 total, subquery proven
- Mobile 375px: no overflow
- 4 locales: en/np/hi/zh all correct

### Known Observations (out of 09C scope)
- Itinerary editor page hardcoded English → ticket `PROVIDER-ITINERARY-I18N-01`
- "Back to service" link points to non-existent `show` route → `PROVIDER-ROUTES-HYGIENE-01`

---

## 📌 PROVIDER-ITINERARY-09C — CLOSED + PUSHED (2026-09-19)

**Commit:** `dcf7be8` — `feat(provider-itinerary): add itinerary status to provider services`
**Full hash:** `dcf7be8a233c242e9fac8b628bfd43416ffdb6b5`
**Push range:** `fc75f5f` → `dcf7be8`
**Remote:** `origin/feature/globe-system` (DigiSewaAI/TravelAI-Nepal)
**Sync:** Local == Remote ✅ (0 ahead / 0 behind)

### What Shipped
- Itinerary column in Provider Services List
- Three-state badge: No itinerary / Draft / Published
- Manage Itinerary link → existing route reuse
- 4 i18n keys × 4 locales

### Push Verification
- push: `fc75f5f..dcf7be8  feature/globe-system`
- local == remote ✅ (0/0)
- main untouched: `110a54854dad46e59d163da008e165b54af7f89e`

### Notes
- Chirwa Mid-Range Lodge user-deleted during T9 (1169→1168 services) — documented; not restored

---

## 📌 GLOBE/JOURNEY FINAL INTEGRATION AUDIT — COMPLETE (2026-09-19)

**Status:** Read-only audit complete. Awaiting Master product decision.
**Git:** `feature/globe-system` @ `dcf7be8` — unchanged.

### Key Findings
**GLOBE system — FUNCTIONAL (प्रमाणित):**
- Leaflet map — runtime PASS
- 77 districts — runtime PASS
- City markers — runtime PASS
- Route selector — runtime PASS
- District panel — runtime PASS
- 3D Globe — visually FUNCTIONAL
- Mobile 375px — कोई body-level horizontal overflow छैन

**Non-critical issues:**
- `three.js process is not defined` console error — NICE/hygiene
- Service Worker `addAll` failure — offline mode non-functional
- 11/16 hardcoded hero pin slugs → 404
- `PlannerService` = fallback-only (5092/5129 rows fallback_used=true)
- Provider itinerary → Explore/Globe/Journey = confirmed architectural gap

### Triage Summary
- 🔴 CRITICAL: NONE FOUND
- 🟡 NICE: 7 items
- ⚪ COSMETIC: 3 items

---

## 📌 D1 + D2 MASTER PRODUCT DECISIONS — LOCKED (2026-09-19)

**Status:** Product direction locked. No implementation authorized.

### D1 — AI PLANNER IDENTITY
**Decision:** TravelAI को "AI-powered Travel Planner" identity कायम रहनेछ।

**Current state acknowledged:**
- `PlannerService` fallback-only implementation
- `ItineraryGenerator` + `LlmService` (Groq) अलग अवस्थित
- दुई systems merge गरिएका छैनन्

---

## 📌 PROVIDER-ITINERARY-09B-R1 — BOOKING STATUS / SEAT SEMANTICS DISCOVERY — COMPLETE (2026-09-19)

**Status:** Read-only audit complete.
**Git:** `feature/globe-system` @ `dcf7be8` — unchanged. No code modification.

---

## 📌 PROVIDER-ITINERARY-09B-01 — DATABASE FOUNDATION COMPLETE (2026-09-19)

**Status:** Implementation + verification complete. Awaiting Master COMMIT GO.
**Git:** `feature/globe-system` @ `dcf7be8` — 4 files changed (uncommitted).

### Master Decisions Applied (D17–D26)
- D17: Seat semantics = Option B (pending reserves)
- D18: Capacity check = create + confirm
- D19: TTL = NO
- D20: BookingStatusTransitions unchanged
- D21: guest_count = INT UNSIGNED NOT NULL DEFAULT 1
- D22: departure deletion → bookings.departure_id ON DELETE SET NULL
- D23: Departure status = ENUM('scheduled','cancelled')
- D24: Itinerary gate deferred
- D25: Existing 31 bookings untouched
- D26: BookingLimitService protected

---

## 📌 PROVIDER-ITINERARY-09B-01 — CLOSED + PUSHED (2026-09-19)

**Commit:** `d5f36e4` — `feat(provider-itinerary): add departure database foundation`
**Full hash:** `d5f36e4ff8dcb60f9fb0fb845bf908311814be88`
**Push range:** `dcf7be8` → `d5f36e4`
**Remote:** `origin/feature/globe-system` (DigiSewaAI/TravelAI-Nepal)
**Sync:** Local == Remote ✅ (0 ahead / 0 behind)

### Files Committed (4)
- `database/migrations/2026_09_19_054143_create_departures_table.php` (+36)
- `database/migrations/2026_09_19_054152_add_departure_id_and_guest_count_to_bookings.php` (+31)
- `app/Models/Departure.php` (+35)
- `app/Models/Service.php` (+10/-1)

**Total:** 112 insertions, 1 deletion

### What Shipped
- `departures` table with schema
- `bookings.departure_id` nullable FK ON DELETE SET NULL
- `bookings.guest_count` INT UNSIGNED DEFAULT 1
- `Departure` model with relations
- `Service::departures()` hasMany relation

---

## 📌 PROVIDER-ITINERARY-09B-02 DISCOVERY — COMPLETE (2026-09-19)

**Status:** Read-only audit complete. Awaiting Master decisions (N1–N12).

### Key Findings
- Existing page: `provider/services/{service}/itinerary`
- Recommended location for Departures UI: **itinerary page मा tab वा section**
- `departures` table empty (0 rows)
- `bookings.departure_id` NULL across all 31 legacy bookings
- `Booking::$fillable` मा `departure_id` / `guest_count` **छैनन्**
- 4 locales मा नयाँ i18n keys थप्नुपर्ने
- `provider_staff = 0 rows`

### Master Decisions Required (N1–N12)
Departures UI location, itinerary gate, date validation, capacity, overlap, edit rules, cancel rules, delete rules, past departure, cancelled re-activate, i18n list

---

## 📌 PROVIDER-ITINERARY-09B-02 — IMPLEMENTATION COMPLETE (2026-09-19)

**Status:** Implementation + verification complete. Awaiting Master COMMIT GO.
**Git:** `feature/globe-system` @ `d5f36e4` — 9 files changed (uncommitted).

### Master Decisions Applied (N1–N12)
- N1: Departures in itinerary page
- N2: Published itinerary required for create
- N3: Unpublish does not auto-cancel departures
- N4: start_date >= today, end_date >= start_date
- N5: capacity >= 1, no maximum
- N6: Duplicate start_date blocked (application-level)
- N7: Future scheduled editable; past read-only; cancelled locked
- N8: Cancel = status-only
- N9: Hard delete only if no bookings
- N10: Past = derived read-only
- N11: Cancelled = terminal
- N12: 14 i18n keys × 4 locales

### Test Matrix T1–T8 — 8/8 PASS

---

## 📌 PROVIDER-ITINERARY-09B-02 — CLOSED + PUSHED (2026-09-19)

**Commit:** `eb5ce8a` — `feat(provider-itinerary): add provider departure management`
**Full hash:** `eb5ce8ad7922db09e23a5e10817366fe632411f5`
**Push range:** `d5f36e4` → `eb5ce8a`
**Remote:** `origin/feature/globe-system` (DigiSewaAI/TravelAI-Nepal)
**Sync:** Local == Remote ✅ (0 ahead / 0 behind)

### Files Committed (9)
**Modified (6):**
- `routes/web.php` (+69/-30)
- `resources/views/provider/services/itinerary/index.blade.php` (+59/-1)
- 4 translation files

**Created (3):**
- `app/Http/Controllers/Provider/DepartureController.php` (+162)
- `resources/views/provider/services/itinerary/_departure_form.blade.php` (+42)
- `resources/views/provider/services/itinerary/_departure_row.blade.php` (+71)

**Total:** 422 insertions, 30 deletions

### Test Gate
- T1-T8 runtime: 8/8 PASS
- T9-T31 acceptance: 25 PASS / 0 FAIL / 1 N/A / 2 NOT VERIFIED

---

## 📌 PROVIDER-ITINERARY-09B-03 DISCOVERY — COMPLETE (2026-09-19)

**Status:** Read-only audit complete. Awaiting Master decisions (P1–P8).

### Key Findings
- `Public\ServiceController::show()` — departures eager load छैन
- `show.blade.php` — 512 lines
- `Public\BookingController::create/store` — कोई `departure_id` field छैन
- Reusable i18n Keys from 09B-02 available

### Derived Display States
- Available | Sold Out | Past | Cancelled

---

## 📌 PROVIDER-ITINERARY-09B-03 — IMPLEMENTATION COMPLETE (2026-09-19)

**Status:** Implementation + verification complete. Awaiting Master COMMIT GO.
**Git:** `feature/globe-system` @ `eb5ce8a` — 7 files changed (uncommitted).

### Master Decisions Applied (P1–P8)
- P1: Section placement — Itinerary पछि, Related अघि
- P2: Filter — scheduled + end_date >= today only
- P3: Display — dates + duration + capacity + derived badge
- P4: Empty → section hide
- P5: Display-only, no booking CTA change
- P6: NO booking integration
- P7: 4 i18n keys × 4 locales; existing reuse
- P8: No calendar UI

### Test Matrix T1–T16 — 11 PASS / 1 N/A

---

## 📌 PROVIDER-ITINERARY-09B-03 — CLOSED + PUSHED (2026-09-19)

**Commit:** `3e73db0` — `feat(provider-itinerary): add public departure display`
**Full hash:** `3e73db0a69cb238f9cce45bad7e1a83f26624671`
**Push range:** `eb5ce8a` → `3e73db0`
**Remote:** `origin/feature/globe-system` (DigiSewaAI/TravelAI-Nepal)
**Sync:** Local == Remote ✅ (0 ahead / 0 behind)

### Files Committed (7)
**Modified (6):**
- `app/Http/Controllers/Public/ServiceController.php` (+~35/-~6)
- `resources/views/public/services/show.blade.php` (+4/-1)
- 4 translation files

**Created (1):**
- `resources/views/public/services/_departures.blade.php` (+68)

**Total:** 118 insertions, 16 deletions

### Test Matrix T1–T16
- 11 PASS + 1 N/A (T11 verified in 09C)

---

## 📌 PROVIDER-ITINERARY-09B-04 — R1 DISCOVERY COMPLETE (2026-09-21)

**Status:** Read-only audit complete. Awaiting Master Q1–Q8 decisions.
**Git:** `feature/globe-system` @ `3e73db0` — unchanged.

### Key Findings
- `Public\BookingController::store()` — DB::transaction wraps quota + booking create
- Public route middleware = `web` मात्र — **कोई throttle नै छैन**
- `Booking::$fillable` मा `departure_id` / `guest_count` छैनन्
- कोई `lockForUpdate` छैन current flow मा

### Critical Findings (8 Risks)
- 🔴 R1: Departure lock missing
- 🔴 R2: Admin updateStatus — कोई canTransition check छैन
- 🔴 R3: Public route — कोई throttle छैन
- 🔴 R7: कोई test coverage छैन
- 🟡 R4: bookings.status index छैन
- 🟡 R5: Legacy backward compat
- 🟡 R6: Provider+Admin race window
- 🟡 R8: Mass-assign guard careful

---

## 📌 PROVIDER-ITINERARY-09B-04 — R2 DESIGN VERIFICATION COMPLETE (2026-09-21)

**Status:** Read-only R2 verification complete. Ready for Implementation GO.

### Master Decisions Applied (Q1–Q8)
- Q1: Public booking throttle — YES
- Q2: Admin canTransition enforcement — YES
- Q3: Composite index (departure_id, status) — YES
- Q4: Departure mandatory only when eligible departures exist
- Q5: Legacy departure_id=NULL — skip departure checks
- Q6: guest_count server-side capacity authoritative
- Q7: Notification departure data — DEFER
- Q8: Quota vs capacity errors — SEPARATE messages

### Lock Order — FINAL
```
Departure → Booking → Provider quota
```

---

## 📌 PROVIDER-ITINERARY-09B-04 — IMPLEMENTATION COMPLETE (2026-09-21)

**Status:** Implementation + verification complete. Awaiting Master COMMIT GO.
**Git:** `feature/globe-system` @ `3e73db0` — 15 files changed (uncommitted).

### Files Changed (15)
**Modified (11):**
- `app/Models/Booking.php`
- `app/Http/Controllers/Public/BookingController.php`
- `app/Http/Controllers/Provider/BookingController.php`
- `app/Http/Controllers/Admin/BookingController.php`
- `app/Providers/AppServiceProvider.php`
- `routes/web.php`
- `resources/views/public/booking/create.blade.php`
- 4 lang files

**New (4):**
- 1 migration composite index
- 3 test files (CreateTest, SecurityAndLegacyTest, ConcurrencyTest)

**Total:** 242 insertions, 29 deletions

### Test Matrix T1–T21
- 19 PASS / 1 SKIP (T12) / 1 N/A (T18 mobile)

---

## 📌 PROVIDER-ITINERARY-09B-04 — CLOSED + PUSHED (2026-09-21)

**Commit:** `c9764ae` — `feat(provider-itinerary): add departure booking integration`
**Full hash:** `c9764ae7ab893e6d3099992436f57e5e6ca50efe`
**Push range:** `3e73db0` → `c9764ae`
**Sync:** Local == Remote ✅ (0/0)
**Note:** Pushed under OWNER DIRECTIVE (Master formal PUSH GO bypassed).

### Files Committed (15)
**Total:** 1030 insertions, 29 deletions

---

## 📌 ENUM HOTFIX — CLOSED + PUSHED (2026-09-21)

**Commit:** `3970163` — `fix(bookings): extend status enum to include rejected`
**Push range:** `c9764ae` → `3970163`
**Sync:** Local == Remote ✅ (0/0)

### Files (2)
- `database/migrations/2026_09_21_050546_extend_bookings_status_enum_rejected.php` (+51)
- `tests/Feature/Booking/CreateTest.php` (+28/-14)

**Total:** 79 insertions, 14 deletions

---

## 📌 TICKET A + B — CLOSED + PUSHED (2026-09-21)

### Ticket A — AI-SSL-VERIFY-01
**Commit:** `fba6b3d` — `fix(ai): enable SSL verification in production`
**Files:** LlmService.php, AiContentAnalysisService.php (+21/-11)

### Ticket B — AI-ANALYSIS-FIX-01
**Commit:** `e6fab12` — `fix(ai): repair content analysis service (url, model, quota)`
**Files:** AiContentAnalysisService.php (+144/-28)

### Combined Push
**Range:** `3970163` → `e6fab12`
**Sync:** Local == Remote ✅ (0/0)

---

## 📌 PHASE X-01 — AI-ASSISTED ITINERARY DRAFT — CLOSED + PUSHED (2026-09-21)

**Commit:** `9c97683` — `feat(provider): add AI-assisted itinerary draft generation`
**Full hash:** `9c97683550b5a01183f0c02944e57a569958deec`
**Push range:** `e6fab12` → `9c97683`
**Sync:** Local == Remote ✅ (0/0)

### Files Pushed (8)
- 2 new: controller + modal partial
- 6 modified: routes, view, 4 translations

### What Shipped
- Provider AI draft: button + modal + preview + apply
- 17 i18n keys × 4 locales
- Session-based draft (30 min TTL)
- Append-only insert
- throttle:ai on draft endpoint

### Milestone
✅ **Master Plan v1.0 — All phases complete (16 phases total)**

---

## 📌 F1 SAGA — CLOSED (2026-09-21)

**Status:** Warning commit shipped. Proper fix deferred to X-02.
**Commit:** `f786780` — warning UI only

---

## 📌 MERGE TO MAIN — CLOSED + PUSHED (2026-09-21)

**Merge:** feature/globe-system → main (fast-forward)
**Merge range:** 110a548 → f786780
**Push range:** 110a548..f786780
**Sync:** Local == Remote ✅ (0/0)

### Commits Merged (25)
All phases from feature/globe-system:
- GLOBE-01 to 07
- PROVIDER-ITINERARY-04 to 08
- 09A Reviews + Related | 09C Dashboard Link
- 09B-01 to 04 Departure + Booking
- Enum Hotfix (rejected)
- Ticket A (SSL, fba6b3d) | Ticket B (Analysis, e6fab12)
- Phase X-01 (AI draft, 9c97683)
- F1 Warning (f786780)

### Merge Stats
- 58 files changed (+6,435 / -121)
- 24 new | 34 modified
- 0 protected systems touched

---

## 📌 MAP PROVIDER + NEPAL LOCK — CLOSED + PUSHED (2026-09-21)

**Commit:** `a6e1259` — `feat(map): switch to OSM provider + Nepal viewport lock`
**Push range:** `f786780` → `a6e1259`
**Sync:** Local == Remote ✅ (0/0)

### Files Changed (2)
- `resources/views/public/services/index.blade.php` (+34/-17)
- `resources/views/public/services/show.blade.php` (+12/-5)

**Total:** +29/-17

### What Shipped
- OSM provider (commercial compliant, free, R21-R24)
- Nepal tint (rebalanced blue, visible)
- Nepal viewport lock (constructor options):
  - `maxBounds: [[26.3, 80.0], [30.5, 88.3]]`
  - `maxBoundsViscosity: 1.0` (hard stop, no bounce)
  - `minZoom: 7`
- Removed: Turf.js mask, custom English labels (Owner rejected)

### 3 LESSONS LEARNED (Session Record)
1. **Cumulative changes = cumulative regression**
2. **Leaflet constructor options > setter calls**
3. **Master holistic review required**

---

## 📌 FIX-AUDIT-01-CURRENCY — CLOSED + PUSHED (2026-09-22)

**Commit:** `d37890f` — `fix(booking): use CurrencyService for total price display`
**Full hash:** `d37890f22d7498eb389feebd4caaced4ccde7980`
**Push range:** `a6e1259` → `d37890f`
**Sync:** Local == Remote ✅ (0 ahead / 0 behind)

### Files Committed (1)
- `resources/views/public/booking/create.blade.php` (+8/-1)

**Total:** 8 insertions, 1 deletion

### What Shipped
- Hardcoded `Rs. {{ number_format($service->price, 0) }}` → `CurrencyService` dynamic conversion
- Session currency respected (USD / NPR)

---

## 🎫 TICKET: MAP-FULL-NEPAL-VIEW-01

**Priority:** 🟡 MEDIUM
**Status:** OPEN
**Created:** 2026-09-22
**Source:** Owner observation (public page test)

### Description
Public service page mini map shows only **partial Nepal** (waypoint fit).
For ABC trek, only ~15% of Nepal visible.

### Root Cause
`resources/views/public/services/show.blade.php` lines ~508-509:
```javascript
map.fitBounds(bounds, { padding: [40, 40], maxZoom: 12 });
```

---

## 📌 DUAL COMMIT — PROVIDER EDITOR UX + TOGGLE UX — CLOSED + PUSHED (2026-09-22)

### Commit 1: Provider Editor UX
**Hash:** `45c4ba2` — `feat(provider): improve itinerary editor UX (accordion + save next + warning)`
**Push range:** `d37890f` → `45c4ba2`

### Commit 2: Public Itinerary Toggle UX
**Hash:** `c3f9c94` — `feat(public): single itinerary toggle + placement + map z-index fix`
**Push range:** `45c4ba2` → `c3f9c94`

---

## 📌 SESSION CLOSED — DUAL COMMIT + 4 TICKETS (2026-09-22)

### Commits Pushed (3)
| # | Hash | Feature |
|---|---|---|
| 1 | d37890f | Currency fix (booking NPR/USD) |
| 2 | 45c4ba2 | Provider Editor UX |
| 3 | c3f9c94 | Public Toggle UX |

---

## 📌 MAP POLISH FINAL — FROZEN (2026-09-22)

**Status:** Ready to commit (Items 2+3+4+5 bundled)

### Applied Changes
- Item 2: Dedup markers (11 → 7)
- Item 3: minZoom 7 → 6
- Item 4: Map height 320px → 450px responsive
- Item 5: setView([28.40, 84.10], 7) — focused Nepal view

### Owner Approval
- Owner saw browser result: "map looks good"
- Map FROZEN — no further zoom/view tuning

---

## 📌 DEPLOY PLATFORM — OWNER NOTE (2026-09-23)

**Status:** UNDECIDED

### Options Under Consideration
1. Oracle Cloud Free Tier (earlier recommendation)
2. Laravel Cloud (Owner preference)

### Rule
Free-first applies — whatever platform, must be free or free-tier.

---

## 📌 PROVIDER-SERVICES-CURRENCY-DISPLAY-01 — CLOSED + PUSHED (2026-09-23)

**Commit:** `ad3f7cd` — `fix(provider): use CurrencyService for services list prices`
**Push range:** `21e035c` → `ad3f7cd`
**Sync:** Local == Remote ✅ (0/0)

### Files (1)
- `resources/views/provider/services/index.blade.php` (+12/-1)

### Session Commits (9 total)
1. d37890f — Currency fix (booking)
2. 45c4ba2 — Provider Editor UX
3. c3f9c94 — Toggle UX
4. a9a885e — Map bounds
5. 97e3f72 — Map Polish
6. cfa0c7c — Globe Phase 1
7. d87d50d — Globe Phase 2A
8. 21e035c — X-02 F1 Phase 1
9. ad3f7cd — Provider Currency Fix

---

## 📌 LOOP HOLE REVIEW — CLASSIFICATION (2026-09-23)

**Master Classification:**

| Tier | Tickets |
|---|---|
| Tier 1 (Deploy blockers) | NONE ✅ |
| Tier 2 (Next session) | X-03-ROUTE-DATA-INJECTION |
| Tier 3 (Post-MVP) | GLOBE-THREE-MODULES-01, GLOBE-TEXTURE-FALLBACK-01, GLOBE-FILE-STRUCTURE-01, GLOBE-RINGS-FILTER-01 |
| Tier 4 (Won't fix) | GLOBE-MOBILE-ZOOM-01 (tradeoff) |

---

## 📌 PHASE 3 — JOURNEY ANIMATION (2D MAP) — CLOSED + PUSHED (2026-09-23)

**Commit:** `622dbf6` — `feat(public): add journey animation 2D map + layout consistency (Phase 3)`
**Push range:** `3b22c0e` → `622dbf6`
**Sync:** Local == Remote ✅ (0/0)

### Files (9)
- `resources/views/public/services/show.blade.php` (M)
- `resources/views/public/services/_journey_animation.blade.php` (NEW)
- `docs/globe/Globe_Master_File.md` (NEW)
- `docs/globe/Globe_Execution_Log.md` (NEW)
- `docs/provider-itinerary/Current_Stage_And_All_Process.md` (M)
- 4 lang files (Phase 3 i18n keys)

**Stats:** +5025/-27

---

## 🎫 TICKET: I18N-INDENT-CLEANUP-01

**Priority:** 🟢 LOW
**Status:** OPEN (deferred)
**Created:** 2026-09-23

### Description
Lang files (messages.php × 4) have inconsistent indentation (16sp/4sp mixed).

### Fix
Standardize indentation across all 4 locale files.

### Effort
~30 min

---

## 🎫 TICKET: DOCS-CONSOLIDATION-01

**Priority:** 🟢 LOW
**Status:** OPEN (deferred)
**Created:** 2026-09-23

### Description
Commit project-wide reference docs (currently untracked).

### Fix
One bundle commit in future session.

### Effort
~15 min

---

## 📌 PHASE 4H — AI ITINERARY CHUNKING — CLOSED + PUSHED (2026-09-24)

**Commit:** `04c6e48` — `feat(provider): add AI itinerary chunking (Phase 4H)`
**Push range:** `fe3bb85` → `04c6e48`
**Sync:** Local == Remote ✅ (0/0)

### What Shipped
- Multi-request chunking (3-day chunks, ~900 tokens each)
- 60s sleep between chunks (OTPM window)
- Cross-chunk context: visitedEndpoints + visitedTitles
- Auto-retry orchestrator (max 2 attempts)
- Journey phase detection (ascend/summit/descend)
- Cross-chunk duplicate validation
- Progress UI

### Tests
- T1 (5-day) — PASS
- T2 (7-day) — PASS
- T3 (14-day) — PASS
- T8 (suite) — 41p/1f (no regression)

---

## 📌 PHASE 4H-FIX — time_of_day Sanitization — CLOSED + PUSHED (2026-09-24)

**Commit:** `0415925` — `fix(ai): sanitize time_of_day enum (Phase 4H-Fix)`
**Push range:** `04c6e48` → `0415925`
**Sync:** Local == Remote ✅ (0/0)

### Files (1)
- `app/Http/Controllers/Provider/AiItineraryDraftController.php` (+18/-3)

### What Shipped
- `apply()` — time_of_day enum sanitization
- `validateChunkStructure()` — items time_of_day validation

---

## 📌 NULL PRICE FIX — SESSION A APPLIED (2026-09-24, In Progress)

**Status:** Partial (3 of 6 files) — Session B pending

### Trigger
Test Tour (id=1233, price=NULL) crashed public pages

### Root Cause
`CurrencyService::convert()` null-unsafe signature

### Session A Applied (3 files)
1. `app/Services/CurrencyService.php` (Layer 1)
2. `resources/views/home.blade.php` (Site 1)
3. `resources/views/public/services/index.blade.php` (Site 2)

---

## 📌 PHASE 4M-1 — max_pax Migration — CLOSED + PUSHED (2026-09-25)

**Commit:** `362d019` — `feat(provider): add max_pax to detail tables (Phase 4M-1)`
**Push range:** `1203d98` → `362d019`
**Sync:** Local == Remote ✅

### Files (1)
- `database/migrations/2026_09_24_032603_add_max_pax_to_detail_tables.php` (NEW)

**Stats:** +34 insertions

### What Shipped
- `trek_details.max_pax` (int, nullable)
- `tour_details.max_pax` (int, nullable)
- Rollback tested + verified

### R19 Authorization
Granted (additive, nullable, no data loss)

---

## 📌 PHASE 4M-2-1 — Activity + Experience Tables — CLOSED + PUSHED (2026-09-25)

**Commit:** `8d1d4e2` — `feat(provider): add activity/experience detail tables (Phase 4M-2-1)`
**Push range:** `362d019` → `8d1d4e2`
**Sync:** Local == Remote ✅

### Files (2)
- `database/migrations/2026_09_24_075412_create_activity_details_table.php` (NEW)
- `database/migrations/2026_09_24_075413_create_experience_details_table.php` (NEW)

**Stats:** +50 insertions

### What Shipped
- `activity_details` table
- `experience_details` table (same structure)
- Rollback tested + re-migrated

---

## 📌 PHASE 4M-2-2 — Models — CLOSED + PUSHED (2026-09-25)

**Commit:** `7392e3b` — `feat(provider): add ActivityDetail/ExperienceDetail models (Phase 4M-2-2)`
**Push range:** `8d1d4e2` → `7392e3b`
**Sync:** Local == Remote ✅

### Files (5)
- `app/Models/ActivityDetail.php` (NEW)
- `app/Models/ExperienceDetail.php` (NEW)
- `app/Models/Service.php` (+2 relations)
- `app/Models/TrekDetail.php` (+max_pax fillable + cast)
- `app/Models/TourDetail.php` (+max_pax fillable + cast)

**Stats:** +64/-2

---

## 📌 NULL-FIX — Null Price Systemic — CLOSED + PUSHED (2026-09-25)

**Commit:** `e2cfe7e` — `fix(pricing): null-safe CurrencyService (NULL-FIX)`
**Push range:** `e577d3e` → `e2cfe7e`
**Sync:** Local == Remote ✅

### Files (7)
- `app/Services/CurrencyService.php` (Layer 1)
- `resources/views/home.blade.php` (Site 1)
- `resources/views/public/services/index.blade.php` (Site 2)
- `resources/views/public/services/category.blade.php` (Site 3)
- `resources/views/public/services/show.blade.php` (Sites 4+5)
- `resources/views/public/booking/create.blade.php` (Site 6)
- `resources/views/provider/services/index.blade.php`

**Stats:** +65/-60

### Fix (3 layers)
- Layer 1: Signature `float|int|null` + null coalescing
- Layer 2: Display guards (`N/A` for null prices)
- Layer 3: Test data cleanup (deleted id=1233)

### Tests
T1-T7 PASS (home, explore, service detail, category, related, booking, mobile)

---

## 📌 PHASE 4M-2-3+4 — Category-Aware Form — CLOSED + PUSHED (2026-09-25)

**Commit:** `c0cc382` — `feat(provider): category-aware service form + detail records (Phase 4M-2-3+4)`
**Push range:** `e2cfe7e` → `c0cc382`
**Sync:** Local == Remote ✅

### Files (12)
- `app/Http/Controllers/Provider/ServiceController.php`
- `resources/views/provider/services/create.blade.php`
- `resources/views/provider/services/edit.blade.php`
- 5 partials: `_fields_trek / _fields_tour / _fields_hotel / _fields_activity / _fields_experience`
- 4 i18n files (+7 keys each)

**Stats:** +355/-13

### What Shipped
- Category-aware form (JS toggle — 5 categories)
- Detail record creation on service store/update
- Amenities JSON transform
- Edit pre-fill support
- Vanilla JS (no Alpine, no library)

### Detail Fields per Category
- Trek: duration_days (required), difficulty (required), max_pax, max_altitude, season
- Tour: duration_days (required), max_pax
- Hotel: room_count, star_rating, amenities, check_in_time, check_out_time
- Activity: max_pax
- Experience: max_pax

### Tests
5/5 categories PASS

### R3
Zero invented fields (discovery-based)

---

## 📌 PHASE 4M FOUNDATION 100% COMPLETE (2026-09-25)

**Summary:** Provider service creation foundation fully functional.

### Achievement
- Provider creates any category → correct detail record saved
- Category-aware form (fields show/hide based on selection)
- Amenities JSON transform working
- Edit flow with pre-fill
- Null-price handling systemic

### Foundation-First Principle (Owner directive) — Validated
> "Service create 100% fix गरेपछि मात्र AI/Itinerary continue"

### Foundation enables AI quality
- Before: AI = 75% (name + description only)
- After: AI = richer inputs (duration, difficulty, altitude, max_pax)
- Expected: 85-90% (Phase 4K)

### Next
Phase 4M-3 (Provider Type ↔ Category Constraint)

---

## 🎫 TICKETS — Batch (2026-09-25)

### ✅ RESOLVED (Phase 4M Foundation)

**SERVICE-MAX-PAX-MIGRATION-01** — ✅ RESOLVED
  Commit: `362d019`
  max_pax added to trek_details + tour_details.

**SERVICE-DETAIL-TABLES-EMPTY-01** — ✅ RESOLVED
  Commit: `c0cc382`
  All 5 category detail tables now populated on service create.

**SERVICE-FORM-CATEGORY-AWARE-01** — ✅ RESOLVED
  Commit: `c0cc382`
  Category-aware form with JS toggle.

**SERVICE-EDIT-DETAILS-01** — ✅ RESOLVED
  Commit: `c0cc382`
  Edit form pre-fills detail records.

**NULL-PRICE-SYSTEMIC-01** — ✅ RESOLVED
  Commit: `e2cfe7e`
  3-layer null-safe fix (service + display + data cleanup).

### 🔴 HIGH PRIORITY (Active)

**PROVIDER-CATEGORY-CONSTRAINT-01** (IN PROGRESS — Phase 4M-3)
  Provider type ↔ Service category constraint.
  Decisions (Q1-Q5):
    Q1: Mapping column (provider_types.service_category_id)
    Q2: Multi-type DEFERRED
    Q3: Enforcement = BOTH (form + controller)
    Q4: Missing types → map to closest
    Q5: Custom type → all categories allowed
  Sub-phases: 4M-3-1 → 4M-3-2 → 4M-3-3 → 4M-3-4
  Status: 4M-3-1 migration approved, implementation pending.

### 🟡 MEDIUM PRIORITY

**EXPLORE-TOUR-DURATION-DISPLAY-01**
  Explore page shows "— days" for Tour services
  even when tour_details.duration_days is populated.
  Reason: Explore query likely doesn't join tour_details.
  Fix: Enhance query + display logic.
  Effort: ~30 min.
  Phase: Post-4M-3.

**CATEGORY-COUNT-DELTA-01**
  Hotel/Trek test services not visible in category counts.
  Possible: pagination or filter logic.
  Effort: ~20 min.
  Phase: Post-4M-3.

**AI-QUOTATION-FORM-INCOMPLETE-01**
  Quotation form missing: days, pax, start_date, accommodation.
  Fix: Add form fields + update prompt.
  Effort: 2 hrs.
  Phase: 4I.

**AI-ARCHITECTURE-UNIFY-01**
  4 hardcoded models. `.env GROQ_MODEL` ignored.
  Fix: Centralize model selection.
  Effort: 1-2 hrs.
  Phase: 4I.

**AI-MULTIPROVIDER-ROTATION-01**
  Multiple free AI providers (Groq + OpenRouter + Cerebras).
  Effort: 2-3 hrs.
  Phase: 4J.

**AI-ITINERARY-CONTENT-QUALITY-01**
  AI-generated itinerary content ~75% accurate.
  Fix: Prompt + validation.
  Expected: 75% → 90%.
  Effort: ~2 hrs.
  Phase: 4K.

**AI-PUBLIC-PLANNER-VERIFY-01**
  Public AI Planner = DB-driven (not actual AI).
  Verify + decide (rename OR upgrade).
  Effort: Verify.
  Phase: 4I or later.

**ELEVATION-DATA-GAP-01**
  elevation_gain_m/loss_m NULL. altitude_m populated.
  Fix: Provider UI to populate.
  Effort: 2-4 hrs.
  Phase: Post-4M.

**WEATHER-SERVICE-LEGACY-FIX-01**
  Legacy WeatherService uses OpenWeatherMap (paid-pattern).
  Safety dependency (R6).
  Fix: Migrate to Open-Meteo OR remove dead code.
  Effort: 2-3 hrs.
  Phase: Post-deploy.

### 🟢 LOW PRIORITY

**PROVIDER-MULTI-TYPE-01** (Deferred from 4M-3)
  Multi-type provider support.
  Current: 1 type at registration.
  Schema ready (pivot exists).
  Phase: Post-MVP.

**SERVICE-CATEGORY-SPLIT-01** (Deferred from 4M-3)
  Split "hotel" category → hotel/resort/lodge/homestay.
  Currently: all map to hotel.
  Phase: Post-MVP.

**PROVIDER-TYPE-I18N-01** (New from 4M-3)
  Provider types = English only.
  Future: 4-locale translations.
  Phase: Post-MVP.

**CUSTOM-PROVIDER-TYPE-POLICY-01** (New from 4M-3)
  Custom "other" type behavior = all categories allowed.
  Future: explicit policy UI.
  Phase: Post-MVP.

**SERVICE-NULL-PRICE-UX-01** (New from NULL-FIX)
  Sites 2+5: N/A shows in blue badge/font styling.
  Fix: Conditional span class for N/A (gray italic).
  Effort: 15 min.
  Phase: Post-4M.

**TREK-DIFFICULTY-I18N-01** (New from 4M-2-3+4)
  Hardcoded Easy/Moderate/Hard.
  Fix: i18n keys + 4 locales.
  Effort: 15 min.
  Phase: Post-4M.

**SERVICE-CATEGORY-CHANGE-CLEANUP-01** (New from 4M-2-3+4)
  Old detail orphaned on category change.
  Acceptable (data preservation).
  Effort: 30 min.
  Phase: Deferred.

**SUNSET-INCONSISTENCY-01**
  Public vs dashboard sunset differs ~19 min.
  Likely cache timing.
  Effort: 30 min verify.

**I18N-DUPLICATE-KEY-01**
  weather_unavailable × 3 duplicates in 4 locales.
  Fix: Audit + deduplicate.
  Effort: 30 min.

**I18N-FALLBACK-PATTERN-01**
  `__('key') ?? 'fallback'` = broken pattern.
  Fix: Audit + fix instances.
  Effort: 1 hr.

**I18N-INDENT-CLEANUP-01** (Updated)
  Inconsistent indentation across lang files +
  AiItineraryDraftController.php (2 sites).
  Fix: Standardize.
  Effort: 30 min.

**DOCS-CONSOLIDATION-01**
  Untracked docs to commit:
    • TravelAI_Nepal_Master_Handoff_AZ_v4.0.md
    • Master_Plan_v1.0.md
    • FIX-*.md (7 files)
  Fix: Single bundle commit.
  Effort: 15 min.

**AI-CONTENT-ANALYSIS-DEAD-CODE-01**
  AiContentAnalysisService — no callers.
  Fix: Delete or document.
  Effort: 15 min.

**MASTER-AI-MD-CREATE-01**
  Create `docs/globe/master_ai.md` — complete AI guide.
  Effort: 1-2 hrs.
  Phase: Post-4M.

**X-03-ROUTE-DATA-INJECTION**
  Tier 2.
  Effort: 1-2 hrs.

**GLOBE-* (6 tickets)**
  Post-MVP.

---

## 📅 MASTER HANDOVER PLAN (Continuity)

**If Master character limit completes:**
1. New Assistant reads this file + Globe_Master_File.md + Globe_Execution_Log.md
2. Assumes Master role (technical + product + priority)
3. Enforces R1-R24 strictly (self + new assistant)
4. Uses same briefing format for next Assistant handover
5. Preserves audit trail (3 files)

**Trigger:** Owner initiates "handover देऊ" OR Master limit flagged.

---

## 📊 CURRENT STATE (2026-09-25)

```
HEAD:    c0cc382 (synced)
Tests:   41 passed / 1 failed (pre-existing Safety)
Sync:    0/0
```

**Last Phase Complete:** 4M-2-3+4 (c0cc382)
**Current Phase:** 4M-3 (Provider type constraint)
**Next Phases:** 4I, 4J, 4K, Deploy

---

**Document End — Provider Itinerary Continuity v2.1**
**Updated:** 2026-09-25 — Phase 4M foundation closure + 4M-3 in progress


---

## 🎫 TICKETS — Session Update (2026-09-25 Evening)

### ✅ RESOLVED (This Session)

**PROVIDER-CATEGORY-CONSTRAINT-01** — ✅ RESOLVED
  Commit: `66eac2b`
  Adaptive UI + backend 403 + legacy bypass + custom fallback.
  10 categories + many-to-many pivot.

**SERVICE-CATEGORY-SPLIT-01** — ✅ RESOLVED
  Commit: `c336559`
  Resort, Lodge, Homestay added as separate categories.

**AI-QUOTATION-BROKEN-01** — ✅ RESOLVED
  Commit: `605dfd3`
  Hardcoded model param removed. AI Quotation working.

**AI-ARCHITECTURE-UNIFY-01** — ✅ RESOLVED (in-scope with 4I)
  Commit: `605dfd3`
  Hardcoded models removed. Config-driven (`.env GROQ_MODEL`).

**AI-LLMSERVICE-FALLBACK-FIX-01** — ✅ RESOLVED
  Commit: `605dfd3`
  LlmService default → `qwen/qwen3.8-27b`.

### 🔴 HIGH PRIORITY (Active)

**AI-ITINERARY-CONTENT-QUALITY-01** (Phase 4K — Next)
  AI itinerary content ~75% accurate.
  Hallucinations: fake places, side-trek confusion, route errors.
  Fix: Prompt enhancement + post-validation.
  Expected: 75% → 90%.
  Effort: ~2 hrs.

### 🟡 MEDIUM PRIORITY

**JOURNEY-REPLAY-MODEL-DISCOVERY-01** (NEW — LOW priority deferred)
  T2 test revealed: `App\Models\JourneyReplay` not found.
  Table `journey_replays` doesn't exist.
  Model assumption incorrect.
  Discovery needed: actual replay storage model.
  Effort: 15 min.
  Phase: Post-4I cleanup.

**AI-MULTIPROVIDER-ROTATION-01** (Phase 4J)
  Currently only Groq (single point of failure).
  Add: OpenRouter free + Cerebras free.
  Load distribution + failover.
  Effort: 2-3 hrs.

**AI-QUOTATION-FORM-INCOMPLETE-01**
  Quotation form missing: days, pax, start_date, accommodation.
  Fix: Add fields + update prompt.
  Effort: 2 hrs.

**AI-REPLAY-AI-STORY-BROKEN-01**
  Journey Replay AI story verification pending.
  Depends on JOURNEY-REPLAY-MODEL-DISCOVERY-01.
  Effort: 15 min (after discovery).

**AI-PUBLIC-PLANNER-VERIFY-01**
  Public AI Planner = DB-driven (verified working).
  Owner decision: Keep name "AI Planner", improve in-place.
  No action needed now.

**ELEVATION-DATA-GAP-01**
  elevation_gain_m/loss_m NULL. altitude_m populated.
  Fix: Provider UI to populate.
  Effort: 2-4 hrs.

**WEATHER-SERVICE-LEGACY-FIX-01**
  Legacy WeatherService uses OpenWeatherMap (paid-pattern).
  Safety dependency (R6).
  Fix: Migrate to Open-Meteo OR remove dead code.
  Effort: 2-3 hrs.

### 🟢 LOW PRIORITY

**SERVICE-NULL-PRICE-UX-01**
  N/A displays in blue styling (sites 2, 5). Cosmetic.
  Effort: 15 min.

**TREK-DIFFICULTY-I18N-01**
  Hardcoded Easy/Moderate/Hard.
  Effort: 15 min.

**SERVICE-CATEGORY-CHANGE-CLEANUP-01**
  Orphaned detail records on category change. Acceptable behavior.
  Effort: 30 min.

**I18N-DUPLICATE-KEY-01**
  weather_unavailable × 3 duplicates in 4 locales.
  Effort: 30 min.

**I18N-FALLBACK-PATTERN-01**
  `__('key') ?? 'fallback'` broken pattern.
  Effort: 1 hr.

**I18N-INDENT-CLEANUP-01** (Updated)
  Inconsistent indentation across 4 lang files + 2 controller sites.
  Effort: 30 min.

**SUNSET-INCONSISTENCY-01**
  Public vs dashboard sunset differs ~19 min.
  Effort: 30 min verify.

**DOCS-CONSOLIDATION-01**
  Untracked docs (7+ files) to commit.
  Effort: 15 min.

**AI-CONTENT-ANALYSIS-DEAD-CODE-01**
  AiContentAnalysisService — no callers.
  Effort: 15 min.

**MASTER-AI-MD-CREATE-01**
  Create `docs/globe/master_ai.md` — AI guide A-Z.
  Effort: 1-2 hrs.

**X-03-ROUTE-DATA-INJECTION**
  Tier 2.
  Effort: 1-2 hrs.

**GLOBE-* (6 tickets)**
  Post-MVP.

---

## 📊 CURRENT STATE (2026-09-25 Evening)


---

## 🎫 TICKETS — Session Update (2026-09-25 Final)

### ✅ RESOLVED (This Session)

**AI-REPLAY-AI-STORY-BROKEN-01** — ✅ RESOLVED
  Commit: `edeb933`
  Journey Replay AI story working. Root cause = LlmService forced
  JSON mode (reasoning model fails). Two-layer fix applied.

**JOURNEY-REPLAY-MODEL-DISCOVERY-01** — ✅ CLOSED
  Architecture confirmed: cache-based (no DB model/table).
  5-min TTL, on-the-fly generation via LlmService.

### 🟡 MEDIUM PRIORITY (Phase 4K — Next)

**AI-JOURNEY-STORY-GROUNDING-01** (NEW — Phase 4K)
  ChatGPT concern (valid): AI merges multiple unrelated trips
  into single continuous narrative.
  Example: ABC (Aug) + EBC (Sep) + Hotel (Sep)
  → Output: "journey began in Nayapul... concluded in Pokhara"
  → Misleading single-journey framing

  3-LAYER FIX ARCHITECTURE (Approved):
    L1 — Hard Facts (DB only): dates, places, check-ins, coords
    L2 — AI Interpretation: tone, transitions, narrative
    L3 — Safety Validation: reject invented/merged facts

  PRINCIPLE:
    "AI can describe beautifully, but never invent the journey."

  Effort: 2-3 hrs
  Phase: 4K (merge with AI-ITINERARY-CONTENT-QUALITY-01)

**AI-ITINERARY-CONTENT-QUALITY-01** (Phase 4K — Combined)
  AI itinerary content ~75% accurate.
  Hallucinations: fake places, side-trek confusion, route errors.
  Fix: Prompt enhancement + post-validation.
  Expected: 75% → 90%.
  Effort: ~2 hrs.

**Combined Phase 4K scope:**
  - AI Itinerary accuracy (75% → 90%)
  - Journey Replay story grounding
  - Cross-cutting validation (places, dates, categories)
  Total: ~4-5 hrs

### 🟢 LOW PRIORITY (Deferred)

**AI-QUOTATION-FORM-INCOMPLETE-01**
  Quotation form missing: days, pax, start_date, accommodation.
  Effort: 2 hrs.
  Phase: Post-4K.

**AI-MULTIPROVIDER-ROTATION-01**
  Currently only Groq. Add OpenRouter + Cerebras (free).
  Effort: 2-3 hrs.
  Phase: 4J (after 4K).

**AI-PUBLIC-PLANNER-VERIFY-01**
  Public AI Planner = DB-driven (working).
  Owner decision: Keep name, improve in-place (last).
  No action needed now.

**ELEVATION-DATA-GAP-01**
  elevation_gain_m/loss_m NULL.
  Fix: Provider UI to populate.
  Effort: 2-4 hrs.
  Phase: Post-4K.

**WEATHER-SERVICE-LEGACY-FIX-01**
  Legacy WeatherService uses OpenWeatherMap (paid-pattern).
  Fix: Migrate to Open-Meteo OR remove dead code.
  Effort: 2-3 hrs.
  Phase: Post-deploy.

**SERVICE-NULL-PRICE-UX-01**
  N/A displays in blue styling. Cosmetic.
  Effort: 15 min.

**TREK-DIFFICULTY-I18N-01**
  Hardcoded Easy/Moderate/Hard.
  Effort: 15 min.

**SERVICE-CATEGORY-CHANGE-CLEANUP-01**
  Orphaned detail records on category change.
  Effort: 30 min.

**I18N-DUPLICATE-KEY-01**
  weather_unavailable × 3 duplicates in 4 locales.
  Effort: 30 min.

**I18N-FALLBACK-PATTERN-01**
  `__('key') ?? 'fallback'` broken pattern.
  Effort: 1 hr.

**I18N-INDENT-CLEANUP-01**
  Inconsistent indentation across lang files + 2 controller sites.
  Effort: 30 min.

**SUNSET-INCONSISTENCY-01**
  Public vs dashboard sunset differs ~19 min.
  Effort: 30 min verify.

**DOCS-CONSOLIDATION-01**
  Untracked docs (7+ files) to commit.
  Effort: 15 min.

**AI-CONTENT-ANALYSIS-DEAD-CODE-01**
  AiContentAnalysisService — no callers.
  Effort: 15 min.

**MASTER-AI-MD-CREATE-01**
  Create `docs/globe/master_ai.md` — AI guide A-Z.
  Effort: 1-2 hrs.

**X-03-ROUTE-DATA-INJECTION**
  Tier 2.
  Effort: 1-2 hrs.

**GLOBE-* (6 tickets)**
  Post-MVP.

---

## 📊 CURRENT STATE (2026-09-25 Final)

---

### 🎫 AI-MULTIPROVIDER-ROTATION-01 — ENRICHED (Phase 4J)

**Priority:** 🟡 MEDIUM → 🟢 HIGH (before scale)
**Phase:** 4J (after Phase 4K)
**Effort:** 2-3 hrs
**Owner Directive:** "500 travelers ले use गर्न मिल्ने बनाउनु"
**Free-First Rule:** R21-R24 कायम (कोई paid provider कहिल्यै)

#### PROBLEM STATEMENT

Current state:
  • Only Groq free tier (single point of failure)
  • Groq free tier rate limit: ~14,400 requests/day
  • 500 travelers × ~10 requests/day = 5,000 (OK baseline)
  • BUT spike scenario (all users same time) = rate limit hit
  • No failover if Groq down

Target:
  • Support 500+ concurrent travelers
  • Zero downtime on provider failure
  • Stay 100% free-tier

#### FREE PROVIDERS (CANDIDATES)

  ✅ Groq (current) — free tier, no card, working
  🟡 OpenRouter — free models (Llama, Mistral, Qwen)
  🟡 Cerebras — free tier (verify availability 2026)
  🟡 Together AI — free tier (verify)
  🟡 Any new free tier provider

**RULES:**
  ❌ No paid plan
  ❌ No credit card
  ❌ No free-trial-then-paid
  ✅ Free forever only

#### SOLUTION ARCHITECTURE

  1. Provider Pool
     • Multiple LlmService-like clients
     • Each = separate API key + endpoint
     • Config-driven (.env: provider list)

  2. Load Distribution
     • Round-robin OR
     • Hash by user_id (sticky per user) OR
     • Least-recently-used
     • Balancer class: MultiProviderLlmService

  3. Failover Logic
     • Try provider A → rate limit → provider B
     • Try B → failure → provider C
     • All fail → friendly error + retry delay

  4. Rate Limit Tracking
     • Per-provider counter (Redis/file cache)
     • Respect free-tier limits
     • Auto-rotate when threshold hit

  5. Config (.env)
     • AI_PROVIDERS=groq,openrouter,cerebras
     • GROQ_API_KEY=...
     • OPENROUTER_API_KEY=...
     • CEREBRAS_API_KEY=...
     • All free-tier keys (Owner provides)

#### AFFECTED FILES (Provisional)

  NEW:
    • app/Services/AI/MultiProviderLlmService.php
    • app/Services/AI/Providers/GroqProvider.php
    • app/Services/AI/Providers/OpenRouterProvider.php
    • app/Services/AI/Providers/CerebrasProvider.php
    • app/Services/AI/ProviderBalancer.php

  MODIFY:
    • app/Services/LlmService.php (delegate to balancer)
    • config/services.php (provider pool)
    • .env.example (new provider keys)

  UNTOUCHED:
    • All callers (transparent — same interface)
    • Provider-side features
    • Public site

#### SUCCESS CRITERIA

  ✅ 500+ concurrent users = no rate limit errors
  ✅ Provider failure = auto failover (no user-visible error)
  ✅ All providers = free tier
  ✅ Zero cost added
  ✅ No regression (41p/1f baseline maintained)

#### TEST PLAN (Post-Implementation)

  T1: Single provider (Groq) → works (baseline)
  T2: Simulate Groq rate limit → OpenRouter auto-used
  T3: Simulate all providers → friendly error
  T4: Load test (mock 500 users) → no crash
  T5: Zero cost verification → no paid keys used
  T6: Suite 41p/1f

#### PRIORITY ORDER

  After Phase 4K (content quality)
  Before final Deploy
  Before 500-user launch

---

---

## 🎫 TICKETS — Session Update (2026-09-25 Evening Final)

### ✅ RESOLVED (This Session)

**AI-DRAFT-CHUNKING-STALL-01** — ✅ RESOLVED
  Commit: `76d5b7d` (4H-EXT-2)
  time_of_day sanitize + OTPM backoff (5s → 45s).

**AI-DRAFT-CHUNKING-TIMING-01** — ✅ RESOLVED
  Commit: `51a33ae` (4H-EXT)
  11.5 min → 3m 45s (67% faster).

**CANCEL-BUTTON-NONFUNCTIONAL-01** — ✅ RESOLVED
  Commit: `e8a586f` (UI-FIX)
  AbortController + cancelAiDraft() cleanup.

### 🟡 MEDIUM PRIORITY (Active)

**AI-DRAFT-RATE-LIMIT-TUNING-01** (NEW)
  Concern: sleep(40) between chunks insufficient (every chunk hits rate limit)
  Fix: sleep(40) → sleep(55) at line 674 (generateAllChunks)
  Effort: 5 min
  Phase: Post-service-close

**AI-DRAFT-ROUTE-ACCURACY-01** (Phase 4K)
  Days 11-13 hallucinations:
    • Chhusang (Upper Mustang restricted — off AC route)
    • Kaski (district name, not town)
    • Tatopani missing (classic AC stop)
  Fix: Route-specific validation + prompt enhancement
  Effort: ~2-3 hrs
  Phase: 4K (deferred)

**AI-DRAFT-CROSS-CHUNK-DUPES-01** (Phase 4K)
  Duplicate titles/endpoints detected across chunks
  Fix: Prompt tuning
  Effort: ~30 min
  Phase: 4K

**AI-ARCHITECTURE-UNIFY-02** (hardcoded model line 717)
  Hardcoded model remaining in AiItineraryDraftController
  Fix: config-driven
  Effort: 5 min
  Phase: Post-service-close

**AI-MULTIPROVIDER-ROTATION-01** (Phase 4J — NEXT)
  Multi-provider rotation (Groq + OpenRouter + Cerebras)
  500+ travelers scale target
  Fix: Provider pool + balancer + failover
  Effort: 2-3 hrs
  Phase: 4J

### 🟢 LOW PRIORITY

**AI-DRAFT-PROGRESS-SYNC-01** (NEW)
  Client progress timer vs server actual timing mismatch
  Fix: Sync with server chunk events
  Effort: ~1 hr
  Phase: Post-MVP

**AI-DRAFT-ASYNC-QUEUE-01**
  Async queue + notification (best UX)
  Effort: Large (queue setup)
  Phase: Post-deploy

**AI-DRAFT-PROGRESSIVE-SAVE-01**
  Progressive save (Day 1-3 available while 4-6 generating)
  Effort: Frontend + backend
  Phase: Post-MVP

---

## 📊 CURRENT STATE (2026-09-25 Evening Final)
