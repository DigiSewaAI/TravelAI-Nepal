# TravelAI Nepal — Master Product, Architecture, Database & Implementation Blueprint

**Version:** 3.0 (Final – Based on Actual Codebase)  
**Date:** August 2026  
**Status:** For Review & Approval  
**Next Step:** Await approval before any implementation  

---

## 1. Executive Summary

This document is the **Single Source of Truth** for the evolution of TravelAI Nepal. It is based on a thorough audit of the **actual Laravel 13 codebase, database schema, routes, models, controllers, and views** provided. The current system is a functional agency‑centric platform with working AI itinerary, booking, QR check‑in, SOS, and dashboard features. The long‑term vision is to expand into a **Nepal Tourism Ecosystem Platform** that supports multiple provider types (trekking agencies, hotels, guides, transport, homestays, etc.) and authenticated travelers.

The key architectural shift is to **separate the user (authentication) from the provider (business entity)** and to **decouple provider types from system roles**. This document provides a detailed audit, target architecture, database mapping, phased migration strategy, and implementation roadmap—all designed to **preserve existing functionality** while enabling future extensibility.

**No implementation will begin until this blueprint is formally approved.**

---

## 2. Current System Overview

TravelAI Nepal is a production‑ready Laravel application with the following characteristics:

- **Purpose:** Connect trekkers/travelers with tourism businesses (trekking agencies, tour operators, hotels) for booking trips, generating AI itineraries, managing check‑ins, and handling SOS.
- **Business Model:** Currently free for agencies; no subscription/payment logic.
- **User Types:**
  - **Agency** (authenticated via `agency` guard) – manages treks, bookings, dashboard.
  - **Super Admin** – special agency role with full system access.
  - **Trekker** – non‑authenticated traveler record for guest booking.
- **Core Functionality:**
  - Public listing of treks/tours/hotels with search/filters.
  - AI itinerary generator (Groq API, Llama 3.1).
  - Guest booking (no login required) with QR code generation.
  - QR check‑in (scan passport at checkpoints).
  - SOS alerts (email notification to agency).
  - Agency dashboard with CRUD for treks and bookings.
  - Super admin dashboard with global statistics and agency management.

---

## 3. Technology Stack

| Component               | Version / Detail                          |
|-------------------------|-------------------------------------------|
| **PHP**                 | ^8.3                                      |
| **Laravel**             | ^13.0                                     |
| **Database**            | MySQL (schema dump provided)              |
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

## 4. Current Database Audit

The following tables exist (based on provided migrations and schema dump). All tables use InnoDB and utf8mb4.

| Table          | Purpose                                                                   | Key Columns                                                                 | Relationships / Notes                          |
|----------------|---------------------------------------------------------------------------|-----------------------------------------------------------------------------|------------------------------------------------|
| `agencies`     | Provider accounts (authenticated) + business details.                     | `id`, `name`, `email`(unique), `password`, `phone`, `address`, `logo_url`, `role` (`super_admin`,`admin`,`agency`) | Has many `treks`, has many `bookings` via `treks`. |
| `trekkers`     | Non‑authenticated traveler records.                                       | `id`, `name`, `email`(unique), `phone`, `emergency_contact`                | Has many `bookings`, `sos_alerts`.             |
| `treks`        | Listings: treks, tours, hotels (category enum).                          | `id`, `agency_id`(FK), `name`, `duration_days`, `difficulty`(enum), `category`(enum), `price`, `cover_image`, `gallery`(JSON), `itinerary`(JSON) | Belongs to `agency`; has many `bookings`.      |
| `bookings`     | Reservations by trekkers against a trek.                                  | `id`, `trekker_id`(FK), `trek_id`(FK), `booking_date`, `start_date`, `status`(enum), `qr_code`(unique), `invoice_url` | Belongs to `trekker`, `trek`; has many `qr_scans`, one `sos_alert`. |
| `qr_scans`     | Check‑in records.                                                         | `id`, `booking_id`(FK), `checkpoint_name`, `scanned_at`, `latitude`, `longitude` | Belongs to `booking`.                          |
| `sos_alerts`   | Emergency alerts.                                                         | `id`, `trekker_id`(FK), `booking_id`(FK), `latitude`, `longitude`, `message`, `is_resolved` | Belongs to `trekker`, `booking`.               |
| `users`        | (Unused) default Laravel user table.                                      | `id`, `name`, `email`, `password`, `remember_token`, etc.                  | No relationships currently.                    |
| `sessions`, `cache`, `cache_locks`, `jobs`, `migrations` | Framework tables.                                                  | Standard columns.                                                           |                                                |

**Foreign Keys:** All FKs have `ON DELETE CASCADE`.

**Enums:**
- `agencies.role`: `super_admin`, `admin`, `agency`
- `treks.difficulty`: `easy`, `moderate`, `hard`
- `treks.category`: `trek`, `tour`, `hotel`
- `bookings.status`: `pending`, `confirmed`, `completed`, `cancelled`

**JSON Columns:**
- `treks.gallery` – array of image paths.
- `treks.itinerary` – array of day‑by‑day descriptions.

**Indexes:** Primary keys, unique on `agencies.email`, `trekkers.email`, `bookings.qr_code`; foreign key indexes.

---

## 5. Current Models Audit

| Model          | File                | Purpose                                                                 | Relationships                                                                 | Casts / Notes                                                                 |
|----------------|---------------------|-------------------------------------------------------------------------|-------------------------------------------------------------------------------|-------------------------------------------------------------------------------|
| `Agency`       | `Agency.php`        | Business entity & authenticated user.                                   | `hasMany(Trek)`, `hasManyThrough(Booking, Trek)`                             | `password` hashed; `role` used for permissions & type.                        |
| `Trek`         | `Trek.php`          | Listing (trek/tour/hotel).                                              | `belongsTo(Agency)`, `hasMany(Booking)`                                      | `itinerary`, `gallery` cast to array; `price` decimal.                        |
| `Trekker`      | `Trekker.php`       | Non‑authenticated traveler.                                             | `hasMany(Booking)`, `hasMany(SosAlert)`                                      |                                                                               |
| `Booking`      | `Booking.php`       | Reservation.                                                            | `belongsTo(Trekker)`, `belongsTo(Trek)`, `hasMany(QrScan)`, `hasOne(SosAlert)` | `booking_date`, `start_date` cast to date.                                    |
| `QrScan`       | `QrScan.php`        | Check‑in record.                                                        | `belongsTo(Booking)`                                                         | `scanned_at` datetime; `latitude`/`longitude` decimal.                        |
| `SosAlert`     | `SosAlert.php`      | Emergency alert.                                                        | `belongsTo(Trekker)`, `belongsTo(Booking)`                                   | `is_resolved` boolean.                                                        |
| `User`         | `User.php`          | (Unused) default Laravel user.                                          | None                                                                         | Has `fillable`, `hidden`, `casts` but no relationships.                       |

**Missing Models:** None; all relevant models are present.

---

## 6. Current Authentication Audit

- **Guard:** `agency` (defined in `config/auth.php`; provider: `agencies` table).
- **Authenticatable Model:** `Agency`.
- **Login:** `Agency\Auth\LoginController` with routes:
  - `GET /agency/login` → show form
  - `POST /agency/login` → attempt login
- **Register:** `Agency\Auth\RegisterController` with routes:
  - `GET /agency/register` → show form
  - `POST /agency/register` → create agency, log in.
