# 🎯 TravelAI Nepal — MASTER HANDOVER DOCUMENT
**Last Updated:** 2026-11-14 (Night — Hotel Booking Complete + VAT/Tax Audit)  
**Current Commit:** `TBD` (Restaurant Reservation + Emails + Public Page, pending push)  
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
| **Rule E — PUSH LOCK** | Master GO बिना `git push` कहिल्यै होइन. |

---

## 📊 CURRENT STATE — Verified

### Git Status
- **HEAD:** Latest push (Review Phase 2 + Notification fixes + Booking fixes)
- **Branch:** main
- **Ahead of origin/main by:** 0 commits (✅ pushed — production live)
- **Tests:** 297 passed, 1 skipped (1970 assertions)
- **Production:** ✅ All local commits pushed

### Environment
- **PHP:** 8.4.27 (Laragon, Device Guard exclusion applied)
- **Laravel:** 12
- **Database:** MySQL `travelai_db`
- **Queue:** `sync` (notification queue fix intentionally deferred)
- **Mail:** `smtp` (production-ready)

---

## ✅ COMPLETED WORK (A-Z)

### 1. Traveler Welcome Email (Commit `999c903`)
- `BookingWelcomeMail`, `OrderWelcomeMail` — registered vs guest logic, i18n
- Wire in `Public\BookingController` + `CheckoutController`
- Amber "What happens next?" note
- `verified_purchase` सम्बन्धित नै

### 2. Wholesale RFQ Email (Commit `cc88632`)
- `WholesaleRfqMail` — `'confirmation'` type
- Rich blade template (`wholesale-rfq.blade.php`)
- Buyer bell + email on submit

### 3. Currency Consistency (Commit `ad6a962`)
- Sidebar session-based currency
- Payment banner converted
- Order email total = user's currency
- Rental calculation fallback (`product->price`)

### 4. Categories & Types Seed (Commit `95eff8f`)
- Migration `2026_11_13_022312_add_missing_categories_and_provider_types.php`
- Production मा Restaurant, MoneyChanger, ArtCraft, Rescue types थपियो

### 5. Subscription Middleware (Commit `d149e55` / `16e4805`)
- `app\Http\Middleware\EnsureProviderHasActiveSubscription.php`
- Registered in `bootstrap/app.php` as `provider.active`
- Applied to `routes/web.php` provider group
- **Bypass** in `runningUnitTests()` (tests सुरक्षित)
- Pending plan → payment redirect

### 6. Admin Panel Features (Commit `6b2f99d` मा मिलाइएको)
- **Reviews Moderation** — `/admin/reviews` + sidebar link
- **Plan Management** — `/admin/plans` + edit (Controller, Routes, Views)
- **Broadcast Announcements** — `/admin/broadcast` (Provider + Traveler)
- **CMS (Content Pages)** — `/admin/pages` (About, Privacy, Terms, GDPR)
- **System Logs** — `/admin/logs` (fixed black-on-black text)
- **Notification 'View all' page** — Traveler + Provider (Blade view, not JSON)

### 7. Broadcast Notification Detail Pages (Commit `6b2f99d`)
- **Provider** — `provider/notifications/{id}` → `provider.notifications.show`
- **Traveler** — `traveler/notifications/{id}` → `traveler.notifications.show`
- `AdminBroadcastNotification` को `url` fix (`url('/')` → dedicated page)

### 8. Review System — PHASE 1 COMPLETE (Commit `6b2f99d`)
**Verified Live:**
- Service 46: 5.00★, 2 reviews
- Provider 177: 5.00★, 2 reviews
- Public page: 5.0 (2)

**Files Created/Modified:**
- **Migrations:**
  - `2026_11_13_071117_add_review_system_fields_to_reviews.php`
  - `2026_11_13_071158_add_rating_aggregates_to_services_and_providers.php`
- **Models:**
  - `Review.php` — polymorphic, sub_ratings, photos, verified_purchase, provider_response
  - `Booking.php` — `canBeReviewedBy()`
  - `Order.php` — `canBeReviewedBy()` + review relation
  - `Service.php` — `recalculateRating()`, `average_rating`, `reviews_count`, `rating_breakdown` (fillable + casts)
  - `Provider.php` — same ३ fields (fillable + casts)
- **Services:**
  - `ReviewEligibilityService.php` — checkBooking, checkOrder
- **Observers:**
  - `ReviewObserver.php` — saved() + deleted() → recalculate aggregates
  - Registered in `AppServiceProvider@boot` via `Review::observe()`
- **Notifications:**
  - `ReviewApproved.php` — traveler लाई
  - `ReviewRejected.php` — traveler लाई (reason सहित)
  - `AdminAlertNotification` — admin लाई (submit हुँदा — `category='review'`)
- **Controllers:**
  - `Traveler\ReviewController` — index, create, createOrder, store, storeOrder, edit, update, destroy
  - `Admin\ReviewController` — index, show, approve, reject, destroy
- **Routes (Traveler):**
  - `traveler/reviews` (index)
  - `traveler/reviews/create/{booking}`
  - `traveler/reviews/create-order/{order}`
  - `traveler/reviews/store/{booking}`
  - `traveler/reviews/store-order/{order}`
  - `traveler/reviews/{review}/edit`
  - `traveler/reviews/{review}` (PUT)
  - `traveler/reviews/{review}` (DELETE)
- **Views:**
  - `traveler/reviews/create.blade.php` (existing)
  - `traveler/reviews/edit.blade.php` (new)
  - `traveler/reviews/index.blade.php` (existing)
  - `public/services/_reviews.blade.php` (public display)

**Design Decisions (Master Approved):**
- Vertical-specific sub-ratings ✅
- Max ३ photos ✅
- Provider one-time response ✅
- Admin pre-moderation ✅
- 24h edit window / pending-only delete ✅
- 30-day review deadline ✅
- Stored rating aggregates ✅
- SOS rescue exception (no reviews) ✅
- Service + Provider २-layer rating ✅
- Dedicated public reviews pages ✅

**Verified:**
- Observer automatic update: Count 1 → 2 ✅
- Admin notification: Bell मा "New Review Submitted" आयो ✅
- Traveler notification: DB मा `ReviewApproved` ✅
- Public display: `5.0 (2)` ✅

### 9. Review System — PHASE 2 COMPLETE (Provider Panel)
**Files Created/Modified:**
- **Controllers:**
  - `Provider\ReviewController` (index, show, respond, `resolveLayout`)
  - `Provider\BookingController@show` — added `resolveLayout`
  - `Provider\HotelDashboardController@bookingsPage` — added public bookings merge
  - `Provider\NotificationController@show` — added `resolveLayout` + `showRescue`
  - `Public\ServiceController@show` — N+1 fix (provider eager load)
- **Notifications:**
  - `ReviewResponded.php` (traveler लाई)
- **Views (new):**
  - `provider/reviews/index.blade.php` — CSS variable theming
  - `provider/reviews/show.blade.php` — CSS variable + respond form
  - `provider-rescue/notifications/show.blade.php` — dedicated rescue broadcast
- **Views (modified):**
  - `provider/bookings/show.blade.php` — dynamic layout + CSS variables
  - `provider/hotel/bookings.blade.php` — public bookings section
  - `provider/notifications/show.blade.php` — CSS theme colors
  - `public/services/_reviews.blade.php` — provider response display
  - 8 × `provider*.blade.php` layouts — Reviews sidebar link
  - `provider-rescue.blade.php` — rescue broadcast URL fix
- **Routes:**
  - `provider/reviews` (index)
  - `provider/reviews/{review}` (show)
  - `provider/reviews/{review}/respond` (post)
  - `provider/rescue/notifications/{id}` (showRescue)
- **Migration:** कोई नै (existing fields use)

**Verified Live:**
- Public page: "Everest Base Camp Trek responded" ✅
- Provider dashboard: list + respond form (सबै ८ verticals) ✅
- Hotel LakeSide (186): public bookings visible ✅
- Broadcast notification: प्रत्येक vertical को accent color ✅
- Rescue: separate show page (B3) ✅
- N+1 fix: 2 reviews = 4 queries ✅

**Commits (local):**
- `a5a87b3` Admin Plan Management
- `e3897c6` Admin Broadcast
- `07bacc8` Admin CMS + Logs
- `0f5e671` Broadcast detail pages
- `6b2f99d` Review Phase 1
- `aeec30c` Review Phase 2 (Provider Panel)
- `xxxxxxx` Provider notification review_id fix
- `e974a29` Booking show + hotel public section
- `yyyyyyy` Broadcast notification CSS colors + rescue page

### 10. Invoice PDF White-label Level 1 (COMPLETE)
**Date:** 2026-11-14

**Files:**
- `app/Http/Controllers/Provider/InvoiceController.php` — `$brand` pass via InvoiceBrand
- `resources/views/provider/invoices/pdf.blade.php` — header logo+name via `$brand`, footer copyright

**Verified:**
- Provider 179 (Business + logo) → white_label tier → logo + name
- Provider 178 (Business no logo) → white_label tier → name + default logo fallback
- Subscription invoice type (booking_id NULL) → InvoiceBrand works (provider-based)
- Trek booking-pdf + Restaurant receipt-pdf → regression PASS

**White-label Definition (Level 1 — current scope):**
- Logo, business name, footer copyright in **documents only** (Invoice, Receipt, Emails)
- NOT full website rebrand, NOT custom domain, NOT email SMTP rebrand
- Level 2 (Branded Portal) + Level 3 (Custom Domain) = **Future tickets**

### 11. Restaurant Public Page & Table Reservation (COMPLETE)
**Date:** 2026-11-14
**Status:** ✅ Complete (Public Page + Emails + Provider Dashboard)

**Files Created/Modified:**
- `app/Http/Controllers/Public/RestaurantController.php` — show + reserve
- `app/Models/RestaurantReservation.php` — new model
- `database/migrations/2026_11_14_054937_create_restaurant_reservations_table.php`
- `app/Mail/ReservationCreatedMail.php`, `ReservationConfirmedMail.php`
- `app/Notifications/NewReservationNotification.php`
- `resources/views/public/restaurant/show.blade.php` — Premium UI
- `resources/views/provider/restaurant/reservations.blade.php`, `reservations-show.blade.php`
- `resources/views/emails/restaurant-reservation-created.blade.php`, `-confirmed.blade.php`
- Routes: `GET/POST restaurant/{slug}`, `POST restaurant/{slug}/reserve`, `GET/PATCH provider/restaurant/reservations`
- Sidebar Link: "Reservations" added to Restaurant layout

**Features:**
- Public Page: Menu display, Contact sidebar, Reservation form
- Email: Customer gets "Request Received" and "Confirmed" emails
- Provider: Bell notification, Dashboard list, Confirm/Cancel, View Details
- Redirect: Old Explore URL for restaurant now redirects to new page

**Verified:** 
- Test email sent successfully (SMTP OK).
- Reservation created in DB (count = 1).
- Provider Dashboard displays reservation and status updates.

**Note:** Other verticals (Hotel, Transport, ArtCraft, Individual, MoneyChanger) still use the old generic Explore page. Need to build dedicated public pages for them.

### 12. Hotel Public Page & Booking Flow (COMPLETE)
**Date:** 2026-11-14 (Night)
**Status:** ✅ Complete

**Files Created/Modified:**
- `app/Http/Controllers/Public/HotelController.php` — show + book (Public Booking)
- `app/Mail/HotelBookingCreatedMail.php`, `HotelBookingConfirmedMail.php`
- `app/Notifications/NewHotelBookingNotification.php`
- `resources/views/public/hotel/show.blade.php` — Premium UI (Amenities, Rooms, Booking Modal)
- `resources/views/emails/hotel-booking-created.blade.php`, `-confirmed.blade.php`
- `resources/views/invoices/hotel-booking-pdf.blade.php` — PDF Invoice (Logo + Contact)
- `app/Models/HotelBooking.php` — QR auto-generate on create
- `app/Http/Controllers/Provider/HotelDashboardController.php` — confirm, verifyPayment, qrCode, downloadInvoice
- `app/Http/Controllers/Traveler/DashboardV2Controller.php` — hotelBookings data
- Migration: `add_qr_code_to_hotel_bookings_table` + `add_image_path_to_hotel_rooms_table`
- Routes: `/hotel/{slug}`, `/hotel/{slug}/book`, `/hotel/bookings/{id}/confirm`, `/verify-payment`, `/qr`, `/invoice`

**Verified:**
- Public booking → Provider notification + email
- Status flow: Pending → Confirmed → Checked In → Checked Out
- Mark as Paid → Payment Status Paid
- QR Code generated + displayed
- Invoice PDF with Logo + Contact + Payment Status
- Email with Logo
- Tests: 297 pass

**Pending (VAT/Tax — see Section 13):**
- Nepal VAT compliance not yet implemented

---

## 🔴 PENDING WORK (Priority Order)

### ✅ PHASE 2 — Review System: Provider Panel (DONE — see Section 9)
**Status:** ✅ **COMPLETE** (2026-11-13 Evening)  
**Verified:** 297 pass, all 8 verticals working

**Scope:**
1. **Provider Review Dashboard** — `/provider/reviews` list
2. **Provider Review Detail** — `/provider/reviews/{id}`
3. **Provider Response** — one-time public reply (already in DB `provider_response` field)
4. **Traveler Notification** — `ReviewResponded` class (नयाँ)
5. **Provider Sidebar Link** — "Reviews" menu
6. **`ReviewResponded` notification** — traveler लाई provider reply आउँदा

