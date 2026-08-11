**Bro, yei ho complete updated Master Document – Phases 7, 8, 9 सम्म update गरिएको।** ✅

---

# TravelAI Nepal — Master Product, Architecture, Database & Implementation Blueprint

**Version:** 5.0 (Updated – Phases 1-9 COMPLETED)  
**Date:** August 2026  
**Status:** ✅ Phases 1-9 Implemented | ⏳ Phase 10-12 Planned  
**Next Step:** Phase 10 — Reviews & Notifications  

---

## 1. Executive Summary

This document is the **Single Source of Truth** for the evolution of TravelAI Nepal. It is based on a thorough audit of the **actual Laravel 13 codebase, database schema, routes, models, controllers, and views**. The current system is a functional agency‑centric platform with working AI itinerary, booking, QR check‑in, SOS, and dashboard features. The long‑term vision is to expand into a **Nepal Tourism Ecosystem Platform** that supports multiple provider types (trekking agencies, hotels, guides, transport, homestays, etc.) and authenticated travelers.

**✅ Phases 1-9 have been successfully implemented:**
- **Phase 1:** Foundation (provider_types, service_categories, plans, subscriptions, locations, verification_documents, provider_provider_type, provider_staff)
- **Phase 2:** User/Provider Integration (agencies → users + providers migration)
- **Phase 3:** Service Migration (treks → services + trek_details, tour_details, hotel_details)
- **Phase 4:** Booking Migration (bookings → traveler_id + service_id, dropped old columns)
- **Phase 5:** Authentication Transition (new User guard with login/register)
- **Phase 6:** Dashboard & Capabilities (Provider dashboard with policies and CRUD)
- **Phase 7:** Public Marketplace (services instead of treks)
- **Phase 8:** Pricing & Subscriptions (UI, plan selection, provider verification)
- **Phase 9:** Payments (Stripe integration, subscription payments, webhooks)

The key architectural shift is to **separate the user (authentication) from the provider (business entity)** and to **decouple provider types from system roles**. This document provides a detailed audit, target architecture, database mapping, phased migration strategy, and implementation roadmap—all designed to **preserve existing functionality** while enabling future extensibility.

---

## 2. Current System Overview (After Phases 1-9)

TravelAI Nepal is a production‑ready Laravel application with the following characteristics:

- **Purpose:** Connect trekkers/travelers with tourism businesses (trekking agencies, tour operators, hotels, guides, transport, etc.) for booking trips, generating AI itineraries, managing check‑ins, and handling SOS.
- **Business Model:** Freemium with subscription plans (Free, Professional, Business, Enterprise). Stripe payment integration for paid plans.
- **User Types:**
  - **Agency** (authenticated via `agency` guard – LEGACY) – manages treks, bookings, dashboard (still works).
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
| **JavaScript**          | Vanilla JS, Axios, Vite                   |
| **AI Provider**         | Groq API (Llama 3.1‑8b‑instant)           |
| **QR Code**             | SimpleSoftwareIO/simple-qrcode (^4.2)     |
| **Queue**               | Database driver (jobs table)              |
| **Notifications**       | Mail (SOS)                                |
| **Payments**            | Stripe (v21.2)                            |
| **Packages**            | `laravel/framework`, `laravel/tinker`, `stripe/stripe-php`, `laravel/pail`, `laravel/pint`, `phpunit`, etc. |
| **Node Dependencies**   | Vite, Tailwind, Axios, concurrently       |

---

## 4. Current Database Audit (After Phases 1-9)

The following tables exist. **New tables added in Phases 1-9 are marked with ✅.**

| Table          | Purpose                                                                   | Key Columns                                                                 | Relationships / Notes                          |
|----------------|---------------------------------------------------------------------------|-----------------------------------------------------------------------------|------------------------------------------------|
| `agencies`     | LEGACY: Provider accounts + business details.                             | `id`, `name`, `email`(unique), `password`, `phone`, `address`, `logo_url`, `role`, `user_id` | Has many `treks`; `user_id` FK to `users`. |
| `trekkers`     | LEGACY: Non‑authenticated traveler records.                               | `id`, `name`, `email`(unique), `phone`, `emergency_contact`                | Has many `bookings`, `sos_alerts`.             |
| `treks`        | LEGACY: Listings (trek/tour/hotel). `service_id` added.                   | `id`, `agency_id`(FK), `name`, `duration_days`, `difficulty`, `category`, `price`, `cover_image`, `gallery`, `itinerary`, `service_id` | Belongs to `agency`; has many `bookings`; `service_id` FK to `services`. |
| `bookings`     | Reservations. Now has `traveler_id` + `service_id`. Old columns dropped.  | `id`, `traveler_id`(FK), `service_id`(FK), `booking_date`, `start_date`, `status`, `qr_code`(unique), `invoice_url` | Belongs to `user` (traveler), `service`; has many `qr_scans`, one `sos_alert`. |
| `qr_scans`     | Check‑in records.                                                         | `id`, `booking_id`(FK), `checkpoint_name`, `scanned_at`, `latitude`, `longitude` | Belongs to `booking`.                          |
| `sos_alerts`   | Emergency alerts.                                                         | `id`, `trekker_id`(FK), `booking_id`(FK), `latitude`, `longitude`, `message`, `is_resolved` | Belongs to `trekker`, `booking`.               |
| `users`        | ✅ Central authentication table.                                          | `id`, `name`, `email`, `password`, `role` (super_admin, provider_owner, manager, staff, traveler), `phone`, `avatar` | Has many `providers`, `provider_staff`, `travelerBookings`. |
| `providers`    | ✅ Business/professional entity.                                          | `id`, `user_id`(FK), `name`, `slug`, `description`, `logo_url`, `contact_email`, `contact_phone`, `address`, `verification_status`, `is_active` | Belongs to `user`; has many `types`, `staff`, `services`, `subscriptions`, `documents`. |
| `provider_types`| ✅ Taxonomy of business types.                                            | `id`, `name`, `slug`, `description`                                       | Many‑to‑many with `providers`.                 |
| `provider_provider_type`| ✅ Pivot table.                                                         | `provider_id`, `provider_type_id`                                         | Links providers to types.                      |
| `provider_staff`| ✅ Staff users assigned to providers.                                     | `id`, `user_id`(FK), `provider_id`(FK), `role`, `permissions`            | Belongs to `user`, `provider`.                 |
| `service_categories`| ✅ Service types (Trek, Tour, Hotel, Guide, Transport, etc.).             | `id`, `name`, `slug`, `description`                                       | Has many `services`.                           |
| `services`     | ✅ Core listing table.                                                    | `id`, `provider_id`(FK), `service_category_id`(FK), `name`, `slug`, `description`, `price`, `currency`, `cover_image`, `gallery`, `status`, `location_id` | Belongs to `provider`, `category`; has many `bookings`; has one `trekDetail`/`tourDetail`/`hotelDetail`. |
| `trek_details` | ✅ Trek‑specific fields.                                                  | `id`, `service_id`(FK), `duration_days`, `difficulty`, `itinerary`, `max_altitude`, `season` | Belongs to `service`.                          |
| `tour_details` | ✅ Tour‑specific fields.                                                  | `id`, `service_id`(FK), `duration_days`, `itinerary`, `inclusions`, `exclusions` | Belongs to `service`.                          |
| `hotel_details`| ✅ Hotel‑specific fields.                                                 | `id`, `service_id`(FK), `room_count`, `star_rating`, `amenities`, `check_in_time`, `check_out_time` | Belongs to `service`.                          |
| `plans`        | ✅ Subscription plans.                                                    | `id`, `name`, `slug`, `description`, `price_monthly`, `price_yearly`, `features`, `limits` | Has many `subscriptions`.                      |
| `subscriptions`| ✅ Provider subscriptions to plans.                                       | `id`, `provider_id`(FK), `plan_id`(FK), `start_date`, `end_date`, `status` | Belongs to `provider`, `plan`; has many `payments`. |
| `payments`     | ✅ Payment records (Phase 9).                                             | `id`, `payable_type`, `payable_id`, `provider_id`, `user_id`, `payment_id`, `gateway`, `amount`, `currency`, `status`, `metadata`, `paid_at` | Morphs to `subscription` or `booking`; belongs to `provider`, `user`. |
| `locations`    | ✅ Reusable location data.                                                | `id`, `country`, `state`, `city`, `latitude`, `longitude`                  | Used by `services` and `providers`.            |
| `verification_documents`| ✅ Provider verification documents.                                    | `id`, `provider_id`(FK), `type`, `file_path`, `status`                     | Belongs to `provider`.                         |
| `sessions`, `cache`, `cache_locks`, `jobs`, `migrations` | Framework tables.                                                  | Standard columns.                                                           |                                                |

