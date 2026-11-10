# TravelAI Nepal — SOS Master Plan & Smart Emergency Triage

## Design Freeze Document v1.2 (Master Plan Only — No Code)

**Created:** 2026-11-10
**Last Updated:** 2026-11-10 (v1.2)
**Status:** 🟡 PENDING USER APPROVAL
**Stage:** Master Planning Only — No coding, no migrations, no implementation
**Owner:** TravelAI Nepal Product & Engineering
**Related:** Rescue Service Plan, Admin Command Center, SOS Full Upgrade

---

## ⚠️ CORE SAFETY PRINCIPLES (Never Violate)

1. **Safety-First:** कुनै पनि SOS लाई "Resolved" तब मात्र भनिन्छ जब Traveler सुरक्षित भएको पुष्टि हुन्छ। Provider ले "Acknowledge" गरेको आधारमा Resolved मान्न **पाइँदैन**।
2. **No False Guarantees:** ३ वा १० मिनेटका escalation targets **operational targets** हुन् — rescue response को **guarantee होइनन्**। Weather, network, provider availability ले असर गर्न सक्छ।
3. **Operational Fallback:** Network/GPS unavailable, provider offline, rescue unavailable हुँदा पनि Traveler ले SOS पठाउन र fallback जानकारी पाउन सक्नुपर्छ।

---

## 01 — PRODUCT OVERVIEW & OBJECTIVES

**समस्या:** Nepal को trekking क्षेत्रमा emergency response ढिलो हुन्छ। Network कमजोर, GPS stale, provider सम्पर्कमा नआउन सक्छ।

**समाधान:** TravelAI Smart Emergency Triage — Traveler ले गम्भीरता अनुसार सही सहायता माग्ने, सही provider लाई सही समयमा खबर पठाउने, Admin लाई real-time visibility दिने।

**Objectives:**
1. Traveler ले 1-click मा Critical SOS पठाउन सकोस्।
2. Critical SOS तुरुन्तै Trekking Provider + Rescue Provider + Admin सबैलाई जाओस्।
3. Urgent SOS मा provider ले assessment गर्न पाओस्, rescue visibility कायम रहोस्।
4. Admin ले सबै SOS सुरुदेखि नै देखोस्।
5. False alarm कम, तर false negative (missed emergency) शून्य।

---

## 02 — EMERGENCY LEVELS & SELECTION CRITERIA

**३ वटा SOS Levels + General Help (अलग) + Unclassified ("Not Sure" को लागि)**

| Level | Name | Criteria | SOS Queue |
|---|---|---|---|
| 🔴 | **CRITICAL** | बेहोस, गम्भीर चोट, अत्यधिक रक्तस्राव, सास फेर्न गाह्रो, जीवन जोखिम | ✅ Yes |
| 🟠 | **URGENT** | हड्डी भाँचिएको शंका, हिँड्न नसक्ने, altitude sickness, बाटो हराएको | ✅ Yes |
| 🟡 | **ASSISTANCE** | सामान्य चोट, dehydration, थकान, मौसमले यात्रा रोकिएको, guidance | ✅ Yes |
| ⬛ | **UNCLASSIFIED** | Traveler लाई थाहा छैन — तुरुन्तै assessment आवश्यक | ✅ Yes |
| ⚪ | **GENERAL HELP** | सामान्य प्रश्न, check-in, जानकारी, trekking provider सम्पर्क | ❌ No — separate messaging |

**नियम:**
- General Help कहिल्यै SOS emergency queue मा नजाओस् — separate messaging feature।
- Level छान्दा **default कोई पनि selected नहोस्** (गलत dispatch रोक्न)।
- **Upgrade** जहिले पनि गर्न सकिने (Assistance → Urgent → Critical, Unclassified → any)।
- **Downgrade** Admin/Provider documented review बिना auto नहुने।

---

## 03 — ALERT ROUTING & ESCALATION MATRIX

**महत्त्वपूर्ण:** यो matrix operational target हो — **guarantee होइन**।

