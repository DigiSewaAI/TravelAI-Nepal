# Future Features — Roadmap & Planning

**Living document** — pending work + future vision + implementation stages.

**Last Updated:** 2026-11-08
**HEAD:** 603db71 (AI itinerary sanitization deployed)
**Previous:** 2026-10-03 (HEAD 0d08c9f, 122 tests)

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
- ✅ SOS system (user + provider dashboard)
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
- ✅ 285 tests passing

---

## 🚨 IMMEDIATE — Pending (Priority Order)

### P0 — SOS Siren Bug Fix 🔴
**Status:** Bug confirmed — dashboard खोल्दा siren बज्छ (false alarm)
**Priority:** Critical (presentation blocker)
**Time:** ~30-60 min
**Scope:** `sos-siren-global.js` + provider dashboard JS
**Expected:** Siren केवल active SOS alert भएमा मात्र बज्नुपर्ने

### P1 — Quick Wins 🟡
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

### 3.1 Rescue Services 🚁
**Priority:** 🔴 HIGH (safety-critical)
**Timeline:** ~2-3 weeks

**Features:**
- Provider type: `rescue-service`, category: `rescue`
- Helicopter evacuation, medical rescue, search & rescue
- SOS integration — active SOS → nearest rescue provider notification
- GPS location sharing, pricing transparency

**Providers:** Air Dynasty, Simrik Air, Manang Air, Heli Everest, Mountain Helicopters, Kailash Helicopter

**Technical:** Migrations (`rescue_providers`, `rescue_missions`), extend `SosAlert`, rescue dashboard, admin dispatch UI

---

### 3.2 Blogs / Content System 📝
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

### 3.3 Blockchain Permits ⛓️
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

### 3.4 Travel Insurance Integration 🛡️
**Priority:** 🟡 MEDIUM (trust builder)
**Timeline:** ~2-3 weeks
- Partner insurance providers, in-checkout upsell, claim assistance, coverage display

### 3.5 AI Enhancements 🤖
**Priority:** 🟡 MEDIUM
**Timeline:** ongoing
- Voice-based planning (Nepali + English)
- Image recognition (photo → destination suggestion)
- Real-time itinerary adaptation (weather, closures)
- AI chat assistant, auto-translation, fraud detection

### 3.6 Live Trek Tracking (GPS) 📡
**Priority:** 🟡 MEDIUM
**Timeline:** ~3-4 weeks
- Real-time GPS sharing (opt-in)
- Family/agency visibility, offline checkpoints
- SOS integration, route deviation alerts

### 3.7 Accessibility (a11y) ♿
**Priority:** 🟡 MEDIUM
**Timeline:** ~2 weeks
- WCAG 2.1 AA, screen reader, keyboard nav, contrast, alt text

### 3.8 Group Booking / Co-Traveler Matching 👥
**Priority:** 🟢 LOW
**Timeline:** ~3-4 weeks
- Travel buddies matching, group discounts, shared cost, group chat

### 3.9 Loyalty / Rewards Program 🎁
**Priority:** 🟢 LOW
**Timeline:** ~2-3 weeks
- Points per booking, referral rewards, tiered membership

### 3.10 Multi-Currency Wallet 💳
**Priority:** 🟢 LOW
**Timeline:** ~4-6 weeks
- Traveler wallet (NPR, USD, EUR), top-up, pay providers, refund automation

### 3.11 API for Third-Party Integrations 🔌
**Priority:** 🟢 LOW
**Timeline:** ~3-4 weeks
- Public REST API + webhooks, partner integrations, Zapier, OAuth

### 3.12 Advanced Provider Analytics 📊
**Priority:** 🟢 LOW
**Timeline:** ~2-3 weeks
- Revenue dashboards, booking trends, demographics, AI pricing, competitor benchmarks

### 3.13 Mobile App (Native) 📱
**Priority:** 🟢 LOW (PWA sufficient)
**Timeline:** ~8-12 weeks
- React Native/Flutter, offline mode, push notifications, native QR, background GPS
- **Recommendation:** Only after 1000+ active users

---

## 📅 Timeline

| Stage | Feature | Timeline | Priority |
|---|---|---|---|
| Immediate | SOS siren fix + quick wins | 1 day | 🔴 |
| Immediate | Placeholder provider + Kathmandu segments | 2-3 days | 🟡 |
| Stage 2 | Path 2 (6 pending features) | 4-6 weeks | 🟡 |
| Stage 3 | **Rescue Services** | 2-3 weeks | 🔴 HIGH |
| Stage 3 | **Blogs system** | 2-3 weeks | 🟡 HIGH |
| Stage 3 | **Blockchain permits** | 4-6 weeks | 🟡 MEDIUM |
| Stage 3 | Travel insurance, AI, GPS, a11y | 2-4 weeks each | 🟡 MEDIUM |
| Stage 3 | Others (loyalty, groups, API, wallet, mobile) | post-1000 users | 🟢 |

---

## 🛠️ Design Principles

### Database
- Additive migrations only, nullable future fields, generic table names, extensible enums

### Features
- Feature flags (`FEATURE_RESCUE`, `FEATURE_BLOG`, `FEATURE_BLOCKCHAIN`)
- Provider opt-in, admin approval, graceful degradation

### Rescue
- SLA-based dispatch (5 min), GPS accuracy, SMS fallback, multi-provider broadcast

### Blogs
- SEO-first (schema, canonical, OG), CDN images, scheduled publishing, multi-author

### Blockchain
- Central DB (Phase 1) → blockchain (Phase 2), QR-first UX, regulatory compliance first

---

## 🎫 Related Tickets

- SOS-SIREN-FIX-01
- DOUBLE-PAREN-FIX-01
- PLACEHOLDER-PROVIDER-01
- KATHMANDU-SEGMENTS-01
- PATH2-VARIANTS-01
- PATH2-WAREHOUSE-01
- PATH2-CONTRACTS-01
- PATH2-SPLIT-ORDERS-01
- PATH2-MULTIVENDOR-SPLIT-01
- RESCUE-SERVICES-01
- BLOG-SYSTEM-01
- BLOCKCHAIN-PERMITS-01
- MOBILE-APP-01
- AI-ENHANCEMENTS-01

---

## 📝 Changelog

- **2026-10-03** — Document created
- **2026-10-04** — Path 3B COMPLETE
- **2026-11-08** — Major update:
  - **Removed complete:** Path 3A/3B/**3C**, Forex, Ads, Claims, SOS, Emails, Booking numbers, Mobile UI, SEO, GDPR, Passport, Notifications, White-label, Multi-lingual, Image upload, Cover/Logo, 15 plan limits, AI itinerary sanitization, **Payment (manual flow), Bulk Orders (RFQ+MOQ), Wholesale Negotiation (chat)**
  - **Removed skipped:** P3 Bulk Manual Claim (unnecessary)
  - **Path 2 pending (6):** Product Variants, Multi-Warehouse, Wholesale Contracts, Split Orders, Multi-vendor Payment Splitting, PayPal Native (optional)
  - **Immediate:** SOS siren fix, quick wins, placeholder provider, Kathmandu segments
  - **Future:** Rescue Services, Blogs system, Blockchain permits, Travel insurance, AI enhancements, GPS tracking, Accessibility, Group booking, Loyalty, Wallet, API, Analytics, Mobile app

---

**End of Document** | Living document — update as project evolves