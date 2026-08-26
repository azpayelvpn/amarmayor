# Project Constitution — Mymensingh City Citizen Service Platform (আমার ময়মনসিংহ / My Mymensingh)

> **Document Status:** Authoritative Architectural Foundation  
> **Source of Truth:** Derived strictly from [docs/MASTER_SPEC.md](file:///C:/laragon/www/amarmayor/docs/MASTER_SPEC.md) (Sections 1–723).

---

## 1. Core Principles & Civic Mission
1. **Primary Philosophy:** Technical complexity may exist internally, but it must **never** become user-interface complexity. Every screen must answer the user’s single core question in plain human language.
2. **Civic Mission:** Bridge citizens and Mymensingh City Corporation (MCC / মসিক) for transparent complaint resolution, municipal operations, workforce coordination, and public accountability.
3. **Institution-First Integrity:** The public platform is institution-first (Mymensingh City Corporation). It is not a platform for personal political promotion or partisan campaigns. Factual attribution of legitimate official actions (directives, notices, programs) is permitted when backed by auditable system records.

---

## 2. Language Priority (Bangla-First Architecture)
1. **Bangla (বাংলা) is Primary:** Default language for all citizen portals, admin dashboards, mobile apps, notifications, validation messages, SMS alerts, and public reports.
2. **English is Secondary:** Standard secondary fallback and internationalization toggle (`বাংলা | EN`).
3. **Language Neutrality in Storage:** Canonical database values and state codes must remain language-neutral (e.g., `status = 'in_progress'`), with presentation layer resolving translations via centralized resource files (`backend/lang/bn/`, `backend/lang/en/`).
4. **Original UGC Preservation:** Citizen complaints and worker field notes must remain preserved in their original submitted text.

---

## 3. Strict Project Isolation
1. **Location:** The project resides strictly inside `C:\laragon\www\amarmayor`.
2. **Absolute Independence:** Under no circumstances shall this codebase depend on, copy from, or link to `C:\laragon\www\ahospital` or any other external project.

---

## 4. Technology Stack & Architectural Constraints
1. **Backend:** PHP 8.2+ Core PHP (Object-Oriented, MVC-style architecture, PDO). No full-stack frameworks (No Laravel, Symfony full-stack, CodeIgniter, Django, Node.js backend).
2. **Database:** MySQL 8+ is the sole durable source of truth. All civic records, history, and audit trails must be safely persisted in MySQL with transactions, foreign keys, and constraints.
3. **Cache & Coordination:** Redis is used for sessions, cache, rate limits, locks, and temporary speedups. **Redis is NEVER the permanent source of truth.** The platform must recover operational state from MySQL if Redis is flushed.
4. **Web Frontend:** Server-side rendered first. HTML5, CSS3, Bootstrap 5, Bootstrap Icons, HTMX (for partial updates/filters), Vanilla JavaScript (for maps/widgets), Fetch API, Chart.js. No frontend SPAs (No React, Vue, Angular, Next.js).
5. **Mobile:** Flutter + Dart producing Android and iOS from a single codebase. Native mobile capabilities (GPS, camera, secure storage, push notifications). **Mobile is NOT a WebView.**
6. **Unified Business Rules:** Web, Android, and iOS clients share the exact same backend domain services and API rules (`/api/v1/`).

---

## 5. Governance & Representation Rules
1. **Mayor vs Administrator:** Mayor (elected) and Administrator (appointed) are distinct governance types and roles. The public UI must accurately reflect the active governance position.
2. **Flexible Representation:**
   * General Wards may have an Elected Councillor or an appointed **Responsible Officer** (who may cover one or multiple Wards).
   * Reserved Women Councillors cover a configurable cluster of 3 General Wards.
   * Dual representation (General + Reserved) is displayed on Ward public profiles.
3. **Time-Bound & Auditable:** All governance and operational assignments use effective dates (`effective_from`, `effective_to`) and maintain permanent, immutable history.
4. **Separation of Powers:** Representation/governance users (Councillors/Officers) monitor and follow up complaints but cannot mark field work complete or overwrite technical evidence.

---

## 6. Complaint Lifecycle, Routing & Immediate Executive Oversight
1. **Citizen Simplicity:** Complaint submission in ~1 minute across 3–4 simple screens without choosing departments, officers, or SLA terms.
2. **Deterministic Routing:** Automatic routing based on category, subcategory, geography (Ward/Zone), and active responsible supervisor/team without hardcoded user IDs.
3. **Accountable Ownership:** Every active complaint has a single clear accountable unit/supervisor regardless of the field team executing the task.
4. **Immediate Executive Visibility (Non-Negotiable):**
   * **1st Missed Service Deadline** $\rightarrow$ Immediately visible in Mayor/Admin *Attention Required*.
   * **1st Citizen "Not Resolved" (Needs More Work)** $\rightarrow$ Immediately visible in Mayor/Admin *Attention Required*.
   * **No Multi-Level Escalation Ladders:** No bureaucratic Level 1/2/3/4 forwarding chains. Operational ownership remains with the assigned unit while executive oversight is established immediately.
5. **Immutable Case History:** Case age and original deadline performance **never reset** upon reopen. All completion attempts and evidence remain historically preserved.
6. **Anti-Gaming Protections:** Prevent premature completions, unauthorized transfers, or category manipulation to manipulate deadlines.

---

## 7. Security, Privacy & Audit Standards
1. **Access Model:** Role + Permission + Scope with mandatory server-side authorization on every request. Direct Object Reference (IDOR) protections enforced on all resources.
2. **Citizen Authentication:** Phone Number + OTP with Redis rate limiting and secure trusted session management. No NID/email required for standard complaints.
3. **Staff Security:** Argon2id password hashing, session regeneration, secure revocable mobile tokens, and privileged MFA (Mayor, Admin, CEO, Super Admins).
4. **Data Privacy (Hard Requirement):** Citizen PII (phone, email, NID, exact private residential coordinates) and unapproved raw evidence are **never** exposed publicly.
5. **Append-Oriented Audit Trail:** All civic lifecycle events, administrative adjustments, executive directives, and governance changes are immutably logged with actor, timestamp, previous/new values, and request IDs.

---

## 8. Development & Production Standards
1. **Local Environment:** Windows + Laragon (`C:\laragon\www\amarmayor`) using PHP 8.2+, MySQL 8+, Redis.
2. **Production Target:** Linux + Nginx + PHP-FPM + OPcache + MySQL 8+ + Redis + HTTPS + systemd background workers + scheduled cron jobs.
3. **No AI Dependency:** The core civic platform functions 100% deterministically without requiring AI models.
4. **Testing Gate:** No module is considered complete without automated tests (Unit, Feature, Authorization/Scope, Security, Idempotency, and Load testing).
