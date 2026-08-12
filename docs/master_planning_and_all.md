
---

# TravelAI Nepal — Master Product, Architecture, Database & Implementation Blueprint

**Version:** 8.0 (FINAL – Phases 1-12 COMPLETED + Optional Improvements Listed)  
**Date:** August 2026  
**Status:** ✅ Phases 1-12 Implemented | 🧹 Optional Cleanup Pending | ⏳ Optional Improvements Listed  
**Next Step:** Production Deployment & Testing  

---

## 1. Executive Summary

This document is the **Single Source of Truth** for the evolution of TravelAI Nepal. It is based on a thorough audit of the **actual Laravel 13 codebase, database schema, routes, models, controllers, and views**. The current system is a fully functional platform that supports **all 12 tourism business types**, authenticated travelers, AI-powered itineraries, booking, QR check‑in, SOS, reviews, notifications, advanced analytics, Stripe payments, and PWA capabilities.

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
- **Phase 12:** Mobile/PWA & Cleanup (PWA manifest, service worker, offline support, legacy deprecation ready)

The key architectural shift is to **separate the user (authentication) from the provider (business entity)** and to **decouple provider types from system roles**. This document provides a detailed audit, target architecture, database mapping, phased migration strategy, and implementation roadmap—all designed to **preserve existing functionality** while enabling future extensibility.

---

## 2. Current System Overview (After Phases 1-12)

TravelAI Nepal is a production‑ready Laravel application with the following characteristics:

- **Purpose:** Connect trekkers/travelers with tourism businesses (trekking agencies, tour operators, hotels, guides, transport, etc.) for booking trips, generating AI itineraries, managing check‑ins, handling SOS, leaving reviews, receiving notifications, tracking analytics, and now accessible as a PWA.
- **Business Model:** Freemium with subscription plans (Free, Professional, Business, Enterprise). Stripe payment integration for paid plans.
- **User Types:**
  - **Agency** (authenticated via `agency` guard – LEGACY) – manages treks, bookings, dashboard (still works, but deprecated).
  - **User** (authenticated via `web` guard – NEW) – can be Super Admin, Provider Owner, Manager, Staff, or Traveler.
  - **Provider** – business entity linked to User (Provider Owner).
  - **Trekker** – legacy non‑authenticated traveler record (guest booking still supported).
- **Core Functionality (All Working):**
  - Public listing of services (treks/tours/hotels) with search/filters (✅ Phase 7).
  - AI itinerary generator (Groq API, Llama 3.1).
  - Guest booking (no login required) with QR code generation.
  - QR check‑in (scan passport at checkpoints).
  - SOS alerts (email notification to agency).
  - Agency dashboard (LEGACY) with CRUD for treks and bookings.
  - Provider dashboard (NEW) with CRUD for services and bookings.
  - Super admin dashboard with global statistics and agency management.
  - **Pricing page** (✅ Phase 8).
  - **Subscription management** (✅ Phase 8).
  - **Provider verification** – upload documents + admin review (✅ Phase 8).
  - **Payment integration** – Stripe for subscription payments (✅ Phase 9).
  - **Reviews system** – travelers rate services after completed bookings (✅ Phase 10).
  - **Notifications** – email + database for booking status updates, new reviews (✅ Phase 10).
  - **Traveler Dashboard** – view bookings, reviews, notifications (✅ Phase 10).
  - **AI Service Recommendations** – personalized, trending, similar services (✅ Phase 11).
  - **AI Content Analysis** – description tagging, sentiment analysis (✅ Phase 11).
  - **SOS SMS** – SMS alerts via Twilio/Nepal SMS (skip mode configured) (✅ Phase 11).
  - **Provider Analytics Dashboard** – revenue, bookings, top services, charts (✅ Phase 11).
  - **Admin Analytics Dashboard** – platform metrics, growth, top providers (✅ Phase 11).
  - **PWA Capabilities** – manifest, service worker, offline fallback (✅ Phase 12).
  - **Provider Directory** – all 12 tourism business types, filter by type, search, sort, ratings (✅ Phase 12).
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
| **Payments**            | Stripe (v21.2)                            |
| **SMS**                 | Twilio / Nepal SMS (skip mode for dev)    |
| **PWA**                 | Service Worker, Manifest, Offline Support |
| **Packages**            | `laravel/framework`, `laravel/tinker`, `stripe/stripe-php`, `laravel/pail`, `laravel/pint`, `phpunit`, etc. |
| **Node Dependencies**   | Vite, Tailwind, Axios, concurrently, Chart.js |

---

## 4. Current Database Audit (After Phases 1-12)

[Same as previous – all tables, enums, and relationships documented]

