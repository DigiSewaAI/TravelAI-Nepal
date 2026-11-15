# 🎯 TravelAI Nepal — MASTER HANDOVER DOCUMENT
**Last Updated:** 2026-11-15 (Transport Phase C — Chunks 1-4 Complete, Payment + Receipt Flow Live)  
**Current Commit:** `TBD-after-chunk4` (Transport Phase C Chunks 1-4 — 15 commits ahead of origin/main)  
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
- **HEAD:** `4339259` (Chunks 2+3) + pending Chunk 4 commit
- **Branch:** main
- **Ahead of origin/main by:** 15 commits (after Chunk 4 commit)
- **Tests:** 297 passed, 1 skipped (1970 assertions)
- **Production:** 🟡 15 commits pending push (after Chunk 5-6 + remaining verticals + Admin Notification complete)

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

### 🟢 PHASE 4 — Review System: Polish
**Status:** Not started  
**Estimate:** ~२-३ घण्टा

1. Fraud detection (admin)
2. Analytics dashboard
3. Weekly email digest (top reviews)

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

### 14. Transport Public Page + Booking Flow — Phase 1 (COMPLETE)
**Date:** 2026-11-14/15
**Status:** ✅ Complete (Phase 1 — bug fixes + emails + payment + images)

**Files Created/Modified:**
- `app/Http/Controllers/Public/TransportController.php` — show + book
- `app/Mail/TransportBookingCreatedMail.php`, `TransportBookingConfirmedMail.php`
- `app/Notifications/NewTransportBookingNotification.php`
- `resources/views/public/transport/show.blade.php` — Premium UI (sky-blue theme)
- `resources/views/emails/transport-booking-created.blade.php`, `-confirmed.blade.php`
- `resources/views/provider/transport/bookings/show.blade.php` — status + payment + Verify buttons
- Migration: `add_image_path_to_transport_vehicles_table`, `add_booking_number_to_transport_bookings_table`, `add_payment_fields_to_transport_bookings_table`, `add_pickup_point_to_transport_details_table`
- Routes: `/transport/{slug}`, `/transport/{slug}/book`, `provider/transport/bookings/{id}/status`, `/verify-payment`
- i18n: 5 locales (`public_transport_*`, `price_on_request`, `vehicle_image`, `update_status`, `confirm_booking`, `cancel_booking`)

**Features:**
- Public page: hero + vehicles + routes + booking form (5 locales, SEO, cover placeholder)
- Booking: sequential `TR-YYYY-NNNN` ref, customer email (with logo + user-journey CTA), provider bell notification
- Provider dashboard: bell notification, booking show with payment badge + Verify Payment + Confirm/Cancel buttons
- Email: hybrid logo (≤50KB base64, >50KB URL) — Gmail clipping fixed
- Edit Vehicle: image upload + preview (Add मा पहिले नै)
- Payment: `payment_status`, `payment_method`, `payment_ref`, `payment_proof`, `paid_at`
- Amount 0 → "Price on request" fallback

**Verified:**
- Public booking → customer email (logo + CTA + no clipping) + provider bell
- Confirm → confirmed email (green theme, logo, dashboard CTA)
- Payment verify → badge paid + Paid At timestamp
- Edit Vehicle image → public page updates
- Tests: 297 pass, 1 skip

**Known Limitations (Phase B मा fix — ✅ ALL RESOLVED in B1-B5):**
- ~~Amount सधैं Rs 0.00~~ → ✅ per-trip pricing (B1-B4)
- ~~Vehicle = Route~~ → ✅ multi-route per vehicle (B1-B4)
- ~~Passenger count field नै~~ → ✅ seat inventory + passenger_count (B1-B5)
- ~~Pickup point service-level मात्र~~ → ✅ trip-level + snapshot (B1-B5)
- ~~कोई Scheduled Trips entity नै~~ → ✅ `transport_trips` table (B1)

### 15. Transport Phase B — Full v2 Architecture (✅ DONE — B1-B5, 2026-11-15)
**Status:** ✅ **Complete** (B1-B5, 10 commits ahead of origin/main)
**Commits:** `59f4aad` (B1) → `a9f57dc` (B2) → `f72545f` (B3) → `4865e69` (B4.2) → `73bf131` + `1221566` (B5)