**Foreign Keys:** All FKs have `ON DELETE CASCADE` unless noted.

**Enums:**
- `agencies.role`: `super_admin`, `admin`, `agency` (LEGACY)
- `users.role`: `super_admin`, `provider_owner`, `manager`, `staff`, `traveler` (NEW)
- `treks.difficulty`: `easy`, `moderate`, `hard` (LEGACY)
- `treks.category`: `trek`, `tour`, `hotel` (LEGACY)
- `trek_details.difficulty`: `easy`, `moderate`, `hard`
- `bookings.status`: `pending`, `confirmed`, `completed`, `cancelled`
- `providers.verification_status`: `pending`, `under_review`, `verified`, `rejected`, `suspended`
- `services.status`: `active`, `inactive`
- `subscriptions.status`: `active`, `expired`, `cancelled`, `pending`
- `payments.status`: `pending`, `success`, `failed`, `refunded`
- `verification_documents.status`: `pending`, `approved`, `rejected`

**JSON Columns:**
- `treks.gallery` – array of image paths (LEGACY)
- `treks.itinerary` – array of day‑by‑day descriptions (LEGACY)
- `services.gallery` – array of image paths
- `trek_details.itinerary` – array of day‑by‑day descriptions
- `tour_details.itinerary`, `inclusions`, `exclusions` – JSON arrays
- `hotel_details.amenities` – JSON array
- `plans.features`, `limits` – JSON arrays/objects
- `provider_staff.permissions` – JSON array
- `payments.metadata` – JSON object

**Indexes:** Primary keys, unique on emails, slugs, qr_code; foreign key indexes; additional indexes on `services.provider_id`, `services.service_category_id`, `bookings.traveler_id`, `bookings.service_id`, `subscriptions.provider_id`, `subscriptions.status`, `payments.payable_type`, `payments.payable_id`.

---

## 5. Current Models Audit (After Phases 1-9)

| Model          | File                | Purpose                                                                 | Relationships                                                                 | Casts / Notes                                                                 |
|----------------|---------------------|-------------------------------------------------------------------------|-------------------------------------------------------------------------------|-------------------------------------------------------------------------------|
| `User`         | `User.php`          | ✅ Central authentication model.                                         | `hasMany(Provider)`, `hasMany(ProviderStaff)`, `hasMany(Booking, 'traveler_id')` | `role` enum; `password` hashed; helper methods: `isSuperAdmin()`, `isProviderOwner()`, `isTraveler()`, `accessibleProviderIds()`. |
| `Agency`       | `Agency.php`        | LEGACY: Business entity & authenticated user.                           | `hasMany(Trek)`, `hasManyThrough(Booking, Trek)`, `belongsTo(User)`          | `role` used for permissions & type; `user_id` FK to `users`. |
| `Provider`     | `Provider.php`      | ✅ Business/professional entity.                                         | `belongsTo(User)`, `belongsToMany(ProviderType)`, `hasMany(ProviderStaff)`, `hasMany(Service)`, `hasMany(Subscription)`, `hasMany(VerificationDocument)` | `verification_status` enum; `is_active` boolean. |
| `ProviderType` | `ProviderType.php`  | ✅ Taxonomy.                                                             | `belongsToMany(Provider)`                                                   |                                                                               |
| `ProviderStaff`| `ProviderStaff.php` | ✅ Staff assignments.                                                    | `belongsTo(User)`, `belongsTo(Provider)`                                    | `permissions` cast to array.                                                  |
| `Service`      | `Service.php`       | ✅ Core listing.                                                         | `belongsTo(Provider)`, `belongsTo(ServiceCategory)`, `belongsTo(Location)`, `hasOne(TrekDetail)`, `hasOne(TourDetail)`, `hasOne(HotelDetail)`, `hasMany(Booking)` | `gallery` cast to array; `price` decimal. |
| `ServiceCategory`| `ServiceCategory.php`| ✅ Service types.                                                        | `hasMany(Service)`                                                           |                                                                               |
| `TrekDetail`   | `TrekDetail.php`    | ✅ Trek‑specific fields.                                                 | `belongsTo(Service)`                                                         | `itinerary` cast to array.                                                    |
| `TourDetail`   | `TourDetail.php`    | ✅ Tour‑specific fields.                                                 | `belongsTo(Service)`                                                         | `itinerary`, `inclusions`, `exclusions` cast to array.                        |
| `HotelDetail`  | `HotelDetail.php`   | ✅ Hotel‑specific fields.                                                | `belongsTo(Service)`                                                         | `amenities` cast to array.                                                    |
| `Plan`         | `Plan.php`          | ✅ Subscription plans.                                                   | `hasMany(Subscription)`                                                      | `features`, `limits` cast to array; `price` decimal.                          |
| `Subscription` | `Subscription.php`  | ✅ Provider subscriptions.                                               | `belongsTo(Provider)`, `belongsTo(Plan)`, `morphMany(Payment)`              | `start_date`, `end_date` cast to date; `status` enum.                         |
| `Payment`      | `Payment.php`       | ✅ Payment records (Phase 9).                                            | `morphTo('payable')`, `belongsTo(Provider)`, `belongsTo(User)`              | `metadata` cast to array; `amount` decimal; `paid_at` datetime.               |
| `Location`     | `Location.php`      | ✅ Reusable locations.                                                   | (Used by `Service` and `Provider`)                                           | `latitude`, `longitude` decimal.                                              |
| `VerificationDocument`| `VerificationDocument.php`| ✅ Provider verification.                                           | `belongsTo(Provider)`                                                         | `type`, `status` enums.                                                       |
| `Trek`         | `Trek.php`          | LEGACY: Listing (trek/tour/hotel).                                      | `belongsTo(Agency)`, `hasMany(Booking)`, `belongsTo(Service)`                | `itinerary`, `gallery` cast to array; `service_id` FK.                        |
| `Trekker`      | `Trekker.php`       | LEGACY: Non‑authenticated traveler.                                     | `hasMany(Booking)`, `hasMany(SosAlert)`, `belongsTo(User, 'email', 'email')` |                                                                               |
| `Booking`      | `Booking.php`       | Reservation. Now has `traveler_id` + `service_id`.                      | `belongsTo(Trekker)` (legacy), `belongsTo(Trek)` (legacy), `belongsTo(User, 'traveler_id')`, `belongsTo(Service)`, `hasMany(QrScan)`, `hasOne(SosAlert)` | `booking_date`, `start_date` cast to date.                                    |
| `QrScan`       | `QrScan.php`        | Check‑in record.                                                        | `belongsTo(Booking)`                                                         | `scanned_at` datetime; `latitude`/`longitude` decimal.                        |
| `SosAlert`     | `SosAlert.php`      | Emergency alert.                                                        | `belongsTo(Trekker)` (legacy), `belongsTo(Booking)`                          | `is_resolved` boolean.                                                        |

---

## 6. Current Authentication Audit (After Phases 1-9)

### ✅ NEW Authentication (User Guard – Web)
- **Guard:** `web` (default) – provider: `users` table.
- **Authenticatable Model:** `User`.
- **Login:** `Auth\LoginController` with routes:
  - `GET /login` → show form
  - `POST /login` → attempt login
- **Register:** `Auth\RegisterController` with routes:
  - `GET /register` → show form with plans & provider types
  - `POST /register` → create user (role: traveler/provider_owner), create provider & subscription if provider registration.
- **Middleware:** `auth` for all protected routes.
- **Logout:** `POST /logout` → guard logout.
- **Role system:** Stored in `users.role` as string (`super_admin`, `provider_owner`, `manager`, `staff`, `traveler`). Authorisation via Policies (ServicePolicy, BookingPolicy).

