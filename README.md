# আমার ময়মনসিংহ (My Mymensingh)
### Mymensingh City Corporation Citizen Service & Grievance Redress Platform
**ময়মনসিংহ সিটি কর্পোরেশন (মসিক)**

---

## 📌 Project Overview
**আমার ময়মনসিংহ (My Mymensingh)** is a modern, institution-first digital civic service, complaint tracking, field operations, and public accountability platform built specifically for **Mymensingh City Corporation (MCC / মসিক)**, Bangladesh.

---

## 🏛️ Core Architecture & Tech Stack
* **Backend:** PHP 8.2+ (Core PHP, Object-Oriented, MVC Modular Monolith, PDO Prepared Statements).
* **Database:** MySQL 8.0+ (InnoDB) as the sole durable source of truth.
* **Fast Storage & Locks:** Redis for sessions, rate limits, distributed locks, and temporary performance caches (disposable & rebuildable).
* **Web Frontend:** Server-rendered HTML5, Bootstrap 5, Bootstrap Icons, HTMX (for asynchronous partial updates), Vanilla JavaScript.
* **Mobile (Upcoming Phase 20):** Native Android and iOS built from a single Flutter & Dart codebase.
* **Language Priority:** **Bangla (বাংলা)** is the primary/default language; **English** is secondary.

---

## 🚀 Local Development Setup (Windows / Laragon)

### 1. Requirements
* Windows 10/11 with **Laragon** (or PHP 8.2+, MySQL 8+, Composer).
* PHP extensions: `pdo_mysql`, `mbstring`, `json`, `openssl`, `fileinfo`. (Optional: `phpredis`).

### 2. Quickstart Commands
```bash
# 1. Navigate to project root
cd C:\laragon\www\amarmayor

# 2. Copy environment template if not already present
cp backend/.env.example backend/.env

# 3. Generate Composer autoloader
composer dump-autoload -d backend

# 4. Check system connectivity & health
php backend/bin/console health

# 5. Run test suite
php backend/tests/run_tests.php

# 6. Start local development server (or access via Laragon virtual host)
php backend/bin/console serve 8000
```
Open [http://localhost:8000](http://localhost:8000) in your browser.

---

## 🛠️ CLI Administrative Console
The platform provides a lightweight command entrypoint at `backend/bin/console`:
* `php backend/bin/console health`: Check MySQL, Redis, and configuration status.
* `php backend/bin/console migrate:status`: View applied and pending database migrations.
* `php backend/bin/console migrate`: Run pending database migrations.
* `php backend/bin/console migrate:rollback`: Rollback the latest migration batch.
* `php backend/bin/console make:migration <name>`: Create a new migration file.
* `php backend/bin/console serve [port]`: Launch PHP built-in web server.

---

## 📚 Architectural Documentation
Complete specifications and models are maintained in `docs/`:
* [docs/MASTER_SPEC.md](docs/MASTER_SPEC.md): Complete 723-section master specification.
* [docs/PROJECT_CONSTITUTION.md](docs/PROJECT_CONSTITUTION.md): Non-negotiable core architectural laws.
* [docs/DATABASE_ERD.md](docs/DATABASE_ERD.md): 35+ entity relational schema and Mermaid ERD.
* [docs/ROLE_PERMISSION_MATRIX.md](docs/ROLE_PERMISSION_MATRIX.md): 22-role RBAC and granular scope matrix.
* [docs/GOVERNANCE_MODEL.md](docs/GOVERNANCE_MODEL.md): Mayor, Administrator, CEO, and Ward representation model.
* [docs/WORKFORCE_MODEL.md](docs/WORKFORCE_MODEL.md): Municipal workforce directory, teams, and postings.
* [docs/COMPLAINT_STATE_MACHINE.md](docs/COMPLAINT_STATE_MACHINE.md): 17 operational states and 7 citizen statuses.
* [docs/ROUTING_MODEL.md](docs/ROUTING_MODEL.md): Multi-tier deterministic routing engine.
* [docs/SLA_OVERDUE_REOPEN_MODEL.md](docs/SLA_OVERDUE_REOPEN_MODEL.md): Deadlines, 1st failure attention, and anti-gaming.
* [docs/SECURITY_MODEL.md](docs/SECURITY_MODEL.md): Authentication, encryption, IDOR, and audit trails.
* [docs/UX_RULES.md](docs/UX_RULES.md): Interface simplicity, persona guidelines, and non-technical admin rules.
* [docs/API_SPEC.md](docs/API_SPEC.md): Full REST API v1 contract and machine error codes.
* [docs/DECISIONS.md](docs/DECISIONS.md): Architectural Decision Records (ADR-0001 to ADR-0007).
* [docs/IMPLEMENTATION_STATUS.md](docs/IMPLEMENTATION_STATUS.md): Milestone progress tracker.

---

## 📋 Current Implementation Status
* **Phase 0 (Specification Synthesis):** ✅ Complete.
* **Phase 1 (Core Backend Foundation):** ✅ Complete (Bootstrap, Router, PDO, Redis adapter, Request/Response envelopes, Validator, Bilingual Translator, View engine, Security helpers, Migration CLI, Test suite).
* **Next Milestone:** Phase 2 — Database Foundation (Core Schema Migrations & Structural Seeds).
