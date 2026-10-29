## 📋 TravelAI Nepal — System Reference Report

### 🅰️ Registration Dropdown — **18 Provider Types**

नयाँ provider register गर्दा **dropdown मा 18 वटा types**:

| # | Provider Type | Dashboard |
|---|---|---|
| 1 | Hotel | Hotel |
| 2 | Resort | Hotel |
| 3 | Lodge | Hotel |
| 4 | Homestay | Hotel |
| 5 | Trekking Agency | Base (Trek) |
| 6 | Tour Agency | Base (Tour) |
| 7 | Activity Provider | Base (Activity) |
| 8 | Local Experience | Base (Experience) |
| 9 | Shop Owner | Inventory |
| 10 | Rental Provider | Inventory |
| 11 | Wholesale Provider | Inventory |
| 12 | Guide | Individual |
| 13 | Porter | Individual |
| 14 | Photographer | Individual |
| 15 | Restaurant | Restaurant |
| 16 | Transport Provider | Transport |
| 17 | Money Changer | Money Changer |
| 18 | Art/Craft | Art/Craft |

---

### 🅱️ Service Categories — **18 Categories**

Provider ले service बनाउँदा **allowed category** (auto-locked based on PT):

| # | Service Category | Provider Type |
|---|---|---|
| 1 | Resort | Resort |
| 2 | Lodge | Lodge |
| 3 | Homestay | Homestay |
| 4 | Shop | Shop Owner |
| 5 | Rental | Rental Provider |
| 6 | Wholesale | Wholesale Provider |
| 7 | Trek | Trekking Agency |
| 8 | Tour | Tour Agency |
| 9 | Hotel | Hotel |
| 10 | Guide | Guide |
| 11 | Transport | Transport Provider |
| 12 | Activity | Activity Provider |
| 13 | Experience | Local Experience |
| 14 | Restaurant | Restaurant |
| 15 | Money Changer | Money Changer |
| 16 | Art/Craft | Art/Craft |
| 17 | **Porter** *(नयाँ — seeder)* | Porter |
| 18 | **Photographer** *(नयाँ — seeder)* | Photographer |

**Mapping: 18 PT ↔ 18 SC (1:1)**

---

### 🅲 Dashboard Coverage — **8 Dashboards**

| Dashboard | Covered PTs | Count | Theme Color |
|---|---|---|---|
| **Hotel** | Hotel + Resort + Lodge + Homestay | **4** | 🟨 Gold |
| **Base** (Trek/Tour) | Trekking Agency + Tour Agency + Activity + Local Experience | **4** | 🟦 Blue |
| **Inventory** | Shop Owner + Rental Provider + Wholesale Provider | **3** | 🟪 Indigo |
| **Individual** | Guide + Porter + Photographer | **3** | 🟩 Emerald |
| **Restaurant** | Restaurant | **1** | 🟧 Orange |
| **Transport** | Transport Provider | **1** | 🟦 Sky Blue |
| **Money Changer** | Money Changer | **1** | 🟩 Emerald |
| **Art/Craft** | Art/Craft | **1** | 🟫 Terracotta |
| **Total** | **18** | **8 dashboards** | 8 unique themes |

---

### 🅳 Examples per Provider Type

| Provider Type | Example Business | Dashboard | Can Create |
|---|---|---|---|
| **Hotel** | Himalayan View Hotel | Hotel | Hotel rooms, bookings |
| **Resort** | Sunrise Luxury Resort | Hotel | Resort services |
| **Lodge** | Lama Hotel Budget Lodge | Hotel | Lodge services |
| **Homestay** | Nepal Homestay Experience | Hotel | Homestay services |
| **Trekking Agency** | Everest Travels | Base | Trek packages |
| **Tour Agency** | Kathmandu Tours Co | Base | Tour packages |
| **Activity Provider** | Paragliding Nepal | Base | Activity services |
| **Local Experience** | Nepali Cooking Class | Base | Experience services |
| **Shop Owner** | Himalayan Gear Shop | Inventory | Products (sale) |
| **Rental Provider** | Kathmandu Rentals | Inventory | Products (rental) |
| **Wholesale Provider** | Bulk Supplier Nepal | Inventory | Products (wholesale) |
| **Guide** | Professional Guides Nepal | Individual | Guide services |
| **Porter** | Sherpa Porters Co | Individual | Porter services |
| **Photographer** | Trek Photography | Individual | Photography sessions |
| **Restaurant** | Everest Spice Kitchen | Restaurant | Menu items |
| **Transport Provider** | Besisahar Transport | Transport | Vehicles, routes |
| **Money Changer** | Himalayan Exchange | Money Changer | Exchange rates |
| **Art/Craft** | Patan Handicraft Studio | Art/Craft | Artworks, gallery |

---

### 🅴 Quick Stats

| Metric | Value |
|---|---|
| **Provider Types** | 18 |
| **Service Categories** | 18 (16 + 2 seeder) |
| **Dashboards** | 8 (7 vertical + 1 Base) |
| **Sidebar Buttons per Vertical** | ~10-13 |
| **Shared Pages per Vertical** | 10 (index + create + show for 5 pages) |
| **Theme Colors** | 8 unique |
