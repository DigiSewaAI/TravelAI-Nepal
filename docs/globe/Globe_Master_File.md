# 🌍 TravelAI Nepal — Globe Master File

**Version:** 2.0 (Reality-Aligned Rewrite)
**Created:** 2026-09-22
**Rewritten:** 2026-09-23
**Updated:** 2026-09-25 (Phase 4M foundation closure)
**Status:** 🟡 DRAFT — Pending STAGE 1-8 discovery verification
**Owner:** Parashar Regmi
**Master:** DeepSeek (Master role)
**Assistant:** DeepSeek (Implementation role)
**HEAD:** c0cc382

> **Single source of truth for TravelAI Nepal — vision, existing systems, phases, and rules.**

---

## 📋 0. HOW TO USE THIS FILE

### For New Session
1. Read this file completely
2. Verify current Git state (`git log -1`)
3. Cross-check Phase Ledger (§6) against reality
4. Check Existing Systems Inventory (§14) — know what's built
5. Wait for Master directive

### For Master
- Reference this file for scope decisions
- Update Phase Ledger after each closure
- Update Existing Systems Inventory when new systems ship
- Keep vision aligned, execution realistic

### For Assistant
- Follow phase order strictly
- No scope expansion without Master GO
- Report after each phase
- **Reality > Documented plan** — if code says X, update file

### ⚠️ Document Integrity Rule
> Master File = living document। Reality ले file भन्दा फरक भन्यो भने — **file update गर्नु** (code touch नगरी)।

---

## 🎯 1. VISION

### Identity
> **"Google Earth होइन — Nepal Journey Intelligence."**

### Core Principle
> **"If a user can search it, TravelAI should locate it, show it on the Globe, and explain everything relevant about it."**

### Value Chain
```
Where → What → When → How high → What's next → What I experienced
```

### Positioning
- ❌ Google Earth clone नहीं
- ✅ Nepal-focused journey platform
- ✅ Free-first (no paid services ever)
- ✅ Progressive disclosure
- ✅ Foundation → Discovery → Journey → Live → Memory
- ✅ **Multi-surface**: Public site + Traveler Dashboard + Provider Dashboard + Admin

---

## 🔒 2. FREE-FIRST CONSTRAINTS (R21-R24)

### Allowed (Free Forever)
| Service | Use |
|---|---|
| Leaflet | 2D map |
| Globe.gl | 3D globe |
| OpenStreetMap | Base tiles |
| Open-Meteo | Weather (no API key) |
| OpenTopoMap | Elevation tiles |
| Groq Free Tier | AI |
| Gmail SMTP | Email |
| Oracle Cloud Free | Hosting (future) |
| Let's Encrypt | SSL |
| Turbo/Stimulus | JS (no Alpine) |
| Vite | Build |

### Forbidden (Paid/Commercial-Restricted)
| Service | Reason |
|---|---|
| Google Earth | Paid |
| Mapbox | Paid |
| CartoDB commercial | Restricted |
| Wikimedia Maps | Non-commercial only |
| Stadia commercial | Paid |
| Any paid API | R21 violation |
| Any commercial plugin | R21 violation |

---

## 🏗️ 3. ARCHITECTURE (Multi-Layer)

### Four Surfaces
```
┌────────────────────────────────────────────────────┐
│              🌍 TRAVELAI NEPAL                     │
├────────────────────────────────────────────────────┤
│                                                    │
│  1️⃣ PUBLIC SITE          → Discover + Explore      │
│     • Globe (Discover Mode)                        │
│     • Journey Mode (package view)                  │
│     • Search + Fly-to                              │
│     • Public service pages                         │
│                                                    │
│  2️⃣ TRAVELER DASHBOARD   → My Journey              │
│     • Bookings + QR check-in                       │
│     • Live journey tracking                        │
│     • Photo memories                               │
│     • Journey Replay (cinematic)                   │
│     • SOS / Safety panel                           │
│                                                    │
│  3️⃣ PROVIDER DASHBOARD   → My Services             │
│     • Service CRUD + itinerary editor              │
│     • Booking management                           │
│     • Media upload                                 │
│                                                    │
│  4️⃣ ADMIN               → Platform Control         │
│     • User + provider management                   │
│     • Safety incident oversight                    │
│     • AI quota monitoring                          │
│                                                    │
└────────────────────────────────────────────────────┘
```

