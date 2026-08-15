---

# TravelAI Nepal — Master Product, Architecture, Database & Implementation Blueprint

**Version:** 10.1 (FINAL – Phases 1-12 COMPLETED + Multi-Currency + Traveler Dashboard + Check-in Management)  
**Date:** August 2026  
**Status:** ✅ Phases 1-12 Implemented | ✅ Multi-Currency (USD/NPR) | ✅ Traveler Dashboard | ✅ Registration Redesign | ✅ Provider Check-in Management | ✅ QR Code in Traveler Booking | 🧹 Optional Cleanup Pending  
**Next Step:** Production Deployment & Testing  

---

## 1. Executive Summary

This document is the **Single Source of Truth** for the evolution of TravelAI Nepal. It is based on a thorough audit of the **actual Laravel 13 codebase, database schema, routes, models, controllers, and views**. The current system is a fully functional platform that supports **all 12 tourism business types**, authenticated travelers, AI-powered itineraries, booking, QR check‑in, SOS, reviews, notifications, advanced analytics, Stripe payments, PWA capabilities, **Multi-Currency (USD/NPR)**, **Traveler Dashboard**, **Account Type Registration**, **Provider Check-in Management**, and **Monthly/Yearly billing toggle**.

**✅ Phases 1-12 have been successfully implemented:**
- **Phase 1:** Foundation (provider_types, service_categories, plans, subscriptions, locations, verification_documents, provider_provider_type, provider_staff)
- **Phase 2:** User/Provider Integration (agencies → users + providers migration)
- **Phase 3:** Service Migration (treks → services + trek_details, tour_details, hotel_details)
- **Phase 4:** Booking Migration (bookings → traveler_id + service_id, dropped old columns)
- **Phase 5:** Authentication Transition (new User guard with login/register)
- **Phase 6:** Dashboard & Capabilities (Provider dashboard with policies and CRUD)
- **Phase 7:** Public Marketplace (services instead of treks)
- **Phase 8:** Pricing & Subscriptions (UI, plan selection, provider verification)
- **Phase 9:** Payments (Stripe integration, subscription payments, webhooks)
- **Phase 10:** Reviews & Notifications (traveler reviews, notification system, traveler dashboard)
- **Phase 11:** Advanced AI & Analytics (AI recommendations, content analysis, SOS SMS, provider/admin analytics)
- **Phase 12:** Mobile/PWA & Cleanup (PWA manifest, service worker, offline support, legacy deprecation ready, multi-currency, traveler dashboard redesign, registration page redesign, provider check-in management)

The key architectural shift is to **separate the user (authentication) from the provider (business entity)** and to **decouple provider types from system roles**. This document provides a detailed audit, target architecture, database mapping, phased migration strategy, and implementation roadmap—all designed to **preserve existing functionality** while enabling future extensibility.

---

## 2. Current System Overview (After Phases 1-12)

TravelAI Nepal is a production‑ready Laravel application with the following characteristics:

- **Purpose:** Connect trekkers/travelers with tourism businesses (trekking agencies, tour operators, hotels, guides, transport, etc.) for booking trips, generating AI itineraries, managing check‑ins, handling SOS, leaving reviews, receiving notifications, tracking analytics, and now accessible as a PWA.
- **Business Model:** Freemium with subscription plans (Free, Professional, Business, Enterprise). **Multi-Currency support (USD/NPR)**. Stripe payment integration for paid plans.
- **User Types:**
  - **Agency** (authenticated via `agency` guard – LEGACY) – manages treks, bookings, dashboard (still works, but deprecated).
  - **User** (authenticated via `web` guard – NEW) – can be Super Admin, Provider Owner, Manager, Staff, or Traveler.
  - **Provider** – business entity linked to User (Provider Owner).
  - **Trekker** – legacy non‑authenticated traveler record (guest booking still supported).
