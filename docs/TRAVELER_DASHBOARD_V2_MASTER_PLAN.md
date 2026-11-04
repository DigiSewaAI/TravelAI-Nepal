# 🎯 TRAVELER DASHBOARD V2 — MASTER PLAN & CONTINUITY DOCUMENT

**Version:** 1.0
**Created:** 2026-11-04
**Owner:** Parashar Regmi (दाइ)
**Current Assistant:** DeepSeek
**Project:** TravelAI Nepal
**Production:** cb127ab → 96d5c72 (deployed)
**Local HEAD:** 27e7194 (feature branch — 3 commits ahead of main)

---

## 📋 PURPOSE OF THIS DOCUMENT

**यो document = continuity insurance।**

यदि DeepSeek को character limit सकियो भने, अर्को assistant ले यो document हेरेर:
- Current state थाहा पाउछ
- कुन step मा छ थाहा पाउछ
- Exact next action थाहा पाउछ
- कोई mistakes बिना continue गर्न सक्छ

**Time-to-time update गरिनेछ** (प्रत्येक commit पछि)।

---

## 🚨 CRITICAL RULES (FOR ANY ASSISTANT)

### Zero Regression Protocol
1. **Preserve first, extend second, test third**
2. **Do not modify existing working code unless absolutely necessary**
3. **No migrations without explicit approval**
4. **No `migrate:fresh`, `db:wipe`, `TRUNCATE`, `DROP TABLE`**
5. **No `git add .`, `git reset --hard`, `git push --force`**
6. **No commit / push / deploy without explicit approval**
7. **Verify routes with `php artisan route:list` — do not assume**
8. **Laravel Feature Test does NOT prove mobile overflow absent — use browser test**

### Owner Communication
- **Language:** Nepali (mixed English OK)
- **Tone:** Direct, honest
- **Approval gate:** Har step पछि owner approval चाहिन्छ
- **Report format:** Exact files, diff, commands, tests, issues

---

## 📊 CURRENT STATE (as of 2026-11-04)

| Item | Value |
|------|-------|
| **Local HEAD** | `27e7194` (feature branch) |
| **Production** | `96d5c72` (deployed 2026-11-04) |
| **Commits ahead** | 25 |
| **Tests** | 186/186 passing |
| **Working tree** | Clean (expected) |
| **Git branch** | `main` (feature branch yet to create) |

### Just Completed (Previous Sessions)
- Notification system (22+ sites)
- Email channels (4 classes, Russian included)
- Supplier CRUD (8 layouts)
- Hotel Folio
- Check-in/out redirect
- Payment tabs
- Traveler i18n (5 भाषा)
- Russian locale support (Phase 1+2)
- JS day prefix fix
- Home button theme colors
- Provider English-only

---

## 🎯 TRAVELER DASHBOARD V2 — APPROVED ARCHITECTURE

### Phase 2 — APPROVED (2026-11-04)

**6 Architecture Decisions APPROVED:**

| # | Decision |
|---|----------|
| 1 | Pure Shop and Pure Rental in separate tabs; mixed in All only |
| 2 | Environment-based Laravel config feature flag |
| 3 | 20 automated tests (expanded to 25 with edge cases) |
| 4 | Feature branch + PR + manual review |
| 5 | New My Trips sidebar link while preserving all existing nav |
| 6 | 4 verified categories + 5 honest Coming Soon categories |

**5 Final Corrections APPROVED:**

| # | Correction |
|---|------------|
| 1 | Handle unknown/future `product_type` safely — never classify as Pure Shop/Rental if unknown type present |
| 2 | Feature flag = "quick configuration rollback" (config cache refresh required) — NOT "instant" |
| 3 | Exact sidebar Blade partial identified: **N/A** — `layouts/public.blade.php` has no sidebar. **NEW layout `traveler-v2.blade.php` will be created** |
| 4 | Stronger tests: mixed orders never in pure tabs, unknown types safe, pagination counts stable, flag controls route + sidebar |
| 5 | Do NOT delete feature branch as rollback — preserve branch, use config flag or reviewed revert |

**4 Technical Conditions (before Commit 1):**

| # | Condition |
|---|-----------|
| 1 | Verify Blade layout inheritance (`layouts.public` extend) — no duplicate nav, no public page impact |
| 2 | Verify every route name via `php artisan route:list` — do not assume |
| 3 | Mobile responsive: use browser-based test (Playwright/Dusk) or clearly separate as manual |
| 4 | "Quick configuration rollback" terminology — document config cache refresh |