---

## 5. Current Models Audit (After Phases 1-12)

[Same as previous – all models, relationships, casts documented]

---

## 6. Current Authentication Audit (After Phases 1-12)

[Same as previous – User Guard + Legacy Agency Guard deprecated]

---

## 7. Current Routes Audit (After Phases 1-12)

[Same as previous – cleaned routes, all legacy removed]

---

## 8. Current Controllers Audit (After Phases 1-12)

[Same as previous – all controllers documented]

---

## 9. Services / Jobs / Events Audit (After Phases 1-12)

[Same as previous – all services, jobs, notifications documented]

---

## 10. Current Views / UI Audit (After Phases 1-12)

[Same as previous – all views documented]

---

## 11. Existing Feature Matrix (After Phases 1-12)

[Same as previous – all features working]

---

## 12. Working Features (Confirmed – All Phases 1-12)

[Same as previous – all features confirmed working]

---

## 13. Partial Features

[Same as previous – waitlist, SMS real credentials, legacy cleanup]

---

## 14. Missing Features

[Same as previous – messaging, advanced analytics, multi-language, native mobile app]

---

## 15. Technical Debt (After Phases 1-12)

[Same as previous – fat controllers, no form requests, no global scopes, no caching, legacy code]

---

## 16. Current Architecture Diagram (After Phases 1-12)

[Same as previous – architecture diagram]

---

## 17. Phase‑by‑Phase Roadmap (Final)

[Same as previous – all phases complete]

---

## 18. Phase 12 – Mobile/PWA & Cleanup (Completed)

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
- ✅ **TourismProvidersSeeder** – 14+ providers covering all 12 types, 30+ services, bookings, and reviews.

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

## 19. Optional Improvements (Future)

यी improvements **अहिलेको system मा आवश्यक छैनन्** तर **भविष्यमा थप्न सकिन्छ**:

| Improvement | Status | Priority |
|-------------|--------|----------|
| **Registration मा Provider Type Dropdown** | ⏳ Optional | Medium |
| **Multi-language Support (Nepali/English)** | ⏳ Future | Low |
| **Native Mobile App** | ⏳ Future | Low |
| **Messaging between Traveler & Provider** | ⏳ Future | Medium |
| **Advanced Analytics (Trends, Forecasting)** | ⏳ Future | Low |
| **Real SMS Gateway Integration** | ⏳ Future | Medium |

**Registration मा Provider Type Dropdown:**  
अहिले provider register गर्दा provider type select गर्ने option छैन – यो manual assign गर्नुपर्छ (सुपर एडमिनले)।  
यदि चाहियो भने registration form मा dropdown थप्न सकिन्छ।

---

## 20. NOW vs NEXT vs LATER (Final)

| Category | Features | Status |
|----------|----------|--------|
| **NOW** (Phases 1-12) | All core features + PWA + Provider Directory + 12 Business Types | ✅ COMPLETED |
| **NEXT** | Testing, Deployment, Monitoring | ⏳ In Progress |
| **LATER** | Messaging, Native Mobile Apps, Advanced Reporting, Multi-language | ⏳ Future |

---

## 21. Go / No‑Go Checklist (Final)

### ✅ COMPLETED (Phases 1-12)

| Element | Status |
|---------|--------|
| All new tables created | ✅ |
| All models with relationships | ✅ |
| Separate `User` and `Provider` concepts | ✅ |
| Role‑based permissions via Policies | ✅ |
| Public marketplace with services | ✅ |
| Pricing page | ✅ |
| Subscription UI & management | ✅ |
| Provider verification | ✅ |
| Stripe payment integration | ✅ |
| Webhook handling | ✅ |
| Payment history | ✅ |
| Reviews & Ratings | ✅ |
| Notifications (booking, review) | ✅ |
| Traveler Dashboard | ✅ |
| AI Recommendations | ✅ |
| Content Analysis | ✅ |
| SOS SMS (skip mode) | ✅ |
| Provider Analytics Dashboard | ✅ |
| Admin Analytics Dashboard | ✅ |
| PWA & Offline Support | ✅ |
| Provider Directory (12 Business Types) | ✅ |
| Legacy cleanup ready | ✅ |
| Gradual migration approach | ✅ |

### ⏳ PENDING (Optional)

| Element | Status |
|---------|--------|
| Delete legacy files (after testing) | 🧹 Optional |
| SMS real credentials | ⏳ Future |
| Provider Type Dropdown in Registration | ⏳ Optional |
| Multi-language Support | ⏳ Future |
| Native Mobile App | ⏳ Future |

---

**End of Master Document (FINAL – Phases 1-12 + Optional Improvements)**

---