### Discover Mode vs Journey Mode (Public Site)
```
┌─────────────────────────────────────┐
│         🌍 TRAVELAI GLOBE            │
├─────────────────────────────────────┤
│  DISCOVER MODE   │   JOURNEY MODE   │
│  (Explore)       │   (Package view) │
├──────────────────┼──────────────────┤
│  Cities          │  Day-by-day      │
│  Treks           │  Route animation │
│  Districts       │  Waypoints       │
│  Destinations    │  Elevation       │
│  Providers       │  Photos          │
│  Layer toggle    │  Weather         │
└──────────────────┴──────────────────┘
```

### Centralized Location Intelligence
```
                    SEARCH
                      │
                      ↓
             Location Intelligence
                      │
        ┌─────────────┼─────────────┐
        ↓             ↓             ↓
      GLOBE         2D MAP       DETAILS
        │             │             │
        └─────────────┼─────────────┘
                      ↓
                Travel Services
```

### Data Source of Truth
- **Coordinates:** DB (`waypoints`, `locations`, `services`)
- **Routes:** `route_segments` (authoritative) — NOT `routes.segments` JSON
- **Districts:** `public/map/nepal-districts.topojson`
- **Weather:** Open-Meteo API (cached)
- **Photos:** Provider uploads (`service_itinerary_day_media`)
- **Check-ins:** `qr_scans` table (verified via STAGE 2)
- **SOS:** `sos_alerts` table (verified via STAGE 3)
- **Safety:** `travel_safety_incidents`, `safety_sources` (verified via STAGE 5)

---

## 🛡️ 4. PROTECTED SYSTEMS (R6)

**NEVER modify without explicit Master authorization:**

| System | Files |
|---|---|
| AI Planner | `PlannerService`, `ItineraryGenerator`, `ItineraryValidator` |
| AI Quota | `AiReservationService`, `AiLimitService` |
| GLOBE-01..07 | `MapDataController`, `routes/api.php` GLOBE endpoints |
| Public Explore | `resources/views/public/services/index.blade.php` |
| District TopoJSON | `public/map/nepal-districts.topojson` |
| Booking | `BookingStatusTransitions`, `BookingLimitService` |
| Safety | All safety system files |
| SOS | All SOS files (verify via STAGE 3) |
| QR Check-in | All QR files (verify via STAGE 2) |
| Journey Replay | All replay files (verify via STAGE 6) |
| Subscription/Payment | All billing files |

---

## 🗺️ 5. PHASE ROADMAP (Reality-Based)

### ✅ COMPLETED PHASES

#### Phase 1 — Foundation Fix ✅
- Globe professional appearance
- Console clean
- Responsive globe
- Nepal focus

#### Phase 2A / 2B — Discovery Mode ✅
- Nepal highlight + city markers
- Category filters (Treks/Tours/Hotels)
- Districts toggle
- Route highlighting
- Search bar + Fly-to

#### Phase 3 — Journey Animation ✅
- Package → Globe zoom
- Day-by-day playback
- Camera animation
- Waypoint info panel
- Play/Pause/Prev/Next

#### Phase 4A / 4B / 4C — Rich Experience ✅
- Weather (Open-Meteo) + cache
- Waypoint photo gallery
- Elevation profile
- Accommodation + activities

#### Phase 4D — Sunrise/Sunset ✅
- Sunrise/sunset times
- Final polish pass

#### Phase 4E — Dashboard Rich Data ✅
- Dashboard rich data integration
- Pushed: `9b92e2b` (2026-09-23)

#### Phase 4G — Provider AI Fix ✅
- Provider AI fixes
- Pushed: `cdf289e` (2026-09-24)

