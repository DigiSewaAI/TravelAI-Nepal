# Future Features — Roadmap & Planning

**Living document** — tracks current work + future vision + implementation stages.

**Last Updated:** 2026-10-03
**HEAD:** 0d08c9f

---

## 🎯 Purpose

यो document ले:
- Current work track गर्छ (Path 3)
- Future vision document गर्छ (Path 2, Forex, Ads)
- Implementation stages + timeline देखाउँछ
- Future masters/assistants लाई onboarding resource हो
- Changelog मार्फत updates track गर्छ

---

## 📊 Current State (as of 2026-10-03)

- **HEAD:** 0ca5c88 (Path 3A complete, pushed)
- **Categories:** 13 (10 original + shop, rental, wholesale)
- **Provider types:** 15 (12 original + shop-owner, rental-provider, wholesale-provider)
- **Seeder strategy:** Hybrid (production-safe)
- **Production users:** 4 (Parashar admin + Anju + Pareen + John)
- **Production providers:** 7 (2 migrated + 5 demo)
- **Tests:** 56/56 pass

### Path 3A — COMPLETE ✅

**Deliverables:**
- products table (polymorphic: shop/rental/wholesale)
- shop_details, rental_details, wholesale_details
- ProductController (6 methods, CRUD + limit enforcement)
- 6 routes (provider.products.*)
- 6 views (index/create/edit + 3 partials)
- i18n: 37 keys × 4 locales (en/np/hi/zh)
- 12 tests (56/56 total)
- Plan limits: max_products (5/30/200/-1)

**Commits:**
- fdcf119 — D1 migrations
- 3953329 — D2 models
- 70415eb — D4 controller + routes
- 280f3f9 — D5 views
- 2500ff3 — D6 i18n
- 0ca5c88 — D9 tests

---

## 🚀 Stage 1 — Path 3 (~4 weeks)

**Status:** 3A COMPLETE (2026-10-03) | 3B + 3C pending

### 3A — Basic Marketplace (Week 1-2)

**Shop category:**
- Handicraft, murti, souvenir
- Listing + basic inquiry

**Rental category:**
- Jackets, sleeping bags, gear
- Simple date booking (no deposit)

**Result:** Deployable state (real shops can join)

### 3B — E-commerce + Wholesale (Week 3-4)

- Cart + checkout
- Order tracking
- Wholesale B2B (RFQ system, bulk pricing)
- Payment integration (existing methods)

**Result:** Complete marketplace

### 3C — Post-Deploy Enhancement

- Rental deposit + return flow
- Late fee, damage claims
- Inventory tracking

---

## 🔮 Stage 2 — Path 2 (Future, 5-9 weeks post-deploy)

- Full e-commerce (SKU, variants, inventory)
- Rental full workflow (deposit, return, late fee, damage)
- Wholesale negotiation + contracts
- Advanced order management
- Multi-warehouse support

---

## 🌟 Stage 3 — Additional Features (Priority Order)

### 3.1 Forex System (Traveler Dashboard)

**Priority:** HIGH (after Path 3, before Ads)

**Features:**
- Live exchange rates display (USD → NPR, EUR → NPR, GBP → NPR, etc.)
- Daily auto-update (cron job, once per day 6 AM Nepal time)
- Source: Free API (exchangerate.host कि similar free tier)
- Cache: 6-12 hours
- Display: Dashboard widget (like weather widget)
- Fallback: Hide if API fails
- Multi-currency: Support 5-10 popular currencies

**Technical:**
- New service: `ForexService.php`
- New command: `UpdateExchangeRates` (scheduled)
- New table: `exchange_rates` (currency, rate, fetched_at)
- Cache: Redis/file
- i18n: 4 locales

### 3.2 Provider Ads System (Traveler Dashboard)

**Priority:** LOW (LAST — after all core features)

**Features:**
- Providers can create ad campaigns
- Ads shown in traveler dashboard (banner/sidebar)
- Targeting (category, location, budget)
- Ad spend tracking
- Payment: existing methods
- Admin moderation
- Analytics (impressions, clicks)

**Technical:**
- New tables: `ads`, `ad_campaigns`, `ad_impressions`, `ad_clicks`
- New controller: `AdController`
- Admin UI: manage ads
- Provider UI: create/manage campaigns
- Traveler UI: ad slots in dashboard
- Rate limiting: max ads per provider

**⚠️ Note:** Ads = revenue model, but complex. Do ONLY after all core features + real users.

---

## 📅 Implementation Timeline (Tentative)

| Stage | Feature | Timeline |
|-------|---------|----------|
| 1 | Path 3 (Shop + Rental + Wholesale) | ~4 weeks |
| 2 | DEPLOY (Laravel Cloud) | After 1 |
| 3 | Path 2 (full e-commerce) | ~5-9 weeks |
| 4 | Forex system | ~3-5 days |
| 5 | Provider ads system | ~2-3 weeks |

---

## 🛠️ Design Principles (Future-Proofing)

### For Path 3 → Path 2 Extension

- Additive migrations only (R8)
- Nullable columns for future fields (e.g., `deposit_amount` now, enable later)
- Table names generic (e.g., `shop_items` not `handicraft_items`)
- Status enums extensible (basic → advanced)
- No data rebuild needed for upgrade

### For Forex

- Service-based (swap API provider easily)
- Cached (avoid rate limit)
- Fallback (never block dashboard)

### For Ads

- Feature flag (`FEATURE_ADS=true/false`)
- Provider opt-in only
- Admin approval required

---

## 🎫 Related Tickets

- RETAIL-SHOP-CATEGORY-01
- RENTAL-SYSTEM-01
- WHOLESALE-B2B-01
- FOREX-SYSTEM-01
- PROVIDER-ADS-01
- (More as project evolves)

---

## 📝 Changelog

- 2026-10-03: Document created (Path 3 decision + future roadmap)
- 2026-10-03: Path 3A COMPLETE (migrations, models, controller, views, i18n, tests)

---

**End of Document** | Living document — update as project evolves