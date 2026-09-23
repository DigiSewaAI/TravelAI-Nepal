```markdown
# 🌍 TravelAI Nepal — Globe Master File

**Version:** 1.0
**Created:** 2026-09-22
**Status:** AUTHORITATIVE REFERENCE
**Owner:** Parashar Regmi
**Master:** DeepSeek (Master role)
**Assistant:** DeepSeek (Implementation role)

> **Single source of truth for Globe system vision, phases, and rules.**

---

## 📋 0. HOW TO USE THIS FILE

### For New Session
1. Read this file completely
2. Verify current Git state
3. Check current phase in Phase Ledger
4. Wait for Master directive

### For Master
- Reference this file for scope decisions
- Update Phase Ledger after each closure
- Keep vision aligned, execution realistic

### For Assistant
- Follow phase order strictly
- No scope expansion without Master GO
- Report after each phase

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
- ✅ Progressive disclosure (not overwhelming)
- ✅ Foundation → Discovery → Journey → Live → Memory

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

## 🏗️ 3. ARCHITECTURE

### Two Modes
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

---

## 🛡️ 4. PROTECTED SYSTEMS (R6)

**NEVER modify without explicit Master authorization:**

| System | Files |
|---|---|
| AI Planner | `PlannerService`, `ItineraryGenerator`, `ItineraryValidator` |
| AI Quota | `AiReservationService`, `AiLimitService` |
| GLOBE-01..07 | `MapDataController`, `routes/api.php` GLOBE endpoints |
| Public Explore | `resources/views/public/services/index.blade.php` (partial) |
| District TopoJSON | `public/map/nepal-districts.topojson` |
| Booking | `BookingStatusTransitions`, `BookingLimitService` |
| Safety | All safety system files |
| Subscription/Payment | All billing files |

---

## 🗺️ 5. PHASE ROADMAP

### PHASE 1 — Foundation Fix (2-3 hrs) 🔴 CURRENT

**Goal:** Globe professional appearance + clean console

| Task | Status |
|---|---|
| three.js version pin | 🟢 GO |
| Process shim add | 🟢 GO |
| Texture URL verify | 🟢 GO |
| Console error fix | 🟢 GO |
| Responsive globe | 🟢 GO |
| Clean controls | 🟢 GO |
| Nepal focus | 🟢 GO |
| Marker performance | 🟢 GO |

**Deliverable:** Globe renders cleanly, no console error, professional look.

**Files:**
- `resources/views/public/services/index.blade.php`
- `resources/views/layouts/public.blade.php` (if needed)

**Test:**
- T1: Globe renders (Earth texture visible)
- T2: Console clean (no `process is not defined`)
- T3: Mobile 375px works
- T4: Full suite 41p/1f

**HOLD:** No commit until COMMIT GO

---

### PHASE 2 — Discovery Mode (1-2 days)

**Goal:** Clean, focused discovery experience

| Task | Priority |
|---|---|
| Nepal highlight | P1 |
| 8 city markers (already exists) | P1 |
| Category filters (Treks/Tours/Hotels) | P1 |
| **138 routes HIDDEN by default** | P1 |
| **77 districts TOGGLE** | P1 |
| Selected route highlighting | P1 |
| Basic search bar (LIKE-based) | P2 |
| Search → Globe fly-to (basic) | P2 |
| "Fly to Nepal" animation | P2 |

**Deliverable:** Clean globe, user can search + select + fly-to.

**Files:**
- `resources/views/public/services/index.blade.php`
- `app/Http/Controllers/Public/ServiceController.php`
- `routes/web.php` (search route)

**Test:**
- T1: Full Nepal initial view
- T2: Districts toggle works
- T3: Search returns results
- T4: Click result → globe flies
- T5: Mobile

---

### PHASE 3 — Journey Animation (2-3 days) ⭐ SIGNATURE

**Goal:** Wow factor — day-by-day animated journey

| Task | Priority |
|---|---|
| Package → Globe zoom | P1 |
| Day-by-day playback | P1 |
| Camera animation (Globe.gl pointOfView) | P1 |
| Waypoint info panel | P1 |
| Play/Pause/Prev/Next controls | P1 |
| Progress indicator (Day 5/14) | P1 |
| Photos inline per waypoint | P2 |
| Elevation inline | P2 |

**Deliverable:** Signature feature — "Play Journey" for any package.

**Files:**
- `resources/views/public/services/show.blade.php`
- `resources/views/public/services/_journey_animation.blade.php` (new)
- `app/Http/Controllers/Public/ServiceController.php`

**Test:**
- T1: Play starts Day 1 → ends Day N
- T2: Camera moves with waypoints
- T3: Panel updates per day
- T4: Pause/Prev/Next works
- T5: Mobile

---

### PHASE 4 — Rich Experience (1 week)

**Goal:** Info-rich journey + weather

| Task | Priority |
|---|---|
| Weather (Open-Meteo) | P1 |
| Weather cache table + cron | P1 |
| Waypoint photo gallery | P1 |
| Elevation profile chart | P1 |
| Accommodation info | P2 |
| Activities per day | P2 |
| Sunrise/sunset | P3 |

**Deliverable:** Trip planner experience — weather + photos + elevation.

**Files:**
- `app/Services/WeatherService.php` (new)
- `app/Jobs/RefreshWeatherCacheJob.php` (new)
- `database/migrations/xxx_create_weather_cache_table.php` (new)
- `resources/views/public/services/_weather_panel.blade.php` (new)
- `resources/views/public/services/_elevation_profile.blade.php` (new)

**Test:**
- T1: Weather loads for 138 destinations
- T2: Cache refresh works
- T3: Photos gallery works
- T4: Elevation chart renders
- T5: Mobile

---

### PHASE 5 — Live Journey (Future, ~1 month)

**Goal:** Live tracking + safety

⚠️ **Complexity Warning:** GPS + battery + connectivity + offline sync

| Task | Priority |
|---|---|
| GPS permission | Future |
| Live location update | Future |
| QR check-in | Future |
| Offline queue + sync | Future |
| Family share link | Future |
| Safety dashboard | Future |

**HOLD:** Don't promise publicly until ready.

---

### PHASE 6 — Memory & Social (Future)

**Goal:** Journey replay + sharing

| Task | Priority |
|---|---|
| Journey replay video | Future |
| Photo timeline | Future |
| Shareable journey page | Future |
| Social media export | Future |

---

## 📊 6. PHASE LEDGER

| Phase | Status | Commit | Date |
|---|---|---|---|
| Phase 1 — Foundation | 🟢 GO | — | — |
| Phase 2 — Discovery | 🔒 HOLD | — | — |
| Phase 3 — Journey Animation | 🔒 HOLD | — | — |
| Phase 4 — Rich Experience | 🔒 HOLD | — | — |
| Phase 5 — Live | 🔒 FUTURE | — | — |
| Phase 6 — Memory | 🔒 FUTURE | — | — |

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
| R6 | GLOBE-01..07 PROTECTED |
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
| three.js | TBD (fix in Phase 1) | unpkg |
| globe.gl | TBD | unpkg |
| Leaflet | 1.9.4 | unpkg |
| topojson-client | 3 | unpkg |
| Tailwind | v4 (CDN) | CDN |
| Alpine.js | NOT installed | — |
| Laravel | 13.x | — |
| PHP | 8.4.23 | — |

### Key Files
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

---

## 📋 9. OPEN TICKETS (Globe-related)

| Ticket | Priority | Status |
|---|---|---|
| GLOBE-ENHANCEMENT-01 | 🔴 HIGH | Phase 1 in progress |
| GLOBE-WEATHER-01 (Open-Meteo) | 🟡 MEDIUM | Phase 4 |
| GLOBE-JOURNEY-ANIMATION-01 | 🔴 HIGH | Phase 3 |
| GLOBE-SEARCH-01 | 🟡 MEDIUM | Phase 2 |
| GLOBE-LAYER-MANAGER-01 | 🟢 LOW | Phase 2+ |
| MAP-FULL-NEPAL-VIEW-01 | ✅ CLOSED | Pushed |
| MAP-DUPLICATE-MARKERS-01 | ✅ CLOSED | Pushed |
| MAP-ZOOM-LIMITED-01 | ✅ CLOSED | Pushed |

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

---

## 🚦 11. CURRENT STATE (2026-09-22)

### Git
```
Branch:     main
HEAD:       97e3f72 (synced)
origin/main: 97e3f72
Tests:      41p / 1f (pre-existing Safety)
```

### Sessions Shipped
- Currency fix (`d37890f`)
- Provider Editor UX (`45c4ba2`)
- Toggle UX bundle (`c3f9c94`)
- Map bounds fix (`a9a885e`)
- Map polish bundle (`97e3f72`)

### Next
Phase 1 — Foundation Fix (2-3 hrs)

---

## 📌 12. CONTINUITY PROTOCOL

### If Session Ends
1. Read this file
2. Check Git state
3. Check Phase Ledger
4. Resume from current phase

### If Master Changes
1. New Master reads this file
2. Acknowledges current phase
3. Continues workflow

### If Assistant Changes
1. New Assistant reads this file + continuity doc
2. Confirms understanding
3. Waits for Master directive

---

## 🎊 FINAL PRINCIPLE

> **"Do not build Google Earth. Build Nepal Journey Intelligence.**
> **Foundation first. Free-first always. Execute one phase at a time.**
> **User can search it → Globe shows it → Journey explains it."**

---

**Document End — Globe Master File v1.0**
```

