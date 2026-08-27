# Real Data Provenance & Gaps Log (REAL_DATA_GAPS.md)

> **Document Status:** Authoritative Data Integrity & Provenance Tracking  
> **Rule Reference:** AV. Hard Integrity Rules 2, 3, 4, 7, 9 & 10

---

## 1. Provenance Standard & Principles

Every verified official Mymensingh City Corporation (MCC / মসিক) Person, Employee, Governance Assignment, and Contact imported from an official public source must record and preserve provenance metadata:
* `source_name`: Title and publisher of the official source document/attachment.
* `source_url`: Verifiable URL or official document archive identifier.
* `source_checked_at`: Timestamp when the source was officially accessed/parsed.
* `verification_status`: Explicit classification state.
* `effective_from`: Official appointment or term start date.
* `effective_to`: Official term expiration or resignation/transfer date (`NULL` if active).

---

## 2. Data Verification Status Classifications

The platform distinguishes five explicit verification states across all administrative and structural entities:

| Verification Status | Definition | Access & Display Policy |
| :--- | :--- | :--- |
| **`verified_current`** | Fully verified against official MCC orders/gazettes with active tenure. | Authoritative institutional representation on public portals. |
| **`verified_historical`** | Verified official record whose effective tenure has expired. | Preserved immutably in governance history and tenure logs. |
| **`pending_verification`** | Imported or submitted data awaiting verification against official source. | Excluded from public authority profiles until verified. |
| **`structural`** | Baseline geographic/administrative boundaries (33 Wards, 3 Zones, 11 Reserved Seats). | Permanent architectural foundation. |
| **`demo_test`** | Fictional demo records for local development, simulation, and automated testing. | Clearly tagged, excluded from production, purgable via `demo:clear`. |

---

## 3. Official Ward Responsibility Source Log

### Document Target:
* **Official Document Title:** *“দায়িত্বপ্রাপ্ত কাউন্সিলরগণের নাম, ওয়ার্ড নং, মোবাইল নং”* (or corresponding MCC Administrator/LGRD Notification for Responsible Officers).
* **Official Publishing Entity:** Mymensingh City Corporation (MCC) / Local Government Division (LGRD).

### Current Status & Ingestion Policy:
1. **Deterministic Parsing Requirement:** Import and update Ward responsibility assignments **ONLY** if the official MCC source can be fetched and parsed with 100% field certainty (Ward Number, Official Name, Designation, Effective Date, Mobile Number).
2. **Strict Non-Fabrication Rules:**
   - **Do NOT guess** or extrapolate missing names.
   - **Do NOT infer** assignments from obsolete past election ballots.
   - **Do NOT infer** assignments from arithmetic Ward clustering (e.g. assuming Ward 1–3 must share an officer).
   - **Do NOT fabricate** placeholder names or placeholder mobile numbers for real officials.
3. **Documented Gaps:**
   - When an official source document is unavailable, unparsed, or partially corrupted, the affected Wards retain baseline `structural` representation with `governance_status = 'vacant'` or `pending_verification`.
   - The exact blocker, document date, and parsing limitation must be logged in this document.

---

## 4. Current Identified Real Data Gaps & Verified Baseline

| Entity / Scope | Official Source | Verification Status | Provenance & Applied Policy |
| :--- | :--- | :--- | :--- |
| **MCC Administrative Leadership** (Administrator, CEO, Secretary, Chief Engineer, Chief Waste Officer, Health Officer, ZEOs, Town Planner) | MCC Official Administration Directory & Ministry of LGRD Gazette (`https://mcc.gov.bd/site/page/officers-directory`) | `verified_current` | Fully imported into `persons` and `employees` with `user_id = NULL`. Zero login accounts created (Rule 7: Person != Employee != User). |
| **General Wards 01–33 Councillors** | Ministry of LGRD Transitional Orders | `pending_verification` / Transition | Post-August 2024 local government transition under Administrator. Wards seeded structurally; representation assignments populated as official gazettes are released. |
| **Reserved Seats 01–11** | Local Government (City Corporation) Act 2009 | `structural` | 11 reserved seats configured with statutorily defined coverage mapping. |
| **Operational Field Workforce** (Cleaners, Sweepers, Drivers, Drain Desilters) | Pending Official HR Personnel Roll | `pending_verification` / Gap | No real field workers or drivers are fabricated. Fictional operational workforce is strictly isolated in demo seeds (`is_demo = 1`). |
| **Departmental Contacts** | MCC Portal Directory | `verified_current` | Core official helpline and email verified (`+8809166666`, `info@mcc.gov.bd`). |

---

## 5. Data Cleanup Guarantee

Commands such as `demo:clear` or `demo:reset` are strictly scoped to remove only records marked `is_demo = 1` or `verification_status = 'demo_test'`. All `structural`, `verified_current`, `verified_historical`, and `pending_verification` records are permanently preserved.