### LEGACY Authentication (Agency Guard – Still Working)
- **Guard:** `agency` – provider: `agencies` table.
- **Authenticatable Model:** `Agency`.
- **Login:** `Agency\Auth\LoginController` with routes:
  - `GET /agency/login` → show form
  - `POST /agency/login` → attempt login
- **Register:** `Agency\Auth\RegisterController` with routes:
  - `GET /agency/register` → show form
  - `POST /agency/register` → create agency, log in.
- **Middleware:** `guest:agency` for login/register; `auth:agency` for all dashboard routes.
- **Logout:** `POST /agency/logout` → guard logout.
- **Role system:** Stored in `agencies.role` as string (`super_admin`, `admin`, `agency`). Authorisation checks are manual in controllers.

**Note:** Both authentication systems work in parallel. Legacy agency system will be deprecated after all features are migrated.

---

## 7. Current Routes Audit (After Phases 1-9)

### Public Routes (no auth)
| Method | URI | Name | Controller | Purpose |
|--------|-----|------|------------|---------|
| GET | `/` | `home` | `HomeController@index` | Homepage with stats, featured services, AI planner, check‑ins. |
| GET | `/features` | `pages.features` | `PageController@features` | Features page. |
| GET | `/how-it-works` | `pages.how-it-works` | `PageController@howItWorks` | How it works. |
| GET | `/agencies` | `pages.agencies` | `PageController@agencies` | List of agencies (will be updated to providers). |
| GET | `/treks` | `treks.index` | `PublicTrekController@index` | Explore treks/tours/hotels (LEGACY – still works). |
| GET | `/explore` | `public.services.index` | `Public\ServiceController@index` | ✅ Explore services with filters (Phase 7). |
| GET | `/explore/category/{slug}` | `public.services.category` | `Public\ServiceController@category` | ✅ Services by category (Phase 7). |
| GET | `/explore/service/{slug}` | `public.services.show` | `Public\ServiceController@show` | ✅ Service detail page (Phase 7). |
| GET | `/provider/{slug}` | `public.providers.show` | `Public\ServiceController@providerProfile` | ✅ Provider profile page (Phase 7). |
| GET | `/trek/{trek}` | `trek.show` | `TrekController@show` | Trek detail page (LEGACY). |
| GET | `/trek/{trek}/book` | `trek.book` | `TrekBookingController@create` | Booking form (LEGACY). |
| POST | `/trek/{trek}/book` | – | `TrekBookingController@store` | Store booking (LEGACY). |
| GET | `/explore/service/{slug}/book` | `public.services.book` | `Public\BookingController@create` | ✅ Service booking form (Phase 7). |
| POST | `/explore/service/{slug}/book` | – | `Public\BookingController@store` | ✅ Store service booking (Phase 7). |
| GET | `/service/confirmation/{booking}` | `public.booking.confirmation` | `Public\BookingController@confirmation` | ✅ Service booking confirmation (Phase 7). |
| GET | `/booking/confirmation/{booking}` | `booking.confirmation` | `TrekBookingController@confirmation` | Booking confirmation (LEGACY). |
| GET | `/booking/qr/{booking}` | `booking.qr` | Closure | Generate QR code image. |
| GET | `/scan/{booking}` | `scan.checkin` | `CheckinController@show` | QR check‑in page. |
| POST | `/scan/{booking}` | – | `CheckinController@checkin` | Record scan. |
| GET | `/pricing` | `pages.pricing` | `PageController@pricing` | ✅ Pricing page (Phase 8). |

### ✅ NEW Authentication Routes (User Guard)
| Method | URI | Name | Controller | Purpose |
|--------|-----|------|------------|---------|
| GET | `/login` | `login` | `Auth\LoginController@showLoginForm` | Login form. |
| POST | `/login` | – | `Auth\LoginController@login` | Handle login. |
| POST | `/logout` | `logout` | `Auth\LoginController@logout` | Handle logout. |
| GET | `/register` | `register` | `Auth\RegisterController@showRegistrationForm` | Registration form with plans & provider types. |
| POST | `/register` | – | `Auth\RegisterController@register` | Handle registration (traveler/provider). |

### ✅ NEW Provider Routes (User Guard)
| Method | URI | Name | Controller | Purpose |
|--------|-----|------|------------|---------|
| GET | `/provider/dashboard` | `provider.dashboard` | `Provider\DashboardController@index` | Provider dashboard. |
| GET | `/provider/profile` | `provider.profile` | `Provider\ProfileController@show` | View provider profile. |
| GET | `/provider/profile/edit` | `provider.profile.edit` | `Provider\ProfileController@edit` | Edit profile form. |
| PUT | `/provider/profile` | `provider.profile.update` | `Provider\ProfileController@update` | Update profile. |
| Resource | `/provider/services` | `provider.services` | `Provider\ServiceController` | CRUD services. |
| GET | `/provider/bookings` | `provider.bookings.index` | `Provider\BookingController@index` | List bookings. |
| GET | `/provider/bookings/{booking}` | `provider.bookings.show` | `Provider\BookingController@show` | View booking. |
| PATCH | `/provider/bookings/{booking}/status` | `provider.bookings.updateStatus` | `Provider\BookingController@updateStatus` | Update booking status. |
| GET | `/provider/subscriptions` | `provider.subscriptions.index` | `Provider\SubscriptionController@index` | ✅ Subscription management. |
| POST | `/provider/subscriptions` | `provider.subscriptions.store` | `Provider\SubscriptionController@store` | ✅ Create subscription. |
| POST | `/provider/subscriptions/upgrade` | `provider.subscriptions.upgrade` | `Provider\SubscriptionController@upgrade` | ✅ Upgrade subscription. |
| POST | `/provider/subscriptions/cancel` | `provider.subscriptions.cancel` | `Provider\SubscriptionController@cancel` | ✅ Cancel subscription. |
| GET | `/provider/verification` | `provider.verification.index` | `Provider\VerificationController@index` | ✅ Verification page. |
| POST | `/provider/verification` | `provider.verification.store` | `Provider\VerificationController@store` | ✅ Upload document. |
| DELETE | `/provider/verification/{document}` | `provider.verification.destroy` | `Provider\VerificationController@destroy` | ✅ Delete document. |
| GET | `/provider/payments` | `provider.payments.index` | `Provider\PaymentController@history` | ✅ Payment history. |
| GET | `/provider/payments/{id}` | `provider.payments.detail` | `Provider\PaymentController@showPayment` | ✅ Payment detail. |
| GET | `/provider/payments/subscription/{subscription}` | `provider.payments.show` | `Provider\PaymentController@show` | ✅ Payment page. |
| POST | `/provider/payments/subscription/{subscription}` | `provider.payments.create` | `Provider\PaymentController@createPayment` | ✅ Create payment intent. |
| GET | `/provider/payments/confirm` | `provider.payments.confirm` | `Provider\PaymentController@confirm` | ✅ Confirm payment. |

### ✅ Admin Routes (Super Admin – Phase 8)
| Method | URI | Name | Controller | Purpose |
|--------|-----|------|------------|---------|
| GET | `/admin/providers` | `admin.providers.index` | `Admin\ProviderController@index` | ✅ List providers. |
| GET | `/admin/providers/{provider}` | `admin.providers.show` | `Admin\ProviderController@show` | ✅ Provider detail. |
| PATCH | `/admin/providers/{provider}/verify` | `admin.providers.verify` | `Admin\ProviderController@verify` | ✅ Update verification status. |
| PATCH | `/admin/providers/{provider}/toggle` | `admin.providers.toggle` | `Admin\ProviderController@toggleActive` | ✅ Toggle active status. |
| DELETE | `/admin/providers/{provider}` | `admin.providers.destroy` | `Admin\ProviderController@destroy` | ✅ Delete provider. |

### ✅ Webhook Routes
| Method | URI | Controller | Purpose |
|--------|-----|------------|---------|
| POST | `/webhook/stripe` | `WebhookController@stripe` | ✅ Stripe webhook handler (Phase 9). |

### LEGACY Agency Routes (prefix `/agency`, name `agency.`)
All require `auth:agency` except login/register (guest:agency).

