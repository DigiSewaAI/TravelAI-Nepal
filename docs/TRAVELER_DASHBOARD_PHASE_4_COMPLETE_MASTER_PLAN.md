# 🎯 PHASE 4 — COMPLETE MASTER PLAN (v2.0)

**Version:** 3.0 | **Updated:** 2026-11-05
**Owner:** Parashar Regmi (दाइ)
**Project:** TravelAI Nepal
**Status:** PLANNING COMPLETE — Implementation pending owner GO

---

## 🚨 CRITICAL RULES (Any Assistant Must Follow)

1. ❌ `migrate:fresh`, `db:wipe`, `TRUNCATE`, `DROP TABLE` — NEVER
2. ❌ `git add .` — NEVER (explicit files only)
3. ❌ Push/Merge — Owner's GO required
4. ❌ `booking_claims` UNIQUE constraint — NEVER remove
5. ❌ OTP/receipt plaintext — NEVER in DB/logs/session/audit
6. ❌ APP_KEY fallback for CLAIM_CODE_SECRET in prod — NEVER
7. ❌ Auto-link phone-only orders — NEVER
8. ❌ Modify existing transport_bookings/hotel_bookings/restaurant_orders data — NEVER
9. ✅ Reuse Phase 4.1 foundation tables — MUST
10. ✅ Feature flag controls routes AND provider UI — MUST

---

## 📊 CURRENT STATE (2026-11-05)

| Item | Value |
|---|---|
| Branch | `feature/restaurant-claim` |
| HEAD | Latest commit (after Transport) |
| Completed Categories | 6 (Trekking, Shop, Rental, Wholesale, Restaurant, Transport) |
| Pending Categories | 3 (Hotel, ArtCraft, MoneyChanger) |
| Tests Passing | **255** (240 + 15 Transport) |
| Phase 4.1 Foundation | ✅ DONE |
| Phase 4.2 Restaurant | ✅ DONE (commit `4423866`) |
| Phase 4.3 Transport | ✅ DONE (this commit) |
| DB Backup | `backup_before_phase42b_2026-11-05.sql` |

### Commits Progress

| Commit | Phase |
|---|---|
| `fd5a762` | 4.1 Foundation |
| `043776d` | 4.2A Services |
| `4c2b75a` | 4.2C Claim flow + i18n |
| `394d153` | 4.2B Provider + PDF |
| `277fe31` | Polish (pluralization + Individual card) |
| `2e007ea` | 4.2D Admin reissue |
| `4423866` | Restaurant tests (22) + role check |
| **(new)** | 4.3 Transport claim (15 tests) |

---

## 📋 CATEGORY STATUS

| Category | Rows | Contact | Ref | Status |
|---|---|---|---|---|
| Trekking | 1 | traveler_id | booking_number | ✅ Working |
| Shop | 20 | user_id | order_number | ✅ Working |
| Rental | 20 | user_id | order_number | ✅ Working |
| Wholesale | 0 | buyer_id | rfq_number | ✅ Working |
| **Transport** | 10 | passenger_email | booking_ref | ✅ **DONE** (commit pending) |
| **Hotel** | 16 | guest_email | id | ⏳ Ready (~8 hr) |
| **Restaurant** | 25 | customer_phone | order_number | ✅ **DONE** (`4423866`) |
| **ArtCraft** | 3 | client_email | id | ⏳ Admin manual (~6 hr) |
| **MoneyChanger** | 26 | ❌ NONE | transaction_number | 🔴 BLOCKED |

---

## 🏗️ PHASE 4.1 — FOUNDATION (DONE)

### 3 Reusable Tables

**1. `booking_claims`** — Ownership (1 row per booking)
- Columns: claimable_type, claimable_id, traveler_id, claim_email, claim_phone, claimed_at
- UNIQUE(claimable_type, claimable_id)

**2. `booking_claim_attempts`** — OTP state (multiple allowed)
- Columns: claimable_type, claimable_id, traveler_id, contact_method, contact_value, otp_hash, otp_expires_at, otp_attempts, status
- Status enum: `pending, verified, expired, locked, cancelled`

**3. `booking_claim_events`** — Audit log (immutable)
- Event types: initiated, otp_sent, otp_wrong, otp_expired, locked, claimed, duplicate_attempt, rate_limited, receipt_wrong, receipt_expired, receipt_reissued, pdf_generation_failed

---

## 🍽️ PHASE 4.2 — RESTAURANT CLAIM (V4 FINAL)

### ✅ Verified Facts (2026-11-05)

