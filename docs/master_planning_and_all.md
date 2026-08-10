# TravelAI Nepal — Master Product, Architecture, Database & Implementation Blueprint

**Version:** 4.0 (Updated – Phases 1-6 COMPLETED)  
**Date:** August 2026  
**Status:** ✅ Phases 1-6 Implemented | ⏳ Phase 7-12 Planned  
**Next Step:** Phase 7 — Public Marketplace  

---

## 1. Executive Summary

This document is the **Single Source of Truth** for the evolution of TravelAI Nepal. It is based on a thorough audit of the **actual Laravel 13 codebase, database schema, routes, models, controllers, and views**. The current system is a functional agency‑centric platform with working AI itinerary, booking, QR check‑in, SOS, and dashboard features. The long‑term vision is to expand into a **Nepal Tourism Ecosystem Platform** that supports multiple provider types (trekking agencies, hotels, guides, transport, homestays, etc.) and authenticated travelers.

**✅ Phases 1-6 have been successfully implemented:**
- **Phase 1:** Foundation (provider_types, service_categories, plans, subscriptions, locations, verification_documents, provider_provider_type, provider_staff)
- **Phase 2:** User/Provider Integration (agencies → users + providers migration)
- **Phase 3:** Service Migration (treks → services + trek_details, tour_details, hotel_details)
- **Phase 4:** Booking Migration (bookings → traveler_id + service_id, dropped old columns)
- **Phase 5:** Authentication Transition (new User guard with login/register)
- **Phase 6:** Dashboard & Capabilities (Provider dashboard with policies and CRUD)

The key architectural shift is to **separate the user (authentication) from the provider (business entity)** and to **decouple provider types from system roles**. This document provides a detailed audit, target architecture, database mapping, phased migration strategy, and implementation roadmap—all designed to **preserve existing functionality** while enabling future extensibility.

---

## 2. Current System Overview (After Phases 1-6)

TravelAI Nepal is a production‑ready Laravel application with the following characteristics:

- **Purpose:** Connect trekkers/travelers with tourism businesses (trekking agencies, tour operators, hotels, guides, transport, etc.) for booking trips, generating AI itineraries, managing check‑ins, and handling SOS.
- **Business Model:** Currently free for agencies; subscription/payment logic foundation is in place (plans, subscriptions tables).
- **User Types:**
  - **Agency** (authenticated via `agency` guard – LEGACY) – manages treks, bookings, dashboard (still works).
  - **User** (authenticated via `web` guard – NEW) – can be Super Admin, Provider Owner, Manager, Staff, or Traveler.
  - **Provider** – business entity linked to User (Provider Owner).
  - **Trekker** – legacy non‑authenticated traveler record (guest booking still supported).
- **Core Functionality (All Working):**
  - Public listing of treks/tours/hotels with search/filters.
  - AI itinerary generator (Groq API, Llama 3.1).
  - Guest booking (no login required) with QR code generation.
  - QR check‑in (scan passport at checkpoints).
  - SOS alerts (email notification to agency).
  - Agency dashboard (LEGACY) with CRUD for treks and bookings.
  - Provider dashboard (NEW) with CRUD for services and bookings.
  - Super admin dashboard with global statistics and agency management.
  - Service categories and provider types seeded.
  - Plans and subscriptions foundation (tables ready, UI coming in Phase 8).

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
| **Packages**            | `laravel/framework`, `laravel/tinker`, `laravel/pail`, `laravel/pint`, `phpunit`, etc. |
| **Node Dependencies**   | Vite, Tailwind, Axios, concurrently       |

---

## 4. Current Database Audit (After Phases 1-6)

The following tables exist. **New tables added in Phases 1-6 are marked with ✅.**