- **Middleware:** `guest:agency` for login/register; `auth:agency` for all dashboard routes.
- **Logout:** `POST /agency/logout` → guard logout.
- **Role system:** Stored in `agencies.role` as string (`super_admin`, `admin`, `agency`). Authorisation checks are manual in controllers (e.g., `if ($trek->agency_id !== Auth::id())`, `if (Auth::user()->role === 'super_admin')`).
- **No traveler authentication** – trekkers are not logged in.

**Note:** The `users` table exists but is not used for authentication.

---

## 7. Current Routes Audit

### Public Routes (no auth)
| Method | URI | Name | Controller | Purpose |
|--------|-----|------|------------|---------|
| GET | `/` | `home` | `HomeController@index` | Homepage with stats, featured treks, AI planner, check‑ins. |
| GET | `/features` | `pages.features` | `PageController@features` | Features page. |
| GET | `/how-it-works` | `pages.how-it-works` | `PageController@howItWorks` | How it works. |
| GET | `/agencies` | `pages.agencies` | `PageController@agencies` | List of agencies. |
| GET | `/treks` | `treks.index` | `PublicTrekController@index` | Explore treks/tours/hotels with filters. |
| GET | `/trek/{trek}` | `trek.show` | `TrekController@show` | Trek detail page. |
| GET | `/trek/{trek}/book` | `trek.book` | `TrekBookingController@create` | Booking form. |
| POST | `/trek/{trek}/book` | – | `TrekBookingController@store` | Store booking. |
| GET | `/booking/confirmation/{booking}` | `booking.confirmation` | `TrekBookingController@confirmation` | Booking confirmation. |
| GET | `/booking/qr/{booking}` | `booking.qr` | Closure | Generate QR code image. |
| GET | `/scan/{booking}` | `scan.checkin` | `CheckinController@show` | QR check‑in page. |
| POST | `/scan/{booking}` | – | `CheckinController@checkin` | Record scan. |

### Agency Routes (prefix `/agency`, name `agency.`)
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

**No authentication on API routes** – relies on validation (booking ID exists, etc.).

---

## 8. Current Controllers Audit

| Controller | Methods | Responsibility | Auth / Ownership Checks | Future Action |
|------------|---------|----------------|-------------------------|---------------|
| `HomeController` | `index()` | Fetches data for homepage. | None | Refactor to use view composers or services. |
| `PublicTrekController` | `index(Request)` | Lists treks with filters. | None | Rename to `ServiceController`; extend to all service types. |
| `TrekController` (public) | `show(Trek)` | Shows single trek detail. | None | Adapt to new `Service` model. |
| `TrekBookingController` | `create`, `store`, `confirmation` | Guest booking creation and confirmation. | None | Extend to handle authenticated travelers and all services. |
| `CheckinController` | `show`, `checkin` | QR check‑in: show page and record scan. | None (validates booking exists) | Keep; update to new booking model. |
| `Api\ItineraryController` | `generate` | Validates input, calls `ItineraryGenerator`, returns JSON. | None | Enhance with service recommendations; move logic to service. |
| `Api\SosController` | `store` | Validates, stores `SosAlert`, dispatches job. | None | Keep; update foreign keys to new booking model. |
| `Agency\Auth\LoginController` | `showLoginForm`, `login`, `logout` | Agency login/logout. | Guest/auth middleware | Will be deprecated after migration. |
| `Agency\Auth\RegisterController` | `showRegistrationForm`, `register` | Agency registration. | Guest middleware | Replaced with new registration. |
| `Agency\DashboardController` | `index()` | Dashboard: super admin overview or normal agency stats. | `auth:agency`; role checks inside. | Refactor to new provider dashboard with capabilities. |
| `Agency\TrekController` | CRUD | Manage treks (only own agency). | `$trek->agency_id !== Auth::id()` | Adapt to new `Service` model and policies. |
| `Agency\BookingController` | `index`, `show`, `updateStatus` | List, view, update status for bookings (only own agency). | Ownership checks via `trek.agency_id`. | Adapt to new `Booking` model. |
| `Agency\AgencyController` | CRUD, toggleStatus | Super admin: manage agencies. | Role check `super_admin`. | Adapt to new `Provider` management. |

**Controllers are relatively thin but contain business logic (e.g., image handling, itinerary conversion) that should be extracted to services.**

---

## 9. Services / Jobs / Events Audit

| Component                     | Purpose                                                                 | Status / Notes                                                          |
|-------------------------------|-------------------------------------------------------------------------|-------------------------------------------------------------------------|
| `ItineraryGenerator` (Service)| Builds prompt, calls Groq API, returns content.                         | Working. Should be enhanced to recommend services.                      |
| `SendSosNotification` (Job)   | Sends email to agency when SOS triggered.                               | Working. Queue driver = database.                                       |
| No other services, events, or listeners are present in the provided files. |                                                                         | Future: introduce events (BookingCreated, SOSReceived) and listeners.   |

---

## 10. Current Views / UI Audit

**Public Layout:** `layouts/public.blade.php` – used by most public pages. Tailwind CSS, FontAwesome, custom JS for check‑in carousel and AI form.

**Key Public Views:**
- `home.blade.php` – Hero, AI planner, stats, featured treks, features grid, workflow, agency section, waitlist, check‑in carousel.
- `pages/features.blade.php`, `pages/how-it-works.blade.php`, `pages/agencies.blade.php`.
- `public/treks/index.blade.php` – listing with filters.
- `trek/show.blade.php` – detail page.
- `booking/create.blade.php` – booking form.
- `booking/confirmation.blade.php` – confirmation with QR.
- `checkin/scan.blade.php` – QR scan page.

**Agency Layout:** `layouts/app.blade.php` – sidebar, header, main content.
- `agency/dashboard.blade.php` – super admin dashboard with charts; normal agency dashboard (basic stats).
- `agency/auth/login.blade.php`, `agency/auth/register.blade.php`.
- `agency/treks/*` – CRUD views.
- `agency/bookings/*` – list and show.
- `agency/agencies/*` – CRUD views (super admin).

**UI Quality:** Modern, responsive, clean. No framework bloat.

---

## 11. Existing Feature Matrix

| Feature                   | Status           | Files Involved                                              | Working? | Future Action |
|---------------------------|------------------|-------------------------------------------------------------|----------|---------------|
| AI Itinerary Generator    | Working          | `Api/ItineraryController`, `ItineraryGenerator`, `home.blade.php` | Yes      | Enhance with service recommendations. |
| Trek/Tour/Hotel Listing   | Working          | `PublicTrekController`, `trek.show`, `public/treks/index`   | Yes      | Rename to `Services`; extend to all categories. |
| Search/Filters            | Basic (category, difficulty, duration, price) | `PublicTrekController` | Yes | Add more filters; integrate Scout. |
| Guest Booking             | Working          | `TrekBookingController`, `Booking` model                    | Yes      | Support authenticated travelers and all services. |
| QR Check‑in               | Working          | `CheckinController`, `QrScan` model                         | Yes      | Preserve; integrate with new booking. |
| SOS Alerts                | Working          | `Api/SosController`, `SendSosNotification` job              | Yes      | Preserve; expand notifications. |
| Agency Dashboard          | Working          | `DashboardController`                                       | Yes      | Refactor to new provider dashboard with capabilities. |
| Super Admin Dashboard     | Working          | `DashboardController` (super admin logic)                   | Yes      | Preserve; expand to manage all providers. |
| Gallery/Images            | Working          | `Agency\TrekController` store/update                        | Yes      | Adapt to new service model. |
| Waitlist                  | Partial (frontend only) | `home.blade.php`                                    | No       | Future; not priority. |
| Notifications (email)     | Only for SOS     | `SendSosNotification`                                       | Yes      | Expand to all booking events. |
| Reports/Exports           | None (partial JSON export not fully implemented) | `DashboardController@exportBookings` | No       | Future. |
| Payments                  | None             | N/A                                                         | No       | Future. |
| Reviews                   | None             | N/A                                                         | No       | Future. |
| Messaging                 | None             | N/A                                                         | No       | Future. |
| Pricing Page              | None             | N/A                                                         | No       | Create. |
| Subscription/Plans        | None             | N/A                                                         | No       | Implement. |
| Provider Verification     | None             | N/A                                                         | No       | Implement. |