---

## 📌 तिम्रो Exact Steps

### Step 1 — File Save गर

**Path:** `docs/globe/Globe_Master_File.md`

**UTF-8 encoding मा save गर।**

### Step 2 — Continuity Doc Reference

`docs/provider-itinerary/Current_Stage_And_All_Process.md` मा append गर:

```markdown
---

## 📌 GLOBE MASTER FILE CREATED (2026-09-22)

**File:** `docs/globe/Globe_Master_File.md`
**Version:** 1.0
**Purpose:** Authoritative reference for all Globe phases
**Phases:** 6 (Foundation → Discovery → Journey → Rich → Live → Memory)
**Current Phase:** Phase 1 (Foundation Fix)
**Free-first compliance:** ✅ R21-R24

See Globe_Master_File.md for full details.

---
```

### Step 3 — Phase 1 सुरु गर

**Assistant लाई Phase 1 directive relay गर:**
- three.js version pin
- Process shim add
- Texture URL fix
- Console error fix
- Test

---

## 💬 छोटो उत्तर (Master Voice)

Bro, **Master File तयार।**

### 🎯 के छ file मा?

1. **Vision** (Nepal Journey Intelligence)
2. **Free-first constraints** (R21-R24)
3. **Architecture** (Discovery + Journey modes)
4. **Protected systems**
5. **6 Phases** (Foundation → Memory)
6. **Phase Ledger** (current status)
7. **Implementation rules**
8. **Technical stack**
9. **Open tickets**
10. **Current state**
11. **Continuity protocol**

### 🎯 Key Features

- ✅ Vision + Execution merged
- ✅ Realistic phases (2-3 hrs to 1 month)
- ✅ Free-first enforced
- ✅ Freeze point = Phase 1
- ✅ Every phase has test plan
- ✅ No vision inflation

### 🎯 तिमी अब

**Step 1 — File save गर** (`docs/globe/Globe_Master_File.md`)

**Step 2 — Continuity doc reference append गर**

**Step 3 — Phase 1 सुरु गर** (Assistant लाई directive relay)

**Step 4 — Execute one phase at a time**

### 🎯 Final Vision

> **"Google Earth होइन — Nepal Journey Intelligence।**
> **Foundation पहिले। Free-first सधैं। एक phase एक चोटि।"**