**B1 — Data Model (DONE `59f4aad`):**
- `transport_routes` table + `TransportRoute` model
- `transport_trips` table + `TransportTrip` model
- `transport_bookings` extend: trip_id, passenger_count, booking_type, pickup_point, price_per_unit, total_amount
- Legacy 15 bookings preserved (`trip_id = NULL`)

**B2 — Provider Routes CRUD (DONE `a9f57dc`):**
- `routesPage` rewrite (visual cards)
- `storeRoute`, `updateRoute`, `destroyRoute` + routes
- i18n via `messages.pickup_point` etc.

**B3 — Provider Trips CRUD (DONE `f72545f`):**
- `tripsPage` + `storeTrip` + `updateTrip` + `destroyTrip`
- Tab nav (Routes | Daily Departures) + sidebar merge
- Conflict guard (same vehicle + date + time)
- Seat auto-fill from vehicle capacity

**B4 — Public Search Flow (DONE `73bf131`/`1221566`):**
- Search bar (date + from + to) → results
- Booking modal (trip-based, passengers, type, pickup)
- Seat inventory (`lockForUpdate` + `decrement`)
- Double-booking guard
- Price snapshot (`price_per_unit` + `total_amount`)

**B4.2 — Hotfix (DONE `4865e69`):**
- `TransportBooking` fillable fix (`trip_id` + Phase B fields — silent fail fix)
- Seat restore on cancel/delete (`destroyBooking` + `updateBookingStatus` + `\DB::transaction`)

**B5 — Booking Display + Email Upgrade (DONE `73bf131` + `1221566`):**
- Email: passenger count + type + pickup + price breakdown
- Show page: passengers + type + pickup + price breakdown + Restore button + Delete button + Legacy badge
- Index page: Legacy badge + Price on request + Delete button
- i18n keys (5 locales)

**Verified:**
- Public search → trip results → booking with seat decrement (4 → 2) ✅
- Cancel → seats restore (2 → 4) ✅
- Restore → seats decrement (4 → 2) ✅
- Delete → seats restore + booking deleted ✅
- Email (created + confirmed) — all fields + price breakdown + no Gmail clipping ✅
- Legacy bookings — "Legacy" badge + "Price on request" + no seat impact ✅
- Tests: 297 pass, 1 skip ✅

**Original Architecture (Master approved Option C):**

**Architecture (ChatGPT-sourced, Master approved Option C):**
Provider
├── Fleet Vehicles (existing — image, capacity, status)
├── Routes (existing — Route, RouteCost, RouteSegment)
├── Scheduled Trips ⬅ NEW: vehicle_id + route_id + date + departure_time + duration + seats_total + seats_available + price_per_seat + price_per_vehicle + driver_id + status
└── Bookings (extend: trip_id, passenger_count, booking_type, pickup_point, price_snapshot)


**Core Principles:**
1. **Vehicle ≠ Route** — एक bus ले धेरै routes चलाउन सक्ने
2. **Pricing route/departure-specific** — same bus, different routes = different fares
3. **Seat inventory** — 30-seat bus, 3 booked = 27 available; double-booking prevention
4. **Immutable booking price snapshot** — provider price बदले पनि पुरानो booking स्थिर
5. **Driver per trip** (not per vehicle)
6. **Service-level defaults only** — description, cancellation policy (`transport_details`)
7. **Pickup: provider default + trip-level + customer request** (provider confirm)
8. **UX principle (Master directive):** Professional but user-friendly — "herne bittikai bujhne"
   - Provider: visual cards, plain language ("When does your bus leave? Where? How much per seat?")
   - Traveler: progressive (date → trips → seat count → pickup → total)
   - No technical jargon, no confusing dropdowns

**Implementation Batches:**
- 4A.1 — Migration: `transport_trips` table + `transport_bookings.trip_id` (nullable) + snapshot columns
- 4A.2 — Provider: Routes CRUD + Trips CRUD (visual, user-friendly)
- 4B — Public: date → trips list → seat select → pickup → amount auto-calc
- 4C — Booking display + email (trip details, seat count, pickup point)
- 4D — Seat inventory + double-booking (pessimistic lock) + driver assignment
- 4E — Legacy 15 bookings migration (trip_id nullable, कोई data loss नै)
- 4F — Logo auto-resize upload handler
- 4G — Full E2E + commit each batch

