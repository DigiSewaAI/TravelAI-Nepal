FIX-07 PHASE 1 — AUDIT REPORT
Subscription Expiry Automation
Date: 2026-09-15 | Status: AUDIT COMPLETE — Awaiting Master Implementation Authorization
Scope: AUDIT ONLY — no code/DB changes made

═══════════════════════════════════════════════════════
A. EXECUTIVE SUMMARY
═══════════════════════════════════════════════════════

Current state:
- Subscription::isActive() correctly implements FIX-04 exact
  datetime expiry semantics.
- NO automation exists to flip expired active subscriptions to
  an 'expired' status in the database.
- NO subscription expiry scheduler, job, or command exists.
- NO subscription-related email/notification infrastructure exists.
- Only 4 subscriptions in DB, all currently active with future
  end_date (2026-10-02).
- 'expired' status exists as a helper method but is never set
  anywhere in the codebase.

Lifecycle gap (confirmed):
  Database can show: status=active, end_date=past
  Runtime evaluates: isActive()=false ✅
  BUT: DB status remains 'active' forever (FIX-07 gap).

═══════════════════════════════════════════════════════
B. CURRENT SUBSCRIPTION LIFECYCLE
═══════════════════════════════════════════════════════

MODEL: app/Models/Subscription.php
CASTS: start_date=date, end_date=datetime
HELPERS:
  - isActive()  — active + (NULL OR end_date>now)
  - isPending() — status === 'pending'
  - isCancelled() — status === 'cancelled'
  - isExpired() — status === 'expired'
  - isFree(), isMonthly(), isYearly()

STATUS VALUES ACTUALLY SET IN CODE:
  'active'    — RegisterController (free), SubscriptionController (local store/upgrade/resume, free prod), PaymentService (after webhook confirms)
  'pending'   — RegisterController (paid prod), SubscriptionController (paid prod store/upgrade/resume)
  'cancelled' — SubscriptionController::upgrade (line 162), ::cancel (line 232)

STATUS SET IN DATA BUT NEVER IN CODE:
  'expired'   — helper exists, never used

STATUS CREATION/MUTATION PATHS:

1. Registration (Auth\RegisterController:139, 143)
   - Free plan → status='active', end_date=+1year
   - Paid plan (prod) → status='pending'

2. Provider subscription page (Provider\SubscriptionController)
   - store()  : local → active; prod → pending
   - upgrade(): cancels current + new sub (local active, prod pending)
   - cancel() : sets status='cancelled', end_date=now
   - resume() : local → new active; prod → new pending

3. Payment callback (Services\PaymentService:126)
   - After Stripe webhook success → status='active'
   - Sets end_date based on billing_interval (monthly/yearly)

