# TravelAI Nepal — Production Environment Guide

**Scope:** F8-01 — Production environment hardening  
**Status:** Authoritative reference for production `.env` setup  
**Last Updated:** 2026-09-17

---

## 1. Overview

Production uses a **separate** `.env.production` file, created from
`.env.production.example`. It is **never committed** to version control.

The local development `.env` remains unchanged and continues to use
`APP_ENV=local`.

---

## 2. Required Production Values

| Key | Required value | Reason |
|---|---|---|
| `APP_ENV` | `production` | Disables debug tooling; enables production paths |
| `APP_DEBUG` | `false` | **MUST be false** — prevents stack-trace exposure |
| `APP_URL` | `https://…` | Correct URL generation, HTTPS scheme |
| `APP_TIMEZONE` | `UTC` | Preserves Phase 6 timezone hardening |
| `APP_KEY` | fresh `php artisan key:generate` | Never reuse the dev key |
| `LOG_LEVEL` | `warning` (or `error`) | Never `debug` in production |
| `SESSION_SECURE_COOKIE` | `true` | HTTPS-only cookies |
| `SESSION_ENCRYPT` | `true` | Encrypted session payloads |
| `SESSION_HTTP_ONLY` | `true` | Blocks JavaScript cookie access |
| `SESSION_SAME_SITE` | `lax` (or `strict`) | CSRF mitigation |
| `QUEUE_CONNECTION` | `database` (or `redis`) | Never `sync` in production |

---

## 3. Secrets — Supply Externally

Production secrets MUST be supplied through **one** of:

- Server environment variables (managed by hosting panel)
- A secret manager (AWS Secrets Manager, Doppler, Vault, etc.)
- A locally-stored `.env.production` (permissions `600`, owner = web user)

**Never** commit real secrets to git.

**Required secrets in production:**

- `APP_KEY`
- `DB_PASSWORD`
- `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`
- `GROQ_API_KEY`
- `OPENWEATHER_API_KEY`
- `MAIL_PASSWORD`
- Optional: `TWILIO_TOKEN`, `NEPAL_SMS_API_KEY`, `AWS_SECRET_ACCESS_KEY`

---

## 4. HTTPS Requirements

- The public site MUST be served over HTTPS.
- `SESSION_SECURE_COOKIE=true` is enforced.
- `URL::forceScheme('https')` is applied automatically in production
  (see `app/Providers/AppServiceProvider.php`).
- The Stripe webhook endpoint (`POST /webhook/stripe`) MUST be HTTPS.

---

## 5. Deployment Sequence (Reference)

Run on the production server, in this order:
