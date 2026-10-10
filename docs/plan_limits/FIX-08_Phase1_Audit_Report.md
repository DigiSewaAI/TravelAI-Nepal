FIX-08 PHASE 1 — STRIPE WEBHOOK SIGNATURE + IDEMPOTENCY AUDIT
Date: 2026-09-15 | Status: AUDIT COMPLETE — Awaiting Master Implementation Authorization
Scope: AUDIT ONLY — zero code/DB changes made

═══════════════════════════════════════════════════════
A. EXECUTIVE SUMMARY
═══════════════════════════════════════════════════════

🔴 CRITICAL: Webhook is simultaneously UNSECURED and UNUSABLE.

1. STRIPE SIGNATURE NOT VERIFIED
   WebhookController::stripe() reads the header but never verifies
   it. Comment says "optional but recommended" — it was never added.

2. WEBHOOK IS CURRENTLY UNREACHABLE BY STRIPE
   Route lives in web.php with default web middleware group.
   CSRF is NOT excluded for this route.
   → Real Stripe POST (no CSRF token) receives 419.
   → Webhook has likely NEVER successfully processed a real event.

3. ZERO IDEMPOTENCY
   No event_id stored. No stripe_webhook_events table. No unique
   constraint on event identity.
   → Duplicate events would re-execute business logic.

4. NO TRANSACTION WRAPPING
   confirmPayment() mutates Payment + Subscription separately.
   No DB::transaction — partial mutation possible on failure.

5. RACE CONDITIONS EXIST
   confirmPayment() reads Stripe API, then updates 2 records
   without row locks. Concurrent webhook + polling via getStatus()
   could double-activate.

Data current state:
  - 5 payments total (3 stripe / 2 free)
  - stripe_webhook_events: DOES NOT EXIST
  - No 90-day retention infra
  - No lease/claim mechanism

Conclusion: FIX-08 is essential and non-trivial. Both security AND
deliverability gaps must be closed together.

═══════════════════════════════════════════════════════
B. CURRENT WEBHOOK ARCHITECTURE
═══════════════════════════════════════════════════════

1. ROUTE:
   File: routes/web.php
   Line: 375
   Route: Route::post('/webhook/stripe', [WebhookController::class, 'stripe'])->name('webhook.stripe');
   Method: POST
   URL: /webhook/stripe

2. CONTROLLER / ACTION:
   App\Http\Controllers\WebhookController::stripe(Request)

3. MIDDLEWARE:
   Only `web` group (default for web.php routes).
   web group = EncryptCookies, AddQueuedCookiesToResponse,
   StartSession, ShareErrorsFromSession, PreventRequestForgery
   (CSRF), SubstituteBindings, App\Localization.

4. CSRF INVOLVEMENT: YES — enforced, NOT excluded.
   Confirmed via runtime: POST /webhook/stripe → 419 Page Expired.
   No CSRF exception registered in bootstrap/app.php.
   findstr in app\Http\Middleware\*.php → no VerifyCsrfToken override.

5. ROUTE CATEGORY: web (not api).
   routes/api.php has zero stripe/webhook references.

6. PAYLOAD HANDLING:
   $payload = $request->all();
   → Assumes JSON body parses to array via $request->all().
   Stripe sends Content-Type: application/json.
   Laravel web group parses JSON to request→all() correctly.

7. SIGNATURE HEADER HANDLING:
   $sigHeader = $request->header('Stripe-Signature');
   → Read into variable, then NEVER USED.
   No verification. No passing to Stripe SDK.

8. EVENT PARSING:
   Delegated to PaymentService::handleWebhook($payload)
   Reads: $payload['type'] and $payload['data']['object']
   No \Stripe\Event::constructFrom().
   No raw-body/JSON-signature binding.

9. EVENT TYPES HANDLED:
   - payment_intent.succeeded
   - payment_intent.payment_failed
   - payment_intent.canceled
   - default: logged, returns true (silently ignored)

10. MUTATIONS:
    - payment_intent.succeeded → Payment::markAsSuccess(),
      Subscription::status='active', start_date=now(),
      end_date=+1month or +1year (per billing_interval)
    - payment_intent.payment_failed → Payment::markAsFailed()
    - payment_intent.canceled → Payment::markAsFailed()
    - No email/notification dispatched
    - No event_id stored anywhere

═══════════════════════════════════════════════════════
C. SIGNATURE VERIFICATION AUDIT
═══════════════════════════════════════════════════════

| Check | Result |
|-------|--------|
| Signature verified? | ❌ NO |
| Webhook secret loaded? | ✅ YES — config('services.stripe.webhook_secret') |
| .env STRIPE_WEBHOOK_SECRET set? | ✅ YES |
| Stripe SDK Webhook::constructEvent used? | ❌ NO |
| Raw request body used for verification? | ❌ NO (uses $request->all() not getContent()) |
| Timestamp tolerance configured? | ❌ NO |
| Invalid signature returns rejection? | ❌ NO — CSRF blocks BEFORE signature check |
| Missing signature returns rejection? | ❌ NO — CSRF blocks BEFORE signature check |
| Signature check BEFORE business logic? | ❌ N/A — no signature check exists |

CLASSIFICATION: ❌ FAIL

RUNTIME EVIDENCE (three synthetic POST tests):
  Test 1: POST no sig, no CSRF → 419 (CSRF blocks)
  Test 2: POST again → 419 (confirms CSRF blocks all)
  Test 3: POST with fake Stripe-Signature header → 419 (CSRF blocks BEFORE header logic runs)

⚠ IMPORTANT: The 419 mask is a DELIVERABILITY BUG, not security.
   Real Stripe webhooks would also get 419.
   → Webhook has never successfully processed in production.
   → When CSRF is bypassed (as required for Stripe), the missing
     signature check becomes the ONLY unblocked attack vector.

If CSRF were correctly excluded (as FIX-08 will do), the current
code would ACCEPT any unauthenticated POST as a legitimate Stripe
event — including attacker-forged subscription activation.

═══════════════════════════════════════════════════════
D. EVENT HANDLING MATRIX
═══════════════════════════════════════════════════════

| Event | Business meaning | Payment mutation | Subscription mutation | Provider mutation | Notification | Idempotent? | Transaction? |
|-------|------------------|------------------|----------------------|-------------------|--------------|-------------|--------------|
| payment_intent.succeeded | Payment captured | status='success', paid_at=now | status='active', start_date=now, end_date=+1mo/+1yr | none | none | ❌ NO | ❌ NO |
| payment_intent.payment_failed | Payment failed | status='failed' | none | none | none | ❌ NO | ❌ NO |
| payment_intent.canceled | Payment canceled | status='failed' | none | none | none | ❌ NO | ❌ NO |
| (default) | Ignored (logged) | none | none | none | none | N/A | N/A |

⚠ Duplicate "succeeded" event:
  - Payment::markAsSuccess() → update to 'success' (idempotent by value)
  - Subscription activation → status='active', end_date=now()+1mo every time
  - → DUPLICATE end_date update: each duplicate pushes end_date forward
    by another month/year → user gets free extra subscription time
  - → SECOND RUN IS NOT IDEMPOTENT for subscription.end_date

The only natural idempotency: Payment.status ends up 'success' whether
run once or many times. But Subscription.end_date is WRITTEN FRESH
EACH RUN.

═══════════════════════════════════════════════════════
E. DUPLICATE EVENT AUDIT
═══════════════════════════════════════════════════════

Q: What happens if same Stripe event is delivered twice?

Analysis:
1. event_id NOT STORED → cannot detect duplicates.
2. NO unique constraint involving event_id.
3. Handler treats each delivery as fresh.
4. Payment.status = 'success' (idempotent value-wise).
5. Subscription: end_date = now()->addMonth() — RE-EXECUTED each time.

Actual runtime test NOT POSSIBLE without CSRF bypass — CSRF blocks
all duplicate deliveries with 419.

If CSRF were bypassed:
  Duplicate 'payment_intent.succeeded':
    → Payment: 2nd write = same value (idempotent)
    → Subscription: end_date advances by another month (BUG)
    → 2× log entries (harmless)
    → NO email sent (no notification code)

Other events (payment_failed, canceled):
  → Payment status set to 'failed' twice (idempotent)
  → No other mutations

CLASSIFICATION: ❌ FAIL (no idempotency guard exists)

═══════════════════════════════════════════════════════
F. CONCURRENCY AUDIT
═══════════════════════════════════════════════════════

Q: Can two workers process the same event simultaneously?

