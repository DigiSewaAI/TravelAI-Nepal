# Service (Listing) Policy

**Last updated:** 2026-09-16  
**Applies to:** FIX-15  
**Status:** Canonical reference

---

## 1. Terminology

### 1.1 Service vs Listing

A **Service** is the canonical code/database entity (`app/Models/Service.php`, table `services`).

A **Listing** is the product/marketing term for the same entity — a provider's product offering (trek, tour, hotel stay, activity, guide service, transport).

**These refer to the same entity.** The database column, plan-limit key (`max_listings`), and code class (`Service`) are used consistently.

### 1.2 Active vs Inactive

- `status = 'active'` — visible in public listings; counts toward plan limit
- `status = 'inactive'` — hidden from public; does **not** count toward plan limit

---

## 2. Plan Limits

| Plan | `max_listings` | Behavior |
|---|---|---|
| Free | 3 | 3 active services |
| Professional | 20 | 20 active services |
| Business | 100 | 100 active services |
| Enterprise | **-1** | Unlimited |

**Plan-limit resolution:**
- Source: `plans.limits` JSON column (see `database/seeders/PlanSeeder.php`)
- Helper: `Provider::getMaxListingsAttribute()`
- Returns the raw plan value (`-1` for Enterprise)
- Callers convert `-1 → PHP_INT_MAX` for boundary comparison

---

## 3. Authorization Model

### 3.1 Policy

`app/Policies/ServicePolicy.php`

| Method | Allows |
|---|---|
| `viewAny(User)` | Provider owner OR super admin |
| `view(User, Service)` | Super admin OR the owning provider |
| `create(User)` | Provider owner OR super admin |
| `update(User, Service)` | Super admin OR the owning provider |
| `delete(User, Service)` | Super admin OR the owning provider |

### 3.2 Registration

`app/Providers/AuthServiceProvider.php:28`:

```php
Service::class => ServicePolicy::class,