| Level | Trekking Provider | Rescue Provider | Admin | Escalation |
|---|---|---|---|---|
| 🔴 Critical | तुरुन्तै (0 min) | **तुरुन्तै parallel** | तुरुन्तै | कोई timer नै — सँगै |
| 🟠 Urgent | तुरुन्तै (0 min) | **तुरुन्तै visibility**, dispatch provider assessment पछि | तुरुन्तै | 3 min escalation यदि accept छैन |
| 🟡 Assistance | तुरुन्तै (0 min) | **आवश्यकता अनुसार** (admin approval वा 10 min) | तुरुन्तै | 10 min escalation यदि सहायता छैन |
| ⬛ Unclassified | तुरुन्तै (0 min) | **तुरुन्तै visibility** (assessment मात्र) | तुरुन्तै | कोई timer नै — तुरुन्तै assessment |
| ⚪ General Help | Message | ❌ | ❌ | ❌ |

**Admin Visibility:** सबै SOS सुरुदेखि नै देखिने। 5/10 min पछि मात्र होइन।

**Responsibility Assignment:**
- **Critical:** जसले पहिले Accept गर्छ (60s lock)
- **Urgent:** Trekking Provider ले assessment; Rescue Provider parallel standby
- **Assistance:** Trekking Provider ले ह्यान्डल; rescue चाहिए Admin escalation
- **Unclassified:** Admin ले तुरुन्तै assess गरेर level निर्धारण गर्ने

**Provider Unavailability:**
- Trekking Provider offline → Admin ले rescue escalation trigger
- Rescue Provider offline → Admin manual dispatch → अन्य provider खोज्ने
- सबै offline → Admin manual intervention + Traveler लाई fallback contact देखाउने

---

## 04 — TRAVELER EMERGENCY UI/UX

**SOS Button Flow (Modal) — ५ वटा Options:**
┌─────────────────────────────────────────────┐
│ TravelAI Nepal — Emergency SOS │
│ │
│ What happened? │
│ Choose the option that best │
│ describes your situation. │
│ │
│ 🔴 CRITICAL │
│ Life-threatening injury or │
│ immediate danger. │
│ │
│ 🟠 URGENT │
│ Serious injury, altitude sickness │
│ or unable to continue safely. │
│ │
│ 🟡 ASSISTANCE │
│ Minor injury, dehydration or │
│ guidance needed. │
│ │
│ ───────────────────────────────── │
│ Not sure what to choose? │
│ 🆘 SEND SOS NOW (Unclassified) │
│ Immediate assessment by responders. │
│ The level will be determined after │
│ assessment. │
│ │
│ ───────────────────────────────── │
│ General questions? Contact your │
│ trekking provider. │
└─────────────────────────────────────────────┘

text

**Rules:**
- **Default selection = कोई नै** (गलत dispatch रोक्न)।
- Critical पहिलो option, तर auto-selected होइन।
- "Not Sure? → SEND SOS NOW" = **Unclassified SOS** — तुरुन्तै सबैलाई notify, तर Critical भनेर silently label नै।
- SOS पठाइसकेपछि level upgrade गर्न मिल्ने, injury type/photo optional।
- GPS stale (>5 min) → "Stale location — last update X min ago" स्पष्ट।
- Cancel → reason mandatory + audit log।

---

## 05 — TREKKING PROVIDER SOS VIEW

**Provider ले देख्ने:**
- Level badge (🔴/🟠/🟡/⬛) prominently
- Traveler info, location (GPS age सहित), emergency description
- Actions: Acknowledge → Contact Traveler → Dispatch Rescue (यदि Urgent) → Mark Resolved (notes mandatory)
- Critical मा: "Rescue already notified" स्पष्ट
- Unclassified मा: "Level under assessment — please assess and classify" स्पष्ट
- **"Acknowledge" ≠ "Resolved"** — UI copy स्पष्ट

---

## 06 — RESCUE PROVIDER SOS VIEW

**Rescue Provider ले देख्ने:**
- Critical, Urgent र Unclassified SOS (Assistance admin approval पछि मात्र)
- Traveler info, GPS (stale सहित), description
- Critical: 60s lock + Accept/Confirm
- Urgent: Parallel visibility — Accept आवश्यक नै, dispatch admin/timer पछि
- Unclassified: Parallel visibility — assessment मात्र, dispatch admin decision पछि
- Dual timeline: Dispatched → Accepted → En Route → On Scene → Extracting → Completed

---

## 07 — TRAVELAI ADMIN COMMAND CENTER

