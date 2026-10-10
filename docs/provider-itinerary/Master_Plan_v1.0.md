# TravelAI Nepal --- Provider Itinerary & Journey Experience

## Master Implementation Plan

### Version 1.0 --- 2026-09-18

### Status: MASTER PLAN DRAFT --- Ready for staged discovery/audit

------------------------------------------------------------------------

## 0. Master Decision

TravelAI Nepal will build a **Provider-authored Itinerary System**
inspired by the strong presentation patterns observed on Himalayan
Holidays Nepal, but it will **not clone their website or
implementation**.

The core product decision is:

> **Manual authoring first, structured data underneath, rich itinerary
> presentation on top, and AI/automation as an optional future layer.**

An itinerary being manually created by a provider does **not** mean it
has to be an unstructured block of text. TravelAI should let a provider
manually enter each day in a structured form, then automatically render
the same information consistently across:

-   Package/Tour detail page
-   Day-by-day itinerary timeline
-   2D map
-   3D Globe
-   Booking/inquiry context
-   Provider dashboard
-   Traveler view
-   Future AI-assisted planning

The Provider itinerary system must coexist with the existing AI Planner
and Globe system. It must not destabilize the existing AI Planner,
GLOBE-01 through GLOBE-07, billing, booking, or safety systems.

------------------------------------------------------------------------

# 1. Why We Are Building This

Himalayan Holidays demonstrates a useful **presentation model**:

-   strong package detail header
-   quick trip facts
-   section/tab navigation
-   day-by-day itinerary
-   compact day rows
-   expandable descriptions
-   "Expand All"
-   booking/inquiry area beside the itinerary
-   maps
-   inclusions/exclusions
-   availability/departures
-   reviews
-   related packages

The important lesson is **not** "copy Himalayan Holidays."

The lesson is:

> A traveler should be able to understand an entire trip progressively
> --- first the summary, then the itinerary, then map/logistics, then
> booking information.

TravelAI can improve this further because it already has structured
geographic data, waypoints, routes, services, and Globe infrastructure.

------------------------------------------------------------------------

# 2. Reference Observation: Himalayan Holidays

## 2.1 What was observed

The Himalayan Holidays homepage currently presents:

-   a Nepal location/program map
-   Fixed Departures
-   Travel Across Nepal
-   International Packages
-   Travel by Activities
-   service/value sections
-   testimonials
-   custom trip planning
-   package cards

Official homepage: https://himalayanholidaysnepal.com/

The supplied tour screenshots show a package-detail experience
containing:

-   hero image
-   package title
-   duration
-   location
-   group size
-   difficulty
-   section navigation
-   itinerary timeline
-   expandable day descriptions
-   Expand All
-   booking/inquiry panel
-   maps
-   inclusions/exclusions
-   availability
-   reviews
-   related packages

The website is a useful **UX reference**, not a source of TravelAI's
technical architecture.

## 2.2 Important distinction

The Himalayan Holidays itineraries are manually authored.

That is completely compatible with TravelAI.

Manual authoring should mean:

Provider: creates package enters itinerary days enters descriptions
selects locations/waypoints/services uploads media sets logistics

TravelAI: validates the structured data renders it consistently connects
it to maps calculates/display selected metadata exposes it to travelers
optionally makes it available to future AI workflows

So:

**Manual authoring ≠ manual rendering.**

------------------------------------------------------------------------

# 3. TravelAI Starting Point

TravelAI already has substantial geographic and itinerary
infrastructure.

Current audited baseline includes approximately:

-   Locations: 294
-   Waypoints: 752 total
-   Active waypoints: 716
-   Routes: 143 total
-   Active routes: 138
-   Services: 1169 total
-   Active services: 1160
-   Service categories: 7
-   Itinerary days: 25,200
-   Itinerary items: 25,545
-   Planner requests: 5,125
-   Planner results: 5,125

Geographic itinerary readiness:

-   24,297 / 25,200 itinerary days have `overnight_waypoint_id`
-   903 days currently have no overnight waypoint
-   480 distinct overnight waypoints are used

This means TravelAI already has much more geographic data than a small
curated destination map.

------------------------------------------------------------------------

# 4. Key Product Difference

Himalayan Holidays can present a relatively curated set of
destinations/packages.