- **Core Functionality (All Working):**
  - Public listing of services (treks/tours/hotels) with search/filters (✅ Phase 7).
  - AI itinerary generator (Groq API, Llama 3.1).
  - Guest booking (no login required) with QR code generation.
  - **QR check‑in** – scan passport at checkpoints, record with location.
  - **SOS alerts** – email notification to agency (SMS skip mode).
  - Agency dashboard (LEGACY) – CRUD for treks and bookings.
  - **Provider dashboard** (NEW) – CRUD for services, bookings, analytics.
  - **Super admin dashboard** – global statistics and agency management.
  - **Pricing page** – NPR-only fixed pricing with Monthly/Yearly toggle (✅ Phase 8 + NPR Update).
  - **Subscription management** – Monthly/Yearly billing interval support (✅ Phase 8 + Billing Interval).
  - **Provider verification** – upload documents + admin review (✅ Phase 8).
  - **Payment integration** – Stripe for subscription payments (✅ Phase 9).
  - **Reviews system** – travelers rate services after completed bookings (✅ Phase 10).
  - **Notifications** – email + database for booking status updates, new reviews (✅ Phase 10).
  - **Traveler Dashboard** – Modern dashboard with stats, bookings, reviews, AI planner, coming soon sections (✅ Phase 10 + Redesign).
  - **AI Service Recommendations** – personalized, trending, similar services (✅ Phase 11).
  - **AI Content Analysis** – description tagging, sentiment analysis (✅ Phase 11).
  - **SOS SMS** – SMS alerts via Twilio/Nepal SMS (skip mode configured) (✅ Phase 11).
  - **Provider Analytics Dashboard** – revenue, bookings, top services, charts (✅ Phase 11).
  - **Admin Analytics Dashboard** – platform metrics, growth, top providers (✅ Phase 11).
  - **PWA Capabilities** – manifest, service worker, offline fallback (✅ Phase 12).
  - **Provider Directory** – all 12 tourism business types, filter by type, search, sort, ratings (✅ Phase 12).
  - **Provider Type Dropdown in Registration** – "Other" option with custom type (✅ Phase 12).
  - **Multi-Currency (USD/NPR)** – Currency selector in header, per-service base currency, display-only conversion (✅ Phase 12).
  - **Registration Redesign** – Account Type Selection (Traveler vs Business/Provider) with visual cards (✅ Phase 12).
  - **Provider Check-in Management** – Dedicated "Check-ins" menu, listing with filters, detail page with traveler info and location (✅ Phase 12).
  - **Traveler Trek History** – QR scan history displayed in Traveler Dashboard ("My Trek History").
  - **QR Code in Traveler Booking Detail** – Travelers can view QR code for check-in.
  - Service categories and provider types seeded.
  - Plans and subscriptions foundation (tables ready, UI complete).

---

## 3. Technology Stack

| Component               | Version / Detail                          |
|-------------------------|-------------------------------------------|
| **PHP**                 | ^8.3                                      |
| **Laravel**             | ^13.0                                     |
| **Database**            | MySQL                                     |
| **Templating**          | Blade                                     |
| **CSS**                 | Tailwind CSS (^4.0) via Vite              |
| **JavaScript**          | Vanilla JS, Axios, Vite, Chart.js         |
| **AI Provider**         | Groq API (Llama 3.1‑8b‑instant)           |
| **QR Code**             | SimpleSoftwareIO/simple-qrcode (^4.2)     |
| **Queue**               | Database driver (jobs table)              |
| **Notifications**       | Mail + Database (SOS, booking, reviews)   |
| **Payments**            | Stripe (v21.2) – Multi-Currency           |
| **SMS**                 | Twilio / Nepal SMS (skip mode for dev)    |
| **PWA**                 | Service Worker, Manifest, Offline Support |
| **Currency**            | Multi-Currency (USD/NPR)                  |
| **Packages**            | `laravel/framework`, `laravel/tinker`, `stripe/stripe-php`, `laravel/pail`, `laravel/pint`, `phpunit`, etc. |
| **Node Dependencies**   | Vite, Tailwind, Axios, concurrently, Chart.js |

