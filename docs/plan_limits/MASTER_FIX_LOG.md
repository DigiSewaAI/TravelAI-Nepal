# TravelAI Nepal — MASTER FIX LOG
## Specification v1.5 + Implementation Status (FIX-01 → FIX-03)

**Date:** 2026-09-15
**Status:** FIX-01 ✅ | FIX-02 ✅ | FIX-03 ✅ (committed) | FIX-04+ ⏸️ pending
**Reference Spec:** v1.5 (full version available in `docs/plan_limits/FIX_SPECIFICATION.md`)

═══════════════════════════════════════════════════════
📊 PROGRESS TRACKER
═══════════════════════════════════════════════════════

| FIX | Title | Status | Commit |
|-----|-------|--------|--------|
| FIX-01 | Enterprise requires_contact + invalid-plan rejection | ✅ APPROVED | committed |
| FIX-02 | Auth middleware on provider routes | ✅ APPROVED | committed |
| FIX-03 | Booking path repair + IDOR + quota infrastructure | ✅ APPROVED | committed |
| FIX-04 | Subscription::isActive() expiry correction | ⏸️ pending | — |
| FIX-05 | Feature gating | ⏸️ pending | — |
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

STATUS: ✅ APPROVED & COMMITTED

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
  Free/Pro/Business unchanged
  plan=enterprise → 302 /contact-sales, 0 subs created
  plan=invalid → 422 validation
  CSRF active
  Data hygiene: 726/39/1169/4 preserved

═══════════════════════════════════════════════════════
FIX-02 — Auth Middleware on Provider Routes
═══════════════════════════════════════════════════════

STATUS: ✅ APPROVED & COMMITTED

## Files Changed (1)
  routes/web.php — line 167

## Change
  BEFORE: Route::prefix('provider')->name('provider.')->group(...)
  AFTER:  Route::middleware(['auth'])->prefix('provider')->name('provider.')->group(...)

## Verification (all PASS)
  Guest /provider/dashboard → 302 /login
  Guest /provider/analytics → 302 /login (was 500)
  Guest /provider/services/create → 302 /login (was 200)
  Guest /provider/staff → 302 /login (was 500)
  Guest /provider/subscriptions → 302 /login
  Guest /provider/bookings → 302 /login
  Authenticated provider → all 200 (unchanged)
  CSRF still 419 for guest POST
  Data hygiene: unchanged

═══════════════════════════════════════════════════════
FIX-03 — Booking Path Repair + IDOR + Quota Infrastructure
═══════════════════════════════════════════════════════

STATUS: ✅ APPROVED & COMMITTED

## Files Changed (4 modified + 6 new + 3 migrations)

Modified:
  app/Http/Controllers/Public/BookingController.php
  app/Http/Controllers/Provider/BookingController.php
  app/Models/Booking.php
  resources/views/public/booking/confirmation.blade.php

New:
  app/Support/QuotaPeriod.php
  app/Support/BookingStatusTransitions.php
  app/Models/BookingUsage.php
  app/Services/BookingLimitService.php

Migrations:
  database/migrations/2026_09_15_051845_add_quota_month_to_bookings.php
  database/migrations/2026_09_15_051914_create_booking_usage_table.php
  database/migrations/2026_09_15_052646_backfill_bookings_quota_month.php

## Migration
  M1: ADD bookings.quota_month VARCHAR(7) NULL AFTER status + index
  M2: Backfill from created_at → quota_month (NPT), chunked, idempotent
  M3: CREATE booking_usage (id, provider_id, month, count, timestamps) UNIQUE(provider_id, month)

## Key Changes

1. **Legacy Trekker removal**
   - Public\BookingController no longer references Trekker
   - Booking::$fillable: removed 'trekker_id', 'trek_id'; added 'quota_month'
   - confirmation.blade.php: removed $booking->trekker fallback

2. **Signed URL confirmation authorization**
   - URL::temporarySignedRoute('public.booking.confirmation', +24h)
   - Guest requires valid signature
   - Owner (traveler_id === auth id) OR provider (own service) bypass signature
   - All other → 403

3. **Status transitions**
   - app/Support/BookingStatusTransitions.php with frozen map
   - pending → [confirmed, cancelled, rejected]
   - confirmed → [completed, cancelled]
   - Terminal: completed, cancelled, rejected
   - Provider\BookingController::updateStatus() uses lockForUpdate() + transition check