TravelAI should not artificially limit itself to a small fixed
destination set.

Instead:

### Layer A --- Discovery

Show curated/important destinations and packages where useful.

### Layer B --- Structured geographic data

Support the full available dataset:

-   locations
-   waypoints
-   routes
-   services
-   itinerary days
-   itinerary items

### Layer C --- Journey visualization

Render the relevant subset for the current package/journey.

Therefore:

> The map does not need to show every TravelAI location at once.

The database can contain hundreds of locations while the UI shows only
the locations relevant to the current discovery context.

------------------------------------------------------------------------

# 5. Product Architecture

The future conceptual hierarchy is:

Provider └── Package / Tour ├── Overview ├── Itinerary │ ├── Day 1 │ │
├── title │ │ ├── description │ │ ├── overnight waypoint │ │ ├──
activities │ │ └── optional logistics │ ├── Day 2 │ ├── Day 3 │ └── ...
├── Route / Map ├── Inclusions ├── Exclusions ├── Availability ├──
Reviews └── Booking / Inquiry

This is a **product abstraction**, not an instruction to create the
database immediately.

------------------------------------------------------------------------

# 6. Manual Provider Authoring Model

The first provider workflow should be simple.

## Provider creates a package

Required:

-   package title
-   short summary
-   duration
-   destination/region
-   difficulty
-   cover image

Optional:

-   group size
-   maximum altitude
-   best season
-   starting point
-   ending point
-   price
-   currency
-   tags
-   activity type

## Provider creates itinerary days

For each day:

-   day number
-   day title
-   description
-   overnight location/waypoint
-   optional start location
-   optional end location
-   optional distance
-   optional walking/driving time
-   optional elevation gain
-   optional elevation loss
-   optional meals
-   optional accommodation
-   optional activities
-   optional media

Provider should never be forced to fill every field.

The UI should support:

> "Simple itinerary" first, "rich itinerary" optionally.

------------------------------------------------------------------------

# 7. Itinerary Presentation --- Master UX

## 7.1 Package header

Show:

-   hero image
-   package title
-   duration
-   destination
-   difficulty
-   group size where available
-   maximum altitude where available
-   starting/ending point where available

Keep the header visually clean.

------------------------------------------------------------------------

## 7.2 Section navigation

Recommended sections:

1.  Overview
2.  Itinerary
3.  Map
4.  Inclusions
5.  Availability
6.  Reviews

Do not force every package to have every section.

Hide empty sections.

------------------------------------------------------------------------

# 8. Itinerary Timeline UX

The core presentation should use a vertical timeline/accordion.

Example:

DAY 01 Kathmandu Arrival

Short summary...

▼

DAY 02 Kathmandu → Pokhara

Short summary...

▼

DAY 03 Pokhara → Trek Start

Short summary...

Each day can expand to reveal:

-   full description
-   overnight
-   activities
-   meals
-   accommodation
-   distance
-   elevation
-   route/location context
-   photos where available

------------------------------------------------------------------------

# 9. Expand/Collapse Behavior

Required:

-   individual day expand/collapse
-   Expand All
-   Collapse All
-   sensible default state

Recommended default:

-   first day expanded
-   remaining days collapsed

For very short itineraries:

-   optionally expand all by default

For long itineraries:

-   keep collapsed to avoid a huge wall of text.

------------------------------------------------------------------------

# 10. Day Metadata

Use compact metadata chips/icons.

Example:

**Day 05 --- Namche to Tengboche**

-   10 km
-   5--6 hrs
-   +500 m
-   Overnight: Tengboche
-   Meals: B/L/D

Do not invent missing values.

If a provider did not supply elevation, do not fabricate it.

If TravelAI can calculate a value reliably from structured geographic
data, that calculation must be explicitly identified as system-derived.

------------------------------------------------------------------------

# 11. Map Integration

The itinerary should eventually connect directly to the existing Globe
architecture.

## 2D

Show:

-   journey route
-   relevant waypoints
-   day markers

## 3D

Show:

-   journey path
-   relevant waypoint markers
-   altitude where reliable

The current GLOBE-06 route system already establishes relational route
geometry using:

`route_segments + waypoints`

Do not use legacy `routes.segments` JSON as authoritative geometry.

