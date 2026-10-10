# TravelAI Nepal --- A--Z Master Handoff & Continuation Plan

## Current Reality, Completed Work, Remaining Roadmap, and Rules for Any Future AI

**Version:** 4.0 --- Current Reality / Continuation Edition\
**Updated:** 2026-09-18\
**Project:** TravelAI Nepal\
**Stack:** Laravel 13 + Blade + Vite + Tailwind v4\
**Local environment:** Windows 11 + Laragon\
**Main Globe branch:** `feature/globe-system`

> **Purpose:** This is the continuity document. A future AI/ChatGPT
> should read this file first and use it as the working source of truth.
> It records what has actually been audited, what has actually been
> implemented, what was deliberately deferred, the current Git state,
> and the A--Z roadmap.

------------------------------------------------------------------------

# 0. MASTER RULES --- READ FIRST

The project follows a strict **Audit → Master Review → Scope Lock →
Implement → Verify → Commit → Push** workflow.

### Mandatory rules

1.  Inspect before changing anything.
2.  Discovery/audit phases are read-only.
3.  Never invent database facts or geographic mappings.
4.  Never silently expand a locked phase.
5.  Existing desktop behavior is protected unless a phase explicitly
    authorizes a change.
6.  AI Planner is protected until the authorized AI/Globe integration
    phase.
7.  Use actual runtime evidence for UI claims.
8.  Use actual DB evidence for data claims.
9.  Never claim the full test suite is green while the known Safety
    Phase1Test failure remains.
10. Never use `git add .` while audit artifacts are untracked.
11. Never force-push unless explicitly authorized.
12. Never amend/rebase a completed commit without explicit approval.
13. Commit only after Master says **COMMIT GO**.
14. Push only after Master says **PUSH GO**.
15. If a later phase conflicts with this document, stop and perform a
    fresh discovery rather than guessing.

------------------------------------------------------------------------

# 1. PROJECT PURPOSE

TravelAI Nepal is an AI/travel ecosystem connecting travelers,
providers/agencies, guides, services, routes, geographic locations,
safety information, and future interactive geographic experiences.

The long-term Globe/Map vision is:

``` text
Explore Nepal
    ↓
2D geographic exploration
    ↓
Districts + cities + waypoints
    ↓
Services
    ↓
Routes
    ↓
3D geographic experience
    ↓
Journey visualization
    ↓
AI-generated / Provider-created Journey
```

Core design principle:

> **"Page नै map हो। Map सजावट होइन।"**

The map should be a functional geographic discovery system, not merely a
decorative hero graphic.

------------------------------------------------------------------------

# 2. CURRENT PROJECT STACK / REALITY

Confirmed from audits:

  -----------------------------------------------------------------------
  Component                           Reality
  ----------------------------------- -----------------------------------
  Laravel                             13.x

  PHP                                 8.3+

  Vite                                7.x

  Tailwind                            v4, CSS-based

  Leaflet                             1.9.4 CDN

  Globe.gl                            CDN

  Three.js                            CDN

  topojson-client                     v3 CDN

  axios                               Installed

  laravel/ai                          Installed

  Stripe PHP                          Installed, but payment activation
                                      is not part of current Globe work

  Local                               Windows 11 + Laragon
  -----------------------------------------------------------------------

### Important architectural correction

An older conceptual roadmap proposed a large new architecture with:

-   Alpine.js
-   new `explore/map.blade.php`
-   many new map components
-   `Destination` model/table
-   CesiumJS
-   separate map modules

The **actual audited codebase did not require that rewrite for GLOBE-01
through GLOBE-04**.

Current strategy is deliberately additive:

``` text
Existing Explore page
        +
existing Leaflet
        +
existing Globe.gl
        +
existing DB geography
        +
small scoped enhancements
```

Do not rebuild the existing Explore page just because an older roadmap
suggested doing so.

------------------------------------------------------------------------

# 3. PROJECT-WIDE SUBSCRIPTION / SECURITY AUDIT HISTORY

Before Globe work, TravelAI Nepal received a substantial
subscription/business-rule/security audit.

## 3.1 Pricing specification

### Free

-   Basic Dashboard
-   3 Listings/Services
-   5 AI Requests/month
-   10 Bookings/month
-   3 Services
-   1 Staff
-   5 AI requests
-   no custom logo
-   no advanced dashboard
-   no analytics
-   no white-label
-   no priority support

### Professional

-   Rs. 4,499/month
-   Advanced Dashboard
-   20 Listings/Services
-   50 AI Requests/month
-   100 Bookings/month
-   Custom Logo
-   20 Services
-   5 Staff
-   50 AI requests

### Business

-   Rs. 11,999/month
-   Full Analytics
-   100 Listings/Services
-   500 AI Requests/month
-   1000 Bookings/month
-   White-label
-   Custom Logo
-   100 Services
-   20 Staff
-   500 AI requests

### Enterprise

-   Custom pricing
-   Unlimited Listings/Services
-   Unlimited Staff
-   Unlimited AI
-   Unlimited Bookings
-   Priority Support
-   Custom Logo
-   White-label
-   Advanced Dashboard
-   Full Analytics