| Table          | Purpose                                                                   | Key Columns                                                                 | Relationships / Notes                          |
|----------------|---------------------------------------------------------------------------|-----------------------------------------------------------------------------|------------------------------------------------|
| `agencies`     | LEGACY: Provider accounts + business details.                             | `id`, `name`, `email`(unique), `password`, `phone`, `address`, `logo_url`, `role`, `user_id` | Has many `treks`; `user_id` FK to `users`. |
| `trekkers`     | LEGACY: Non‑authenticated traveler records.                               | `id`, `name`, `email`(unique), `phone`, `emergency_contact`                | Has many `bookings`, `sos_alerts`.             |
| `treks`        | LEGACY: Listings (trek/tour/hotel). `service_id` added.                   | `id`, `agency_id`(FK), `name`, `duration_days`, `difficulty`, `category`, `price`, `cover_image`, `gallery`, `itinerary`, `service_id` | Belongs to `agency`; has many `bookings`; `service_id` FK to `services`. |
| `bookings`     | Reservations. Now has `traveler_id` + `service_id`. Old columns dropped.  | `id`, `traveler_id`(FK), `service_id`(FK), `booking_date`, `start_date`, `status`, `qr_code`(unique), `invoice_url` | Belongs to `user` (traveler), `service`; has many `qr_scans`, one `sos_alert`. |
| `qr_scans`     | Check‑in records.                                                         | `id`, `booking_id`(FK), `checkpoint_name`, `scanned_at`, `latitude`, `longitude` | Belongs to `booking`.                          |
| `sos_alerts`   | Emergency alerts.                                                         | `id`, `trekker_id`(FK), `booking_id`(FK), `latitude`, `longitude`, `message`, `is_resolved` | Belongs to `trekker`, `booking`.               |
| `users`        | ✅ NOW ACTIVE: Central authentication table.                              | `id`, `name`, `email`, `password`, `role` (super_admin, provider_owner, manager, staff, traveler), `phone`, `avatar` | Has many `providers`, `provider_staff`, `travelerBookings`. |
| `providers`    | ✅ NEW: Business/professional entity.                                     | `id`, `user_id`(FK), `name`, `slug`, `description`, `logo_url`, `contact_email`, `contact_phone`, `address`, `verification_status`, `is_active` | Belongs to `user`; has many `types`, `staff`, `services`, `subscriptions`, `documents`. |
| `provider_types`| ✅ NEW: Taxonomy of business types.                                       | `id`, `name`, `slug`, `description`                                       | Many‑to‑many with `providers`.                 |
| `provider_provider_type`| ✅ NEW: Pivot table.                                                   | `provider_id`, `provider_type_id`                                         | Links providers to types.                      |
| `provider_staff`| ✅ NEW: Staff users assigned to providers.                                | `id`, `user_id`(FK), `provider_id`(FK), `role`, `permissions`            | Belongs to `user`, `provider`.                 |
| `service_categories`| ✅ NEW: Service types (Trek, Tour, Hotel, Guide, Transport, etc.).        | `id`, `name`, `slug`, `description`                                       | Has many `services`.                           |
| `services`     | ✅ NEW: Core listing table.                                               | `id`, `provider_id`(FK), `service_category_id`(FK), `name`, `slug`, `description`, `price`, `currency`, `cover_image`, `gallery`, `status`, `location_id` | Belongs to `provider`, `category`; has many `bookings`; has one `trekDetail`/`tourDetail`/`hotelDetail`. |
| `trek_details` | ✅ NEW: Trek‑specific fields.                                             | `id`, `service_id`(FK), `duration_days`, `difficulty`, `itinerary`, `max_altitude`, `season` | Belongs to `service`.                          |
| `tour_details` | ✅ NEW: Tour‑specific fields.                                             | `id`, `service_id`(FK), `duration_days`, `itinerary`, `inclusions`, `exclusions` | Belongs to `service`.                          |
| `hotel_details`| ✅ NEW: Hotel‑specific fields.                                            | `id`, `service_id`(FK), `room_count`, `star_rating`, `amenities`, `check_in_time`, `check_out_time` | Belongs to `service`.                          |
| `plans`        | ✅ NEW: Subscription plans.                                               | `id`, `name`, `slug`, `description`, `price_monthly`, `price_yearly`, `features`, `limits` | Has many `subscriptions`.                      |
| `subscriptions`| ✅ NEW: Provider subscriptions to plans.                                  | `id`, `provider_id`(FK), `plan_id`(FK), `start_date`, `end_date`, `status` | Belongs to `provider`, `plan`.                 |
| `locations`    | ✅ NEW: Reusable location data.                                           | `id`, `country`, `state`, `city`, `latitude`, `longitude`                  | Used by `services` and `providers`.            |
| `verification_documents`| ✅ NEW: Provider verification documents.                             | `id`, `provider_id`(FK), `type`, `file_path`, `status`                     | Belongs to `provider`.                         |
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
- `subscriptions.status`: `active`, `expired`, `cancelled`
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

**Indexes:** Primary keys, unique on emails, slugs, qr_code; foreign key indexes; additional indexes on `services.provider_id`, `services.service_category_id`, `bookings.traveler_id`, `bookings.service_id`, `subscriptions.provider_id`, `subscriptions.status`.

---

## 5. Current Models Audit (After Phases 1-6)

| Model          | File                | Purpose                                                                 | Relationships                                                                 | Casts / Notes                                                                 |
|----------------|---------------------|-------------------------------------------------------------------------|-------------------------------------------------------------------------------|-------------------------------------------------------------------------------|
| `User`         | `User.php`          | ✅ Central authentication model.                                         | `hasMany(Provider)`, `hasMany(ProviderStaff)`, `hasMany(Booking, 'traveler_id')` | `role` enum; `password` hashed; helper methods: `isSuperAdmin()`, `isProviderOwner()`, `isTraveler()`, `accessibleProviderIds()`. |
| `Agency`       | `Agency.php`        | LEGACY: Business entity & authenticated user.                           | `hasMany(Trek)`, `hasManyThrough(Booking, Trek)`, `belongsTo(User)`          | `role` used for permissions & type; `user_id` FK to `users`. |
| `Provider`     | `Provider.php`      | ✅ NEW: Business/professional entity.                                   | `belongsTo(User)`, `belongsToMany(ProviderType)`, `hasMany(ProviderStaff)`, `hasMany(Service)`, `hasMany(Subscription)`, `hasMany(VerificationDocument)` | `verification_status` enum; `is_active` boolean. |
| `ProviderType` | `ProviderType.php`  | ✅ NEW: Taxonomy.                                                        | `belongsToMany(Provider)`                                                   |                                                                               |
| `ProviderStaff`| `ProviderStaff.php` | ✅ NEW: Staff assignments.                                               | `belongsTo(User)`, `belongsTo(Provider)`                                    | `permissions` cast to array.                                                  |
| `Service`      | `Service.php`       | ✅ NEW: Core listing.                                                    | `belongsTo(Provider)`, `belongsTo(ServiceCategory)`, `belongsTo(Location)`, `hasOne(TrekDetail)`, `hasOne(TourDetail)`, `hasOne(HotelDetail)`, `hasMany(Booking)` | `gallery` cast to array; `price` decimal. |
| `ServiceCategory`| `ServiceCategory.php`| ✅ NEW: Service types.                                                   | `hasMany(Service)`                                                           |                                                                               |
| `TrekDetail`   | `TrekDetail.php`    | ✅ NEW: Trek‑specific fields.                                            | `belongsTo(Service)`                                                         | `itinerary` cast to array.                                                    |
| `TourDetail`   | `TourDetail.php`    | ✅ NEW: Tour‑specific fields.                                            | `belongsTo(Service)`                                                         | `itinerary`, `inclusions`, `exclusions` cast to array.                        |
| `HotelDetail`  | `HotelDetail.php`   | ✅ NEW: Hotel‑specific fields.                                           | `belongsTo(Service)`                                                         | `amenities` cast to array.                                                    |
| `Plan`         | `Plan.php`          | ✅ NEW: Subscription plans.                                              | `hasMany(Subscription)`                                                      | `features`, `limits` cast to array; `price` decimal.                          |
| `Subscription` | `Subscription.php`  | ✅ NEW: Provider subscriptions.                                          | `belongsTo(Provider)`, `belongsTo(Plan)`                                    | `start_date`, `end_date` cast to date; `status` enum.                         |
| `Location`     | `Location.php`      | ✅ NEW: Reusable locations.                                              | (Used by `Service` and `Provider`)                                           | `latitude`, `longitude` decimal.                                              |
| `VerificationDocument`| `VerificationDocument.php`| ✅ NEW: Provider verification.                                      | `belongsTo(Provider)`                                                         | `type`, `status` enums.                                                       |
| `Trek`         | `Trek.php`          | LEGACY: Listing (trek/tour/hotel).                                      | `belongsTo(Agency)`, `hasMany(Booking)`, `belongsTo(Service)`                | `itinerary`, `gallery` cast to array; `service_id` FK.                        |
| `Trekker`      | `Trekker.php`       | LEGACY: Non‑authenticated traveler.                                     | `hasMany(Booking)`, `hasMany(SosAlert)`, `belongsTo(User, 'email', 'email')` |                                                                               |
| `Booking`      | `Booking.php`       | Reservation. Now has `traveler_id` + `service_id`.                      | `belongsTo(Trekker)` (legacy), `belongsTo(Trek)` (legacy), `belongsTo(User, 'traveler_id')`, `belongsTo(Service)`, `hasMany(QrScan)`, `hasOne(SosAlert)` | `booking_date`, `start_date` cast to date.                                    |
| `QrScan`       | `QrScan.php`        | Check‑in record.                                                        | `belongsTo(Booking)`                                                         | `scanned_at` datetime; `latitude`/`longitude` decimal.                        |
| `SosAlert`     | `SosAlert.php`      | Emergency alert.                                                        | `belongsTo(Trekker)` (legacy), `belongsTo(Booking)`                          | `is_resolved` boolean.                                                        |

