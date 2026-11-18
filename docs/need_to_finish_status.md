# 🎯 TravelAI Nepal — MASTER HANDOVER DOCUMENT
**Last Updated:** 2026-11-18 (Timezone Fix COMPLETE + Email System Fixes)
**Current Commit:** `15c15c9` (Timezone Fix — pushed to main)
**Test Baseline:** ✅ **297 passed, 1 skipped** (1970 assertions)
**Production URL:** https://travelainepal.com (Laravel Cloud)

> ⚠️ **NEW ASSISTANT — यो file पूरै पढ्नुस् पहिले।** यसले तपाईंलाई A-Z context दिन्छ। कोई confusion हुनु हुँदैन। Rule A पालना गर्नुस् — code लेख्नु अघि verify गर्नुस्।

---

## 🚨 ABSOLUTE RULES (कोई अपवाद नै)

| Rule | Meaning |
|---|---|
| **Rule A — NO GUESS** | कोई code लेख्नु अघि actual file पढ्नुस् (FIND strings actual code बाट लिनुस्). DB columns verify गर्नुस्. `getFillable()`, `getCasts()` check गर्नुस्. |
| **Rule B — ZERO REGRESSION** | हरेक change पछि `php artisan test` → 297 pass हुनुपर्ने. Fail भयो भने STOP. |
| **Rule C — NO DESTRUCTIVE** | `migrate:fresh`, `db:wipe`, existing data overwrite — कहिल्यै होइन. सधैं `Schema::hasColumn()` guard राख्नुस्. |
| **Rule D — EXPLICIT GIT ADD** | `git add .` कहिल्यै होइन. हरेक file explicit add. |
| **Rule E — PUSH LOCK** | Master GO बिना `git push` कहिल्यै होइन। |
| **Rule F — NO CONFIG TIMEZONE MUTATION** | Middleware भित्र `config(['app.timezone' => ...])` वा `date_default_timezone_set()` कहिल्यै नगर्नुस् — logs/queues बिग्रन्छ। Request-scoped मात्र (`view()->share()` + `$request->attributes`). |

---

## 📊 CURRENT STATE — Verified

### Git Status
- **Branch:** main
- **Tests:** 297 passed, 1 skipped (1970 assertions)
- **Production:** 🟡 Local commits pending push (Master GO after all tasks)
- **Last Push:** `15c15c9` (Timezone Fix)

### Environment
- **PHP:** 8.4.27 (Laragon, Device Guard exclusion applied)
- **Laravel:** 13.32.0
- **Database:** MySQL `travelai_db`
- **Queue:** `sync` (Notification queue fix intentionally deferred)
- **Mail:** `smtp` (production-ready)

---

## ✅ COMPLETED WORK (A-Z) — Latest First

### 🚀 GLOBAL-TIMEZONE-SYNC-01 — COMPLETE (Commit `15c15c9`)
**Goal:** सबै dashboards मा UTC लाई user को local time मा देखाउने।
**Approach (FINAL — safe pattern):**
- **DB save:** सधैं UTC (unchanged, Laravel default)
- **Display:** Carbon `setTimezone()` — **कहिल्यै** `config(['app.timezone'])` mutate गरिएको छैन
- **Files added:**
  - `app/Helpers/helpers.php` — `local_time()`, `local_iso()`, `user_timezone()`
  - `app/Http/Middleware/SetUserTimezone.php` — request-scoped, `view()->share('userTimezone', ...)`
  - `database/migrations/2026_11_18_014758_add_timezone_to_users_table.php` — `users.timezone` default `Asia/Kathmandu`
- **Files modified:**
  - `bootstrap/app.php` — web middleware group मा SetUserTimezone
  - `composer.json` — `autoload.files` मा helpers.php
  - `app/Models/User.php` — `timezone` in `$fillable`
  - `resources/js/app.js` — `window.timeAgo()`, `window.localTime()`, `[data-ago]` autoscan
  - `routes/web.php` — `POST /api/set-timezone` (guest session sync, in web.php so CSRF+session work)
  - 46 blade views — `->format('...H:i...')` → `local_time(...)`
- **DATE vs DATETIME pattern (महत्त्वपूर्ण नियम):**
  - DATETIME (`created_at`, `updated_at`, `paid_at`) → `local_time($x, 'M d, Y H:i')`
  - DATE (`booking_date`, `start_date`, `departure_date`, `check_in_date`) → `$x->format('M d, Y')` **(कहिल्यै local_time होइन)**