**Rule C Compliance:**
- पहिले read-only audit (Routes, RouteCosts, existing bookings, Reviews/Claims references)
- कोई destructive migration नै
- Legacy bookings `trip_id = NULL` (preserved)
- Rollback plan प्रत्येक migration संगै

**पछि (Phase B पछि):**
- ArtCraft, Individual, MoneyChanger — Public Pages
- Admin Notification System (final)
- Push (एकैचोटि)

### 16. Transport Phase C — Payments & Cancellation (Master APPROVED 2026-11-15, C/A/B/A)
**Status:** ✅ **ALL 6 CHUNKS COMPLETE** (2026-11-15)
**Commit:** `5defb0a` (Chunks 5 complete) + Chunk 6 polish

**Chunks 1-4 (✅ COMPLETE):**

**Chunk 1 (`ece6a03` + `a96204a` + placeholder polish `ece6a03`):** Payment Methods + Model
- Migration: `providers.transport_payment_model` (advance/pay_on_arrival/both, default both)
- Migration: `provider_payment_methods.is_primary` (index guard)
- `Provider::hasFeature()` plan gate
- Controller: 6 methods (store/update/destroy/toggle/setPrimary/updatePaymentModel) + `isTransportProvider()` helper
- Transaction locking (parent provider row) + 5 safety rules:
  1. एक मात्र primary per provider
  2. Atomic switch (transaction + lock)
  3. Inactive cannot be primary
  4. Delete primary → auto-fallback (next active) / कोई active नै → zero primary
  5. A1 toggle: reject disable of primary
- Blade: Payment Model radio (top) + is_primary checkbox + Primary badge + Set Primary button
- **Type-based field visibility:** eSewa/Khalti/PayPal/Wise → Identifier only; Bank/Intl Bank → Account Name + Number + Bank; Cash → कोई account; Intl Bank → + SWIFT
- **Domestic/Intl dropdown filter** (optgroup data-pm-group) — add domestic → only domestic options
- **Dynamic placeholders + help texts** per type
- i18n: 17 new keys

**Chunk 2+3 (`4339259`):** Public payment modal + Traveler upload + Provider verify/reject
- Migration: `transport_bookings.payment_model` (advance/pay_on_arrival) + `payment_submitted_at`
- Public form: Payment Option radio (advance vs PAY) — provider model 'both' मा; PAY मा note only
- `Public\TransportController@book`: `payment_model` snapshot + advance validation (≥1 active method)
- Public payment modal auto-opens on advance booking (payment methods + QR + proof upload)
- `Public\TransportController@uploadPublicProof`: email guard, resubmit block, provider notification
- Traveler controller: `index`, `show`, `uploadProof` (email match guard)
- Traveler views: `/traveler/transport/bookings` + `/traveler/transport/bookings/{id}` (payment methods display + upload form)
- Email CTA: "Complete Payment →" (advance pending मा)
- Provider verify: `verifyPaymentProof` (auto-confirm + paid + email) + `rejectPaymentProof` (reason + traveler notify)
- Provider show page: Payment section (Model/Status/Method/Ref/Submitted At/Verified At + Proof screenshot + Verify/Reject buttons + Reject form)
- Notifications: `PaymentProofSubmittedNotification` (provider), `PaymentRejectedNotification` (traveler)
- Routes: 3 traveler + 2 provider verify/reject
- i18n: 13 keys + `transport_booking` missing key fix (५ locale)
- Claim show fix: `$booking->vehicle->vehicle_number ?? 'N/A'` (name/type नै)

**Chunk 4 (pending commit `+ pending`):** Receipt PDF + Invoice integration + Email attachment
- `app/Services/TransportReceiptService.php` (NEW):
  - `create(TransportBooking)` — idempotent invoice generation (`TR-RCP-YYYY-NNNN`, `tax=0`, VAT-free, metadata JSON)
  - `buildBrandForPdf($provider)` — logo base64 (कोई size limit, DomPDF-safe) + plan gate `hasFeature('custom_logo')`
  - `buildLogoForEmail($provider)` — hybrid (<=50KB base64, >50KB URL) + plan gate