**Admin ले देख्ने:**
- सबै SOS real-time (Critical, Urgent, Assistance, Unclassified)
- Level filter tabs
- Escalation queue (Urgent >3 min, Assistance >10 min)
- Manual dispatch capability
- Level upgrade/downgrade capability (reason सहित)
- Immutable admin actions log
- Provider status (online/offline/busy)

---

## 08 — SOS & RESCUE SEPARATE LIFECYCLES

**SOS Lifecycle:**
Pending → Acknowledged → Responding → Resolved / False Alarm / Cancelled

text

**Rescue Lifecycle:**
Dispatched → Accepted → En Route → On Scene → Extracting → Completed / Cancelled

text

**महत्त्वपूर्ण:**
- Emergency Level कुनै lifecycle status नै — separate classification।
- SOS Resolved ≠ Rescue Completed।
- **Assistance SOS:** Rescue mission बिना पनि SOS Resolved हुन सक्छ (Trekking Provider ले ह्यान्डल गरेपछि)।
- **Critical/Urgent:** SOS Resolved तब मात्र जब Rescue Completed वा Traveler safe confirmed।
- **Unclassified:** पहिले Admin/Provider ले level classify गर्छ, त्यसपछि मात्र उही नियम लागू।

---

## 09 — EDGE CASES & SAFETY FALLBACKS (Expanded)

### 09.1 — GPS Stale / Unavailable

**Trigger:** GPS last ping >5 minutes, OR GPS permission denied, OR no signal।

| Scenario | Behavior |
|---|---|
| GPS unavailable (no permission) | SOS पठाउन सकिने। UI: "📍 Location unknown — please describe where you are" |
| GPS stale (>5 min) | UI: "⚠️ Location may be outdated — last update X min ago" |
| GPS stale (>30 min) | UI: "🔴 Location is significantly outdated — include landmark/route in description" |
| GPS live (<1 min) | UI: "🟢 Live location" (green badge) |

**Rules:**
- **कहिल्यै stale GPS लाई "Live Location" भनेर नदेखाउने।**
- SOS submission **कहिल्यै GPS को कारण block हुँदैन**।
- Admin/Provider view मा सधैं GPS age + last ping time देखिन्छ।
- Rescue dispatch निर्णयमा GPS freshness ले role खेल्छ, तर **blocker होइन**।

### 09.2 — False Alarm / Accidental SOS

**Flow:**

| Step | Action |
|---|---|
| 1 | Traveler "Cancel SOS" थिच्छ |
| 2 | Modal: "Why are you cancelling? (required, max 500 chars)" |
| 3 | Reasons: Accidental / Resolved on my own / Wrong person / Other |
| 4 | Submit → SOS status = `cancelled` (Resolved नै) |
| 5 | Audit log entry: actor, reason, timestamp |
| 6 | Admin ले "Cancelled SOS" filter मा देख्छ (awareness) |

**Rules:**
- **Cancel गर्नु ≠ emergency सकियो** — Admin/Provider ले verify गर्न सक्छ।
- Repeated false alarms (3+) → Traveler लाई soft warning, admin review flag।
- False alarm reason **mandatory**, कहिल्यै skip नहुने।

### 09.3 — Level Upgrade / Downgrade

**Upgrade (Assistance → Urgent → Critical, Unclassified → any):**

| From | To | Who | Approval |
|---|---|---|---|
| Assistance | Urgent | Traveler | Auto |
| Urgent | Critical | Traveler | Auto |
| Unclassified | Any | Traveler, Admin, Trekking Provider, Rescue Provider | Auto |
| Any | Critical | Admin | Auto |

**Downgrade (Critical → Urgent → Assistance):**

| From | To | Who | Approval |
|---|---|---|---|
| Critical | Urgent | Admin only | Reason mandatory |
| Urgent | Assistance | Admin only | Reason mandatory |
| Any | Unclassified | ❌ Not allowed | — |

**Rules:**
- **Upgrade**: कहिल्यै blocked नहुने, तुरुन्तै notify।
- **Downgrade**: Admin documented reason + audit log mandatory।
- Downgrade पछि rescue provider लाई notify (यदि rescue मा थियो भने "stand down" alert)।
- सबै level changes `sos_level_history` table मा store।

### 09.4 — Audit Requirements (Level & Escalation)

**हरेक level change मा capture गर्नुपर्ने:**