---

## 6. Current Authentication Audit (After Phases 1-6)

### ✅ NEW Authentication (User Guard – Web)
- **Guard:** `web` (default) – provider: `users` table.
- **Authenticatable Model:** `User`.
- **Login:** `Auth\LoginController` with routes:
  - `GET /login` → show form
  - `POST /login` → attempt login
- **Register:** `Auth\RegisterController` with routes:
  - `GET /register` → show form
  - `POST /register` → create user (role: traveler), log in.
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

## 7. Current Routes Audit (After Phases 1-6)

### Public Routes (no auth)
| Method | URI | Name | Controller | Purpose |
|--------|-----|------|------------|---------|
| GET | `/` | `home` | `HomeController@index` | Homepage with stats, featured treks, AI planner, check‑ins. |
| GET | `/features` | `pages.features` | `PageController@features` | Features page. |
| GET | `/how-it-works` | `pages.how-it-works` | `PageController@howItWorks` | How it works. |
| GET | `/agencies` | `pages.agencies` | `PageController@agencies` | List of agencies (will be updated to providers). |
| GET | `/treks` | `treks.index` | `PublicTrekController@index` | Explore treks/tours/hotels with filters (will be replaced with services). |
| GET | `/trek/{trek}` | `trek.show` | `TrekController@show` | Trek detail page (will be replaced with service detail). |
| GET | `/trek/{trek}/book` | `trek.book` | `TrekBookingController@create` | Booking form (will be updated). |
| POST | `/trek/{trek}/book` | – | `TrekBookingController@store` | Store booking. |
| GET | `/booking/confirmation/{booking}` | `booking.confirmation` | `TrekBookingController@confirmation` | Booking confirmation. |
| GET | `/booking/qr/{booking}` | `booking.qr` | Closure | Generate QR code image. |
| GET | `/scan/{booking}` | `scan.checkin` | `CheckinController@show` | QR check‑in page. |
| POST | `/scan/{booking}` | – | `CheckinController@checkin` | Record scan. |

### ✅ NEW Authentication Routes (User Guard)
| Method | URI | Name | Controller | Purpose |
|--------|-----|------|------------|---------|
| GET | `/login` | `login` | `Auth\LoginController@showLoginForm` | Login form. |
| POST | `/login` | – | `Auth\LoginController@login` | Handle login. |
| POST | `/logout` | `logout` | `Auth\LoginController@logout` | Handle logout. |
| GET | `/register` | `register` | `Auth\RegisterController@showRegistrationForm` | Registration form. |
| POST | `/register` | – | `Auth\RegisterController@register` | Handle registration. |

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

## 8. Current Controllers Audit (After Phases 1-6)

| Controller | Methods | Responsibility | Auth / Ownership Checks | Status |
|------------|---------|----------------|-------------------------|--------|
| `HomeController` | `index()` | Fetches data for homepage. | None | Working. Will be updated to use services. |
| `PublicTrekController` | `index(Request)` | Lists treks with filters. | None | Will be replaced with ServiceController. |
| `TrekController` (public) | `show(Trek)` | Shows single trek detail. | None | Will be replaced with ServiceController. |
| `TrekBookingController` | `create`, `store`, `confirmation` | Guest booking creation and confirmation. | None | Will be updated to use new booking model. |
| `CheckinController` | `show`, `checkin` | QR check‑in: show page and record scan. | None (validates booking exists) | Working; uses new booking model. |
| `Api\ItineraryController` | `generate` | Validates input, calls `ItineraryGenerator`, returns JSON. | None | Working. |
| `Api\SosController` | `store` | Validates, stores `SosAlert`, dispatches job. | None | Working. |
| `Auth\LoginController` | `showLoginForm`, `login`, `logout` | ✅ NEW: User login/logout. | Guest/auth middleware | Working. |
| `Auth\RegisterController` | `showRegistrationForm`, `register` | ✅ NEW: User registration. | Guest middleware | Working. |
| `Provider\DashboardController` | `index()` | ✅ NEW: Provider dashboard with stats. | `auth`; Policies | Working. |
| `Provider\ProfileController` | `show`, `edit`, `update` | ✅ NEW: Provider profile management. | `auth`; Policies | Working. |
| `Provider\ServiceController` | CRUD | ✅ NEW: Manage services (only own provider). | `auth`; Policies | Working. |
| `Provider\BookingController` | `index`, `show`, `updateStatus` | ✅ NEW: Manage bookings (only own provider). | `auth`; Policies | Working. |
| `Agency\Auth\LoginController` | `showLoginForm`, `login`, `logout` | LEGACY: Agency login/logout. | Guest/auth middleware | Working; will be deprecated. |
| `Agency\Auth\RegisterController` | `showRegistrationForm`, `register` | LEGACY: Agency registration. | Guest middleware | Working; will be deprecated. |
| `Agency\DashboardController` | `index()` | LEGACY: Agency dashboard. | `auth:agency`; role checks. | Working; will be replaced. |
| `Agency\TrekController` | CRUD | LEGACY: Manage treks (only own agency). | `$trek->agency_id !== Auth::id()` | Working; will be deprecated. |
| `Agency\BookingController` | `index`, `show`, `updateStatus` | LEGACY: Manage bookings (only own agency). | Ownership checks via `trek.agency_id`. | Working; will be deprecated. |
| `Agency\AgencyController` | CRUD, toggleStatus | LEGACY: Super admin: manage agencies. | Role check `super_admin`. | Working; will be adapted to providers. |

---

## 9. Services / Jobs / Events Audit (After Phases 1-6)

| Component                     | Purpose                                                                 | Status / Notes                                                          |
|-------------------------------|-------------------------------------------------------------------------|-------------------------------------------------------------------------|
| `ItineraryGenerator` (Service)| Builds prompt, calls Groq API, returns content.                         | Working. Should be enhanced to recommend services.                      |
| `SendSosNotification` (Job)   | Sends email to agency when SOS triggered.                               | Working. Queue driver = database.                                       |
| No other services, events, or listeners are present. |                                                                         | Future: introduce events (BookingCreated, SOSReceived) and listeners.   |

---

## 10. Current Views / UI Audit (After Phases 1-6)

**Public Layout:** `layouts/public.blade.php` – used by most public pages.

**Key Public Views:**
- `home.blade.php` – Hero, AI planner, stats, featured treks, features grid, workflow, agency section, waitlist, check‑in carousel.
- `pages/features.blade.php`, `pages/how-it-works.blade.php`, `pages/agencies.blade.php`.
- `public/treks/index.blade.php` – listing with filters.
- `trek/show.blade.php` – detail page.
- `booking/create.blade.php` – booking form.
- `booking/confirmation.blade.php` – confirmation with QR.
- `checkin/scan.blade.php` – QR scan page.

**✅ NEW Auth Views:** `resources/views/auth/`
- `login.blade.php` – User login form.
- `register.blade.php` – User registration form.

**✅ NEW Provider Layout:** `layouts/provider.blade.php` – sidebar, header, main content.
- `provider/dashboard.blade.php` – Provider dashboard with stats.
- `provider/profile.blade.php` – Provider profile view.
- `provider/profile-edit.blade.php` – Provider profile edit form.
- `provider/services/index.blade.php`, `create.blade.php`, `edit.blade.php` – Service CRUD.
- `provider/bookings/index.blade.php`, `show.blade.php` – Booking list and detail.

**LEGACY Agency Layout:** `layouts/app.blade.php` – sidebar, header, main content.
- `agency/dashboard.blade.php` – Super admin dashboard with charts; normal agency dashboard.
- `agency/auth/login.blade.php`, `agency/auth/register.blade.php`.
- `agency/treks/*` – CRUD views.
- `agency/bookings/*` – list and show.
- `agency/agencies/*` – CRUD views (super admin).

---

## 11. Existing Feature Matrix (After Phases 1-6)

| Feature                   | Status           | Files Involved                                              | Working? | Future Action |
|---------------------------|------------------|-------------------------------------------------------------|----------|---------------|
| AI Itinerary Generator    | Working          | `Api/ItineraryController`, `ItineraryGenerator`, `home.blade.php` | Yes      | Enhance with service recommendations. |
| Trek/Tour/Hotel Listing   | Working          | `PublicTrekController`, `trek.show`, `public/treks/index`   | Yes      | Will be replaced with ServiceController. |
| Search/Filters            | Basic            | `PublicTrekController`                                       | Yes      | Add more filters; integrate Scout. |
| Guest Booking             | Working          | `TrekBookingController`, `Booking` model                    | Yes      | Support authenticated travelers. |
| QR Check‑in               | Working          | `CheckinController`, `QrScan` model                         | Yes      | Preserve. |
| SOS Alerts                | Working          | `Api/SosController`, `SendSosNotification` job              | Yes      | Preserve. |
| ✅ Provider Dashboard     | Working          | `Provider\DashboardController`, `layouts/provider.blade.php` | Yes      | ✅ Phase 6 complete. |
| ✅ Service CRUD (Provider)| Working          | `Provider\ServiceController`                                 | Yes      | ✅ Phase 6 complete. |
| ✅ Booking Management (Provider)| Working    | `Provider\BookingController`                                 | Yes      | ✅ Phase 6 complete. |
| ✅ User Auth (Login/Register)| Working       | `Auth\LoginController`, `Auth\RegisterController`           | Yes      | ✅ Phase 5 complete. |
| ✅ Policies (Service/Booking)| Working       | `ServicePolicy`, `BookingPolicy`                             | Yes      | ✅ Phase 6 complete. |
| Agency Dashboard (LEGACY) | Working          | `DashboardController`                                       | Yes      | Will be replaced. |
| Super Admin Dashboard (LEGACY)| Working      | `DashboardController` (super admin logic)                   | Yes      | Will be adapted to providers. |
| Gallery/Images            | Working          | `Agency\TrekController` store/update                        | Yes      | Adapt to new service model. |
| Waitlist                  | Partial (frontend only) | `home.blade.php`                                    | No       | Future. |
| Notifications (email)     | Only for SOS     | `SendSosNotification`                                       | Yes      | Expand. |
| Reports/Exports           | None             | N/A                                                         | No       | Future. |
| Payments                  | None             | N/A                                                         | No       | Future. |
| Reviews                   | None             | N/A                                                         | No       | Future. |
| Messaging                 | None             | N/A                                                         | No       | Future. |
| Pricing Page              | None             | N/A                                                         | No       | Create. |
| Subscription UI/Plans     | Tables only      | `plans`, `subscriptions` tables                             | No       | Phase 8. |
| Provider Verification     | Tables only      | `verification_documents` table                              | No       | Phase 8. |

---

## 12. Working Features (Confirmed – All Phases 1-6)

- ✅ AI itinerary generation (API endpoint and frontend form)
- ✅ Public listing of treks/tours/hotels with filters
- ✅ Trek detail page with agency info
- ✅ Guest booking with QR code generation
- ✅ Booking confirmation page
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

---

## 13. Partial Features

- **Waitlist:** Frontend form present but no backend logic.
- **Reports:** `exportBookings` method exists but returns JSON (not a proper CSV/Excel export).
- **Pricing/Subscriptions:** Tables exist, but no UI or plan selection logic.

---

## 14. Missing Features

- Pricing page
- Subscription plan selection UI
- Payment integration
- Reviews/ratings
- Provider verification UI
- Notifications (other than SOS)
- Messaging between traveler and provider
- Advanced analytics
- Traveler dashboard

---

## 15. Technical Debt (After Phases 1-6)

- **Fat Controllers:** Business logic (e.g., image handling, itinerary conversion) inside controllers – needs extraction to services.
- **No Form Requests:** Validation is in controllers.
- **No Global Scopes:** Data isolation is not enforced globally (Policies handle it).
- **Mixed Views:** Some Blade files still use `treks` instead of `services`.
- **No Caching:** Statistics and check‑ins are recalculated on every request.
- **LEGACY Code:** Agency controllers, views, routes, and models still exist alongside new code.

---

## 16. Current Architecture Diagram (After Phases 1-6)

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
  └─────────────┘        └─────────────┘        └─────────────┘
         │                       │                       │
         └───────────────────────┼───────────────────────┘
                                 ▼
                        ┌─────────────────────────────────┐
                        │          Models                 │
                        │ User, Provider, Service,        │
                        │ Booking, Trek, Agency, Trekker, │
                        │ QrScan, SosAlert, etc.          │
                        └─────────────────────────────────┘
                                 │
                                 ▼
                        ┌─────────────────────────────────┐
                        │          Database               │
                        │ (LEGACY + NEW tables)           │
                        └─────────────────────────────────┘