---

## 4. Current Database Audit (After Phases 1-12)

[Same as previous – all tables, enums, and relationships documented]

**Additional:**
- `plans.price_monthly` – **NPR (Rs.)** – Professional: 4499, Business: 11999
- `plans.price_yearly` – **NPR (Rs.)** – Professional: 44999, Business: 119999
- `subscriptions.billing_interval` – `monthly` / `yearly` (✅ Added)
- `services.currency` – `USD` / `NPR` (✅ Added – base currency per service)
- `qr_scans` – checkpoint_name, scanned_at, latitude, longitude (existing)

---

## 5. Current Models Audit (After Phases 1-12)

[Same as previous – all models, relationships, casts documented]

**Additional:**
- `Subscription::billing_interval` – `monthly` / `yearly`
- `Subscription::isMonthly()` / `Subscription::isYearly()` helpers
- `Plan` – prices now in NPR
- `Service::currency` – base currency (USD/NPR)
- `CurrencyService` – centralized currency formatting/conversion
- `QrScan` – booking relationship, traveler relationship via booking

---

## 6. Current Authentication Audit (After Phases 1-12)

[Same as previous – User Guard + Legacy Agency Guard deprecated]

---

## 7. Current Routes Audit (After Phases 1-12)

[Same as previous – cleaned routes, all legacy removed]

**Additional:**
- `GET /currency/switch` – Currency switch route (Session-based)
- `GET /traveler/bookings/{booking}` – Traveler booking detail route
- `GET /provider/checkins` – Provider check-in listing
- `GET /provider/checkins/{scan}` – Provider check-in detail

---

## 8. Current Controllers Audit (After Phases 1-12)

[Same as previous – all controllers documented]

**Additional:**
- `Traveler\DashboardController` – Updated with stats, active trip, greeting, QR history
- `Traveler\BookingController` – Booking detail with QR code
- `Provider\CheckinController` – Listing and detail of QR scans
- `Provider\DashboardController` – Added bookings trend chart, top services, recent check-ins
- `Provider\AnalyticsController` – Fixed avg booking value, CSV currency column
- `CurrencyService` – Centralized currency handling

---

## 9. Services / Jobs / Events Audit (After Phases 1-12)

[Same as previous – all services, jobs, notifications documented]

**Additional:**
- `CurrencyService` – ✅ Created and integrated
- `PaymentService` – `currency = 'npr'` – ✅ Verified and tested

---

## 10. Current Views / UI Audit (After Phases 1-12)

**Public Layout:** `layouts/public.blade.php` – with PWA support + Currency Selector.

| View | Purpose | Status |
|------|---------|--------|
| `public/pricing.blade.php` | Pricing with NPR-only, Monthly/Yearly toggle | ✅ Complete |
| `public/providers/index.blade.php` | Provider Directory | ✅ Complete |
| `auth/register.blade.php` | Account Type Selection (Traveler/Provider) | ✅ Complete |
| `traveler/dashboard.blade.php` | Modern Traveler Dashboard with stats, AI planner, coming soon, Trek History | ✅ Complete |
| `traveler/bookings/show.blade.php` | Booking Detail with QR Code, Review | ✅ Complete |
| `provider/dashboard.blade.php` | Charts, Recent Check-ins | ✅ Complete |
| `provider/checkins/index.blade.php` | Check-in listing with search/filters | ✅ Complete |
| `provider/checkins/show.blade.php` | Check-in detail with traveler, service, checkpoint, location | ✅ Complete |
| `provider/analytics/index.blade.php` | Analytics with Avg. Booking Value | ✅ Complete |
| All other views | Various | ✅ Complete |

---

## 11. Existing Feature Matrix (After Phases 1-12)

