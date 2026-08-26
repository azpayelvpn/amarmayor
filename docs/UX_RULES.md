# User Experience (UX), Design System & Accessibility Rules

> **Document Status:** Authoritative UX & Interface Design Specification  
> **Source of Truth:** [docs/MASTER_SPEC.md](file:///C:/laragon/www/amarmayor/docs/MASTER_SPEC.md) (Sections 1–62, 313–376, 443–474, 577–594, 710–711, 718)

---

## 1. UX Philosophy: The Simplicity Principle

> **"Technical complexity belongs in backend domain services; simplicity belongs on the screen."**

Every screen in the Mymensingh City Citizen Service Platform must instantly answer the user's single primary question in clear, natural human language.

| Persona | Primary Screen Question | First Viewport Answer |
|---|---|---|
| **Citizen (নাগরিক)** | *"আমার সমস্যার সমাধান কি হচ্ছে?" (Is my problem being solved?)* | Instant timeline, current clear status badge, photo evidence of resolution. |
| **Field Worker (মাঠকর্মী)** | *"আমাকে এখন ঠিক কী করতে হবে?" (What do I need to do right now?)* | Today's active task, map location, direct "Start Work" & "Take Photo" buttons. |
| **Supervisor (সুপারভাইজার)** | *"আমার এলাকায় আজ কোন কাজটি আটকে আছে বা পিছিয়ে পড়ছে?" (What is delayed in my area?)* | Overdue items, unassigned queue, 1-click team dispatch and verification cards. |
| **Mayor / Administrator (মেয়র / প্রশাসক)** | *"নগরের কোথায় আজ আমার জরুরি মনোযোগ প্রয়োজন?" (What needs my immediate attention?)* | 6 citywide KPIs, *Attention Required* card (missed deadlines, reopens), Action Directives. |
| **Ward Councillor / Officer (কাউন্সিলর / কর্মকর্তা)** | *"আমার ওয়ার্ডের সামগ্রিক সেবার অবস্থা কেমন?" (What is my Ward's service status?)* | Ward resolution rate, active problem hotspots, citizen feedback ratings. |
| **Platform Super Admin (প্ল্যাটফর্ম অ্যাডমিন)** | *"প্রশাসনিক পরিবর্তন কীভাবে সহজে করব?" (How do I update staffing/rules without jargon?)* | Plain-language modules (People, Areas, Governance, Routing) with 2-click guided wizards. |
| **Technical Super Admin (কারিগরি অ্যাডমিন)** | *"সিস্টেম কি পুরোপুরি সচল আছে?" (Is the platform healthy?)* | Green/Amber/Red health tiles (Website, DB, Redis, SMS, Workers, Backups). |

---

## 2. Action-Driven Interface Vocabulary

Normal users must never see or manipulate raw database state codes (e.g., `in_progress`, `routed`). All UI controls use **active, plain-language verbs**:

| Domain Action | Bangla Action Button | English Action Button | Primary User Role |
|---|---|---|---|
| Submit Complaint | সমস্যা জানান | Report a Problem | Citizen |
| Start Task | কাজ শুরু করুন | Start Work | Field Worker |
| Upload Evidence | কাজের ছবি দিন | Upload Photo Evidence | Field Worker |
| Mark Complete | কাজ শেষ হয়েছে | Work Completed | Field Worker |
| Verify Resolution | সমাধান যাচাই করুন | Verify Resolution | Supervisor |
| Return for More Work | পুনরায় কাজ করতে বলুন | Return for More Work | Supervisor |
| Confirm Resolution | হ্যাঁ, সমাধান হয়েছে | Yes, Problem Resolved | Citizen |
| Reject Resolution | না, সমস্যা রয়ে গেছে | Problem Still Exists | Citizen |
| Ask for Action | দ্রুত ব্যবস্থা নিন | Ask for Action | Mayor / Administrator |
| Provide Support | সহায়তা প্রদান করুন | Provide Support | Mayor / Administrator |
| Request Explanation | ব্যাখ্যা তলব করুন | Request Explanation | Mayor / Administrator |

---

## 3. Typography & Localization Rules

1. **Bangla-First Presentation:**
   * Primary Font: High-legibility Bengali web fonts (e.g., *Hind Siliguri*, *Noto Sans Bengali*, or clean system fallback *SolaimanLipi*).
   * Bangla Numerals & Dates: Citizen screens, public counters, and time stamps format numbers in Bangla (e.g., `১২ মিনিট আগে`, `২৪ আগস্ট ২০২৬`).
2. **Bilingual Toggle:** Every public and administrative header contains a clean toggle (`বাংলা | EN`) that switches language preferences instantly.
3. **No Bureaucratic Jargon:** Terminology must be easily understood by citizens with basic digital literacy.

---

## 4. Platform Admin Non-Technical Experience

The Platform Super Admin dashboard is tailored for non-technical administrative officers:
* **No Database Terminology:** Avoid terms like *Foreign Keys, Enums, Crud Tables, Idempotency*.
* **Organized Modules:**
  1. **কর্মীবাহিনী (People & Workforce):** Add employees, change postings, assign teams.
  2. **এলাকা ও ওয়ার্ড (Areas & Wards):** View zones, wards, office locations.
  3. **জনপ্রতিনিধি (Governance):** Assign Mayor, Councillors, Responsible Officers with effective dates.
  4. **সেবা ও সময়সীমা (Services & Deadlines):** Set expected resolution hours (e.g., *"ময়লা অপসারণ: ৮ ঘণ্টা"*).
  5. **স্বয়ংক্রিয় দায়িত্ব বণ্টন (Automatic Routing):** Connect categories and wards to responsible supervisors.
  6. **দায়িত্ব বিভ্রাট সমাধান (Responsibility Gaps):** 2-click guided wizard to fix unassigned services.

---

## 5. Technical Super Admin Simple Health Experience

The Technical Super Admin landing page features a **traffic-light health matrix**:

```text
┌─────────────────────────────────────────────────────────────┐
│  🟢 ওয়েবসাইট ও পোর্টাল (Web Portal)     - সচল (Healthy)     │
│  🟢 মূল ডেটাবেজ (MySQL Database)       - সচল (0.02ms)      │
│  🟢 দ্রুত মেমোরি সার্ভিস (Redis Cache) - সচল (Connected)   │
│  🟢 এসএমএস সার্ভিস (SMS Gateway)        - সচল (Balance: OK) │
│  🟢 ব্যাকগ্রাউন্ড প্রসেসিং (Workers)     - সচল (0 Failed)    │
│  🟢 ব্যাকআপ ও নিরাপত্তা (Backups)       - সফল (আজ রাত ৩:০০)  │
└─────────────────────────────────────────────────────────────┘
```

* **Advanced Details:** Raw server logs, OPcache statistics, and queue retry internals are neatly placed under an **"উন্নত কারিগরি তথ্য (Advanced Technical Details)"** expandable panel.

---

## 6. Mobile Responsiveness & HTMX Performance

1. **Mobile-First Field Experience:**
   * Large tap targets ($\ge 48\text{px}$) for field workers wearing gloves or operating in bright sunlight.
   * Native camera integration with live preview and compression before upload.
2. **Fast Dynamic Updates via HTMX:**
   * Filter bars, search boxes, tab navigation, and pagination update asynchronously without full-page reloads.
   * Executive Command Center and Supervisor Queue utilize configurable background polling (15–30s) to keep status live without complex WebSocket infrastructure.