---

## 12. Working Features (Confirmed)

- AI itinerary generation (API endpoint and frontend form)
- Public listing of treks/tours/hotels with filters
- Trek detail page with agency info
- Guest booking with QR code generation
- Booking confirmation page
- QR check‑in (page and scan recording)
- SOS alert creation and email notification (queued)
- Agency login/register
- Agency dashboard (stats, recent bookings)
- Trek CRUD (with image upload and itinerary management)
- Booking management (list, view, update status)
- Super admin dashboard (global stats, charts, agency management)
- Super admin agency CRUD (create, edit, delete, toggle role)
- Gallery image upload and display

---

## 13. Partial Features

- **Waitlist:** Frontend form present but no backend logic.
- **Reports:** `exportBookings` method exists but returns JSON (not a proper CSV/Excel export).

---

## 14. Missing Features

- Pricing page
- Subscription plans
- Payment integration
- Reviews/ratings
- Provider verification
- Notifications (other than SOS)
- Messaging between traveler and provider
- Advanced analytics
- Traveler authentication

---

## 15. Technical Debt

- **Fat Controllers:** Business logic (e.g., image handling, itinerary conversion) inside controllers.
- **No Form Requests:** Validation is in controllers.
- **No Policies:** Authorisation checks are scattered.
- **Repetitive Image Handling:** In `store` and `update` of `Agency\TrekController`.
- **Mixed Concerns:** `DashboardController` contains both super admin and normal agency logic.
- **No Caching:** Statistics and check‑ins are recalculated on every request.

---

## 16. Current Architecture Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                         Browser                            │
└─────────────────────────────────────────────────────────────┘
                             │
         ┌───────────────────┼───────────────────┐
         │                   │                   │
         ▼                   ▼                   ▼
  ┌─────────────┐    ┌─────────────┐    ┌─────────────┐
  │ Public      │    │ Agency      │    │ API         │
  │ Routes      │    │ Routes      │    │ Routes      │
  └─────────────┘    └─────────────┘    └─────────────┘
         │                   │                   │
         ▼                   ▼                   ▼
  ┌─────────────┐    ┌─────────────┐    ┌─────────────┐
  │ Controllers │    │ Controllers │    │ Controllers │
  └─────────────┘    └─────────────┘    └─────────────┘
         │                   │                   │
         └───────────────────┼───────────────────┘
                             ▼
                    ┌─────────────────┐
                    │   Models        │
                    │ Agency, Trek,   │
                    │ Booking,        │
                    │ Trekker, QR, SOS│
                    └─────────────────┘
                             │
                             ▼
                    ┌─────────────────┐
                    │   Database      │
                    └─────────────────┘