| Feature                   | Status           | Notes |
|---------------------------|------------------|-------|
| AI Itinerary Generator    | ✅ Working      | Enhanced in Phase 11 |
| Service Listing           | ✅ Working      | Phase 7 |
| Service Detail            | ✅ Working      | Phase 7 + rating (Phase 10) |
| Provider Profile          | ✅ Working      | Phase 7 |
| Search/Filters            | ✅ Working      | Phase 7 |
| Guest Booking             | ✅ Working      | Phase 7 |
| QR Check‑in               | ✅ Working      | Preserve |
| SOS Alerts                | ✅ Working      | Email + SMS (skip mode) |
| Provider Dashboard        | ✅ Working      | Phase 6 |
| Service CRUD (Provider)   | ✅ Working      | Phase 6 |
| Booking Management (Provider) | ✅ Working | Phase 6 + notifications (Phase 10) |
| User Auth (Login/Register) | ✅ Working     | Phase 5 |
| Policies (Service/Booking/Review) | ✅ Working | Phase 6 + Phase 10 |
| Pricing Page              | ✅ Working      | NPR-only + Billing Toggle |
| Subscription UI           | ✅ Working      | Phase 8 + Billing Interval |
| Plan Selection (Register) | ✅ Working      | Phase 8 |
| Provider Verification     | ✅ Working      | Phase 8 |
| Payment Integration       | ✅ Working      | Multi-Currency |
| Reviews & Ratings         | ✅ Working      | Phase 10 |
| Notifications             | ✅ Working      | Phase 10 |
| Traveler Dashboard        | ✅ Working      | Redesigned |
| AI Recommendations        | ✅ Working      | Phase 11 |
| Content Analysis          | ✅ Working      | Phase 11 |
| SOS SMS                   | ✅ Working (skip)| Phase 11 |
| Provider Analytics        | ✅ Working      | Phase 11 |
| Admin Analytics           | ✅ Working      | Phase 11 |
| PWA & Offline Support     | ✅ Working      | Phase 12 |
| Provider Directory        | ✅ Working      | Phase 12 |
| Provider Type Dropdown    | ✅ Working      | Phase 12 |
| Monthly/Yearly Billing    | ✅ Working      | Phase 12 |
| Multi-Currency (USD/NPR)  | ✅ Working      | Phase 12 |
| Account Type Registration | ✅ Working      | Phase 12 |
| **Provider Check-in Management** | ✅ Working | Phase 12 – New |
| **Traveler Trek History** | ✅ Working      | Phase 12 – New |
| **QR Code in Booking Detail** | ✅ Working | Phase 12 – New |

---

## 12. Working Features (Confirmed – All Phases 1-12)

- ✅ AI itinerary generation (API endpoint and frontend form)
- ✅ Public listing of services with filters (Phase 7)
- ✅ Service detail page with provider info and rating (Phase 7 + 10)
- ✅ Provider profile page (Phase 7)
- ✅ Guest booking with QR code generation (Phase 7)
- ✅ Booking confirmation page (Phase 7)
- ✅ QR check‑in (page and scan recording)
- ✅ SOS alert creation and email notification (queued)
- ✅ User login/register (NEW)
- ✅ Provider dashboard (NEW)
- ✅ Service CRUD (NEW)
- ✅ Booking management (NEW)
- ✅ Policies for Services, Bookings, and Reviews
- ✅ Pricing page – NPR-only + Billing Toggle (Phase 8 + Phase 12)
- ✅ Subscription management – Monthly/Yearly (Phase 8 + Phase 12)
- ✅ Provider verification (Phase 8)
- ✅ Payment integration with Stripe – Multi-Currency (Phase 9 + Phase 12)
- ✅ Reviews & Ratings (Phase 10)
- ✅ Notifications (booking, review) (Phase 10)
- ✅ Traveler Dashboard – Modern redesign (Phase 10 + Phase 12)
- ✅ AI Service Recommendations (Phase 11)
- ✅ Content Analysis (Phase 11)
- ✅ SOS SMS (skip mode) (Phase 11)
- ✅ Provider Analytics Dashboard (Phase 11)
- ✅ Admin Analytics Dashboard (Phase 11)
- ✅ PWA Manifest & Service Worker (Phase 12)
- ✅ Offline Fallback View (Phase 12)
- ✅ Provider Directory (Phase 12)
- ✅ Provider Type Dropdown (Phase 12)
- ✅ Monthly/Yearly Billing Toggle (Phase 12)
- ✅ Multi-Currency (USD/NPR) (Phase 12)
- ✅ Account Type Registration (Phase 12)
- ✅ **Provider Check-in Management** (Phase 12)
- ✅ **Traveler Trek History** (Phase 12)
- ✅ **QR Code in Traveler Booking Detail** (Phase 12)