| Method | URI | Name | Controller | Purpose |
|--------|-----|------|------------|---------|
| GET/POST | `/login`, `/register` | login, register | Auth controllers | Agency login/register. |
| GET | `/dashboard` | dashboard | `DashboardController@index` | Agency dashboard. |
| POST | `/logout` | logout | `LoginController@logout` | Logout. |
| Resource | `/treks` | treks | `Agency\TrekController` | CRUD treks. |
| GET | `/bookings` | bookings.index | `BookingController@index` | List bookings. |
| GET | `/bookings/{booking}` | bookings.show | `BookingController@show` | View booking. |
| PUT/PATCH | `/bookings/{booking}/status` | bookings.updateStatus | `BookingController@updateStatus` | Update booking status. |
| GET | `/agencies` | agencies.index | `AgencyController@index` | List agencies (super admin). |
| GET | `/agencies/create` | agencies.create | `AgencyController@create` | Create agency form. |
| POST | `/agencies` | agencies.store | `AgencyController@store` | Store agency. |
| GET | `/agencies/{id}/edit` | agencies.edit | `AgencyController@edit` | Edit agency. |
| PUT | `/agencies/{id}` | agencies.update | `AgencyController@update` | Update agency. |
| DELETE | `/agencies/{id}` | agencies.destroy | `AgencyController@destroy` | Delete agency. |
| PATCH | `/agencies/{id}/toggle-status` | agencies.toggle-status | `AgencyController@toggleStatus` | Toggle role. |
| GET | `/agencies/{id}` | agencies.show | `AgencyController@show` | View agency. |

### API Routes
| Method | URI | Controller | Purpose |
|--------|-----|------------|---------|
| POST | `/api/itinerary/generate` | `Api\ItineraryController@generate` | AI itinerary generation. |
| POST | `/api/sos` | `Api\SosController@store` | Create SOS alert. |

---

## 8. Current Controllers Audit (After Phases 1-9)

| Controller | Methods | Responsibility | Auth / Ownership Checks | Status |
|------------|---------|----------------|-------------------------|--------|
| `HomeController` | `index()` | Fetches data for homepage using `services`. | None | ✅ Updated Phase 7. |
| `Public\ServiceController` | `index`, `show`, `category`, `providerProfile` | ✅ Public marketplace for services. | None | ✅ Phase 7. |
| `Public\BookingController` | `create`, `store`, `confirmation` | ✅ Service booking (guest & authenticated). | None | ✅ Phase 7. |
| `PublicTrekController` | `index(Request)` | Lists treks with filters (LEGACY). | None | Working; will be deprecated. |
| `TrekController` (public) | `show(Trek)` | Shows single trek detail (LEGACY). | None | Working; will be deprecated. |
| `TrekBookingController` | `create`, `store`, `confirmation` | Guest booking (LEGACY). | None | Working; will be deprecated. |
| `CheckinController` | `show`, `checkin` | QR check‑in: show page and record scan. | None (validates booking exists) | Working. |
| `Api\ItineraryController` | `generate` | Validates input, calls `ItineraryGenerator`, returns JSON. | None | Working. |
| `Api\SosController` | `store` | Validates, stores `SosAlert`, dispatches job. | None | Working. |
| `Auth\LoginController` | `showLoginForm`, `login`, `logout` | ✅ User login/logout. | Guest/auth middleware | ✅ Phase 5. |
| `Auth\RegisterController` | `showRegistrationForm`, `register` | ✅ User registration with plans & provider types. | Guest middleware | ✅ Phase 8. |
| `Provider\DashboardController` | `index()` | ✅ Provider dashboard with stats. | `auth`; Policies | ✅ Phase 6. |
| `Provider\ProfileController` | `show`, `edit`, `update` | ✅ Provider profile management. | `auth`; Policies | ✅ Phase 6. |
| `Provider\ServiceController` | CRUD | ✅ Manage services (only own provider). | `auth`; Policies | ✅ Phase 6. |
| `Provider\BookingController` | `index`, `show`, `updateStatus` | ✅ Manage bookings (only own provider). | `auth`; Policies | ✅ Phase 6. |
| `Provider\SubscriptionController` | `index`, `store`, `upgrade`, `cancel` | ✅ Subscription management. | `auth` | ✅ Phase 8. |
| `Provider\VerificationController` | `index`, `store`, `destroy` | ✅ Provider verification documents. | `auth` | ✅ Phase 8. |
| `Provider\PaymentController` | `show`, `createPayment`, `confirm`, `history`, `showPayment` | ✅ Payment processing with Stripe. | `auth` | ✅ Phase 9. |
| `Admin\ProviderController` | `index`, `show`, `verify`, `toggleActive`, `destroy` | ✅ Super admin provider management. | `auth`, `admin` | ✅ Phase 8. |
| `WebhookController` | `stripe` | ✅ Stripe webhook handler. | None | ✅ Phase 9. |
| `PricingController` | `index` | ✅ Pricing page (via PageController). | None | ✅ Phase 8. |
| `Agency\Auth\LoginController` | `showLoginForm`, `login`, `logout` | LEGACY: Agency login/logout. | Guest/auth middleware | Working; will be deprecated. |
| `Agency\Auth\RegisterController` | `showRegistrationForm`, `register` | LEGACY: Agency registration. | Guest middleware | Working; will be deprecated. |
| `Agency\DashboardController` | `index()` | LEGACY: Agency dashboard. | `auth:agency`; role checks. | Working; will be deprecated. |
| `Agency\TrekController` | CRUD | LEGACY: Manage treks (only own agency). | `$trek->agency_id !== Auth::id()` | Working; will be deprecated. |
| `Agency\BookingController` | `index`, `show`, `updateStatus` | LEGACY: Manage bookings (only own agency). | Ownership checks via `trek.agency_id`. | Working; will be deprecated. |
| `Agency\AgencyController` | CRUD, toggleStatus | LEGACY: Super admin: manage agencies. | Role check `super_admin`. | Working; will be adapted to providers. |

---

## 9. Services / Jobs / Events Audit (After Phases 1-9)

| Component                     | Purpose                                                                 | Status / Notes                                                          |
|-------------------------------|-------------------------------------------------------------------------|-------------------------------------------------------------------------|
| `ItineraryGenerator` (Service)| Builds prompt, calls Groq API, returns content.                         | Working. Should be enhanced to recommend services.                      |
| `PaymentService` (Service)    | ✅ Stripe payment integration, webhook handling, refunds.               | ✅ Phase 9. |
| `SendSosNotification` (Job)   | Sends email to agency when SOS triggered.                               | Working. Queue driver = database.                                       |
| No other services, events, or listeners are present. |                                                                         | Future: introduce events (BookingCreated, SOSReceived) and listeners.   |

---

## 10. Current Views / UI Audit (After Phases 1-9)

**Public Layout:** `layouts/public.blade.php` – used by most public pages.

**Key Public Views:**
- `home.blade.php` – Hero, AI planner, stats, featured services, features grid, workflow, agency section, waitlist, check‑in carousel (✅ Updated Phase 7).
- `pages/features.blade.php`, `pages/how-it-works.blade.php`, `pages/agencies.blade.php`.
- `public/treks/index.blade.php` – listing with filters (LEGACY).
- `public/services/index.blade.php` – ✅ Service listing with filters (Phase 7).
- `public/services/show.blade.php` – ✅ Service detail page (Phase 7).
- `public/providers/show.blade.php` – ✅ Provider profile page (Phase 7).
- `public/booking/create.blade.php` – ✅ Service booking form (Phase 7).
- `public/booking/confirmation.blade.php` – ✅ Service booking confirmation (Phase 7).
- `trek/show.blade.php` – Trek detail page (LEGACY).
- `booking/create.blade.php` – Booking form (LEGACY).
- `booking/confirmation.blade.php` – Confirmation with QR (LEGACY).
- `checkin/scan.blade.php` – QR scan page.
- `public/pricing.blade.php` – ✅ Pricing page (Phase 8).

**✅ NEW Auth Views:** `resources/views/auth/`
- `login.blade.php` – User login form.
- `register.blade.php` – User registration form with plans & provider types (✅ Phase 8).

