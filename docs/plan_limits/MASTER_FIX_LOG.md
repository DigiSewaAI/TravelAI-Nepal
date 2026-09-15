# TravelAI Nepal — MASTER FIX LOG
## Specification v1.5 + Implementation Status (FIX-01 → FIX-05)

**Date:** 2026-09-15
**Status:** FIX-01 ✅ | FIX-02 ✅ | FIX-03 ✅ | FIX-04 ✅ (committed) | FIX-05 🟡 (Phase 2 approved, Phase 3 in progress) | FIX-06+ ⏸️ pending
**Reference Spec:** v1.5 (full version available in `docs/plan_limits/FIX_SPECIFICATION.md`)

═══════════════════════════════════════════════════════
📊 PROGRESS TRACKER
═══════════════════════════════════════════════════════

| FIX | Title | Status | Commit |
|-----|-------|--------|--------|
| FIX-01 | Enterprise requires_contact + invalid-plan rejection | ✅ APPROVED | 0622334 |
| FIX-02 | Auth middleware on provider routes | ✅ APPROVED | f07437e |
| FIX-03 | Booking path repair + IDOR + quota infrastructure | ✅ APPROVED | 580c2a6 |
| FIX-04 | Subscription::isActive() exact datetime expiry | ✅ APPROVED | 65fc2d4 |
| FIX-05 Phase 1 | Pre-implementation audit | ✅ COMPLETE | — |
| FIX-05 Phase 2 | Plan feature normalization (Option A) | ✅ APPROVED | pending commit |
| FIX-05 Phase 3 | Centralized feature gating | 🟡 IN PROGRESS | — |
| FIX-06 | Booking quota enforcement | ⏸️ pending | — |
| FIX-07 | Subscription expiry automation | ⏸️ pending | — |
| FIX-08 | Stripe webhook signature + idempotency | ⏸️ pending | — |
| FIX-09 | Remove API key prefix logging | ⏸️ pending | — |
| FIX-10 | APP_DEBUG production hardening | ⏸️ pending | — |
| FIX-11 | Configurable rate limiting | ⏸️ pending | — |
| FIX-12 | AI reservation crash-safe infrastructure | ⏸️ pending | — |
| FIX-13 | AI quota timing (reserve pattern) | ⏸️ pending | — |
| FIX-14 | Staff dual-system cleanup | ⏸️ pending | — |
| FIX-15 | Service policy documentation | ⏸️ pending | — |
| FIX-16 | PII-free booking error logging | ⏸️ pending | — |
| FIX-17 | (see FIX-12) | — | — |
| FIX-18 | Payment FAQ correction | ⏸️ pending | — |

═══════════════════════════════════════════════════════
FIX-01 — Enterprise requires_contact + invalid-plan rejection
═══════════════════════════════════════════════════════

STATUS: ✅ APPROVED & COMMITTED (0622334)

## Files Changed (10 modified + 3 new + 1 migration)
Modified:
  app/Models/Plan.php
  app/Models/Subscription.php
  app/Http/Controllers/Auth/RegisterController.php
  app/Http/Controllers/Provider/SubscriptionController.php
  routes/web.php
  resources/lang/{en,np,hi,zh}/messages.php (6 keys each)
  resources/views/provider/subscriptions/index.blade.php

New:
  database/migrations/2026_09_15_040051_add_requires_contact_to_plans.php
  app/Http/Controllers/PublicPage/ContactSalesController.php
  resources/views/public/contact-sales.blade.php

## Migration
  ALTER TABLE plans ADD requires_contact BOOLEAN DEFAULT 0 AFTER features
  UPDATE plans SET requires_contact = 1 WHERE slug = 'enterprise'
  Rollback: DROP COLUMN requires_contact

## Verification (all PASS)
  Enterprise isContactOnly() = true
  Enterprise isFree() = false
  plan=enterprise → 302 /contact-sales
  plan=invalid → 422
  Data hygiene: 726/39/1169/4 preserved

═══════════════════════════════════════════════════════
FIX-02 — Auth Middleware on Provider Routes
═══════════════════════════════════════════════════════

STATUS: ✅ APPROVED & COMMITTED (f07437e)