- `invoices.transport-receipt.blade.php` (NEW): white-label header (logo + name + contact), customer + booking ref, trip details, amount table (कोई tax row), payment info (method + ref + paid_at), **legal notice** ("Payment Receipt, not Tax Invoice"), footer
- Provider `downloadReceipt` + Traveler `downloadReceipt` (service call, कोई inline brand build)
- `TransportBookingConfirmedMail`: receipt PDF attachment (DomPDF), nullable `$receipt` param
- `TransportBookingCreatedMail` + Confirmed: logo via service (plan-gated)
- Provider/traveler show pages: "📄 Download Receipt" button (paid state मा)
- Routes: 2 (provider.receipt + traveler.receipt)
- **Invoice table reuse** — कोई migration नै (metadata JSON मा `transport_booking_id` + `provider_vat_status: non_registered` + `receipt_type: payment_receipt`)
- Provider invoices page: existing `/provider/invoices` मा transport receipts (TR-RCP-*) देखिने (verified — 2 receipts Paid)
- Email attachment: Gmail "One attachment" (scanned)

**Chunk 5 (`5defb0a`):** Traveler Cancellation + Provider Approve/Reject/Refund
- Migration: `transport_cancellation_requests` (unique booking_id, reason, estimated_refund, status, refund_status)
- Migration: `transport_bookings.cancellation_status` + `cancelled_at`
- `TransportCancellationRequest` model + relation
- Traveler: `showCancelForm` (policy display + hours + refund calculator), `requestCancel`
- Policy: default 24h/12h/0 refund rules + provider override
- Provider: `cancellationsPage`, `approveCancellation` (seat restore + refund_status='pending'), `rejectCancellation` (reason), `markRefunded` (manual)
- Notifications: `CancellationRequestedNotification` (provider), `CancellationApprovedNotification` (traveler), `CancellationRejectedNotification` (traveler)
- Views: `cancel-request.blade.php`, `provider/transport/cancellations/index.blade.php`, show page cancellation state
- Routes: 2 traveler + 4 provider
- **My Bookings integration:** `UnifiedBookingController` + `my-bookings/index.blade.php` — Transport tab + cards + counts (🚐 Transport (N))
- **Fix:** Double ✅ (flash), "Payment Verified — Booking was cancelled" (cancelled state)

**Chunks 6 (Final — polish):**
- Hours display: `22h 9m` (float → readable)
- Full E2E: advance → PAY → cancel → reject cycle
- Doc update + Transport CLOSE

**Chunks 1-4 (✅ COMPLETE — see below for details):**
- Migration: `transport_cancellation_requests` table (booking_id, requested_at, reason, refund_amount, status, provider_response)
- Traveler: cancel request form (policy display + estimated refund)
- Provider: approve/reject request + refund status track (कोई gateway — manual)
- On approve: seat restore + booking cancelled + refund_status = 'pending'
- On refund: provider marks refunded (audit trail)
- Notifications: traveler (approve/reject), provider (new request)
- Reuse: `transport_details.cancellation_policy` field (Master decision B — provider-controlled)

**Chunk 6 — Full E2E + Polish (1-2h):**
- Full E2E: public → advance → upload → verify → receipt → email (सबै)
- Reject cycle E2E
- PAY cycle E2E
- Regression 297 pass
- Doc update + commit + (NO PUSH)

**Master Decisions (Final):**

| # | Decision | Choice | Reason |
|---|---|---|---|
| 1 | Payment Model | **C (Both)** | Advance + Pay on Arrival दुवै — provider choose |
| 2 | Scope | **A (All 6 chunks, phased)** | Complete system, one chunk at a time |
| 3 | Traveler Cancel | **B (Request → Provider Approval)** | Nepal business reality — auto refund risky |
| 4 | Receipt Format | **A (PDF + Email + Download)** | Legal compliance + professional |

**Infrastructure Audit (Rule A — Verified):**