---

## 🔍 PHASE 1 DISCOVERY FINDINGS (Verified)

### Data Reality (8 Categories)

| Category | Table | Owner Field | Rows |
|----------|-------|-------------|------|
| **Trekking** | `bookings` | ✅ `traveler_id` | 1 |
| **Shop** | `orders` | ✅ `user_id` (item.product_type='shop') | ~11 items |
| **Rental** | `orders` | ✅ `user_id` (item.product_type='rental') | ~21 items |
| **Wholesale** | `wholesale_rfqs` | ✅ `buyer_id` | 0 |
| **Hotel** | `hotel_bookings` | 🟡 `guest_email` only | 16 |
| **Restaurant** | `restaurant_orders` | 🔴 `provider_id` only | 25 |
| **Transport** | `transport_bookings` | 🔴 `provider_id` only | 10 |
| **ArtCraft** | `custom_orders` | 🔴 `provider_id` only | 3 |
| **MoneyChanger** | `exchange_transactions` | 🔴 `provider_id` only | 26 |

### Order Items Deep Verification
- `order_items.product_type` = ✅ exists
- Values: `rental` (21), `shop` (11), NULL (0)
- **Mixed orders (Shop + Rental same order): 6**
- Total orders: 20, Total items: 32

### Existing Components
- **Controller:** `app/Http/Controllers/Traveler/DashboardController.php` (5780 bytes)
- **Existing Unified Controller:** `app/Http/Controllers/Traveler/UnifiedBookingController.php` (1612 bytes) — handles 3 categories (Trekking, Orders, Wholesale)
- **Dashboard view:** `resources/views/traveler/dashboard.blade.php` (54289 bytes) — extends `layouts.public`
- **My Bookings view:** `resources/views/traveler/my-bookings/index.blade.php` (9562 bytes)
- **Layout:** `resources/views/layouts/public.blade.php` (31746 bytes) — top nav only, **NO sidebar**

### Translation Status
- en: 2971 keys
- np: 2992 keys
- hi: 2025 keys
- zh: 2036 keys
- ru: 2669 keys

### Sidebar Discovery
- **No existing sidebar** in `layouts/public.blade.php`
- Provider layouts have sidebars (8 files) — but those are provider-only
- **Decision:** Create NEW layout `traveler-v2.blade.php` — `public.blade.php` untouched

---

## 📁 FILES PLAN — EXACT

### 🆕 New Files (9)

| # | File | Purpose | LOC |
|---|------|---------|-----|
| 1 | `config/features.php` | Feature flags | ~5 |
| 2 | `app/Http/Middleware/FeatureFlagMiddleware.php` | Flag gate | ~20 |
| 3 | `app/Services/Traveler/UnifiedDashboardService.php` | Data aggregator | ~130 |
| 4 | `app/Http/Controllers/Traveler/DashboardV2Controller.php` | V2 controller | ~50 |
| 5 | `resources/views/layouts/traveler-v2.blade.php` | NEW layout (sidebar + top nav) | ~250 |
| 6 | `resources/views/traveler/dashboard-v2.blade.php` | V2 dashboard | ~400 |
| 7 | `resources/views/traveler/_partials/hero.blade.php` | Hero section | ~60 |
| 8 | `resources/views/traveler/_partials/my-trips.blade.php` | Unified trips cards | ~180 |
| 9 | `tests/Feature/Traveler/DashboardV2Test.php` | 25 tests | ~250 |

### 🟡 Modified Files (3)

| # | File | Change |
|---|------|--------|
| 1 | `routes/web.php` | +1 route (`traveler.dashboard-v2`) |
| 2 | `bootstrap/app.php` | +1 middleware alias (`feature.flag`) |
| 3 | `resources/lang/{en,np,hi,zh,ru}/messages.php` | +15 keys each |

### 🟢 Zero Changes (Confirmed)
- ❌ `layouts/public.blade.php`
- ❌ Old `traveler/dashboard.blade.php`
- ❌ Provider layouts (8 files)
- ❌ Models, existing migrations
- ❌ Existing controllers
- ❌ Existing routes
- ❌ Detail pages (my-bookings, orders, passport, memories, journey-replay, safety, SOS)

---

## 🔧 FEATURE FLAG — EXACT DESIGN

