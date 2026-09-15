# TravelAI Nepal — Master Audit & Fix Tracking Report

**Audit Start:** 2026-09-15
**Audit Status:** IN PROGRESS
**Last Updated:** 2026-09-15

---

## 📊 EXECUTIVE SUMMARY

| Metric | Value |
|---|---|
| Total Findings | 35+ |
| 🚨 CRITICAL | 5 |
| 🔴 HIGH | 8 |
| 🟡 MEDIUM | 6 |
| 🟢 LOW | 4 |
| ✅ PASS (verified working) | 15+ |
| ❓ NOT TESTABLE | 2 |

**Current Verdict:** 🚨 NOT READY FOR PRODUCTION

---

## 🎯 FIX PROGRESS TRACKER

| # | Finding | Severity | Status | Fixed On | Notes |
|---|---|---|---|---|---|
| F1 | Enterprise 1-click free upgrade | 🚨 CRITICAL | ⏳ PENDING | — | UI confirmed |
| F2 | Booking Confirmation IDOR | 🚨 CRITICAL | ⏳ PENDING | — | Guest+cross-user |
| F3 | Feature Gating (5 features) | 🚨 CRITICAL | ⏳ PENDING | — | Analytics Free को |
| F4 | Guest → services/create (200) | 🚨 CRITICAL | ⏳ PENDING | — | UI exposed |
| F5 | Guest → analytics (500 trace) | 🚨 CRITICAL | ⏳ PENDING | — | Info leak |
| F6 | Booking limit enforcement | 🔴 HIGH | ⏳ PENDING | — | No table/service |
| F7 | Provider routes auth middleware | 🔴 HIGH | ⏳ PENDING | — | `web` only |
| F8 | Subscription expiry automation | 🔴 HIGH | ⏳ PENDING | — | No cron |
| F9 | Webhook signature verification | 🔴 HIGH | ⏳ PENDING | — | Code-confirmed |
| F10 | Public booking path broken | 🔴 HIGH | ⏳ PENDING | — | Trekker table |
| F11 | API key prefix logging | 🔴 HIGH | ⏳ PENDING | — | Logs मा |
| F12 | Stripe NPR currency | 🔴 HIGH | ⏳ PENDING | — | Verify needed |
| F13 | API throttling missing | 🟡 MEDIUM | ⏳ PENDING | — | All public routes |
| F14 | `isActive()` end_date check | 🟡 MEDIUM | ⏳ PENDING | — | Missing |
| F15 | AI race condition | 🟡 MEDIUM | ⏳ PENDING | — | CODE-ONLY |
| F16 | Quota consume before LLM | 🟡 MEDIUM | ⏳ PENDING | — | Fail quota खान्छ |
| F17 | Staff dual-system dead code | 🟡 MEDIUM | ⏳ PENDING | — | users.provider_id |
| F18 | Currency symbol mismatch | 🟡 MEDIUM | ⏳ PENDING | — | $ vs Rs. |
| F19 | Route `/api/*` in web.php | 🟢 LOW | ⏳ PENDING | — | Convention |
| F20 | Error file/line leak | 🟢 LOW | ⏳ PENDING | — | APP_DEBUG |
| F21 | SSL verify=false in LLM | 🟢 LOW | ⏳ PENDING | — | MITM risk |
| F22 | Service inactive count | 🟢 LOW | ⏳ PENDING | — | Product decision |
| F23 | LLM hallucination (fake contact) | 🟡 MEDIUM | ⏳ PENDING | — | Prompt fix |

---

## 📋 DETAILED FINDINGS

### 🚨 F1 — Enterprise 1-Click Free Upgrade

**Phase:** 6 (UI) + 5 (API)
**Severity:** 🚨 CRITICAL
**Classification:** CONFIRMED RUNTIME VULNERABILITY

**Expected:** Enterprise plan "Custom pricing" → contact sales → pending subscription
**Actual:** Free user → "Switch to Enterprise" click → **active Enterprise subscription** (payment बिना)

**Evidence:**
- Phase 5: `POST /register` with `plan=enterprise` → active subscription
- Phase 6: UI click → Subscription ID 15 created, `price_monthly = NULL`
- Code: `RegisterController::isFree` check → `(null ?? 0) == 0` → TRUE

**Root Cause:**
```php
$isFree = ($plan->price_monthly ?? 0) == 0 && ($plan->price_yearly ?? 0) == 0;
// Enterprise: (null ?? 0) == 0 → TRUE