**Files to Create:**
- 🆕 `app\Http\Controllers\Provider\ReviewController.php` — index, show, respond
- 🆕 `resources\views\provider\reviews\index.blade.php`
- 🆕 `resources\views\provider\reviews\show.blade.php`
- 🆕 `app\Notifications\ReviewResponded.php`
- ✏️ `routes\web.php` — provider review routes (inside `provider.active` group)
- ✏️ `resources\views\layouts\provider.blade.php` (or vertical-specific) — sidebar link

**Flow:**
- Provider → `/provider/reviews` → sees only reviews on his services
- Click → detail page → "Respond" button (only if `provider_response` is NULL)
- Submit → `provider_response` save + `provider_responded_at` set
- Notify traveler via `ReviewResponded`
- Public page shows provider response below review

**Guard:**
- Provider sees only `$review->service->provider_id === $provider->id`
- One-time response only (`provider_response` already set → edit नै, button hide)

**Test:**
- Admin approve गरिसकेको review (ID 2) मा respond गर्नुस्
- Public page मा response देखिनुपर्ने
- Traveler लाई notification

---

### 🟡 PHASE 3 — Review System: Public Display Advanced
**Status:** Not started  
**Estimate:** ~२-३ घण्टा

**Scope:**
1. **Dedicated Public Reviews Page** — `/explore/service/{slug}/reviews` (paginated AJAX)
2. **Provider Reviews Page** — `/providers/{slug}/reviews`
3. **Rating Breakdown UI** — 5★xN, 4★xN (bar chart)
4. **Photos Gallery** — अझै UI नै (DB मा `photos` छ)
5. **Helpful Votes** — `helpful_count` field थपिएको छ, UI थप्नुछ
6. **Flag/Report** — `flagged_count` field थपिएको छ, UI थप्नुछ

**Files to Create:**
- 🆕 `app\Http\Controllers\Public\ReviewController.php` (or similar)
- 🆕 `resources\views\public\reviews\index.blade.php` (dedicated)
- 🆕 `resources\views\public\reviews\partials\_rating-breakdown.blade.php`
- ✏️ `resources\views\public\services\_reviews.blade.php` (enhanced)

---

### 🟢 PHASE 4 — Review System: Polish
**Status:** Not started  
**Estimate:** ~२-३ घण्टा

1. Fraud detection (admin)
2. Analytics dashboard
3. Weekly email digest (top reviews)

---

### 🔴 FINAL TASK — Admin Notification System (Full)
**Status:** Not started  
**Priority:** LAST (तपाईंको plan)  
**Estimate:** ~३-४ घण्टा

**Currently exists:**
- Bell (database channel) ✅
- Email (critical events — SOS/Payment/Verification) ✅
- Broadcast (Provider + Traveler) ✅
- Review submit notification ✅

**Missing:**
1. **Admin notification settings** — per-category toggles
2. **User preferences** — user can turn off email types
3. **Scheduled digest** — daily/weekly summary email
4. **Admin → User custom notifications** — send targeted alerts
5. **Notification templates** — customizable per category

---

### 🟣 FUTURE TICKETS (Documented — Not Started)

| Ticket | Scope | Priority |
|---|---|---|
| **CUSTOM-DOMAIN-FEATURE-01** | White-label Level 3 — Custom domain (`provider.com`), DNS, SSL, tenant resolution, branded portal | Future (Enterprise+) |
| **BOOKING-WELCOME-EMAIL-FIX-01** | `BookingWelcomeMail` dispatch missing in `Public\BookingController@store` — email कहिल्यै जाँदैन। Dispatch, queue, delivery test चाहियो | High (customer experience) |
| **INVOICES-PDF-DEAD-CODE-01** | `resources/views/invoices/pdf.blade.php` = 100% unreferenced (कोई controller नै) — cleanup ticket | Low |
| **VERTICALS-PDF-GENERATION-01** | Hotel, Transport, ArtCraft, Individual, MoneyChanger — कोई invoice/receipt PDF generation feature नै (भविष्य) | Medium |
| **EMAIL-BRANDING-AUDIT-01** | २२ mail classes — कुन-कुन `$brand` use गर्छ? कुन-कुन hardcoded? Comprehensive audit | Medium |
| **PRICING-WHITE-LABEL-CLARIFY-01** | Pricing page मा "White-label" → "Branded Documents (Level 1)" — scope clarification | Low |
| **RESTAURANT-ADD-ITEMS-01** | Restaurant order मा थप items (existing order मा append) — Master approved Option B, testing पछि | Medium |
| **NEPAL-VAT-INVOICE-01** | **Nepal VAT (13%) Compliance — All Providers** — नेपाल सरकारको ईनभ्वाइस मापदण्ड अनुसार VAT breakdown, PAN/VAT number, sequential invoice numbering, fiscal year format | 🔴 **HIGH** |
| **PROVIDER-PAN-VAT-01** | Provider profile मा PAN/VAT number field थप्ने (नयाँ registration र profile edit मा) | 🔴 HIGH |
| **INVOICE-FORMAT-NEPAL-01** | नेपाल सरकारको prescribed invoice format (IRD मापदण्ड) — सबै verticals को लागि | 🔴 HIGH |
| **VAT-THRESHOLD-CONFIG-01** | VAT threshold config (hotel/restaurant 13%, trek/tour may vary, threshold amount) | 🟡 MEDIUM |

### 13. VAT/Tax Nepal Compliance — Discussion Needed

**Current Gap (Rule A verified):**
- Restaurant Receipt: कोई VAT breakdown नै (Total मात्र)
- Hotel Invoice: कोई VAT breakdown नै (Total मात्र, Section 12 मा थपिएको Invoice पनि)
- Provider Profile: कोई **PAN/VAT number** field नै
- Invoice Number Format: `HTL-2026-0032` — Nepal fiscal year (Shrawan-Ashad) अनुसार नै
- Nepal Government (IRD) prescribed format: **follow गरिएको छैन**

**Nepal VAT Rules (IRC 2058):**
1. **13% VAT** — Hotel, Restaurant, Catering (threshold: Rs. 20 lakh annual turnover)
2. **PAN/VAT Number** — Invoice मा अनिवार्य (9-digit PAN, VAT registered: 9-digit + suffix)
3. **Sequential Invoice Number** — Fiscal year अनुसार (Shrawan 1 — Ashad 31)
4. **Tax Invoice Format** — IRD prescribed (provider name, PAN, invoice no, date, description, taxable amount, VAT amount, total)
5. **Fiscal Year Format** — 2081/82 (not 2026)

**Master Decision Needed:**
- के यो Phase मा implement गर्ने? कि Future (production launch अघि)?
- कति providers ले VAT threshold cross गरेका छन्? (currently सबै below threshold हुनसक्छन्)
- Simple breakdown (13% flat) कि full IRD format?

**Recommendation:** Production launch अघि **अनिवार्य** — नेपालमा VAT invoice बिना service provide गर्न गैरकानूनी हुनसक्छ।

### ❌ OUT OF SCOPE (No Need)
1. **Products/Shop Moderation** — Admin ले provider को product हेर्नु पर्दैन
2. **Quotations (AI Planner)** — Provider को काम
3. **Notification Queue (7.6s delay)** — `sync` चल्न दिने (तपाईंको निर्णय)

---

## 🗂️ KEY FILES REFERENCE

### Routes
- `routes\web.php` — सबै routes (line ~५००+ मा traveler reviews, ~४००+ मा admin, ~२००+ मा provider)

### Controllers (Review System)
- `app\Http\Controllers\Traveler\ReviewController.php` — create/store (Booking + Order), edit, update, destroy
- `app\Http\Controllers\Admin\ReviewController.php` — index, show, approve, reject, destroy
- `app\Http\Controllers\Traveler\NotificationController.php` — index (JSON + Blade), show, markAsRead, markAllAsRead
- `app\Http\Controllers\Provider\NotificationController.php` — index (JSON + Blade), show, markAsRead, markAllAsRead
- `app\Http\Controllers\Admin\NotificationController.php` — index, markAsRead, markAllAsRead

### Models (Review System)
- `app\Models\Review.php` — polymorphic, sub_ratings, photos, verified_purchase, provider_response; `#[ObservedBy([ReviewObserver::class])]`
- `app\Models\Booking.php` — `review()` hasOne, `canBeReviewedBy()`
- `app\Models\Order.php` — `review()` hasOne, `canBeReviewedBy()`
- `app\Models\Service.php` — `reviews()`, `approvedReviews()`, `recalculateRating()`, `averageRating()`, `ratingsCount()`; `$fillable` मा ३ rating fields
- `app\Models\Provider.php` — `approvedReviews()`, `recalculateRating()`; `$fillable` मा ३ rating fields

### Services/Observers/Notifications
- `app\Services\ReviewEligibilityService.php` — checkBooking, checkOrder
- `app\Observers\ReviewObserver.php` — saved(), deleted() → recalculate
- `app\Notifications\ReviewApproved.php`, `ReviewRejected.php`, `NewReviewReceived.php`, `AdminBroadcastNotification.php`
- `app\Providers\AppServiceProvider.php` — `Review::observe()` register (boot method मा `Route::observe(RouteObserver::class)` पछि)