#### Phase 4H — AI Itinerary Chunking ✅
- Multi-request chunking (3-day chunks, ~900 tokens each)
- 60s sleep between chunks (OTPM window)
- Cross-chunk context: visitedEndpoints + visitedTitles
- Auto-retry orchestrator (max 2 attempts)
- Journey phase detection (ascend/summit/descend)
- Cross-chunk duplicate validation
- Progress UI (estimated, chunk N/M + days X-Y)
- Pushed: `04c6e48` (2026-09-24)

#### Phase 4H-Fix — time_of_day Sanitization ✅
- `apply()` — time_of_day enum sanitization
- `validateChunkStructure()` — items time_of_day validation
- Pushed: `0415925` (2026-09-24)

#### Phase 4M-1 — max_pax Migration ✅
- `trek_details.max_pax` (int, nullable)
- `tour_details.max_pax` (int, nullable)
- Rollback tested + verified
- Pushed: `362d019` (2026-09-25)

#### Phase 4M-2-1 — Activity + Experience Tables ✅
- `activity_details` table (service_id FK unique cascade, max_pax nullable)
- `experience_details` table (same structure)
- Rollback tested + re-migrated
- Pushed: `8d1d4e2` (2026-09-25)

#### Phase 4M-2-2 — Models ✅
- `ActivityDetail`, `ExperienceDetail` models (HasFactory + fillable + casts + relations)
- Service model: +2 hasOne relations
- TrekDetail/TourDetail: max_pax fillable + cast
- Pushed: `7392e3b` (2026-09-25)

#### NULL-FIX — Null Price Systemic ✅
- 3-layer fix (service signature + display guards + data cleanup)
- 7 files: CurrencyService + 6 views
- i18n `messages.na` in 4 locales
- Pushed: `e2cfe7e` (2026-09-25)

#### Phase 4M-2-3+4 — Category-Aware Form ✅
- Category-aware form (JS toggle — 5 categories)
- Detail record creation on service store/update
- Amenities JSON transform
- Edit pre-fill support
- Vanilla JS (no Alpine)
- 12 files (+355/-13)
- Pushed: `c0cc382` (2026-09-25)

#### ✅ COMPLETED (UNDOCUMENTED — VERIFY IN AUDIT)
These were listed as "future" in v1.0 but Master confirms **BUILT**:

- **Traveler Dashboard** — Full user dashboard with bookings, QR, memories, replay
- **Provider Dashboard** — Full provider dashboard with service + itinerary management
- **QR Check-in System** — Working, 9 check-ins recorded
- **Safety System** — Map + incidents + weather integration
- **Journey Replay** — Cinematic replay with share token
- **Photo Memories** — Per-checkpoint upload
- **AI Travel Planner** — Working, 468 requests used

> ⚠️ **Detailed file references + commits = STAGE 1-8 audit मा verify हुनेछ।**

### 🟢 IN PROGRESS

#### Phase 4M-3 — Provider Type ↔ Category Constraint

**Goal:** Provider business type constrains allowed service categories.

**Decisions Locked (Q1-Q5):**
- Q1: Mapping column (`provider_types.service_category_id`)
- Q2: Multi-type DEFERRED
- Q3: Enforcement = BOTH (form + controller)
- Q4: Missing types → map to closest category
- Q5: Custom type → all categories allowed

**Sub-phases:**
- 4M-3-1: Migration (approved, implementation pending)
- 4M-3-2: Model + relation
- 4M-3-3: Form constraint (create/edit)
- 4M-3-4: Controller validation

**Foundation Status (4M-1 → 4M-2-3+4):** ✅ 100% COMPLETE
- Provider creates any category → correct detail record saved
- Category-aware form working
- Null-price handling systemic
- Edit flow with pre-fill

**Owner Directive (validated):**
> "Service create 100% fix गरेपछि मात्र AI/Itinerary continue"

**Foundation enables AI quality:** AI inputs richer (duration, difficulty, altitude, max_pax) → expected 85-90% in Phase 4K.

