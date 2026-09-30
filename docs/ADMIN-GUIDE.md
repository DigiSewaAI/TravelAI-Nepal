# TravelAI Nepal — Admin Guide

**Audience:** Platform Admin (Owner / Super Admin)
**Version:** 1.0
**Last Updated:** 2026-09-30
**Status:** Authoritative reference for admin operations

> **Note:** All commands are Windows CMD compatible (Laragon environment) unless stated otherwise.

---

## 1. Admin Overview

### 1.1 Login

**URL:**
- Production: `https://travelainepal.com/admin/dashboard`
- Local: `http://localhost:8000/admin/dashboard`

**Credentials:** Admin email + password (created during seeder run — see `database/seeders/UserSeeder.php`).

**After login:** Redirected to Admin Dashboard.

### 1.2 Sidebar Navigation

| Menu | Purpose |
|------|---------|
| Dashboard | Overview stats + recent activity |
| Providers | Manage provider accounts + verification |
| Users | All users (travelers + providers + admins) |
| Safety | Safety dashboard, incidents, sources, audit log |
| Services | All services (trek / tour / hotel / transport) |
| Bookings | All bookings (filter by status) |
| Subscriptions | Provider subscription management |
| Payments | All payment records |
| Verify Payments | Pending payment queue (Phase 7D) |
| Invoices | Admin invoice list |
| Routes / Waypoints / Segments / Route Costs | Geographic data |
| Reports | Bookings, payments, providers analytics |
| Settings | Platform-wide settings |

---

## 2. Payment Verification (Primary Daily Task)

### 2.1 Overview

Providers submit payment proof manually (bank transfer / eSewa / Khalti). Admin verifies each payment. Two possible outcomes:

| Action | Result |
|--------|--------|
| Verify (Approve) | Payment → `verified`; Subscription → `active` |
| Reject | Payment → `rejected`; Subscription → `cancelled`; Refund reminder added |

### 2.2 Access the Queue

**URL:** `/admin/payments/verify`

**Shows:**
- Stats: Pending / Verified Today / Rejected This Week / Revenue (month)
- Pending table (provider, plan, amount, method, reference, receipt, actions)
- Recent Activity (latest verified + rejected)

### 2.3 Verify Flow (Approve)

**Step 1:** Click "View" under Receipt column to open the uploaded payment proof.

**Step 2:** Cross-check with bank statement:
- Amount matches?
- Reference number matches?
- Date reasonable?
- Receipt not edited / fake?

**Step 3:** If all good → Click "Verify" button → Confirm dialog → Payment approved.

**System effect:**
- `payments.status = 'verified'`
- `payments.verified_by = admin_id`
- `payments.verified_at = now()`
- `subscriptions.status = 'active'`
- Auto-generated invoice (Phase 7G)
- Email sent to provider (Phase 7G)

### 2.4 Reject Flow

**Step 1:** Click "Reject" button → Modal opens.

**Modal shows:**
- Yellow reminder: "Rejection does NOT refund automatically. If funds were received, refund via bank manually."
- Reason textarea (min 10 chars, max 500)

**Step 2:** Enter clear reason.

**Good example:**
markdown
Receipt image is unclear. Please resubmit with a clearer photo.

text

**Bad example:**
bad

text
(Rejected — too short, no context)

**Step 3:** Click "Confirm Reject".

**System effect:**
- `payments.status = 'rejected'`
- `payments.admin_note = reason`
- `payments.verified_by = admin_id`
- `payments.verified_at = now()`
- `payments.metadata.refund_reminder = true`
- `payments.metadata.refund_reminded_at = ISO8601`
- `subscriptions.status = 'cancelled'`
- `subscriptions.end_date = now()`

Result: Provider loses premium features; subscription cancelled.

### 2.5 Refund Handling (CRITICAL)

**Reject does NOT auto-refund money.**

**Reality:**
- If provider actually sent funds → money is in TravelAI bank account.
- Admin must manually refund via bank.
- Software only bookkeeps (via metadata flag).

**Refund workflow:**

**Step 1:** After rejecting, go to Recent Activity section.

**Step 2:** Find the rejected payment row. You will see:
- Red X icon
- Provider name + amount + reason
- "Refund pending" label + "Mark Refunded" button

**Step 3:** Login to your bank portal (NIC Asia / etc.). Find the payment by reference number.

**Step 4:** Initiate manual refund transfer to provider's account.

**Step 5:** Back in admin panel → Click "Mark Refunded" button.

**System effect:**
- `payments.metadata.refund_marked_at = ISO8601`
- Row updates → "Refund marked as completed."

Audit trail complete.

### 2.6 When Refund is NOT Needed

| Case | Refund needed? |
|------|----------------|
| Fake receipt (provider never paid) | No |
| Duplicate payment (2 payments, 1 valid) | Yes (refund 1) |
| Amount mismatch (paid less than required) | Yes (refund partial) |
| Wrong account (paid wrong person) | Bank-level recovery |
| Subscription cancelled by provider request | Yes |

**Rule of thumb:** If money is in TravelAI bank, refund it.

### 2.7 Audit Trail

Every payment action logs:
- Who (`verified_by`)
- When (`verified_at`)
- Why (`admin_note` for reject)
- Refund flags (`metadata`)

**Access:**
```cmd
php artisan tinker --execute="App\Models\Payment::where('id', PAYMENT_ID)->first()->toJson(JSON_PRETTY_PRINT);"
3. Subscription Management
3.1 Access
URL: /admin/subscriptions

Shows: All subscriptions (provider, plan, status, dates, actions).

3.2 Subscription Statuses
Status	Meaning
pending	Payment submitted, awaiting admin verify
active	Verified + active, features unlocked
cancelled	Cancelled by provider or rejected by admin
expired	Past end_date (auto via scheduler)
3.3 Status Transitions
text
pending → active       (admin approves payment)
pending → cancelled    (admin rejects payment)
active → cancelled     (provider cancels or upgrades)
active → expired       (end_date passes — scheduler)
cancelled → active     (provider resumes + new payment)
3.4 Update Status Manually
Step 1: Click on subscription row → Open detail page.

Step 2: Click "Update Status" → Select new status → Save.

Caution: Manual status change bypasses payment verification. Use only for support cases.

3.5 Plan Details
Plan	Monthly	Yearly	Features
Free	Rs. 0	Rs. 0	3 services, 1 staff, 5 AI requests
Professional	Rs. 4,499	Rs. 44,999	20 services, 5 staff, 50 AI requests
Business	Rs. 11,999	Rs. 119,999	100 services, 20 staff, 500 AI requests
Enterprise	Contact	Contact	Unlimited
4. User + Provider Management
4.1 Users
URL: /admin/users

Actions: View, edit role, delete, disable.

Caution: Deleting a user cascades to their provider + services + bookings. Use with care.

4.2 Providers
URL: /admin/providers

Actions: View, verify, activate / deactivate.

Verification statuses:

pending — new registration, awaiting document review

verified — full access, trusted badge

rejected — denied

4.3 Provider Verification Workflow
Step 1: Open provider detail → View uploaded documents.

Step 2: Check business registration, license, etc.

Step 3: Click "Verify" or "Reject".

Step 4: If verified → provider can now accept bookings + create services.

5. Reports + Statistics
5.1 Available Reports
URL: /admin/reports

Report	Shows
Bookings	By status, by provider, by date
Payments	Verified vs pending vs rejected
Providers	Active vs inactive, plan distribution
5.2 Revenue Stat — Clarity
Revenue = verified payments only.

text
revenue = SUM(payments.amount)
  WHERE status = 'verified'
  AND verified_at IN this_month
Not included:

Pending payments

Rejected payments

Refunded payments (post-verify)

Common confusion: If you reject Rs. 4,499 but total shows Rs. 11,999, that's because the Rs. 11,999 is a DIFFERENT payment (already verified).

6. Troubleshooting
6.1 Common Issues
Issue: "Reject modal doesn't open"

Cause: @stack('scripts') missing in admin layout (Phase 7D bug — fixed in 5fa218d)

Check: findstr /n "@stack" resources\views\layouts\admin.blade.php

Fix: Already in place after 5fa218d

Issue: "Cannot verify payment"

Check payment status = pending_verification (only state can be verified)

If already verified / rejected → no action needed

Issue: "Email not sent after approval"

Check .env MAIL_* settings

Check storage/logs/laravel.log for errors

Test send:

cmd
php artisan tinker
Mail::raw('test', fn($m) => $m->to('test@example.com')->subject('Test'));
Issue: "Provider can't login after rejection"

Reject does not block login. Provider can still login.

Check if provider has verification_status = 'verified'.

Issue: "Revenue doesn't decrease after refund"

Revenue stat only counts verified. Reject doesn't affect it.

If a verified payment is refunded (via existing refund route), status becomes refunded and revenue decreases.

Current reject flow: payment was never verified, so no revenue impact.

6.2 Log Locations
Log	Location
Application	storage/logs/laravel.log
Failed jobs	failed_jobs DB table
Email	storage/logs/laravel.log (log driver)
View recent errors:

cmd
findstr /n "ERROR\|error" storage\logs\laravel.log | powershell -Command "$input | Select-Object -Last 10"
6.3 Rollback
If a mistake happens:

Restore DB from backup:

cmd
mysql -u root -p travelai_db < database\backups\backup_YYYY_MM_DD.sql
Backups = emergency only. Restore reverts ALL changes since backup.

6.4 Escalation
Issue	Action
Payment DB corruption	Contact developer
Email gateway failure	Check Gmail app password
Scheduler not running	Check cron (crontab -l)
Queue stuck	Restart supervisor: supervisorctl restart travelai-worker:*
7. Quick Reference Card
Task	Command / URL
Verify payments	/admin/payments/verify
Pending count	Sidebar "Verify Payments" badge
Revenue check	/admin/dashboard
Recent refunds	Recent Activity section
System log	storage/logs/laravel.log
Restart queue	supervisorctl restart travelai-worker:*
8. Emergency Contacts
Role	Contact
Developer	(fill when known)
Bank (NIC Asia)	(fill when known)
Hosting (Oracle Cloud)	(fill when known)
End of Admin Guide v1.0