### Views
- `resources\views\traveler\reviews\{create,edit,index}.blade.php`
- `resources\views\traveler\notifications\{index,show}.blade.php`
- `resources\views\provider\notifications\show.blade.php`
- `resources\views\admin\reviews\{index,show}.blade.php`
- `resources\views\public\services\_reviews.blade.php`
- `resources\views\layouts\traveler-v2.blade.php` — bell JS, notification dropdown
- `resources\views\layouts\provider.blade.php` — bell JS

### DB Tables
- `reviews` — polymorphic, status, verified_purchase, provider_response, sub_ratings, photos, etc.
- `services` — `average_rating`, `reviews_count`, `rating_breakdown` (added)
- `providers` — `average_rating`, `reviews_count`, `rating_breakdown` (added)

---

## 🎨 DESIGN PATTERNS (Follow these)

### Review Submission Flow
Traveler → Booking/Order Show → "Write Review"
↓
Traveler\ReviewController@create (eligibility check)
↓
Form (rating, title, comment, sub_ratings, photos)
↓
Store → Review::create (status='pending', verified_purchase=true)
↓
Admin notify (AdminAlertNotification, category='review')

text

### Admin Approval Flow
Admin@approve → status='approved' → save()
↓
ReviewObserver@saved triggers
↓
Service::recalculateRating() (approved only)
Provider::recalculateRating() (weighted aggregate)
↓
Traveler notify (ReviewApproved)
↓
Public display (average_rating, count)

text