```

**Flow:**
- **Public Users:** Browse treks (LEGACY) → Book (guest) → QR check‑in → SOS.
- **Authenticated Users (User Guard):** Login → Provider Dashboard → Manage Services → Manage Bookings → Profile.
- **Agency Users (LEGACY):** Login → Agency Dashboard → Manage Treks → Manage Bookings → Manage Agencies (super admin).

---

## 17. Current User Flow (After Phases 1-6)

1. **Visitor** → Homepage → Browse treks → View trek detail → Book (guest) → Confirmation with QR.
2. **User (NEW)** → Login → Provider Dashboard → Create/Edit Services → Manage Bookings → Update Profile.
3. **Traveler (Guest)** → QR scan → Check‑in recorded.
4. **Traveler (Guest)** → SOS trigger → Alert sent to agency via email.
5. **Agency (LEGACY)** → Login → Dashboard → Manage treks (CRUD) → Manage bookings → Update status.
6. **Super Admin (LEGACY)** → Login → Dashboard (global stats) → Manage agencies (CRUD).

---

## 18. Current Booking Flow (After Phases 1-6)

1. Visitor selects a trek and clicks "Book".
2. Fills in name, email, phone, start date.
3. System creates/updates `Trekker` record (by email) – LEGACY.
4. Creates `Booking` with `traveler_id` (User) and `service_id` (Service) – also keeps `trekker_id` for legacy.
5. Status `pending`, generates unique `qr_code`.
6. Redirects to confirmation page showing QR code.
7. Provider (NEW) can update booking status (pending → confirmed → completed/cancelled).
8. Agency (LEGACY) can also update status (pending → confirmed → completed/cancelled).
9. QR code can be scanned at checkpoints to record `QrScan`.

---

## 19. Current AI Flow (Unchanged)

1. User fills form on homepage (destination, days, budget, travel style, interests).
2. Form submits to `/api/itinerary/generate` via AJAX.
3. `ItineraryGenerator` builds prompt and calls Groq API.
4. Returns plain text, frontend formats with markdown and displays.
5. User can copy or download as TXT.

---

## 20. Current QR / SOS Flow (Unchanged)

**QR:**
- Booking confirmation page shows QR code (generated via `simple-qrcode`).
- QR code links to `/scan/{booking}`.
- Scanning opens check‑in page; user enters checkpoint name (and optional location).
- POST to `/scan/{booking}` creates `QrScan` record.

**SOS:**
- API endpoint `/api/sos` accepts `booking_id`, `trekker_id`, latitude, longitude, message.
- Creates `SosAlert`.
- Dispatches `SendSosNotification` job (queued).
- Job sends email to agency with SOS details and Google Maps link.

---

## 21. Current Strengths (After Phases 1-6)

- ✅ Fully functional core features (booking, QR, SOS, AI).
- ✅ Clean, modern UI with Tailwind.
- ✅ Well‑organised MVC structure.
- ✅ Queue/job system for async tasks.
- ✅ Extensible with multiple categories (trek/tour/hotel).
- ✅ Working super admin dashboard.
- ✅ Image gallery support.
- ✅ **User authentication with roles (super_admin, provider_owner, manager, staff, traveler).**
- ✅ **Provider ecosystem with types, staff, and services.**
- ✅ **Policy-based authorization.**
- ✅ **Both legacy and new systems running in parallel.**
- ✅ **All Phases 1-6 implemented and working.**

---

## 22. Current Problems (After Phases 1-6)

- **Dual Systems:** Both legacy (agency) and new (provider) systems run in parallel, causing code duplication.
- **No Traveler Dashboard:** Travelers can only book as guests; no authenticated traveler experience.
- **No Pricing/Subscription UI:** Tables exist but no plan selection or payment.
- **No Provider Verification UI:** Tables exist but no document upload or admin review.
- **Public Marketplace Still Uses `treks`:** Homepage, explore, detail pages still use `treks` table.
- **No Notifications:** Only SOS email; no booking confirmations, reminders, etc.
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
| `traveler`     | Own bookings, reviews, profile.             | ⏳ Pending (Traveler Dashboard) |

---

## 27. Provider Architecture (Achieved ✅)

**Core Concepts:**
- ✅ **User** – authenticated entity.
- ✅ **Provider** – business/professional entity with types.
- ✅ **Role** – system permissions.

**Tables (All Created ✅):**
- `users`, `providers`, `provider_types`, `provider_provider_type`, `provider_staff`

**Relationships (All Working ✅):**
- `User` → `hasMany(Provider)`
- `Provider` → `belongsToMany(ProviderType)`
- `Provider` → `hasMany(ProviderStaff)`
- `Provider` → `hasMany(Service)`
- `Provider` → `hasMany(Booking)` via services.

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

**Workflow**: Working for both guest and authenticated users (traveler dashboard pending).

**Migration**: ✅ Completed.

---

## 30. Traveler Architecture (⏳ Pending)

- **Traveler = User with role `traveler`** – ✅ User model supports this.
- **Profile**: Name, email, phone, emergency contact, preferences – ⏳ To be implemented.
- **Dashboard**: My Trips, Bookings, AI Planner, QR Passport, SOS History, Reviews, Profile – ⏳ To be implemented.
- **Guest Booking**: Working (LEGACY `trekkers` table still used for guest booking).
- **Future**: Merge `trekkers` into `users` when traveler accounts become mandatory.

---

## 31. Dashboard Architecture (Achieved ✅)

### Common Shell
- ✅ `layouts/provider.blade.php` – sidebar, header, main content.
- ✅ Dynamic menu based on capabilities.

### Menu System
- ✅ Common menu items: Dashboard, Services, Bookings, Profile.
- ✅ Future: Provider‑specific modules based on provider types.

### Capabilities
- ✅ Policies control access to services and bookings.
- ✅ Provider owners can manage their own services and bookings.

### Modules (Achieved ✅)
- ✅ Common: Dashboard (stats), Profile, Bookings, Services.
- ✅ Super Admin: Global stats (via legacy dashboard for now).

---

## 32. Registration Architecture (⏳ Pending Phase 8)

**Public Registration Page** with steps:
1. Choose Role – Tourism Business, Tourism Professional, Traveler – ⏳ To be designed.
2. Provider Type Selection – ⏳ To be designed.
3. Account Details – ✅ Basic registration works.
4. Provider Details – ⏳ To be designed.
5. Plan Selection – ⏳ To be designed.
6. Verification – ⏳ To be designed.
7. Submit – ✅ User creation works.

**Current**: Simple registration creates a User with role `traveler`. Provider registration will be built in Phase 8.

---

## 33. Verification Architecture (⏳ Pending)

**Verification Status** (enum): `pending`, `under_review`, `verified`, `rejected`, `suspended` – ✅ Table ready.
**Documents**: `verification_documents` table – ✅ Ready.
**Workflow**: ⏳ UI and admin review pending (Phase 8).

---

## 34. Pricing Architecture (⏳ Pending)

**Plans Table**: `plans` – ✅ Created and seeded.
**Features & Limits**: JSON fields – ✅ Ready.
**Pricing Display**: ⏳ Dedicated `/pricing` page and homepage section pending (Phase 8).
**Revenue Model**: Freemium + Hybrid – ✅ Architected.

---

## 35. Subscription Architecture (⏳ Pending)

- `subscriptions` table – ✅ Created.
- Plan selection during registration – ⏳ Pending (Phase 8).
- Subscription management in dashboard – ⏳ Pending (Phase 8).

---

## 36. Marketplace Architecture (⏳ Pending Phase 7)

**Public Pages**:
- `/` – Homepage – ⏳ Will be updated to use `services`.
- `/explore` – Search/filter – ⏳ To be created.
- `/services/{slug}` – Service detail – ⏳ To be created.
- `/providers/{slug}` – Provider profile – ⏳ To be created.
- `/categories/{slug}` – Category list – ⏳ To be created.

**Search**: Current DB queries – ⏳ Scout integration later.

---

## 37. Search Architecture (⏳ Pending)

**Current**: Simple `where` clauses in `PublicTrekController`.
**Future**: Laravel Scout with Meilisearch – Phase 7.

---

## 38. AI Architecture (Working)

**Current**: `ItineraryGenerator` service calls Groq API with a prompt.
**Future Enhancements**: Extend prompt to recommend services – Phase 11.

---

## 39. QR / Safety Architecture (Working ✅)

**QR Check‑in**: Working – uses `bookings.id`.
**SOS**: Working – uses `bookings.id`.

---

## 40. Notification Architecture (⏳ Pending)

**Current**: Only email for SOS.
**Future**: Booking creation, status updates, reminders – Phase 10.

---

## 41. Reporting Architecture (⏳ Pending)

**Current**: None (partial JSON export).
**Future**: CSV/Excel/PDF exports – Phase 10.

---

## 42. Review Architecture (⏳ Pending)

**Future**: Reviews linked to `booking_id` and `service_id` – Phase 10.

---

## 43. Messaging Architecture (⏳ Pending)

**Future**: Messages between traveler and provider – Phase 11.

---

## 44. Authorization Architecture (Achieved ✅)

**Policies**:
- `ServicePolicy`: ✅ Created and working.
- `BookingPolicy`: ✅ Created and working.
- `Gate`: ✅ Used for global permissions.

**Ownership Checks**: ✅ Policies ensure provider A cannot see provider B's data.

**Global Scopes**: ⏳ Optional – not yet implemented (Policies are sufficient for now).

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
| `trekkers` → `users` (future) | ⏳ Pending (Phase 8) |
| `treks` → `services` + `trek_details` | ✅ Completed (Phase 3) |
| `bookings` → new `bookings` columns | ✅ Completed (Phase 4) |
| `agency` guard → `auth` guard | ✅ Completed (Phase 5) |
| `role` field → `users.role` + provider types | ✅ Completed (Phase 1-2) |

---

## 48. Migration Strategy (Phased, Additive – ✅ Completed for Phases 1-6)

### Stage 1 – Foundation Setup (Phase 1) – ✅ COMPLETED
### Stage 2 – User/Provider Data Build (Phase 2) – ✅ COMPLETED
### Stage 3 – Service Migration (Phase 3) – ✅ COMPLETED
### Stage 4 – Booking Migration (Phase 4) – ✅ COMPLETED
### Stage 5 – Authentication Transition (Phase 5) – ✅ COMPLETED
### Stage 6 – Dashboard Refactor (Phase 6) – ✅ COMPLETED
### Stage 7 – Public Marketplace Update (Phase 7) – ⏳ PENDING
### Stage 8 – Cleanup (Phase 8-12) – ⏳ PENDING

---

## 49. Backward Compatibility Strategy (✅ Working)

- ✅ Old tables (`agencies`, `treks`, `trekkers`, `bookings` with old columns) kept during migration.
- ✅ Legacy `agency` guard and routes still work.
- ✅ New `User` guard and routes work in parallel.
- ✅ Existing features (AI, QR, SOS, booking) work with both systems.

---

## 50. Laravel Code Architecture (Target – ✅ Achieved for Phases 1-6)

**Directory Structure (Current):**
```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   ├── Auth/              ✅ NEW (User auth)
│   │   ├── Provider/          ✅ NEW (Provider dashboard)
│   │   ├── Agency/            LEGACY
│   │   └── Public/
│   ├── Middleware/
│   ├── Requests/              (To be added)
│   └── Resources/             (To be added)
├── Models/
│   ├── User                   ✅ NEW
│   ├── Provider               ✅ NEW
│   ├── ProviderType           ✅ NEW
│   ├── ProviderStaff          ✅ NEW
│   ├── Service                ✅ NEW
│   ├── ServiceCategory        ✅ NEW
│   ├── Plan                   ✅ NEW
│   ├── Subscription           ✅ NEW
│   ├── Location               ✅ NEW
│   ├── VerificationDocument   ✅ NEW
│   ├── TrekDetail             ✅ NEW
│   ├── TourDetail             ✅ NEW
│   ├── HotelDetail            ✅ NEW
│   ├── Agency                 LEGACY
│   ├── Trek                   LEGACY
│   ├── Trekker                LEGACY
│   ├── Booking                (updated)
│   ├── QrScan                 LEGACY
│   └── SosAlert               LEGACY
├── Policies/
│   ├── ServicePolicy          ✅ NEW
│   └── BookingPolicy          ✅ NEW
├── Services/
│   └── ItineraryGenerator     (existing)
├── Jobs/
│   └── SendSosNotification    (existing)
└── Console/
    └── Commands/
        ├── MigrateAgenciesToProviders  ✅ Phase 2
        ├── MigrateTreksToServices      ✅ Phase 3
        └── MigrateBookingsToNewSchema  ✅ Phase 4
