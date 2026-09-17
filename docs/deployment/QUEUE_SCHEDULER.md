# TravelAI Nepal — Queue & Scheduler Deployment Guide

**Scope:** F8-02 — Queue / Scheduler deployment readiness  
**Status:** Authoritative reference for production worker + cron setup  
**Last Updated:** 2026-09-17

---

## 1. Overview

TravelAI Nepal registers **6 scheduled jobs** and several queueable jobs.
In production, they require:

1. A **long-running queue worker** (`php artisan queue:work`)
2. A **cron entry** that runs `php artisan schedule:run` every minute

Locally, `QUEUE_CONNECTION=sync` runs jobs inline — this is **not**
appropriate for production. Production MUST use a real queue
(`database` or `redis`).

---

## 2. Registered Scheduled Jobs

| Cadence | Job | Purpose |
|---|---|---|
| `*/5 * * * *` | `FetchSafetySourcesJob` | Ingest safety data sources |
| `0 0 * * *` | `VerifyExpiredIncidentsJob` | Verify stale incidents |
| `*/15 * * * *` | `UpdateSafetyStatusesJob` | Refresh safety statuses |
| `0 0 * * *` | `ExpireSubscriptionsJob` | FIX-07 subscription expiry |
| `0 0 * * *` | `CleanupStripeWebhookEventsJob` | FIX-08 webhook retention |
| `*/5 * * * *` | `ReleaseStaleAiReservationsJob` | FIX-12 AI reservation cleanup |

All jobs use `withoutOverlapping()` (see `routes/console.php`).

Verify in production: