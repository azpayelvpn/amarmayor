# Project Constitution — Mymensingh City Citizen Service Platform

## Core Principles & Governance Rules

### 1. Purpose & Civic Mission
The platform exists to serve the residents of Mymensingh City Corporation (MCC) through rapid grievance resolution, civic transparency, and seamless governance. Every architectural decision must prioritize ease of access for everyday citizens, robust field officer workflows, and accountable governance for leadership.

### 2. Language Priority (Bangla-First)
* **Primary Language:** **Bangla (বাংলা)** is the primary language. All citizen-facing interfaces, forms, notifications, SMS alerts, and public reports must be native, natural, and accurately translated in Bangla.
* **Secondary Language:** **English** is available as a toggle and secondary administrative fallback.
* All error messages, confirmation dialogues, and field tooltips must provide first-class Bangla localization.

### 3. Isolation & Independence
* This project is strictly isolated inside `C:\laragon\www\amarmayor`.
* Under no circumstances shall this codebase depend on, copy from, or link to external unrelated projects (including `C:\laragon\www\ahospital`).

### 4. Administrative Hierarchy & Data Governance
* **Mayor / City Administrator:** Top-level macro monitoring, escalations, ward performance ratings, and strategic resource allocation.
* **Ward Councillors & Ward Officers:** Operational oversight for specific wards, complaint validation, and field worker dispatch.
* **Field Officers & Workforce:** Ground execution, before/after photo verification, geo-tagged resolution logs.
* **Citizens:** Anonymous or authenticated submission, live tracking, feedback ratings upon resolution.

### 5. Architectural Integrity
* Clear separation of concerns between `backend/`, `mobile/`, `infra/`, `scripts/`, and `docs/`.
* Secure, scalable, and audit-friendly data modeling with timestamped status transitions for all complaints.
* Offline-first or low-bandwidth considerations for mobile field operations.
