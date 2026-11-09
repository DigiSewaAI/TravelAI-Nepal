# Future Features — Roadmap & Planning

**Living document** — pending work + future vision + implementation stages.

**Last Updated:** 2026-11-09
**HEAD:** (SOS siren fix + security patch deployed)
**Previous:** 2026-11-08 (HEAD 934cab7, security fix)

---

## 🎯 Purpose

- अझै बाँकी काम track
- Future vision document (Rescue, Blogs, Blockchain, आदि)
- Future masters/assistants लाई onboarding resource
- Changelog मार्फत updates track

---

## ✅ COMPLETED (since 2026-10-04) — हटाइयो

### Path 3 (Marketplace) — COMPLETE
- Path 3A — Products (shop/rental/wholesale), CRUD, plan limits
- Path 3B — Cart + checkout + orders + RFQ + tracking + emails
- **Path 3C — Rental full lifecycle COMPLETE ✅**
  - Rental deposit, return flow, condition check, late fee, damage claim, inventory tracking
  - Evidence: `RentalDetail.php`, `OrderItem.php` (return_requested_at, return_confirmed_at, return_condition, deposit_refund_amount, damage_deposit_pct, late_fee_per_day), `calculateLateFee()`, `calculateDepositRefund()`, `InventoryMovement` model

### Path 2 (Full E-commerce) — Partial Complete
- ✅ SKU / Product Code (Product.php)
- ✅ Stock Level Alerts (InventoryAiService)
- ✅ Refund / Returns (17 files)
- ✅ **Bulk Orders COMPLETE** — RFQ + MOQ (`min_order_qty`) + threaded chat (`WholesaleRfqMessage`)
- ✅ **Wholesale Negotiation COMPLETE** — chat-based (RFQ message thread)

### Payment — COMPLETE (Manual flow, intentional)
- ✅ Manual bank/eSewa/Khalti + admin verification (Phase 7C)
- ✅ Stripe decommissioned intentionally (Phase 7A stub retained)

### Forex System — COMPLETE
- ✅ Phase 1 — Exchange rate widget (`405b937`)
- ✅ Phase 2+3 — Live rate + snapshots + scheduler (`35f2d73`)

### Provider Ads System — COMPLETE
- ✅ Phase 1 MVP — Provider-paid ads (`b1452dc`)
- ✅ Left-column + CTR cap (`c183a39`)
- ✅ Carousel + deduplication (`d7eb564`)

### Core Features — COMPLETE
- ✅ 6 Claim systems (Restaurant, Transport, Hotel, ArtCraft, MoneyChanger, Individual) — Phase 4
- ✅ Admin Manual Claim tool
- ✅ Email system (Brevo, OTP, booking/order emails)
- ✅ Booking numbers (IND/HTL/ART/TXN)
- ✅ SOS system (user + provider dashboard) — Phase 1 MVP
- ✅ Mobile UI fixes (traveler + 7 provider + admin)
- ✅ SEO (noindex, sitemap, JSON-LD, breadcrumbs)
- ✅ GDPR cookie consent + GA4
- ✅ Passport / achievements
- ✅ Notifications (provider + admin bell)
- ✅ White-label invoices
- ✅ Multi-lingual (EN/NP/HI/ZH/RU)
- ✅ Image upload (all 13 categories) + cover/logo
- ✅ 15 plan limits enforced
- ✅ AI itinerary sanitization (fake accommodation names)
- ✅ **Security patch — 12 vulnerabilities → 0 (composer audit clean)**
- ✅ **SOS siren bug fix — first load auto-acknowledges existing alerts, only new SOS rings**
- ✅ CRUD completeness verified — 8 categories, 229 provider routes
- ✅ 285 tests passing (कोई regression नै)

---

## 🚨 IMMEDIATE — Pending (Priority Order)

### P0 — SOS Emergency Response Upgrade 🔴
**Status:** MVP exists (single "Mark as Resolved" button); proper emergency system पending
**Priority:** Critical (safety feature)
**Time:** ~1-2 weeks
**Scope:** See Section 3.1a — Emergency Response System Upgrade

**Current State (MVP):**
- 2 states: `pending`, `resolved`
- 1 button: "Mark as Resolved"
- No confirmation, no auto-escalation, no audit trail
- Risk: accidental resolve, no backup dispatch, no false-alarm separation