------------------------------------------------------------------------

# 12. Day-to-Map Interaction

Future UX:

Click:

`Day 07 — Namche → Tengboche`

Then:

-   map focuses on relevant area
-   relevant waypoint(s) highlight
-   relevant route segment/path highlights
-   itinerary day remains visibly selected

Conversely:

Click a map waypoint.

Then:

-   related itinerary day can be highlighted

This should be implemented only after the basic provider itinerary
presentation is stable.

------------------------------------------------------------------------

# 13. Journey Abstraction

TravelAI should eventually support at least two Journey origins:

### A. Provider Journey

Manually authored by a provider.

### B. Planner Journey

Produced from TravelAI's planner/read-model workflow.

The visual layer should not care where the journey came from.

Conceptually:

`Journey View Model`

↓

-   title
-   duration
-   days
-   route
-   waypoints
-   metadata
-   media
-   provider/creator
-   source type

Then the same renderer can display both.

------------------------------------------------------------------------

# 14. Important Current AI Constraint

The current PlannerService audit found that the active planner path is
deterministic fallback-based rather than a direct LLM generation path.

Therefore the Provider Itinerary System must **not** label current
planner data as "AI-generated" merely because planner tables contain an
AI model string.

Until the separate AI audit/fix is completed:

-   Provider-authored = provider-authored
-   Planner/fallback = planner-generated/read-model
-   AI-generated = only when the actual AI path is verified

This distinction must remain visible in future architecture.

------------------------------------------------------------------------

# 15. No Immediate Journey Database Redesign

The GLOBE-07 decision explicitly avoided creating:

-   `journeys`
-   `journey_days`

Do not reverse that decision simply because we are now designing
provider itineraries.

First:

1.  inspect existing provider/package/service models
2.  inspect current itinerary tables
3.  inspect ownership relationships
4.  inspect authorization
5.  inspect booking relationships
6.  inspect existing media
7.  inspect existing route/location relationships

Only then decide whether new provider itinerary tables are necessary.

------------------------------------------------------------------------

# 16. Future Data Model --- Conceptual Only

Potential future structure:

Provider ↓ Provider Package ↓ Package Itinerary ↓ Itinerary Day ↓
Itinerary Item

Optional geographic links:

Itinerary Day → start waypoint → end waypoint → overnight waypoint →
route

Optional service links:

Itinerary Item → service

Optional media:

Package → gallery

Itinerary Day → day media

This is intentionally conceptual.

No migration should be created from this section alone.

------------------------------------------------------------------------

# 17. Provider Package Page

Target page structure:

## Hero

Image + title + key stats

## Quick facts

Duration \| Difficulty \| Region \| Group size \| Altitude

## Overview

Rich provider description

## Itinerary

Timeline / accordion

## Map

2D + optional 3D

## Inclusions

Included services

## Exclusions

Not included

## Availability

Departure dates / availability where supported

## Reviews

Traveler reviews where supported

## Booking / Inquiry

Primary CTA

## Related packages

Relevant provider/package discovery

------------------------------------------------------------------------

# 18. Booking / Inquiry Panel

Desktop:

-   itinerary left
-   booking/inquiry card right

Mobile:

-   itinerary first
-   booking CTA/card placed naturally below or as a sticky bottom CTA
    where appropriate

The booking panel should never obstruct the itinerary.

Potential CTA:

`Book This Trip`

Secondary:

`Ask a Question`

Future:

`Customize This Trip`

------------------------------------------------------------------------

# 19. Mobile-First Requirements

The desktop presentation can resemble the reference structure, but
TravelAI must not simply shrink desktop UI.

Mobile requirements:

-   no horizontal overflow
-   accordion full width
-   comfortable tap targets
-   readable day titles
-   metadata wraps naturally
-   map remains usable
-   booking CTA remains easy to access
-   long descriptions do not create awkward nested scrolling
-   sticky controls only where they genuinely help

------------------------------------------------------------------------

# 20. Provider Authoring UX

The provider dashboard should eventually contain:

### Package Builder

Step 1 --- Basic Info Step 2 --- Images Step 3 --- Itinerary Step 4 ---
Route/Map Step 5 --- Inclusions Step 6 --- Availability Step 7 ---
Preview Step 8 --- Publish

