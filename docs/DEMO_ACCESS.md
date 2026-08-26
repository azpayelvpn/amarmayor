# Amar Mayor (আমার ময়মনসিংহ) — Development Demo Testing Guide

> [!IMPORTANT]
> **DEVELOPMENT & TESTING ONLY**  
> All identities, email addresses, and phone numbers in this document are **fictional demonstration records** generated exclusively by `DemoSeeder` for local manual and automated testing.  
> `DemoSeeder`, the Developer OTP Inbox (`/dev/otp-inbox`), and the Testing Access Hub (`/dev/testing-access`) are **strictly blocked and return 404 in production environments**.

---

## 1. Quick Testing Links (Local Environment)

- **Main Public Homepage:** [http://amarmayor.test:8010/](http://amarmayor.test:8010/)
- **Unified Login Page:** [http://amarmayor.test:8010/login](http://amarmayor.test:8010/login)
- **Developer OTP Verification Inbox:** [http://amarmayor.test:8010/dev/otp-inbox](http://amarmayor.test:8010/dev/otp-inbox)
- **1-Click Demo Testing Access Hub:** [http://amarmayor.test:8010/dev/testing-access](http://amarmayor.test:8010/dev/testing-access)
- **Complaint Submission Wizard:** [http://amarmayor.test:8010/complaints/create](http://amarmayor.test:8010/complaints/create)
- **Public Tracking & Resolution Confirmation:** [http://amarmayor.test:8010/track](http://amarmayor.test:8010/track)
- **Who Is Responsible? Civic Directory:** [http://amarmayor.test:8010/who-is-responsible](http://amarmayor.test:8010/who-is-responsible)

---

## 2. Complete Demo Roles & Credential Directory (All 22 Canonical Roles)

**Standard Password for all staff demo accounts:** `Demo@12345`

| # | Canonical Role | Demo Name | Login Identifier | Method / Password | Assigned Fictional Scope | Starting Route | What this Role Should See |
|---|---|---|---|---|---|---|---|
| **1** | **Citizen Demo A** | Demo Citizen A (নাগরিক ক) | `01711000001` | Phone + OTP (Code in `/dev/otp-inbox`) | Personal Citizen Portfolio | `/login` or `/complaints/create` | Complaint submission, active tracking, satisfaction confirmation / rework request |
| **2** | **Citizen Demo B** | Demo Citizen B (নাগরিক খ) | `01711000002` | Phone + OTP (Code in `/dev/otp-inbox`) | Personal Citizen Portfolio | `/login` or `/my-complaints` | Alternative citizen account for duplicate / affected testing |
| **3** | **Public Viewer** | Demo Public Viewer | `demo.public_viewer@demo.local` | `Demo@12345` | Public Scope (Read-Only) | `/who-is-responsible` | Public accountability statistics, city notices, ward directory |
| **4** | **Call Center Operator** | Demo Call Center Operator | `demo.call_center_operator@demo.local` | `Demo@12345` | Citywide Assisted Intake | `/login` $\to$ `/dashboard` | Assisted citizen phone intake, direct registration on citizen's behalf |
| **5** | **Control Room Officer** | Demo Control Room Officer | `demo.control_room_officer@demo.local` | `Demo@12345` | Central Dispatch & Emergency | `/login` $\to$ `/dashboard` | Incoming triage queue, routing gap escalation, live emergency dispatch |
| **6** | **Field Worker** | Demo Field Worker | `demo.field_worker@demo.local` | `Demo@12345` | Assigned Sanitation Team 1 | `/login` $\to$ `/dashboard` | Assigned field tasks, Start Work button, Before/After photo evidence upload, Work Completed action |
| **7** | **Team Leader** | Demo Team Leader | `demo.team_leader@demo.local` | `Demo@12345` | Sanitation Team 1 | `/login` $\to$ `/dashboard` | Team task queue, field worker assignments, equipment & site status |
| **8** | **Supervisor** | Demo Supervisor | `demo.supervisor@demo.local` | `Demo@12345` | Sanitation Team 1 / Ward 1 | `/login` $\to$ `/dashboard` | Task dispatch to field workers, on-site resolution verification, rework dispatch |
| **9** | **Ward Officer** | Demo Ward Officer | `demo.ward_officer@demo.local` | `Demo@12345` | Ward 1 Scope | `/login` $\to$ `/dashboard` | Ward 1 complaints portfolio, local citizen messages, ward operational queue |
| **10** | **Zone Officer** | Demo Zone Officer | `demo.zone_officer@demo.local` | `Demo@12345` | Zone 1 (Approved Wards: 1, 2, 4, 6, 11, 12, 27, 28, 29, 30) | `/login` $\to$ `/dashboard` | Zonal aggregated metrics, multi-ward coordination, zonal supervisor overview |
| **11** | **Department Officer** | Demo Department Officer | `demo.department_officer@demo.local` | `Demo@12345` | Waste Management Dept | `/login` $\to$ `/dashboard` | Department service units, operational tasks, material & asset tracking |
| **12** | **Department Head** | Demo Department Head | `demo.department_head@demo.local` | `Demo@12345` | Waste Management Dept | `/login` $\to$ `/dashboard` | Department-wide SLAs, overdue task queue, supervisor workload allocation |
| **13** | **General Councillor** | Demo General Councillor | `demo.general_councillor@demo.local` | `Demo@12345` | Elected Ward 1 Scope | `/login` $\to$ `/dashboard` | Ward 1 citizen grievances, local civic oversight, representative messaging |
| **14** | **Reserved Women Councillor** | Demo Reserved Women Councillor | `demo.reserved_women_councillor@demo.local` | `Demo@12345` | Reserved Seat (Unassigned / Pending Official Gazette) | `/login` $\to$ `/dashboard` | Multi-ward women representation oversight, community civic issues |
| **15** | **Responsible Officer** | Demo Responsible Officer | `demo.responsible_officer@demo.local` | `Demo@12345` | Appointed Ward 2 Scope | `/login` $\to$ `/dashboard` | Appointed ward oversight, administrative civic liaison |
| **16** | **Chief Executive Officer (CEO)** | Demo Chief Executive Officer | `demo.ceo@demo.local` | `Demo@12345` | Citywide Operational Oversight | `/login` $\to$ `/dashboard` | Operational performance across all 9 departments, overdue SLA monitoring |
| **17** | **Mayor** | Demo Mayor | `demo.mayor@demo.local` | `Demo@12345` | Citywide Executive Oversight | `/login` $\to$ `/dashboard` | Executive Command Center (6 KPIs, 24h Daily Brief, Attention Required queue, Directives) |
| **18** | **Administrator** | Demo Administrator | `demo.administrator@demo.local` | `Demo@12345` | Citywide Executive Oversight | `/login` $\to$ `/dashboard` | Executive Command Center (6 KPIs, Attention Required queue, Directives) |
| **19** | **Public Information Officer** | Demo Public Information Officer | `demo.public_info_officer@demo.local` | `Demo@12345` | Communications & Notices | `/login` $\to$ `/dashboard` | City notices publisher, public announcements, media releases |
| **20** | **Data & Monitoring Officer** | Demo Data & Monitoring Officer | `demo.data_monitoring_officer@demo.local` | `Demo@12345` | Analytics & Intelligence | `/login` $\to$ `/dashboard` | Hotspot clustering, recurring problem patterns, project required evaluation |
| **21** | **Auditor** | Demo Auditor | `demo.auditor@demo.local` | `Demo@12345` | Internal Audit Scope | `/login` $\to$ `/dashboard` | Read-only immutable audit trail, case transition logs, tenure histories |
| **22** | **Platform Super Admin** | Demo Platform Super Admin | `demo.platform_super_admin@demo.local` | `Demo@12345` | Platform Governance Scope | `/login` $\to$ `/dashboard` | Non-technical People, Areas, Governance, Services, Taxonomies, Routing Rules |
| **23** | **Technical Super Admin** | Demo Technical Super Admin | `demo.technical_super_admin@demo.local` | `Demo@12345` | Technical Infrastructure Scope | `/login` $\to$ `/dashboard` | Traffic-Light System Health, Background Queues, Backups, Diagnostics |

---

## 3. Recommended Manual Test Order (End-to-End Civic Lifecycle)

### Step 1: Citizen Submission & OTP Authentication
1. Open [http://amarmayor.test:8010/complaints/create](http://amarmayor.test:8010/complaints/create).
2. Fill out:
   - **১. কী সমস্যা?** $\to$ Select *বর্জ্য ব্যবস্থাপনা (Waste Management)* $\to$ *দৈনন্দিন বর্জ্য অপসারণ*.
   - **২. কোথায়?** $\to$ Select *ওয়ার্ড নং ১ (Ward 1)*, landmark *টাউন হল মোড়*.
   - **৩. বিবরণ** $\to$ Type a description of the issue.
   - **৪. দেখে পাঠান** $\to$ Enter Citizen Demo A phone: `01711000001`.
3. Submit and copy the generated **Tracking Number** (e.g. `MCC-2608-XXXXX`).

### Step 2: Supervisor Dispatch
1. Go to [http://amarmayor.test:8010/login](http://amarmayor.test:8010/login), select **কর্মকর্তা ও কর্মচারী** tab.
2. Log in with `demo.supervisor@demo.local` / `Demo@12345`.
3. Locate the new complaint in the Ward 1 queue and dispatch a field task to `Demo Field Worker`.

### Step 3: Field Worker Execution & Completion
1. Log in as `demo.field_worker@demo.local` / `Demo@12345`.
2. Click **কাজ শুরু করুন (Start Work)** $\to$ complaint status becomes `in_progress`.
3. Attach completion notes and click **কাজ সম্পন্ন ঘোষণা করুন (Complete Work)** $\to$ complaint status becomes `work_completed` (verification pending).

### Step 4: Supervisor Verification
1. Log in as `demo.supervisor@demo.local` / `Demo@12345`.
2. Inspect the field evidence and click **যাচাই করুন (Verify)** $\to$ complaint status becomes `awaiting_citizen_confirmation` (`confirmation_needed`).

### Step 5: Citizen Confirmation or Rework Request
1. Open [http://amarmayor.test:8010/track](http://amarmayor.test:8010/track) and enter the tracking number.
2. Test either:
   - **সমস্যা সমাধান হয়েছে (Satisfied):** Select 5-Star Rating $\to$ complaint transitions to `resolved` & `closed`.
   - **সমাধান হয়নি — আবার কাজ প্রয়োজন (Rework):** Submit a reason $\to$ case age is preserved, complaint returns to `in_progress` with `reopen_count` incremented.

### Step 6: Mayor / Administrator Oversight
1. Log in as `demo.mayor@demo.local` / `Demo@12345`.
2. Inspect the 6 High-Level KPIs, 24-hour Daily Brief, Attention Required queue, and issue binding executive directives.

### Step 7: Platform vs Technical Super Admin Separation
1. Log in as `demo.platform_super_admin@demo.local` $\to$ Verify access to People, Areas, Taxonomies, Routing Rules.
2. Log in as `demo.technical_super_admin@demo.local` $\to$ Verify access to Traffic-Light System Health, Background Queues, Backups.