**Status:** 4M-3-1 migration approved, implementation pending.

---

### 🔒 FUTURE

#### Phase 4I — AI Typo Fixes
- Model typo corrections (`qwen/qwen3.6-27b` → `qwen/qwen3.8-27b`)
- LlmService fallback fix
- QuotationController typo fix
- AI Quotation form incomplete fix

#### Phase 4J — Multi-Provider
- Add multiple free AI providers (Groq + OpenRouter free + Cerebras)
- Load distribution + fallback for rate limits

#### Phase 4K — Content Quality
- AI itinerary content accuracy improvements (75% → 90%)
- Prompt + post-validation + UI warnings

#### Phase 5 — Live Journey Enhancements
- Enhanced GPS tracking
- Family share link (expand)
- Offline queue improvements

#### Phase 6 — Memory & Social Enhancements
- Social media export
- Shareable journey page improvements

#### Phase 7+ — Platform Evolution
- Blockchain permits (exploration)
- PWA offline mode
- AI planner improvements (R5 protected)

---

## 📊 6. PHASE LEDGER

| Phase | Status | Commit | Date |
|---|---|---|---|
| Phase 1 — Foundation | ✅ PUSHED | cfa0c7c | 2026-09-22 |
| Phase 2A — Clean Globe | ✅ PUSHED | d87d50d | 2026-09-22 |
| Phase 2B — Search Fly-to | ✅ PUSHED | 3b22c0e | 2026-09-23 |
| Phase 3 — Journey Animation | ✅ PUSHED | 622dbf6 | 2026-09-23 |
| Phase 4A — Weather Panel | ✅ PUSHED | fe45542 | 2026-09-23 |
| Phase 4B — Altitude Profile | ✅ PUSHED | 3fbafc7 | 2026-09-23 |
| Phase 4C — Media Lightbox | ✅ PUSHED | f6026da | 2026-09-23 |
| Phase 4D — Sunrise/Sunset | ✅ PUSHED | 5da3ce4 | 2026-09-23 |
| Phase 4E — Dashboard Rich Data | ✅ PUSHED | 9b92e2b | 2026-09-23 |
| Phase 4G — Provider AI Fix | ✅ PUSHED | cdf289e | 2026-09-24 |
| Phase 4H — AI Chunking | ✅ PUSHED | 04c6e48 | 2026-09-24 |
| Phase 4H-Fix — time_of_day | ✅ PUSHED | 0415925 | 2026-09-24 |
| Phase 4M-1 — max_pax Migration | ✅ PUSHED | 362d019 | 2026-09-25 |
| Phase 4M-2-1 — Activity/Experience Tables | ✅ PUSHED | 8d1d4e2 | 2026-09-25 |
| Phase 4M-2-2 — Models | ✅ PUSHED | 7392e3b | 2026-09-25 |
| NULL-FIX — Null Price | ✅ PUSHED | e2cfe7e | 2026-09-25 |
| Phase 4M-2-3+4 — Category-Aware Form | ✅ PUSHED | c0cc382 | 2026-09-25 |
| **Phase 4M-3 — Provider Type Constraint** | 🟢 **IN PROGRESS** | — | 2026-09-25 |
| Phase 4I — AI Typo Fixes | 🔒 After 4M-3 | — | — |
| Phase 4J — Multi-Provider | 🔒 After 4I | — | — |
| Phase 4K — Content Quality | 🔒 After 4J | — | — |
| Phase 5 — Live Journey | 🔒 FUTURE | — | — |
| Phase 6 — Memory & Social | 🔒 FUTURE | — | — |

> ⚠️ **Traveler/Provider Dashboard, QR, Safety, Replay, AI Planner — commit hashes = STAGE 1-8 audit पछि भरिनेछ।**

---

## 🎯 7. IMPLEMENTATION RULES

### Workflow (Mandatory)
```
Discover → Report → Master Review → Scope Lock → Implement
→ Verify → COMMIT GO → Commit → PUSH GO → Push → Close
```