| Component | State | Reuse |
|---|---|---|
| `provider_payment_methods` table | ✅ EXISTS (16 cols, 0 rows) | ✅ As-is + extend |
| `ProviderPaymentMethod` model | ✅ Full (fillable, scope, relations) | ✅ Extend fillable |
| `Provider\PaymentMethodController` | ✅ Full (5 methods, vertical layout) | ✅ Extend validated() |
| Routes `provider.settings.payment-methods.*` | ✅ 5 routes | ✅ As-is |
| Views `payment-methods.blade.php` + premium | ✅ EXISTS | ✅ Extend form |
| i18n `pm_*` keys | ✅ 29 keys (line 574-609) | ✅ Extend |
| `invoices` table + `InvoiceController` + DomPDF | ✅ EXISTS (2 rows) | ✅ Reuse via `metadata` JSON |
| `Provider\HotelDashboardController@verifyPayment` | ✅ Pattern | ✅ Reference |

**⚠️ Critical:** कोई duplicate payment system नै। Existing infrastructure extend मात्र।

**Chunk 1 — Payment Methods Setup (Pending approval):**
- Payment Model: **provider-level** (Master directive: not per-method — conflicting config risk)
  - New column `providers.transport_payment_model` enum (`advance`/`pay_on_arrival`/`both`, default `both`)
- `is_primary` safety rules (५):
  1. एक मात्र primary per provider
  2. Switch गर्दा पुरानो primary auto-false
  3. Disabled method primary हुन नसक्ने
  4. Delete गर्दा safe fallback (auto-assign अन्य active method)
  5. सबै vertical shared behavior
- Files: +2 migrations, +2 model fillable, controller validated + is_primary logic, blade radio + checkbox, ~8 i18n keys
- **Estimate:** 1.5-2 hours

**Chunk 2 — Traveler Payment Flow (Not started):**
- Traveler booking show page (`/traveler/transport/bookings/{id}`)
- Payment methods display (from provider, active only)
- Transaction ref + proof upload (`payment_proof` column exists)
- `payment_status` → `submitted`
- Provider bell notification

**Chunk 3 — Provider Verify + Receipt (Not started):**
- Provider dashboard → payment proof review
- ✅ Approve / ❌ Reject
- On Approve: PDF receipt generate + email attachment + invoice auto-add
- On Reject: reason note + traveler notification

**Chunk 4 — Provider Invoice Integration (Not started):**
- `/provider/transport/invoices` — list (existing infrastructure reuse)
- Auto-generate from paid bookings
- PDF download (DomPDF)
- Filters

**Chunk 5 — Traveler Cancellation (Not started):**
- Traveler cancel request (separate table `transport_cancellation_requests`)
- Policy display + estimated refund
- Provider approve/reject
- On Approve: seat restore + refund status tracking

**Chunk 6 — Polish + E2E (Not started):**
- All emails (payment submitted, verified, cancelled)
- Full E2E test
- Doc update
- Commit

**Audit Trails Required (Master directive):**
- Payment approval/rejection (who + when + notes)
- Cancellation approval/rejection
- Refund status changes
- Immutable issued document snapshots

### 17. Remaining Verticals Public Pages (Master Directive — Transport Pattern)
**Status:** 🟡 Not started — next after Transport Phase C (Chunks 5-6)
**Estimate:** 15-18 hours (~5-6h per vertical)

**Master Directive (2026-11-15):**
> "बाँकी verticals (ArtCraft, Individual, MoneyChanger) public pages **Transport जस्तै advanced, professional, simple** — **herne bittikai bujhne** UI pattern"

**Common Pattern (Transport model — सबै verticals मा apply):**

1. **Premium hero** — vertical-specific color theme + gradient + cover placeholder (icon)
2. **Profile card** — provider name + verified badge + rating + address + logo/cover
3. **Search/filter UI** — vertical-appropriate (date/from-to/search as needed)
4. **Item cards** — visual, high-information (image + price + meta + CTA)
5. **Booking flow** — modal-based, progressive (Step 1 → Step 2 → Step 3)
6. **Email** — created + confirmed (logo plan-gated + user journey CTA + price breakdown)
7. **Provider bell notification** — new booking/order
8. **Payment flow** — reuse transport payment infrastructure (advance/PAY/both, per-vertical model)
9. **Receipt PDF** — reuse `TransportReceiptService` pattern (rename → generic — see `RECEIPT-SERVICE-GENERIC-01` ticket)
10. **i18n 5 locales** (en/np/hi/zh/ru)
11. **SEO + OG tags** (vertical-specific)
12. **Cover placeholder** (gradient + icon fallback)

