# Production Seeders Summary (v2)

**Last Updated:** 2026-10-03
**HEAD:** 43ce2f9
**Supersedes:** PRODUCTION_SEEDERS_SUMMARY_ARCHIVED_2026-09-27.md

---

## Strategy: Hybrid Deployment

- **Production DB = fresh** (migrations only)
- **Real entities migrated** = 3 users + 2 providers + services + bookings (idempotent seeders)
- **Curated demo data** = 5 providers (1 per category) with 2 services each
- **No 700+ fake providers** in production
- **Real providers** join naturally post-deploy

---

## Production-Safe Seeders (run via `php artisan db:seed --force`)

### Core Data
- ProviderTypeSeeder
- ServiceCategorySeeder
- PlanSeeder
- LocationSeeder

### Route Data (Sacred — AI Planner depends)
- 14 route seeders (Abc, Ebc, Langtang, Annapurna, Everest, etc.)
- WaypointLocationSeeder
- SyncMissingLocationsSeeder
- AssignRouteCategoriesSeeder

### Admin User
- **ProductionAdminSeeder** (env-driven, idempotent)
  - Defaults: Parashar Regmi (parasharregmi@gmail.com)
  - Can override via `.env`: ADMIN_EMAIL, ADMIN_PASSWORD, ADMIN_NAME, ADMIN_PHONE
  - Role: `super_admin`

### Real Entities (Migrated from Local)
- **RealEntitiesSeeder** (idempotent)
  - Users: Anju Regmi, Pareen Regmi, John Adreson
  - Providers: The Himalayan Journey (HJO), The Himalayan Travels Pvt. Ltd (HTP)
  - Services: 5 (4 active + 1 inactive for booking FK integrity)
  - Bookings: 3 (HJO-BK-26-00001/2, HTP-BK-26-00001)

### Demo Providers (Fresh)
- **DemoProvidersSeeder** (idempotent)
  - 1 provider per remaining category
  - Categories: Hotel, Activity, Experience, Resort, Homestay
  - Each provider has 2 demo services

---

## Dev-Only Seeders (SKIPPED in production via env guard)

Wrapped in `if (!app()->environment('production'))`:
- 12 Provider Seeders (Annapurna, Everest, Langtang, etc.)
- ServiceSeeder + ServiceLocationSeeder
- TourismProvidersSeeder
- AssignProviderTypesSeeder
- TestingDataSeeder (already commented)

---

## Locked Seeders (never run in production)

- UserSeeder (hardcoded password + real PII — see tickets)
- AchievementSeeder
- Phase4RFixSeeder
- SafetySourceSeeder
- BaseProviderSeeder (abstract)

---

## Deploy Runbook

### 1. Set env vars in production `.env`

```
ADMIN_EMAIL=parasharregmi@gmail.com
ADMIN_NAME=Parashar Regmi
ADMIN_PASSWORD=Himalayan@1980
ADMIN_PHONE=9761762036
```

### 2. Run migrations

```bash
php artisan migrate --force
```

### 3. Run seeders (env guards auto-skip test data)

```bash
php artisan db:seed --force
```

### 4. Verify admin login

Login at `/login` with `ADMIN_EMAIL` + `ADMIN_PASSWORD`.

### 5. Verify migrated entities

- Provider: The Himalayan Journey (code: HJO) — 4 active services
- Provider: The Himalayan Travels Pvt. Ltd (code: HTP) — 1 active service
- Demo providers: 5 (1 per category: Hotel, Activity, Experience, Resort, Homestay)

### 6. Post-seed (optional)

Curate additional demo services via Admin UI as needed.

---

## Migration Details

### Users (3 migrated + 1 admin)
| Email | Name | Role | Source |
|-------|------|------|--------|
| parasharregmi@gmail.com | Parashar Regmi | super_admin | ProductionAdminSeeder |
| anjuregmimesh@gmail.com | Anju Regmi | provider_owner | RealEntitiesSeeder |
| regmiashish629@gmail.com | Pareen Regmi | provider_owner | RealEntitiesSeeder |
| shresthaxok@gmail.com | John Adreson | traveler | RealEntitiesSeeder |

### Providers (2 migrated + 5 demo)
| Code | Name | Category Focus | Source |
|------|------|----------------|--------|
| HJO | The Himalayan Journey | Trek/Tour/Lodge/Guide/Transport | RealEntitiesSeeder |
| HTP | The Himalayan Travels Pvt. Ltd | Transport | RealEntitiesSeeder |
| DMH | Himalayan View Hotel | Hotel | DemoProvidersSeeder |
| DMA | Adventure Nepal Sports | Activity | DemoProvidersSeeder |
| DME | Nepal Cultural Experiences | Experience | DemoProvidersSeeder |
| DMR | Pokhara Lakeside Resort | Resort | DemoProvidersSeeder |
| DMH2 | Gurung Village Homestay | Homestay | DemoProvidersSeeder |

### Bookings (3 migrated)
| Number | Traveler | Service | Status |
|--------|----------|---------|--------|
| HJO-BK-26-00001 | John Adreson | Annapurna Base Camp Trek | completed |
| HJO-BK-26-00002 | John Adreson | Everest Base Camp Trek (hidden) | confirmed |
| HTP-BK-26-00001 | John Adreson | Kathmandu → Pokhara Transfer | confirmed |

**Note:** Service 28 ("EBC - 14 Days" — weak data) is preserved as `status=inactive` so Booking 31's FK remains intact. Public does not see it.

---

## Tickets (Related)

- 🔴 SEEDER-USER-HARDCODED-PWD-01 (security — UserSeeder has hardcoded password)
- 🔴 SEEDER-USER-REAL-PII-01 (privacy — UserSeeder has real PII)
- 🟢 SEEDER-PROVIDER-AUDIT-01 (this task)

---

## Rollback

If deploy fails:
1. Restore DB: `mysql -u travelai -p travelai_db < backup.sql`
2. Revert code: `git revert <bad-commit> --no-edit && git push origin main`
3. Clear caches: `php artisan optimize:clear`

---

**End of Summary v2** | 2026-10-03