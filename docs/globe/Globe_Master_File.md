# 🌍 TravelAI Nepal — Globe Master File

**Version:** 2.1 (Reality-Aligned Rewrite)
**Created:** 2026-09-22
**Rewritten:** 2026-09-23
**Updated:** 2026-09-25 (Phase 4M-3-REDO + 4I closure)
**Status:** 🟡 DRAFT — Pending STAGE 1-8 discovery verification
**Owner:** Parashar Regmi
**Master:** DeepSeek (Master role)
**Assistant:** DeepSeek (Implementation role)
**HEAD:** 605dfd3

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

#### Phase 4M-3-1 — Mapping (1:1) ✅
- Initial mapping column attempt
- Superseded by 4M-3-REDO (many-to-many pivot)
- Pushed: `cd3c110` (2026-09-25)

#### Phase 4M-3-REDO — Pivot (N:N) ✅
- 3 new categories: Resort, Lodge, Homestay (7 → 10 total)
- `provider_type_service_category` pivot (many-to-many)
- 19 seed mappings (trekking-agency → 4 categories, etc.)
- `hotel_details.property_type` enum (hotel/resort/lodge/homestay)
- Deprecated `provider_types.service_category_id` (kept for BC)
- 7 files (+172/-6)
- Pushed: `c336559` (2026-09-25)

#### Phase 4M-3-2-REDO — Form Constraint ✅
- Adaptive UI: locked if 1 category, dropdown if 2+
- Custom fallback: all categories if pivot empty
- Backend constraint: 403 on disallowed category
- Legacy bypass: existing disallowed service = OK to keep
- JS hidden input fallback (for locked case)
- 7 files (+154/-33)
- Pushed: `66eac2b` (2026-09-25)

#### Master Handover File ✅
- `docs/globe/Master_Handover_2026-09-25.md` (NEW, 569 lines, 12 sections)
- Purpose: Continuity insurance — full plan if Master limit completes
- Pushed: `f86de4f` (2026-09-25)

#### Phase 4I — AI Typo Fixes + Architecture ✅
- Removed 2 hardcoded `qwen/qwen3.6-27b` params (non-existent model)
- LlmService default fallback → `qwen/qwen3.8-27b`
- Config-driven via `.env GROQ_MODEL` (single source of truth)
- 2 features unlocked (AI Quotation + Journey Replay)
- AI-ARCHITECTURE-UNIFY-01 ticket resolved in-scope
- 3 files (+14/-15)
- Pushed: `605dfd3` (2026-09-25)

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

**Goal:** AI itinerary content accuracy improvements (75% → 90%)

**Scope:**
- Hallucination fix: fake places (Drolapaura, Gorak Shep monastery)
- Side-trek confusion (Chukhung Ri on main route)
- Village order errors (Phortse on ascent)
- Typo fixes (Pherice vs Pheriche)
- Route confusion (Khumbu Icefall = climbing only)
- Prompt enhancement + post-validation + UI warnings

**Expected:** 75% → 90%
**Effort:** ~2 hrs

**Status:** Ready to start (AI foundation 4I complete)

---

### 🔒 FUTURE

