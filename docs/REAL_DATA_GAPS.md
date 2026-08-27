# Real Data Provenance & Gaps Log (REAL_DATA_GAPS.md)

> **Document Status:** Authoritative Data Integrity & Provenance Tracking  
> **Source Document:** `docs/sources/MCC_WARD_RESPONSIBILITY_2026-03-18.pdf` (Visible Document Date: 18.03.2026)  
> **Rule Reference:** AV. Hard Integrity Rules 2, 3, 4, 7, 9 & 10

---

## 1. Provenance Standard & Principles

Every verified official Mymensingh City Corporation (MCC / মসিক) Person, Employee, Governance Assignment, and Contact imported from an official public source records and preserves strict provenance metadata:
* `source_name`: Title and publisher of the official source document/attachment.
* `source_url`: Verifiable URL or official document file path.
* `source_checked_at`: Timestamp when the source was officially accessed/parsed.
* `verification_status`: Explicit classification state.
* `effective_from`: Official appointment or term start date.
* `effective_to`: Official term expiration or resignation/transfer date (`NULL` if active).
* `raw_source_title`: Original wording used in the official source (e.g. `দায়িত্বপ্রাপ্ত কাউন্সিলর`, `দায়িত্বযুক্ত কর্মকর্তা`).

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

## 3. Official Ward Responsibility Document Ingestion Audit (18.03.2026)

### 3.1 Document Metadata
* **Authoritative Source File:** `docs/sources/MCC_WARD_RESPONSIBILITY_2026-03-18.pdf`
* **Issuing Authority:** ময়মনসিংহ সিটি কর্পোরেশন, প্রশাসন বিভাগ (Mymensingh City Corporation, Administration Division)
* **Document Date Stamp:** 18.03.2026 (March 18, 2026)
* **Document Type:** 2-Page Official Scanned Administrative Notice
* **Ingestion Timestamp:** 2026-08-27 10:00:00

---

### 3.2 Page 1: Governance / Ward Representation Assignments (14 Rows, 33 Wards)
**Heading:** *“৩৩ টি ওয়ার্ড এর দায়িত্বপ্রাপ্ত কাউন্সিলরগণের নামের তালিকা”*  
**Canonical Representation Type:** `responsible_officer` (দায়িত্বপ্রাপ্ত কর্মকর্তা) — *Appointed Public Officials under City Administration, NOT democratically elected councillors*.  
**Raw Source Title Preserved:** `দায়িত্বপ্রাপ্ত কাউন্সিলর`

| Row | Official Name (নাম) | Official Designation & Department (পদবী ও দপ্তর) | Assigned Wards | Source Published Mobile | Ambiguity / Status Note |
| :---: | :--- | :--- | :---: | :---: | :--- |
| **1** | জনাব মোঃ নুরুজ্জামান | অতিরিক্ত পুলিশ সুপার (ক্রাইম ম্যানেজমেন্ট), রেঞ্জ ডিআইজির কার্যালয়, ময়মনসিংহ | 13, 14, 15 | 01320-102818 | None (Clear) |
| **2** | জনাব ডাঃ প্রদীপ কুমার সাহা | পরিচালক (স্বাস্থ্য), ময়মনসিংহ বিভাগ, ময়মনসিংহ | 7, 8, 9 | 01718-270941 | None (Clear) |
| **3** | মোঃ আশিক নূর | উপ-পরিচালক, স্থানীয় সরকার, ময়মনসিংহ | 3, 5, 10 | 01713-373335 | None (Clear) |
| **4** | জনাব শফিকুল ইসলাম | তত্ত্বাবধায়ক প্রকৌশলী, ময়মনসিংহ গণপূর্ত সার্কেল, ময়মনসিংহ | 22, 26 | 01716-747270 | None (Clear) |
| **5** | জনাব মোঃ রাশেদুল আলম | তত্ত্বাবধায়ক প্রকৌশলী, সওজ, সড়ক সার্কেল, ময়মনসিংহ | 27, 28 | 01713-782617 | None (Clear) |
| **6** | তত্ত্বাবধায়ক প্রকৌশলী (জনস্বাস্থ্য) | তত্ত্বাবধায়ক প্রকৌশলী, জনস্বাস্থ্য প্রকৌশল অধিদপ্তর ময়মনসিংহ সার্কেল, ময়মনসিংহ | 16, 17 | 01712-029174 | **নথি নোট: "বদলী"** (ব্যক্তির নাম অনুপস্থিত; পদবী অনুযায়ী দায়িত্ব সংরক্ষিত) |
| **7** | জনাব এ.কে.এম ইসমত কিবরিয়া | তত্ত্বাবধায়ক প্রকৌশলী, স্থানীয় সরকার প্রকৌশল অধিদপ্তর, ময়মনসিংহ অঞ্চল, ময়মনসিংহ | 11, 12 | 01708-123156 | **নথি নোট: "মৃত"** (নথিতে স্পষ্ট লিপিবদ্ধ) |
| **8** | জনাব এস এম ইকবাল | তত্ত্বাবধায়ক প্রকৌশলী, পরিচালন ও সংরক্ষণ সার্কেল-১, বাংলাদেশ বিদ্যুৎ উন্নয়ন বোর্ড, ময়মনসিংহ | 19, 20, 21 | 01713-850013 | None (Clear) |
| **9** | জনাব মোঃ জানে আলম | উপপরিচালক, ফায়ার সার্ভিস ও সিভিল ডিফেন্স, ময়মনসিংহ বিভাগ, ময়মনসিংহ | 4, 6 | 01715-926114 | None (Clear) |
| **10** | জনাব নাজিয়া উদ্দিন | সহকারী পরিচালক, পরিবেশ অধিদপ্তর, ময়মনসিংহ জেলা কার্যালয়, ময়মনসিংহ | 1, 2 | 01723-089233 | None (Clear; Also on Page 2) |
| **11** | জনাব মোঃ সাদেকুল ইসলাম খান | সহকারী বন সংরক্ষক, ময়মনসিংহ বন বিভাগ, ময়মনসিংহ | 23, 24, 25 | 01999-000752 | None (Clear; Also on Page 2) |
| **12** | জনাব মোহাম্মদ মহসিন মিয়া | নির্বাহী প্রকৌশলী (পুর), ময়মনসিংহ অঞ্চল, বিআইডব্লিউটিএ, ময়মনসিংহ | 18, 33 | 01712-764104 | None (Clear) |
| **13** | তাহমিনা খাতুন | ভারপ্রাপ্ত বিভাগীয় উপপরিচালক (চঃদাঃ), প্রাথমিক শিক্ষা, ময়মনসিংহ | 31, 32 | 01711-940962 | None (Clear) |
| **14** | জনাব মোহাঃ নাসির উদ্দীন | উপপরিচালক (ভারপ্রাপ্ত) মাধ্যমিক ও উচ্চ শিক্ষা, ময়মনসিংহ অঞ্চল, ময়মনসিংহ | 29, 30 | 01718-168919 | None (Clear; Also on Page 2) |

* **Page 1 Ward Coverage:** All 33 General Wards (100% covered).

---

### 3.3 Page 2: MCC Operational Ward Officers & Leave Substitutes (21 Rows, 33 Wards)
**Heading:** *“৩৩ টি ওয়ার্ডের দায়িত্বযুক্ত কর্মকর্তাগণের নামের তালিকা”*  
**Architecture:** `Person` -> `Employee` -> `employee_responsibilities` (responsibility_type: `primary`, substitute_employee_id linked).  
**Raw Source Title Preserved:** `দায়িত্বযুক্ত কর্মকর্তা`

| Row | Primary Officer Name (নাম ও পরিচিতি) | Primary Designation (পদবী) | Assigned Wards | Published Mobile | Leave Substitute Officer (ছুটিকালীন প্রতিস্থাপক) | Substitute Designation & Mobile |
| :---: | :--- | :--- | :---: | :---: | :--- | :--- |
| **1** | জনাব মোছাঃ শিরীন সুলতানা (১৭৩৫৪) | প্রধান বর্জ্য ব্যবস্থাপনা কর্মকর্তা, মসিক | 10 | 01712-444277 | জনাব মোহাম্মদ রাজীব-উল-আহসান (১৭৩৮৩) | মহাব্যবস্থাপক (পরিবহন), মসিক (০১৭১৭-১১৪৪৬৫) |
| **2** | জনাব মোহাম্মদ রাজীব-উল-আহসান (১৭৩৮৩) | মহাব্যবস্থাপক (পরিবহন), মসিক | 3 | 01717-114465 | জনাব মোছাঃ শিরীন সুলতানা (১৭৩৫৪) | প্রধান বর্জ্য ব্যবস্থাপনা কর্মকর্তা, মসিক (০১৭১২-৪৪৪২৭৭) |
| **3** | জনাব শীতেন্দু শুভ্র সরকার (১৭৪৫৪) | প্রধান সমাজকল্যাণ কর্মকর্তা, মসিক | 4 | 01318-323368 | মিস্ নাজনীন বেগম সেলু (১৮০৬০) | প্রধান রাজস্ব কর্মকর্তা, মসিক (০১৭১৩-৭১৩৭২২) |
| **4** | জনাব ফৌজিয়া নাজনীন (১৭৫৪৭) | আঞ্চলিক নির্বাহী কর্মকর্তা, অঞ্চল-৩, মসিক | 20, 25, 26 | 01832-141304 | জনাব নাহিদ হাসান খান (১৭৫৮৯) | আঞ্চলিক নির্বাহী কর্মকর্তা, অঞ্চল-০১, মসিক (০১৮৩২-১৪১৩০৪) |
| **5** | জনাব নাহিদ হাসান খান (১৭৫৮৯) | আঞ্চলিক নির্বাহী কর্মকর্তা, অঞ্চল-০১, মসিক | 7, 8, 9 | 01832-141304 | জনাব ফৌজিয়া নাজনীন (১৭৫৪৭) | আঞ্চলিক নির্বাহী কর্মকর্তা, অঞ্চল-৩, মসিক (০১৮৩২-১৪১৩০৪) |
| **6** | মিস্ নাজনীন বেগম সেলু (১৮০৬০) | প্রধান রাজস্ব কর্মকর্তা, মসিক | 17 | 01713-713722 | জনাব শীতেন্দু শুভ্র সরকার (১৭৪৫৪) | প্রধান সমাজকল্যাণ কর্মকর্তা, মসিক (০১৩১৮-৩২৩৩৬৮) |
| **7** | জনাব এস এম সিরাজুল ইসলাম (২০৩৮৫) | বিজ্ঞ এক্সিকিউটিভ ম্যাজিস্ট্রেট, মসিক | 11, 12 | 01706-041118 | জনাব জাকির হোসাইন (১৮০৪৮) | বিজ্ঞ এক্সিকিউটিভ ম্যাজিস্ট্রেট, মসিক (০১৭১৭-০৫২৭০৪) |
| **8** | জনাব জাকির হোসাইন (১৮০৪৮) | বিজ্ঞ এক্সিকিউটিভ ম্যাজিস্ট্রেট, মসিক | 18 | 01717-052704 | জনাব মোহাঃ নাসির উদ্দীন | উপপরিচালক (ভারপ্রাপ্ত), মাধ্যমিক ও উচ্চ শিক্ষা (০১৭১১-০৫১২২৪) |
| **9** | জনাব মোহাঃ নাসির উদ্দীন | উপপরিচালক (ভারপ্রাপ্ত), মাধ্যমিক ও উচ্চ শিক্ষা | 29, 30 | 01711-051224 | জনাব এস এম সিরাজুল ইসলাম (২০৩৮৫) | বিজ্ঞ এক্সিকিউটিভ ম্যাজিস্ট্রেট, মসিক (০১৭০৬-০৪১১১৮) |
| **10** | জনাব নাজিয়া উদ্দিন | সহকারী পরিচালক, পরিবেশ অধিদপ্তর | 1, 2 | 01723-089233 | জনাব মোঃ সাদেকুল ইসলাম খান | সহকারী বন সংরক্ষক, বন বিভাগ (০১৯৯৯-০০০৭৫২) |
| **11** | জনাব মোঃ সাদেকুল ইসলাম খান | সহকারী বন সংরক্ষক, বন বিভাগ | 23, 24 | 01999-000752 | জনাব নাজিয়া উদ্দিন | সহকারী পরিচালক, পরিবেশ অধিদপ্তর (০১৭২৩-০৮৯২৩৩) |
| **12** | জনাব ডাঃ এইচ কে দেবনাথ | প্রধান স্বাস্থ্য কর্মকর্তা (চঃদাঃ), মসিক | 21 | 01711-186207 | জনাব অসীম কুমার সাহা | প্রধান হিসাবরক্ষণ কর্মকর্তা (চঃদাঃ), মসিক (০১৭১১-০৭২৫৬৬) |
| **13** | জনাব অসীম কুমার সাহা | প্রধান হিসাবরক্ষণ কর্মকর্তা (চঃদাঃ), মসিক | 13 | 01711-072566 | জনাব ডাঃ এইচ কে দেবনাথ | প্রধান স্বাস্থ্য কর্মকর্তা (চঃদাঃ), মসিক (০১৭১১-১৮৬২০৭) |
| **14** | জনাব মোঃ জহুরুল হক | তত্ত্বাবধায়ক প্রকৌশলী (সিভিল) (চঃদাঃ), মসিক | 14, 15 | 01711-446018 | জনাব মানস বিশ্বাস | নগর পরিকল্পনাবিদ, মসিক (০১৭১২-২৮১৮০১) |
| **15** | জনাব মানস বিশ্বাস | নগর পরিকল্পনাবিদ, মসিক | 22 | 01712-281801 | জনাব মোঃ জহুরুল হক | তত্ত্বাবধায়ক প্রকৌশলী (সিভিল) (চঃদাঃ), মসিক (০১৭১১-৪৪৬০১৮) |
| **16** | জনাব মোঃ জিল্লুর রহমান | নির্বাহী প্রকৌশলী (বিদ্যুৎ) (চঃদাঃ), মসিক | 5, 6 | 01711-478452 | জনাব মোঃ আবুল কালাম আজাদ | হিসাবরক্ষণ কর্মকর্তা, মসিক (০১৭১২-২৮৭৯৪২) |
| **17** | জনাব মোঃ আবুল কালাম আজাদ | হিসাবরক্ষণ কর্মকর্তা, মসিক | 27, 28 | 01712-287942 | জনাব মোঃ জিল্লুর রহমান | নির্বাহী প্রকৌশলী (বিদ্যুৎ) (চঃদাঃ), মসিক (০১৭১১-৪৭৮৪৫২) |
| **18** | জনাব উম্মে হালিমা | সমাজকল্যাণ কর্মকর্তা, মসিক | 31, 32 | 01916-220015 | জনাব মোঃ মামুন-অর-রশিদ | নির্বাহী প্রকৌশলী (পানি) (চঃদাঃ), মসিক (০১৭১২-১৪১১৯৭) |
| **19** | জনাব মোঃ মামুন-অর-রশিদ | নির্বাহী প্রকৌশলী (পানি) (চঃদাঃ), মসিক | 16 | 01712-141197 | জনাব উম্মে হালিমা | সমাজকল্যাণ কর্মকর্তা, মসিক (০১৯১৬-২২০০১৫) |
| **20** | জনাব মুহাম্মদ আযহারুল হক | নির্বাহী প্রকৌশলী (সিভিল) (চঃদাঃ), মসিক | 33 | 01711-105999 | জনাব মোঃ জসিম উদ্দিন | নির্বাহী প্রকৌশলী (সিভিল) (চঃদাঃ), মসিক (০১৭১২-১০৩৫১২) |
| **21** | জনাব মোঃ জসিম উদ্দিন | নির্বাহী প্রকৌশলী (সিভিল) (চঃদাঃ), মসিক | 19 | 01712-103512 | জনাব মুহাম্মদ আযহারুল হক | নির্বাহী প্রকৌশলী (সিভিল) (চঃদাঃ), মসিক (০১৭১১-১০৫৯৯৯) |