Do not build all steps at once.

------------------------------------------------------------------------

# 21. Itinerary Builder UX

Recommended editor:

### Day card

`Day 01`

`Title`

`Description`

`Overnight location`

`Activities`

`Meals`

`Accommodation`

`Distance`

`Duration`

`Elevation`

Actions:

-   Move up
-   Move down
-   Duplicate
-   Delete
-   Expand/collapse

Global:

-   Add Day
-   Reorder
-   Preview
-   Save Draft
-   Publish

------------------------------------------------------------------------

# 22. Draft / Preview / Publish

Provider packages should eventually have:

-   Draft
-   Preview
-   Published
-   Unpublished/Archived

A provider should be able to preview the exact traveler-facing itinerary
before publishing.

This is important because the presentation is a major part of the
product.

------------------------------------------------------------------------

# 23. Versioning --- Future

Do not implement immediately.

Future provider itinerary editing should consider:

-   version number
-   published version
-   draft version
-   last edited timestamp
-   change history

Reason:

Changing an itinerary after bookings exist can create historical
inconsistency.

This needs a separate booking/data-retention design.

------------------------------------------------------------------------

# 24. What TravelAI Should Borrow From Himalayan Holidays

Borrow the UX principles:

### 1. Progressive disclosure

Summary first → details later.

### 2. Day-by-day structure

Travelers understand a journey faster when it is broken into days.

### 3. Expand/collapse

Prevents long itineraries from becoming overwhelming.

### 4. Quick facts

Duration, difficulty, location, group size etc. should be immediately
visible.

### 5. Booking context beside itinerary

The traveler does not have to leave the page to act.

### 6. Related packages

Useful discovery after reading a package.

### 7. Map + itinerary relationship

The journey should be spatially understandable.

------------------------------------------------------------------------

# 25. What TravelAI Should Improve

TravelAI can go beyond a conventional manual itinerary page.

## A. Geographic intelligence

Provider selects a waypoint rather than typing only plain text.

## B. Map synchronization

Day ↔ map interaction.

## C. 2D + 3D

Use existing Leaflet + Globe.gl infrastructure.

## D. Structured service data

Accommodation, activities and other services can connect to existing
service records where appropriate.

## E. Future AI assistance

Provider may eventually click:

`Improve this itinerary`

or:

`Create draft from route`

But AI must produce a **draft for provider review**, not silently
overwrite provider-authored content.

## F. Data validation

Warn provider about:

-   missing overnight location
-   invalid waypoint
-   impossible day numbering
-   empty title
-   duplicate days
-   inconsistent duration
-   route mismatch

Warnings should be explicit and non-destructive.

------------------------------------------------------------------------

# 26. AI-Assisted Authoring --- Future Phase

This is intentionally later.

Possible workflow:

Provider enters:

-   route
-   duration
-   destination
-   key stops

AI suggests:

-   day titles
-   descriptions
-   activities
-   packing notes
-   highlights

Provider reviews and edits.

Then:

`Save as Draft`

Never:

`AI automatically publishes`

unless a separate product decision explicitly authorizes it.

------------------------------------------------------------------------

# 27. Geographic Integrity Rules

The system must never invent geographic data.

Rules:

1.  Use existing waypoint coordinates when available.
2.  Use existing location relationships when reliable.
3.  Use route_segments for route geometry.
4.  Do not manufacture coordinates from place names.
5.  Do not jitter coordinates to make markers visible.
6.  Do not silently map ambiguous district/state values.
7.  Missing geographic data must be represented as missing.
8.  Provider text can remain valid even when map data is unavailable.

------------------------------------------------------------------------

# 28. Existing Globe System Compatibility

The Provider Itinerary System must preserve:

-   GLOBE-01 map initialization
-   GLOBE-02 district boundaries
-   GLOBE-03 city waypoint layer
-   GLOBE-04 district interaction
-   GLOBE-05 altitude behavior
-   GLOBE-06 single route visualization
-   GLOBE-07 journey read-model

The itinerary work must not casually refactor Globe code.

Any Globe integration change must be treated as a separate scoped phase.

------------------------------------------------------------------------

# 29. Protected Systems

Unless a future phase explicitly authorizes it, do not modify:

-   AI Planner core
-   PlannerService
-   ItineraryGenerator
-   ItineraryValidator
-   AiReservationService
-   AI quota/subscription logic
-   Safety system
-   billing/subscription
-   booking security
-   JourneyReplay system
-   existing Globe API contracts
-   district TopoJSON
-   GLOBE-01 init contract
-   GLOBE-06 route geometry contract

------------------------------------------------------------------------

# 30. Recommended Implementation Phases

## PHASE I --- Discovery / Audit

Status: NEXT

Inspect only.

Audit:

-   Provider model
-   Package/Tour model
-   existing service ownership
-   existing itinerary models
-   current provider dashboard
-   media/gallery system
-   booking relation
-   availability/departure system
-   review system
-   location/waypoint relationships
-   route relationships
-   authorization/policies
-   existing public package pages

Output:

`Provider Itinerary Discovery Report`

No code changes.

------------------------------------------------------------------------

## PHASE II --- UX Specification

Create exact traveler-facing specification:

-   package header
-   quick facts
-   tabs
-   itinerary timeline
-   day accordion
-   Expand All
-   booking panel
-   map section
-   inclusion/exclusion
-   availability
-   reviews
-   related packages
-   mobile behavior

Output:

`Provider Itinerary UX Specification`

No implementation until approved.

------------------------------------------------------------------------

## PHASE III --- Data Architecture Audit

Determine whether current tables can support the desired provider
workflow.

Questions:

-   Can existing itinerary_days be reused?
-   Are itinerary_days currently tied only to planner results?
-   Can provider packages own itinerary days?
-   What ownership key is required?
-   Can existing waypoints be referenced safely?
-   Can services be referenced safely?
-   How should drafts work?
-   How should published state work?

Output:

`Provider Itinerary Data Architecture Report`

No migration until approved.

------------------------------------------------------------------------

## PHASE IV --- Minimal Provider Itinerary Backend

Only after Phase III approval.

Implement the smallest safe provider authoring foundation.

Potential scope:

-   package itinerary ownership
-   day CRUD
-   authorization
-   draft/save
-   ordering
-   validation

No AI.

No Globe redesign.

No payment changes.

------------------------------------------------------------------------

## PHASE V --- Traveler Itinerary Renderer

Implement:

-   package detail page
-   quick facts
-   itinerary timeline
-   accordion
-   Expand All
-   mobile layout
-   booking/inquiry panel

This is where the Himalayan Holidays-inspired UX becomes visible.

------------------------------------------------------------------------

## PHASE VI --- Geographic Integration

Connect provider itinerary to:

-   waypoint
-   route
-   Leaflet
-   Globe.gl

Rules from GLOBE-06 remain authoritative.

------------------------------------------------------------------------

## PHASE VII --- Availability / Booking Context

Only after package + itinerary rendering is stable.

Connect:

-   departure dates
-   availability
-   booking
-   inquiry

Do not mix this phase with AI.

------------------------------------------------------------------------

## PHASE VIII --- Reviews / Related Packages

Add:

-   reviews
-   related packages
-   region/category discovery

Keep recommendation logic separate from itinerary rendering.

------------------------------------------------------------------------

## PHASE IX --- Provider Preview / Publish

Add:

-   preview
-   publish
-   unpublish
-   validation warnings

------------------------------------------------------------------------

## PHASE X --- AI-Assisted Provider Authoring

Only after the manual provider workflow is proven stable.

Possible:

-   AI draft
-   AI rewrite
-   AI day expansion
-   AI consistency check
-   AI route summary

Provider remains the final editor.

------------------------------------------------------------------------

# 31. Suggested Build Order Inside the Itinerary

Do NOT start with the biggest feature.

Recommended order:

1.  Provider package discovery audit
2.  Existing itinerary data audit
3.  Basic day structure
4.  Traveler itinerary renderer
5.  Expand/collapse UX
6.  Mobile UX
7.  Provider day editor
8.  Draft/save
9.  Preview
10. Map integration
11. Booking/inquiry
12. Availability
13. Reviews
14. Related packages
15. AI assistance

This order gives visible value early while reducing architectural risk.

------------------------------------------------------------------------

# 32. Audit Gates

Every phase follows:

### STEP A --- Inspect

No modifications.

### STEP B --- Report

Provide:

-   files
-   models
-   routes
-   DB tables
-   relationships
-   risks
-   exact current behavior

### STEP C --- Master Review

No implementation yet.

### STEP D --- Scope Lock

Explicitly define:

-   allowed files
-   protected files
-   allowed DB changes
-   expected behavior
-   acceptance tests

### STEP E --- Implement

Only locked scope.

### STEP F --- Verify

Test:

-   happy path
-   empty state
-   invalid input
-   authorization
-   mobile
-   regression
-   database integrity

### STEP G --- Commit Gate

Review diff.

### STEP H --- Commit

Commit only authorized files.

### STEP I --- Push Gate

Push only after separate approval.

------------------------------------------------------------------------

# 33. Testing Strategy

## Provider tests

-   provider can create itinerary
-   provider can edit own itinerary
-   provider cannot edit another provider's itinerary
-   unauthorized user cannot access editor
-   invalid day rejected
-   empty required fields rejected
-   ordering preserved
-   duplicate day numbers prevented

## Traveler tests

-   package loads
-   itinerary loads
-   all days render
-   Expand All works
-   Collapse All works
-   individual day works
-   missing optional data does not break UI
-   mobile no horizontal overflow

## Geographic tests

-   valid waypoint maps
-   missing waypoint remains unmapped
-   route uses authoritative segments
-   invalid/deleted route handled safely
-   2D and 3D use same journey data

## Regression tests

-   AI Planner
-   bookings
-   billing
-   safety
-   Globe-01..07
-   public services
-   authentication

------------------------------------------------------------------------

# 34. Performance Principles

Provider itineraries can become long.

Rules:

-   render collapsed days by default for long trips
-   avoid loading huge image sets immediately
-   lazy-load itinerary media
-   avoid unnecessary map re-renders
-   do not fetch the same journey data repeatedly
-   use existing cache patterns where appropriate
-   avoid N+1 queries
-   paginate large provider package lists
-   keep mobile DOM manageable

------------------------------------------------------------------------

# 35. SEO Principles

Future provider package pages should support:

-   unique title
-   meta description
-   canonical URL
-   structured headings
-   readable day content
-   package schema where valid
-   image alt text
-   indexable itinerary content

Do not generate duplicate pages for every filter combination.

------------------------------------------------------------------------

# 36. Accessibility Principles

Required:

-   keyboard-accessible accordions
-   visible focus state
-   semantic headings
-   button labels
-   ARIA expanded state
-   accessible map fallback
-   sufficient contrast
-   no information conveyed only by color

Example:

Do not rely only on:

`green = included`

Use:

`Included`

as text too.

------------------------------------------------------------------------

# 37. Data Quality Principles

Provider-entered data should remain provider-owned.

TravelAI-derived data must be distinguishable.

Example:

Provider text: `Beautiful sunrise walk to Tengboche.`

System-derived: `Elevation: 3,860 m`

Never merge them invisibly.

------------------------------------------------------------------------

# 38. Manual vs Automated Data Labels

Use explicit source types internally/conceptually:

-   `provider`
-   `system`
-   `planner`
-   `ai_assisted`

Do not expose technical labels unnecessarily to travelers.

But internally the distinction is critical for auditing and trust.

------------------------------------------------------------------------

# 39. Future Journey View Model

Eventually the public renderer should consume a normalized structure
similar to:

Journey - source - title - summary - duration - destination -
difficulty - stats - days\[\] - route - waypoints\[\] - inclusions\[\] -
exclusions\[\] - availability\[\] - reviews\[\] - provider - booking -
media

The renderer should not care whether the source is:

-   provider package
-   planner read-model
-   future AI-generated journey

This is the long-term architectural goal.

------------------------------------------------------------------------

# 40. What We Must NOT Do

Do not:

-   clone Himalayan Holidays code
-   assume their backend architecture
-   create a `journeys` table immediately
-   modify AI Planner just to support the UI
-   call current fallback data "AI-generated"
-   invent coordinates
-   automatically publish AI output
-   redesign the existing Globe system
-   replace existing route geometry contracts
-   add payment work to this project phase
-   build all provider features in one giant change
-   modify desktop behavior when a mobile-only fix is requested
-   mix unrelated refactors into itinerary work