4. Admin (Admin\SubscriptionController:32)
   - Direct `$subscription->status = $request->status` + save
   - No validation of transition
   - Admin CAN set 'expired' manually (but UI probably doesn't)

ENTERPRISE / CONTACT-ONLY:
  - FIX-01: Enterprise registration → redirect to contact-sales
  - Enterprise subscription created only via admin/manual
  - Current Enterprise sub (ID 4) has end_date=2026-10-02 (finite)
  - Enterprise has max_*=-1 in limits (unlimited)
  - No special expiry handling

FREE FALLBACK:
  - No provider currently falls back to Free
  - BookingLimitService::maxFor() uses plan.limits['max_bookings'] ?? 10
  - Provider::hasFeature() returns false if no active sub
  - Provider with expired sub → hasFeature() returns false → deny paid features

═══════════════════════════════════════════════════════
C. CURRENT EXPIRY BEHAVIOR (Runtime Evidence)
═══════════════════════════════════════════════════════

RUNTIME TESTS (Tinker with Carbon::setTestNow):

| Scenario | Sub 1 end_date | Test clock | isActive() | Status in DB |
|----------|---------------|------------|------------|--------------|
| A. active + future | 2026-10-02 | now (2026-09-15) | TRUE | active |
| B. active + future boundary | 2026-10-02 | 2026-10-01 23:59:59 | TRUE | active |
| C. active + exact boundary | 2026-10-02 | 2026-10-02 00:00:00 | FALSE | **active** |
| D. active + past | 2026-10-02 | (would need future date) | FALSE | active |
| E. cancelled + future | N/A (no cancelled subs) | N/A | N/A | N/A |
| F. active + NULL | N/A (no NULL subs) | N/A | TRUE (by code) | active |

⚠ CRITICAL EVIDENCE:
  Scenario C: at 2026-10-02 00:00:00, isActive() returns FALSE,
  but DB still has status='active'.
  → This is EXACTLY the gap FIX-07 must close.
  → No automatic mechanism exists to flip status.

RUNTIME EVIDENCE:
  Sub 1 @ 2026-10-02 00:00:00 → isActive=false
  Sub 1 @ 2026-10-01 23:59:59 → isActive=true
  Both confirmed via Tinker + Carbon::setTestNow

═══════════════════════════════════════════════════════
D. EXISTING SCHEDULER / AUTOMATION
═══════════════════════════════════════════════════════

routes/console.php CONTAINS:
  - inspire (placeholder)
  - FetchSafetySourcesJob → everyFiveMinutes
  - VerifyExpiredIncidentsJob → daily
  - UpdateSafetyStatusesJob → everyFifteenMinutes

NO subscription-related schedule.

app/Console/Commands/ — 11 files:
  DiagnoseRouteSegments, MigrateAgenciesToProviders,
  MigrateBookingsToNewSchema, MigrateTreksToServices,
  PassportBackfill, PassportRegenerateQrTokens,
  PassportVerifyScans, PlannerAudit, SemanticAudit,
  TestAllRoutes, TestPlannerMatrix
  → NO subscription expiry command.

app/Jobs/ — 8 files (2 root + 6 Safety):
  ProcessMediaUpload, SendSosNotification,
  Safety/{FetchSafetySourcesJob, ProcessIncidentDetectionJob,
          SendSafetyAlertsJob, UpdateSafetyStatusesJob,
          VerifyExpiredIncidentsJob}
  → NO subscription expiry job.

findstr for Schedule::|ExpireSubscription|SubscriptionExpiry
  → Only Safety matches. NO subscription automation.

bootstrap/app.php:
  - Registers middleware aliases (admin, localize, feature)
  - Appends Localization to web group
  - NO scheduler configuration
  - Commands loaded from routes/console.php

SCHEDULER RUNNING?:
  - findstr on README/composer/.env → no schedule:run/schedule:work refs
  - No local evidence scheduler is invoked
  - Scheduler would need Windows Task Scheduler or similar to run
  - NOT verified as running.

VERDICT: ❌ ZERO subscription-expiry automation exists.

═══════════════════════════════════════════════════════
E. CALL-SITE AUDIT (isActive() consumers)
═══════════════════════════════════════════════════════

ONLY CALLER (app/*.php):
  app\Models\Provider.php:108
    public function hasFeature(string $feature): bool
    {
        $subscription = $this->activeSubscription()->first();
        if (!$subscription || !$subscription->isActive()) {
            return false;
        }
        ...
    }

DEFINITION ONLY:
  app\Models\Subscription.php:54

BLADE VIEWS:
  resources/views/provider/subscriptions/index.blade.php:
    - Line 32-37: displays status badge (active/cancelled/other)
    - Line 41: cancel button shown only if status='active'
    - Line 52: "no active subscription" message
  → No isActive() calls in blades (uses DB status directly).

NO EXISTING CODE treats an expired-active subscription as Free
without changing DB status — except Provider::hasFeature(), which
returns false correctly at runtime but leaves DB unchanged.

═══════════════════════════════════════════════════════
F. CURRENT SUBSCRIPTION DATA
═══════════════════════════════════════════════════════

Snapshot as of 2026-09-15:

| ID | Provider | Plan | Status | Start | End | isActive |
|----|----------|------|--------|-------|-----|----------|
| 1 | 14 (The Himalayan Journey) | Business (3) | active | 2026-09-02 | 2026-10-02 00:00:00 | TRUE |
| 2 | 15 (Test Free Provider) | Free (1) | active | 2026-09-02 | 2026-10-02 00:00:00 | TRUE |
| 3 | 16 (Test Professional Provider) | Professional (2) | active | 2026-09-02 | 2026-10-02 00:00:00 | TRUE |
| 4 | 17 (Test Enterprise Provider) | Enterprise (4) | active | 2026-09-02 | 2026-10-02 00:00:00 | TRUE |

COUNTS:
  Total subs:        4
  Active:            4
  Active NULL end:   0
  Active future end: 4
  Active past end:   0  ← no currently expired
  Pending:           0
  Cancelled:         0
  Expired:           0

RELATED COUNTS:
  Providers:  726
  Users:       39
  Bookings:    31
  Services:  1169

⚠ No subscription is currently in an expired-active state.
  First expiry will occur 2026-10-02 00:00:00 (17 days from now).

═══════════════════════════════════════════════════════
G. MULTIPLE ACTIVE SUBSCRIPTION ANALYSIS
═══════════════════════════════════════════════════════

QUERY RESULT:
  Providers with multiple active subs: 0

activeSubscription() DEFINITION:
  hasOne(Subscription::class)
    ->where('status', 'active')
    ->latest('id')

BEHAVIOR IF MULTIPLE ACTIVE SUBS EXISTED:
  - `hasOne` limits query to 1 row.
  - `latest('id')` picks highest ID.
  - If both expired (status still 'active'), the newest would be
    selected — even though runtime isActive() would return false.
  - → Ambiguity: correct "current" subscription may not be the
    highest-ID active-status row.

CURRENT RISK: LOW (zero multi-active rows).
FUTURE RISK: MEDIUM if automation creates new Free sub without
cancelling old expired-but-still-active sub.

MASTER DECISION NEEDED:
  When subscription expires, should the system:
  A. Flip status to 'expired' (single-row change), or
  B. Create a new Free sub and cancel the expired one?

═══════════════════════════════════════════════════════
H. CONCURRENCY RISKS
═══════════════════════════════════════════════════════

If expiry automation runs while:

1. Provider upgrades
   - upgrade() cancels current sub (status='cancelled')
   - Expiry job might have just set status='expired'
   - Race: which write wins? Last commit.
   - Current code has no lock/version check → race exists.

2. Provider resumes
   - resume() reads cancelled sub, creates new
   - If expiry sets expired during read → resume may fail to find
     "cancelled" sub.
   - Race exists.

3. Provider cancels
   - cancel() reads active sub, sets cancelled
   - If expiry sets expired simultaneously → last write wins
   - Both are terminal states; no functional harm.

4. Provider registers
   - Registration creates new sub — no conflict with expiry on
     existing subs (new sub has fresh end_date).

5. Two expiry workers simultaneously
   - Both query same expired-active row
   - Both try to UPDATE status='expired' + save()
   - Race: only one meaningful; second is no-op if check exists.
   - Without WHERE status='active' guard → double-write (harmless).

NO EXISTING LOCKING for subscription mutations.
FIX-07 will need to consider WHERE status='active' guards.

═══════════════════════════════════════════════════════
I. NOTIFICATION / EMAIL FINDINGS
═══════════════════════════════════════════════════════

app/Mail/ (3 classes):
  InvoiceMail, QuotationMail, WaitlistConfirmation
  → NONE subscription-related.

app/Notifications/ (6 classes):
  BookingStatusUpdated, NewReviewReceived,
  QuotationReadyNotification, QuotationRequestedNotification,
  SafetyAlertNotification, SosSmsNotification
  → NONE subscription-related.

findstr "subscription|expiry|expire" in Mail/Notifications
  → ZERO matches.

QUEUE_CONNECTION=sync (local)
MAIL_MAILER=smtp (local)

VERDICT: ❌ NO subscription expiry notification infrastructure.
Any email on expiry would need to be created from scratch.

MASTER DECISION NEEDED:
  Should FIX-07 include an expiry notification email?
  - If yes: create new Mail class + notification job.
  - If no: FIX-07 limited to status flip only.

═══════════════════════════════════════════════════════
J. PROPOSED FIX-07 SCOPE (DRAFT — awaiting Master decision)
═══════════════════════════════════════════════════════

MINIMAL SCOPE (option A — status flip only):
  1. Create job/command: ExpireSubscriptionsJob
     - Query: status='active' AND end_date IS NOT NULL AND end_date <= now()
     - Update: status='expired', save()
     - Idempotent via WHERE status='active' clause
  2. Register in routes/console.php as daily schedule
  3. (Optional) Admin manual trigger route
  4. No notification email

FULL SCOPE (option B — status flip + notification):
  All of option A +
  5. New Mail class: SubscriptionExpiredMail
  6. Dispatch on expiry (queued)
  7. Provider dashboard shows "expired" state
  8. Sidebar/nav adjusted for expired state

ENTERPRISE SPECIAL HANDLING:
  - Current code: Enterprise has finite end_date (2026-10-02)
  - Same expiry logic applies — no special-case needed
  - Enterprise unlimited limits are feature-based, not expiry-based

CANCELLED SUBS:
  - Already status='cancelled' → not touched by expiry job
  - Preserved ✅

NULL end_date:
  - Not touched (query uses `end_date <= now()` — NULL excluded)
  - Enterprise/custom perpetual subs remain active ✅

PROVIDER FREE FALLBACK:
  - Already handled at runtime by Provider::hasFeature()
  - After status='expired', hasFeature() returns false for paid features
  - No new Free sub auto-created (Master decision needed — see G)

═══════════════════════════════════════════════════════
K. PROPOSED RUNTIME TEST PLAN
═══════════════════════════════════════════════════════

T1. Future subscription untouched
   - Create sub with end_date=+1day → run job → status='active' unchanged

T2. Exact expiry boundary
   - Carbon::setTestNow(end_date) → run job → status='expired'

T3. Past expiry
   - Carbon::setTestNow(end_date+1s) → run job → status='expired'

T4. Cancelled subscription untouched
   - Sub with status='cancelled' → run job → unchanged

T5. NULL end_date untouched
   - Sub with status='active', end_date=NULL → run job → unchanged

T6. Multiple active subs per provider
   - Create 2 active subs, both expired → run job → both 'expired'

T7. Idempotency
   - Run job twice → second run makes 0 changes

T8. Provider Free fallback (runtime)
   - Expire paid sub → provider->hasFeature() → false
   - BookingLimitService::maxFor() → 10 (Free default)

T9. Feature access after expiry
   - Provider 14 has Business; if expired → full_analytics DENY

T10. Limits after expiry
   - maxFor() drops from 1000 (Business) to 10 (Free fallback)

ALL tests to use temporary providers/subs, cleaned after.

═══════════════════════════════════════════════════════
L. OPEN DECISIONS FOR MASTER
═══════════════════════════════════════════════════════

DECISION 1 — Expired status vs new Free sub
  When a paid sub expires, does provider:
  A. Just flip status='expired' (simplest, matches current schema), or
  B. Auto-create new Free sub + cancel expired one?

  Based on FIX-01 spec § A9: "expired → Free fallback" at runtime.
  → Option A is consistent with existing architecture.
  → Master को confirm चाहिन्छ।

DECISION 2 — Notification email on expiry
  Does FIX-07 need to send an email to the provider?
  A. Yes — create SubscriptionExpiredMail
  B. No — status flip only

  Current Mail infrastructure: 0 subscription-related.
  → Decision affects scope significantly.

DECISION 3 — Admin Manual Trigger
  Should admin be able to manually trigger expiry?
  A. Yes — add button in admin panel
  B. No — scheduler only

DECISION 4 — Enterprise end_date
  Enterprise sub has finite end_date (2026-10-02).
  Should Enterprise:
  A. Auto-expire same as paid plans (current data implies yes)
  B. Never expire (NULL end_date, manual renewal)

  Current data has finite end_date — implies Option A.
  → Master confirmation needed.

DECISION 5 — Scheduler Running Mechanism
  Local dev has no evidence scheduler is running.
  Windows Task Scheduler setup needed?
  A. Yes — provide setup instructions
  B. No — job triggered manually in dev, scheduler only in prod

═══════════════════════════════════════════════════════
M. FILES INSPECTED
═══════════════════════════════════════════════════════

Models:
  app/Models/Subscription.php
  app/Models/Provider.php (activeSubscription, hasFeature)
  app/Models/Plan.php (via relationship)

Controllers:
  app/Http/Controllers/Provider/SubscriptionController.php
  app/Http/Controllers/Admin/SubscriptionController.php (referenced)
  app/Http/Controllers/Auth/RegisterController.php (status creation)
  app/Http/Controllers/Public/BookingController.php (hasFeature consumer)

Services:
  app/Services/PaymentService.php (subscription activation)

Routes/Config:
  routes/console.php
  bootstrap/app.php
  .env

Data (Tinker runtime queries):
  - All subscriptions with plan + provider
  - Counts across subscription statuses
  - Multiple active subs check
  - Carbon boundary tests

Scheduler/Jobs:
  app/Console/Commands/ (11 files)
  app/Jobs/ (8 files including Safety)

Mail/Notifications:
  app/Mail/ (3 classes)
  app/Notifications/ (6 classes)

═══════════════════════════════════════════════════════
N. GIT / DATA HYGIENE
═══════════════════════════════════════════════════════

GIT STATUS:
  On branch main
  Ahead of origin/main by 8 commits.
  No modified files.
  Untracked (intentional, kept):
    - docs/plan_limits/FIX-05_Phase3_Targeted_Report.md
    - docs/plan_limits/FIX-06_Implementation_Report.md

DATA HYGIENE:
  NO code changes made.
  NO DB records modified.
  NO migrations created.
  NO scheduler modified.
  All runtime Tinker operations were read-only.
  Carbon::setTestNow() was reset after each test.

═══════════════════════════════════════════════════════
STOP CONDITION
═══════════════════════════════════════════════════════

Per Master directive: AUDIT ONLY — STOP after report.

❌ NO commit
❌ NO push
❌ NO code changes
❌ FIX-07 implementation not started
❌ FIX-08 not started

AWAITING MASTER AUTHORIZATION FOR IMPLEMENTATION.
Master decisions needed on 5 items (Section L) before
implementation can begin.