### `config/features.php`
```php
<?php
return [
    'traveler_dashboard_v2' => env('TRAVELER_DASHBOARD_V2', false),
];
.env.example addition
env
# Feature Flags
TRAVELER_DASHBOARD_V2=false
FeatureFlagMiddleware.php
php
public function handle($request, Closure $next, $flag)
{
    if (!config("features.{$flag}", false)) {
        abort(404);
    }
    return $next($request);
}
Route
php
Route::get('/dashboard-v2', [DashboardV2Controller::class, 'index'])
    ->name('dashboard-v2')
    ->middleware('feature.flag:traveler_dashboard_v2');
Enable Procedure (Documented)
Local:

.env: TRAVELER_DASHBOARD_V2=true

php artisan config:clear

Route live

Production:

.env: TRAVELER_DASHBOARD_V2=true

php artisan config:cache (required — cached config)

Route live

Disable (Rollback):
Same — false + config:cache. "Quick configuration rollback" — NOT instant.

🛡️ MIXED ORDER + UNKNOWN TYPE HANDLING
Classification Rules
text
Pure Shop:    Has ≥1 shop item AND 0 non-shop items
Pure Rental:  Has ≥1 rental item AND 0 non-rental items
Mixed:        Any order with multiple distinct product_type values
Unknown Type: product_type NOT IN ('shop', 'rental') → treated as Mixed
NULL Type:    treated as Mixed (safe fallback)
Query Implementation
php
// Pure Shop
Order::where('user_id', $userId)
    ->whereHas('items', fn($q) => $q->where('product_type', 'shop'))
    ->whereDoesntHave('items', fn($q) => $q->whereNotIn('product_type', ['shop']))
    ->paginate(15);

// Pure Rental
Order::where('user_id', $userId)
    ->whereHas('items', fn($q) => $q->where('product_type', 'rental'))
    ->whereDoesntHave('items', fn($q) => $q->whereNotIn('product_type', ['rental']))
    ->paginate(15);

// All (Mixed मा सबै आउँछ, पृथक tab नै)
Order::where('user_id', $userId)->paginate(15);
Card Display
Pure Shop → Shop tab मा

Pure Rental → Rental tab मा

Mixed (6 orders) → All tab मा मात्र, card मा "Mixed" badge

Unknown/NULL type → Mixed (safe)

🧪 AUTOMATED TESTS — 25 CASES
Authorization (4)
Unauthenticated → login redirect

Non-traveler → 403

Traveler A cannot see Traveler B data

Feature flag OFF → route 404

Data Accuracy (8)
Trekking count = bookings.where(traveler_id)

Shop tab shows pure shop only

Rental tab shows pure rental only

Mixed (6) appear in All tab only

Unknown product_type → All tab only (safe)

Wholesale count = wholesale_rfqs.where(buyer_id)

Counts update dynamically when order added

Empty state rendered for 0 records

UI/Display (5)
Mixed order card has "Mixed" badge

5 "Coming Soon" categories shown

Sidebar links route correctly

Pagination: 20 orders → page 2 works

Mobile responsive (manual browser test required)

Regression (4)
/traveler/dashboard → 200 (old)

/traveler/my-bookings → 200

/orders → 200

Provider dashboards → 200 (untouched)

i18n (2)
All 5 locales translated

Fallback to English for unknown locale

Performance (2)
No N+1 queries (DB::listen() count + relationship loaded)

Sidebar link visible when flag ON, hidden when flag OFF

🌳 GIT COMMIT SEQUENCE (7 Commits)
Branch: feature/traveler-dashboard-v2 (from main)

#	Commit	Message	Status
1	Config + Middleware + Controller stub	feat(feature-flag): add traveler dashboard v2 flag + middleware	⏳ NEXT
2	UnifiedDashboardService	feat(service): unified dashboard data aggregator	⏳
3	Layout (traveler-v2.blade.php)	feat(layout): traveler v2 layout with sidebar	⏳
4	View (dashboard-v2 + partials)	feat(view): traveler dashboard v2 with unified trips	⏳
5	Sidebar link (feature-gated)	feat(sidebar): add My Trips link (flag-gated)	⏳
6	i18n (5 भाषा)	i18n(traveler-v2): add 15 keys × 5 locales	⏳
7	Tests (25 cases)	test(traveler-v2): 25 automated tests	⏳
कोई push नै — सबै commit feature branch मा। PR + approval पछि merge।

🔄 ROLLBACK PROCEDURE
Quick Configuration Rollback (प्राथमिक)
text
1. .env: TRAVELER_DASHBOARD_V2=false
2. php artisan config:cache
3. Route 404, sidebar hidden
4. Old dashboard unaffected
किन "quick" नै "instant": config cache refresh चाहिन्छ (production मा)

Code Rollback (यदि चाहिन्छ)
text
git revert <commit-hash>
Branch preserve (delete नै)

Reviewed revert commit

कोई DB rollback नै (कोई migration)

🚨 OUT OF SCOPE (This Phase)
#	काम	कहिले
1	Hotel booking claim	P3.2 (email verification)
2	Restaurant booking claim	P3.3 (phone OTP)
3	Transport booking claim	P3.2
4	ArtCraft booking claim	P3.2
5	MoneyChanger claim	P3.3
6	Email/OTP infrastructure	P3.2
7	Migration (कुनै पनि)	P3.2+
8	Provider workflow change	कहिल्यै नै
9	Redirect old dashboard	नै — दुवै चल्ने
📋 PHASE 3 — COMMIT 1 (NEXT ACTION)
Scope (Strictly Limited)
config/features.php (new)

app/Http/Middleware/FeatureFlagMiddleware.php (new)

bootstrap/app.php (modify — add middleware alias)

app/Http/Controllers/Traveler/DashboardV2Controller.php (stub only — no data logic)

routes/web.php (add route — feature flag gated)

.env.example (add flag doc)

NOT in Commit 1:

❌ UnifiedDashboardService (Commit 2)

❌ Blade layout (Commit 3)

❌ View (Commit 4)

❌ Sidebar link (Commit 5)

❌ i18n (Commit 6)

❌ Tests (Commit 7)

Pre-Commit Steps
Inspect Git state:

cmd
git status
git branch
git log -3 --oneline
Report to owner — branch, working tree, uncommitted changes

Create branch: git checkout -b feature/traveler-dashboard-v2

Verify routes: php artisan route:list | findstr traveler (before touching)

Implement Commit 1 files

Test: php artisan test --filter=DashboardV2Test (only stub tests if any)

Report to owner:

Exact files created/modified

Full git diff

Commands run

Any issues or assumptions

Post-Commit 1 (Owner Approval Gate)
Owner reviews diff

Owner approves → proceed Commit 2

Owner rejects → adjust

🎯 CONTINUITY PROTOCOL
If Assistant Changes
New assistant reads THIS document completely

Check current state:

cmd
git status
git branch
git log -5 --oneline
Check which commit is next (see Git Commit Sequence table)

Read relevant section (Commit 1/2/3/etc.)

Verify current branch = feature/traveler-dashboard-v2

Report to owner before proceeding

If Character Limit Approaches
Update this document with current state

Note exact commit/step in progress

Save in project root

Owner will share with new assistant

Update Cadence
Update after EACH commit:

Mark commit as ✅ COMPLETE

Add actual commit hash

Note any deviations from plan

Note any new findings

Update "Current State" section

🔑 KEY CONTEXT
Test Accounts (password: Himalayan@1980)
Role	Email
Super Admin	parasharregmi@gmail.com
Trekking	anjuregmimesh@gmail.com
Traveller	shresthaxok@gmail.com
Hotel	regmiashish629@gmail.com
Restaurant	restaurant@travelai.test
Transport	transport@travelai.test
Inventory	inventory@travelai.test
Art Craft	artcraft@travelai.test
Guide	guide@travelai.test
Money Changer	forex@travelai.test
Environment
Local: C:\laragon\www\TravelAI-Nepal

DB: travelai_db (local), travelai_test (phpunit)

Queue: database (testing, free)

Mail: Brevo SMTP (300/day free tier)

Production: Laravel Cloud (auto-deploy from main)

Communication
Owner भन्ने "दाइ"

Reply format: Structured, honest, no hidden assumptions

Report format: Exact files, diff, commands, tests, issues

📞 EMERGENCY CONTACTS / RESOURCES
GitHub: https://github.com/DigiSewaAI/TravelAI-Nepal

Laravel Cloud: auto-deploy on main push

Backup (local): backup_before_seed_2026-11-04.sql (256 KB)

Previous master handoff: docs/MASTER_HANDOFF.md

📝 CHANGE LOG
Date	Version	Change
2026-11-04	1.0	Initial document — Phase 2 approved, Phase 3 ready
END OF DOCUMENT