```

**Flow:** Public users browse treks, book (no auth), check in via QR, trigger SOS. Agencies log in, manage their treks and bookings. Super admin manages all agencies.

---

## 17. Current User Flow

1. **Visitor** → Homepage → Browse treks → View trek detail → Book (guest) → Confirmation with QR.
2. **Agency** → Login → Dashboard → Manage treks (CRUD) → Manage bookings → Update status.
3. **Super Admin** → Login → Dashboard (global stats) → Manage agencies (CRUD).
4. **Traveler** (guest) → QR scan → Check‑in recorded.
5. **Traveler** (guest) → SOS trigger → Alert sent to agency via email.

---

## 18. Current Booking Flow

1. Visitor selects a trek and clicks "Book".
2. Fills in name, email, phone, start date.
3. System creates/updates `Trekker` record (by email).
4. Creates `Booking` with status `pending`, generates unique `qr_code`.
5. Redirects to confirmation page showing QR code.
6. Agency can update booking status (pending → confirmed → completed/cancelled).
7. QR code can be scanned at checkpoints to record `QrScan`.

---

## 19. Current AI Flow

1. User fills form on homepage (destination, days, budget, travel style, interests).
2. Form submits to `/api/itinerary/generate` via AJAX.
3. `ItineraryGenerator` builds prompt and calls Groq API.
4. Returns plain text, frontend formats with markdown and displays.
5. User can copy or download as TXT.

---

## 20. Current QR / SOS Flow

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

## 21. Current Strengths

- Fully functional core features (booking, QR, SOS, AI).
- Clean, modern UI with Tailwind.
- Well‑organised MVC structure.
- Queue/job system for async tasks.
- Extensible with multiple categories (trek/tour/hotel).
- Working super admin dashboard.
- Image gallery support.

---

## 22. Current Problems

- `Agency` is both user and business entity; no separation.
- No traveler authentication; trekkers are passive records.
- `role` field overloaded (permissions + business type).
- Booking hardcoded to `trek_id`.
- No pricing/subscription model.
- No provider verification.
- Authorisation checks scattered in controllers.
- No global data isolation (but ownership checks exist).
- Future provider types (guides, hotels, etc.) not supported.

---

## 23. Product Vision

> **TravelAI Nepal is a unified digital ecosystem that connects all tourism stakeholders in Nepal—businesses, professionals, and travelers—through a single platform. It provides AI‑powered trip planning, seamless booking, secure check‑ins, emergency support, and transparent management tools, fostering trust and efficiency in Nepal’s tourism industry.**

---

## 24. Target User Types

| Type          | Description                                           | Authenticated? |
|---------------|-------------------------------------------------------|----------------|
| Super Admin   | Platform administrator, full system access.           | Yes            |
| Provider Owner| Owner of a tourism business/professional entity.      | Yes            |
| Manager       | Manager of a provider, limited admin rights.          | Yes            |
| Staff         | Employee of a provider, basic view/update rights.     | Yes            |
| Traveler      | Individual who books and experiences services.        | Yes (optional) |

---

## 25. Target Provider Types (Taxonomy)

| ID | Name              | Description                                |
|----|-------------------|--------------------------------------------|
| 1  | Trekking Agency   | Organises trekking expeditions.            |
| 2  | Tour Agency       | Organises guided tours and packages.       |
| 3  | Hotel             | Accommodation provider.                    |
| 4  | Resort            | Luxury accommodation.                      |
| 5  | Lodge             | Basic accommodation in remote areas.       |
| 6  | Homestay          | Family‑run accommodation with local experience. |
| 7  | Guide             | Independent trekking or tour guide.        |
| 8  | Porter            | Carries equipment for trekkers.            |
| 9  | Transport Provider| Offers vehicle rentals or transfers.       |
| 10 | Activity Provider | Offers adventure activities (rafting, paragliding, etc.). |
| 11 | Local Experience  | Cultural or culinary experiences.          |
| 12 | Photographer      | Professional photographer for trips.       |

*Note: A provider can have multiple types.*

---

## 26. Role Architecture (System Permissions)

| Role           | Scope                                       |
|----------------|---------------------------------------------|
| `super_admin`  | All providers, all data, system settings.   |
| `provider_owner` | Own provider(s) – full management.         |
| `manager`      | Own provider – manage services/bookings but not staff or settings. |
| `staff`        | Own provider – view and limited updates.    |
| `traveler`     | Own bookings, reviews, profile.             |

**Permissions (sample)**:
- `view_any_service`, `view_service`, `create_service`, `update_service`, `delete_service`
- `view_any_booking`, `view_booking`, `update_booking_status`
- `manage_staff`, `manage_provider_settings`

**Implementation**: Laravel Policies with `Gate` facade. Use a `Permission` table (or JSON in `roles` table) for flexibility.

---

## 27. Provider Architecture (Target)

**Core Concepts:**
- **User** – the authenticated entity (both providers and travelers).
- **Provider** – a business/professional entity that offers services. A provider has one or more **Provider Types**.
- **Role** – defines system permissions (Super Admin, Provider Owner, Manager, Staff, Traveler).

**Tables**:
- `users` (central auth)
- `providers` (business/professional entity)
- `provider_types` (taxonomy)
- `provider_provider_type` (many‑to‑many)
- `provider_staff` (assign users to providers with roles)

**Relationships:**
- A `User` can be a `provider_owner` of one or more `Provider`s.
- A `Provider` can have many `Staff` (users with roles).
- A `Provider` can have many `ProviderType`s.
- A `Provider` owns many `Service`s.
- A `Provider` has many `Booking`s via services.

---

## 28. Service Architecture (Target)

**Core Service Table** (`services`):
- Common fields: `provider_id`, `service_category_id`, `name`, `slug`, `description`, `price`, `currency`, `cover_image`, `gallery`, `status`, `location_id`.

**Service Categories**:
- `service_categories` table: `id`, `name`, `slug`, `description`.

**Category‑Specific Details**:
- Use **specialised tables** instead of polymorphic to avoid complexity.
- Examples:
  - `trek_details`: `service_id`, `duration_days`, `difficulty`, `itinerary` (JSON), `max_altitude`, `season`.
  - `tour_details`: `service_id`, `duration_days`, `itinerary`, `inclusions`, `exclusions`.
  - `hotel_details`: `service_id`, `room_count`, `star_rating`, `amenities` (JSON), `check_in_time`, `check_out_time`.
  - `guide_details`: `service_id`, `experience_years`, `languages` (JSON), `certifications` (JSON), `specialties`.
  - `transport_details`: `service_id`, `vehicle_type`, `capacity`, `route`, `availability`.

**Migration**: Existing `treks` will be mapped to `services` and corresponding `trek_details`.

---

## 29. Booking Architecture (Target)

**Central Booking Table** (`bookings`):
- `id`, `traveler_id` (FK to `users`), `service_id` (FK to `services`), `start_date`, `end_date`, `status`, `total_price`, `currency`, `qr_code` (unique), `invoice_url`, `created_at`, `updated_at`.

**Booking Attributes**:
- Use a `details` JSON field to store category‑specific data (e.g., number of trekkers, room type, vehicle type). This is flexible and avoids many tables.
- For complex attributes (like multiple rooms), we may later create child tables.

**Workflow**:
1. User selects a service, specifies dates and details.
2. System checks availability (if implemented). For MVP, no availability check – just confirm.
3. Booking created with status `pending`.
4. Agency can update status to `confirmed`, `completed`, `cancelled`.
5. QR code generated (unique per booking).
6. Check‑ins recorded via QR scans.

**Migration**: Existing `bookings` will be updated to add `traveler_id` and `service_id` while keeping `trekker_id` and `trek_id` until data is fully migrated. Then drop old columns.

---

## 30. Traveler Architecture

- **Traveler = User with role `traveler`**.
- **Profile**: name, email, phone, emergency contact, preferences.
- **Dashboard**: My Trips, Bookings, AI Planner, QR Passport, SOS History, Reviews, Profile.
- **Guest Booking**: Keep guest booking flow (Trekker model) for now; later we can create a `user` with `role='traveler'` and a random password, or keep guest bookings as separate records. We'll support both: guest booking stores data in `trekkers` (or a `guests` table) and does not require a user account; when a guest later creates an account, we can link their bookings to their user ID.

---

## 31. Dashboard Architecture

### Common Shell
- Sidebar (collapsible) with dynamic menu based on capabilities.
- Header with user avatar, notifications, profile link.
- Main content area.

### Menu System
- Define a `menu` configuration array in a service provider, keyed by provider type(s). For each menu item, define: `label`, `route`, `icon`, `capability` (a permission/gate).
- In the blade, loop through items and check `can()` to show/hide.

### Capabilities
- Map provider types to capabilities: e.g., `trekking_agency` → `manage_treks`, `manage_guides`, `manage_porters`; `hotel` → `manage_rooms`, `manage_reservations`; etc.
- These capabilities are essentially permissions assigned to the user’s role within the provider (via `provider_staff`).

### Modules
- **Common**: Dashboard (stats), Profile, Bookings, Customers/Travelers, Analytics, Settings.
- **Provider‑specific**: Treks, Tours, Hotels, Rooms, Guides, Transport, etc., as per type.
- **Super Admin**: Global stats, manage providers, system settings.

---

## 32. Registration Architecture

**Public Registration Page** with steps (single page with dynamic sections):

1. **Choose Role** – buttons: "Tourism Business", "Tourism Professional", "Traveler".
2. **Provider Type Selection** (if business/professional): show list of provider types (checkboxes for multiple).
3. **Account Details**: name, email, password, phone.
4. **Provider Details** (if applicable): business name, description, contact info, address, logo upload.
5. **Plan Selection**: show plans (Free, Professional, Business, Enterprise) with feature comparison. Traveler skips.
6. **Verification**: (optional at first) upload documents for approval.
7. **Submit**: create user, provider (if applicable), assign provider types, subscribe to selected plan, log in, redirect to dashboard.

**Dynamic Fields**:
- Use **Laravel Form Requests** with conditional validation based on the selected provider type(s).
- JavaScript to show/hide sections.

---

## 33. Verification Architecture

**Verification Status** (enum): `pending`, `under_review`, `verified`, `rejected`, `suspended`.

**Documents**: Store in `verification_documents` table with file path, type, status.

**Workflow**:
1. Provider uploads documents during registration or later.
2. Status set to `pending`.
3. Super admin reviews documents and updates status to `verified` or `rejected`.
4. When verified, provider gets a verification badge on public profile.

**Note**: For MVP, verification can be manual; automation later (e.g., OCR).

---

## 34. Pricing Architecture

**Plans Table**:
- `id`, `name`, `slug`, `description`, `price_monthly`, `price_yearly`, `features` (JSON), `limits` (JSON).

**Features & Limits**:
- `max_listings`: number of services.
- `max_staff`: number of staff users.
- `max_ai_requests`: monthly AI uses.
- `max_bookings`: monthly booking limit.
- `analytics`: basic/advanced.
- `branding`: platform logo removal.
- `support`: community/email/priority.

**Pricing Display**:
- Dedicated `/pricing` page with a comparison table.
- Homepage shows a short version with CTA to pricing.

**Revenue Model Recommendation**:
- **Freemium + Hybrid**: Free plan (limited listings) to lower barrier. Paid plans add features and higher limits. Add a small commission (e.g., 3‑5%) on paid bookings to generate revenue while keeping subscription affordable. This aligns with marketplace models and encourages usage.
- Exact numbers: TBD in later business planning.

---

## 35. Subscription Architecture

- When a provider registers, they select a plan. A subscription record is created.
- Expiry handling: use a scheduled job to check expirations and update status.
- Upgrade/downgrade: can be done by updating `plan_id` and adjusting `end_date`; proration handled later.
- Payment gateways: design a `payments` table with `id, subscription_id, amount, status, gateway, reference`; but payment integration is Phase 8.

**Implementation**:
- `SubscriptionService` handles plan selection, renewal, expiration.

---

## 36. Marketplace Architecture

**Public Pages**:
- `/` – Homepage with featured services, AI planner, stats, etc.
- `/explore` – Search/filter results.
- `/services/{slug}` – Service detail page.
- `/providers/{slug}` – Provider profile page.
- `/categories/{slug}` – List of services in a category.

**Search**:
- Use Laravel Scout with Meilisearch for speed. For MVP, we can keep the current simple `where` queries; later replace with Scout.
- Filters: category, location (via location_id or city), price range, duration, difficulty (for treks), rating, availability.

**Listing Display**:
- Card layout with image, title, price, short description, rating.

**Provider Profile**:
- Show all services of that provider, verification badge, description, contact info, reviews.

---

## 37. Search Architecture

**Current**: Simple `where` clauses in `PublicTrekController`.

**Future**: Integrate Laravel Scout with Meilisearch/Elasticsearch for full‑text search and faceted filters.

**Implementation Plan**: Keep current for MVP; add Scout in Phase 7.

---

## 38. AI Architecture

**Current**: `ItineraryGenerator` service calls Groq API with a prompt.

**Future Enhancements**:
- Extend prompt to include available services (treks, tours, hotels) from the platform, so AI can recommend specific listings.
- Add a recommendation engine that suggests services based on user preferences.
- Use AI for dynamic pricing, automated quotations.

**Integration**: Keep the service layer; controllers will call `ItineraryService`. The AI logic should be decoupled.

---

## 39. QR / Safety Architecture

**QR Check‑in**:
- Each booking has a unique QR code.
- Scanning leads to a check‑in page where the checkpoint name and optional location are recorded.
- Store in `qr_scans` table.

**SOS**:
- Triggered via API with booking ID, location, message.
- Creates `sos_alert` and dispatches a notification job.
- Email sent to agency; future: SMS, push notifications.

**Preservation**: Both features will remain largely unchanged; we only update foreign keys to `bookings.id`.

---

## 40. Notification Architecture

**Current**: Only email for SOS.

**Future**:
- Notifications for booking creation, status updates, upcoming trips, etc.
- Use Laravel Notifications with channels (mail, SMS, database, broadcast).
- Store notifications in a `notifications` table (already provided by Laravel).
- Add a real‑time notification system (Pusher) for in‑app alerts.

**Design**:
- `Notification` model (Laravel's built-in) with a `notifiable` polymorphic relationship.
- Use events like `BookingCreated` to trigger notifications.

---

## 41. Reporting Architecture

- Provide export of bookings, revenue, provider statistics in CSV/Excel/PDF.
- Use Laravel Excel package (or custom CSV generation).
- Reports accessible from dashboard (provider and super admin).

**Implementation**: Create a `ReportService` that generates data arrays and passes to a `ReportController`. Use a queued job for large exports.

---

## 42. Review Architecture

- Travelers can leave a review after a booking is completed.
- Reviews linked to `booking_id` and `service_id`.
- Average rating displayed on service detail and provider profile.
- Use `reviews` table: `id`, `booking_id`, `rating`, `comment`, `created_at`.

**Implementation**: After booking status becomes `completed`, allow traveler to submit review.

---

## 43. Messaging Architecture

- Allow travelers to message providers (and vice versa) regarding bookings or inquiries.
- Use `messages` table: `id`, `sender_id` (user), `recipient_id` (user), `booking_id` (optional), `message`, `read_at`, `created_at`.
- Real‑time via WebSockets (Pusher) or polling.
- **Future**: not in MVP.

---

## 44. Authorization Architecture

**Policies**:
- Create a `ServicePolicy` with methods: `viewAny`, `view`, `create`, `update`, `delete` – all using `$user->canManageProvider($service->provider_id)`.
- Create a `BookingPolicy` with similar methods.
- Use `Gate` to define global permissions (e.g., `manage_system` for super admin).

**Ownership Checks**:
- In Policies, check that the authenticated user is either `super_admin` or owns the provider that owns the service/booking.
- For travelers: check that the booking belongs to the user.

**Global Scopes** (optional):
- For providers, we can use a global scope to automatically filter services and bookings by the user’s accessible provider IDs. This reduces duplicate checks.
- However, careful: public pages must not apply scope. Use `withoutGlobalScope()` in public contexts.

**Security**:
- **Authentication**: Use Laravel’s built‑in hashing, session, and token mechanisms.
- **Authorization**: Policies and Gates; no hard‑coded checks.
- **Data Isolation**: Global scopes where appropriate, but always test in all contexts.
- **File Uploads**: Validate file types, store in private directories with signed URLs if necessary; use `Storage::disk('public')` for images.
- **QR Codes**: Unique and random; no sensitive info.
- **SOS**: Require valid booking ID; rate limit to prevent abuse.
- **API**: Add `throttle` middleware; validate all inputs.
- **Mass Assignment**: Use `$fillable` and `$guarded` properly.
- **CSRF**: Enabled on web routes; API uses tokens.

---

## 45. Data Isolation Architecture

- Data isolation is critical: Provider A must never see Provider B’s data.
- We will enforce this at the **query level** using **global scopes** on `Service`, `Booking`, etc., to automatically filter by the current user’s accessible provider IDs.
- For `super_admin`, no scope is applied.
- In public contexts (e.g., homepage, explore), scopes are disabled.
- In all agency dashboard routes, scopes are active.

**Implementation**:
- Create a trait `HasProviderScope` that adds a global scope to models.
- For models that belong to a provider (`Service`, `Booking`), add the trait.
- In the scope, get `auth()->user()->accessibleProviderIds()` and filter the query.

---

## 46. Target Database ER Diagram

*(Diagram in text form – see detailed table structures above.)*

**Core Entities**:
- `users` (1) ──┬─ (many) `providers` (1) ──┬─ (many) `services` (1) ──┬─ (many) `bookings` (1) ──┬─ (many) `qr_scans`
-               │                           │                           │                           └─ (many) `sos_alerts`
-               │                           ├─ (many) `provider_staff`  ├─ (many) `service_attributes` (specialised tables)
-               │                           ├─ (many) `subscriptions`   └─ (many) `reviews`
-               │                           └─ (many) `verification_documents`
-               └─ (many) `provider_provider_type` ── (many) `provider_types`
- `plans` (1) ──┬─ (many) `subscriptions`
- `service_categories` (1) ──┬─ (many) `services`
- `locations` (1) ──┬─ (many) `services`, `providers`

---

## 47. Existing → Target Database Mapping

| Existing Table/Model | Target Table/Model | Mapping Strategy |
|----------------------|---------------------|------------------|
| `agencies`           | `providers` + `users` | For each agency, create a `User` (copy email, hashed password, set role `provider_owner`) and a `Provider` (link to user, copy business info). `agencies.role` → `users.role` (but we'll assign `provider_owner`). Keep `agencies` as reference until migration complete. |
| `trekkers`           | `users` (traveler role) or `guests` table | For each trekker, create a `User` with role `traveler` if email doesn't exist; if duplicate, skip. Or keep a separate `guests` table for non‑registered. We'll decide later. Keep `trekkers` table for now. |
| `treks`              | `services` + `trek_details` | For each trek, create a `Service` with `service_category_id` mapped from `category`. Create a `trek_details` row with duration, difficulty, itinerary. Keep `treks` as reference. |
| `bookings`           | `bookings` (new columns) | Add `traveler_id` and `service_id` columns. Fill by joining `trekker_id` → new `user` and `trek_id` → new `service`. Keep old columns until validated. |
| `agency` guard       | `auth` guard (User model) | Gradually switch to `User` model; run both guards in parallel; eventually deprecate. |
| `role` field         | `users.role` + provider types | Use `users.role` for system permissions; provider types stored in many‑to‑many. |

**Detailed Column Mapping:**

| Old Column (agencies) | New Column (users) | Transformation |
|-----------------------|--------------------|----------------|
| `id`                  | (used as foreign) | Keep as `agency_id` in `providers` for reference. |
| `name`                | `name`             | Copy. |
| `email`               | `email`            | Copy. |
| `password`            | `password`         | Copy directly (Laravel hashes). |
| `phone`               | `phone`            | Copy. |
| `address`             | (moved to `providers.address`) | Copy to provider. |
| `logo_url`            | (moved to `providers.logo_url`) | Copy to provider. |
| `role`                | `role`             | Map: `super_admin` → `super_admin`; `admin`/`agency` → `provider_owner`. |

| Old Column (treks) | New Column (services) | Transformation |
|--------------------|-----------------------|----------------|
| `id`               | (used as foreign) | Keep as `trek_id` in `services` for reference. |
| `agency_id`        | `provider_id`      | Map to `providers.id` via `agencies` mapping. |
| `name`             | `name`             | Copy. |
| `category`         | `service_category_id` | Map `trek`→1, `tour`→2, `hotel`→3. |
| `price`            | `price`            | Copy. |
| `cover_image`      | `cover_image`      | Copy. |
| `gallery`          | `gallery`          | Copy. |
| `itinerary`        | (moved to `trek_details.itinerary`) | Copy to `trek_details`. |
| `duration_days`    | (moved to `trek_details.duration_days`) | Copy. |
| `difficulty`       | (moved to `trek_details.difficulty`) | Copy. |

| Old Column (trekkers) | New Column (users) | Transformation |
|-----------------------|--------------------|----------------|
| `id`                  | (used as foreign) | Keep as `trekker_id` in `bookings` for reference. |
| `name`                | `name`             | Copy. |
| `email`               | `email`            | Copy. |
| `phone`               | `phone`            | Copy. |
| `emergency_contact`   | (moved to traveler profile) | Copy to `traveler_profiles` if we create that. |

| Old Column (bookings) | New Column (bookings) | Transformation |
|-----------------------|-----------------------|----------------|
| `trekker_id`          | `traveler_id`         | Map to new `users.id` via `trekkers` mapping. |
| `trek_id`             | `service_id`          | Map to new `services.id` via `treks` mapping. |
| `booking_date`        | `booking_date`        | Copy. |
| `start_date`          | `start_date`          | Copy. |
| `status`              | `status`              | Copy. |
| `qr_code`             | `qr_code`             | Copy. |
| `invoice_url`         | `invoice_url`         | Copy. |

| Old Table | New Table | Notes |
|-----------|-----------|-------|
| `qr_scans` | `qr_scans` | Add `booking_id` foreign key to new `bookings.id`. |
| `sos_alerts` | `sos_alerts` | Add `booking_id` foreign key to new `bookings.id`. |

---

## 48. Migration Strategy (Phased, Additive)

We adopt a **safe, additive migration** that does not break existing functionality.

### Stage 1 – Foundation Setup (Phase 1)
- Create new tables: `provider_types`, `service_categories`, `plans`, `subscriptions`, `locations`, `verification_documents`, `provider_provider_type`, `provider_staff`.
- Add columns to `users` (if needed) – role, phone, avatar.
- Do not touch `agencies`, `treks`, `bookings`, etc.

### Stage 2 – User/Provider Data Build (Phase 2)
- Add `user_id` to `agencies` (nullable).
- Write a console command to create `User` records for each `Agency` (copy email, set a hashed password, assign role `provider_owner`). Also create `Provider` records for each agency (using `user_id`). Link provider to provider_type(s) (for existing agencies, set type as "Trekking Agency", "Tour Agency", or "Hotel" based on business logic – we may need a manual mapping).
- After creating providers, update `agencies.user_id`.

### Stage 3 – Service Migration (Phase 3)
- Create `services` table and specialised tables (`trek_details`, etc.).
- For each `trek`, create a `service` record, mapping `agency_id` to `provider_id`, `category` to `service_category_id`.
- Create `trek_details` with existing data (duration, difficulty, itinerary). Keep `cover_image` and `gallery` in `services`.
- Add `service_id` to `treks` (nullable) for reference.

### Stage 4 – Booking Migration (Phase 4)
- Add `traveler_id` and `service_id` to `bookings` (nullable).
- Create `User` records for `trekkers` (if not already) via script (skip duplicates).
- Fill `traveler_id` and `service_id` using joins.
- After verification, make columns not nullable and drop `trekker_id`, `trek_id`.

### Stage 5 – Authentication Transition (Phase 5)
- Replace `auth:agency` middleware with a new middleware that checks both guards and eventually uses `auth` guard.
- Update controllers to use `auth()->user()` instead of `Auth::guard('agency')->user()`.
- Redirect old login/register routes to new ones; keep backward compatibility by checking if user exists in old system.

### Stage 6 – Dashboard Refactor (Phase 6)
- Rewrite `DashboardController` and others to work with new models.
- Update views to use new data.
- Introduce policies.

### Stage 7 – Public Marketplace Update (Phase 7)
- Update `PublicTrekController` to `ServiceController`.
- Use `services` table for listing, detail, and search.

### Stage 8 – Cleanup (Phase 8)
- Drop old tables (`agencies`, `treks`, `trekkers`) after ensuring no dependencies.
- Remove old guards and configuration.

**Rollback**: Each stage can be rolled back via migration rollback, as long as we keep old tables until the final stage.

---

## 49. Backward Compatibility Strategy

- **During migration**: Keep old tables and columns intact; new tables are additive.
- **Code**: Use conditional logic to check if old columns exist or use a version flag.
- **Routes**: Old routes continue to work; new routes introduced gradually.
- **Data**: We can run a migration to copy data, but do not delete old data until the system is fully verified.
- **API**: Existing API endpoints (`/api/itinerary/generate`, `/api/sos`) will continue to work; we only update the underlying logic to use new models.
- **Browser Testing**: After each stage, perform regression testing on all major flows.

---

## 50. Laravel Code Architecture (Target)

**Directory Structure**:
```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   ├── Provider/          # Provider dashboard controllers
│   │   ├── Public/            # Public website controllers
│   │   └── Auth/              # Authentication controllers
│   ├── Middleware/
│   ├── Requests/              # Form Requests
│   └── Resources/             # API Resources (if needed)
├── Models/
│   ├── User
│   ├── Provider
│   ├── ProviderType
│   ├── Service
│   ├── ServiceCategory
│   ├── Booking
│   ├── QrScan
│   ├── SosAlert
│   ├── Plan
│   ├── Subscription
│   ├── Review
│   └── ...
├── Services/
│   ├── ItineraryService
│   ├── BookingService
│   ├── ProviderService
│   ├── SubscriptionService
│   └── ...
├── Policies/
│   ├── ServicePolicy
│   ├── BookingPolicy
│   ├── ProviderPolicy
│   └── ...
├── Events/
│   ├── BookingCreated
│   ├── SOSReceived
│   └── ...
├── Listeners/
│   ├── SendBookingNotification
│   ├── SendSOSNotification
│   └── ...
├── Jobs/
│   ├── SendEmailJob
│   ├── ProcessBooking
│   └── ...
└── Console/
    ├── Commands/
    │   ├── MigrateAgencies
    │   ├── MigrateTreks
    │   └── ...