**✅ NEW Provider Layout:** `layouts/provider.blade.php` – sidebar, header, main content.
- `provider/dashboard.blade.php` – Provider dashboard with stats.
- `provider/profile.blade.php` – Provider profile view.
- `provider/profile-edit.blade.php` – Provider profile edit form.
- `provider/services/index.blade.php`, `create.blade.php`, `edit.blade.php` – Service CRUD.
- `provider/bookings/index.blade.php`, `show.blade.php` – Booking list and detail.
- `provider/subscriptions/index.blade.php` – ✅ Subscription management (Phase 8).
- `provider/verification/index.blade.php` – ✅ Verification documents (Phase 8).
- `provider/payments/show.blade.php` – ✅ Payment page (Phase 9).
- `provider/payments/history.blade.php` – ✅ Payment history (Phase 9).
- `provider/payments/detail.blade.php` – ✅ Payment detail (Phase 9).

**✅ Admin Views:** `resources/views/admin/providers/`
- `index.blade.php` – ✅ Provider list (Phase 8).
- `show.blade.php` – ✅ Provider detail with verification actions (Phase 8).

**LEGACY Agency Layout:** `layouts/app.blade.php` – sidebar, header, main content.
- `agency/dashboard.blade.php` – Super admin dashboard with charts; normal agency dashboard.
- `agency/auth/login.blade.php`, `agency/auth/register.blade.php`.
- `agency/treks/*` – CRUD views.
- `agency/bookings/*` – list and show.
- `agency/agencies/*` – CRUD views (super admin).

---

## 11. Existing Feature Matrix (After Phases 1-9)

| Feature                   | Status           | Files Involved                                              | Working? | Future Action |
|---------------------------|------------------|-------------------------------------------------------------|----------|---------------|
| AI Itinerary Generator    | Working          | `Api/ItineraryController`, `ItineraryGenerator`, `home.blade.php` | Yes      | Enhance with service recommendations. |
| Service Listing           | ✅ Working      | `Public\ServiceController`, `public/services/index.blade.php` | Yes      | ✅ Phase 7 complete. |
| Service Detail            | ✅ Working      | `Public\ServiceController`, `public/services/show.blade.php` | Yes      | ✅ Phase 7 complete. |
| Provider Profile          | ✅ Working      | `Public\ServiceController`, `public/providers/show.blade.php` | Yes      | ✅ Phase 7 complete. |
| Search/Filters            | ✅ Working      | `Public\ServiceController`                                   | Yes      | ✅ Phase 7 complete. |
| Guest Booking             | ✅ Working      | `Public\BookingController`                                   | Yes      | ✅ Phase 7 complete. |
| QR Check‑in               | Working          | `CheckinController`, `QrScan` model                         | Yes      | Preserve. |
| SOS Alerts                | Working          | `Api/SosController`, `SendSosNotification` job              | Yes      | Preserve. |
| ✅ Provider Dashboard     | Working          | `Provider\DashboardController`, `layouts/provider.blade.php` | Yes      | ✅ Phase 6 complete. |
| ✅ Service CRUD (Provider)| Working          | `Provider\ServiceController`                                 | Yes      | ✅ Phase 6 complete. |
| ✅ Booking Management (Provider)| Working    | `Provider\BookingController`                                 | Yes      | ✅ Phase 6 complete. |
| ✅ User Auth (Login/Register)| Working       | `Auth\LoginController`, `Auth\RegisterController`           | Yes      | ✅ Phase 5 complete. |
| ✅ Policies (Service/Booking)| Working       | `ServicePolicy`, `BookingPolicy`                             | Yes      | ✅ Phase 6 complete. |
| ✅ Pricing Page           | ✅ Working      | `public/pricing.blade.php`                                   | Yes      | ✅ Phase 8 complete. |
| ✅ Subscription UI        | ✅ Working      | `Provider\SubscriptionController`, `provider/subscriptions/index.blade.php` | Yes | ✅ Phase 8 complete. |
| ✅ Plan Selection (Register)| ✅ Working   | `Auth\RegisterController`, `auth/register.blade.php`         | Yes      | ✅ Phase 8 complete. |
| ✅ Provider Verification  | ✅ Working      | `Provider\VerificationController`, `Admin\ProviderController` | Yes      | ✅ Phase 8 complete. |
| ✅ Payment Integration    | ✅ Working      | `PaymentService`, `Provider\PaymentController`, `WebhookController` | Yes | ✅ Phase 9 complete. |
| Agency Dashboard (LEGACY) | Working          | `DashboardController`                                       | Yes      | Will be replaced. |
| Super Admin Dashboard (LEGACY)| Working      | `DashboardController` (super admin logic)                   | Yes      | Will be adapted to providers. |
| Gallery/Images            | Working          | `Agency\TrekController` store/update                        | Yes      | Adapt to new service model. |
| Waitlist                  | Partial (frontend only) | `home.blade.php`                                    | No       | Future. |
| Notifications (email)     | Only for SOS     | `SendSosNotification`                                       | Yes      | Expand (Phase 10). |
| Reports/Exports           | None             | N/A                                                         | No       | Future. |
| Reviews                   | None             | N/A                                                         | No       | Phase 10. |
| Messaging                 | None             | N/A                                                         | No       | Future. |
| Traveler Dashboard        | None             | N/A                                                         | No       | Phase 10. |

---

## 12. Working Features (Confirmed – All Phases 1-9)

- ✅ AI itinerary generation (API endpoint and frontend form)
- ✅ Public listing of services with filters (Phase 7)
- ✅ Service detail page with provider info (Phase 7)
- ✅ Provider profile page (Phase 7)
- ✅ Guest booking with QR code generation (Phase 7)
- ✅ Booking confirmation page (Phase 7)
- ✅ QR check‑in (page and scan recording)
- ✅ SOS alert creation and email notification (queued)
- ✅ Agency login/register (LEGACY)
- ✅ Agency dashboard (LEGACY)
- ✅ Trek CRUD (LEGACY)
- ✅ Booking management (LEGACY)
- ✅ Super admin dashboard (LEGACY)
- ✅ Super admin agency CRUD (LEGACY)
- ✅ **User login/register (NEW)**
- ✅ **Provider dashboard (NEW)**
- ✅ **Service CRUD (NEW)**
- ✅ **Booking management (NEW)**
- ✅ **Policies for Services and Bookings (NEW)**
- ✅ **Pricing page (Phase 8)**
- ✅ **Subscription management (Phase 8)**
- ✅ **Provider verification (Phase 8)**
- ✅ **Payment integration with Stripe (Phase 9)**

---

## 13. Partial Features

- **Waitlist:** Frontend form present but no backend logic.
- **Reports:** `exportBookings` method exists but returns JSON (not a proper CSV/Excel export).
- **Notifications:** Only SOS email; booking confirmations, reminders, etc. pending.

---

## 14. Missing Features

- Reviews/ratings
- Notifications (other than SOS)
- Messaging between traveler and provider
- Advanced analytics
- Traveler dashboard
- PWA/Offline capabilities
- Multi-language support

---

## 15. Technical Debt (After Phases 1-9)

- **Fat Controllers:** Business logic (e.g., image handling, itinerary conversion) inside controllers – needs extraction to services.
- **No Form Requests:** Validation is in controllers.
- **No Global Scopes:** Data isolation is not enforced globally (Policies handle it).
- **No Caching:** Statistics and check‑ins are recalculated on every request.
- **LEGACY Code:** Agency controllers, views, routes, and models still exist alongside new code.

---

## 16. Current Architecture Diagram (After Phases 1-9)

```
┌─────────────────────────────────────────────────────────────────────┐
│                            Browser                                 │
└─────────────────────────────────────────────────────────────────────┘
                                 │
         ┌───────────────────────┼───────────────────────┐
         │                       │                       │
         ▼                       ▼                       ▼
  ┌─────────────┐        ┌─────────────┐        ┌─────────────┐
  │ Public      │        │ Provider    │        │ LEGACY      │
  │ Routes      │        │ Routes      │        │ Agency      │
  │ (web)       │        │ (web)       │        │ Routes      │
  └─────────────┘        └─────────────┘        └─────────────┘
         │                       │                       │
         ▼                       ▼                       ▼
  ┌─────────────┐        ┌─────────────┐        ┌─────────────┐
  │ Controllers │        │ Controllers │        │ Controllers │
  │ Public      │        │ Provider    │        │ Agency      │
  │ Auth        │        │ Admin       │        │             │
  │ Webhook     │        │             │        │             │
  └─────────────┘        └─────────────┘        └─────────────┘
         │                       │                       │
         └───────────────────────┼───────────────────────┘
                                 ▼
                        ┌─────────────────────────────────┐
                        │          Models                 │
                        │ User, Provider, Service,        │
                        │ Booking, Payment, Subscription, │
                        │ Plan, VerificationDocument,     │
                        │ Agency, Trek, Trekker, etc.    │
                        └─────────────────────────────────┘
                                 │
                                 ▼
                        ┌─────────────────────────────────┐
                        │          Database               │
                        │ (LEGACY + NEW tables)           │
                        └─────────────────────────────────┘
```