------------------------------------------------------------------------

# 41. First Actual Task

The next task is **NOT implementation**.

It is:

## PROVIDER-ITINERARY-01 --- Discovery Audit

Deep inspection should cover:

### Code

-   provider models
-   package/tour models
-   itinerary models
-   controllers
-   policies
-   routes
-   public package pages
-   provider dashboard pages
-   booking controllers
-   availability logic
-   review logic
-   media handling

### Database

Inspect:

-   table names
-   columns
-   foreign keys
-   ownership fields
-   indexes
-   nullable fields
-   soft deletes
-   existing itinerary data
-   existing package data

### Relationships

Map:

`Provider → Package → Itinerary → Day → Item → Location → Waypoint → Route → Service`

Do not assume every relationship exists.

### Output

DeepSeek must report:

1.  Exact current architecture
2.  Exact reusable components
3.  Existing tables/models
4.  Existing public UI
5.  Existing provider UI
6.  What can be reused
7.  What is missing
8.  Risks
9.  Minimal proposed architecture
10. Files that would need modification
11. Files that must remain untouched

**No code changes.**

------------------------------------------------------------------------

# 42. Master Rule for the Entire Project

The guiding principle is:

> **First make manual provider itineraries excellent. Then connect
> geography. Then connect booking/availability. Then add AI
> assistance.**

AI should enhance the provider's work, not replace the provider's
ownership of the itinerary.

------------------------------------------------------------------------

# 43. Current Status Ledger

  Area                              Status
  --------------------------------- ----------------
  Himalayan Holidays UX study       COMPLETE
  Manual itinerary concept          APPROVED
  Provider itinerary architecture   MASTER PLANNED
  Provider data architecture        AUDIT REQUIRED
  Provider editor                   NOT STARTED
  Traveler itinerary renderer       NOT STARTED
  Map integration                   NOT STARTED
  Booking integration               NOT STARTED
  Availability integration          NOT STARTED
  Reviews integration               NOT STARTED
  AI-assisted authoring             FUTURE
  Journey DB redesign               NOT AUTHORIZED
  AI Planner modification           NOT AUTHORIZED

------------------------------------------------------------------------

# 44. Final Master Direction

TravelAI Nepal should evolve from:

`AI Planner + Map`

into:

`Travel Intelligence Platform`

with three complementary journey sources:

### 1. Provider-authored journeys

Human expertise and commercially published packages.

### 2. Planner journeys

Traveler-specific planning/read-model.

### 3. Future AI-assisted journeys

AI helps create or adapt drafts under human/provider control.

All three can eventually share the same traveler-facing Journey
presentation:

**Overview → Day-by-Day Itinerary → Map → Logistics → Booking →
Reviews**

This gives TravelAI the familiar usability of a mature trekking operator
website while preserving its larger geographic data layer and future AI
capabilities.

------------------------------------------------------------------------

## MASTER STATUS

**Version:** 1.0\
**Decision:** APPROVED AS PLANNING BASELINE\
**Implementation:** NOT YET AUTHORIZED\
**Next phase:** PROVIDER-ITINERARY-01 Discovery Audit\
**Rule:** Inspect → Report → Master Review → Scope Lock → Implement →
Verify → Commit Gate → Push Gate
---

## 📌 FIX-AUDIT-01-CURRENCY — CLOSED + PUSHED (2026-09-22)

**Commit:** `d37890f` — `fix(booking): use CurrencyService for total price display`
**Full hash:** `d37890f22d7498eb389feebd4caaced4ccde7980`
**Push range:** `a6e1259` → `d37890f`
**Remote:** `origin/main` (DigiSewaAI/TravelAI-Nepal)
**Sync:** Local == Remote ✅ (0/0)

### Files Changed (1)
- `resources/views/public/booking/create.blade.php` (+8/-1)

### What Shipped
- Hardcoded `Rs. {{ number_format($service->price, 0) }}` → `CurrencyService` dynamic conversion
- Session currency respected (USD / NPR)
- Pattern matches `show.blade.php` lines 98-102

### Verification
- NPR switch: `Rs. 114,450` ✅ (750 × 152.60)
- USD switch: `$750` ✅
- Full suite: 41p / 1f (pre-existing Safety — unchanged)
- Zero regression