---

## 13. Multi-Currency Implementation

### Architecture
- **Base Currency:** Each service has its own base currency (`services.currency` – USD or NPR)
- **Display Currency:** User selects from header (USD / NPR), stored in session
- **Default:** USD
- **Conversion:** Display-only via `CurrencyService`
- **Exchange Rate:** Configurable via `.env` (`EXCHANGE_RATE_USD_NPR=152.60`)

### Public Display

| Scenario | Display |
|----------|---------|
| Service base = USD, Display = USD | $1,500 |
| Service base = USD, Display = NPR | Rs. 228,900 |
| Service base = NPR, Display = NPR | Rs. 15,000 |
| Service base = NPR, Display = USD | $98 |

### Key Files
- `app/Services/CurrencyService.php` – Centralized currency handling
- `resources/views/layouts/public.blade.php` – Currency selector in header
- `routes/web.php` – `GET /currency/switch` route
- `config/app.php` – `exchange_rate` configuration

---

## 14. Traveler Dashboard (Redesigned)

### Features
- **Welcome Message:** Time-based greeting with user name
- **Quick Actions:** Plan with AI, Explore Nepal
- **Stats Cards:** Upcoming, Active, Completed, Reviews
- **Active Trip:** Shows confirmed booking with status
- **My Bookings:** List with status, review button, view button
- **My Reviews:** Submitted reviews with ratings
- **AI Travel Planner:** Prominent card with description
- **Coming Soon:** Digital Trek Passport, Safety Center
- **My Trek History:** QR scan history (recent check-ins) – *New*

### Key Files
- `app/Http/Controllers/Traveler/DashboardController.php`
- `resources/views/traveler/dashboard.blade.php`
- `app/Http/Controllers/Traveler/BookingController.php`
- `resources/views/traveler/bookings/show.blade.php` – with QR code

---

## 15. Registration Page (Redesigned)

### Account Type Selection
- **Traveler:** Simple form (name, email, phone, password) – no plans
- **Business/Provider:** Full form + plan selection + provider fields

### UI Features
- Visual cards with icons
- Selected state with checkmark
- Dynamic submit button text
- Provider fields hidden by default

### Key File
- `resources/views/auth/register.blade.php`

---

## 16. Provider Check-in Management (New)

### Features
- **Sidebar Menu:** "Check-ins" in provider layout
- **Check-in Listing:** Table with traveler, service, checkpoint, time, actions
- **Search & Filter:** Search by traveler/service, date range
- **Check-in Detail:** Shows traveler info, service, provider, checkpoint, location (lat/lng)
- **Integration:** Links to booking detail

### Key Files
- `app/Http/Controllers/Provider/CheckinController.php`
- `resources/views/provider/checkins/index.blade.php`
- `resources/views/provider/checkins/show.blade.php`
- `routes/web.php` – provider check-in routes

---

## 17. Partial Features

- **Waitlist:** Frontend form present but no backend logic.
- **SMS:** SOS SMS implemented in skip mode (logs only); needs real credentials for production.
- **Legacy Cleanup:** Files/directories are backed up but not yet deleted (optional – Phase 12 cleanup pending).
- **Invoice/Billing System:** Not yet implemented (Phase 13 planned).

---

## 18. Missing Features

- Messaging between traveler and provider
- Advanced analytics (trends, forecasting)
- Multi-language support
- Native mobile app
- Invoice & Receipt System (Phase 13 planned)