* **Page 2 Ward Coverage:** All 33 General Wards (100% covered).

---

## 4. Current Identified Real Data Gaps & Verified Baseline Summary

| Scope / Domain | Status | Provenance & Handling Policy |
| :--- | :--- | :--- |
| **1. MCC Administrative Leadership** | `verified_current` | Fully imported from official MCC Portal directory (`https://mcc.gov.bd`). `user_id = NULL`. |
| **2. Ward Governance Assignments (1–33)** | `verified_current` | 14 rows from `MCC_WARD_RESPONSIBILITY_2026-03-18.pdf` Page 1. Appointed Responsible Officers (`responsible_officer`). |
| **3. Ward Operational Officers (1–33)** | `verified_current` | 21 rows from `MCC_WARD_RESPONSIBILITY_2026-03-18.pdf` Page 2 with primary and substitute designations. |
| **4. Leave-Time Substitutes** | `verified_current` | Modelled as linked `substitute_employee_id` in `employee_responsibilities` with individual mobile numbers. |
| **5. Unreadable / Ambiguous Rows** | Documented | Row 6 (DPHE Superintending Engineer: name absent, marked "বদলী"); Row 7 (LGED Superintending Engineer: marked "মৃত"). |
| **6. Reserved Women Councillors (Seats 1–11)** | `structural` / `pending_verification` | 11 reserved seats configured structurally without arithmetic Ward clustering assumptions. Awaiting official gazette. |
| **7. Field Operational Workforce Roster** | `pending_verification` / Gap | No real cleaners, drivers, or sweepers are fabricated. Isolated in demo environment (`is_demo = 1`). |
| **8. GIS Spatial Boundaries** | `structural` / Gap | Approximate Ward boundaries present. Precise GIS shapefiles/polygon coordinates pending official release. |
| **9. Official Helpline vs Officer Contacts** | `verified_current` | City helpline (`+8809166666`) restricted to citywide helpline only; all Ward officer contacts use source-published mobiles. |

---

## 5. Data Cleanup Guarantee

Commands such as `demo:clear` or `demo:reset` are strictly scoped to remove only records marked `is_demo = 1` or `verification_status = 'demo_test'`. All `structural`, `verified_current`, `verified_historical`, and `pending_verification` records are permanently preserved.