### Root Cause (from AUDIT-01)
- AUDIT-01 discovered hardcoded "Rs." in booking summary
- CurrencyService was bypassed
- Confirmation view verified CLEAN (1-file fix scope)

### Final State

---

## 📌 AUDIT-01 (PUBLIC PAGE COMPLETENESS) — CLOSED (2026-09-22)

**Status:** Complete (read-only)
**Mode:** 6 batches (B1-B6)
**Findings:** 21 classified

### Summary
- 🔴 Confirmed bugs: 1 (currency — now fixed)
- ⚠️ Not testable: 2 (itinerary/departures — no real data)
- ✅ False alarms: 3 (locale mojibake, booking form fields)
- 🟡 Known/deferred: 3 (CJK labels, three.js, CTA layout)
- ⚪ Console errors: 6 (all known/deferred)
- ✅ Pass/clean: 12+

### Deployment Blockers
**NONE identified.**

### Final State

---

## 📌 PROVIDER-EDITOR-UX-01 — DISCOVERY COMPLETE (2026-09-22)

**Status:** Report submitted. Master Scope Lock issued.
**Mode:** READ-ONLY audit

### Root Cause Identified
Owner ने ABC service मा EBC days rakhe — **Editor UX confusion** (not AI bug).

6 compounding issues:
1. No accordion (all days expanded)
2. No sticky day header
3. Save = full reload, scroll lost
4. No visual differentiation
5. No sequential flow (§21)
6. AI draft appends silently

### Files Inspected (5)
- `index.blade.php` (306 lines)
- `_day_card.blade.php` (207 lines)
- `ItineraryDayController.php` (282 lines)
- `ItineraryItemController.php` (167 lines)
- `AiItineraryDraftController.php` (~350 lines)

### Final State

---

## 📌 PROVIDER-EDITOR-UX-01-MIN — PLAN APPROVED + IMPLEMENTATION GO (2026-09-22)

**Status:** Plan approved. Implementation GO issued.
**Scope:** Minimum Fix (~7 hrs)

### 5 Fixes Authorized
1. Accordion collapse (`<details>`)
2. Sticky "Day N" header
3. Save & Next Day button
4. Anchor on save redirect (`->to()` — not `withFragment()`)
5. Unsaved changes warning (`beforeunload`)

### Files (Authorized)
1. `resources/views/provider/services/itinerary/_day_card.blade.php`
2. `resources/views/provider/services/itinerary/index.blade.php`
3. `app/Http/Controllers/Provider/ItineraryDayController.php`
4-7. `resources/lang/{en,np,hi,zh}/messages.php` (+3 keys each)

### Constraints
- No protected system touch
- No migration, no new route, no model change
- Vanilla JS मात्र (no Alpine.js)
- Mobile-friendly (375px)
- Free-first (R21-R24)

### Test Plan
T1-T10 (Master defined)

### Status
- Plan: ✅ APPROVED
- Implementation: 🟢 GO (issued)
- Commit: 🔒 HOLD
- Push: 🔒 HOLD

### Final State

---

## 📌 PRIORITY QUEUE (Current — 2026-09-22)

| Priority | Task | Status |
|---|---|---|
| 1 | Currency fix | ✅ CLOSED + PUSHED |
| 2 | **Provider Editor UX (MIN)** | 🟢 **IMPLEMENTATION GO** |
| 3 | X-02 F1 (AI geographic) | 🔒 After editor |
| 4 | Public Page Completeness | 🔒 After X-02 |
| 5 | Provider Editor Full Fix (§21) | 🔒 Future |
| 6 | Package Builder 8-step | 🔒 Future |
| 7 | 3D Globe itinerary | 🔒 Future |

### Open Tickets
- `X-02-F1-PROMPT-MODEL` (MEDIUM)
- `X-04-MAPLIBRE-MIGRATION` (LOW)
- `PROVIDER-ITINERARY-I18N-01` (MEDIUM)
- `PROVIDER-ROUTES-HYGIENE-01` (MEDIUM)
- `MEDIA-THUMBNAIL-01` (MEDIUM)
- Safety Phase1Test (pre-existing)

---