- **Emails/PDFs:** UTC नै राखियो (recipient TZ unknown)
- **Verified:** Booking created 08:54 Nepal → DB 03:09 UTC → view 08:54 ✅. Booking Date → `Nov 18, 2026` ✅.

### 🚀 Email Notification System Overhaul (All Verticals)
- **Traveler Emails:** `->queue()` → `->send()` (Transport, Restaurant, ArtCraft, Orders)
- **Provider Emails:** Added missing Provider email triggers (Trek/Tour, Transport, Restaurant, ArtCraft, Orders)
- **New Mail Classes:** `ProviderBookingReceivedMail`, `ProviderReservationReceivedMail`, `ProviderArtCraftReceivedMail`, `ProviderOrderPaymentNoticeMail`
- **Verified:** All emails sent successfully. 297 tests pass.

### 1. Traveler Welcome Email (Commit `999c903`)
- `BookingWelcomeMail`, `OrderWelcomeMail` — registered vs guest logic, i18n
- Wire in `Public\BookingController` + `CheckoutController`

### 2. Wholesale RFQ Email (Commit `cc88632`)
- `WholesaleRfqMail` — `'confirmation'` type

### 3. Review System (Phase 1 & 2) (Commits `6b2f99d`, `aeec30c`)
- Public Display, Admin Moderation, Provider Respond
- Observer auto-update, verified live

### 4. Transport Module (Phase 1, B, C) (Commits `02527fb` etc.)
- Public search flow, Scheduled Trips, Seat Inventory
- Payment flow (advance/PAY), Cancellation + Refund
- Email receipt PDF

### 5. Hotel & Restaurant Modules
- Hotel: Public page, Booking flow, Provider confirm/payment, QR
- Restaurant: Public page, Table reservation, Emails

### 6. ArtCraft A+ (Phase 1+2+3 partial)
- Public gallery, Artwork detail, Custom commission form
- Enquiry mail + notification

---

## 🔴 PENDING WORK (Priority Order)

### 🚨 TICKET #1: AI-QUALITY-GATE-01 (Poon Hill Bug Prevention)
**Issue:** Route data got corrupted/overwritten previously, causing Poon Hill to be missing. AI followed the broken data.
**Fix Required:** Add `validateRequiredWaypoints()` in `AiItineraryDraftController.php` to reject AI output if required detours (like Poon Hill) are missing.

### 🚨 TICKET #2: CHECKOUT-EMAIL-FIX-01
**Issue:** Traveler welcome email might be missing when order is placed in the e-commerce checkout.
**Fix Required:** Add `OrderWelcomeMail` trigger in `CheckoutController.php` right after order creation.

### 🚨 TICKET #3: SEEDER-PROTECTION-01
**Issue:** Production `route_segments` can be accidentally overwritten by seeders, breaking itineraries.
**Fix Required:** Add guard in seeder to prevent running on production or truncating existing data.

---

## 🟡 MEDIUM PRIORITY (Post-Presentation)

1. **DUMMY-DATA-CLEANUP-01:** Flag 600+ dummy providers/services with `is_demo = 1` to hide them from public view.
2. **HOTEL/RESTAURANT EMAIL VERIFY:** Verify if `queue()` → `send()` switch is needed for Hotel/Restaurant.
3. **ADMIN-NOTIFICATION-SYSTEM:** Full notification preferences, digests, and templates.
4. **INDIVIDUAL & MONEYCHANGER PUBLIC PAGES:** Transport pattern.

---

## 🟣 FUTURE TICKETS (Documented)

- **NEPAL-VAT-INVOICE-01:** 13% VAT compliance, PAN/VAT number, IRD format.
- **CUSTOM-DOMAIN-FEATURE-01:** White-label Level 3.
- **PROVIDER-EARNINGS-DASHBOARD-01:** Consolidated earnings across verticals.

---

## 📋 NEXT STEPS — Priority
1. **Add AI Quality Gate Validator** (Poon Hill)
2. **Check Checkout Email Logic**
3. **Add Seeder Protection**
4. **Push to Production (Master GO)**

---

## 📋 Baki Kaam Haru ko Shortlist (Priority Order)
1. AI Quality Gate Validator (Poon Hill Bug): `AiItineraryDraftController` मा Validator halne, jasko karan data bigriyeko bela AI le galat itinerary banauna nadina sake.
2. Checkout Email Verify: `CheckoutController` मा order place huda Traveler lai mail jana ko lagi code thapne (yadi chhutiyeko chha bhane).
3. Seeder Protection: Production data accidentally reset huna bata rokne.
4. Dummy Data Hide (`is_demo`): 600+ dummy providers/services lai hide garne (public bata).