## 3.2 Payment decision

Approved product decision:

-   Pro remains Rs. 4,499/month.
-   Business remains Rs. 11,999/month.
-   Enterprise remains Custom.
-   Paid activation is **Contact Sales** for now.
-   Payment gateway implementation is not part of the current Globe/fix
    phase.
-   Pricing should not falsely claim eSewa/Khalti/bank-transfer
    activation.
-   Future payment implementation requires its own specification and
    current provider verification.

## 3.3 Major completed fix sequence

Completed fix commits included:

-   `0622334` --- FIX-01
-   `f07437e` --- FIX-02
-   `580c2a6` --- FIX-03
-   `65fc2d4` --- FIX-04
-   `99f23c9`, `b3c5f4f` --- FIX-05
-   `b5b8c25` --- FIX-06
-   `965a7bb` --- FIX-07
-   `1fba674` --- FIX-08
-   `b10d3d0` --- FIX-09
-   `d9e7836` --- FIX-10
-   `122225e` --- FIX-11
-   `5f037d9` --- FIX-12
-   `053e51c` --- FIX-16
-   `8eb0c6e` --- FIX-15
-   `a8c33b9` --- FIX-14
-   `d242b92` --- FIX-18

Additional production-readiness work:

-   `5cf93c7` --- Composer advisory remediation
-   `5c52f99` --- exception/client leak remediation
-   `67a5d13f` --- queue/scheduler safety
-   `00fbc3c` --- timezone hardening
-   `cff165c` --- logging observability
-   `cdd49fd` --- production-readiness/main synchronization cycle

## 3.4 Known test-suite baseline

The full suite historically had:

``` text
21 passing
1 known pre-existing Safety Phase1Test failure
```

The Safety failure was not treated as a Globe regression.

**Never say "full suite green" unless that exact failure is later
resolved and independently verified.**

------------------------------------------------------------------------

# 4. DATABASE / GEOGRAPHIC BASELINE

Verified Globe discovery baseline:

  Entity                    Count
  ----------------------- -------
  Locations                   294
  Waypoints total             752
  Active waypoints            716
  Routes total                143
  Active routes               138
  Deleted routes                5
  Route segments total       1360
  Active route segments       817
  Services total             1169
  Active services            1160
  Service categories            7
  Planner requests           6636
  Planner results            6636
  Itinerary days            32508
  Itinerary items           32853

Important relationships:

-   630 active waypoints are linked to locations.
-   86 active waypoints have no `location_id`.
-   716 active waypoints have coordinates.
-   546 active waypoints are overnight stops.
-   Services are largely location-linked.
-   28 services were identified as missing a location link in the Globe
    discovery baseline.
-   Duplicate coordinate groups exist.
-   Duplicate waypoint-name groups exist.

------------------------------------------------------------------------

# 5. LOCATION DATA WARNING

`locations.state` is mixed-quality metadata.

It is not guaranteed to be a clean administrative-district field.

Known province/non-administrative values:

``` text
Bagmati
Gandaki
Lumbini
Rolwaling
```

Interpretation:

-   Bagmati = province value in affected records
-   Gandaki = province value in affected records
-   Lumbini = province value in affected records
-   Rolwaling = valley/non-administrative geographic value

Known spelling variants:

``` text
CHITAWAN → Chitwan
DHANUSHA → Dhanusa
KAPILBASTU → Kapilavastu
KABHREPALANCHOK → Kavrepalanchok
```

Rules:

-   only the four explicit variants may be normalized for district
    matching
-   no fuzzy matching
-   no automatic province-to-district interpretation
-   no invented district relationships
-   no assumption that every waypoint has a district mapping

------------------------------------------------------------------------

# 6. GLOBE-00 --- INITIAL AUDIT --- CLOSED

The original Globe audit established:

-   actual Laravel structure
-   existing Explore routes
-   existing models
-   existing location/service/route/waypoint relationships
-   existing Leaflet
-   existing Globe.gl
-   absence of Cesium
-   absence of Alpine at that time
-   existence of Safety location-resolution infrastructure
-   absence of a `destinations` table

The audit also corrected several assumptions from the older conceptual
roadmap.

### Critical original findings

-   no `destinations` table
-   97%+ of services were location-linked
-   752 waypoints had geographic data
-   1360 route segments existed
-   143 routes existed
-   `locations.state` mixed province/district semantics
-   AI Planner fallback behavior required a separate gate
-   existing Globe.gl was already present
-   no need to blindly create a new map architecture

GLOBE-00 ended as discovery only.

------------------------------------------------------------------------

# 7. GLOBE-01 --- MAP INIT API --- CLOSED

## Endpoint

``` text
GET /api/map/init
```

Route name:

``` text
api.map.init
```

Middleware:

``` text
throttle:api
```

Public/auth-free.

## Response

Contains:

-   waypoints
-   routes
-   categories
-   service_counts
-   center
-   meta

Confirmed:

``` text
716 waypoints
138 active routes
7 categories
241 service-count location keys
```

Center:

``` text
28.3949, 84.1240
```

Waypoint response fields include:

``` text
id
name
slug
type
lat
lng
altitude
is_overnight
location_id
state
city
```

Cache:

``` text
map:init:v1
TTL = 600 seconds
```

Manual payload was approximately 155 KB raw.

Test:

``` text
MapDataTest = 16/16 PASS
```

Commit:

``` text
04a2081
```

------------------------------------------------------------------------

# 8. GLOBE-02 --- NEPAL DISTRICT BOUNDARIES --- CLOSED

Source audited:

OKNP `localboundaries` dataset.

Confirmed:

-   77 districts
-   7 provinces
-   valid geometry for 77/77 districts
-   properties include:
    -   `DISTRICT`
    -   `STATE_CODE`
    -   `PR_NAME`

Stable source identity:

``` text
STATE_CODE + DISTRICT
```

Planned feature ID:

``` text
NPL-D-{STATE_CODE}-{DISTRICT}
```

## Files

``` text
public/map/nepal-districts.topojson
public/map/ATTRIBUTION.txt
```

Decision:

-   district geometry loaded initially
-   province geometry deferred
-   3D boundary overlay deferred

Province + district combined compressed size exceeded the original
target, so district-only was selected.

Commit:

``` text
af472d8
```

------------------------------------------------------------------------

# 9. GLOBE-03 --- REAL CITY WAYPOINT LAYER --- CLOSED

## Discovery

Existing Explore page contained:

-   16 hardcoded Leaflet hero/marketing pins
-   10 hardcoded Globe.gl hero pins

These were not reliable DB waypoints.

Therefore the architecture was locked as:

``` text
Layer A = existing hero/marketing pins
Layer B = real API-driven waypoint layer
```

Never pretend Layer A is database waypoint data.

## Selected real dataset

Only active waypoints with:

``` text
type = city
```

25 source city waypoints were found.

They were grouped into 8 coordinate groups:

1.  Pokhara --- 1
2.  Pokhara --- 9
3.  Dharan --- 2
4.  Patan --- 2
5.  Bhaktapur --- 3
6.  Lumbini --- 1
7.  Chitwan --- 1
8.  Kathmandu --- 6

Total:

``` text
25 source waypoints
→
8 rendered coordinate groups
```

No jitter.

No coordinate mutation.

No data hiding.

## Marker styling

Real city markers:

``` text
#991b1b
```

Existing Wildlife green:

``` text
#10b981
```

## UI additions

Added:

``` text
📍 Mapped Cities
```

with 8 chips.

Added hint:

``` text
📍 8 mapped cities · zoom in to explore
```

Hint behavior:

-   visible around zoom 7--8
-   hidden at zoom \>= 9

Chip click:

-   pans/zooms
-   opens grouped popup

## Popup behavior

Example:

``` text
Pokhara (9 waypoints)
Type: city
Alt: 827m
```

If all grouped records have the same source name, no fake "additional
names" are displayed. The source-record count remains preserved.

## Regression

Verified:

-   16 hero pins unchanged
-   hero popups unchanged
-   `.map-filter` remains hero-pin filtering
-   service category chips remain service-grid filtering
-   search works
-   service grid works
-   district boundaries remain
-   Globe.gl remains
-   API remains unchanged

Commit:

``` text
60dabc5
```

GLOBE-03 = CLOSED.

------------------------------------------------------------------------

# 10. GLOBE-04 --- DISTRICT INTERACTION --- LOCAL COMPLETE / PUSH PENDING

## Locked scope

Frontend-only.

Primary application file:

``` text
resources/views/public/services/index.blade.php
```

Infrastructure config was deliberately separated into:

``` text
.gitignore
```

## Desktop

District click:

``` text
district click
→ selected district
→ dark-red selected outline
→ right-side panel
→ geometry-based fitBounds
```

## Mobile

District click:

``` text
district click
→ bottom-sheet/bottom-panel
```

## Panel data

Only evidence-backed data:

-   district name
-   province
-   reliably mapped waypoint count
-   waypoint list:
    -   name
    -   type
    -   altitude

No invented:

-   service counts
-   route counts
-   AI stats
-   popularity
-   rankings

## Mapping rules

Only:

``` text
CHITAWAN → Chitwan
DHANUSHA → Dhanusa
KAPILBASTU → Kapilavastu
KABHREPALANCHOK → Kavrepalanchok
```

No fuzzy matching.

No auto-mapping of:

``` text
Bagmati
Gandaki
Lumbini
Rolwaling
```

## Empty district behavior

All 77 districts remain clickable.

Empty/unavailable mapping state:

``` text
No waypoint data mapped to this district yet.
```

Do not hide or gray out empty districts.

## Interaction

-   X closes panel
-   ESC closes panel
-   clicking another district updates the same panel
-   selected district is tracked
-   hover respects selected state
-   fitBounds uses district geometry
-   no jitter
-   no coordinate mutation

## Mobile issue discovered during verification