Current flow (confirmPayment):
  1. $stripePayment = $this->stripe->paymentIntents->retrieve($id)
     (external API call, ~100-500ms)
  2. $payment = Payment::where('payment_id', $id)->first()
  3. if status == succeeded:
     a. $payment->markAsSuccess()          // UPDATE #1
     b. $subscription = $payment->payable
     c. $subscription->status = 'active'   // UPDATE #2 (start of multi-step)
     d. $subscription->end_date = +1mo
     e. $subscription->save()

Race windows:
  - Between step 1 (Stripe API) and step 3a: any number of workers
    could be waiting. All see same "succeeded" result.
  - No row-level locks (no lockForUpdate).
  - No DB::transaction.
  - No unique constraint preventing double-activation.
  - Two workers → two sequential subscription saves → last end_date wins.
  - Payment markAsSuccess called twice → idempotent (same value).

Additional risk: PaymentController::confirm() (user-driven polling)
also calls confirmPayment(). Could race with webhook.

Real parallel test cannot be executed locally because:
  - CSRF blocks all incoming webhook requests (419).
  - Tinker is single-threaded.

CLASSIFICATION: ❌ FAIL (race window exists; not testable in current env)

═══════════════════════════════════════════════════════
G. PAYMENT / SUBSCRIPTION SCHEMA
═══════════════════════════════════════════════════════

payments columns:
  id, payable_type, payable_id, provider_id, user_id,
  payment_id (UNIQUE), gateway, amount, currency, status,
  metadata (json), paid_at, created_at, updated_at

subscriptions columns:
  id, provider_id, plan_id, start_date, end_date, status,
  billing_interval, created_at, updated_at

stripe_webhook_events table: DOES NOT EXIST

Natural idempotency key availability:
  - payments.payment_id is UNIQUE and holds the Stripe PI id.
  - But payments.payment_id is NOT the same as Stripe event_id.
    A single PI can generate multiple events (succeeded, refund,
    dispute, etc.) → event_id needed separately.
  - No existing column can store event_id without schema change.

Conclusion: New table `stripe_webhook_events` is REQUIRED. No existing
schema satisfies the approved v1.5 lease design.

Current payment data:
  - Total: 5 (3 stripe, 2 free)
  - Statuses: completed=3, success=2
  - No pending/failed rows currently
  - No webhook-sourced payments (all appear seeded or free)

═══════════════════════════════════════════════════════
H. LEASE DESIGN FEASIBILITY
═══════════════════════════════════════════════════════

Approved v1.5 design (G2 §):

Fields:
  event_id UNIQUE, state (claimed|processed|failed),
  claim_token, claimed_at, lease_expires_at, processed_at

Feasibility per v1.5 requirement:

1. Atomic claim?
   ✅ YES — INSERT (event_id UNIQUE) with claim_token, or
   UPDATE WHERE lease_expires_at < now() guarded by where(event_id).
   MySQL supports both patterns.

2. Stale claim takeover?
   ✅ YES — atomic UPDATE ... WHERE event_id=? AND lease_expires_at <= now().
   Only one worker wins.

3. Ownership re-verification?
   ✅ YES — inside processing transaction, re-check
   claim_token = this worker's token.

4. Retry on failure?
   ✅ YES — set state='failed', keep claim_token; Stripe retry
   or new worker can claim.

5. Business logic succeeds but event state update fails?
   ⚠ RISK — business mutations committed (via DB::transaction
   wrapping both), but event row update must be in SAME transaction.
   Design v1.5 requires: claim + business DB changes +
   processed_at update in ONE DB transaction (where possible).

6. Claim succeeds but worker crashes?
   ✅ RECOVERABLE — lease_expires_at passes after 60s, another
   worker takes over via stale claim.

7. Two workers race to claim same event?
   ✅ SAFE — UNIQUE(event_id) on INSERT: one insert wins,
   other catches UniqueConstraintViolation and either returns
   duplicate or attempts takeover after lease expiry.

CONCLUSION: Design feasible with current MySQL + Laravel.

═══════════════════════════════════════════════════════
I. CSRF / ROUTING SECURITY
═══════════════════════════════════════════════════════

Current state:
  - Route is POST /webhook/stripe in web.php.
  - CSRF middleware ACTIVE (no exception).
  - Runtime: any POST → 419 (Page Expired).
  - Stripe cannot include CSRF token in webhooks (by design).

