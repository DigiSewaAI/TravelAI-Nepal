# Providers Type & Marketing Policy

**Last Updated:** 2026-11-08
**HEAD:** 603db71
**Maintained by:** TravelAI Nepal team

---

## 1. Platform Positioning

**What we are:** Nepal's first AI-native travel platform — an ecosystem connecting travelers, local agencies, and service providers.

**Tagline:** "AI itineraries. Verified local providers. One platform."

**Key differentiators:**
- AI-generated personalized itineraries (all categories)
- Zero-commission provider model
- Digital contracts + blockchain-ready permits
- Offline-capable SOS safety system
- Multi-lingual (EN/NP/HI/ZH/RU)
- 285 automated tests, continuous deployment

**What to say in marketing:**
- ✅ "AI-powered travel platform" (not "website")
- ✅ "Ecosystem for travelers and providers"
- ✅ "Nepal's first AI-native travel OS"
- ❌ Avoid: "Just a website" / "booking site"

---

## 2. Provider Types (10)

| # | Provider Type | Slug | Category |
|---|---|---|---|
| 1 | Shop Owner | shop-owner | Shop |
| 2 | Rental Provider | rental-provider | Rental |
| 3 | Wholesale Provider | wholesale-provider | Wholesale |
| 4 | Hotel | hotel | Hotel / Resort / Lodge / Homestay |
| 5 | Restaurant | restaurant | Restaurant |
| 6 | Transport Provider | transport-provider | Transport |
| 7 | Individual Guide | guide | Individual |
| 8 | Money Changer | money-changer | Money Changer |
| 9 | Art Craft | art-craft | Art Craft |
| 10 | Trekking Agency | trekking-agency | Trekking |

---

## 3. Service Categories (13)

Resort, Lodge, Homestay, Shop, Rental, Wholesale, Hotel, Restaurant, Transport, Individual, Money Changer, Art Craft, Trekking

---

## 4. What Providers Can Do

| Action | All Types |
|---|---|
| Register at `/register/provider` | ✅ |
| Create services/products with images | ✅ |
| Upload cover image / logo | ✅ |
| Set prices | ✅ |
| Receive bookings/orders/RFQs | ✅ |
| Access dashboard | ✅ |
| Manage staff | ✅ (plan-dependent) |

---

## 5. Image Upload System (Confirmed Complete)

| Feature | Status | Location |
|---|---|---|
| Provider logo | ✅ | ProfileController |
| Provider cover image | ✅ | ProfileController |
| Service cover image | ✅ | ServiceController |
| Product cover image | ✅ | ProductController |
| Itinerary day media (multiple) | ✅ | ItineraryDayMediaController |
| Ad images | ✅ | AdController |
| Payment proof / receipts | ✅ | PaymentController |
| QR code images | ✅ | PaymentMethodController |
| Verification documents | ✅ | VerificationController |
| User avatar | ✅ | User model |

**सबै provider categories मा image upload काम गर्छ।**

---

## 6. Plan Limits (15 keys, All Enforced)

| Plan | Price (NPR/mo) | Key Limits |
|---|---|---|
| **Free** | 0 | rooms:5, staff:1, treks:3, guides:3, hotels:1, routes:3, tables:3, artworks:5, bookings:10, products:5, vehicles:5, currencies:5, menu_items:5, ai_requests:5, itinerary_days:10 |
| **Professional** | 999 | rooms:30, staff:5, treks:20, guides:20, hotels:5, routes:15, tables:10, artworks:30, bookings:100, products:30, vehicles:30, currencies:20, menu_items:30, ai_requests:50, itinerary_days:30 |
| **Business** | 2,999 | rooms:200, staff:20, treks:100, guides:100, hotels:20, routes:50, tables:30, artworks:200, bookings:1000, products:200, vehicles:200, currencies:50, menu_items:200, ai_requests:500, itinerary_days:100 |
| **Enterprise** | Custom | सबै -1 (unlimited) — ai_requests:5000 |

**सबै 15 limits code मा enforce भएको ✅**

---

## 7. Marketing Policy

### 7.1 Who to Approach

**Primary (highest ROI):**
- Trekking agencies (route + itinerary providers)
- Hotels, lodges, resorts, homestays
- Restaurants
- Transport operators

**Secondary:**
- ArtCraft studios
- Gear rental shops
- Wholesale suppliers

**Tertiary:**
- Money changers
- Individual guides

### 7.2 Key Messages

- **"Zero commission"** — keep 100% of revenue
- **"AI brings qualified leads"** — no ad spend needed
- **"One dashboard"** — bookings, permits, analytics, staff
- **"Verified + digital contracts"** — build traveler trust
- **"Free plan forever"** — no risk to start

### 7.3 Onboarding Flow

1. Provider registers at `/register/provider` (choose provider type)
2. Upload logo + cover image
3. Admin verifies business documents (optional initially)
4. Create services/products with cover images
5. Provider goes live — AI itineraries attach their services
6. Provider manages bookings, staff, analytics from dashboard

### 7.4 Plan Upgrade Path

Free → Professional (999) → Business (2999) → Enterprise (custom)

Upgrade anytime; limits increase immediately.

---

## 8. Future Category: Rescue Services

**Status:** Planned (post-launch)

**Why:** Nepal मा helicopter rescue, medical evacuation, and mountain rescue providers छन् — SOS system सँग integrate गर्न सकिन्छ।

**Planned:**
- New Provider Type: `rescue-service`
- New Category: `rescue`
- Services: helicopter rescue, medical evacuation, search & rescue
- Integration: SOS alerts → nearest rescue provider notification

**Providers to onboard (when live):**
Air Dynasty, Simrik Air, Manang Air, Heli Everest, Mountain Helicopters, Kailash Helicopter

**Priority:** After SOS siren bug fix + core stability.

---

## 9. Changelog

| Date | Change |
|---|---|
| 2026-11-08 | Document created — full audit of provider types, categories, plan limits, image upload, marketing policy |

---

**End of Document** | Living document — update as project evolves