### Response Pattern (Phase 2 को लागि)
```php
// One-time response
$review->provider_response = $request->input('response');
$review->provider_responded_at = now();
$review->save();
$review->user->notify(new ReviewResponded($review));
Rating Calculation
php
$reviews = Review::where('service_id', $id)
    ->where('status', 'approved')
    ->get(['rating']);
$count = $reviews->count();
$avg = $count > 0 ? round($reviews->avg('rating'), 2) : 0.00;
$breakdown = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
foreach ($reviews as $r) {
    $breakdown[(int) $r->rating]++;
}
🧪 TESTING WORKFLOW
After EVERY change:
cmd
php artisan optimize:clear
php artisan test
Expected: 297 passed, 1 skipped

New Feature Test:
Feature file बनाउनुस् tests\Feature\Review\{Feature}Test.php

297 existing tests intact राख्नुस्

New tests थपेपछि total बढ्न सक्छ

Manual Browser Test (Review system):
Submit: Traveler → completed booking → write review

Admin: /admin/reviews → approve

Public: /explore/service/{slug} → review देखिनुपर्ने

DB: aggregates update verify

🚨 KNOWN ISSUES / GOTCHAS
1. $fillable में add गर्न नबिर्सनुस्
नयाँ column update गर्दा $fillable मा थप्नुस् — नत्र $this->update() silently ignore गर्छ। (Phase 1 मा यही bug थियो: average_rating, reviews_count, rating_breakdown)

2. Observer register गर्न नबिर्सनुस्
ReviewObserver मा #[ObservedBy] attribute + AppServiceProvider@boot मा Review::observe() — दुवै चाहिन्छ।

3. reviews() relation मा scope
Service::reviews() मा where('status','approved') छ — त्यसैले approvedReviews() मा double filter हुनसक्छ। (Phase 1 मा relation मा scope थियो कि थिएन verify गरिएको)

4. Tinker CMD escaping
CMD मा $ → \$ लेख्नुस्, \| काम गर्दैन। Complex command को लागि findstr मा /C:"pattern" प्रयोग गर्नुस् (backslash pipe छैन)।

5. Windows findstr मा \| ignore
Multiple patterns को लागि अलग findstr चलाउनुस् वा powershell -Command "Select-String -Pattern 'a|b'" प्रयोग गर्नुस्।

6. .bat files commit नगर्नुस्
Audit scripts debug को लागि मात्र — production code नै।

7. Migration ID mismatch
New migration create गर्दा php artisan make:migration बाट generate हुने file नै edit गर्नुस्। manual filename नबनाउनुस् (Phase 1 मा YYYY_MM_DD_HHMMSS_... file बनाइयो — delete गर्यो)।

📋 NEXT STEPS — Priority
Master GO प्राप्त ✅ — 3 commits push भइरहेको छ (Hotel Public Page + Booking Flow + Complete)
Restaurant E2E test — DONE (Public page, Email, Reservation, Provider dashboard all verified)
Hotel E2E test — DONE (Public page, Email, Reservation, QR, Invoice, Payment verified)
Next Vertical: Transport Public Page using same pattern.

Phase 2 सुरु गर्ने — Provider Review Panel

Phase 3 — Public advanced display

Phase 4 — Polish

Admin Notification System (Full) — अन्तिम

Push — सबै काम सिध्याएपछि, Master GO पछि

## 🟣 PENDING TASKS — A-Z COMPLETE LIST

### 🔴 HIGH PRIORITY (Immediate)

| # | Task | Source | Est. |
|---|---|---|---|
| 1 | **Push to Production** — ५+ local commits pending (Master GO पछि) | भोलिको to-do #1 | — |
| 2 | **Notification Queue (7.6s delay)** — सबै notification class मा `implements ShouldQueue` | भोलिको to-do #2 | 1-2h |
| 3 | **Rescue Quick Accept (1-Click)** — Dashboard card मै Accept button | भोलिको to-do #3 | 1-2h |

### 🟡 MEDIUM PRIORITY

| # | Task | Source | Est. |
|---|---|---|---|
| 4 | **Fleet & Staff CRUD Verify** — Helicopter, गाडी, staff add/remove test | भोलिको to-do #4 | 30m |
| 5 | **Reports & Analytics Verify** — Revenue, Charts सही छन्? | भोलिको to-do #5 | 30m |
| 6 | **Provider Manual Booking Notification** — Provider आफैंले booking बनाउँदा admin notify? | Earlier discussion | 30m |
| 7 | **Per-Vertical CRUD Deep Test** — Hotel, Restaurant, Transport, ArtCraft, Individual, MoneyChanger (Add/Edit/Delete UI) | भोलिको to-do #6 | 1h |

### 🟢 TESTING PHASE (तपाईंको Plan — Order important!)

| # | Task | Note | Est. |
|---|---|---|---|
| 8 | **Restaurant E2E** — Order → email verify (cart → checkout → delivered) | Hotel पछि | 15m |
| 9 | **Transport E2E** — Booking → email verify (Public Booking flow) | — | 15m |
| 10 | **ArtCraft E2E** — Order → email verify (cart flow) | — | 15m |
| 11 | **Individual Guide E2E** — Booking → email verify | — | 15m |
| 12 | **MoneyChanger E2E** — Order → email verify (**सबैभन्दा अन्तिम**) | — | 15m |

### 🟣 COSMETIC / LOW PRIORITY

| # | Task | Source | Est. |
|---|---|---|---|
| 13 | **Ad Title Duplicate Fix** — Rescue ad मा title = description (UI bug) | Earlier observation | 15m |
| 14 | **Provider Notification 'View all' page** — same JSON bug fix करना (Traveler मा fix भयो, Provider मा verify गर्नुछ) | Pending assumption | 30m |
| 15 | **Active Districts Filter** (if pending) | Phase 3 (earlier) | 1h |
| 16 | **Detail Page** (Service detail enhancement) | Phase 3 (earlier) | 1h |

---

## 🟢 FUTURE FEATURES (Not Yet Started — For Discussion)

### 🔵 1. Expedition Service Vertical (NEW — Discuss करना)

**What is it?**
A new vertical for **multi-day expedition packages** (Mountaineering, Peak Climbing, Long Treks like Annapurna Circuit, Manaslu, Mustang, etc.) — beyond standard Trek/Tour.

**Why separate from Trek?**
- Trek = standard routes (EBC, ABC) with fixed itinerary
- Expedition = **technical climbing** (Island Peak, Mera Peak, Lobuche East), **expedition permits**, **summit certificates**, **climbing gear rental**, **rescue insurance mandatory**, **Sherpa climbing guides**

**Suggested Features:**
- Expedition listing (peak, grade, difficulty, technicality)
- Required climbing gear checklist
- Insurance mandatory verification
- Sherpa climbing guide assignment
- Expedition permits (different from TIMS/ACAP)
- Summit certificate (auto-issue)
- Expedition-specific review (technicality, safety, gear quality)

**Admin scope:** Approve expedition listings, verify permits, monitor safety

**Decision needed:** Master (discuss करना)

---

### 🟠 2. Blockchain-Based Digital Permits (Future — Website मा "Coming 2026")

**Already on marketing website — mentioned as "Coming 2026"**

**Scope (Conceptual):**
- **TIMS & ACAP permits** as NFTs / blockchain credentials
- Immutable, verifiable, instant
- Reduces corruption & delays
- Traveler receives digital permit via blockchain
- Checkpost QR scan verifies on-chain
- Agency cannot fake permits
- Rescue teams access permit holder data (privacy-controlled)

**Possible Implementation Options:**
- **Polygon / Solana** (low gas fee)
- **Private consortium chain** (Nepal tourism board partnership)
- **Decentralized identity (DID)** standard

**Decision needed:** Master (is it scope? when?)

---

### 🟡 3. Advanced Review Features (Phase 3/4 — discussed)

- Photos in reviews (DB ready)
- Helpful votes (DB ready)
- Flag/report (DB ready)
- Dedicated public reviews page
- Rating breakdown charts
- Provider response visibility
- Review email digest (weekly)

---

### 🟢 4. Other Future Ideas (Not Yet Discussed in Detail)

- **Live Chat / Messaging** — Traveler ↔ Provider direct chat
- **Multi-currency Wallet** — Save & pay in multiple currencies
- **Loyalty / Rewards Program** — Points for repeat bookings
- **AI-Powered Dynamic Pricing** — Auto-adjust based on demand
- **Video Content Moderation** — AI scan of video ads
- **Mobile PWA Push Notifications** — Real-time alerts on mobile
- **Trip Replay Social Sharing** — Viral growth loop
- **Referral Program** — Agency & traveler referrals
- **Expedition Insurance Integration** — Automated policy
- **Blockchain Permits** (above)
- **B2B API for Agencies** — White-label integration

---

## 🎯 CURRENT MASTER DIRECTIVE

> "सबै काम A-Z complete गरेपछि मात्र one time push। Admin Notification System = सबैभन्दा अन्तिम काम। Rule A, B, C, D, E पालना गर्नुस्। कोई hasty नै।"

---

## 🚦 WORK ORDER — Priority Sequence
Phase 1 (DONE): ✅ Review System Foundation
↓
Phase 2 (DONE): ✅ Review System — Provider Panel (respond)
↓
Notification Fixes (DONE): ✅ Broadcast layouts + URLs + CSS colors
↓
Booking Fixes (DONE): ✅ Dynamic layout + hotel public section
↓
PDF White-label Level 1 (DONE): ✅ Provider invoice + receipt branding
↓
Sidebar Logo Fix (DONE): ✅ 7 layouts conditional provider logo
↓
Profile/Subscription hasFeature (DONE): ✅ Slug normalization + white-label alias
↓
Testing Phase (NEXT): 🟢 Restaurant → Transport → ArtCraft → Individual → MoneyChanger
↓
Future Tickets: 🟣 Custom Domain, Welcome Email Fix, Add Items, Email Audit
↓
Phase 3: 🟢 Review System — Public Advanced (photos, votes)
↓
Phase 4: 🟣 Review System — Polish (analytics, digest)
↓
Testing Phase: 🟢 (Restaurant DONE, Hotel DONE) → Transport → ArtCraft → Individual → MoneyChanger
↓
Medium Priority: 🟡 Fleet CRUD, Reports, Per-Vertical CRUD
↓
High Priority: 🔴 Quick Accept, Queue 7.6s, Provider Manual Notify
↓
Low Priority: 🟣 Ad duplicate, Provider "View all", Active Districts, Detail Page
↓
FINAL TASK: 🔴 Admin Notification System (Full)
↓
Discuss: 🔵 Expedition Service | Blockchain Permits | Future Features
↓
Master GO: 🚀 Single Push to Production

text

---

## 📞 HANDOVER CONTACTS / CREDENTIALS

| Role | Credential |
|---|---|
| **Admin** | `parasharregmi@gmail.com` (super_admin) |
| **Test Traveler** | `hostelhub.nep@gmail.com` (User ID 299) |
| **Test Provider** | `anjuregmimesh@gmail.com` (Anju Regmi — Provider ID 177) |
| **Test Service** | Service ID 46 (Everest Base Camp Trek — 14 Days) |
| **Test Hotel Provider** | `hotel@test.com` (Bikash Gurung) |
| **Test Restaurant Provider** | `restaurant@test.com` (pending setup) |

---

## 📝 FINAL NOTES FOR NEW ASSISTANT

1. **यो doc पूरै पढ्नुस्** — कोई guess नै।
2. **Rule A** — कोई code लेख्नु अघि actual file पढ्नुस्।
3. **Review System Phase 1** — 100% verified (297 tests, 2 reviews live)।
4. **NEXT — Phase 2** — Provider Panel (respond feature)।
5. **Push** — सबै सिध्याएपछि, Master GO पछि एकैचोटि।
6. **Admin Notification System** — अन्तिम task।
7. **Future Features (Expedition, Blockchain)** — Master सँग discuss गर्नुस्।

---

**END OF HANDOVER — कोई confusion भए user (दाइ) लाई सोध्नुस्, guess नगर्नुस्। 🙏**

**🇳🇵 TravelAI Nepal — Verified Trust & Reputation System निर्माण गर्दै।**
