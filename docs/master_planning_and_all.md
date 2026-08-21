Bro, **अब मैले तिम्रो `master_planning_and_all.md` फाइललाई **Version 10.3** मा update गरेको छु।**  
यसमा **AI Planner (ABC/EBC/Langtang)** को पूरा implementation, **Fallback Mechanism**, **Cost Calculation**, **Service Integration**, र **Phase 1–3** को सबै काम समावेश गरिएको छ।

तलको **पूरा content** लाई `docs/master_planning_and_all.md` मा **Replace** गर्नुहोस्।

---

```markdown
# TravelAI Nepal — Master Product, Architecture, Database & Implementation Blueprint

**Version:** 10.3 (FINAL – Phases 1-12 COMPLETED + AI Planner Grounded + Fallback + UI/UX & SEO Enhancements + Invoice Foundation)  
**Date:** August 2026  
**Status:** ✅ Phases 1-12 Implemented | ✅ **AI Planner (ABC/EBC/Langtang) Grounded** | ✅ **Fallback Itinerary Mechanism** | ✅ **Cost Calculation Backend** | ✅ **Service Integration** | ✅ Multi-Currency (USD/NPR) | ✅ Traveler Dashboard | ✅ Registration Redesign | ✅ Provider Check-in Management | ✅ QR Code in Traveler Booking | ✅ **SEO Optimization** | ✅ **High‑Resolution Favicon** | ✅ **Logo in All Dashboards** | ✅ **Login/Register Logo** | ✅ **.htaccess Cache Control** | ✅ **Invoice System Foundation** | 🧹 Optional Cleanup Pending  
**Next Step:** Production Deployment & Testing

---

## 1. Executive Summary

This document is the **Single Source of Truth** for the evolution of TravelAI Nepal. It is based on a thorough audit of the **actual Laravel 13 codebase, database schema, routes, models, controllers, and views**. The current system is a fully functional platform that supports **all 12 tourism business types**, authenticated travelers, AI-powered itineraries, booking, QR check‑in, SOS, reviews, notifications, advanced analytics, Stripe payments, PWA capabilities, **Multi-Currency (USD/NPR)**, **Traveler Dashboard**, **Account Type Registration**, **Provider Check-in Management**, and **Monthly/Yearly billing toggle**.

**✅ Phases 1-12 have been successfully implemented** (see roadmap below).  
**✅ Additional Enhancements (Phase 12.5) have been completed:**

- **AI Planner (Grounded):** ABC, EBC, Langtang routes fully seeded with waypoints, segments, costs. Database-grounded itinerary generation with fallback.
- **Fallback Mechanism:** If AI (Groq) fails (rate limit, timeout), the system automatically generates a grounded itinerary from database segments.
- **Cost Calculation:** Backend calculates total cost from `route_costs` (permits, transport, food × days) – NPR only.
- **Service Integration:** Partner services (hotel, transport, guide) are passed to AI context – Phase 2.
- **SEO Optimization:** Meta tags (description, keywords, robots, canonical), Open Graph (og:title, og:description, og:image), Twitter Cards, dynamic sitemap.xml, robots.txt.
- **High‑Resolution Favicon:** Cropped logo, multiple sizes (16×16, 32×32, 64×64, 96×96, 128×128, 180×180, 512×512) with `?v=3` cache‑busting.
- **Logo in All Dashboards:** Admin and Provider sidebars now display the TravelAI logo (instead of text/icon), matching the public and traveler layouts.
- **Login/Register Page Logo:** Replaced FontAwesome icon with the actual logo image on both login and registration pages.
- **.htaccess Cache Control:** Added `Cache-Control` headers for static assets (images, favicons) to improve performance.
- **Invoice System Foundation:** Created `invoices` table, `Invoice` model, and `InvoiceService` (ready for Phase 13).

The key architectural shift is to **separate the user (authentication) from the provider (business entity)** and to **decouple provider types from system roles**. This document provides a detailed audit, target architecture, database mapping, phased migration strategy, and implementation roadmap—all designed to **preserve existing functionality** while enabling future extensibility.

---

## 2. Current System Overview (After Phases 1-12 + AI Planner + Enhancements)

TravelAI Nepal is a production‑ready Laravel application with the following characteristics:

- **Purpose:** Connect trekkers/travelers with tourism businesses (trekking agencies, tour operators, hotels, guides, transport, etc.) for booking trips, generating AI itineraries, managing check‑ins, handling SOS, leaving reviews, receiving notifications, tracking analytics, and now accessible as a PWA.
- **Business Model:** Freemium with subscription plans (Free, Professional, Business, Enterprise). **Multi-Currency support (USD/NPR)**. Stripe payment integration for paid plans.
- **User Types:**
  - **Agency** (authenticated via `agency` guard – LEGACY) – manages treks, bookings, dashboard (still works, but deprecated).
  - **User** (authenticated via `web` guard – NEW) – can be Super Admin, Provider Owner, Manager, Staff, or Traveler.
  - **Provider** – business entity linked to User (Provider Owner).
  - **Trekker** – legacy non‑authenticated traveler record (guest booking still supported).
- **Core Functionality (All Working):**
  - Public listing of services (treks/tours/hotels) with search/filters.
  - **AI itinerary generator (Grounded):** ABC, EBC, Langtang routes with database-grounded data. Fallback to database segments if AI fails.
  - Guest booking (no login required) with QR code generation.
  - **QR check‑in** – scan passport at checkpoints, record with location.
  - **SOS alerts** – email notification to agency (SMS skip mode).
  - Agency dashboard (LEGACY) – CRUD for treks and bookings.
  - **Provider dashboard** (NEW) – CRUD for services, bookings, analytics.
  - **Super admin dashboard** – global statistics and agency management.
  - **Pricing page** – NPR-only fixed pricing with Monthly/Yearly toggle.
  - **Subscription management** – Monthly/Yearly billing interval support.
  - **Provider verification** – upload documents + admin review.
  - **Payment integration** – Stripe for subscription payments (multi‑currency).
  - **Reviews system** – travelers rate services after completed bookings.
  - **Notifications** – email + database for booking status updates, new reviews.
  - **Traveler Dashboard** – Modern dashboard with stats, bookings, reviews, AI planner, coming soon sections, and **Trek History**.
  - **AI Service Recommendations** – personalized, trending, similar services.
  - **AI Content Analysis** – description tagging, sentiment analysis.
  - **SOS SMS** – SMS alerts via Twilio/Nepal SMS (skip mode configured).
  - **Provider Analytics Dashboard** – revenue, bookings, top services, charts.
  - **Admin Analytics Dashboard** – platform metrics, growth, top providers.
  - **PWA Capabilities** – manifest, service worker, offline fallback.
  - **Provider Directory** – all 12 tourism business types, filter by type, search, sort, ratings.
  - **Provider Type Dropdown in Registration** – "Other" option with custom type.
  - **Multi-Currency (USD/NPR)** – Currency selector in header, per-service base currency, display-only conversion.
  - **Registration Redesign** – Account Type Selection (Traveler vs Business/Provider) with visual cards.
  - **Provider Check-in Management** – Dedicated "Check-ins" menu, listing with filters, detail page.
  - **Traveler Trek History** – QR scan history displayed in Traveler Dashboard.
  - **QR Code in Traveler Booking Detail** – Travelers can view QR code for check‑in.
  - **SEO Optimization** – Complete meta tags, Open Graph, Twitter Cards, sitemap.xml, robots.txt.
  - **High‑Resolution Favicon** – Multiple PNG sizes + ICO, cache‑busting, cropped logo.
  - **Logo in All Dashboards** – Admin, Provider, Traveler (via public layout) all show the brand logo.
  - **Login/Register Logo** – Logo image on authentication pages.
  - **.htaccess Cache Control** – Headers for static assets to improve load times.
  - **Invoice System Foundation** – Database schema, model, and service class ready.

---

## 3. AI Planner (Grounded) – Implementation Details

### 3.1 Overview

The AI Planner generates day-by-day trekking itineraries using **database-grounded data** (waypoints, segments, costs) rather than relying solely on the LLM. This eliminates hallucinations (e.g., "Nayapul ≈ 2 km", "18,000 NPR bus").

### 3.2 Architecture Flow

```
User Input (destination, days, budget, style)
     ↓