| Field | Value |
|---|---|
| SOS ID | `sos_alert_id` |
| From level | Old value (NULL for first) |
| To level | New value |
| Changed by | `user_id` (System = NULL) |
| Reason | Free text (downgrade मा mandatory) |
| Timestamp | `created_at` |

**हरेक escalation मा capture गर्नुपर्ने:**

| Field | Value |
|---|---|
| SOS ID | `sos_alert_id` |
| Escalation type | `admin_notified`, `auto_escalated`, `manual_dispatch`, `rescue_notified` |
| From status | SOS status before |
| To status | SOS status after |
| Triggered by | `user_id` or `System` |
| Reason | Notes |
| Timestamp | `created_at` |

**Audit immutability:**
- Audit rows **कहिल्यै delete/update हुँदैन**।
- OTP / receipt codes **कहिल्यै audit मा store नहुने**।
- Audit log १८ महिना retain (typical compliance)।

---

## 10 — PRIVACY, MEDICAL DATA & AUDIT TRAIL (Expanded)

### 10.1 — GPS Data Privacy

| Aspect | Rule |
|---|---|
| Storage | SOS creation + level change मा मात्र snapshot |
| Visibility | Admin, Trekking Provider (own SOS), Rescue Provider (Critical/Urgent/Unclassified only) |
| Retention | SOS Resolved + १८ महिना पछि GPS history delete |
| Export | Admin export को लागि request + audit log |
| Third-party | कहिल्यै shared नहुने (except Rescue Provider for dispatch) |

### 10.2 — Medical Data Handling

**Traveler opt-in only:**

| Field | Field-level encryption? | Visible to |
|---|---|---|
| Blood group | No (low sensitivity) | Admin, Trekking Provider, Rescue Provider |
| Allergies | Yes (PII) | Trekking Provider, Rescue Provider (यदि Critical/Urgent मा) |
| Chronic conditions | Yes (PII) | Same as allergies |
| Emergency contact | Yes (PII) | Admin, Trekking Provider |

**Rules:**
- Medical data **opt-in mandatory** — Traveler profile मा opt-in checkbox।
- Opt-in नगरेको अवस्थामा SOS मा "Medical info not available" स्पष्ट।
- Rescue Provider ले **केवल active mission सँग सम्बन्धित** medical data देख्छ।
- Medical data **कहिल्यै** log/analytics मा export नहुने।

### 10.3 — Audit Trail Structure

**Tables involved:**

| Table | Purpose | Immutable? |
|---|---|---|
| `sos_alert_actions` | All SOS actions (ack, respond, resolve, false_alarm) | ✅ Yes |
| `sos_level_history` | Level changes (from → to) | ✅ Yes |
| `rescue_missions_updates` | Rescue mission lifecycle | ✅ Yes |
| `rescue_dispatch_locks` | 60s lock events | ✅ Yes (deleted on release) |
| `notifications` | Notification delivery | ❌ Mutable (read status) |

**Rules:**
- **सबै audit rows**: `actor_id` (System = NULL), `created_at`, `from_state`, `to_state` mandatory।
- Audit log **कहिल्यै delete नहुने** (retention १८ महिना minimum)।
- Admin audit viewer मा filter by date, actor, SOS ID।

### 10.4 — OTP / Receipt Code Handling

| Code type | Storage | Logging |
|---|---|---|
| OTP (SMS) | Hash only (bcrypt) | **कहिल्यै log नहुने** |
| Receipt code | Hash only | **कहिल्यै log नहुने** |
| Audit metadata | `otp_hash_last4` only | "No OTP/code in metadata" |
| Reissue tracking | `reissue_count`, `last_reissue_at` | ✅ Yes |

**Rules:**
- OTP/code **कहिल्यै** audit log / error log / debug log मा देखिने नै।
- Code को hash **कहिल्यै** API response मा expose नहुने।
- Code verify fail → counter increment (rate limit), but raw code **कहिल्यै log नहुने**।

### 10.5 — Data Retention & Deletion

| Data | Retention |
|---|---|
| Active SOS | जबसम्म Resolved/Cancelled नहुन्छ |
| Resolved SOS (metadata) | १८ महिना |
| GPS history | १८ महिना |
| Audit log | १८ महिना minimum (कहिल्यै auto-delete नै) |
| Medical data | Traveler opt-out गरेपछि ३० दिनमा delete |
| Notifications | ९० दिन |