### Rules (R1-R24)
| # | Rule |
|---|---|
| R1 | Inspect before code |
| R2 | Discovery = read-only |
| R3 | Never invent data |
| R4 | Never expand locked scope |
| R5 | AI Planner SACRED |
| R6 | Protected systems — explicit auth only |
| R7 | Explore page protected |
| R8 | DB safety |
| R9 | No PII |
| R10 | No fake data |
| R11 | No secrets |
| R12 | Report out-of-scope |
| R13 | Never `git add .` |
| R14 | Never force-push |
| R15 | Never amend/rebase |
| R16 | Commit only after COMMIT GO |
| R17 | Push only after PUSH GO |
| R18 | Runtime evidence required |
| R19 | Migration auth required |
| R20 | Preserve existing systems |
| R21 | No paid service |
| R22 | Free alternatives first |
| R23 | Production free-tier |
| R24 | Paid = explicit decision |

---

## 🔧 8. TECHNICAL STACK

### Current Versions (Verify Each Session)
| Component | Version | CDN/Path |
|---|---|---|
| three.js | (verify) | unpkg |
| globe.gl | (verify) | unpkg |
| Leaflet | 1.9.4 | unpkg |
| topojson-client | 3 | unpkg |
| Tailwind | v4 (CDN) | CDN |
| Alpine.js | NOT installed | — |
| Laravel | 13.x | — |
| PHP | 8.4.23 | — |

### Key Files (Partial — full inventory via STAGE 1)
```
resources/views/public/services/index.blade.php       ← Explore + globe
resources/views/public/services/show.blade.php        ← Service detail
resources/views/layouts/public.blade.php              ← Layout
app/Http/Controllers/Public/ServiceController.php     ← Public controller
app/Http/Controllers/Api/MapDataController.php        ← API (protected)
routes/web.php                                        ← Web routes
routes/api.php                                        ← API routes (protected)
public/map/nepal-districts.topojson                   ← Districts (protected)
```

### Additional Systems (Verify in Audit)
```
app/Models/QrScan.php                                 ← QR model
app/Models/SosAlert.php                               ← SOS model
app/Jobs/SendSosNotification.php                      ← SOS job
app/Services/Safety/                                  ← Safety services
app/Services/JourneyReplay/                           ← Replay service
resources/views/traveler/                             ← Traveler dashboard
resources/views/provider/                             ← Provider dashboard
resources/views/admin/                                ← Admin views
```

---

## 📋 9. OPEN TICKETS (Globe-related)

| Ticket | Priority | Status |
|---|---|---|
| GLOBE-ENHANCEMENT-01 | 🔴 HIGH | Phase 4M next |
| GLOBE-WEATHER-01 | ✅ CLOSED | Phase 4A shipped |
| GLOBE-JOURNEY-ANIMATION-01 | ✅ CLOSED | Phase 3 shipped |
| GLOBE-SEARCH-01 | ✅ CLOSED | Phase 2 shipped |
| GLOBE-LAYER-MANAGER-01 | 🟢 LOW | Phase 2+ |
| MAP-FULL-NEPAL-VIEW-01 | ✅ CLOSED | Pushed |
| MAP-DUPLICATE-MARKERS-01 | ✅ CLOSED | Pushed |
| MAP-ZOOM-LIMITED-01 | ✅ CLOSED | Pushed |
| **SYSTEM-AUDIT-01** | 🔴 HIGH | In progress (this mission) |
| **MASTER-FILE-REWRITE-01** | 🔴 HIGH | DRAFT |
| **QR-RUNTIME-VERIFY-01** | 🟡 MED | STAGE 2 |
| **SOS-RUNTIME-VERIFY-01** | 🔴 HIGH | STAGE 3 |
| **DASHBOARD-ARCH-DOC-01** | 🟡 MED | STAGE 4 |

---

## 🎯 10. VISION PRINCIPLES

### 1. Foundation First
Fix broken things before adding new features.

