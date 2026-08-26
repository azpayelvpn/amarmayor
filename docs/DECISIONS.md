# Architectural Decision Records (ADR) — Mymensingh City Citizen Service Platform

> **Document Status:** Authoritative Architectural Decisions Log  
> **Source of Truth:** [docs/MASTER_SPEC.md](file:///C:/laragon/www/amarmayor/docs/MASTER_SPEC.md)

---

## ADR-0001: Project Foundation and Repository Isolation
* **Date:** 2026-08-26
* **Status:** Accepted
* **Context:** Initializing a greenfield civic management platform for Mymensingh City Corporation (*আমার ময়মনসিংহ / My Mymensingh*).
* **Decision:** Establish modular directories (`docs/`, `backend/`, `mobile/`, `infra/`, `scripts/`) strictly isolated in `C:\laragon\www\amarmayor` with zero dependency on external projects.
* **Consequences:** Clear separation of concerns, absolute independence.

---

## ADR-0002: Technology Stack Selection
* **Date:** 2026-08-26
* **Status:** Accepted
* **Context:** Need a maintainable, high-performance, robust, and transparent tech stack suitable for local government deployment in Bangladesh.
* **Decision:**
  * **Backend:** Core PHP 8.2+ (OOP, MVC architecture, PDO). No heavy full-stack frameworks.
  * **Database:** MySQL 8+ (InnoDB) as sole durable source of truth.
  * **Cache & Locks:** Redis for sessions, rate limits, distributed locks, and temporary fast cache. Redis is non-durable.
  * **Web Frontend:** Server-rendered HTML5, Bootstrap 5, Bootstrap Icons, HTMX (for fast partial DOM swaps), Vanilla JS. No SPA frameworks.
  * **Mobile:** Flutter & Dart producing native Android and iOS from a single codebase.
* **Consequences:** Ultra-fast page loads, simple server maintenance, high concurrency, zero framework obsolescence risk.

---

## ADR-0003: Complaint State Machine & Anti-Gaming Model
* **Date:** 2026-08-26
* **Status:** Accepted
* **Context:** Complaint tracking requires operational rigor while presenting a friendly, simple interface to citizens.
* **Decision:** Implement a centralized state machine with 17 operational internal states mapped to 7 citizen presentation statuses. Case age and SLA metrics remain anchored to original `submitted_at` and **never reset** on reopen.
* **Consequences:** Eliminates administrative gaming, guarantees auditability, and provides reassuring citizen clarity.

---

## ADR-0004: Role, Permission, and Scope Authorization Model
* **Date:** 2026-08-26
* **Status:** Accepted
* **Context:** Municipal staff have varying responsibilities across departments, zones, and wards. Hiding UI elements is insufficient.
* **Decision:** Enforce `User -> Role -> Permission -> Scope` authorization with mandatory server-side enforcement and IDOR protection on every API/HTMX endpoint.
* **Consequences:** Robust security, eliminates unauthorized cross-ward/cross-department data tampering.

---

## ADR-0005: Decoupled Governance & Dual Ward Representation
* **Date:** 2026-08-26
* **Status:** Accepted
* **Context:** MCC requires the ability to represent both elected Mayors and appointed Administrators, as well as General Councillors, Responsible Officers, and Reserved Women Councillors covering 3 Wards.
* **Decision:** Create effective-dated `representation_assignments` and `reserved_seat_wards` mapping tables. Public Ward profiles display both General and Reserved representatives.
* **Consequences:** Seamless administrative transitions without schema changes or data corruption.

---

## ADR-0006: Immediate Executive Oversight vs Escalation Ladders
* **Date:** 2026-08-26
* **Status:** Accepted
* **Context:** Traditional escalation ladders (Level 1 $\rightarrow$ 2 $\rightarrow$ 3 $\rightarrow$ 4) create bureaucratic delays and ownership ambiguity.
* **Decision:** Strictly forbid multi-level escalation ladders. The 1st missed deadline and 1st citizen reopen immediately enter Mayor / Administrator *Attention Required*. Operational ownership remains with the assigned unit.
* **Consequences:** Immediate executive visibility, no diffused accountability.

---

## ADR-0007: Privacy by Design & Coarse Public Geodata
* **Date:** 2026-08-26
* **Status:** Accepted
* **Context:** Public accountability requires transparency without exposing citizen PII or precise doorstep residential GPS coordinates.
* **Decision:** Obfuscate public map coordinates to ~100m neighborhood centroids (`public_latitude`, `public_longitude`), hide citizen phone/email, and require explicit moderation for public photo evidence derivatives.
* **Consequences:** Total citizen privacy protection compliant with government data governance standards.