#### Phase 4J — Multi-Provider
- Add multiple free AI providers (Groq + OpenRouter free + Cerebras)
- Load distribution + fallback for rate limits
- Single point of failure mitigation

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
| Phase 4M-3-1 — Mapping (1:1) | ✅ PUSHED | cd3c110 | 2026-09-25 |
| Phase 4M-3-REDO — Pivot (N:N) | ✅ PUSHED | c336559 | 2026-09-25 |
| | Phase 4M-3-2-REDO — Constraint | ✅ PUSHED | 66eac2b | 2026-09-25 |
| Master Handover File | ✅ PUSHED | f86de4f | 2026-09-25 |
| Phase 4I — AI Typo Fixes | ✅ PUSHED | 605dfd3 | 2026-09-25 |
| Phase 4I-EXT — Journey Replay AI | ✅ PUSHED | edeb933 | 2026-09-25 |
| Phase 4K-F1 — Journey Replay Grounding | ✅ PUSHED | 52ff921 | 2026-09-25 |
| Phase 4K-F2 — AI Draft Layer 3 | ✅ PUSHED | 2cacc06 | 2026-09-25 |
| Phase 4H-EXT — Timing (11m→3m45s) | ✅ PUSHED | 51a33ae | 2026-09-25 |
| Phase UI-FIX — Cancel + Abort | ✅ PUSHED | e8a586f | 2026-09-25 |
| Phase 4H-EXT-2 — Chunking Reliability | ✅ PUSHED | 76d5b7d | 2026-09-25 |
| Phase 4J — Multi-Provider | ✅ PUSHED | 5f9b3e7 | 2026-09-25 |
| Phase 4J-Fix — Chunker + Guard | ✅ PUSHED | 5f568bf | 2026-09-25 |
| Phase 4K — Route Accuracy (100%) | ✅ PUSHED | d051257 | 2026-09-25 |
| **Phase 4K-DATA-FIX — Data cleanup** | 🟢 **NEXT** | — | — |
| Phase E1 — Home AI Improve | 🔒 LAST | — | — |
| Phase 4K-F3 — Content tuning | 🔒 Post-deploy | — | — |
| Deploy | 🔒 FINAL | — | — |
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
| GLOBE-ENHANCEMENT-01 | 🔴 HIGH | Post-4K |
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

## 🚦 11. CURRENT STATE (2026-09-25 Evening)

### Git
```
Branch:       main
HEAD:         605dfd3
Status:       ✅ synced (0/0)
Tests:        41 passed / 1 failed (pre-existing Safety)
```

### Reality Summary
- **Systems built:** 12+
- **Phases shipped:** 1, 2A, 2B, 3, 4A, 4B, 4C, 4D, 4E, 4G, 4H, 4H-Fix, 4M-1, 4M-2-1, 4M-2-2, NULL-FIX, 4M-2-3+4, 4M-3-1, 4M-3-REDO, 4M-3-2-REDO, 4I
- **Bonus systems (undocumented in v1.0):** Traveler Dashboard, Provider Dashboard, QR Check-in, Safety, Journey Replay, Photo Memories, AI Planner
- **QR check-ins recorded:** 9
- **AI planner requests used:** 468
- **Foundation status:** Phase 4M foundation + 4M-3 REDO 100% COMPLETE
| Phase 4K-F4 — Duration + Cap | ✅ PUSHED | 257b1e2 | 2026-09-26 |
| Phase 4K-F4c/F4d — Template + Multi-Model | ✅ PUSHED | 7902aff | 2026-09-26 |
| 4K-DATA-FIX — Data Cleanup | ✅ DB-ONLY | — | 2026-09-26 |
| Phase 4K-P1 — Rich Template | ✅ PUSHED | 4b2aadd | 2026-09-26 |
| MODEL-OPT — Short 429 + Gemma Models | ✅ PUSHED | 4b2aadd | 2026-09-26 |
| **P0-B — Real LLM Test** | 🟡 **NEXT SESSION** | — | — |
| **E1 — Home AI Improve** | 🔒 Last | — | — |
| Deploy | 🔒 FINAL | — | — |
### Today's Session Commits (8 pushed)
1. 4M-3-1 mapping (superseded)
2. 4M foundation log
3. 4M-3-REDO pivot (many-to-many, `c336559`)
4. Master Handover file (`f86de4f`)
5. 4M-3-2 REDO constraint (`66eac2b`)
6. 4I AI typo fixes (`605dfd3`)
7. Chore docs (this append — pending)

### Next
**Phase 4K** (AI content quality) → Phase 4J (multi-provider) → Deploy

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
| Adaptive Category UI | ✅ Built | Phase 4M-3-2-REDO |
| Departure Management | ✅ Built | Phase 09B-02 |
| AI Itinerary Draft | ✅ Built | Phase X-01 |

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
| AI Quotation | ✅ Fixed | Phase 4I |
| Config-Driven AI Model | ✅ Built | Phase 4I (.env GROQ_MODEL) |

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
- [ ] Adaptive category UI works (locked + dropdown)

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
- [ ] AI story renders (Phase 4I fix verify)

### AI Planner (R5 — Inspect Only)
- [ ] Endpoint responds
- [ ] Quota tracking works
- [ ] No touch — verify only