### P1 — Quick Wins 🟡
- **SOS resolve confirmation dialog** — accidental click रोक्न (~10 min, safe) ⭐ NEW
- Double parentheses fix: "Accommodation in X (Est.) (Estimated)" → "Accommodation in X (Estimated)" (~10 min)
- `.gitignore` update (`*.bak_*`, `ngrok.exe`, `php`, scratch HTML) (~5 min)
- Claim date display in card (~15 min)
- MC profit calculation — 2% hardcoded → actual spread (~30 min)

### P2 — Placeholder Provider Names 🟡
**Status:** Paragliding output मा "Remote Treks Provider System" देखियो
**Time:** ~1-2 hr
**Scope:** Discovery चाहिन्छ — real provider हो कि system placeholder

### P3 — Kathmandu Route Segments 🟢
**Status:** अहिले 1 segment मात्र — 2-day tour मा 1 rest day pad हुन्छ
**Time:** ~1-2 hr (production DB INSERT — backup चाहिन्छ)
**Priority:** Low (acceptable अहिलेको लागि)

---

## 🔮 STAGE 2 — Path 2 Pending (6 features)

**Status:** 🟡 Partial — core exists, 6 advanced features pending
**Timeline:** ~4-6 weeks

### Product Variants
- Size / color / model options
- Variant-specific pricing
- Variant-specific stock
- Variant images

### Multi-Warehouse Support
- Multiple warehouse locations per provider
- Stock per warehouse
- Warehouse-wise order routing
- Transfer between warehouses

### Wholesale Contracts
- Signed contracts (PDF)
- Terms & conditions
- Digital signature
- Contract lifecycle (draft → signed → active)

### Split Orders
- Partial delivery
- Multi-shipment tracking
- Split invoices
- Per-shipment payment

### Multi-vendor Payment Splitting
- Commission split between providers
- Automatic revenue distribution
- Platform fee deduction

### PayPal Native Integration (optional)
- Current: manual receipt upload works
- Future: native PayPal API (only if user demand)

---

## 🌟 STAGE 3 — New Features (Priority Order)

### 3.1 SOS System — Emergency Response Upgrade 🚨
**Priority:** 🔴 HIGH (safety-critical)
**Timeline:** ~1-2 weeks

#### 3.1a Emergency Response System Upgrade (MVP → Full)

**Current MVP limitations:**
- 2 states only (`pending`, `resolved`)
- Single "Mark as Resolved" button — no confirmation
- No "acknowledged" state (provider देख्यो भन्ने track छैन)
- No "responding" state (rescue पठाइयो भन्ने track छैन)
- No false-alarm separation
- No auto-escalation (provider offline भने SOS सधैं active)
- No multi-provider sync (एकै booking मा multiple providers)
- No admin oversight of active SOS
- No audit trail (कसले के गर्यो)
- No traveler medical/emergency info panel
- No GPS map / nearest rescue integration

**5-State Machine:**
| State | Meaning | Siren | Bar |
|---|---|---|---|
| `pending` | New SOS — कोई response नै | 🔴 Ring | 🔴 Red |
| `acknowledged` | Provider ले देख्यो, respond गर्दैछ | ⚪ Stop | 🟡 Yellow |
| `responding` | Rescue/help dispatch भयो | ⚪ Stop | 🟡 Yellow |
| `resolved` | Help पुग्यो, handled | ⚪ Stop | ⚪ Hide |
| `false_alarm` | Traveler ले गल्तीले थिच्यो | ⚪ Stop | ⚪ Hide |

**6 Provider Actions:**
| Button | Purpose | Available State |
|---|---|---|
| 👁️ **Acknowledge** | "देखें, respond गर्दैछु" — siren बन्द | `pending` |
| 📞 **Contact Traveler** | Call / in-app chat / SMS | `acknowledged` |
| 🚁 **Dispatch Rescue** | Helicopter / ambulance / local rescue लाई notify | `acknowledged` |
| 📝 **Add Notes** | के action लियो — audit trail | Any state |
| ✅ **Mark Resolved** | Final resolution (notes सहित) | `responding` |
| ⚠️ **Mark False Alarm** | गल्तीले थियो भन्ने record | `pending` / `acknowledged` |