**A. ArtCraft Public Page (Order: 1st)**
**Pattern:** Gallery-based (e-commerce style)
- Hero: gallery carousel (artwork photos)
- Artist profile (bio + specialized crafts + experience)
- Products grid (image + price + type: handmade/painting/sculpture/pottery)
- Filter: craft type, price range, availability
- **Custom order request form** — instead of direct purchase (provider quote + accept)
- **Unique:** Gallery lightbox, artwork zoom, custom commission flow
- **i18n keys:** `public_artcraft_*` (gallery, artist profile, custom order, etc.)
- **Estimate:** 5-6 hours

**B. Individual (Guide) Public Page (Order: 2nd)**
**Pattern:** Profile-based (professional booking)
- Hero: guide photo + experience years + certifications
- Profile card (languages, specialties: trekking/porter/cultural guide)
- **Calendar availability display** (dates available/unavailable)
- Services list (day hike, multi-day trek, porter services, cultural tour)
- Per-service pricing (per day / per trek)
- Booking: date picker → confirm
- **Unique:** Calendar integration, availability display, per-day pricing
- **i18n keys:** `public_individual_*` (guide profile, calendar, specialty, etc.)
- **Estimate:** 5-6 hours

**C. MoneyChanger Public Page (Order: 3rd)**
**Pattern:** Rate-table based (utility style)
- Hero: exchange rates display (live from `ForexService` — existing)
- Rate table (currency + buy + sell rates + updated at)
- Calculator widget (currency conversion — two-way)
- Location + hours + contact
- Booking: reserve currency (pickup time slot)
- **Unique:** Live rates, calculator, threshold display
- **Note:** Generic page पनि functional छ — तर rate widget + calculator = high value-add. Transport जस्तै full build recommended।
- **i18n keys:** `public_moneychanger_*` (rates, calculator, reserve, etc.)
- **Estimate:** 4-5 hours (simpler than ArtCraft/Individual)

**Shared refactors (future tickets — apply before/after):**
- `PAYMENT-MODEL-GENERIC-01` — rename `transport_payment_model` → `payment_model` (providers table) — apply to all verticals
- `RECEIPT-SERVICE-GENERIC-01` — extract `TransportReceiptService` → `ReceiptService` (polymorphic)
- Reuse `is_primary` payment method logic (already shared across all verticals)
- Reuse `invoices` table via metadata JSON (`source: artcraft_receipt | individual_receipt | ...`)

**Order rationale:**
1. **ArtCraft** — highest commercial value, gallery-driven
2. **Individual** — professional service (guide profile)
3. **MoneyChanger** — smallest scope, rate-table (generic reuse)

**After all 3:** Admin Notification System (final) → Push

---

### 18. Additional Future Tickets (पछि)

| Ticket | Scope | Priority |
|---|---|---|
| **PROVIDER-EARNINGS-DASHBOARD-01** | Provider total earnings across all verticals (transport + hotel + restaurant + artcraft + individual) — monthly/weekly summary + charts. **Note:** `/provider/payments` page currently TravelAI subscription payments only (सही — कोई fix नै)। Earnings dashboard अलग page। | 🟡 Medium |
| **PAYMENT-MODEL-GENERIC-01** | Rename `transport_payment_model` → `payment_model` (providers table) — apply to Hotel, Restaurant, ArtCraft, Individual, Trek | 🟡 Medium (before/after remaining verticals) |
| **RECEIPT-SERVICE-GENERIC-01** | Extract `TransportReceiptService` → `ReceiptService` (polymorphic — provider + booking type) | 🟢 Low (after 3 verticals) |
| **RECEIPT-LOCALE-01** | Receipt PDF i18n (currently English only) — traveler locale based | 🟢 Low |
| **RECEIPT-BRANDING-AUDIT-01** | Audit all verticals receipts — logo policy consistency (`hasFeature('custom_logo')`) | 🟢 Low |
| **LEGACY-BOOKINGS-FILTER-01** | Legacy 15 transport bookings (trip_id NULL) — provider dashboard filter option | 🟢 Low |
| **TRAVELER-CANCEL-POLICY-UI-01** | Chunk 5 follow-up — policy display + estimated refund calculator (client-side) | 🟡 Medium |
| **PROVIDER-REFUND-TRACKER-01** | After cancellation approve — track manual refund status (audit trail) | 🟡 Medium |

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


