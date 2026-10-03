# PRODUCTION SEEDERS SUMMARY

**Deploy Reference — TravelAI Nepal**
**Generated:** 2026-09-27
**HEAD:** eb94587
**Purpose:** Production मा कुन seeder run गर्ने, कुन नगर्ने — clear reference.

---

## 📊 Overview

| Category | Count |
|---|---|
| **Total seeder files** | 44 |
| **Run in production** (called by DatabaseSeeder.php) | 37 |
| **Skip in production** | 7 |
| **🔒 Locked (never touch)** | 14 |

---

## ✅ SECTION 1 — Production मा RUN गर्ने Seeders (37)

**यी seeders `DatabaseSeeder.php` मा call गरिएका छन्।** `php artisan db:seed --force` चल्दा automatically run हुन्छन्।

### 1.1 Base / Reference Data (4)

| # | Seeder | Purpose |
|---|---|---|
| 1 | `ProviderTypeSeeder` | Provider type reference |
| 2 | `ServiceCategorySeeder` | Service categories (hotel/guide/transport) |
| 3 | `PlanSeeder` | Subscription plans (Free/Professional/Business/Enterprise) |
| 4 | `LocationSeeder` | Base location master data |

### 1.2 Route Seeders (14)

| # | Seeder | Purpose |
|---|---|---|
| 5 | `AbcRouteSeeder` | Annapurna Base Camp route |
| 6 | `EbcRouteSeeder` | Everest Base Camp route |
| 7 | `LangtangRouteSeeder` | Langtang route |
| 8 | `AnnapurnaRegionSeeder` | Annapurna region routes |
| 9 | `EverestRegionSeeder` | Everest region routes |
| 10 | `LangtangHelambuManasluRegionSeeder` | Langtang/Helambu/Manaslu |
| 11 | `MustangDolpoRegionSeeder` | Mustang/Dolpo region |
| 12 | `KanchenjungaMakaluRegionSeeder` | Kanchenjunga/Makalu |
| 13 | `RemoteTreksSeeder` | Remote treks |
| 14 | `CityCulturalToursSeeder` | City/cultural tours |
| 15 | `NationalParksSeeder` | National parks |
| 16 | `ReligiousSitesSeeder` | Religious sites |
| 17 | `AdventureActivitiesSeeder` | Adventure activities |
| 18 | `HiddenGemsSeeder` | Hidden gems |

**Expected result:** 138 active routes created.

### 1.3 Waypoint ↔ Location Sync (2)

| # | Seeder | Purpose |
|---|---|---|
| 19 | `WaypointLocationSeeder` | Sync waypoint ↔ location mappings |
| 20 | `SyncMissingLocationsSeeder` | Fill missing locations |

### 1.4 Category Assignment (1)

| # | Seeder | Purpose |
|---|---|---|
| 21 | `AssignRouteCategoriesSeeder` | Link routes to categories |

### 1.5 Provider Seeders (12) — ⚠️ See Section 5.2

| # | Seeder | Purpose |
|---|---|---|
| 22 | `AnnapurnaProviderSeeder` | Demo provider (Annapurna) |
| 23 | `EverestProviderSeeder` | Demo provider (Everest) |
| 24 | `LangtangProviderSeeder` | Demo provider (Langtang) |
| 25 | `ManasluProviderSeeder` | Demo provider (Manaslu) |
| 26 | `KanchenjungaMakaluProviderSeeder` | Demo provider |
| 27 | `MustangDolpoProviderSeeder` | Demo provider |
| 28 | `NationalParksProviderSeeder` | Demo provider |
| 29 | `ReligiousSitesProviderSeeder` | Demo provider |
| 30 | `HiddenGemsProviderSeeder` | Demo provider |
| 31 | `CityCulturalProviderSeeder` | Demo provider |
| 32 | `AdventureActivitiesProviderSeeder` | Demo provider |
| 33 | `RemoteTreksProviderSeeder` | Demo provider |

**⚠️ Owner decision required — Section 5.2 हेर्नु।**

### 1.6 Services (2)

| # | Seeder | Purpose |
|---|---|---|
| 34 | `ServiceSeeder` | Base services |
| 35 | `ServiceLocationSeeder` | Service ↔ location links |

### 1.7 Final Assignment (2)

| # | Seeder | Purpose |
|---|---|---|
| 36 | `TourismProvidersSeeder` | Tourism providers + reviews |
| 37 | `AssignProviderTypesSeeder` | Provider type assignments |

---

## ❌ SECTION 2 — Production मा RUN नगर्ने Seeders (7)

| # | Seeder | Reason | Status |
|---|---|---|---|
| 1 | `TestingDataSeeder` | Dev/test fake data (John Adreson) | 🟢 Commented out in DatabaseSeeder |
| 2 | `UserSeeder` | 🔒 Owner manual control | Locked |
| 3 | `AchievementSeeder` | DB currently 0 rows — verify first | 🟡 Ticket |
| 4 | `SafetySourceSeeder` | Placeholder URL (`example.com/feed`) | 🟡 Ticket |
| 5 | `Phase4RFixSeeder` | One-time fix — already applied | 🗑️ Delete candidate |
| 6 | `BaseProviderSeeder` | Abstract base class (not a runnable seeder) | 🔒 Never delete |
| 7 | *(reserved for future use)* | — | — |