4. **Quota infrastructure (no enforcement wiring)**
   - QuotaPeriod::current() / forDate() (NPT)
   - BookingLimitService::reserve(), release(), getUsage()
   - Free=10, Pro=100, Business=1000, Enterprise=-1
   - Enforcement wiring reserved for FIX-06

## Verification (all PASS)

### Runtime Booking Test
- Direct controller call → 302 signed URL
- Booking 72 created: traveler_id=114, service_id=27, quota_month=2026-09, status=pending
- No SQL exception (trekkers removed)

### Confirmation Security Tests
| Test | Result |
|------|--------|
| Guest no signature (booking 27) | 403 ✅ |
| Tampered signature | 403 ✅ |
| Valid signed URL | 200, 18958 bytes ✅ |
| **Signature reuse A→B (CRITICAL)** | **403** ✅ |
| Unrelated user (david.chen) | 403 ✅ |
| Owner (shresthaxok) | 200 ✅ |
| Provider own booking (anjuregmimesh) | 200 ✅ |
| Cross-provider (Provider 14 → booking 32) | 403 ✅ |
| Expired signed URL (guest) | 403 ✅ |

### Quota Infrastructure Tests
| Plan | Provider | Limit | Result |
|------|----------|-------|--------|
| Free | 15 | 10 | ✅ |
| Professional | 16 | 100 | ✅ |
| Enterprise | 17 | -1 | ✅ |

### Status Transitions
- pending → confirmed: OK ✅
- cancelled → pending: rejected ✅

### Data Hygiene
- BEFORE: 31 bookings, 39 users, 726 providers
- AFTER: 31 bookings, 39 users, 726 providers
- Zero test records remaining
- All 31 existing bookings have quota_month populated

═══════════════════════════════════════════════════════
SPEC REFERENCE — v1.5
═══════════════════════════════════════════════════════

Full v1.5 specification is maintained in:
  docs/plan_limits/FIX_SPECIFICATION.md

Key sections summary:

- § A0: QuotaPeriod helper (Asia/Kathmandu)
- § A1: Booking quota rules (creation-based, terminal states)
- § A3: AI quota reserve/finalize/release architecture
- § A7: Guest email conflict rules (traveler-only attach)
- § A9: Expired subscription → Free fallback
- § B1: Public booking flow + signed URL confirmation
- § B0: Feature source-of-truth (User → Provider → Subscription → Plan)
- § C1: Enterprise requires_contact
- § D1: Feature gating middleware
- § G2: Webhook signature + lease-based idempotency
- § G3: Payment Option A (Contact Sales, prices visible)
- § J2: Named rate limiters
- § L0: Recursive staff cleanup audit
- § M5: ai_usage migration ALTER strategy
- § O: FIX-01 → FIX-16 normalized IDs
- § Q: 7 migrations with rollback
- § R: Transaction boundaries
- § S: Authorization boundaries
- § T: Regression test suite
- § U: Master approval checklist

═══════════════════════════════════════════════════════
DEFERRED / OUT-OF-SCOPE NOTES
═══════════════════════════════════════════════════════

1. Legacy Trekker references remain in dead/unrouted code:
   - app/Http/Controllers/TrekBookingController.php (not routed)
   - app/Http/Controllers/Api/SosController.php (SOS scope)
   - app/Http/Controllers/Agency/* (Agency scope)
   - app/Models/SosAlert.php (SOS scope)
   - app/Jobs/SendSosNotification.php (SOS scope)
   
   These are outside FIX-03 scope and deferred.

2. Migration timestamps:
   - Actual migrations use runtime-generated timestamps
   - Names match purpose; not exact master-spec timestamps

3. Booking confirmation email: not implemented in FIX-03
   - QUEUE_CONNECTION=sync (local)
   - No BookingConfirmationMail exists
   - Future scope

4. Non-provider authorization testing: deferred
   - FIX-02 preserved controller-level authorization
   - Dedicated FIX for authorization audit TBD

═══════════════════════════════════════════════════════
VERDICTS
═══════════════════════════════════════════════════════

FIX-01: PASS (approved)
FIX-02: PASS (approved)
FIX-03: PASS (approved)

═══════════════════════════════════════════════════════
CHANGELOG
═══════════════════════════════════════════════════════

2026-09-15: FIX-01 implemented, tested, approved, committed
2026-09-15: FIX-02 implemented, tested, approved, committed
2026-09-15: FIX-03 implemented, tested, approved, committed
2026-09-15: Consolidated MASTER_FIX_LOG.md created