Classification:
  - Webhook is NOT usable by Stripe → DELIVERABILITY BUG.
  - CSRF 419 is NOT protection for webhook — it's an accident.
  - When CSRF is removed (FIX-08), signature verification MUST
    become the sole authentication mechanism.

Risk: Without FIX-08, removing CSRF would create a critical hole.
       FIX-08 must add signature verification BEFORE removing CSRF.

Appropriate fix (v1.5 §G1+G2):
  - Exclude /webhook/stripe from CSRF.
  - Require \Stripe\Webhook::constructEvent() in controller.
  - Return 400 on invalid signature.

═══════════════════════════════════════════════════════
J. RETENTION / SCHEDULER FINDINGS
═══════════════════════════════════════════════════════

Existing scheduler (routes/console.php):
  - FetchSafetySourcesJob (every 5 min, withoutOverlapping)
  - VerifyExpiredIncidentsJob (daily)
  - UpdateSafetyStatusesJob (every 15 min)
  - ExpireSubscriptionsJob (daily, withoutOverlapping) [FIX-07]

No 90-day retention pattern exists. No job or command does
"delete records older than N days" for any table.

FIX-08 needs a new job:
  StripeWebhookEventsCleanupJob (or equivalent) — daily
  Deletes stripe_webhook_events WHERE processed_at < now()->subDays(90)
  (or where state='processed' AND processed_at <= 90 days ago)

Can be registered in existing routes/console.php without conflict.

Naming convention in codebase: `<Thing>Job` in app/Jobs. FIX-07 used
`ExpireSubscriptionsJob` (verb-first). FIX-08 could use
`CleanupStripeWebhookEventsJob` or `PruneStripeWebhookEventsJob`.

VERDICT: No existing retention pattern; small new job is required.

═══════════════════════════════════════════════════════
K. RISK CLASSIFICATION
═══════════════════════════════════════════════════════

| Finding | Severity |
|---------|----------|
| Webhook signature never verified | 🔴 CRITICAL |
| Webhook unreachable by Stripe (CSRF blocks) | 🔴 CRITICAL |
| No event_id storage | 🔴 HIGH |
| No idempotency; duplicate → extra end_date | 🔴 HIGH |
| No transaction wrapping in confirmPayment() | 🟠 MEDIUM |
| Race window (Stripe API + dual mutation) | 🟠 MEDIUM |
| No locking in subscription mutation | 🟠 MEDIUM |
| No lease/claim mechanism | 🟠 MEDIUM |
| No 90-day retention cleanup | 🟢 LOW |
| CSRF does not prove webhook security | ℹ️ NOTED (per Master) |

Aggregate: FIX-08 is REQUIRED and must not be deferred. Both security
and deliverability problems must be solved together.

═══════════════════════════════════════════════════════
L. IMPLEMENTATION TEST MATRIX
═══════════════════════════════════════════════════════

Test | Description | Expected Result
-----|-------------|-----------------
T1   | Valid Stripe signature | 200; business logic executes; event row state='processed'
T2   | Invalid signature | 400; no business logic; no event row inserted
T3   | Missing signature header | 400; same as T2
T4   | Valid signature but modified payload | 400 (signature mismatch)
T5   | Duplicate event_id delivered | 200; second = no-op; only one business effect; event row state='processed'
T6   | Two workers claim same fresh event | Exactly 1 claim; other returns 200 duplicate/in_progress
T7   | Stale claim (lease_expires_at past) | Second worker takes over via atomic UPDATE
T8   | Active claim (lease not expired) | Second worker cannot steal; returns 200 in_progress
T9   | Ownership re-check fails (token mismatch inside TX) | Processing aborts; state unchanged; no double mutation
T10  | Business processing throws | DB::transaction rolls back all mutations; event row state='failed'
T11  | Successful processing | Payment status='success'; subscription active; event row state='processed', processed_at set
T12  | Processed event delivered again | 200; no re-execution; state still 'processed'
T13  | Retention cleanup (event 91 days old) | Deleted if state='processed'; younger not deleted
T14  | Unknown event type delivered validly | 200; ignored; event row recorded to prevent infinite retry
T15  | Existing payment/subscription behavior preserved | No regressions to PaymentController flows

For every test, record:
  - HTTP status
  - Event row state (inserted / updated / none)
  - Payment mutation
  - Subscription mutation
  - Final DB counts

═══════════════════════════════════════════════════════
M. PROPOSED FIX-08 SCOPE
═══════════════════════════════════════════════════════