**Flow:**
- **Public Users:** Browse services (Phase 7) → Book → QR check‑in → SOS.
- **Authenticated Users (User Guard):** Login → Provider Dashboard → Manage Services → Manage Bookings → Manage Subscriptions → Upload Verification → View Payments.
- **Super Admin:** Login → Admin Dashboard → Manage Providers → Verify Documents.
- **Agency Users (LEGACY):** Login → Agency Dashboard → Manage Treks → Manage Bookings → Manage Agencies (super admin).

---

## 17. Current User Flow (After Phases 1-9)

1. **Visitor** → Homepage → Browse services → View service detail → Book → Confirmation with QR.
2. **User (NEW)** → Login → Provider Dashboard → Create/Edit Services → Manage Bookings → Manage Subscriptions → Upload Verification → View Payments.
3. **Traveler (Guest)** → QR scan → Check‑in recorded.
4. **Traveler (Guest)** → SOS trigger → Alert sent to agency via email.
5. **Agency (LEGACY)** → Login → Dashboard → Manage treks (CRUD) → Manage bookings → Update status.
6. **Super Admin** → Login → Admin Dashboard → Manage Providers → Verify/Reject documents.

---

## 18. Current Booking Flow (After Phases 1-9)

1. Visitor selects a service and clicks "Book".
2. Fills in name, email, phone, start date.
3. System creates/updates `User` (traveler) and `Trekker` record (for legacy).
4. Creates `Booking` with `traveler_id` (User) and `service_id` (Service).
5. Status `pending`, generates unique `qr_code`.
6. Redirects to confirmation page showing QR code.
7. Provider can update booking status (pending → confirmed → completed/cancelled).
8. QR code scanned at checkpoints to record `QrScan`.

---

## 19. Payment Flow (✅ Phase 9)

1. Provider selects a paid plan from subscriptions page.
2. Subscription created with `pending` status.
3. Redirects to payment page with Stripe Elements.
4. Provider enters test card (`4242 4242 4242 4242`).
5. Stripe payment intent created and confirmed.
6. Webhook (`payment_intent.succeeded`) triggers:
   - Payment record updated to `success`.
   - Subscription activated (`active`, `start_date`, `end_date`).
7. Provider sees active subscription in dashboard.

---

## 20. Current QR / SOS Flow (Unchanged)

**QR:**
- Booking confirmation page shows QR code.
- QR code links to `/scan/{booking}`.
- Scanning opens check‑in page; user enters checkpoint name.
- POST to `/scan/{booking}` creates `QrScan` record.

**SOS:**
- API endpoint `/api/sos` accepts `booking_id`, latitude, longitude, message.
- Creates `SosAlert`.
- Dispatches `SendSosNotification` job (queued).
- Job sends email to agency with SOS details.

---

## 21. Current Strengths (After Phases 1-9)

- ✅ Fully functional core features (booking, QR, SOS, AI).
- ✅ Clean, modern UI with Tailwind.
- ✅ Well‑organised MVC structure.
- ✅ Queue/job system for async tasks.
- ✅ Extensible with multiple categories (trek/tour/hotel).
- ✅ Working super admin dashboard.
- ✅ Image gallery support.
- ✅ **User authentication with roles.**
- ✅ **Provider ecosystem with types, staff, and services.**
- ✅ **Policy-based authorization.**
- ✅ **Both legacy and new systems running in parallel.**
- ✅ **Public marketplace with services (Phase 7).**
- ✅ **Pricing page and subscription UI (Phase 8).**
- ✅ **Provider verification (Phase 8).**
- ✅ **Stripe payment integration (Phase 9).**

---

## 22. Current Problems (After Phases 1-9)

- **Dual Systems:** Both legacy (agency) and new (provider) systems run in parallel, causing code duplication.
- **No Traveler Dashboard:** Travelers can only book as guests; no authenticated traveler experience.
- **No Notifications:** Only SOS email; no booking confirmations, reminders, etc.
- **No Reviews:** Travelers cannot review services after completion.
- **Authorisation Scattered:** Legacy controllers use manual checks; new controllers use Policies.
- **No Global Scopes:** Data isolation not enforced globally.

---

## 23. Product Vision

> **TravelAI Nepal is a unified digital ecosystem that connects all tourism stakeholders in Nepal—businesses, professionals, and travelers—through a single platform. It provides AI‑powered trip planning, seamless booking, secure check‑ins, emergency support, and transparent management tools, fostering trust and efficiency in Nepal’s tourism industry.**

---

## 24. Target User Types (Achieved ✅)

| Type          | Description                                           | Authenticated? | Status |
|---------------|-------------------------------------------------------|----------------|--------|
| Super Admin   | Platform administrator, full system access.           | Yes            | ✅ Achieved |
| Provider Owner| Owner of a tourism business/professional entity.      | Yes            | ✅ Achieved |
| Manager       | Manager of a provider, limited admin rights.          | Yes            | ✅ Achieved |
| Staff         | Employee of a provider, basic view/update rights.     | Yes            | ✅ Achieved |
| Traveler      | Individual who books and experiences services.        | Yes (optional) | ⏳ Pending |

---

## 25. Target Provider Types (Taxonomy – Achieved ✅)

| ID | Name              | Status |
|----|-------------------|--------|
| 1  | Trekking Agency   | ✅ Seeded |
| 2  | Tour Agency       | ✅ Seeded |
| 3  | Hotel             | ✅ Seeded |
| 4  | Resort            | ✅ Seeded |
| 5  | Lodge             | ✅ Seeded |
| 6  | Homestay          | ✅ Seeded |
| 7  | Guide             | ✅ Seeded |
| 8  | Porter            | ✅ Seeded |
| 9  | Transport Provider| ✅ Seeded |
| 10 | Activity Provider | ✅ Seeded |
| 11 | Local Experience  | ✅ Seeded |
| 12 | Photographer      | ✅ Seeded |

---

## 26. Role Architecture (Achieved ✅)

| Role           | Scope                                       | Status |
|----------------|---------------------------------------------|--------|
| `super_admin`  | All providers, all data, system settings.   | ✅ Achieved |
| `provider_owner` | Own provider(s) – full management.         | ✅ Achieved |
| `manager`      | Own provider – manage services/bookings but not staff or settings. | ✅ Achieved |
| `staff`        | Own provider – view and limited updates.    | ✅ Achieved |
| `traveler`     | Own bookings, reviews, profile.             | ⏳ Pending (Phase 10) |

---

## 27. Provider Architecture (Achieved ✅)

**Core Concepts:**
- ✅ **User** – authenticated entity.
- ✅ **Provider** – business/professional entity with types.
- ✅ **Role** – system permissions.

**Tables (All Created ✅):**
- `users`, `providers`, `provider_types`, `provider_provider_type`, `provider_staff`, `subscriptions`, `payments`, `verification_documents`

**Relationships (All Working ✅):**
- `User` → `hasMany(Provider)`
- `Provider` → `belongsToMany(ProviderType)`
- `Provider` → `hasMany(ProviderStaff)`
- `Provider` → `hasMany(Service)`
- `Provider` → `hasMany(Booking)` via services.
- `Provider` → `hasMany(Subscription)`
- `Provider` → `hasMany(Payment)`
- `Subscription` → `morphMany(Payment)`

---

## 28. Service Architecture (Achieved ✅)

**Core Service Table** (`services`): ✅ Created.
**Service Categories** (`service_categories`): ✅ Seeded.
**Category‑Specific Details** (Specialised Tables):
- `trek_details`: ✅ Created & populated.
- `tour_details`: ✅ Created.
- `hotel_details`: ✅ Created.