### AI Quotation (Phase 4I Verify)
- [ ] Real AI response (no 404)
- [ ] Model config-driven (.env GROQ_MODEL)
- [ ] Form fields complete

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

**Document End — Globe Master File v2.1 (DRAFT)**
**Updated:** 2026-09-25 — Phase 4M-3-REDO + 4I closure, 4K next
**Next revision:** After Phase 4K OR STAGE 1-8 audit completes


---

## 📌 SESSION 2026-09-26 — PHASE LEDGER UPDATE

### Phase Ledger — New Entries

| Phase | Status | Commit | Date |
|---|---|---|---|
| Phase CACHE-01 | ✅ PUSHED | 6c9e781 | 2026-09-26 |
| 4K-DATA-FIX-02 | ✅ DB-ONLY | — | 2026-09-26 |
| 4J-EXT Phase 1A | ✅ PUSHED | 16f5fca | 2026-09-26 |
| 4J-EXT Phase 1B | 🔴 CLOSED (no candidate) | — | 2026-09-26 |
| 4J-EXT Phase 2 | ✅ PUSHED | c906cd1 | 2026-09-26 |
| **ROUTE-DATA-AUDIT-EBC-01** | 🔴 **NEXT SESSION** | — | — |
| AI-ACCLIMATIZATION-ENFORCEMENT-01 | 🔴 Next | — | — |
| AI-ROUTE-COMPLETENESS-01 | 🔴 Next | — | — |
| 4J-EXT-Phase-2B-Groq-Dedup | 🟡 Low | — | — |
| TEST-FLOW-CONFIG-CACHE-01 | 🔴 HIGH | — | — |

---

### Current State (2026-09-26 Evening)
Branch:       main
HEAD:         c906cd1 (synced with origin/main)
Tests:        41 passed / 1 failed (pre-existing Safety)
Config:       cleared (dev-mode)
Cache:        AI_DRAFT_CACHE_ENABLED=true

**Latest phase shipped:** 4J-EXT Phase 2 (parallel + circuit breaker, 29× speedup)

---

### Session 2026-09-26 — Achievements

| Achievement | Value |
|---|---|
| Speed record | **29×** (9:30 min → 19.4 sec) |
| Providers | Groq + OpenRouter + Gemini (3 working) |
| Circuit breaker | Proven in runtime log |
| Parallel race | 6 candidates → fastest wins |
| API tests | 5 iterations → 2 winners |

---

### Session Incidents — Handled

1. **DB wipe** (config:cache + test error) → **Recovered** via `backup_4K_data_fix.sql` (100%)
2. **Login blocked** → **Resolved** (restore + correct credentials)
3. **Quality regression** → **Flagged** (route 18→15 drop, ticket created)

---

### Next Session Priority