Original:

``` css
.globe-wrap-premium {
    max-width: 600px;
}
```

could force a narrow viewport to a 600px document width.

Observed:

``` text
Viewport 384
Document 600
Overflow 216px
```

Fix:

``` css
.globe-wrap-premium {
    max-width: min(600px, 100%);
    overflow: hidden;
}
```

After:

``` text
Viewport 384
Document 384
Overflow 0
```

This was directly discovered while validating GLOBE-04 mobile behavior.

## Mobile runtime

At 375×812:

-   page load --- PASS
-   no horizontal scroll --- PASS
-   district tap --- PASS
-   bottom sheet --- PASS
-   full-width panel --- PASS
-   readable content --- PASS
-   X close --- PASS
-   district switching --- PASS
-   empty district --- PASS
-   Chitwan spelling variant --- PASS
-   hero pins --- PASS
-   legend --- PASS
-   hint --- PASS
-   no new GLOBE-04 console errors --- PASS

## Desktop

-   3D Globe preserved
-   right district panel works
-   existing layout preserved

## Runtime examples

Kathmandu:

``` text
3 waypoints
```

Palpa:

``` text
9 waypoints
```

Chitwan:

``` text
6 waypoints
matched via CHITAWAN variant
```

Kavrepalanchok:

``` text
8 waypoints
matched via KABHREPALANCHOK variant
```

Nawalparasi_w:

``` text
empty-state message
```

Dhading:

``` text
empty/unavailable mapping state
```

## Helper logic

GLOBE-04 added helpers including:

``` text
findWaypointsForDistrict()
renderDistrictPanelContent()
openDistrictPanel()
closeDistrictPanel()
normalizeStateName()
escapeHtmlSafe()
```

The implementation reuses the shared waypoint cache.

No additional map-init fetch was introduced.

## Local commits

Infrastructure:

``` text
42c418f
chore(gitignore): ignore ngrok binary
```

Feature:

``` text
9c96fd2
feat(globe): add district interaction panel (GLOBE-04)
```

------------------------------------------------------------------------

# 11. CURRENT GIT STATE

At the time of this handoff:

``` text
local HEAD = 9c96fd2
origin/feature/globe-system = 60dabc5
```

Local is:

``` text
2 commits ahead
```

The two commits are:

``` text
42c418f
9c96fd2
```

The working tree has no modified tracked application files; audit
artifacts remain untracked.

Untracked examples:

``` text
_globe02_audit/
_globe02_discover.txt
_globe02_micro/
_globe02_precommit/
_globe03_audit/
_globe03_diff.txt
_globe04_audit/
_globe04_diff.txt
docs/Globe_Master_File.md
docs/plan_limits/FIX-*.md
```

`ngrok` is ignored by `.gitignore`.

## Push rule

At handoff:

> **PUSH HOLD until explicit PUSH GO.**

After explicit authorization:

``` cmd
cd C:\laragon\www\TravelAI-Nepal
git push origin feature/globe-system
git status -sb
git log --oneline -3
git ls-remote origin refs/heads/feature/globe-system
```

Expected remote HEAD:

``` text
9c96fd2
```

Never force-push.

------------------------------------------------------------------------

# 12. CURRENT GLOBE ROADMAP

  Phase                                   Status
  --------------------------------------- ---------------------------------
  GLOBE-00 --- audit                      CLOSED
  GLOBE-01 --- map-init API               CLOSED
  GLOBE-02 --- district geometry          CLOSED
  GLOBE-03 --- city waypoint layer        CLOSED
  GLOBE-04 --- district interaction       LOCALLY COMPLETE / PUSH PENDING
  GLOBE-05 --- 3D discovery/enhancement   NEXT
  GLOBE-06 --- route visualization        FUTURE
  GLOBE-07 --- Journey/AI integration     HOLD / AI AUDIT GATE
  GLOBE-08 --- final QA                   FUTURE
  GLOBE-09 --- production deployment      FUTURE

------------------------------------------------------------------------

# 13. GLOBE-05 --- 3D DISCOVERY --- NEXT

GLOBE-05 must start as:

``` text
READ-ONLY DISCOVERY
```

No immediate implementation.

## Questions to answer from actual code

1.  Where is `initGlobe()`?
2.  How is the Globe modal opened?
3.  How is it closed?
4.  What Globe.gl version/CDN is loaded?
5.  What Three.js version/CDN is loaded?
6.  How are the current 10 hero pins rendered?
7.  Is auto-rotation implemented?
8.  How is reset implemented?
9.  What is the current lifecycle on repeated open/close?
10. Is lazy loading already present?
11. What happens if Globe.gl initialization fails?
12. What happens on mobile?
13. What happens on slow network?
14. What causes the existing `process is not defined` console message?
15. Is that message functionally harmful?
16. Can Globe.gl support future route lines?
17. Can it support altitude?
18. Can it support future Journey visualization?
19. Can it support the desired interactions without excessive
    performance cost?
20. Is Cesium actually necessary?

## Important engine rule

Older roadmap documents mention CesiumJS.

