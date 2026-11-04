# 🎯 PHASE 4 — COMPLETE MASTER PLAN (5 REMAINING CATEGORIES)

**Version:** 1.0
**Created:** 2026-11-04
**Owner:** Parashar Regmi (दाइ)
**Project:** TravelAI Nepal
**Status:** PLANNING ONLY — Implementation scheduled for 2026-11-05 (Tomorrow)

---

## 📋 PURPOSE

Dashboard V2 ले ४ वटा categories (Trekking, Shop, Rental, Wholesale) पूर्ण रूपमा काम गरिरहेको छ। यो document ले बाँकी ५ वटा categories (Transport, Hotel, Restaurant, ArtCraft, MoneyChanger) लाई securely Dashboard मा कसरी जोड्ने भनेर पूर्ण Technical Plan दिन्छ।

**मुख्य उद्देश्य:** झूटो booking नदेखाउने। Verified ownership पछि मात्र booking देखाउने। Provider workflow मा कुनै परिवर्तन नगर्ने।

---

## 📊 CURRENT STATE (Verified 2026-11-04)

| Item | Value |
|---|---|
| HEAD | `a7d6344` (feature/traveler-dashboard-v2) |
| Completed Categories | 4 (Trekking, Shop, Rental, Wholesale) |
| Pending Categories | 5 (Transport, Hotel, Restaurant, ArtCraft, MoneyChanger) |
| Tests Passing | 186 + 10 (Dashboard V2) |
| Database Status | Backup: `backup_2026-11-04.sql` (953 KB) |

---

## 🏨 CATEGORY-BY-CATEGORY VERDICT (From Discovery)

| Category | Rows | Contact Field | Reference Field | Verdict |
|---|---|---|---|---|
| **Transport** | 10 | `passenger_email` | `booking_ref` (Unique) | ✅ Ready for Email OTP |
| **Hotel** | 16 | `guest_email` | ❌ None (need to use `id` or add new) | ⚠️ Needs reference field |
| **Restaurant** | 25 | `customer_phone` | `order_number` | ⚠️ Needs Phone OTP infra |
| **ArtCraft** | 3 | `client_email` | ❌ None | 🔴 Admin-assisted claim only |
| **MoneyChanger** | 26 | ❌ None (only name/initials) | `transaction_number` | 🔴 BLOCKED — No contact info |

---

## 🏗️ REVISED ARCHITECTURE (All Master Corrections Applied)

### Database Schema (3 Tables, Polymorphic)