PlannerController (validate input)
     ↓
PlannerService::generate()
     ↓
resolveRoute() → routes table (ABC/EBC/Langtang)
     ↓
Load route + segments + waypoints + costs
     ↓
calculateCost() → route_costs (NPR only)
     ↓
buildContext() → segments + costs + available_services
     ↓
LLM (Groq) – attempt to generate AI itinerary
     ↓
If AI fails (rate limit, timeout, invalid JSON) → Fallback
     ↓
buildFallbackResponse() → grounded segments + costs
     ↓
ItineraryValidator → validate waypoints, service_id, pad days
     ↓
Save to DB (planner_requests, results, days, items)
     ↓
Frontend (home.blade.js) → render itinerary with total cost
```

### 3.3 Routes Seeded

| Route | Slug | Segments | Waypoints | Costs (NPR) |
| :--- | :--- | :--- | :--- | :--- |
| Annapurna Base Camp | `annapurna-base-camp` | 13 | 14 | TIMS 2,000, ACAP 3,000, Transport 1,000, Food 2,500/day |
| Everest Base Camp | `everest-base-camp` | 14 (forward + return) | 8 | Sagarmatha 3,000, Khumbu 2,000, Food 3,000/day |
| Langtang Valley | `langtang-valley` | 6 (forward + return) | 4 | Langtang NP 3,000, TIMS 2,000, Food 2,500/day |

### 3.4 Key Services

| Service | File | Purpose |
| :--- | :--- | :--- |
| `LlmService` | `app/Services/LlmService.php` | Groq API integration, JSON extraction, rate limit handling |
| `PlannerService` | `app/Services/PlannerService.php` | Main planner logic, route resolution, cost calc, context building, fallback |
| `ItineraryValidator` | `app/Services/ItineraryValidator.php` | Validate waypoints, service_id, pad days, generate fallback |
| `PlannerController` | `app/Http/Controllers/Api/PlannerController.php` | API endpoint `POST /api/planner/generate` |
| `home.blade.php` | `resources/views/home.blade.php` | Frontend form, auto-fill, cost preview, itinerary render |

### 3.5 Cost Calculation (Backend)

`calculateCost()` uses `route_costs` table:
- `per_person` → fixed (TIMS, ACAP)
- `per_day` → multiplied by user's requested days (food)

**Example (ABC, 9 days):**
```
TIMS: 2,000
ACAP: 3,000
Transport: 1,000
Food: 2,500 × 9 = 22,500
─────────────────
Total: 28,500 NPR
```

### 3.6 Fallback Mechanism

If the LLM (Groq) fails (rate limit, timeout, invalid JSON), the system automatically generates a **grounded itinerary** from the database segments:

- Each day = one segment (from → to waypoint)
- Cost per day = (permits + transport) / total days + food per day
- Extra days → Rest & Acclimatization days

### 3.7 Service Integration (Phase 2)

The planner passes **verified partner services** (hotel, transport, guide) to the LLM context. The LLM can only select from these services, preventing hallucinated business names.

### 3.8 Frontend Features

- **Destination dropdown:** ABC, EBC, Langtang
- **Auto-fill days & budget** based on destination (user can override)
- **Real-time cost preview** (NPR + approx USD)
- **Total cost display** after generation
- **Download TXT** and **Copy** buttons

---

## 4. Technology Stack

| Component               | Version / Detail                          |
|-------------------------|-------------------------------------------|
| **PHP**                 | ^8.3                                      |
| **Laravel**             | ^13.0                                     |
| **Database**            | MySQL                                     |
| **Templating**          | Blade                                     |
| **CSS**                 | Tailwind CSS (^4.0) via Vite              |
| **JavaScript**          | Vanilla JS, Axios, Vite, Chart.js         |
| **AI Provider**         | Groq API (qwen/qwen3.6-27b)               |
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

## 5. Current Database Audit (After Phases 1-12 + AI Planner + Enhancements)

### 5.1 Core Tables

| Table | Purpose | Status |
| :--- | :--- | :--- |
| `users` | Authenticated users (Super Admin, Provider Owner, Manager, Staff, Traveler) | ✅ |
| `providers` | Business entities linked to `users` | ✅ |
| `provider_types` | 12 tourism business types (Trekking Agency, Tour Agency, Hotel, Resort, Lodge, Homestay, Guide, Porter, Transport, Activity, Local Experience, Photographer) | ✅ |
| `provider_staff` | Staff assigned to providers | ✅ |
| `services` | Services offered by providers (treks, tours, hotels, guides, transport, activities, experiences) | ✅ |
| `service_categories` | Trek, Tour, Hotel, Guide, Transport, Activity, Experience | ✅ |
| `trek_details` | Trek-specific details (duration, difficulty, itinerary JSON) | ✅ |
| `tour_details` | Tour-specific details (duration, inclusions, exclusions) | ✅ |
| `hotel_details` | Hotel-specific details (star rating, amenities, check-in/out) | ✅ |
| `bookings` | Guest/authenticated bookings with QR code | ✅ |
| `qr_scans` | QR check-in history with location | ✅ |
| `sos_alerts` | SOS alerts from trekkers | ✅ |
| `reviews` | Reviews & ratings for services | ✅ |
| `notifications` | Database notifications | ✅ |
| `plans` | Subscription plans (Free, Professional, Business, Enterprise) | ✅ |
| `subscriptions` | Provider subscriptions with billing interval | ✅ |
| `payments` | Stripe payment records | ✅ |
| `verification_documents` | Provider verification documents | ✅ |
| `locations` | Geographic locations | ✅ |
| `invoices` | Invoice foundation (Phase 13 ready) | ✅ |

### 5.2 AI Planner Tables

| Table | Purpose | Status |
| :--- | :--- | :--- |
| `waypoints` | Trekking waypoints (name, type, lat, lng, altitude) | ✅ |
| `routes` | Trekking routes (name, slug, difficulty, duration_days, max_altitude) | ✅ |
| `route_segments` | Segments between waypoints (distance, time, elevation) | ✅ |
| `route_costs` | Route costs (permit, transport, food) | ✅ |
| `planner_requests` | User itinerary requests | ✅ |
| `planner_results` | AI-generated results with validation status | ✅ |
| `itinerary_days` | Normalized itinerary days | ✅ |
| `itinerary_items` | Items within each day (activities, costs, service_id) | ✅ |

---

## 6. Current Models Audit (After Phases 1-12 + AI Planner + Enhancements)

| Model | Table | Relationships |
| :--- | :--- | :--- |
| `User` | `users` | `hasMany(ProviderStaff)`, `hasMany(Provider)` |
| `Provider` | `providers` | `belongsTo(User)`, `belongsToMany(ProviderType)`, `hasMany(Service)`, `hasMany(Subscription)`, `hasMany(VerificationDocument)`, `hasMany(Invoice)` |
| `ProviderType` | `provider_types` | `belongsToMany(Provider)` |
| `Service` | `services` | `belongsTo(Provider)`, `belongsTo(ServiceCategory)`, `belongsTo(Location)`, `hasOne(TrekDetail)`, `hasOne(TourDetail)`, `hasOne(HotelDetail)`, `hasMany(Booking)`, `hasMany(Review)` |
| `ServiceCategory` | `service_categories` | `hasMany(Service)` |
| `TrekDetail` | `trek_details` | `belongsTo(Service)` |
| `TourDetail` | `tour_details` | `belongsTo(Service)` |
| `HotelDetail` | `hotel_details` | `belongsTo(Service)` |
| `Booking` | `bookings` | `belongsTo(Trekker)`, `belongsTo(Trek)`, `belongsTo(User)`, `belongsTo(Service)`, `hasMany(QrScan)`, `hasMany(Review)` |
| `QrScan` | `qr_scans` | `belongsTo(Booking)` |
| `SosAlert` | `sos_alerts` | `belongsTo(Trekker)`, `belongsTo(Booking)` |
| `Review` | `reviews` | `belongsTo(Booking)`, `belongsTo(User)`, `belongsTo(Service)` |
| `Notification` | `notifications` | polymorphic `notifiable` |
| `Plan` | `plans` | `hasMany(Subscription)` |
| `Subscription` | `subscriptions` | `belongsTo(Provider)`, `belongsTo(Plan)` |
| `Payment` | `payments` | polymorphic `payable` |
| `VerificationDocument` | `verification_documents` | `belongsTo(Provider)` |
| `Location` | `locations` | `hasMany(Service)` |
| `Invoice` | `invoices` | `belongsTo(Provider)`, `belongsTo(Subscription)`, `belongsTo(Booking)` |
| `Waypoint` | `waypoints` | `hasMany(RouteSegment, from_waypoint_id)`, `hasMany(RouteSegment, to_waypoint_id)`, `hasMany(ItineraryDay, overnight_waypoint_id)` |
| `Route` | `routes` | `hasMany(RouteSegment)`, `hasMany(RouteCost)`, `hasMany(PlannerRequest)` |
| `RouteSegment` | `route_segments` | `belongsTo(Route)`, `belongsTo(Waypoint, from_waypoint_id)`, `belongsTo(Waypoint, to_waypoint_id)` |
| `RouteCost` | `route_costs` | `belongsTo(Route)` |
| `PlannerRequest` | `planner_requests` | `belongsTo(User)`, `belongsTo(Route)`, `hasOne(PlannerResult)` |
| `PlannerResult` | `planner_results` | `belongsTo(PlannerRequest)`, `hasMany(ItineraryDay)` |
| `ItineraryDay` | `itinerary_days` | `belongsTo(PlannerResult)`, `belongsTo(Waypoint, overnight_waypoint_id)`, `hasMany(ItineraryItem)` |
| `ItineraryItem` | `itinerary_items` | `belongsTo(ItineraryDay)`, `belongsTo(Service)` |

---

## 7. Current Routes Audit (After Phases 1-12 + AI Planner + Enhancements)

### 7.1 Public Routes

| Method | URI | Controller | Purpose |
| :--- | :--- | :--- | :--- |
| GET | `/` | `HomeController` | Homepage |
| GET | `/services` | `PublicServiceController@index` | Service listing |
| GET | `/services/{slug}` | `PublicServiceController@show` | Service detail |
| GET | `/providers` | `PublicProviderController@index` | Provider directory |
| GET | `/providers/{slug}` | `PublicProviderController@show` | Provider profile |
| GET | `/pricing` | `PublicPricingController` | Pricing page |
| GET | `/sitemap.xml` | `SitemapController` | Dynamic sitemap |
| GET | `/robots.txt` | static | Robots directives |

### 7.2 Auth Routes

| Method | URI | Controller | Purpose |
| :--- | :--- | :--- | :--- |
| GET | `/login` | `Auth\LoginController@showLoginForm` | Login page |
| POST | `/login` | `Auth\LoginController@login` | Login |
| GET | `/register` | `Auth\RegisterController@showRegistrationForm` | Register page |
| POST | `/register` | `Auth\RegisterController@register` | Register |
| GET | `/logout` | `Auth\LoginController@logout` | Logout |

### 7.3 API Routes

| Method | URI | Controller | Purpose |
| :--- | :--- | :--- | :--- |
| POST | `/api/planner/generate` | `PlannerController@generate` | AI itinerary generation |
| POST | `/api/booking/checkin` | `Api\BookingController@checkin` | QR check-in |
| POST | `/api/sos/create` | `Api\SosController@create` | SOS alert |

---

## 8. Working Features (Confirmed – All Phases 1-12 + AI Planner + Enhancements)

- ✅ AI itinerary generation (ABC, EBC, Langtang) – grounded + fallback
- ✅ Public listing of services with filters
- ✅ Service detail page with provider info and rating
- ✅ Provider profile page
- ✅ Guest booking with QR code generation
- ✅ Booking confirmation page
- ✅ QR check‑in (page and scan recording)
- ✅ SOS alert creation and email notification (queued)
- ✅ User login/register (NEW)
- ✅ Provider dashboard (NEW)
- ✅ Service CRUD (NEW)
- ✅ Booking management (NEW)
- ✅ Policies for Services, Bookings, and Reviews
- ✅ Pricing page – NPR-only + Billing Toggle
- ✅ Subscription management – Monthly/Yearly
- ✅ Provider verification
- ✅ Payment integration with Stripe – Multi-Currency
- ✅ Reviews & Ratings
- ✅ Notifications (booking, review)
- ✅ Traveler Dashboard – Modern redesign
- ✅ AI Service Recommendations
- ✅ Content Analysis
- ✅ SOS SMS (skip mode)
- ✅ Provider Analytics Dashboard
- ✅ Admin Analytics Dashboard
- ✅ PWA Manifest & Service Worker
- ✅ Offline Fallback View
- ✅ Provider Directory
- ✅ Provider Type Dropdown
- ✅ Monthly/Yearly Billing Toggle
- ✅ Multi-Currency (USD/NPR)
- ✅ Account Type Registration
- ✅ Provider Check-in Management
- ✅ Traveler Trek History
- ✅ QR Code in Traveler Booking Detail
- ✅ **SEO Meta Tags, Open Graph, Twitter Cards, sitemap.xml, robots.txt**
- ✅ **High‑Resolution Favicon (multiple sizes)**
- ✅ **Logo in Admin, Provider, Traveler Dashboards**
- ✅ **Logo on Login and Register pages**
- ✅ **.htaccess Cache Control for static assets**
- ✅ **Invoice table, model, and service class**

---

## 9. Phase-by-Phase Roadmap (Final)

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
| **Phase 12.5** | **UI/UX & SEO Enhancements** | ✅ **COMPLETED** |
| **Phase 13** | **Invoice & Billing System** | ⏳ Planned |

---

## 10. NOW vs NEXT vs LATER (Final)

| Category | Features | Status |
|----------|----------|--------|
| **NOW** (Phases 1-12 + 12.5) | All core features + PWA + Provider Directory + 12 Business Types + Multi-Currency + Traveler Dashboard + Check-in Management + SEO + High-res Favicon + Logo in Dashboards + Invoice Foundation + **AI Planner (Grounded)** + **Fallback** | ✅ COMPLETED |
| **NEXT** | Testing, Deployment, Monitoring | ⏳ In Progress |
| **LATER** | Messaging, Native Mobile Apps, Advanced Reporting, Multi-language, Full Invoice System | ⏳ Future |

---

## 11. Go / No‑Go Checklist (Final)

### ✅ COMPLETED

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
| Provider Check-in Management | ✅ |
| Traveler Trek History | ✅ |
| QR Code in Booking Detail | ✅ |
| **AI Planner (ABC/EBC/Langtang) – Grounded** | ✅ |
| **Fallback Itinerary Mechanism** | ✅ |
| **Cost Calculation (Backend – NPR)** | ✅ |
| **Service Integration (Phase 2)** | ✅ |
| **SEO Meta Tags, Open Graph, Twitter Cards** | ✅ |
| **Sitemap.xml & robots.txt** | ✅ |
| **High‑Resolution Favicon (multiple sizes)** | ✅ |
| **Logo in Admin, Provider, Traveler Dashboards** | ✅ |
| **Logo on Login/Register pages** | ✅ |
| **.htaccess Cache Control** | ✅ |
| **Invoice table, model, service** | ✅ |
| Legacy cleanup ready | ✅ |
| Gradual migration approach | ✅ |

### ⏳ PENDING (Future)

| Element | Status |
|---------|--------|
| Delete legacy files (after testing) | 🧹 Optional |
| SMS real credentials | ⏳ Future |
| Full Invoice & Receipt System (UI, PDF, email) | ⏳ Phase 13 |
| Multi-language Support | ⏳ Future |
| Native Mobile App | ⏳ Future |

---

**End of Master Document (FINAL – Phases 1-12 + Phase 12.5 UI/UX & SEO + AI Planner Grounded + Fallback + Invoice Foundation)**
```

---

## 🚀 **अब File Update गर्नुहोस्:**

```bash
# पुरानो file हटाउनुहोस् (अथवा replace गर्नुहोस्)
# माथिको content लाई docs/master_planning_and_all.md मा पेस्ट गर्नुहोस्

# Git add/commit/push
git add docs/master_planning_and_all.md
git commit -m "Update: Master blueprint to v10.3 with AI Planner (ABC/EBC/Langtang), fallback, cost calculation, service integration"
git push origin main
```

---

**Bro, अब यो file पूरा update भयो – AI Planner को सबै काम, fallback, cost calculation, service integration सबै documented छ।** 🏔️💻