Current actual project already uses Globe.gl.

Therefore:

> Do not install Cesium or remove Globe.gl merely because the old
> roadmap mentioned Cesium.

The discovery must prove the need first.

------------------------------------------------------------------------

# 14. GLOBE-06 --- ROUTE VISUALIZATION --- FUTURE

Verified route data:

``` text
143 total routes
138 active routes
1360 route segments
817 active segments
```

Route metadata includes:

``` text
id
name
slug
route_type
difficulty
duration_days
max_altitude
service_category_id
```

Potential future features from the conceptual roadmap:

-   route selector
-   2D route visualization
-   3D polyline
-   fly-through
-   optional elevation profile

These are not yet locked requirements.

Before GLOBE-06 implementation:

1.  audit route-segment ordering
2.  verify coordinate completeness
3.  verify waypoint relationships
4.  detect broken/duplicate segments
5.  decide 2D/3D responsibility
6.  decide performance strategy
7.  lock exact scope
8.  implement
9.  verify
10. commit
11. push

------------------------------------------------------------------------

# 15. GLOBE-07 --- JOURNEY / AI INTEGRATION --- HOLD

Long-term abstraction:

``` text
Journey
├── AI-generated
└── Provider-created
```

The Globe renderer should eventually be able to display both.

However:

> **Do not create a Journey database schema yet.**

## Existing AI Planner architecture

``` text
POST /api/planner/generate
        ↓
PlannerController
        ↓
PlannerService
        ↓
resolveRoute()
        ↓
route_segments
        ↓
getServicesForDay()
        ↓
buildFallbackResponse()
        ↓
ItineraryValidator
        ↓
DB transaction
        ↓
PlannerRequest
PlannerResult
ItineraryDay
ItineraryItem
```

Existing output relationships include:

-   `overnight_waypoint_id` → `waypoints.id`
-   service IDs → `services.id`

Known earlier issue:

``` text
validation_status = fallback
fallback_used = 1
```

This was explicitly kept outside early Globe phases.

## GLOBE-07 gate

Before integration:

-   audit AI Planner
-   understand fallback behavior
-   verify itinerary data quality
-   verify route/waypoint/service relationships
-   decide normalized geographic output
-   lock integration contract
-   only then modify AI/Globe code

------------------------------------------------------------------------

# 16. GLOBE-08 --- FINAL QA

## Functional

-   Explore page
-   77 districts
-   hover
-   click
-   selected highlight
-   panel
-   empty districts
-   explicit spelling variants
-   Mapped Cities chips
-   hero pins
-   service search
-   service categories
-   service grid
-   Globe
-   routes
-   Journey when authorized

## Browser

Target:

-   Chrome
-   Edge
-   Firefox
-   Safari
-   Android Chrome
-   iOS Safari

## Network

-   WiFi
-   4G
-   throttled 3G
-   failed API
-   failed 3D initialization
-   slow device

## Accessibility

-   Tab
-   Enter
-   Space where appropriate
-   ESC
-   focus state
-   screen-reader semantics
-   contrast
-   reduced-motion

## Security

-   CSRF
-   XSS
-   IDOR
-   rate limiting
-   public API behavior
-   error leakage
-   production debug settings

## Performance targets

These are targets to measure, not guarantees:

  Metric               Target
  -------------------- ---------
  Initial load         \< 2s
  3D load on WiFi      \< 4s
  Lighthouse desktop   \> 90
  Lighthouse mobile    \> 80
  FCP                  \< 1.5s
  LCP                  \< 2.5s
  CLS                  \< 0.1
  Bundle               \< 5MB

------------------------------------------------------------------------

# 17. GLOBE-09 --- DEPLOYMENT

Only after GLOBE-08 is complete:

1.  production build
2.  asset verification
3.  map asset verification
4.  API verification
5.  cache verification
6.  SSL verification
7.  production environment verification
8.  `APP_DEBUG=false`
9.  Explore smoke test
10. district smoke test
11. 3D smoke test
12. monitoring
13. rollback readiness

Do not deploy an unverified large architectural change.

------------------------------------------------------------------------

# 18. FUTURE POST-LAUNCH

After launch:

-   monitor API failures
-   monitor 3D failures
-   monitor mobile performance
-   monitor interaction behavior
-   collect feedback
-   fix verified bugs
-   consider richer geographic data
-   consider route fly-through
-   consider Journey visualization
-   consider deeper provider/service discovery

Each major feature needs its own discovery and scope lock.

------------------------------------------------------------------------

# 19. IMPORTANT EXISTING UX / TECHNICAL ITEMS

## 19.1 Hero-pin overlap

Known from GLOBE-03.

Examples:

-   Kathmandu exact overlap
-   Lumbini near
-   Pokhara near
-   Patan near
-   Chitwan near
-   Bhaktapur near

GLOBE-03 intentionally did not:

-   jitter markers
-   move hero pins
-   mutate coordinates
-   hide real waypoint data

This remains a future UX issue.

## 19.2 Header mobile stacking

Known pre-existing issue in:

``` text
layouts/public.blade.php
```

It is outside GLOBE-04.

## 19.3 Existing console warnings

Known pre-existing messages include:

-   Edge Tracking Prevention
-   Tailwind CDN production warning
-   Globe.gl/Three.js: `process is not defined`
-   apple-mobile-web-app-capable deprecation
-   service-worker informational messages

Future phase reports must distinguish pre-existing messages from
phase-specific errors.

------------------------------------------------------------------------

# 20. API / DATA PERFORMANCE RULES

Current map-init response:

``` text
716 active waypoints
138 active routes
7 categories
~155 KB raw
```

GLOBE-03 intentionally rendered only the city subset:

``` text
25 records
→ 8 coordinate groups
```

Do not immediately render all 716 waypoints as DOM-heavy Leaflet
markers.

The discovery suggested:

-   \~50 markers: generally manageable
-   \~100: may become noticeable on some mobile devices
-   \~200+: potential lag
-   716: requires aggregation/clustering/performance design

These are planning observations, not guarantees.

------------------------------------------------------------------------

# 21. DISTRICT COVERAGE RULE

There are:

``` text
77 official district geometries
```

but only approximately:

``` text
33 / 77 districts
```

have reliable waypoint mapping through the current database path.

Approximately:

``` text
44 / 77
```

do not.

Therefore:

> **77 polygons does not mean 77 districts have complete geographic
> data.**

Never claim complete district waypoint coverage without a new verified
audit.

------------------------------------------------------------------------

# 22. CURRENT MAP LAYER MODEL

Conceptually:

``` text
Map
├── Base map
├── District boundaries
│   └── existing district pane / hierarchy
├── Real city waypoint markers
│   └── dark red
├── Existing hero/marketing pins
│   └── preserved
└── UI
    ├── legend
    ├── hint
    ├── Mapped Cities chips
    └── district panel
```

Do not alter this hierarchy casually.

------------------------------------------------------------------------

# 23. DESTINATION MODEL / TABLE STATUS

Older conceptual plans proposed:

``` text
destinations
service_destination
Destination model
DestinationMatcher
```

These have NOT been created as part of GLOBE-01 through GLOBE-04.

Current rule:

> Do not create a Destination database schema simply to continue the
> Globe roadmap.

If a true Destination abstraction is later needed:

``` text
READ-ONLY AUDIT
→ schema proposal
→ Master approval
→ migration
```

No shortcut.

------------------------------------------------------------------------

# 24. PROVINCE / DISTRICT NORMALIZATION STATUS

No new province/district DB tables were created for GLOBE-01 through
GLOBE-04.

Current static official district geometry is the authoritative boundary
visualization.

The DB `locations.state` field is treated as raw metadata with known
ambiguity.

Do not:

-   rewrite all locations
-   assume state means district
-   auto-map provinces to districts
-   use fuzzy matching

without a dedicated administrative geography project.

------------------------------------------------------------------------

# 25. FILE PROTECTION LIST

Unless a phase explicitly authorizes them, do not modify:

``` text
app/Models/*
database/migrations/*
routes/*
app/Services/PlannerService.php
app/Services/ItineraryValidator.php
app/Services/ItineraryGenerator.php
app/Services/Safety/LocationResolutionService.php
composer.json
composer.lock
package.json
package-lock.json
vite.config.js
public/map/nepal-districts.topojson
public/map/ATTRIBUTION.txt
```

GLOBE-04 specifically modified only:

``` text
resources/views/public/services/index.blade.php
```

plus the separate `.gitignore` infrastructure commit.

------------------------------------------------------------------------

# 26. FUTURE PHASE TEMPLATE

Every new phase should use this exact structure.

## A. Discovery

Prompt:

``` text
READ-ONLY AUDIT ONLY.
NO CODE.
NO DB.
NO MIGRATION.
NO COMMIT.
NO PUSH.
```

Report:

-   current architecture
-   data evidence
-   current UI
-   risks
-   exact files
-   unknowns
-   possible options

## B. Master review

Master locks:

-   exact scope
-   exact files
-   UX behavior
-   exclusions
-   tests
-   rollback

## C. Implementation

Prompt:

``` text
IMPLEMENTATION AUTHORIZED.
ONLY LOCKED SCOPE.
COMMIT/PUSH HOLD.
```

## D. Verification

Must include:

-   runtime evidence
-   targeted tests
-   regression
-   git diff
-   file list
-   DB baseline
-   console result
-   mobile evidence when applicable

## E. Commit

Only:

``` text
COMMIT GO
```

## F. Push

Only:

``` text
PUSH GO
```

------------------------------------------------------------------------

# 27. A--Z MASTER ROADMAP

## A --- Audit

Current:

-   Globe architecture audited
-   subscription/security audit completed
-   DB baseline established

Future:

-   each major phase starts with discovery

## B --- Boundaries

Current:

-   77 districts loaded
-   official boundary asset exists

Future:

-   richer province/3D boundary work only if authorized

## C --- Cities

Current:

-   25 city waypoint records
-   8 rendered coordinate groups
-   Mapped Cities chips

Future:

-   richer city exploration if needed

## D --- District Interaction

Current:

-   GLOBE-04 complete locally
-   desktop panel
-   mobile bottom sheet
-   empty states
-   explicit mappings

## E --- Engine

Next:

-   GLOBE-05 discovery
-   decide whether Globe.gl remains

## F --- Filters

Current:

-   service category filters work
-   hero-pin map filters remain
-   city chips work

Future:

-   geographic filtering only after explicit scope

## G --- Globe

Current:

-   Globe.gl already exists
-   3D experience exists

Future:

-   performance/lifecycle/error handling audit

## H --- Hero Pins

Current:

-   preserved
-   overlap known

Future:

-   separate UX fix if authorized

## I --- Integration

Future:

-   2D ↔ 3D state sync
-   deep links
-   URL state

## J --- Journey

Future:

-   normalized Journey renderer
-   no DB schema yet

## K --- Keyboard / Accessibility

Future final QA:

-   Tab
-   Enter
-   ESC
-   focus
-   reduced motion
-   screen reader

## L --- Locations

Current:

-   294 locations
-   state semantics imperfect

Future:

-   administrative normalization only via dedicated audit

## M --- Mobile

Current:

-   GLOBE-04 tested at 375×812
-   horizontal overflow fixed

Future:

-   broader device matrix

## N --- Network

Future:

-   3G
-   4G
-   failed API
-   failed 3D
-   degraded mode

## O --- Optimization

Future:

-   bundle
-   image
-   cache
-   compression
-   Lighthouse
-   N+1 review

## P --- Performance

Future:

-   marker density
-   3D lifecycle
-   memory leak testing
-   load timings

## Q --- QA

Future:

-   GLOBE-08

## R --- Routes

Future:

-   GLOBE-06

## S --- Services

Current:

-   1169 total
-   1160 active
-   service filters/search remain intact

Future:

-   geographic service visualization

## T --- Testing

Current:

-   targeted GLOBE tests completed through GLOBE-04
-   MapDataTest 16/16 baseline

Future:

-   full matrix in GLOBE-08

## U --- UI

Current:

-   existing Explore UI preserved
-   district panel added
-   city chips added

Future:

-   polish only through explicit scope

## V --- Validation

Current:

-   geographic mappings use explicit rules
-   HTML output is escaped

Future:

-   complete accessibility/security validation

## W --- Web/API

Current:

``` text
/api/map/init
```

Future:

-   new endpoints only when justified

## X --- XSS / Security

Current:

-   subscription/security hardening completed
-   GLOBE-04 uses escaping helper

Future:

-   final security regression

## Y --- Year/Release

Deployment should happen only after:

``` text
GLOBE-05
→ GLOBE-06
→ GLOBE-07 gate
→ GLOBE-08
→ GLOBE-09
```

as applicable to the actual approved product scope.

## Z --- Zero-Regression Principle

Every Globe phase must preserve:

-   existing Explore service behavior
-   existing hero pins
-   existing service search
-   existing category filtering
-   existing desktop layout
-   existing API behavior unless explicitly changed
-   AI Planner unless explicitly authorized

------------------------------------------------------------------------

# 28. IMMEDIATE NEXT ACTIONS --- EXACT ORDER

## Step 1 --- GLOBE-04 push authorization

Current status:

``` text
LOCAL COMPLETE
PUSH PENDING
```

Wait for explicit:

``` text
PUSH GO
```

Then:

``` cmd
git push origin feature/globe-system
git status -sb
git log --oneline -3
git ls-remote origin refs/heads/feature/globe-system
```

Verify local and remote:

``` text
9c96fd2
```

## Step 2 --- GLOBE-05 discovery

Do not code immediately.

Ask the next AI to perform a read-only audit of the existing Globe.gl
implementation.

## Step 3 --- Master locks GLOBE-05

Only after discovery.

## Step 4 --- Implement and verify GLOBE-05

No unrelated refactor.

## Step 5 --- GLOBE-06 route discovery

Audit first.

## Step 6 --- GLOBE-07 AI/Journey gate

Do the AI Planner audit before touching AI integration.

## Step 7 --- GLOBE-08 final QA

Full browser/device/network/accessibility/security/performance
validation.

## Step 8 --- GLOBE-09 deployment

Only after QA.

------------------------------------------------------------------------

# 29. WHAT NOT TO DO NEXT

Do NOT:

-   create Destination tables
-   create province/district tables
-   install Cesium just because an old document mentioned it
-   replace Globe.gl without discovery
-   rewrite the Explore Blade architecture
-   change AI Planner during GLOBE-05/06
-   change route data without route audit
-   modify district geometry
-   jitter markers
-   move hero pins
-   auto-map Bagmati/Gandaki/Lumbini/Rolwaling
-   fuzzy-match districts
-   add random CDN libraries
-   add random npm dependencies
-   use `git add .`
-   force-push
-   reset/rebase completed history
-   push without PUSH GO

------------------------------------------------------------------------