| # | Fact | Evidence |
|---|---|---|
| 1 | `storeOrder()` customer fields write **गर्दैन** | findstr empty |
| 2 | Provider form मा customer fields **छैनन्** | verify output |
| 3 | Existing orders: `ORD-REST-00001` (sequential) | DB |
| 4 | storeOrder() format: `ORD-<uniqid>` (random) | Code |
| 5 | **निष्कर्ष:** 25 orders = seeders/admin | Proof |
| 6 | `customer_email` column **छैन** | Schema |

### Architecture

Provider form enhanced → Order create → Receipt code generated (8 chars, HMAC hash) → PDF streamed immediately → Customer gets PDF → Traveler claims via Email OTP OR Receipt code → Atomic ownership → Invalidates other method.

### Schema Changes

**Migration 1 — `restaurant_orders`:**
```php
$table->string('customer_email', 190)->nullable()->after('customer_phone');
$table->string('receipt_code_hash', 64)->nullable()->after('customer_email');
$table->timestamp('receipt_code_issued_at')->nullable();
$table->timestamp('receipt_code_used_at')->nullable();
$table->unsignedTinyInteger('receipt_code_reissue_count')->default(0);
$table->index(['receipt_code_used_at', 'created_at'], 'idx_claimable');
Migration 2 — booking_claim_attempts (rate limit indexes):

php
$table->index(['claimable_type', 'claimable_id', 'status', 'created_at'], 'idx_rl_order');
$table->index(['traveler_id', 'status', 'created_at'], 'idx_rl_user');
Security Design
Receipt Code:

Format: 8 chars from ABCDEFGHJKLMNPQRSTUVWXYZ23456789

HMAC-SHA256 with CLAIM_CODE_SECRET (dedicated, mandatory)

Plaintext: PDF मा एक पटक मात्र — DB/logs/session मा कहिल्यै होइन

Expiry: 90 days

Single-use: receipt_code_used_at = now() + receipt_code_hash = null

OTP:

6 digits with str_pad(random_int(0, 999999), 6, '0', LEFT) — leading zeros

HMAC-SHA256 with config('app.key')

5 min expiry, 5 max attempts

Mail channel only, synchronous (NOT queued)

Rate Limits:

Per-IP: 5/15min (Laravel RateLimiter)

Per-order: 3 failures/24h (via booking_claim_events)

Per-user: 10 failures/24h (cross-category)

Concurrency:

DB::transaction + lockForUpdate() on order row

UNIQUE constraint + catch QueryException code 23000

Generic 404 on all failure cases

Config (config/claim.php)
php
return [
    'restaurant_enabled' => env('RESTAURANT_CLAIM_ENABLED', false),
    'code_secret'     => env('CLAIM_CODE_SECRET'),      // MANDATORY
    'code_secret_old' => env('CLAIM_CODE_SECRET_OLD'),  // rotation
    'receipt_expiry_days' => 90,
    'otp_expiry_minutes'  => 5,
    'otp_max_attempts'    => 5,
    'resend_cooldown_sec' => 90,
    'max_reissues'        => 3,
    'max_reissues_24h'    => 5,
    'rate_limit' => [
        'ip_per_15min'  => 5,
        'order_per_day' => 3,
        'user_per_day'  => 10,
    ],
    'claimable_models' => [
        \App\Models\RestaurantOrder::class  => 'restaurant',
        \App\Models\TransportBooking::class => 'transport',
        \App\Models\HotelBooking::class     => 'hotel',
        \App\Models\CustomOrder::class      => 'artcraft',
    ],
];
File List
🆕 New (18):

2 migrations

config/claim.php

4 services: ReceiptCodeService, OtpService, RestaurantClaimService, RateLimitService

2 controllers: RestaurantClaimController, ReceiptReissueController

3 requests

1 notification: ClaimOtpNotification

4 views (PDF, claim form, verify, admin reissue)

2 test files (Unit + Feature)

🟡 Modified (6):

RestaurantDashboardController (customer fields + PDF)

RestaurantOrder model (fillable)

routes/web.php (+5 flag-gated)

Provider dashboard view (+customer inputs)

Traveler dashboard-v2 (restaurant card)

i18n × 5 locales (+20 keys)

Receipt Delivery (PDF-Only)
Provider submits order

DB::transaction { create + code + hash }

PDF streamed (success) OR nulled (failure — provider retries via admin)

No session flash — plaintext in PDF only

Reissue (Admin Only)
POST /admin/restaurant/orders/{order}/reissue-receipt

Guards: POST+CSRF+Admin auth, reissue_count < 3 (or 5 in 24h), no existing claim

Transaction: new hash + issued_at = now() + used_at = NULL + reissue_count++

Testing
Unit (15): code gen, HMAC, rotation, OTP padding, rate limits, concurrency, audit sanitization

Feature (21): full flows, wrong OTP, wrong code, expired, used, duplicate (same user idempotent / diff user 404), existing 25, rate limit, concurrent, reissue

Browser (10): provider PDF, traveler claim both methods, dashboard visibility, mobile

Rollback (3 Tiers)
Flag OFF — RESTAURANT_CLAIM_ENABLED=false + config:cache

Code revert — git revert <commit>

Migration rollback — explicit path, verify batch first

Effort: ~16 hours (2-3 sessions)
🚐 PHASE 4.3 — TRANSPORT (READY)
Item	Value
Contact	passenger_email (10/10)
Ref	booking_ref (unique, TR-2026-XXXX)
Method	Email OTP only (no receipt)
Reuse	All foundation + Restaurant services
Effort	~6 hours
🏨 PHASE 4.4 — HOTEL (DISCOVERY NEEDED)
Item	Value
Contact	guest_email (16/16)
Ref	id
Method	Email OTP + id
Note	Verify email is real (not seeded)
Effort	~8 hours
🎨 PHASE 4.5 — ARTCRAFT (ADMIN MANUAL)
Item	Value
Contact	client_email (3/3)
Method	Admin manual claim only
Reason	3 records — automation overkill
Effort	~6 hours
💱 PHASE 4.6 — MONEYCHANGER (BLOCKED)
🔴 कोई email/phone छैन — securely claim असम्भव। Provider workflow change बिना हुँदैन।

🔧 CROSS-CUTTING
Env Required Before Implementation
env
CLAIM_CODE_SECRET=<32+ chars>       # openssl rand -base64 32
CLAIM_CODE_SECRET_OLD=               # rotation only
RESTAURANT_CLAIM_ENABLED=false
TRANSPORT_CLAIM_ENABLED=false
HOTEL_CLAIM_ENABLED=false
ARTCRAFT_CLAIM_ENABLED=false
Secrets Rotation
Secret	Purpose	Rotation
APP_KEY	Laravel encryption	Never (re-encrypt)
CLAIM_CODE_SECRET	Receipt HMAC	90-day grace via _OLD
Generic Error (Enumeration Prevention)
All failures → "We couldn't verify this order."

📅 IMPLEMENTATION SEQUENCE
Day 1 — Restaurant Foundation (4 hr)

Branch: git checkout -b feature/restaurant-claim

Env: CLAIM_CODE_SECRET set

Migrations (2) + Config + 4 Services + Unit tests (15)

Day 2 — Provider + Traveler (4 hr)

Provider form + storeOrder + PDF

Claim flow + verify

Feature tests (partial)

Day 3 — Admin + Dashboard + QA (4 hr)

Reissue + dashboard + i18n

Full tests + browser QA

Commit

Day 4-5 — Transport (6 hr)

Day 6 — Hotel (8 hr)

Day 7 — ArtCraft (6 hr)

❓ OPEN DECISIONS (Owner Approval Required)
#	Question	Recommended	Answer
1	Historical 25 orders	Admin manual + "Have receipt?" prompt	❓
2	PDF re-download	New code (max 5 in 24h)	❓
3	Branch	feature/restaurant-claim (new)	❓
4	Prod secret missing	Fail-fast (no fallback)	❓
🧪 TEST CASES (Per Category)
Restaurant (46): 15 unit + 21 feature + 10 browser
Transport (22): auth, expired recovery, OTP security, audit, concurrency, enumeration, regression
Hotel/ArtCraft: add when implemented

📞 RESOURCES
Resource	Path/Value
DB Backup	backup_after_dashboard_v2_2026-11-04.sql (955 KB)
Tests	tests/Feature/Traveler/DashboardV2Test.php
Feature Middleware	app/Http/Middleware/FeatureFlagMiddleware.php
Brevo SMTP	smtp-relay.brevo.com:587
dompdf	config/dompdf.php
📝 CHANGE LOG
Date	Version	Change
2026-11-04	1.0	Initial plan (5 categories)
2026-11-05	2.0	Phase 4.1 DONE; Restaurant V4 full design (8 blockers resolved); Transport/Hotel/ArtCraft/MoneyChanger updated; 4 open decisions
🎯 NEXT ACTIONS (For New Assistant)
Step 1: Run verify commands:

cmd
git status
git branch --show-current
git log -3 --oneline
php artisan migrate:status
Step 2: Read this file completely

Step 3: Continue from last completed phase (check file existence)

Never assume — always verify first.

END OF DOCUMENT