---

## 19. Technical Debt (After Phases 1-12)

- Fat Controllers – business logic inside controllers
- No Form Requests – validation in controllers
- No Global Scopes – data isolation via Policies
- No Caching – statistics recalculated on every request
- Legacy Code – agency controllers/views/models still present

---

## 20. Phase‑by‑Phase Roadmap (Final)

| Phase | Goal | Status |
|-------|------|--------|
| Phase 1 | Foundation | ✅ COMPLETED |
| Phase 2 | User/Provider Integration | ✅ COMPLETED |
| Phase 3 | Service Migration | ✅ COMPLETED |
| Phase 4 | Booking Migration | ✅ COMPLETED |
| Phase 5 | Authentication Transition | ✅ COMPLETED |
| Phase 6 | Dashboard & Capabilities | ✅ COMPLETED |
| Phase 7 | Public Marketplace | ✅ COMPLETED |
| Phase 8 | Pricing & Subscriptions | ✅ COMPLETED |
| Phase 9 | Payments | ✅ COMPLETED |
| Phase 10 | Reviews & Notifications | ✅ COMPLETED |
| Phase 11 | Advanced AI, Safety, Analytics | ✅ COMPLETED |
| Phase 12 | Mobile/PWA & Cleanup | ✅ COMPLETED |
| Phase 13 | Invoice & Billing System | ⏳ Planned |

---

## 21. Phase 12 – Mobile/PWA & Cleanup (Completed)

**What was done:**
- ✅ PWA manifest (`public/manifest.json`) created.
- ✅ Service worker (`public/sw.js`) registered.
- ✅ Offline fallback view (`resources/views/offline.blade.php`) created.
- ✅ PWA meta tags and SW registration added to `layouts/public.blade.php`.
- ✅ Migration to drop legacy tables (ready for final cleanup).
- ✅ `config/auth.php` – removed `agency` guard and provider.
- ✅ `routes/web.php` – removed all legacy routes (agency, treks, etc.).
- ✅ `bootstrap/app.php` – confirmed no legacy middleware references.
- ✅ Legacy controllers, models, views **backed up** (optional deletion pending testing).
- ✅ `.env` – production flags updated.
- ✅ **Provider Directory Page** – with cover image, stats bar, filter by type, search, sort, ratings, and all 12 business types visible.
- ✅ **TourismProvidersSeeder** – realistic data, no dummy services, mixed currencies.
- ✅ **Registration मा Provider Type Dropdown** – with "Other" custom option.
- ✅ **Multi-Currency (USD/NPR)** – Currency selector, per-service base currency, display-only conversion.
- ✅ **Traveler Dashboard Redesign** – Modern dashboard with stats, AI planner, coming soon sections, **Trek History**.
- ✅ **Registration Page Redesign** – Account Type Selection (Traveler vs Business/Provider).
- ✅ **PaymentService NPR tested** – Stripe NPR payment confirmed working.
- ✅ **Provider Check-in Management** – Dedicated menu, listing, detail page, filters.
- ✅ **QR Code in Traveler Booking Detail** – Travelers can view QR code for check-in.
- ✅ **Realistic Demo Data** – All providers, services, bookings, reviews, QR scans updated with real traveler names.
- ✅ **Provider Dashboard Charts** – Bookings trend, top services.
- ✅ **Provider Analytics** – Avg. Booking Value fixed, CSV export with currency column.

**What remains (optional):**
- 🧹 Delete legacy files/folders (after thorough testing):
  - `app/Http/Controllers/Agency/`
  - `app/Http/Controllers/PublicTrekController.php`
  - `app/Http/Controllers/TrekController.php`
  - `app/Http/Controllers/TrekBookingController.php`
  - `app/Models/Agency.php`
  - `app/Models/Trek.php`
  - `app/Models/Trekker.php`
  - `resources/views/agency/`
  - `resources/views/trek/`
  - `resources/views/booking/`
  - `resources/views/public/treks/`

---