---

## 🔒 SECTION 3 — LOCKED Seeders (Never Touch)
🔒 UserSeeder
Reason: Owner manual control
Action: कुनै change नगर्नु (add/delete/modify)
Status: Existing state = final

🔒 Provider Seeders (12)
Reason: Owner testing + client demos
Action: कुनै delete नगर्नु (files stay in repo)
Production: Owner deletes manually IF needed

🔒 BaseProviderSeeder
Reason: Abstract base class — 12 provider seeders extend गर्छन्
Action: 🔴 NEVER delete (would crash all provider seeders)

**= Future sessions: यी locks respect गर्नु।**
---

## 🚀 SECTION 4 — Deployment Step-by-Step

```bash
# STEP 1 — Pull latest code
git pull origin main

# STEP 2 — Install PHP dependencies (production)
composer install --no-dev --optimize-autoloader

# STEP 3 — Install + build frontend
npm ci
npm run build

# STEP 4 — Environment setup
cp .env.example .env
php artisan key:generate

# STEP 5 — Configure .env
#   • DB_DATABASE, DB_USERNAME, DB_PASSWORD
#   • MAIL_* (SMTP credentials)
#   • AI providers (GROQ_API_KEY, OPENROUTER_API_KEY, GEMINI_API_KEY)
#   • APP_URL (production domain)
#   • APP_ENV=production, APP_DEBUG=false

# STEP 6 — Run migrations
php artisan migrate --force

# STEP 7 — Seed database (runs DatabaseSeeder → 37 seeders)
php artisan db:seed --force

# STEP 8 — Cache for performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# STEP 9 — Scheduler (cron)
# * * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1

# STEP 10 — Queue worker (if needed)
# php artisan queue:work --daemon

# STEP 11 — Verify
#   • Site loads: https://yourdomain.com
#   • Admin login (Section 5.1 if UserSeeder not run)
#   • AI Planner generates
#   • Flow 2 quotation generates

---

## ⚠️ SECTION 5 — Post-Deploy Manual Steps

### 5.1 UserSeeder (Manual — Owner Decision)

`UserSeeder` DatabaseSeeder मा automatically call हुँदैन।

Admin user create गर्न production मा:

```bash
php artisan db:seed --class=UserSeeder --force


### 5.3 Achievement Seeder (Post-Deploy Verify)

`AchievementSeeder` DB = 0 rows। Ticket: `ACHIEVEMENT-SEEDER-EMPTY-01`

---

## 🔄 SECTION 6 — Rollback Plan

Deploy fail भएमा:

```bash
# STEP 1 — Restore DB from backup
mysql -u root -p travelai_db < database/backups/backup_YYYYMMDD.sql

# STEP 2 — Revert code (R14 — NO force push)
git revert <bad-commit-hash> --no-edit
git push origin main

# STEP 3 — Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# STEP 4 — Contact Master

## 🎫 SECTION 7 — Ticket Summary (Post-Deploy)

| Ticket | Priority | Phase |
|---|---|---|
| `AI-PLANNER-DAYS-PADDING-01` | 🟢 LOW | Post-deploy |
| `ACHIEVEMENT-SEEDER-EMPTY-01` | 🟡 MED | Post-deploy |
| `SAFETY-SOURCE-URL-REAL-01` | 🟡 MED | Post-deploy |
| `CLEANUP-AUDIT-ARTIFACTS-01` | 🟢 LOW | Post-deploy |
| `JOURNEY-REPLAY-SHARE-LABEL-01` | 🟢 LOW | Post-deploy |
| `AI-QUOTATION-NARRATIVE-QUALITY-01` | 🟡 MED | Post-deploy |
| `AI-QUOTATION-PAX-FORM-01` | 🟡 MED | Post-deploy |
| `ROUTE-EBC-DESCENT-01` | 🟡 MED | Next session |
| `SAFETY-TEST-TYPE-ERROR-01` | 🟡 MED | Next session |
| `PAYMENT-STRIPE-REMOVAL-01` | 🔴 HIGH | Phase 7 |
| `PAYMENT-SUBSCRIPTION-LAYER1-01` | 🔴 HIGH | Phase 7 |
| `PAYMENT-PROVIDER-DIRECT-01` | 🟡 MED | Phase 7 |
| `PAYMENT-DATA-PROVIDER-MAPPING-01` | 🟡 MED | Phase 7 |

---

## 📌 Quick Reference

**✅ Run in production (automatic):**
```bash
php artisan db:seed --force

```markdown
```

= Runs 37 seeders

**❌ Do NOT run:** TestingDataSeeder, UserSeeder, AchievementSeeder, SafetySourceSeeder, Phase4RFixSeeder

**🔒 NEVER touch:** UserSeeder, Provider seeders (12), BaseProviderSeeder

---

**End of Summary** | 2026-09-27 (HEAD eb94587)
```
