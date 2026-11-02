# VERTICAL-AI-FEATURES-01 — 7 Vertical AI Roadmap

**Status:** 📌 Post-push ticket (Decision B, Session 15)
**Priority:** TBD (Owner post-push decision)
**Estimated:** 3-4 days
**Dependency:** Existing shared AI quota (`AiLimitService`, `ai_usage`, `ai_reservations`)

---

## 🎯 Vision

TravelAI Nepal को AI = **vertical-specific business intelligence**, केवल chatbot/content generator नै।

सबै 7 verticals ले **एउटै underlying AI infrastructure** (shared quota + reservation service) प्रयोग गर्छ, तर प्रत्येकको **vertical-specific prompts + business context**।

---

## 📋 Roadmap (per vertical)

### 🏨 Hotel — AI Hotel Intelligence
| Feature | Description |
|---|---|
| AI Room Pricing Assistant | Season, occupancy, demand → price suggestion |
| AI Occupancy Forecast | Future occupancy prediction |
| AI Guest Insights | Guest booking history → preferences |
| AI Revenue Insights | Revenue trends + opportunities |

**Data:** `hotel_bookings`, `hotel_rooms`, `users` (guest history)
**Complexity:** 🟡 Medium

### 🍜 Restaurant — AI Restaurant Intelligence
| Feature | Description |
|---|---|
| AI Menu Recommendations | Popular item analysis |
| AI Demand Forecast | Food item demand trends |
| AI Food Cost Insights | Ingredient cost vs selling price |
| AI Menu Description Generator | Professional descriptions |

**Data:** `restaurant_orders`, `restaurant_order_items`, `menu_items`
**Complexity:** 🟡 Medium

### 🚐 Transport — AI Transport Intelligence
| Feature | Description |
|---|---|
| AI Route Optimization | Distance, time, road conditions → route suggestion |
| AI Vehicle Assignment | Trip → vehicle selection |
| AI Fuel Cost Estimation | Distance × consumption |
| AI Maintenance Insights | Vehicle history → servicing |

**Data:** `transport_bookings`, `transport_vehicles`
**Complexity:** 🟡 Medium

### 🧑‍✈️ Individual Guides — AI Guide Intelligence
| Feature | Description |
|---|---|
| AI Guide Matching | Trip requirement → guide matching |
| AI Skill Recommendations | Skills/experience improvement |
| AI Profile Generator | Professional profile |
| AI Availability Assistant | Schedule analysis |

**Data:** `individual_bookings`, guides (staff)
**Complexity:** 🟢 Small

### 💱 Money Changer — AI Financial Intelligence
| Feature | Description |
|---|---|
| AI Currency Trend Insights | Historical exchange rate trends |
| AI Cash Flow Forecast | Currency-wise requirements |
| AI Transaction Anomaly Detection | Unusual patterns |
| AI Profit Margin Insights | Currency-wise margin |

**⚠️ Compliance:** AI ले rate **guarantee** गर्नु हुँदैन। Actual rates = operator decision.
**Data:** `provider_exchange_rates`, `exchange_transactions`
**Complexity:** 🟡 Medium

### 🎨 Art Craft — AI Craft Intelligence
| Feature | Description |
|---|---|
| AI Artwork Pricing Assistant | Material, labor, craftsmanship → pricing |
| AI Product Description | Professional descriptions |
| AI Custom Order Matching | Customer requirements → artisan |
| AI Product Recommendations | Customer interest → artwork |

**Data:** `artworks`, `custom_orders`
**Complexity:** 🟢 Small

### 📦 Inventory — AI Inventory Intelligence
| Feature | Description |
|---|---|
| AI Stock Forecast | Future requirements |
| AI Reorder Assistant | When + how much |
| AI Sales Prediction | Historical demand |
| AI Dead Stock Detection | Slow-moving items |

**Data:** `products`, `inventory_movements`, `orders`, `order_items`
**Complexity:** 🟡 Medium (highest value)

---

## 🏗️ Shared AI Infrastructure (Existing)

| Component | Purpose |
|---|---|
| `AiLimitService` | Quota check per provider |
| `AiReservationService` | Crash-safe reserve/finalize/release |
| `ai_usage` table | Monthly count (provider_id, month, count) |
| `ai_reservations` table | In-flight reservation tracking |
| `LlmService` | Gemini/Llama3 API wrapper |

**⚠️ नयाँ quota tables बनाउनु हुँदैन** — existing shared quota leverage गर्ने।

---

## 🔍 Discovery Required (before implementation)

| # | Question |
|---|---|
| 1 | प्रत्येक vertical को existing database schema |
| 2 | कुन AI feature लाई कुन data चाहिन्छ |
| 3 | कुन features implementable (sufficient data?) |
| 4 | नयाँ DB fields चाहिन्छन्? |
| 5 | Groq API मार्फत implement possible? |
| 6 | Testing/validation strategy |
| 7 | Existing AI quota reuse strategy |

---

## 🚨 Data Truth Policy

- **No fake forecasts** — insufficient historical data → "Data insufficient" message
- **AI suggests, operator decides** — कोई automated price change नै
- **Compliance** — Money changer = strict (no rate guarantees)

---

## 📊 Plan Tier Mapping (proposal)

| Feature Class | Plan Tier |
|---|---|
| Simple AI (Guide Matching, Menu Recs) | Free+ (limited quota) |
| Standard AI (Hotel Pricing, Restaurant Forecast) | Pro+ |
| Advanced AI (Financial Insights, Revenue) | Business+ |
| Enterprise | Unlimited |

---

## ✅ Sequence (post-push)

1. **Discovery** (1 day) — schema + data availability
2. **Pilot**: 1-2 verticals (Individual + Inventory — simplest + highest value)
3. **Iterate**: remaining 5 verticals
4. **Pricing page**: Update AI note (currently says "Trekking only")

---

## 🔗 Related Tickets

- `AI-QUOTA-TRACKING` — ✅ already implemented
- `PRICING-VERTICAL-LIMITS` — ✅ Phase 2A-2C done
- `EXPLORE-VIEW-SPLIT` — independent