## 22. Phase 13 – Invoice & Billing System (Planned)

| Improvement | Status | Priority |
|-------------|--------|----------|
| Invoice Generation (PDF) | ⏳ Planned | Medium |
| Receipt System | ⏳ Planned | Medium |
| Invoice Numbering | ⏳ Planned | Medium |
| Invoice Email | ⏳ Planned | Medium |
| Admin Invoice Management | ⏳ Planned | Medium |
| Provider Invoice Dashboard | ⏳ Planned | Medium |
| Tax/GST Calculation | ⏳ Planned | Low |

**Overview:**
- Payment success → Automatic invoice generation
- Invoice number: `INV-2026-0001`
- PDF generation with `laravel-dompdf`
- Email to provider with invoice PDF
- Admin panel: List, view, filter, manage invoices
- Provider dashboard: View and download own invoices
- Receipt generation on payment confirmation

---

## 23. Optional Improvements (Future)

| Improvement | Status | Priority |
|-------------|--------|----------|
| Multi-language Support (Nepali/English) | ⏳ Future | Low |
| Native Mobile App | ⏳ Future | Low |
| Messaging between Traveler & Provider | ⏳ Future | Medium |
| Advanced Analytics (Trends, Forecasting) | ⏳ Future | Low |
| Real SMS Gateway Integration | ⏳ Future | Medium |
| Invoice & Receipt System | ⏳ Future (Phase 13) | Medium |

---

## 24. NOW vs NEXT vs LATER (Final)

| Category | Features | Status |
|----------|----------|--------|
| **NOW** (Phases 1-12) | All core features + PWA + Provider Directory + 12 Business Types + Multi-Currency + Traveler Dashboard + Check-in Management | ✅ COMPLETED |
| **NEXT** | Testing, Deployment, Monitoring | ⏳ In Progress |
| **LATER** | Messaging, Native Mobile Apps, Advanced Reporting, Multi-language, Invoice System | ⏳ Future |

---

## 25. Go / No‑Go Checklist (Final)

### ✅ COMPLETED (Phases 1-12)

| Element | Status |
|---------|--------|
| All new tables created | ✅ |
| All models with relationships | ✅ |
| Separate `User` and `Provider` concepts | ✅ |
| Role‑based permissions via Policies | ✅ |
| Public marketplace with services | ✅ |
| Pricing page – NPR-only + Billing Toggle | ✅ |
| Subscription UI & management – Monthly/Yearly | ✅ |
| Provider verification | ✅ |
| Stripe payment integration – Multi-Currency | ✅ |
| Webhook handling | ✅ |
| Payment history | ✅ |
| Reviews & Ratings | ✅ |
| Notifications (booking, review) | ✅ |
| Traveler Dashboard – Redesigned | ✅ |
| AI Recommendations | ✅ |
| Content Analysis | ✅ |
| SOS SMS (skip mode) | ✅ |
| Provider Analytics Dashboard | ✅ |
| Admin Analytics Dashboard | ✅ |
| PWA & Offline Support | ✅ |
| Provider Directory (12 Business Types) | ✅ |
| Provider Type Dropdown in Registration | ✅ |
| Monthly/Yearly Billing Toggle | ✅ |
| Multi-Currency (USD/NPR) | ✅ |
| Account Type Registration | ✅ |
| **Provider Check-in Management** | ✅ |
| **Traveler Trek History** | ✅ |
| **QR Code in Booking Detail** | ✅ |
| Legacy cleanup ready | ✅ |
| Gradual migration approach | ✅ |

### ⏳ PENDING (Future)

| Element | Status |
|---------|--------|
| Delete legacy files (after testing) | 🧹 Optional |
| SMS real credentials | ⏳ Future |
| Invoice & Receipt System | ⏳ Future (Phase 13) |
| Multi-language Support | ⏳ Future |
| Native Mobile App | ⏳ Future |

---

**End of Master Document (FINAL – Phases 1-12 + Multi-Currency + Traveler Dashboard + Check-in Management)**

---