**Safety Features (New):**
- **Confirmation dialog** on resolve + false alarm (accidental click रोक्न)
- **Auto-escalation:** 5 min response नभए → admin notification; 10 min → rescue services notify
- **Traveler info panel:** Medical info, emergency contact, blood group, allergies, insurance
- **GPS map:** Live location + nearest rescue providers
- **Time tracking:** "SOS sent 3 min ago" — urgency badge
- **Multi-provider sync:** सबै involved providers ले same state देखून्
- **Admin oversight:** Admin dashboard मा सबै active SOS list + map + dispatch
- **Audit log:** को-कसले के गर्यो, कहिले (legal + training + insurance claims)
- **SMS fallback:** अगर provider ले app नै खोलेको छैन → SMS alert
- **Push notifications:** Mobile PWA push (post-MVP)

**Technical:**
- Migration: `sos_alerts` मा columns — `acknowledged_at`, `acknowledged_by`, `responding_at`, `responding_by`, `resolved_by`, `false_alarm_reason`, `auto_escalated_at`
- New table: `sos_alert_actions` (audit log — user_id, action, notes, timestamp)
- `SosAlert` model: state machine (Laravel state package वा custom)
- Notification: admin + rescue services channel
- Scheduled job: auto-escalation (`AutoEscalateSos` command, every 1 min)
- UI: provider dashboard — action buttons per state
- UI: admin — active SOS oversight dashboard
- UI: traveler — status visibility ("Provider acknowledged 2 min ago")

**Testing:**
- State transition tests (5 states × valid/invalid transitions)
- Auto-escalation timing tests
- Multi-provider sync tests
- Audit log integrity tests
- Confirmation dialog (JS)
- E2E: SOS → acknowledge → dispatch → resolve

#### 3.1b Rescue Services Integration (see Section 3.2)

**Existing MVP (Phase 1):**
- ✅ Siren bug fixed (first load auto-ack)
- ✅ Provider-specific alerts (server query correct)
- ✅ Single "Mark as Resolved" button
- ✅ Siren loop until ack (5s interval)
- ✅ localStorage-based ack

---

### 3.2 Rescue Services Category 🚁
**Priority:** 🔴 HIGH (safety-critical)
**Timeline:** ~2-3 weeks
**Depends on:** 3.1a (Emergency Response Upgrade) — better to do together

**Features:**
- Provider type: `rescue-service`, category: `rescue`
- Helicopter evacuation, medical rescue, search & rescue
- **SOS integration — active SOS → auto-dispatch nearest rescue provider** ⭐
- GPS location sharing with rescue team
- **Real-time tracking** — traveler + rescue + provider + admin सबैले live location हेर्न ⭐
- **Insurance claim integration** — auto-claim on rescue dispatch ⭐
- Pricing transparency (heli rescue cost estimate)
- Mission lifecycle (dispatched → en route → arrived → extracted → completed)
- Response time tracking (SLA — 5 min dispatch target)

**Providers to onboard:**
Air Dynasty, Simrik Air, Manang Air, Heli Everest, Mountain Helicopters, Kailash Helicopter

**Technical:**
- New migrations: `rescue_providers`, `rescue_missions`, `rescue_mission_updates`
- Extend `SosAlert` — `rescue_mission_id`, `dispatch_status`
- Extend `SosAlertController` — dispatch action
- New: `RescueDispatchService` — nearest provider matching (GPS distance)
- New: `RescueMissionController` (provider + admin)
- Notification: rescue provider SMS + push + dashboard
- GPS: real-time location via WebSocket (Pusher/Ably) वा polling
- Insurance integration: API hooks (post-launch partner)
- Admin dispatch UI
- Audit + SLA metrics

**SOS → Rescue Flow:**
Traveler SOS → Provider acknowledged (5 min SLA)
↓ (no response in 5 min)
Auto-escalate → Admin dashboard alert
↓ (no response in 10 min)
Auto-notify → Nearest rescue providers (GPS radius)
↓
Rescue accepts → Real-time tracking starts
↓
Insurance auto-claim → Traveler's policy notified
↓
Mission complete → SOS resolved + audit log


---

### 3.3 Blogs / Content System 📝
**Priority:** 🟡 HIGH (SEO growth)
**Timeline:** ~2-3 weeks

**Features:**
- Blog CRUD (admin + provider authors)
- Categories, tags, rich text editor
- SEO fields (meta, slug, OG image)
- Author profiles, related posts, RSS, schema.org Article
- Multi-language (EN/NP), scheduled publishing

**Content types:** Trek guides, destination guides, travel tips, provider spotlights, seasonal content

**Technical:** `posts`, `post_categories`, `post_tags`, `post_comments` tables, `BlogController`, routes `/blog`, admin UI, sitemap integration