IN SCOPE:
  ✅ CSRF exclusion for /webhook/stripe
  ✅ Stripe signature verification via \Stripe\Webhook::constructEvent()
  ✅ Migration: stripe_webhook_events table
  ✅ WebhookEvent model
  ✅ Claim/lease/takeover logic (atomic UPDATEs)
  ✅ Ownership re-verification inside processing transaction
  ✅ Idempotency: reject second processing of same event_id
  ✅ Business logic wrapping in DB::transaction
  ✅ 90-day retention cleanup job
  ✅ Scheduler registration for cleanup
  ✅ Tests T1-T15

OUT OF SCOPE (per Master §L):
  ❌ Stripe checkout redesign
  ❌ Payment gateway expansion
  ❌ eSewa / Khalti / bank transfer
  ❌ Stripe NPR currency decision
  ❌ Pricing changes
  ❌ Subscription product changes
  ❌ Frontend payment UI redesign
  ❌ Unrelated subscription lifecycle work

═══════════════════════════════════════════════════════
N. OPEN DECISIONS FOR MASTER
═══════════════════════════════════════════════════════

DECISION 1 — Stripe NPR currency
  PaymentService currently sends currency='npr' to Stripe.
  Stripe does NOT support NPR as a presentment currency.
  Is this a known/planned issue for FIX-08 or a separate ticket?
  (FIX-08 does not change payment creation; only webhook security.)

DECISION 2 — Notification on webhook processing
  Current handler sends NO email on subscription activation.
  Should FIX-08 add email? (Master's v1.5 spec does not mention
  email for FIX-08 — assumes NO.)

DECISION 3 — Behaviour on unknown event types
  Current: log and return 200 (silently).
  FIX-08 proposal: record event row with state='processed' (to prevent
  Stripe infinite retry loop) and return 200. Confirm?

DECISION 4 — `provider_id` nullable in payments
  All existing payment rows have provider_id = NULL (seeded).
  Is this expected? FIX-08 will not change this, but worth noting for
  future payment-hygiene work.

DECISION 5 — `processed_at` for failed events
  Should failed events also set `processed_at` (so retention cleanup
  can purge them), or keep only `claimed_at`?
  Proposal: set processed_at on both success and failure so cleanup
  covers all terminal states.

═══════════════════════════════════════════════════════
O. FILES INSPECTED
═══════════════════════════════════════════════════════

Routes:
  routes/web.php (webhook route line 375)
  routes/api.php (no webhook)

Controller:
  app/Http/Controllers/WebhookController.php

Service:
  app/Services/PaymentService.php

Config:
  config/services.php
  .env (STRIPE_* keys only — values not echoed)

Bootstrap:
  bootstrap/app.php (no CSRF exception)

Middleware:
  app/Http/Middleware/AdminMiddleware.php
  app/Http/Middleware/Localization.php
  (No VerifyCsrfToken override)

Model:
  app/Models/Payment.php
  app/Models/Subscription.php

Schema (runtime Tinker):
  payments columns
  subscriptions columns
  stripe_webhook_events existence check

Scheduler:
  routes/console.php

SDK:
  composer.json (stripe/stripe-php ^21.2)
  vendor/stripe/stripe-php (installed)

Runtime tests (Tinker):
  3 synthetic POST requests (all → 419)

═══════════════════════════════════════════════════════
P. GIT / DATA HYGIENE
═══════════════════════════════════════════════════════

GIT STATUS:
  On branch main
  Your branch is ahead of 'origin/main' by 9 commits.
  Untracked: 3 FIX-05/06/07 docs (as intended)

GIT DIFF: EMPTY (no code changes)

DATA HYGIENE:
  ✅ NO code changes made
  ✅ NO DB records created
  ✅ NO migrations created
  ✅ NO config changed
  ✅ Runtime tests were all rejected by CSRF (no side effects)

Existing 5 payments unchanged.
Existing 4 subscriptions unchanged.
No stripe_webhook_events rows created (table doesn't exist).

═══════════════════════════════════════════════════════
STOP CONDITION
═══════════════════════════════════════════════════════

Per Master directive: AUDIT ONLY.
  ❌ NO code changes
  ❌ NO DB changes
  ❌ NO commit
  ❌ NO push
  ❌ NO FIX-09

Awaiting Master authorization to implement FIX-08.
Master decisions needed on 5 items (Section N) before implementation.