## Files Changed (1)
  routes/web.php — line 167

## Change
  Route::middleware(['auth'])->prefix('provider')->name('provider.')->group(...)

## Verification (all PASS)
  Guest provider routes → 302 /login (all 6 tested)
  Authenticated provider → 200 unchanged

═══════════════════════════════════════════════════════
FIX-03 — Booking Path Repair + IDOR + Quota Infrastructure
═══════════════════════════════════════════════════════

STATUS: ✅ APPROVED & COMMITTED (580c2a6)

## Files Changed (4 modified + 6 new + 3 migrations)
Modified: Public/BookingController, Provider/BookingController, Booking model, confirmation.blade
New: QuotaPeriod, BookingStatusTransitions, BookingUsage, BookingLimitService
Migrations: add_quota_month_to_bookings, create_booking_usage_table, backfill_bookings_quota_month

## Key Changes
- Removed legacy Trekker references
- Signed URL confirmation authorization (+24h)
- Status transition map (terminal states)
- BookingLimitService (no enforcement wiring — FIX-06)

## Verification (all PASS)
- Public booking → 302 signed URL, no SQL error
- 9/9 confirmation security tests PASS (incl. signature reuse A→B = 403)
- Quota service: Free=10, Pro=100, Enterprise=-1
- Data hygiene: 31/39/726 preserved

═══════════════════════════════════════════════════════
FIX-04 — Subscription::isActive() Exact Datetime Expiry
═══════════════════════════════════════════════════════

STATUS: ✅ APPROVED & COMMITTED (65fc2d4)

## Files Changed (1 modified + 1 new)
Modified: app/Models/Subscription.php ($casts, isActive logic)
New: migration 2026_09_15_061433_change_subscriptions_end_date_to_datetime.php

## Migration (Master-approved OPTION A)
  subscriptions.end_date: DATE NULL → DATETIME NULL
  Existing 4 rows → midnight datetimes

## Logic Change
  IF status != active → false
  IF end_date == null → true
  ELSE return end_date->isFuture()

## Verification (7/7 PASS)
  T1 (future) TRUE, T2 (later today) TRUE, T3 (earlier today) FALSE
  T4 (yesterday) FALSE, T5 (exact boundary) FALSE
  T6 (non-active) FALSE, T7 (null) TRUE

═══════════════════════════════════════════════════════
FIX-05 — Feature Gating (3-PHASE APPROACH)
═══════════════════════════════════════════════════════

STATUS: 🟡 IN PROGRESS — Phase 2 approved, Phase 3 implementing

## Phase 1 — Pre-Implementation Audit
STATUS: ✅ COMPLETE

Findings:
- plans.features DB values were human-readable strings (not slugs)
- Business MISSING "Advanced Dashboard"
- Enterprise MISSING "Advanced Dashboard", "Full Analytics", "White-label"
- Marketing copy mixed into features array
- No hasFeature(), canUse(), or feature middleware existed
- No feature-related policies
- Analytics accessible to Free (200), no route gate
- Profile logo upload accessible to all plans

## Phase 2 — Plan Feature Normalization (Option A)
STATUS: ✅ APPROVED

Migration: database/migrations/2026_09_15_063111_normalize_plan_features_to_slugs.php

Canonical matrix (frozen):
  free         → []
  professional → ["advanced_dashboard","custom_logo"]
  business     → ["advanced_dashboard","full_analytics","white_label","custom_logo"]
  enterprise   → ["advanced_dashboard","full_analytics","white_label","custom_logo","priority_support"]

Verification (all PASS):
  - All 4 plans normalized correctly
  - Zero unknown slugs, zero duplicates
  - Zero marketing strings remain
  - Counts unchanged (4/726/4/39/31)
  - Other columns unchanged (name, price, limits, requires_contact)
  - down() restores exact prior values

## Phase 3 — Centralized Feature Gating
STATUS: 🟡 IN PROGRESS — Authorized by Master

Canonical slugs (frozen):
  advanced_dashboard
  full_analytics
  white_label
  custom_logo
  priority_support

Canonical entitlement matrix (frozen):
              Free   Pro   Business   Enterprise
