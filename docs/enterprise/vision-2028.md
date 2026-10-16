# Enterprise Plan — Vision 2028

**Status:** Aspirational roadmap (not current commitment)
**Date:** 2026-10-16 (updated)
**Purpose:** Internal reference for enterprise offering + future roadmap

---

## ⚠️ Important Note

यो document = **vision document** (2026-2028 roadmap)।

**कहिले use गर्ने:**
- Internal reference (sales quote बनाउँदा)
- Future roadmap planning
- New assistant onboarding

**कहिले NOT use:**
- ❌ Website मा publish
- ❌ Client लाई "we have X" promise
- ❌ SLA/feature guarantees without infrastructure

---

## 🎯 Phase 1 (2026 Q4) — Foundation

**Tiers:**
- Tier 1: Rs. 20,000 – Rs. 30,000/mo (Small Enterprise)
- Tier 2: Rs. 35,000 – Rs. 55,000/mo (Large Enterprise)
- Custom: Rs. 55,000+ (National/International)

**Setup Fee:** Rs. 25,000 (never waive)
**Support:** Founder-direct (first 5 clients)
**Target:** 3-5 clients
**ARR Target:** Rs. 12-24 Lakh

**Focus:** Case studies + testimonials

---

## 🎯 Phase 2 (2027) — Structured

- Add Tier 3 (Rs. 60k-1L)
- Hire 1 support person
- Full legal docs (MSA, SOW, DPA)
- Add-ons expansion
- Partner program launch

**Target:** 15 clients, ARR Rs. 60 Lakh – 1 Cr

---

## 🎯 Phase 3 (2028+) — Scale

- Full playbook (3 tiers, SLA, dedicated team)
- Support team (2-3 people)
- International expansion
- ISO 27001 certification
- SOC 2 Type II

**Target:** 25-50 clients, ARR Rs. 3-5 Cr

---

## 🔒 Currently Built (as of 2026-10-16)

✅ Multi-staff, listings, bookings
✅ White-label (Phase 3B)
✅ Watermark (Phase 2)
✅ Email system (Brevo + Zoho)
✅ GA4 tracking (Phase 4)
✅ Invoice download/email
✅ 99% uptime (Laravel Cloud)
✅ Contact + Enterprise inquiry forms
✅ Register provider flow + NPR currency
✅ PHPUnit 12 fix
✅ Font Awesome 6.7.2

---

## 🚀 Future Features Roadmap

### 📅 2026 Q4 (Oct-Dec) — Priority HIGH

**Revenue + Foundation:**
```
1. Real Provider Onboarding       (3-5 days)
   → Self-service signup + verification
   → KYC document upload
   → Admin approval flow

2. Payment Gateway (eSewa/Khalti) (5-7 days)
   → Auto subscription billing
   → Refund flow
   → Invoice generation auto

3. Customer Support System         (1 week)
   → Ticket system
   → Email-based support
   → Admin dashboard

4. Cookie Consent GDPR             (2-3 hrs)
   → Banner + localStorage
   → GA4 consent gating

5. Region Migration (US → Singapore) (4-8 hrs)
   → Latency improvement for Nepal
   → Laravel Cloud region switch
```

### 📅 2027 — Priority MEDIUM

**Scale + Security:**
```
1. SSO (Google/Microsoft)          (3-4 days)
   → OAuth integration
   → Enterprise domain login

2. User-level 2FA (TOTP)           (1-2 days)
   → Google Authenticator
   → Backup codes

3. Audit Logs                       (2-3 days)
   → Activity trail per user
   → Compliance evidence

4. Multi-currency Billing          (2-3 days)
   → NPR/USD/INR support
   → Auto conversion

5. Advanced Analytics Dashboard    (1 week)
   → Custom reports
   → Export CSV/Excel

6. Mobile App (PWA v2)             (2-3 weeks)
   → Offline-first
   → Push notifications

7. Custom Domain (Provider)        (3-5 days)
   → Provider-owned domain
   → DNS verification
   → SSL automation

8. API Access                       (1 week)
   → REST API for enterprise
   → API key management
   → Rate limiting

9. Webhook Integrations            (3-4 days)
   → Booking events
   → Payment events
   → Provider sync

10. ERP/CRM Basic (Zapier)         (1 week)
    → Zapier connector
    → Common workflow templates
```

### 📅 2028 — Priority ASPIRATIONAL

**Enterprise + Compliance:**
```
1. Full ERP Integration             (4-6 weeks)
   → SAP, Oracle connectors
   → Custom data mapping

2. Full CRM Integration             (4-6 weeks)
   → Salesforce, HubSpot
   → Bidirectional sync

3. 99.9% SLA Infrastructure        (8-12 weeks)
   → Multi-region failover
   → Auto-scaling
   → Uptime monitoring

4. Dedicated Server Option          (3-4 weeks)
   → Isolated infrastructure
   → Client-specific deployment

5. 24/7 Support Team               (hire 3-5)
   → Follow-the-sun support
   → Dedicated channels

6. Dedicated Account Manager        (hire 2-3)
   → Per-client relationship
   → Quarterly reviews

7. Custom Feature Development       (ongoing)
   → Client-funded features
   → Roadmap influence

8. White-label Complete             (4-6 weeks)
   → Full domain ownership
   → Complete branding separation

9. SSO/SAML Enterprise Grade        (4-6 weeks)
   → SAML 2.0
   → SCIM provisioning

10. Compliance Certifications      (3-6 months)
    → SOC 2 Type II
    → ISO 27001
    → GDPR audit
```

---

## 🎯 Priority Order (Executive Summary)

**2026 Q4 — MUST HAVE:**
```
1. Real provider onboarding (scale)
2. Payment gateway (revenue)
3. Support system (retention)
4. Cookie consent (legal)
5. Region migration (UX)
```

**2027 — SHOULD HAVE:**
```
1. SSO + 2FA (security)
2. Audit logs (compliance)
3. Analytics dashboard (value)
4. Mobile app (reach)
5. Custom domain (brand)
```

**2028 — NICE TO HAVE:**
```
1. Enterprise SLA (contracts)
2. Full integrations (enterprise)
3. Compliance certs (trust)
```

---

## 🎯 Dependencies (Examples)

```
SSO needs:
  → User auth refactor
  → Provider configuration
  → Domain verification setup

Payment Gateway needs:
  → eSewa/Khalti merchant account
  → Legal compliance (T&C)
  → Refund flow + escalation

Support System needs:
  → Email infrastructure (have)
  → Admin dashboard
  → SLA tracking

Region Migration needs:
  → Laravel Cloud region switch
  → Data migration (backup + restore)
  → DNS update (Cloudflare)
  → Zero-downtime cutover plan
```

---

## 🎯 Owner Decision Points

**Q1: Enterprise client timeline?**
```
2026 Q4:  1-3 clients (founder sale)
2027 Q1:  5-10 clients (after case studies)
2027 Q4:  20+ clients (after support hire)
```

**Q2: Which feature priority?**
```
A) Revenue first      → Payment gateway, onboarding
B) Retention first    → Support system, analytics
C) Scale first        → SSO, audit logs
मेरो सुझाव: A (revenue), then B (retention)
```

**Q3: Market focus?**
```
A) Nepal only         → NPR focus, single language + English
B) South Asia         → Multi-currency, multi-lang
C) Global             → Full compliance, international
मेरो सुझाव: A first, B by 2027
```

---

## 📋 Sales Process (Simple)

1. Client inquiry (form) → email to info@
2. Discovery call (30 min)
3. Custom quote (per client)
4. Negotiate (1-2 rounds)
5. Contract + payment (eSewa/Khalti/bank)
6. Manual activation (Admin panel)
7. Onboarding (2-4 weeks)

---

## 🎯 Pricing Matrix (Reference)

| Attribute | Starter | Growth | Scale |
|-----------|---------|--------|-------|
| Branches | 1-2 | 3-5 | 6-10 |
| Staff | 5-10 | 15-25 | 30-50 |
| Listings | 20-50 | 100-200 | 300-500 |
| Bookings/mo | 50-200 | 500-1000 | 2000-5000 |
| AI Requests | 100-500 | 1000-3000 | 5000-10000 |

**Discount policy:**
- Quarterly: -5%
- Annual: -15%
- 3-year: -25%
- Founder sale (first 5): -20% + 50% setup off

**Floor price:** Rs. 20,000/mo (never go below)

---

## 🎯 Open Decisions (Owner)

- [ ] Public pricing vs Contact-only
- [ ] Exact tier limits (staff/listings/bookings)
- [ ] Add-on pricing finalization
- [ ] Legal docs (lawyer review)
- [ ] 2026 Q4 priority confirm (revenue first?)

**= पहिलो 3-5 clients आए → real data बाट refine।**

---

*End of vision document — Internal use only*