**Revenue:** Google AdSense, sponsored posts, affiliate links

---

### 3.4 Blockchain Permits ⛓️
**Priority:** 🟡 MEDIUM (differentiator)
**Timeline:** ~4-6 weeks

**Features:**
- TIMS + Conservation permits on-chain
- Immutable issuance, QR verification, real-time validation
- Permit NFT (digital badge), QR passport integration

**Stack:** Polygon (low gas) recommended, Solidity ERC-721, Web3.js/Ethers.js, IPFS metadata

**Risks:** Regulatory (Nepal crypto policy), user adoption, gas fees

**Recommendation:** Phase 1 QR-verified central DB → Phase 2 blockchain (after regulatory clarity)

---

### 3.5 Travel Insurance Integration 🛡️
**Priority:** 🟡 MEDIUM (trust builder)
**Timeline:** ~2-3 weeks
**Depends on:** 3.2 (Rescue Services) for auto-claim flow
- Partner insurance providers, in-checkout upsell, claim assistance, coverage display
- Auto-claim on SOS rescue dispatch

### 3.6 AI Enhancements 🤖
**Priority:** 🟡 MEDIUM
**Timeline:** ongoing
- Voice-based planning (Nepali + English)
- Image recognition (photo → destination suggestion)
- Real-time itinerary adaptation (weather, closures)
- AI chat assistant, auto-translation, fraud detection

### 3.7 Live Trek Tracking (GPS) 📡
**Priority:** 🟡 MEDIUM
**Timeline:** ~3-4 weeks
**Depends on:** 3.2 (Rescue Services) for rescue integration
- Real-time GPS sharing (opt-in)
- Family/agency visibility, offline checkpoints
- SOS integration, route deviation alerts
- Rescue dispatch coordination

### 3.8 Accessibility (a11y) ♿
**Priority:** 🟡 MEDIUM
**Timeline:** ~2 weeks
- WCAG 2.1 AA, screen reader, keyboard nav, contrast, alt text

### 3.9 Group Booking / Co-Traveler Matching 👥
**Priority:** 🟢 LOW
**Timeline:** ~3-4 weeks
- Travel buddies matching, group discounts, shared cost, group chat

### 3.10 Loyalty / Rewards Program 🎁
**Priority:** 🟢 LOW
**Timeline:** ~2-3 weeks
- Points per booking, referral rewards, tiered membership

### 3.11 Multi-Currency Wallet 💳
**Priority:** 🟢 LOW
**Timeline:** ~4-6 weeks
- Traveler wallet (NPR, USD, EUR), top-up, pay providers, refund automation

### 3.12 API for Third-Party Integrations 🔌
**Priority:** 🟢 LOW
**Timeline:** ~3-4 weeks
- Public REST API + webhooks, partner integrations, Zapier, OAuth

### 3.13 Advanced Provider Analytics 📊
**Priority:** 🟢 LOW
**Timeline:** ~2-3 weeks
- Revenue dashboards, booking trends, demographics, AI pricing, competitor benchmarks

### 3.14 Mobile App (Native) 📱
**Priority:** 🟢 LOW (PWA sufficient)
**Timeline:** ~8-12 weeks
- React Native/Flutter, offline mode, push notifications, native QR, background GPS
- **Recommendation:** Only after 1000+ active users

---

## 📅 Timeline

| Stage | Feature | Timeline | Priority |
|---|---|---|---|
| Immediate | Quick wins (SOS confirm dialog + 4 items) | 1 day | 🟡 |
| Immediate | Placeholder provider + Kathmandu segments | 2-3 days | 🟡 |
| Stage 2 | Path 2 (6 pending features) | 4-6 weeks | 🟡 |
| Stage 3 | **SOS Emergency Response Upgrade** | 1-2 weeks | 🔴 HIGH |
| Stage 3 | **Rescue Services + SOS integration** | 2-3 weeks | 🔴 HIGH |
| Stage 3 | **Blogs system** | 2-3 weeks | 🟡 HIGH |
| Stage 3 | Travel Insurance (depends on Rescue) | 2-3 weeks | 🟡 MEDIUM |
| Stage 3 | **Blockchain permits** | 4-6 weeks | 🟡 MEDIUM |
| Stage 3 | AI, GPS, a11y | 2-4 weeks each | 🟡 MEDIUM |
| Stage 3 | Others (loyalty, groups, API, wallet, mobile) | post-1000 users | 🟢 |

