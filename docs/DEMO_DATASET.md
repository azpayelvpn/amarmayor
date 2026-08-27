# Realistic Demo Dataset Distribution & Metrics (ডেমো ডেটাসেট পরিসংখ্যান)

> [!WARNING]
> **DEVELOPMENT & TESTING DISCLAIMER:**  
> This dataset contains purely fictional demonstration entities generated exclusively for development, user testing, training, and operational simulations. Fictional demo accounts, fake citizen phone numbers (`01711000001` - `01711000099`), and demo workforce records are tagged with `is_demo = 1` and `verification_status = 'demo_test'`. They are strictly prohibited from production environments.

---

## ১. সার্বিক পরিসংখ্যান (Overall Metrics)

| সূচক (Metric) | পরিমাণ (Quantity) | বর্ণনা (Description) |
| :--- | :--- | :--- |
| **মোট ডেমো অভিযোগ (Total Complaints)** | **642** | বিগত ৯০ দিনের বাস্তবসম্মত জীবনচক্র ও টাইমলাইনভিত্তিক অভিযোগ |
| **অন্তর্ভুক্ত ওয়ার্ড (Wards Covered)** | **33 / 33 (100%)** | ময়মনসিংহ সিটি কর্পোরেশনের সকল ৩৩টি ওয়ার্ডে সুষম বণ্টন |
| **অন্তর্ভুক্ত অঞ্চল (Zones Covered)** | **3 / 3 (100%)** | অঞ্চল ০১, অঞ্চল ০২ এবং অঞ্চল ০৩ |
| **সমস্যার খাত (Categories Covered)** | **12 / 12 (100%)** | বর্জ্য, ড্রেনেজ, সড়কবাতি, মশক নিধন, সড়ক সংস্কার ইত্যাদি |
| **ডেমো নাগরিক অ্যাকাউন্ট (Demo Citizens)** | **42** | বৈচিত্র্যময় নাম, আবাসিক ওয়ার্ড ও ওটিপি যাচাইকৃত মোবাইল প্রোফাইল |
| **ক্যানোনিকাল ডেমো ভূমিকা (Demo Canonical Roles)** | **21** | ২২টি ক্যানোনিকাল সিস্টেম ভূমিকার জন্য প্রস্তুত ডেমো লগইন |
| **যাচাইকৃত অফিশিয়াল এমসিসি কর্মকর্তা (Verified Officers)** | **98** | গেজেট ও অফিশিয়াল উৎস অনুযায়ী কর্মকর্তা বেসলাইন (`user_id = NULL`) |
| **সক্রিয় ডেমো ফিল্ড টিম (Demo Teams)** | **6** | পরিচ্ছন্নতা, ড্রেন পরিষ্কার, সড়কবাতি, মশক স্প্রে ও কুইক একশন দল |

---

## ২. অভিযোগের জীবনচক্র বণ্টন (Complaint Status Distribution)

| স্ট্যাটাস (Status) | সংখ্যা (Count) | শতকরা হার (%) | বিবরণ |
| :--- | :--- | :--- | :--- |
| `in_progress` (কাজ চলছে) | 122 | 19.0% | মাঠকর্মী দ্বারা সক্রিয়ভাবে কাজ চলমান |
| `resolved` (সমাধান হয়েছে) | 109 | 17.0% | নাগরিক কর্তৃক চূড়ান্ত সন্তুষ্টি যাচাইকৃত |
| `assigned` (দায়িত্ব প্রাপ্ত) | 102 | 15.9% | সুপারভাইজার ও টিম নির্ধারিত |
| `work_completed` (কাজ সম্পন্ন) | 91 | 14.2% | মাঠের কাজ শেষ, সুপারভাইজার যাচাই অপেক্ষমান |
| `supervisor_verified` (নাগরিক নিশ্চিতকরণ) | 72 | 11.2% | সিটিজেনের অনুমোদন অপেক্ষমান (`confirmation_needed`) |
| `submitted` (নতুন দাখিলকৃত) | 64 | 10.0% | নতুন অভিযোগ, প্রাথমিক ট্রায়াজে রয়েছে |
| `needs_more_work` (পুনরায় কাজ প্রয়োজন) | 37 | 5.8% | নাগরিক কর্তৃক অসন্তোষজনক কাজের কারণে রিওপেন |
| `cancelled` (বাতিল / অকার্যকর) | 24 | 3.7% | সদৃশ বা এখতিয়ার বহির্ভূত কারণে বাতিল |
| `review_required` (পুনর্বিবেচনা) | 21 | 3.2% | বিশেষ কারিগরি মূল্যায়ন প্রয়োজন |

---

## ৩. বিশেষ পরিস্থিতি ও এসএলএ বিশ্লেষণ (Special Cases & SLA Metrics)

| বিশেষ ক্ষেত্র (Special Case) | সংখ্যা (Count) | প্রভাব ও আচরণ (System Behavior) |
| :--- | :--- | :--- |
| **এসএলএ ডেডলাইন অতিক্রান্ত (Overdue SLA)** | **69** | নির্ধারিত সময়সীমা পার হওয়ার কারণে স্বয়ংক্রিয়ভাবে মেয়রের **Executive Attention Queue** তে স্থানান্তর। মাঠ দলের দায়িত্ব বহাল থাকে। |
| **নাগরিক রিওপেন (Reopened by Citizen)** | **49** | প্রথম রিওপেনেই এক্সিকিউটিভ দৃষ্টি আকর্ষণ কিউতে নথিভুক্ত (`reopened_unresolved`), মূল অভিযোগের বয়স ও ইতিহাস সংরক্ষিত। |
| **নিয়মিত হটস্পট (Recurring Hotspots)** | **47** | একই স্থানে বারবার ঘটা সমস্যা (যেমন: গাঙ্গিনার পাড় ড্রেন, বড় বাজার ডাস্টবিন)। |

---

## ৪. সিএলআই কমান্ড ও ডেটা পরিচালনা (Demo CLI Commands)

* **ডেমো ডেটা পপুলেট করা:**
  ```bash
  php backend/bin/console demo:seed
  ```
* **শুধুমাত্র ডেমো ডেটা মুছে ফেলা (ড্রাই-রান মোড):**
  ```bash
  php backend/bin/console demo:clear --dry-run
  ```
* **শুধুমাত্র ডেমো ডেটা অপসারণ (স্ট্রাকচারাল ও ভেরিফাইড অপরিবর্তিত রেখে):**
  ```bash
  php backend/bin/console demo:clear
  ```
* **সম্পূর্ণ ডেমো ডেটাসেট রিসেট করা:**
  ```bash
  php backend/bin/console demo:reset
  ```