1. 🔴 **ROUTE-DATA-AUDIT-EBC-01** (discovery first — READ-ONLY)
2. 🔴 **AI-ACCLIMATIZATION-ENFORCEMENT-01** (after root cause)
3. 🟡 Phase 2B (Groq#2 removal — 5 min)
4. 🟡 CACHE-CONTENT-VERSION-01 (30 min)
5. 🔒 Deploy prep (after quality fixed)

---

### Prevention Rule — Locked

**🚨 TEST EXECUTION PROTOCOL:**
1. NEVER `php artisan config:cache` before `php artisan test`
2. ALWAYS `php artisan config:clear` FIRST
3. Consider `.env.testing` with separate DB
4. Assistant MUST warn about DB wipe risk before Owner runs test

**Ticket:** `TEST-FLOW-CONFIG-CACHE-01` (🔴 HIGH)

---

**Master File — Session 2026-09-26 End**
**Next revision:** After ROUTE-DATA-AUDIT-EBC-01


---

## 📌 SESSION 2026-09-27 — PHASE LEDGER UPDATE

### Phase Ledger — New Entries

| Phase | Status | Commit | Date |
|---|---|---|---|
| Phase 3A | ✅ DB-ONLY | — | 2026-09-27 |
| Phase 3B | ⚠️ Partial | — | 2026-09-27 |
| Phase 3C | ✅ PUSHED | c8a6a22 | 2026-09-27 |
| **AI-DESCENT-COMPRESSION-01** | 🔴 **NEXT SESSION** | — | — |
| **SEEDER-SYNC-01** | 🔴 Before deploy | — | — |
| **SEEDER-SAFETY-AUDIT-01** | 🔴 Before deploy | — | — |
| **PRODUCTION-DEPLOY-CHECKLIST-01** | 🔴 Before deploy | — | — |

---

### Current State (2026-09-27)
Branch: main
HEAD: c8a6a22 (synced with origin/main)
Tests: 41 passed / 1 failed (pre-existing Safety)
Config: cleared (dev-mode)
Cache: AI_DRAFT_CACHE_ENABLED=true

text

**Latest phase shipped:** Phase 3C — Structural round-trip grouping (19.5 sec, 1 day short)

---

### Session 2026-09-27 — Achievements

| Achievement | Value |
|---|---|
| Speed | 19.5 sec (best) |
| Route | 18 segments proven |
| Round-trip grouping | Structural fix proven |
| Fix scripts | Committed as documentation |

---

### Session Incidents — Handled

1. **Backup restore reverted DB** — Kala Patthar + 3 Route 2 segments lost
   → Re-applied via scripts (verified)
2. **Prompt tuning failed** — LLM ignored rules
   → Structural fix (Option C) succeeded

---

### Next Session Priority

1. 🔴 **AI-DESCENT-COMPRESSION-01** (structural, ~30 min)
2. 🟡 **CACHE-CONTENT-VERSION-01** (30 min)
3. 🟡 **Phase 2B (Groq#2 removal)** (5 min)
4. 🔒 **Provider Itinerary CLOSE** (after quality)
5. 🔒 **Quotation + Home AI improve** (future)
6. 🔒 **Production deploy prep** (with seeder audit)

---

### Production Concerns (Owner Raised)

**🚨 Seeders production-unsafe risk:**
- Seeders = designed for fresh install
- Production = already data → duplicate/wipe risk
- Routes fixes = DB-only, seeders unchanged
- **Action:** SEEDER-SAFETY-AUDIT-01 + SEEDER-SYNC-01 before deploy

**🚨 Fix scripts production-safe?**
- `fix_kala_patthar.php` = idempotent ✅
- `fix_route2_ebc.php` = hardcoded IDs ⚠️ verify

**🚨 New Assistant handoff:**
- 5 files must read (Master File + Execution Log + Current Stage + 2 new)
- Strong documentation = safe continuity

---

**Master File — Session 2026-09-27 End**
**Next revision:** After AI-DESCENT-COMPRESSION-01
---

## 📌 SESSION 2026-09-27 (CONT.) — PHASE LEDGER UPDATE

### Phase Ledger — New Entries

| Phase | Status | Commit | Date |
|---|---|---|---|
| ROUTE-DATA-QUALITY-AUDIT-01 | ✅ PUSHED | f693858 | 2026-09-27 |
| Phase 3D (descent split) | ⚠️ Partial | (merged into 3E) | 2026-09-27 |
| **PHASE 3E** | ✅ **PUSHED** | **6503ca8** | 2026-09-27 |
| AI-ROUNDTRIP-CONTINUITY-01 | 🟢 LOW | — | — |
| AI-DESCENT-DISTRIBUTION-01 | 🟢 LOW | — | — |

---

### Current State (2026-09-27)
Branch: main
HEAD: 6503ca8 (synced with origin/main)
Tests: 41 passed / 1 failed (pre-existing Safety)
Config: cleared (dev-mode)
Cache: AI_DRAFT_CACHE_ENABLED=true

text

**Latest phase shipped:** Phase 3E — Chunk boundary alignment (14.2 sec, EBC complete)

---

### Phase 3 — Full Summary (3 Days)

| Phase | Result |
|---|---|
| 3A | Data fix (18 segments, Kala Patthar) |
| 3B | Prompt tuning (partial — LLM ignored) |
| 3C | Round-trip grouping (structural win) |
| 3D | Descent split (Lukla win, KP missing) |
| **3E** | **Boundary alignment (EBC COMPLETE)** ✅ |

**Final state:** EBC 14-day = correct, complete, fast (14.2 sec)

---

### Session 2026-09-27 — Achievements

| Achievement | Value |
|---|---|
| Route Audit | ✅ CLOSED (Route 5 + 86 orphans) |
| Phase 3E | ✅ EBC 14-day complete |
| Speed | 14.2 sec (best yet) |
| Chunks | 3 (vs 5, 40% faster) |
| Suite | 41p/1f intact |

---

### Next Session Priority

1. 🟡 `CACHE-CONTENT-VERSION-01` (30 min)
2. 🟡 `Phase 2B Groq#2 removal` (5 min)
3. 🔴 `PRODUCTION-DEPLOY-CHECKLIST-01` (1 hr)
4. 🔴 `SEEDER-SYNC-01` + `SEEDER-SAFETY-AUDIT-01` (pre-deploy)
5. 🔒 Quotation Generator check
6. 🔒 Home AI Travel Planner improve
7. 🔒 Deploy

---

### Production Concerns (Owner Raised)

- Seeders production-unsafe risk
- Fix scripts hardcoded IDs
- Routes fixes DB-only (not in seeders)
- New Assistant handoff = strong docs required

---

**Master File — Session 2026-09-27 (cont.) End**
**Next revision:** After CACHE-CONTENT-VERSION-01

---

### Session 2026-09-27 (Flow 2 + Phase 5D + 5E)

**HEAD:** `8aa36db` (synced 0/0)

**Phase Ledger Update:**

| Phase | Status | Commit |
|---|---|---|
| Flow 2 (traveler → provider quote) | ✅ SHIPPED | 58c6b72 |
| Phase 5D (Journey Replay) | ✅ CLOSED | 8aa36db |
| Phase 5E (Payment verify) | ✅ CLOSED | 8aa36db |

**Deploy-ready milestone:** ✅ Achieved

**Active Tickets:** 10 (see Execution Log)

**Next phase:** Phase 6 (Deploy prep) + AI Planner audit

---

**Master File — Session 2026-09-27 (Flow 2 + 5D + 5E) End**
**Next revision:** After Phase 6 (Deploy prep)

---

### Phase 5G — AI Planner Fix (2026-09-27)

**HEAD:** `d276b4c` (synced 0/0)

**Phase Ledger Update:**

| Phase | Status | Commit |
|---|---|---|
| Phase 5G (AI Planner smart compression) | ✅ CLOSED | d276b4c |

**Session Milestone:**
- Flow 2 — SHIPPED
- Phase 5D — CLOSED
- Phase 5E — CLOSED
- Phase 5G — CLOSED

= Deploy-ready + AI Planner fixed

**Active Tickets:**
- AI-PLANNER-DAYS-PADDING-01 (post-deploy)
- SAFETY-TEST-TYPE-ERROR-01 (next session)
- CLEANUP-AUDIT-ARTIFACTS-01 (post-deploy)

**Next phase:** TRANSPORT P2 (discovery) OR Phase 6 (Deploy prep)

**New Rule:** R16-EXT (regression before commit — enforce)

---

**Master File — Session 2026-09-27 (Phase 5G CLOSED) End**
**Next revision:** After Phase 6 (Deploy prep)
---

## Phase Ledger Update — 2026-09-28

### Current HEAD
8dee6b0 — feat(payment): traveler booking payment display + notify (Phase 7E.2)

text

### Payment Phase 7 Progress

| Sub-Phase | Status | Commit |
|-----------|--------|--------|
| 7A — Stripe removal | ✅ CLOSED | 943093f |
| 7B — Migrations + models | ✅ CLOSED | 21fcd48 |
| 7C — Provider subscription UI | ✅ CLOSED | af9617e |
| 7D — Admin verify queue | ✅ CLOSED | 9272936 |
| 7E.1 — Provider settings UI | ✅ CLOSED | 54d2530 |
| 7E.1b — Sidebar link | ✅ CLOSED | 54d2530 |
| 7E.2 — Traveler display | ✅ CLOSED | 8dee6b0 |
| 7E.2b — PayPal side-by-side | 🟡 PENDING | — |
| 7E.3 — Cancel confirmation | 🟡 PENDING | — |
| 7F — Full regression | 🟡 PENDING | — |
| 7G — Notifications + Invoice | 🟡 PENDING | — |
| 7H — Legacy Stripe data | 🟡 PENDING | — |

### New Tickets (2026-09-28)
🟢 PM-PAYPAL-SIDEBYSIDE-01 LOW — 7E.2b (UI polish)
🟢 PM-MODAL-DYNAMIC-FIELDS-01 LOW — post-deploy (dynamic field visibility)
🟢 PM-MODAL-FIELD-HINTS-01 LOW — post-deploy (field hints in modal)
🟢 PM-PAYPAL-NEPAL-GUIDANCE-01 LOW — post-deploy (provider tooltip)
🟢 SIDEBAR-EMOJI-MOJIBAKE-01 LOW — i18n cleanup batch
🟡 ADMIN-REJECT-TEST-01 MED — before deploy
🟡 PROVIDER-BOOKING-NOTIFICATION-01 MED — 7G scope
🟢 7E.1-RUNTIME-JSON-01 CLOSED (base64 fix)
🟡 PROVIDER-SIDEBAR-LINK-01 CLOSED (7E.1b)

text

### Protected Systems — Verified Intact

- ✅ PlannerService (R5)
- ✅ Booking status transitions
- ✅ QR / Safety / SOS / JourneyReplay
- ✅ All Seeders (locks preserved)
- ✅ Backups intact
---

## Phase Ledger Update — 2026-09-29 (Phase 7 COMPLETE)

### Current HEAD
a9c4bfe — fix(i18n): remove Stripe mentions from pricing (Phase 7A cleanup)

text

### Phase 7 — COMPLETE ✅

| Sub-Phase | Status | Commit |
|-----------|--------|--------|
| 7A — Stripe removal | ✅ | 943093f |
| 7B — Migrations + models | ✅ | 21fcd48 |
| 7C — Provider subscription UI | ✅ | af9617e |
| 7D — Admin verify queue | ✅ | 9272936 |
| 7E.1 — Provider settings | ✅ | 54d2530 |
| 7E.1b — Sidebar link | ✅ | 54d2530 |
| 7E.2 — Traveler display | ✅ | 8dee6b0 |
| 7E.2b — Side-by-side + tooltip | ✅ | 62203ee |
| 7E.3 — Cancel confirmation | ✅ | 92d7d61 |
| Race fix | ✅ | 2b81696 |
| Pricing Stripe fix | ✅ | a9c4bfe |
| **7F — Regression** | ✅ **PASS (44p/0f)** | — |

### Payment Architecture — LOCKED

**Layer 1 (TravelAI ↔ Provider):**
- Bank transfer (NIC Asia)
- eSewa
- Khalti
- Manual admin verify (7D queue)

**Layer 2 (Provider ↔ Traveler):**
- Domestic: bank, eSewa, Khalti, cash
- International: PayPal, Wise, international bank
- Provider self-config
- Display-only (no platform processing)
- "I've Paid" = notification only (no payment record)

### Tickets Closed Today (7)
✅ 7E.1-RUNTIME-JSON-01
✅ PROVIDER-SIDEBAR-LINK-01
✅ PM-COPY-TOOLTIP-01
✅ CANCEL-CONFIRMATION-01
✅ PLAN-CHANGE-RACE-CONDITION-01
✅ JUNK-FILES-CLEANUP-01
✅ PRICING-STRIPE-MENTION-01

text

### Tickets Open (deferred)
🟢 PM-MODAL-DYNAMIC-FIELDS-01 LOW post-deploy
🟢 PM-MODAL-FIELD-HINTS-01 LOW post-deploy
🟢 PM-PAYPAL-NEPAL-GUIDANCE-01 LOW post-deploy
🟢 SIDEBAR-EMOJI-MOJIBAKE-01 LOW i18n batch
🟢 PLAN-CHANGE-PRO-RATED-REFUND-01 LOW post-MVP
🟡 ADMIN-REJECT-TEST-01 MED before deploy
🟡 PROVIDER-BOOKING-NOTIFICATION-01 MED 7G scope

text

### Next Phases
7G — Notifications + Invoice (email + dompdf + hooks) ~3 hrs
7H — Legacy Stripe data migration ~30 min
Transport P2 — Discovery (READ-ONLY) ~30 min
Transport P3 — V1 Build ~3-4 hrs
Deploy prep + final regression

text

### Protected Systems — Verified Intact

- ✅ PlannerService (R5)
- ✅ Booking status transitions
- ✅ QR / Safety / SOS / JourneyReplay
- ✅ All Seeders (locks preserved)
- ✅ Backups intact
---

## Phase Ledger Update — 2026-09-29 (Phase 7 + Transport COMPLETE)

### Current HEAD
3e6812f — fix(i18n): blade wrap cleanup — provider sidebar + service show (i18n cleanup)

text

### Phase Status

| Phase | Status | Notes |
|-------|--------|-------|
| 7A — Stripe removal | ✅ | 943093f |
| 7B — Migrations | ✅ | 21fcd48 |
| 7C — Provider UI | ✅ | af9617e |
| 7D — Admin verify | ✅ | 9272936 |
| 7E.1 — Provider settings | ✅ | 54d2530 |
| 7E.1b — Sidebar link | ✅ | 54d2530 |
| 7E.2 — Traveler display | ✅ | 8dee6b0 |
| 7E.2b — Side-by-side + tooltip | ✅ | 62203ee |
| 7E.3 — Cancel confirmation | ✅ | 92d7d61 |
| Race fix | ✅ | 2b81696 |
| Pricing Stripe cleanup | ✅ | a9c4bfe |
| 7F — Full regression | ✅ | PASS (44p/0f) |
| 7G — Notifications + invoice | ✅ | d7c5bc5 |
| 7H — Legacy data | ✅ | 3 orphaned payments resolved |
| Transport (P3-C) | ✅ | bc33288 + i18n cleanup 3e6812f |
| **i18n cleanup bundle** | ✅ | 77a3125 + 3e6812f |

### Payment Architecture — LOCKED

**Layer 1 (TravelAI ↔ Provider):**
- Bank transfer (NIC Asia)
- eSewa
- Khalti
- Manual admin verify (7D queue)
- Auto-invoice + email (7G)

**Layer 2 (Provider ↔ Traveler):**
- Domestic: bank, eSewa, Khalti, cash
- International: PayPal, Wise, international bank
- Provider self-config
- Display-only (no platform processing)
- "I've Paid" = notification only

### Transport — COMPLETE

- Category: transport (id 5)
- Provider type: transport-provider (id 9)
- Services: 186 (existing)
- transport_details table (19 cols) — 1 filled (185 pending backfill)
- Public display + i18n + AI Planner integration

### Tickets — OPEN
🟢 FROM-TRANSLATION-ZH-01 (CLOSED — fixed 3e6812f)
🟢 TRANSPORT-BLADE-HARDCODED-EN-01 (CLOSED — 77a3125 + 3e6812f)
🟢 SIDEBAR-EMOJI-MOJIBAKE-01 (CLOSED — 3e6812f)
🟡 TRANSPORT-DATA-BACKFILL-01 (deferred — post-deploy, 185 services)
🟢 PM-MODAL-DYNAMIC-FIELDS-01 (post-deploy)
🟢 PM-MODAL-FIELD-HINTS-01 (post-deploy)
🟢 PM-PAYPAL-NEPAL-GUIDANCE-01 (post-deploy)
🟡 ADMIN-REJECT-TEST-01 (MED — before deploy)
🟢 PLAN-CHANGE-PRO-RATED-REFUND-01 (post-MVP)
🚨 SECURITY-GMAIL-ROTATE-01 (deferred — SIM pending)

text

### Next Priority
E1 (Home AI improve) — ~1-2 hrs

Phase 6 (Deploy prep) — ~2-3 hrs

Remaining tickets (ADMIN-REJECT-TEST-01 etc.)

Deploy (Phase 8)
= ~3-4 days to deploy

text

### Protected Systems — Verified Intact

- ✅ PlannerService (R5)
- ✅ Booking status transitions
- ✅ QR / Safety / SOS / JourneyReplay
- ✅ All Seeders (locks preserved)
- ✅ Backups intact