---

## 🛠️ Design Principles

### Database
- Additive migrations only, nullable future fields, generic table names, extensible enums

### Features
- Feature flags (`FEATURE_SOS_UPGRADE`, `FEATURE_RESCUE`, `FEATURE_BLOG`, `FEATURE_BLOCKCHAIN`)
- Provider opt-in, admin approval, graceful degradation

### SOS Emergency Response
- State machine (5 states, valid transitions only)
- Confirmation dialogs on destructive actions
- Auto-escalation (5 min admin, 10 min rescue)
- Audit trail (immutable)
- Multi-provider sync
- SMS fallback (provider offline)
- Admin oversight (active SOS dashboard)

### Rescue Services
- SLA-based dispatch (5 min), GPS accuracy, SMS fallback, multi-provider broadcast
- Real-time tracking (WebSocket preferred)
- Auto-insurance claim
- Mission lifecycle tracking
- Response time SLA metrics

### Blogs
- SEO-first (schema, canonical, OG), CDN images, scheduled publishing, multi-author

### Blockchain
- Central DB (Phase 1) → blockchain (Phase 2), QR-first UX, regulatory compliance first

---

## 🎫 Related Tickets

- SOS-CONFIRM-DIALOG-01
- DOUBLE-PAREN-FIX-01
- PLACEHOLDER-PROVIDER-01
- KATHMANDU-SEGMENTS-01
- PATH2-VARIANTS-01
- PATH2-WAREHOUSE-01
- PATH2-CONTRACTS-01
- PATH2-SPLIT-ORDERS-01
- PATH2-MULTIVENDOR-SPLIT-01
- **SOS-RESPONSE-UPGRADE-01** (5 states + 6 actions + auto-escalation)
- **SOS-ADMIN-OVERSIGHT-01** (admin dashboard + dispatch)
- **SOS-AUDIT-LOG-01** (immutable action log)
- **RESCUE-SERVICES-01** (provider type + category)
- **RESCUE-DISPATCH-01** (GPS + nearest provider matching)
- **RESCUE-TRACKING-01** (real-time WebSocket)
- **RESCUE-INSURANCE-01** (auto-claim integration)
- BLOG-SYSTEM-01
- BLOCKCHAIN-PERMITS-01
- MOBILE-APP-01
- AI-ENHANCEMENTS-01

---

## 📝 Changelog

- **2026-10-03** — Document created
- **2026-10-04** — Path 3B COMPLETE
- **2026-11-08** — Major update:
  - Removed complete: Path 3A/3B/**3C**, Forex, Ads, Claims, SOS (MVP), Emails, Booking numbers, Mobile UI, SEO, GDPR, Passport, Notifications, White-label, Multi-lingual, Image upload, Cover/Logo, 15 plan limits, AI itinerary sanitization, Payment (manual flow), Bulk Orders (RFQ+MOQ), Wholesale Negotiation (chat)
  - Removed skipped: P3 Bulk Manual Claim
  - Path 2 pending (6): Product Variants, Multi-Warehouse, Wholesale Contracts, Split Orders, Multi-vendor Payment Splitting, PayPal Native (optional)
  - Immediate: SOS siren fix, quick wins, placeholder provider, Kathmandu segments
  - Future: Rescue Services, Blogs system, Blockchain permits, Travel insurance, AI enhancements, GPS tracking, Accessibility, Group booking, Loyalty, Wallet, API, Analytics, Mobile app
- **2026-11-09** — Update:
  - ✅ **SOS siren bug fix deployed** — first load auto-acks existing alerts, only new SOS rings
  - ✅ **Security patch deployed** — 12 vulnerabilities → 0 (composer audit clean)
  - ✅ **CRUD completeness verified** — 8 categories, 229 provider routes, all views exist
  - 🔴 **P0 new: SOS Emergency Response Upgrade** — 5-state machine, 6 actions, auto-escalation, admin oversight, audit log, traveler info panel, GPS map, SMS fallback
  - 🔴 **Rescue Services integration** — SOS → auto-dispatch, real-time tracking, insurance auto-claim
  - 🟡 **New quick win: SOS resolve confirmation dialog** (10 min, accidental click रोक्न)
  - Refined Rescue Services section with full SOS integration flow
  - Refined Travel Insurance — depends on Rescue Services for auto-claim

---

**End of Document** | Living document — update as project evolves