advanced_dashboard DENY  ALLOW  ALLOW      ALLOW
full_analytics     DENY  DENY   ALLOW      ALLOW
white_label        DENY  DENY   ALLOW      ALLOW
custom_logo        DENY  ALLOW  ALLOW      ALLOW
priority_support   DENY  DENY   DENY       ALLOW

Requirements:
- Centralized mechanism (e.g. Provider::hasFeature())
- Requires Subscription::isActive() === true (FIX-04)
- Plan inheritance forbidden
- Hardcoded plan-name checks forbidden
- Route middleware enforcement (not UI-only)
- Direct URL access must be blocked
- HTTP 403 for authenticated user lacking entitlement
- /provider/analytics → Business+ only (currently Free gets 200)

To implement:
- Provider::hasFeature(string $slug): bool
- Generic middleware `feature:{slug}`
- Route application to real protected surfaces
- UI conditional hiding where surfaces exist

Out of scope:
- Do NOT invent routes/features that don't exist
- Do NOT redesign dashboard
- Do NOT modify normalized feature data

═══════════════════════════════════════════════════════
SPEC REFERENCE — v1.5
═══════════════════════════════════════════════════════

Full v1.5 specification: docs/plan_limits/FIX_SPECIFICATION.md

Key sections:
- § A0: QuotaPeriod helper (Asia/Kathmandu)
- § A1: Booking quota rules
- § A3: AI quota reserve/finalize/release
- § A7: Guest email conflict rules
- § A9: Expired subscription → Free fallback
- § B0: Feature source-of-truth
- § B1: Public booking + signed URL confirmation
- § C1: Enterprise requires_contact
- § D1: Feature gating middleware
- § F1: Subscription isActive exact datetime
- § G2: Webhook signature + lease
- § G3: Payment Option A
- § J2: Named rate limiters
- § L0: Staff cleanup audit
- § M5: ai_usage migration ALTER
- § O: FIX-01 → FIX-18 IDs
- § Q: Migrations with rollback
- § R: Transaction boundaries
- § S: Authorization boundaries
- § T: Regression suite
- § U: Master approval checklist

═══════════════════════════════════════════════════════
DEFERRED / OUT-OF-SCOPE NOTES
═══════════════════════════════════════════════════════

1. Legacy Trekker references in dead/unrouted code
   (TrekBookingController, SosController, Agency controllers)
   — outside FIX-03 scope

2. Booking confirmation email: not implemented
   (QUEUE_CONNECTION=sync; no BookingConfirmationMail)

3. Automatic subscription expiry: FIX-07
   (isActive() correct in FIX-04; status flip scheduled for FIX-07)

4. Non-provider authorization testing: deferred to dedicated FIX

5. Marketing strings removed from plans.features — now stored only in
   `limits` (numeric) and `description` (human-readable)

═══════════════════════════════════════════════════════
VERDICTS
═══════════════════════════════════════════════════════

FIX-01:              ✅ PASS (approved + committed)
FIX-02:              ✅ PASS (approved + committed)
FIX-03:              ✅ PASS (approved + committed)
FIX-04:              ✅ PASS (approved + committed)
FIX-05 Phase 1:      ✅ COMPLETE
FIX-05 Phase 2:      ✅ PASS (approved, awaiting commit as part of FIX-05)
FIX-05 Phase 3:      🟡 IN PROGRESS

═══════════════════════════════════════════════════════
CHANGELOG
═══════════════════════════════════════════════════════

2026-09-15: FIX-01 implemented, approved, committed (0622334)
2026-09-15: FIX-02 implemented, approved, committed (f07437e)
2026-09-15: FIX-03 implemented, approved, committed (580c2a6)
2026-09-15: FIX-04 implemented, approved, committed (65fc2d4)
2026-09-15: MASTER_FIX_LOG.md created
2026-09-15: FIX-05 Phase 1 audit complete (discrepancy found)
2026-09-15: FIX-05 Phase 2 approved (feature normalization)
2026-09-15: FIX-05 Phase 3 authorized (feature gating)
2026-09-15: MASTER_FIX_LOG.md updated with FIX-05 phases