# 30. FINAL CURRENT STATUS

``` text
TravelAI Nepal
│
├── Project-wide subscription/security hardening
│   └── Major planned fixes completed
│
├── Globe System
│   │
│   ├── GLOBE-00  Audit ................ CLOSED
│   ├── GLOBE-01  Map API .............. CLOSED
│   ├── GLOBE-02  District boundaries .. CLOSED
│   ├── GLOBE-03  City waypoints ....... CLOSED
│   ├── GLOBE-04  District interaction . LOCAL COMPLETE
│   │
│   │                         ↓
│   │                    PUSH PENDING
│   │
│   ├── GLOBE-05  3D discovery ......... NEXT
│   ├── GLOBE-06  Routes ............... FUTURE
│   ├── GLOBE-07  Journey/AI .......... HOLD
│   ├── GLOBE-08  QA ................... FUTURE
│   └── GLOBE-09  Deployment .......... FUTURE
│
└── Immediate:
    PUSH GO → push → verify → GLOBE-05 discovery
```

------------------------------------------------------------------------

# 31. CONTINUATION PROMPT --- GIVE THIS TO ANOTHER AI

Use this prompt when handing the project to another AI:

``` text
You are continuing the TravelAI Nepal project.

FIRST: read the entire A–Z Master Handoff file.

Treat the document as the current continuity source of truth.

Current Globe status:
- GLOBE-00 CLOSED
- GLOBE-01 CLOSED
- GLOBE-02 CLOSED
- GLOBE-03 CLOSED
- GLOBE-04 locally complete and committed
- GLOBE-04 push is pending explicit authorization
- local HEAD = 9c96fd2
- remote Globe branch was 60dabc5 at handoff

GLOBE-04 commits:
- 42c418f = .gitignore infrastructure
- 9c96fd2 = GLOBE-04 feature

DO NOT push until explicit PUSH GO.

The next phase is GLOBE-05.

GLOBE-05 MUST START WITH READ-ONLY DISCOVERY.

During discovery:
- no code changes
- no database changes
- no migrations
- no package installation
- no API changes
- no commits
- no push

Audit the EXISTING Globe.gl/Three.js implementation.

Do not install Cesium or replace Globe.gl just because an older roadmap mentioned Cesium. Determine from actual evidence whether a change is necessary.

Preserve existing Explore desktop behavior.

Do not rewrite the Explore architecture.

AI Planner is protected until the GLOBE-07 gate.

Never invent geographic mappings.

Never fuzzy-match districts.

Never automatically map Bagmati, Gandaki, Lumbini, or Rolwaling to districts.

Only these spelling variants are explicitly allowed:
- CHITAWAN → Chitwan
- DHANUSHA → Dhanusa
- KAPILBASTU → Kapilavastu
- KABHREPALANCHOK → Kavrepalanchok

Remember:
- 77 official district geometries exist
- only about 33/77 districts currently have reliable waypoint mapping through the existing DB path
- 86 active waypoints lack location_id
- 716 active waypoints have coordinates
- 138 active routes exist
- 1160 active services exist

Use actual runtime and database evidence.

Never claim the historical full suite is green:
baseline was 21 pass + 1 known pre-existing Safety Phase1Test failure.

Never use:
git add .

Never force-push.

Every phase must follow:
Audit → Master Review → Scope Lock → Implement → Verify → COMMIT GO → Commit → PUSH GO → Push → Verify.

Your first deliverable is a READ-ONLY GLOBE-05 DISCOVERY REPORT.
End discovery with:
COMMIT/PUSH: HOLD — awaiting Master review.
```

------------------------------------------------------------------------

# 32. MASTER HANDOFF SUMMARY

**Current stage:** GLOBE-04 local completion / push pending.

**Completed Globe work:**

``` text
GLOBE-01 → map-init API
GLOBE-02 → 77 official district boundaries
GLOBE-03 → real city waypoint layer + chips + hint
GLOBE-04 → district interaction panel + mobile bottom sheet
```

**Current local commits:**

``` text
42c418f
9c96fd2
```

**Next technical phase:**

``` text
GLOBE-05 — READ-ONLY 3D DISCOVERY
```

**Later:**

``` text
GLOBE-06 — Routes
GLOBE-07 — Journey/AI after AI audit
GLOBE-08 — Final QA
GLOBE-09 — Deployment
```

**Most important principle:**

> **Do not blindly follow the old roadmap. Continue from the actual
> audited codebase and the locked decisions recorded in this document.**

------------------------------------------------------------------------

# DOCUMENT CONTROL

**Document:** TravelAI Nepal A--Z Master Handoff & Continuation Plan\
**Version:** 4.0\
**Last updated:** 2026-09-18\
**Current phase:** GLOBE-04 locally complete; push pending\
**Next phase:** GLOBE-05 read-only discovery\
**Primary branch:** `feature/globe-system`\
**Local HEAD:** `9c96fd2`\
**Remote HEAD at handoff:** `60dabc5`\
**Main branch:** protected / untouched by current Globe feature commits

# END