### 2. Progressive Disclosure
Default = clean. Advanced = user opt-in.

### 3. Free-First Always
No paid service, no matter how tempting.

### 4. Realistic Execution
Vision = 6 months. Execution = 1 phase at a time.

### 5. Data Integrity
Never invent coordinates. Never fake weather. Never assume.

### 6. Target User
Foreign travelers (Nepal unfamiliar). Design for them.

### 7. Mobile-First
375px = baseline. Desktop = enhancement.

### 8. No Vision Inflation
Plan ≠ Implementation. Executed commits = truth.

### 9. 🆕 Document Reality
> Master File must reflect what's ACTUALLY built, not aspirational roadmap.

---

## 🚦 11. CURRENT STATE (2026-09-25)

### Git
```
Branch:       main
HEAD:         c0cc382
Status:       ✅ synced (0/0)
Tests:        41 passed / 1 failed (pre-existing Safety)
```

### Reality Summary
- **Systems built:** 12+
- **Phases shipped:** 1, 2A, 2B, 3, 4A, 4B, 4C, 4D, 4E, 4G, 4H, 4H-Fix, 4M-1, 4M-2-1, 4M-2-2, NULL-FIX, 4M-2-3+4
- **Bonus systems (undocumented in v1.0):** Traveler Dashboard, Provider Dashboard, QR Check-in, Safety, Journey Replay, Photo Memories, AI Planner
- **QR check-ins recorded:** 9
- **AI planner requests used:** 468
- **Foundation status:** Phase 4M foundation 100% COMPLETE
- **Current phase:** 4M-3 — Provider Type ↔ Category Constraint (migration approved, implementation pending)

### Sessions Shipped (Historical — from v1.0)
- Currency fix (`d37890f`)
- Provider Editor UX (`45c4ba2`)
- Toggle UX bundle (`c3f9c94`)
- Map bounds fix (`a9a885e`)
- Map polish bundle (`97e3f72`)
- ...plus all Phase 1-4H + 4M foundation work

### Next
**Phase 4M-3-1** (migration) → 4M-3-2/3/4 → then Phase 4I, 4J, 4K, Deploy

---

## 📌 12. CONTINUITY PROTOCOL

### If Session Ends
1. Read this file
2. Check Git state (`git log -1`)
3. Check Phase Ledger + Systems Inventory
4. Resume from current phase

### If Master Changes
1. New Master reads this file
2. Acknowledges current phase
3. Continues workflow

### If Assistant Changes
1. New Assistant reads this file
2. **Reads §14 Existing Systems Inventory**
3. Confirms understanding
4. Waits for Master directive

---

## 🎊 13. FINAL PRINCIPLE

> **"Do not build Google Earth. Build Nepal Journey Intelligence.**
> **Foundation first. Free-first always. Execute one phase at a time.**
> **User can search it → Globe shows it → Journey explains it."**

---

## 🆕 14. EXISTING SYSTEMS INVENTORY

> ⚠️ **DRAFT — Full details pending STAGE 1-8 audit।**

### Public-Facing Systems
| System | Status | Notes |
|---|---|---|
| Globe (Discover Mode) | ✅ Built | Phase 1-2 |
| Journey Animation | ✅ Built | Phase 3 |
| Weather Panel | ✅ Built | Phase 4A |
| Photo Gallery | ✅ Built | Phase 4B |
| Elevation Profile | ✅ Built | Phase 4C |
| Sunrise/Sunset | ✅ Built | Phase 4D |
| Search + Fly-to | ✅ Built | Phase 2 |
| District Layer | ✅ Built | Protected |
| Route Layer | ✅ Built | — |

### Traveler Systems
| System | Status | Notes |
|---|---|---|
| Traveler Dashboard | ✅ Built | Verify via STAGE 4 |
| Booking Management | ✅ Built | — |
| QR Check-in | ✅ Working | 9 records |
| Journey Replay | ✅ Built | Cinematic |
| Photo Memories | ✅ Built | Per-checkpoint |
| SOS Panel | ❓ Unknown | Verify via STAGE 3 |
| Safety Map | ✅ Built | Verify via STAGE 5 |