```

---

## 51. Folder Structure Proposal (✅ Achieved)

New directories added:
- `app/Http/Controllers/Auth/` – ✅ Created.
- `app/Http/Controllers/Provider/` – ✅ Created.
- `app/Policies/` – ✅ Created.
- `app/Console/Commands/` – ✅ Migrations commands added.

---

## 52. Testing Strategy (⏳ To be implemented)

**Test Types**:
- Unit: Models, services, helpers.
- Feature: HTTP endpoints, authentication, authorisation.
- Database: Migrations, relationships.
- Browser (Dusk): Critical user journeys.

**Priority Tests**:
1. Registration (provider and traveler) – ✅ Need tests.
2. Login – both guards – ✅ Need tests.
3. Booking – guest and logged‑in – ✅ Need tests.
4. QR Check‑in – ✅ Need tests.
5. SOS – ✅ Need tests.
6. AI Itinerary – ✅ Need tests.
7. Dashboard – ✅ Need tests.
8. Provider Data Isolation – ✅ Need tests.
9. Super Admin – ✅ Need tests.
10. Marketplace – ⏳ Pending.

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

---

## 54. Performance Strategy (⏳ To be implemented)

- Indexes: ✅ Added on foreign keys and frequently queried columns.
- Eager Loading: ✅ Used in controllers.
- Caching: ⏳ Not yet implemented.
- Pagination: ✅ Used in listings.
- Queue: ✅ Used for SOS emails.
- Scout: ⏳ Pending Phase 7.

---

## 55. Rollback Strategy (✅ Available)

- Each migration step is reversible via `php artisan migrate:rollback`.
- Old tables kept until final cutover.
- Data‑copying scripts use transactions.
- Smoke tests after each phase.

---

## 56. Risk Analysis (✅ Mitigated)

| Risk | Status |
|------|--------|
| Data loss during migration | ✅ Mitigated – backups, transactions, rollback scripts. |
| Authentication breakage | ✅ Mitigated – both guards run in parallel. |
| Performance degradation | ⏳ Monitoring – indexes added; caching later. |
| Mis‑mapping of provider types | ✅ Mitigated – manual mapping, verification. |
| Existing bookings fail | ✅ Mitigated – old columns kept until verification. |

---

## 57. Phase‑by‑Phase Roadmap (Updated)

| Phase | Goal | Status |
|-------|------|--------|
| **Phase 1** | Foundation (tables, models, seeders) | ✅ COMPLETED |
| **Phase 2** | User/Provider Integration (agencies → users + providers) | ✅ COMPLETED |
| **Phase 3** | Service Migration (treks → services + details) | ✅ COMPLETED |
| **Phase 4** | Booking Migration (traveler_id + service_id) | ✅ COMPLETED |
| **Phase 5** | Authentication Transition (User guard) | ✅ COMPLETED |
| **Phase 6** | Dashboard & Capabilities (Provider dashboard, policies) | ✅ COMPLETED |
| **Phase 7** | Public Marketplace (services instead of treks) | ⏳ PENDING |
| **Phase 8** | Pricing & Subscriptions (UI, plan selection) | ⏳ PENDING |
| **Phase 9** | Payments (gateway integration) | ⏳ PENDING |
| **Phase 10** | Reviews & Notifications | ⏳ PENDING |
| **Phase 11** | Advanced AI, Safety, Analytics | ⏳ PENDING |
| **Phase 12** | Mobile/PWA & Cleanup | ⏳ PENDING |

---

## 58. NOW vs NEXT vs LATER (Updated)

| Category | Features | Status |
|----------|----------|--------|
| **NOW** (Phases 1-6) | Foundation, User/Provider, Service, Booking, Auth, Dashboard | ✅ COMPLETED |
| **NEXT** (Phase 7) | Public Marketplace (services) | ⏳ In Progress |
| **LATER** (Phases 8-12) | Pricing, Subscriptions, Payments, Reviews, Notifications, Advanced AI, PWA | ⏳ Planned |

---

## 59. What Must NOT Change Yet

- ✅ Do not drop or rename `agencies`, `treks`, `trekkers` tables (still used for legacy).
- ✅ Do not remove the `agency` guard or auth middleware (still in use).
- ✅ Do not modify existing controller logic until the new system is verified.
- ✅ Do not change existing routes or their names (legacy still works).
- ✅ Do not remove any existing feature (AI, QR, SOS, booking).

---

## 60. What Can Be Added Later

- Payment gateways
- SMS notifications
- Real‑time messaging
- Advanced analytics
- PWA/offline capabilities
- Multi‑language support
- Smart permits/blockchain
- Advanced AI recommendations
- Mobile app (native)

---

## 61. Final Implementation Order (Updated)

**After Phases 1-6:**

### Phase 7: Public Marketplace (NEXT)
1. Create `ServiceController` for public listing and detail.
2. Update `HomeController` to use `services`.
3. Update public views (`home.blade.php`, `public/treks/index.blade.php`, `trek/show.blade.php`) to use `services`.
4. Create `/explore`, `/services/{slug}`, `/providers/{slug}` routes.
5. Replace `treks` references with `services` in booking flow.
6. Integrate Laravel Scout (optional).

### Phase 8: Pricing & Subscriptions
1. Create `/pricing` page.
2. Plan selection in registration.
3. Subscription management in provider dashboard.
4. Provider verification UI.

### Phase 9: Payments
1. Create `payments` table.
2. Integrate Stripe/eSewa/Khalti.
3. Payment flow for subscriptions and bookings.

### Phase 10: Reviews & Notifications
1. Create `reviews` table.
2. Review submission after booking completion.
3. Notification channels (mail, database, SMS).

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

### ✅ COMPLETED

| Element | Status |
|---------|--------|
| New tables: `provider_types`, `service_categories`, `plans`, `subscriptions`, `locations`, `verification_documents`, `provider_provider_type`, `provider_staff` | ✅ |
| New models for new tables | ✅ |
| Separate `User` and `Provider` concepts | ✅ |
| Role‑based permissions via Policies | ✅ |
| Gradual migration approach with stages | ✅ |
| Preservation of existing features | ✅ |
| Extensible service architecture with category‑specific details | ✅ |
| Future‑ready for hotels, guides, transport, etc. | ✅ |
| Pricing/Subscription framework (tables only) | ✅ |
| Verification system (tables only) | ✅ |
| User authentication (login/register) | ✅ |
| Provider dashboard | ✅ |
| Service CRUD | ✅ |
| Booking management | ✅ |

### ⏳ PENDING

| Element | Status |
|---------|--------|
| Public Marketplace (services instead of treks) | ⏳ Phase 7 |
| Pricing page | ⏳ Phase 8 |
| Subscription UI | ⏳ Phase 8 |
| Payment integration | ⏳ Phase 9 |
| Reviews | ⏳ Phase 10 |
| Notifications | ⏳ Phase 10 |
| Advanced AI | ⏳ Phase 11 |
| PWA/Offline | ⏳ Phase 12 |
| Traveler Dashboard | ⏳ Phase 8 |

---

**End of Master Document**

---

**This document is the single source of truth for the evolution of TravelAI Nepal. All future development decisions must align with this blueprint.**

**Approval Signatures:**

- **Product Owner:** __________________
- **Technical Lead:** __________________
- **Business Stakeholder:** __________________

**Date of Approval:** __________________