**1. `booking_claims` (Ownership — One row per booking)**
```php
Schema::create('booking_claims', function (Blueprint $table) {
    $table->id();
    $table->string('claimable_type', 100);
    $table->unsignedBigInteger('claimable_id');
    $table->unsignedBigInteger('traveler_id');
    $table->string('claim_email', 190);
    $table->timestamp('claimed_at');
    $table->timestamps();
    $table->unique(['claimable_type', 'claimable_id'], 'uq_booking_owner');
    $table->index('traveler_id');
});
2. booking_claim_attempts (Transient OTP State — Multiple allowed)

php
Schema::create('booking_claim_attempts', function (Blueprint $table) {
    $table->id();
    $table->string('claimable_type', 100);
    $table->unsignedBigInteger('claimable_id');
    $table->unsignedBigInteger('traveler_id');
    $table->string('claim_email', 190);
    $table->string('otp_hash', 64)->nullable(); // HMAC-SHA256
    $table->timestamp('otp_expires_at')->nullable();
    $table->unsignedTinyInteger('otp_attempts')->default(0);
    $table->enum('status', ['pending_otp', 'verified', 'expired', 'locked', 'cancelled'])->default('pending_otp');
    $table->ipAddress('ip_address')->nullable();
    $table->string('user_agent', 255)->nullable();
    $table->timestamps();
    $table->index(['claimable_type', 'claimable_id', 'status'], 'idx_active');
});
3. booking_claim_events (Audit Log — Immutable)

php
Schema::create('booking_claim_events', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('booking_claim_id')->nullable();
    $table->unsignedBigInteger('booking_claim_attempt_id')->nullable();
    $table->unsignedBigInteger('traveler_id')->nullable();
    $table->enum('event_type', ['initiated', 'otp_sent', 'otp_wrong', 'otp_expired', 'locked', 'claimed', 'duplicate_attempt', 'rate_limited']);
    $table->string('claimable_type', 100)->nullable();
    $table->unsignedBigInteger('claimable_id')->nullable();
    $table->ipAddress('ip_address')->nullable();
    $table->string('user_agent', 255)->nullable();
    $table->json('metadata')->nullable();
    $table->timestamp('created_at');
    $table->index(['claimable_type', 'claimable_id'], 'idx_booking');
});
Security & OTP Workflow (Master Corrections Applied)
OTP Security: Cryptographic random (6 digits), hashed (HMAC-SHA256), 5 min expiry, 5 max attempts, single-use.

Queue Security: OTP NEVER queued. Sent synchronously via mail channel only. No database channel for OTP. ShouldBeEncrypted applied if queued later.

Expired Recovery: booking_claims row ONLY created on success. Expired/locked attempts never block future legitimate claims.

Concurrency: DB::transaction + UNIQUE(claimable_type, claimable_id) catches race conditions. Duplicate insert returns generic error.

Enumeration Prevention: Same generic error for: invalid ref, wrong email, already claimed.

Rate Limiting: 3 initiate requests / 15 min / (user_id + IP).

📋 PHASED ROLLOUT PLAN (Start Tomorrow)
Phase 4.1 — Transport (Day 1-2)
यो पहिलो pilot हो।

Email OTP + booking_ref

Files: TransportClaimController, TransportClaimService, BookingClaimAttempt, BookingClaimEvent, 2 views, 22 tests.

Effort: ~22 hours.

Phase 4.2 — Hotel (Day 3)
Transport को code 80% reuse हुनेछ।

Email OTP + id as reference (guest_email match).

Missing: Reference number field. (सिफारिस: id प्रयोग गर्ने, नयाँ field थप्नु पर्दैन)।

Effort: ~8 hours (Infra ready भएकोले)।

Phase 4.3 — Restaurant (Day 4-5)
Phone OTP infrastructure चाहिन्छ।

Phone OTP + order_number

Missing: SMS gateway (Twilio/Sparrow). अहिले SMS infra छैन।

Effort: ~16 hours (SMS setup सहित)।

Phase 4.4 — ArtCraft (Day 6)
Manual/Admin Claim।

Admin-assisted verification (किनभने reference छैन, ३ वटा मात्र order छन्)।

Admin ले prove हेरेर claim गर्ने, audit log राख्ने।

Effort: ~6 hours.

Phase 4.5 — MoneyChanger (BLOCKED)
अहिलेलाई रोक्ने।

कुनै contact info छैन (email/phone)।

Future: Provider workflow मा contact field थपिएपछि मात्र claim design गर्ने।

Effort: N/A (Blocked)।

🚨 FEATURE FLAG DESIGN
config/features.php

php
return [
    'traveler_dashboard_v2' => env('TRAVELER_DASHBOARD_V2', false),
    'transport_claim'       => env('TRANSPORT_CLAIM_ENABLED', false),
    'hotel_claim'           => env('HOTEL_CLAIM_ENABLED', false),
    'restaurant_claim'      => env('RESTAURANT_CLAIM_ENABLED', false),
    'artcraft_claim'        => env('ARTCRAFT_CLAIM_ENABLED', false),
];
.env.example

env
TRAVELER_DASHBOARD_V2=false
TRANSPORT_CLAIM_ENABLED=false
HOTEL_CLAIM_ENABLED=false
RESTAURANT_CLAIM_ENABLED=false
ARTCRAFT_CLAIM_ENABLED=false
🔄 ROLLBACK PROCEDURE (Master Corrections Applied)
Tier 1: Feature Flag OFF (Fastest)

cmd
.env: TRANSPORT_CLAIM_ENABLED=false
php artisan config:cache
Result: Route 404, dashboard CTA hidden. Data preserved.

Tier 2: Code Revert

cmd
git revert <commit-hash>
Result: Claim code removed. Tables remain.

Tier 3: Explicit Table Drop (Never urgent)

cmd
php artisan migrate:rollback --path=database/migrations/XXXX_create_booking_claim_tables.php
Result: ONLY claim tables dropped. transport_bookings untouched.

🧪 TEST CASES (Per Category)
Phase 4.1 Transport (22 tests):

Auth & Flag (4)

Expired Claim Recovery (3)

OTP Security (4)

Audit Trail (2)

Concurrency (3)

Enumeration & Security (3)

Regression (3)

(Hotel/Restaurant/ArtCraft को tests, तिनको implementation सुरु हुँदा थपिनेछ)

📅 TOMORROW'S EXACT NEXT ACTIONS
Pre-Implementation Verification (5 min)

Run phase4_1_verify.php (find storeBooking, check passenger_email duplicates).

Master Plan Approval (5 min)

तपाईंले यो document review गर्ने।

Implementation Phase 4.1 (Day 1)

Migrations (3 tables)

Models (3)

Service + Notification + Controller

Routes + Feature Flag

Views (2)

Tests (22)

Manual QA + Commit (Day 1 end)

🚨 CRITICAL RULES (For Any Assistant)
migrate:fresh, db:wipe, TRUNCATE — NEVER

git add . — NEVER (explicit files only)

Push/Merge — तपाईंको GO बिना NEVER

booking_claims UNIQUE constraint — NEVER remove (ownership guarantee)

OTP plaintext — NEVER in DB, logs, queue, or notifications

Existing transport_bookings — NEVER modify schema

📞 RESOURCES
Backup: backup_2026-11-04.sql (953 KB)

Test File: tests/Feature/Traveler/DashboardV2Test.php (10 tests passing)

Feature Flag Middleware: app/Http/Middleware/FeatureFlagMiddleware.php

END OF DOCUMENT