**GDPR-style Traveler rights:**
- Right to access (export SOS history)
- Right to deletion (profile + medical data)
- Right to audit trail access (own SOS only)

---

## 11 — DATABASE & TECHNICAL REQUIREMENTS

**नयाँ columns (`sos_alerts`):**

```sql
ALTER TABLE sos_alerts 
    ADD COLUMN emergency_level ENUM('critical','urgent','assistance','unclassified') NULL DEFAULT NULL,
    ADD COLUMN level_changed_at TIMESTAMP NULL,
    ADD COLUMN level_changed_by BIGINT UNSIGNED NULL,
    ADD COLUMN cancel_reason VARCHAR(500) NULL;
नयाँ table (sos_level_history):

sql
CREATE TABLE sos_level_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sos_alert_id BIGINT UNSIGNED NOT NULL,
    from_level ENUM('critical','urgent','assistance','unclassified') NULL,
    to_level ENUM('critical','urgent','assistance','unclassified') NOT NULL,
    changed_by BIGINT UNSIGNED NULL,
    reason VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
Config (config/sos.php):

php
'levels' => [
    'critical'     => ['rescue_immediate' => true,  'escalation_minutes' => 0],
    'urgent'       => ['rescue_immediate' => true,  'escalation_minutes' => 3],
    'assistance'   => ['rescue_immediate' => false, 'escalation_minutes' => 10],
    'unclassified' => ['rescue_immediate' => true,  'escalation_minutes' => 0],
],
✅ Default NULL स्पष्ट — कोई default level नै।

12 — ACCEPTANCE CRITERIA & TESTING
Level परीक्षण:

□ Critical → तुरुन्तै Trekking + Rescue + Admin
□ Urgent → तुरुन्तै सबै, rescue dispatch admin/timer पछि
□ Assistance → Trekking मात्र, 10 min मा escalation
□ Unclassified → तुरुन्तै सबै, level assessment पछि classify
□ General Help → SOS queue मा नै
Lifecycle परीक्षण:

□ Trekking Acknowledge → SOS "Acknowledged" मात्र (Resolved नै)
□ Rescue Complete → SOS "Resolved"
□ Assistance rescue बिना Resolved
□ False Alarm → reason + audit
□ Unclassified → classify → resolve
Edge Case:

□ GPS unavailable → SOS पठाउन सकिने
□ Network unavailable → local queue + fallback message
□ सबै provider offline → admin intervention
□ Level upgrade → works, downgrade → admin review
Regression:

□ 295 + नयाँ tests सबै pass
□ Hotel, Restaurant, Trek, Rescue सबै आ-आफ्नै layout
13 — IMPLEMENTATION ROADMAP
Phase 1: DB & Backend (~2 hr)

Migration (emergency_level + level history)

SOS Model update

Api\SosController (level save + immediate rescue dispatch)

EscalateSosAlerts (level-based timing)

Phase 2: Traveler UI (~2 hr)

SOS Modal (3 levels + Unclassified + General Help)

Level change (upgrade only)

GPS stale warning

Cancel flow

Phase 3: Provider & Admin UI (~2 hr)

Trekking Provider view (level badge + Unclassified badge)

Rescue Provider view (Critical/Urgent/Unclassified)

Admin filter, manual upgrade/downgrade + classify Unclassified

Phase 4: Testing & Deploy (~2 hr)

Unit + Feature + Browser tests

Production deploy

Total: ~8 hr (सबै phases पूरा गर्न)

⚠️ कोड सुरु गर्नु अघि User Approval आवश्यक।

14 — CHANGELOG (v1.0 → v1.1 → v1.2)
v1.0 → v1.1 (10 Contradictions Fixed)
#	Contradiction (v1.0)	Resolution (v1.1)
1	General Help SOS queue मा	General Help = separate messaging
2	"Not sure → SOS" silently Critical	"SEND SOS NOW" = explicit Critical
3	Assistance rescue visibility	Admin approval वा 10 min escalation
4	Lifecycles confusion	SOS vs Rescue पूर्ण अलग
5	Assistance resolution	Rescue बिना पनि Resolved हुन सक्छ
6	Offline SOS ambiguous	Local queue = responder ले पाएको होइन
7	Default level confusion	emergency_level DEFAULT NULL
8	Responsibility unclear	प्रत्येक level को responsibility स्पष्ट
9	Timers guarantee vs target	Operational targets, guarantee नै
10	Privacy/Edge incomplete	GPS stale, false alarm, upgrade/downgrade, audit पूरा
v1.1 → v1.2 (Product Decisions Approved)
#	Change	Detail
1	Unclassified SOS added	"Not Sure" → explicit unclassified — silently Critical नै
2	Section 09 expanded	GPS stale, false alarm, level upgrade/downgrade, audit detail
3	Section 10 expanded	GPS privacy, medical data opt-in, audit tables, OTP handling, retention
4	Section 15 added	Final product decisions with 7 product-level requirements
5	DB enum updated	critical, urgent, assistance, unclassified
6	Config updated	unclassified level added
7	Acceptance criteria	Unclassified + Assistance-resolves-without-rescue tests
15 — FINAL PRODUCT DECISIONS (Approved Direction)
Status: ✅ Approved by Product Owner (2026-11-10)

15.1 — Emergency Levels
Decision: A — 3 SOS Levels + General Help (separate) + Unclassified

Level	SOS Queue	Notes
🔴 Critical	✅ Yes	Immediate parallel alert
🟠 Urgent	✅ Yes	Immediate visibility, human dispatch
🟡 Assistance	✅ Yes	Trekking Provider first
⬛ Unclassified	✅ Yes	"Not Sure" case
⚪ General Help	❌ No	Separate messaging
15.2 — Unclassified SOS / "Not Sure" Button
Decision: C — Explicit Unclassified SOS with Immediate Assessment

Traveler ले "Not Sure? Send SOS Now" थिच्दा emergency_level = 'unclassified' SOS बन्छ।

Admin + Trekking Provider + Rescue Provider सबैलाई तुरुन्तै जानकारी जान्छ।

SOS card मा "Level not yet determined — under assessment" स्पष्ट देखाइन्छ।

Critical भनेर silently label गर्दैन।

Assessment पछि Admin वा authorized responder ले level upgrade गर्न सक्छ।

Initial notification कहिल्यै ढिलो हुँदैन।

15.3 — Assistance Rescue Visibility
Decision: A — Admin approval or escalation only

Admin ले सबै SOS तुरुन्तै देख्छ।

Assistance SOS पहिले Trekking Provider मा जान्छ।

Rescue involvement ३ तरिकाले: Admin assessment / Traveler upgrade / 10-min escalation।

10-min timer = rescue guarantee होइन।

15.4 — Urgent Rescue Dispatch
Decision: A — Human assessment before dispatch

Urgent SOS तुरुन्तै Trekking + Rescue + Admin सबैलाई देखिन्छ।

Rescue Provider ले तुरुन्तै assess र acknowledge गर्न सक्छ।

Actual rescue mission dispatch = authorized human decision।

Timer expiry ले auto dispatch गर्दैन।

Critical SOS ले parallel notification पाउँछ — assessment कुर्दैन।

15.5 — Emergency Level Default
Decision: A — NULL default, explicit selection mandatory

नयाँ SOS मा कहिल्यै silently Critical assign गर्दैन।

Traveler ले explicit रूपमा select गर्नुपर्छ: Critical / Urgent / Assistance / Unclassified।

Historical SOS records: NULL allowed।

DB design: ENUM('critical','urgent','assistance','unclassified') NULL DEFAULT NULL

15.6 — Additional Product-Level Requirements
#	Requirement	Enforcement
1	SOS acknowledgement ≠ SOS resolution	Section 08 + 09
2	Rescue completion ≠ SOS resolution (but coordinated)	Section 08 + mission→SOS sync
3	Assistance SOS can resolve without rescue mission	Section 08 + Section 12 tests
4	Every level change = accountable actor + reason	Section 10 + sos_level_history
5	Every escalation = preserved in history	Section 10 audit
6	No timer/acceptance/notification = proof of safety	Section 03 + UI copy
7	Every level = clear ownership + fallback	Section 03 responsibility matrix
📋 APPROVAL SECTION
TravelAI Nepal Product Owner:

मैले यो Design Freeze Document v1.2 पढें र approve गर्छु। Implementation सुरु गर्न सकिन्छ।

Signature: _________________
Date: _________________
Comments (optional): _________________

End of Document | Version 1.2 | No Code Written — Pending Approval