```

**Key Principles**:
- **Controllers**: thin; delegate to services.
- **Services**: contain business logic; called by controllers and commands.
- **Policies**: authorisation.
- **Events/Listeners**: decouple side effects.
- **Form Requests**: validation and authorisation.
- **Jobs**: asynchronous tasks (email, SMS, etc.).

---

## 51. Folder Structure Proposal

We will keep the current structure but add new directories:
- `app/Services/` for business logic.
- `app/Policies/` for authorisation.
- `app/Requests/` for form requests.
- `app/Events/` and `app/Listeners/` for event-driven architecture.
- `app/Console/Commands/` for migration scripts.

Existing controllers will be refactored gradually.

---

## 52. Testing Strategy

**Test Types**:
- **Unit**: Models, services, helpers.
- **Feature**: HTTP endpoints, authentication, authorisation.
- **Database**: Migrations, relationships.
- **Browser** (Dusk): Critical user journeys.

**Priority Tests**:
1. **Registration** (provider and traveler) – create users, providers, types.
2. **Login** – both guards (during transition).
3. **Booking** – guest and logged‑in.
4. **QR Check‑in** – create scan, verify booking relation.
5. **SOS** – create alert, check job dispatched.
6. **AI Itinerary** – valid/invalid inputs.
7. **Dashboard** – data displayed correctly.
8. **Provider Data Isolation** – user A cannot see user B’s data.
9. **Super Admin** – manage providers, toggle roles.
10. **Marketplace** – search, filter, detail pages.

**Continuous Integration**: Use GitHub Actions to run tests on push.

**Regression Rule**: After each phase, verify that all existing features (AI, booking, QR, SOS, dashboard) still work.

---

## 53. Security Strategy

- **Authentication**: Use Laravel’s built‑in hashing, session, and token mechanisms.
- **Authorization**: Policies and Gates; no hard‑coded checks.
- **Data Isolation**: Global scopes where appropriate, but always test in all contexts.
- **File Uploads**: Validate file types, store in private directories with signed URLs if necessary; use `Storage::disk('public')` for images.
- **QR Codes**: Unique and random; no sensitive info.
- **SOS**: Require valid booking ID; rate limit to prevent abuse.
- **API**: Add `throttle` middleware; validate all inputs.
- **Mass Assignment**: Use `$fillable` and `$guarded` properly.
- **CSRF**: Enabled on web routes; API uses tokens.

---

## 54. Performance Strategy

- **Indexes**: Ensure foreign keys and frequently queried columns are indexed.
- **Eager Loading**: Use `with()` to prevent N+1 queries.
- **Caching**: Use Laravel cache for homepage stats and frequent queries.
- **Pagination**: Use `paginate()` for large listings.
- **Queue**: Offload email and heavy processing to jobs.
- **Scout**: For search, integrate Meilisearch in Phase 7.

---

## 55. Rollback Strategy

- Each migration step is reversible via `php artisan migrate:rollback` if executed within the same transaction.
- Keep old tables/columns until final cutover, so if something fails, we can revert to old code and data.
- For data‑copying scripts, use transactions and log errors.
- After each phase, run a smoke test to confirm no regression. If any, rollback that phase and investigate.

---

## 56. Risk Analysis

| Risk | Impact | Mitigation |
|------|--------|------------|
| Data loss during migration | Critical | Always keep backups; use transactions; have rollback scripts. |
| Authentication breakage | High | Run both guards in parallel; test extensively; use feature flags. |
| Performance degradation | Medium | Add indexes; use caching; monitor queries. |
| Mis‑mapping of provider types | Medium | Use manual mapping seeders; involve business owners. |
| Existing bookings fail | High | Keep old columns until fully verified; write comprehensive tests. |
| Developer complexity | Medium | Document all changes; conduct code reviews. |
| User confusion (two systems) | Low | Communicate migration timeline to users; provide clear UI. |

---

## 57. Phase‑by‑Phase Roadmap

### Phase 1: Foundation (Months 1‑2)
- **Goal**: Set up new tables and basic models.
- **Tables**: `provider_types`, `service_categories`, `plans`, `subscriptions`, `locations`, `verification_documents`, `provider_provider_type`, `provider_staff`.
- **Models**: Create all new models with relationships.
- **Migrations**: Create migrations; seed initial data (provider types, service categories, plans).
- **Testing**: Unit tests for models and relationships.
- **Dependencies**: None.
- **Risk**: Minimal.
- **Completion**: All new tables exist and models are tested.

### Phase 2: User/Provider Integration (Months 2‑3)
- **Goal**: Link existing agencies to new `User` and `Provider`.
- **Add `user_id` to `agencies`**.
- **Write migration/command** to create `User` records for each agency and `Provider` records; link them.
- **Update `Agency` model** to have `user()` relationship.
- **Add `role` column to `users`** (enum).
- **Testing**: Verify that existing agencies now have corresponding users and providers.
- **Dependencies**: Phase 1.
- **Risk**: Data integrity; duplicate emails. We’ll handle duplicates gracefully.

### Phase 3: Service Migration (Months 3‑4)
- **Goal**: Migrate `treks` to `services` and create `trek_details`.
- **Create `services` table** and `trek_details` (and optionally `tour_details`, `hotel_details`).
- **Migration script** to copy data from `treks` to `services` and `trek_details`; map `agency_id` to `provider_id`; map `category` to `service_category_id`.
- **Add `service_id` to `treks`** for reference.
- **Update models**: `Trek` now belongs to `Service`.
- **Testing**: Verify all treks appear as services; relationships intact.
- **Dependencies**: Phase 2.
- **Risk**: Data loss; ensure all fields are transferred correctly.

### Phase 4: Booking Migration (Months 4‑5)
- **Goal**: Migrate `bookings` to use new `traveler_id` (User) and `service_id`.
- **Add columns**: `traveler_id`, `service_id` to `bookings`.
- **Create `User` records for trekkers** (if not already); link via `email`.
- **Fill `traveler_id` and `service_id`** using joins.
- **Drop old foreign keys and columns** after verification.
- **Testing**: Booking flows work, QR, SOS still functional.
- **Dependencies**: Phase 3.
- **Risk**: High; careful rollback plan.

### Phase 5: Authentication Transition (Months 5‑6)
- **Goal**: Switch to default `auth` guard for all users.
- **Create new login/register controllers** using `User` model.
- **Add middleware** to handle both guards.
- **Update all controllers** to use `auth()->user()`.
- **Deprecate old guard** routes.
- **Testing**: Registration, login, dashboard access for all roles.
- **Dependencies**: Phase 4.
- **Risk**: High; user sessions may break; thorough testing.

### Phase 6: Dashboard & Capabilities (Months 6‑7)
- **Goal**: Implement new provider dashboard with dynamic menus.
- **Refactor `DashboardController`** to use new models.
- **Implement menu system** based on provider types and permissions.
- **Update views**.
- **Testing**: Different provider types see correct modules.
- **Dependencies**: Phase 5.

### Phase 7: Public Marketplace (Months 7‑8)
- **Goal**: Update public pages to use `services` instead of `treks`.
- **Create `ServiceController`** for listing, detail, provider profile.
- **Update search/filters**.
- **Replace `treks` references** in views.
- **Testing**: Browse, search, detail, provider profile.
- **Dependencies**: Phase 6.

### Phase 8: Pricing & Subscriptions (Months 8‑9)
- **Goal**: Implement plan selection during registration; show pricing page.
- **Create `/pricing` page**.
- **Integrate plan selection** in registration.
- **Add subscription management** in dashboard.
- **Testing**: Registration with plan; subscription updates.
- **Dependencies**: Phase 7.

### Phase 9: Payments (Months 9‑10)
- **Goal**: Integrate a payment gateway (e.g., Stripe) for subscriptions and bookings (optional).
- **Create `payments` table**.
- **Implement payment flow**.
- **Testing**: End‑to‑end payment.
- **Dependencies**: Phase 8.

### Phase 10: Reviews & Notifications (Months 10‑11)
- **Goal**: Add reviews and system notifications.
- **Create `reviews` table**.
- **Implement rating after booking completion**.
- **Set up notification channels**.
- **Testing**: Review creation, notification triggers.
- **Dependencies**: Phase 9.

### Phase 11: Advanced AI, Safety, Analytics (Months 11‑12)
- **Goal**: Enhance AI, QR, SOS, and add analytics.
- **Extend AI to recommend services**.
- **Add analytics dashboards**.
- **Improve SOS with SMS**.
- **Testing**: All enhanced features.
- **Dependencies**: Phase 10.

### Phase 12: Mobile/PWA & Cleanup (Months 12‑14)
- **Goal**: Make the platform PWA‑ready; remove legacy code.
- **Add service worker, manifest**.
- **Offline support**.
- **Drop old tables and guards**.
- **Final testing**.
- **Dependencies**: Phase 11.

---

## 58. NOW vs NEXT vs LATER

| Category | Features |
|----------|----------|
| **NOW** (Phase 1‑4) | Foundation tables, user/provider migration, service migration, booking migration, authentication transition, dashboard refactor, public marketplace update. |
| **NEXT** (Phase 5‑8) | Pricing/subscription, payments, reviews, notifications, basic AI enhancements. |
| **LATER** (Phase 9‑12) | Advanced AI, analytics, PWA, offline, messaging, smart permits, multi‑language. |

---

## 59. What Must NOT Change Yet

- **Do not drop or rename** `agencies`, `treks`, `trekkers`, `bookings` tables.
- **Do not remove** the `agency` guard or auth middleware.
- **Do not modify** existing controller logic until the new system is ready and tests pass.
- **Do not change** existing routes or their names.
- **Do not remove** any existing feature (AI, QR, SOS, booking).
- **Do not make** major UI overhauls until the data layer is stable.

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

## 61. Final Implementation Order

**After approval, the exact sequence of first coding steps will be:**

1. **Create a new branch** from main (e.g., `feature/ecosystem-foundation`).
2. **Run a full backup** of the production database.
3. **Create migrations** for the new tables (Phase 1):
   - `provider_types`
   - `service_categories`
   - `plans`
   - `subscriptions`
   - `locations`
   - `verification_documents`
   - `provider_provider_type`
   - `provider_staff`
4. **Add columns** to `users` (role, phone, avatar) if not already present.
5. **Create models** for each new table with relationships.
6. **Seed** provider types, service categories, and a default plan (Free).
7. **Write unit tests** for the new models.
8. **Run migrations and tests** to confirm no regression.
9. **Create a console command** to migrate agencies to users/providers (Phase 2) – to be run later.
10. **Commit and push**; await review.

**Important**: These steps do not modify any existing tables or business logic. They are purely additive. After each step, the existing system continues to work.

---

## 62. Go / No‑Go Checklist

### ✅ APPROVED / SAFE TO IMPLEMENT (Architecture clear)

| Element | Status | Notes |
|---------|--------|-------|
| New tables: `provider_types`, `service_categories`, `plans`, `subscriptions`, `locations`, `verification_documents`, `provider_provider_type`, `provider_staff`. | ✅ | Approved. |
| New models for new tables. | ✅ | Approved. |
| Separate `User` and `Provider` concepts. | ✅ | Approved. |
| Role‑based permissions via Policies. | ✅ | Approved. |
| Gradual migration approach with stages. | ✅ | Approved. |
| Preservation of existing features. | ✅ | Approved. |
| Extensible service architecture with category‑specific details (specialised tables). | ✅ | Approved. |
| Future‑ready for hotels, guides, transport, etc. | ✅ | Approved. |
| Pricing/Subscription framework (tables only, no numbers). | ✅ | Approved. |
| Verification system (tables, statuses). | ✅ | Approved. |

### ❓ BUSINESS DECISION REQUIRED

| Decision | Options | Notes |
|----------|---------|-------|
| Exact pricing numbers for plans. | TBD | Not urgent; can be decided later. |
| Commission percentage on bookings. | TBD | Not urgent; can be decided later. |
| Payment gateway selection. | eSewa, Khalti, Stripe, etc. | Not urgent; can be decided later. |
| Which provider types to prioritise in MVP. | Trekking, Tour, Hotel vs. all | Initial focus on existing types; others later. |
| Whether to allow guest booking permanently or require registration. | Guest allowed vs. mandatory account | Guest booking is useful; we can keep it. |
| Scope of AI enhancements. | Basic vs. service integration | Service integration can be phased later. |

### ❓ TECHNICAL DECISION REQUIRED

| Decision | Options | Recommendation |
|----------|---------|----------------|
| Authentication cutover timing. | When to switch to `User` guard | After Phase 4 (booking migration) to ensure all data is ready. |
| Service polymorphism vs. dedicated detail tables. | Polymorphic vs. specialised tables | **Specialised tables** (e.g., `trek_details`) for clarity and ease of validation. |
| Search engine. | Current DB queries vs. Scout (Meilisearch) | Keep current for MVP; integrate Scout in Phase 7. |
| Notification infrastructure. | Mail, SMS, Database, Pusher | Start with Mail + Database; add others later. |
| Guest traveler storage. | Keep `trekkers` table vs. merge into `users` | Keep `trekkers` for now; merge later when traveler accounts are mandatory. |

---

**End of Master Document**

---

**This document is the single source of truth for the evolution of TravelAI Nepal. All future development decisions must align with this blueprint. Implementation will begin only after a formal Go decision from stakeholders.**

**Approval Signatures:**

- **Product Owner:** __________________
- **Technical Lead:** __________________
- **Business Stakeholder:** __________________

**Date of Approval:** __________________