**Migration**: Existing `treks` mapped to `services` and `trek_details` – ✅ Completed.

---

## 29. Booking Architecture (Achieved ✅)

**Central Booking Table** (`bookings`):
- ✅ `traveler_id` (FK to `users`) – populated.
- ✅ `service_id` (FK to `services`) – populated.
- ✅ Old columns (`trekker_id`, `trek_id`) dropped.

**Workflow**: Working for both guest and authenticated users.

---

## 30. Traveler Architecture (⏳ Pending Phase 10)

- **Traveler = User with role `traveler`** – ✅ User model supports this.
- **Profile**: Name, email, phone, emergency contact, preferences – ⏳ Phase 10.
- **Dashboard**: My Trips, Bookings, AI Planner, QR Passport, SOS History, Reviews, Profile – ⏳ Phase 10.
- **Guest Booking**: Working (LEGACY `trekkers` table still used for guest booking).

---

## 31. Dashboard Architecture (Achieved ✅)

### Common Shell
- ✅ `layouts/provider.blade.php` – sidebar, header, main content.
- ✅ Dynamic menu based on capabilities.

### Menu System
- ✅ Common menu items: Dashboard, Services, Bookings, Subscriptions, Verification, Payments, Profile.

### Modules (Achieved ✅)
- ✅ Common: Dashboard (stats), Profile, Bookings, Services.
- ✅ Subscription management (Phase 8).
- ✅ Verification (Phase 8).
- ✅ Payments (Phase 9).
- ✅ Super Admin: Provider management (Phase 8).

---

## 32. Registration Architecture (✅ Phase 8)

**Public Registration Page** now includes:
- ✅ Basic fields (name, email, password).
- ✅ Plan selection (Free, Professional, Business, Enterprise).
- ✅ Business/Provider registration checkbox.
- ✅ Provider fields (Business Name, Provider Type).
- ✅ Creates User, Provider, and Subscription.

**Status:** ✅ Complete.

---

## 33. Verification Architecture (✅ Phase 8)

**Verification Status** (enum): `pending`, `under_review`, `verified`, `rejected`, `suspended` – ✅ Table ready.
**Documents**: `verification_documents` table – ✅ Ready.
**Workflow**:
- Provider uploads documents → status `pending`.
- Super Admin reviews → can verify/reject.
- Documents status tracked (`pending`, `approved`, `rejected`).
- Provider verification status updated.

**Status:** ✅ Complete.

---

## 34. Pricing Architecture (✅ Phase 8)

**Plans Table**: `plans` – ✅ Created and seeded.
**Features & Limits**: JSON fields – ✅ Ready.
**Pricing Display**: ✅ Dedicated `/pricing` page.
**Pricing Page Content**: Plans, features, limits, FAQ.

**Status:** ✅ Complete.

---

## 35. Subscription Architecture (✅ Phase 8)

- `subscriptions` table – ✅ Created.
- Plan selection during registration – ✅ Complete.
- Subscription management in dashboard – ✅ Complete.
- Upgrade/Downgrade/Cancel – ✅ Complete.
- Free plans activate immediately; paid plans redirect to payment.

**Status:** ✅ Complete.

---

## 36. Payment Architecture (✅ Phase 9)

- `payments` table – ✅ Created.
- Payment Service (`PaymentService`) – ✅ Complete.
- Stripe integration – ✅ Complete.
- Webhook handling – ✅ Complete.
- Payment flow for subscriptions – ✅ Complete.
- Payment history in dashboard – ✅ Complete.
- Test mode ready with Stripe test card.

**Status:** ✅ Complete.

---

## 37. Marketplace Architecture (✅ Phase 7)

**Public Pages**:
- `/` – Homepage updated to use `services`.
- `/explore` – Search/filter with categories.
- `/explore/service/{slug}` – Service detail.
- `/provider/{slug}` – Provider profile.
- `/explore/category/{slug}` – Category list.

**Search**: Basic filters (category, search, price, duration, difficulty).

**Status:** ✅ Complete.

---

## 38. AI Architecture (Working)

**Current**: `ItineraryGenerator` service calls Groq API with a prompt.
**Future Enhancements**: Extend prompt to recommend services – Phase 11.

---

## 39. QR / Safety Architecture (Working ✅)

**QR Check‑in**: Working – uses `bookings.id`.
**SOS**: Working – uses `bookings.id`.

---

## 40. Notification Architecture (⏳ Pending Phase 10)

**Current**: Only email for SOS.
**Future**: Booking creation, status updates, reminders.

---

## 41. Reporting Architecture (⏳ Pending)

**Current**: None (partial JSON export).
**Future**: CSV/Excel/PDF exports – Phase 10.

---

## 42. Review Architecture (⏳ Pending Phase 10)

**Future**: Reviews linked to `booking_id` and `service_id`.

---

## 43. Messaging Architecture (⏳ Pending)

**Future**: Messages between traveler and provider – Phase 11.

---

## 44. Authorization Architecture (✅ Achieved)

**Policies**:
- `ServicePolicy`: ✅ Created and working.
- `BookingPolicy`: ✅ Created and working.
- `Gate`: ✅ Used for global permissions.

**Ownership Checks**: ✅ Policies ensure provider A cannot see provider B's data.

---

## 45. Data Isolation Architecture (✅ Achieved via Policies)

- **Provider A** cannot access Provider B's services – ✅ Policy check.
- **Provider A** cannot access Provider B's bookings – ✅ Policy check.
- **Super Admin** has global access – ✅ `isSuperAdmin()` check.

---

## 46. Target Database ER Diagram (✅ Achieved)

All target tables are created. Refer to Section 4 for the complete list.

---

## 47. Existing → Target Database Mapping (✅ Completed)

| Existing Table | Target Table | Status |
|----------------|--------------|--------|
| `agencies` → `users` + `providers` | ✅ Completed (Phase 2) |
| `trekkers` → `users` (future) | ⏳ Pending (Phase 10) |
| `treks` → `services` + `trek_details` | ✅ Completed (Phase 3) |
| `bookings` → new `bookings` columns | ✅ Completed (Phase 4) |
| `agency` guard → `auth` guard | ✅ Completed (Phase 5) |
| `role` field → `users.role` + provider types | ✅ Completed (Phase 1-2) |

---

## 48. Migration Strategy (Phased, Additive – ✅ Completed for Phases 1-9)

### Stage 1 – Foundation Setup (Phase 1) – ✅ COMPLETED
### Stage 2 – User/Provider Data Build (Phase 2) – ✅ COMPLETED
### Stage 3 – Service Migration (Phase 3) – ✅ COMPLETED
### Stage 4 – Booking Migration (Phase 4) – ✅ COMPLETED
### Stage 5 – Authentication Transition (Phase 5) – ✅ COMPLETED
### Stage 6 – Dashboard Refactor (Phase 6) – ✅ COMPLETED
### Stage 7 – Public Marketplace Update (Phase 7) – ✅ COMPLETED
### Stage 8 – Pricing & Subscriptions (Phase 8) – ✅ COMPLETED
### Stage 9 – Payments (Phase 9) – ✅ COMPLETED
### Stage 10 – Reviews & Notifications (Phase 10) – ⏳ PENDING
### Stage 11 – Advanced AI & Analytics (Phase 11) – ⏳ PENDING
### Stage 12 – Mobile/PWA & Cleanup (Phase 12) – ⏳ PENDING

---

## 49. Backward Compatibility Strategy (✅ Working)

- ✅ Old tables (`agencies`, `treks`, `trekkers`, `bookings` with old columns) kept during migration.
- ✅ Legacy `agency` guard and routes still work.
- ✅ New `User` guard and routes work in parallel.
- ✅ Existing features (AI, QR, SOS, booking) work with both systems.

---

## 50. Laravel Code Architecture (Target – ✅ Achieved for Phases 1-9)