### Provider Systems
| System | Status | Notes |
|---|---|---|
| Provider Dashboard | ✅ Built | Verify via STAGE 4 |
| Service CRUD | ✅ Built | Phase 4M complete |
| Itinerary Editor | ✅ Built | Phase 4H shipped |
| Media Upload | ✅ Built | — |
| Booking Management | ✅ Built | — |
| Category-Aware Form | ✅ Built | Phase 4M-2-3+4 |

### Admin Systems
| System | Status | Notes |
|---|---|---|
| Admin Views | ✅ Built | Verify via STAGE 4 |
| User Management | ✅ Built | — |
| Safety Oversight | ✅ Built | Verify via STAGE 5 |

### Platform Systems
| System | Status | Notes |
|---|---|---|
| AI Travel Planner | ✅ Working | 468 requests — R5 protected |
| AI Quota System | ✅ Built | R5 protected |
| Booking Engine | ✅ Built | R6 protected |
| Notification System | ✅ Built | Verify via STAGE 8 |
| Auth System | ✅ Built | Verify via STAGE 4 |

> **Action:** STAGE 1-8 audit ले यो inventory verify + expand गर्नेछ।

---

## 🆕 15. TESTING CHECKLIST (Runtime Verification)

> ⚠️ **DRAFT — To be executed during STAGE 2-8 audit।**

### QR Check-in System
- [ ] Booking detail page shows QR
- [ ] QR download works
- [ ] Scan workflow verified (web/mobile?)
- [ ] Check-in records in `qr_scans` table
- [ ] Duplicate scan prevented
- [ ] Invalid QR rejected
- [ ] QR delivery method confirmed (email/dashboard)

### SOS System
- [ ] SOS button visible (where?)
- [ ] Emergency trigger works
- [ ] Location captured
- [ ] Notification sent (email/SMS)
- [ ] Rescue team alert path
- [ ] Test/dev mode exists
- [ ] Integration with Safety system

### Traveler Dashboard
- [ ] Login works
- [ ] Bookings visible
- [ ] QR accessible from booking
- [ ] Journey Replay plays
- [ ] Photo upload works
- [ ] SOS panel visible (if exists)

### Provider Dashboard
- [ ] Login works
- [ ] Service CRUD works
- [ ] Itinerary editor functional
- [ ] Media upload works
- [ ] Booking notifications received

### Safety System
- [ ] Safety map loads
- [ ] Incidents visible
- [ ] Weather auto-refresh
- [ ] Risk assessment accurate
- [ ] Data sources verified

### Journey Replay
- [ ] Replay loads from booking
- [ ] Chapters build correctly
- [ ] Share token generates
- [ ] Share link works

### AI Planner (R5 — Inspect Only)
- [ ] Endpoint responds
- [ ] Quota tracking works
- [ ] No touch — verify only

---

## 🆕 16. AUDIT MISSION LOG

| Stage | Scope | Status |
|---|---|---|
| STAGE 1 | Project structure map | ⏳ Pending |
| STAGE 2 | QR system audit | ⏳ Pending |
| STAGE 3 | SOS system audit | ⏳ Pending |
| STAGE 4 | Dashboard systems audit | ⏳ Pending |
| STAGE 5 | Safety system audit | ⏳ Pending |
| STAGE 6 | Journey Replay audit | ⏳ Pending |
| STAGE 7 | AI Planner audit (inspect only) | ⏳ Pending |
| STAGE 8 | Booking → QR flow | ⏳ Pending |
| Deliverable | Discovery Report to Master | ⏳ Pending |
| Deliverable | Master File v2.0 FINAL | ⏳ Pending |

---

**Document End — Globe Master File v2.0 (DRAFT)**
**Updated:** 2026-09-25 — Phase 4M foundation closure + 4M-3 in progress
**Next revision:** After STAGE 1-8 audit completes