# Architectural Decision Records (ADR) — Mymensingh City Citizen Service Platform

This document records all significant architectural, technological, and design decisions made throughout the lifecycle of the project.

---

## ADR-0001: Project Foundation and Repository Setup

* **Date:** 2026-08-26
* **Status:** Accepted

### Context
Initializing a greenfield civic management and complaint tracking platform for Mymensingh City Corporation (*Amar Mayor*), covering web, mobile (Android/iOS), back-office administration, and public analytics.

### Decision
1. Establish a modular directory structure:
   * `docs/`: Central repository of project specifications, ADRs, constitutions, and progress tracking.
   * `backend/`: Core REST/GraphQL APIs, business logic, background queues, and database migrations.
   * `mobile/`: Cross-platform mobile clients (Android & iOS) for citizens and field officers.
   * `infra/`: Infrastructure as Code (Docker, Nginx configs, deployment templates).
   * `scripts/`: Development, build, data seeding, and deployment helper scripts.
2. Adopt a Bangla-first localization strategy across all civic endpoints and interfaces.
3. Keep strict repository isolation within `C:\laragon\www\amarmayor`.

### Consequences
* Provides a clean foundation ready for full specification definition and modular implementation without coupling to external projects.