**Directory Structure (Current):**
```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   ├── Auth/              ✅ (User auth)
│   │   ├── Provider/          ✅ (Provider dashboard)
│   │   ├── Admin/             ✅ (Super admin)
│   │   ├── Public/            ✅ (Public marketplace)
│   │   ├── Agency/            LEGACY
│   │   └── ...
│   ├── Middleware/
│   ├── Requests/              (To be added)
│   └── Resources/             (To be added)
├── Models/
│   ├── User                   ✅
│   ├── Provider               ✅
│   ├── ProviderType           ✅
│   ├── ProviderStaff          ✅
│   ├── Service                ✅
│   ├── ServiceCategory        ✅
│   ├── Plan                   ✅
│   ├── Subscription           ✅
│   ├── Payment                ✅ (Phase 9)
│   ├── Location               ✅
│   ├── VerificationDocument   ✅
│   ├── TrekDetail             ✅
│   ├── TourDetail             ✅
│   ├── HotelDetail            ✅
│   ├── Agency                 LEGACY
│   ├── Trek                   LEGACY
│   ├── Trekker                LEGACY
│   ├── Booking                (updated)
│   ├── QrScan                 LEGACY
│   └── SosAlert               LEGACY
├── Policies/
│   ├── ServicePolicy          ✅
│   └── BookingPolicy          ✅
├── Services/
│   ├── ItineraryGenerator     (existing)
│   └── PaymentService         ✅ (Phase 9)
├── Jobs/
│   └── SendSosNotification    (existing)
└── Console/
    └── Commands/
        ├── MigrateAgenciesToProviders  ✅ Phase 2
        ├── MigrateTreksToServices      ✅ Phase 3
        └── MigrateBookingsToNewSchema  ✅ Phase 4
```

---

## 51. Folder Structure (✅ Achieved)

New directories added:
- `app/Http/Controllers/Auth/` – ✅ Created.
- `app/Http/Controllers/Provider/` – ✅ Created.
- `app/Http/Controllers/Public/` – ✅ Created (Phase 7).
- `app/Http/Controllers/Admin/` – ✅ Created (Phase 8).
- `app/Policies/` – ✅ Created.
- `app/Services/` – ✅ Created.
- `app/Console/Commands/` – ✅ Migrations commands added.

---

## 52. Testing Strategy (⏳ To be implemented)

**Test Types**:
- Unit: Models, services, helpers.
- Feature: HTTP endpoints, authentication, authorisation.
- Database: Migrations, relationships.
- Browser (Dusk): Critical user journeys.

**Priority Tests**:
1. Registration (provider and traveler) – ⏳ Need tests.
2. Login – both guards – ⏳ Need tests.
3. Booking – guest and logged‑in – ⏳ Need tests.
4. QR Check‑in – ⏳ Need tests.
5. SOS – ⏳ Need tests.
6. AI Itinerary – ⏳ Need tests.
7. Dashboard – ⏳ Need tests.
8. Provider Data Isolation – ⏳ Need tests.
9. Super Admin – ⏳ Need tests.
10. Marketplace – ⏳ Need tests.
11. Payments – ⏳ Need tests.

---

## 53. Security Strategy (✅ In Place)

- Authentication: Laravel built‑in hashing, session, and token mechanisms.
- Authorization: Policies and Gates – ✅ Implemented.
- Data Isolation: Policies – ✅ Implemented.
- File Uploads: Validate file types, store in `public` disk – ✅ Working.
- QR Codes: Unique and random – ✅ Working.
- SOS: Valid booking ID required – ✅ Working.
- API: `throttle` middleware – ✅ Need to add.
- Mass Assignment: `$fillable` and `$guarded` – ✅ Proper.
- CSRF: Enabled on web routes – ✅ Working.
- Payments: Stripe secure webhooks – ✅ Implemented.

---

## 54. Performance Strategy (⏳ To be implemented)

- Indexes: ✅ Added on foreign keys and frequently queried columns.
- Eager Loading: ✅ Used in controllers.
- Caching: ⏳ Not yet implemented.
- Pagination: ✅ Used in listings.
- Queue: ✅ Used for SOS emails and jobs.

---

## 55. Rollback Strategy (✅ Available)

- Each migration step is reversible via `php artisan migrate:rollback`.
- Old tables kept until final cutover.
- Data‑copying scripts use transactions.

---

## 56. Risk Analysis (✅ Mitigated)

| Risk | Status |
|------|--------|
| Data loss during migration | ✅ Mitigated – backups, transactions, rollback scripts. |
| Authentication breakage | ✅ Mitigated – both guards run in parallel. |
| Performance degradation | ⏳ Monitoring – indexes added; caching later. |
| Payment failures | ✅ Mitigated – webhook retry logic, logging. |

---

## 57. Phase‑by‑Phase Roadmap (Updated)

| Phase | Goal | Status |
|-------|------|--------|
| **Phase 1** | Foundation (tables, models, seeders) | ✅ COMPLETED |
| **Phase 2** | User/Provider Integration | ✅ COMPLETED |
| **Phase 3** | Service Migration | ✅ COMPLETED |
| **Phase 4** | Booking Migration | ✅ COMPLETED |
| **Phase 5** | Authentication Transition | ✅ COMPLETED |
| **Phase 6** | Dashboard & Capabilities | ✅ COMPLETED |
| **Phase 7** | Public Marketplace | ✅ COMPLETED |
| **Phase 8** | Pricing & Subscriptions | ✅ COMPLETED |
| **Phase 9** | Payments | ✅ COMPLETED |
| **Phase 10** | Reviews & Notifications | ⏳ PENDING |
| **Phase 11** | Advanced AI, Safety, Analytics | ⏳ PENDING |
| **Phase 12** | Mobile/PWA & Cleanup | ⏳ PENDING |

---

## 58. NOW vs NEXT vs LATER (Updated)

| Category | Features | Status |
|----------|----------|--------|
| **NOW** (Phases 1-9) | Foundation, User/Provider, Service, Booking, Auth, Dashboard, Marketplace, Pricing, Subscriptions, Payments | ✅ COMPLETED |
| **NEXT** (Phase 10) | Reviews & Notifications | ⏳ In Progress |
| **LATER** (Phases 11-12) | Advanced AI, Safety, Analytics, PWA, Cleanup | ⏳ Planned |

---

## 59. What Must NOT Change Yet

- ✅ Do not drop or rename `agencies`, `treks`, `trekkers` tables (still used for legacy).
- ✅ Do not remove the `agency` guard or auth middleware (still in use).
- ✅ Do not remove any existing feature (AI, QR, SOS, booking).

---

## 60. What Can Be Added Later

- SMS notifications
- Real‑time messaging
- Advanced analytics
- PWA/offline capabilities
- Multi‑language support
- Mobile app (native)

---

## 61. Final Implementation Order (Updated)

**After Phases 1-9:**

### Phase 10: Reviews & Notifications (NEXT)
1. Create `reviews` table.
2. Review submission after booking completion.
3. Notification channels (mail, database, SMS).
4. Booking confirmation notifications.
5. Traveler dashboard.

### Phase 11: Advanced AI, Safety, Analytics
1. AI service recommendations.
2. SOS SMS notifications.
3. Analytics dashboards.

### Phase 12: Mobile/PWA & Cleanup
1. Service worker, manifest.
2. Offline support.
3. Drop old tables and guards.
4. Legacy code cleanup.

---

## 62. Go / No‑Go Checklist (Updated)

### ✅ COMPLETED (Phases 1-9)

| Element | Status |
|---------|--------|
| All new tables created (providers, services, subscriptions, payments, etc.) | ✅ |
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
| Gradual migration approach | ✅ |
| Preservation of existing features | ✅ |

### ⏳ PENDING (Phases 10-12)

| Element | Status |
|---------|--------|
| Reviews & Ratings | ⏳ Phase 10 |
| Notifications (booking, reminders) | ⏳ Phase 10 |
| Traveler Dashboard | ⏳ Phase 10 |
| Advanced AI recommendations | ⏳ Phase 11 |
| Analytics dashboards | ⏳ Phase 11 |
| PWA/Offline support | ⏳ Phase 12 |
| Legacy code cleanup | ⏳ Phase 12 |

---

**End of Master Document (Updated to Phase 9)**

---

**This document is the single source of truth for the evolution of TravelAI Nepal. All future development decisions must align with this blueprint.**

**Approval Signatures:**

- **Product Owner:** __________________
- **Technical Lead:** __________________
- **Business Stakeholder:** __________________

**Date of Approval:** __________________