### Response Pattern (Phase 2 को लागि)
```php
// One-time response
$review->provider_response = $request->input('response');
$review->provider_responded_at = now();
$review->save();
$review->user->notify(new ReviewResponded($review));
Rating Calculation
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
Transport Phase 1 — DONE (Public page, Emails, Payment, Edit image)
Transport Phase B — DONE (B1-B5 — Trips, seat inventory, search flow, emails)
Transport Phase C — Chunks 1-4 DONE ✅ | Chunks 5-6 PENDING 🟡
Next: Chunk 5 (Traveler Cancellation) → Chunk 6 (E2E + polish)
After Transport C: ArtCraft → Individual → MoneyChanger public pages (Transport pattern)
After 3 verticals: Admin Notification System → Final Push

🟣 PENDING TASKS — A-Z COMPLETE LIST
🔴 HIGH PRIORITY (Immediate)
#	Task	Source	Est.
1	Push to Production — ५+ local commits pending (Master GO पछि)	भोलिको to-do #1	—
2	Notification Queue (7.6s delay) — सबै notification class मा implements ShouldQueue	भोलिको to-do #2	1-2h
3	Rescue Quick Accept (1-Click) — Dashboard card मै Accept button	भोलिको to-do #3	1-2h
🟡 MEDIUM PRIORITY
#	Task	Source	Est.
4	Fleet & Staff CRUD Verify — Helicopter, गाडी, staff add/remove test	भोलिको to-do #4	30m
5	Reports & Analytics Verify — Revenue, Charts सही छन्?	भोलिको to-do #5	30m
6	Provider Manual Booking Notification — Provider आफैंले booking बनाउँदा admin notify?	Earlier discussion	30m
7	Per-Vertical CRUD Deep Test — Hotel, Restaurant, Transport, ArtCraft, Individual, MoneyChanger (Add/Edit/Delete UI)	भोलिको to-do #6	1h
🟢 TESTING PHASE (तपाईंको Plan — Order important!)
#	Task	Note	Est.
8	Restaurant E2E — Order → email verify (cart → checkout → delivered)	Hotel पछि	15m
9	Transport E2E — Booking → email verify (Public Booking flow)	—	15m
10	ArtCraft E2E — Order → email verify (cart flow)	—	15m
11	Individual Guide E2E — Booking → email verify	—	15m
12	MoneyChanger E2E — Order → email verify (सबैभन्दा अन्तिम)	—	15m
🟣 COSMETIC / LOW PRIORITY
#	Task	Source	Est.
13	Ad Title Duplicate Fix — Rescue ad मा title = description (UI bug)	Earlier observation	15m
14	Provider Notification 'View all' page — same JSON bug fix करना (Traveler मा fix भयो, Provider मा verify गर्नुछ)	Pending assumption	30m
15	Active Districts Filter (if pending)	Phase 3 (earlier)	1h
16	Detail Page (Service detail enhancement)	Phase 3 (earlier)	1h
🟢 FUTURE FEATURES (Not Yet Started — For Discussion)
🔵 1. Expedition Service Vertical (NEW — Discuss करना)
What is it?
A new vertical for multi-day expedition packages (Mountaineering, Peak Climbing, Long Treks like Annapurna Circuit, Manaslu, Mustang, etc.) — beyond standard Trek/Tour.

Why separate from Trek?

Trek = standard routes (EBC, ABC) with fixed itinerary

Expedition = technical climbing (Island Peak, Mera Peak, Lobuche East), expedition permits, summit certificates, climbing gear rental, rescue insurance mandatory, Sherpa climbing guides

Suggested Features:

Expedition listing (peak, grade, difficulty, technicality)

Required climbing gear checklist

Insurance mandatory verification

Sherpa climbing guide assignment

Expedition permits (different from TIMS/ACAP)

Summit certificate (auto-issue)

Expedition-specific review (technicality, safety, gear quality)

Admin scope: Approve expedition listings, verify permits, monitor safety

Decision needed: Master (discuss करना)

🟠 2. Blockchain-Based Digital Permits (Future — Website मा "Coming 2026")
Already on marketing website — mentioned as "Coming 2026"

Scope (Conceptual):

TIMS & ACAP permits as NFTs / blockchain credentials

Immutable, verifiable, instant

Reduces corruption & delays

Traveler receives digital permit via blockchain

Checkpost QR scan verifies on-chain

Agency cannot fake permits

Rescue teams access permit holder data (privacy-controlled)

Possible Implementation Options:

Polygon / Solana (low gas fee)

Private consortium chain (Nepal tourism board partnership)

Decentralized identity (DID) standard

Decision needed: Master (is it scope? when?)

🟡 3. Advanced Review Features (Phase 3/4 — discussed)
Photos in reviews (DB ready)

Helpful votes (DB ready)

Flag/report (DB ready)

Dedicated public reviews page

Rating breakdown charts

Provider response visibility

Review email digest (weekly)

🟢 4. Other Future Ideas (Not Yet Discussed in Detail)
Live Chat / Messaging — Traveler ↔ Provider direct chat

Multi-currency Wallet — Save & pay in multiple currencies

Loyalty / Rewards Program — Points for repeat bookings

AI-Powered Dynamic Pricing — Auto-adjust based on demand

Video Content Moderation — AI scan of video ads

Mobile PWA Push Notifications — Real-time alerts on mobile

Trip Replay Social Sharing — Viral growth loop

Referral Program — Agency & traveler referrals

Expedition Insurance Integration — Automated policy

Blockchain Permits (above)

B2B API for Agencies — White-label integration

🎯 CURRENT MASTER DIRECTIVE
"सबै काम A-Z complete गरेपछि मात्र one time push। Admin Notification System = सबैभन्दा अन्तिम काम। Rule A, B, C, D, E पालना गर्नुस्। कोई hasty नै।"

🚦 WORK ORDER — Priority Sequence
text
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
Transport Phase 1 (DONE): ✅ Public page + Emails + Payment + Images
Transport Phase B (DONE): ✅ Scheduled Trips + Seat Inventory + Multi-route (B1-B5)
Transport Phase C (Chunks 1-4 DONE): ✅ Payment flow + verify/reject + receipt + invoice + email
Transport Phase C (Chunks 5-6 NEXT): 🟡 Traveler Cancellation + E2E polish
Remaining Verticals Public Pages (NEXT): 🟡 ArtCraft → Individual → MoneyChanger (Transport pattern — advanced, professional, simple)
Testing Phase: 🟢 (Restaurant DONE, Hotel DONE)
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
📞 HANDOVER CONTACTS / CREDENTIALS
Role	Credential
Admin	parasharregmi@gmail.com (super_admin)
Test Traveler	hostelhub.nep@gmail.com (User ID 299)
Test Provider	anjuregmimesh@gmail.com (Anju Regmi — Provider ID 177)
Test Service	Service ID 46 (Everest Base Camp Trek — 14 Days)
Test Hotel Provider	hotel@test.com (Bikash Gurung)
Test Restaurant Provider	restaurant@test.com (pending setup)
📝 FINAL NOTES FOR NEW ASSISTANT
यो doc पूरै पढ्नुस् — कोई guess नै।

Rule A — कोई code लेख्नु अघि actual file पढ्नुस्।

Review System Phase 1 + 2 — 100% verified (297 tests, 2 reviews live)।

Transport Phase 1 — DONE (3 commits ahead, pending push)।

NEXT — Transport Phase B — Scheduled Trips + Seat Inventory + Multi-route।

Push — सबै सिध्याएपछि, Master GO पछि एकैचोटि।

Admin Notification System — अन्तिम task।

Future Features (Expedition, Blockchain) — Master सँग discuss गर्नुस्।

END OF HANDOVER — कोई confusion भए user (दाइ) लाई सोध्नुस्, guess नगर्नुस्। 🙏

🇳🇵 TravelAI Nepal — Verified Trust & Reputation System निर्माण गर्दै।

