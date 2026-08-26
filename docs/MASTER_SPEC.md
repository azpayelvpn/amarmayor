# Mymensingh City Citizen Service Platform — Master Specification (MASTER_SPEC.md)

---

# SPECIFICATION PART 1

## Product Vision, Architecture, Technology Stack, Language and Core UX Principles

This is **Specification Part 1** of the complete master specification for the Mymensingh City Citizen Service Platform.

---

# 1. PROJECT IDENTITY

Working Bengali product name:

**আমার ময়মনসিংহ**

Working English product name:

**My Mymensingh**

The product name and branding must remain configurable.

Do not deeply hardcode the project name throughout business logic, templates, APIs, notifications, or mobile applications.

The system is initially being designed for:

**Mymensingh City Corporation, Bangladesh**

However, the software architecture should avoid unnecessary hardcoding that would make legitimate future administrative changes difficult.

---

# 2. PRODUCT VISION

Build a complete digital civic service, citizen complaint, city operations, workforce coordination, administrative monitoring, public accountability, and citizen-government communication platform.

This is NOT merely a complaint submission website.

The complete platform will eventually connect:

```text
Citizen
   ↓
Problem / Complaint
   ↓
Location
   ↓
Ward / Zone
   ↓
Responsible Service / Department
   ↓
Responsible Supervisor / Unit
   ↓
Field Team / Employee
   ↓
Work
   ↓
Evidence
   ↓
Verification
   ↓
Citizen Confirmation
   ↓
Resolution
   ↓
Public Statistics
   ↓
Administrative Monitoring
   ↓
Mayor / Administrator Oversight
```

The system must create accountability without creating unnecessary bureaucracy.

---

# 3. PRIMARY SYSTEM PHILOSOPHY

The most important design rule is:

> Technical complexity may exist internally, but it must never become user-interface complexity.

The software must feel simple even if the backend is sophisticated.

Every major user should immediately understand one central question.

Citizen:

> আমার সমস্যার কী হলো?
> What happened to my problem?

Field Worker:

> এখন আমাকে কোন কাজটি করতে হবে?
> What work do I need to do now?

Supervisor:

> কোন কাজটি এখন করতে হবে এবং কাকে দায়িত্ব দেব?
> What needs attention and who should do it?

Ward / Zone / Department Officer:

> আমার দায়িত্বের মধ্যে কোথায় কাজ আটকে আছে?
> What is blocked within my responsibility?

Mayor / Administrator:

> শহরের কোথায় আমার নজর বা সিদ্ধান্ত প্রয়োজন?
> Where does the city need my attention or decision?

Platform Administrator:

> লোকজন, দায়িত্ব, ওয়ার্ড, সেবা এবং নিয়মের মধ্যে কী পরিচালনা করতে হবে?
> What people, responsibilities, areas, services, or rules need management?

Technical Administrator:

> সিস্টেম ঠিকমতো চলছে কি?
> Is the system working properly?

If a screen does not help its user answer the relevant question, simplify the screen.

---

# 4. SIMPLICITY IS A HARD REQUIREMENT

Do NOT build a complicated government ERP-style interface.

Do NOT expose administrative or technical complexity merely because it exists internally.

Avoid interfaces filled with:

```text
Dozens of menus
Large configuration tables
Technical IDs
Database terminology
Infrastructure terminology
Complex permission codes
Unnecessary workflow states
Unnecessary approval chains
Long forms
Unnecessary dropdowns
```

The normal user experience should rely on:

```text
Clear language
Large primary actions
Simple status
Short forms
Guided setup
Automatic routing
Smart defaults
Minimal required input
Progressive disclosure
```

Advanced details should appear only when someone explicitly opens them.

---

# 5. USER EXPERIENCE TARGETS

These are design targets, not artificial limitations that should damage functionality.

## Citizen

A normal complaint should normally be submit-able within approximately:

```text
1 minute
```

A normal citizen complaint should require approximately:

```text
3–4 primary screens
```

The citizen should NOT need to understand:

```text
Department
Zone administration
Internal unit
Officer hierarchy
Supervisor hierarchy
Routing rules
SLA terminology
Technical complaint states
```

---

## Field Worker

A field worker should normally be able to update a routine task within approximately:

```text
10–15 seconds
```

The field worker must not be forced to write long administrative reports for ordinary work.

---

## Supervisor

A normal new task should be assignable to a team/worker within approximately:

```text
3 primary interactions
```

Supervisor home should prioritize:

```text
New
Ongoing
Due Today
Overdue
Completed
```

---

## Mayor / Administrator

The first executive viewport should contain no more than approximately:

```text
6 major KPIs
```

The executive user should understand critical problems without studying complex charts.

---

## Platform Administrator

A non-technical administrative employee should be able to manage:

```text
Employees
Wards
Zones
Responsibilities
Services
Complaint types
Service deadlines
Representatives
Notices
User access presets
```

without understanding:

```text
Database schema
Redis
PHP internals
API internals
Permission IDs
Server configuration
```

---

## Technical Administrator

Even the Technical Administrator's first-level interface must remain simple.

It should answer:

```text
Is the website working?
Is the database working?
Are notifications working?
Are background processes working?
Did backup succeed?
Is there any important security problem?
```

Raw technical detail should be hidden behind an optional:

```text
Technical Details
Advanced Details
```

view.

---

# 6. LANGUAGE REQUIREMENT

The complete platform MUST support exactly these initial interface languages:

```text
Bangla
English
```

Bangla is the primary/default language.

English is the secondary language.

This requirement applies to:

```text
Public Website
Citizen Portal
Administration Portal
Mayor / Administrator Dashboard
Platform Super Admin
Technical Super Admin
Call Center
Councillor / Representative Dashboard
Supervisor Interface
Field Worker Interface
Android Application
iOS Application
Notifications
Validation
System Messages
Reports
Complaint Categories
Status Labels
Public Statistics
```

Default new-user language:

```text
Bangla
```

Provide a simple language control similar to:

```text
বাংলা | EN
```

Remember the user's language preference.

---

# 7. LANGUAGE ARCHITECTURE

Never use translated text as the canonical business value.

Example internal status:

```text
in_progress
```

Bangla presentation:

```text
কাজ চলছে
```

English presentation:

```text
In Progress
```

Do NOT store:

```text
status = "কাজ চলছে"
```

as the source-of-truth business state.

Use language-neutral internal codes.

---

# 8. STATIC TRANSLATIONS

Create centrally managed translation resources.

Suggested backend structure:

```text
backend/lang/
  bn/
    common.php
    auth.php
    complaints.php
    administration.php
    workforce.php
    governance.php
    validation.php
    notifications.php

  en/
    common.php
    auth.php
    complaints.php
    administration.php
    workforce.php
    governance.php
    validation.php
    notifications.php
```

Exact internal organization may be adjusted if justified, but translations must remain centralized.

Do not scatter Bangla and English hardcoded throughout templates.

---

# 9. DYNAMIC BILINGUAL CONTENT

Configurable system entities should support both languages.

Examples include:

```text
Complaint categories
Complaint subcategories
Departments
Services
Service units
Public notices
Structured reasons
Public labels
Administrative titles where configurable
```

Use fields or related translation structures supporting values such as:

```text
name_bn
name_en
```

Do not translate citizen-generated content automatically as a replacement for the original.

---

# 10. USER-GENERATED CONTENT

Preserve original citizen and employee content.

Example citizen complaint:

```text
তিন দিন ধরে আমাদের বাসার সামনে ময়লা পড়ে আছে।
```

This original text must remain preserved.

If automated translation or summarization is added later, it must be stored separately.

Never overwrite the original evidence or statement.

---

# 11. DATE, TIME AND TIMEZONE

Store time internally in a consistent machine-readable format.

Prefer UTC storage where technically appropriate.

Default application presentation timezone:

```text
Asia/Dhaka
```

Example Bangla:

```text
২৫ আগস্ট ২০২৬, রাত ১০:৪৫
```

Example English:

```text
25 August 2026, 10:45 PM
```

The same underlying timestamp must support both presentations.

---

# 12. REQUIRED BACKEND TECHNOLOGY

Use:

```text
PHP 8.2+
Core PHP
Object-Oriented PHP
MVC-style architecture
PDO
```

Composer may be used only for clearly justified lightweight dependencies.

PSR-4 autoloading may be used where appropriate.

---

# 13. BACKEND FRAMEWORK RESTRICTION

Do NOT replace Core PHP with:

```text
Laravel
Symfony full-stack framework
CodeIgniter
Node.js backend
Django
Ruby on Rails
```

Do not silently introduce another full-stack backend framework.

This is a hard architectural requirement.

---

# 14. ANTI-OVERENGINEERING RULE

Core PHP does NOT mean building a giant custom framework.

Prefer:

```text
Explicit
Understandable
Predictable
Maintainable
Boring where appropriate
```

over:

```text
Magical
Over-abstracted
Clever
Highly indirect
```

Do NOT build a general-purpose framework.

Build only infrastructure required by this application.

---

# 15. DATABASE

Use:

```text
MySQL 8+
```

MySQL is the permanent source of truth.

Persistent civic records must ultimately exist safely in MySQL.

Examples:

```text
Users
Employees
Governance assignments
Complaints
Locations
Assignments
Status history
Evidence metadata
Citizen feedback
Notifications
Audit logs
Administrative configuration
```

---

# 16. REDIS

Use Redis for performance and coordination.

Expected purposes:

```text
Sessions
Cache
Counters
Locks
Rate Limits
Temporary State
Fast Dashboard Data
Worker Coordination
```

Non-negotiable rule:

> Redis is never the permanent source of truth for civic data.

If Redis is lost or flushed:

```text
Complaints must remain.
Employee records must remain.
Assignments must remain.
Complaint history must remain.
Audit history must remain.
Governance records must remain.
```

The system must recover operational cache/counters from durable data.

---

# 17. BACKEND INFRASTRUCTURE MODEL

Target architecture:

```text
Client
  │
  ▼
Nginx / HTTPS
  │
  ▼
PHP-FPM
  │
  ├───────────────┐
  ▼               ▼
Redis           MySQL
  │               │
  ├─ Sessions     ├─ Persistent Data
  ├─ Cache        ├─ Transactions
  ├─ Counters     ├─ Relationships
  ├─ Locks        └─ Source of Truth
  └─ Rate Limits
```

---

# 18. WEB FRONTEND TECHNOLOGY

Use:

```text
HTML5
CSS3
Bootstrap 5
Bootstrap Icons
HTMX
Vanilla JavaScript
Fetch API
Chart.js
```

Do not introduce a JavaScript-heavy SPA architecture.

---

# 19. HTMX RESPONSIBILITIES

Prefer HTMX for:

```text
Search
Filters
Pagination
Partial page updates
Lightweight polling
Small server-rendered interactions
```

---

# 20. VANILLA JAVASCRIPT RESPONSIBILITIES

Use Vanilla JavaScript for:

```text
Interactive widgets
Dynamic forms
Maps
Complex client UI state
Media interactions
Custom client logic
Fetch API operations where needed
```

---

# 21. FRONTEND FRAMEWORK RESTRICTION

Do NOT introduce:

```text
React
Vue
Angular
Next.js
Nuxt
```

unless the user explicitly changes this requirement later.

The web interface must remain server-rendered-first and progressively enhanced.

---

# 22. MOBILE TECHNOLOGY

Use:

```text
Flutter
Dart
```

Maintain one primary mobile codebase capable of producing:

```text
Android
iOS
```

Do not build completely separate Kotlin and Swift applications unless this requirement is explicitly changed later.

---

# 23. MOBILE IS NOT A WEBVIEW

The Android and iOS applications must be real mobile applications.

Do not merely wrap the web portal inside a WebView.

Use native Flutter capabilities for:

```text
Camera
GPS
Location
Push Notifications
Secure Local Storage
Offline/Pending Actions
Deep Links
Mobile Navigation
```

where applicable.

---

# 24. THREE CLIENTS, ONE PLATFORM

The complete product must consist of:

```text
Responsive Web Application
Android Application
iOS Application
```

All clients must use one central backend and one set of business rules.

Do not create separate complaint-routing logic for each client.

---

# 25. API-FIRST ARCHITECTURE

Create a versioned REST API.

Initial prefix:

```text
/api/v1/
```

Potential groups include:

```text
/api/v1/auth
/api/v1/profile
/api/v1/complaints
/api/v1/tasks
/api/v1/categories
/api/v1/wards
/api/v1/zones
/api/v1/departments
/api/v1/employees
/api/v1/notifications
/api/v1/public
/api/v1/admin
```

Exact endpoint details will be defined in later specification sections.

---

# 26. SHARED BUSINESS LOGIC

Important business rules must live in shared backend domain/service logic.

Example:

```text
Web Controller
      │
      ▼
Complaint Service
      ▲
      │
API Controller
```

Do not place core complaint routing, status transition, responsibility, SLA, or permission logic only inside web controllers.

---

# 27. API RESPONSE CONSISTENCY

Use consistent machine-readable API responses.

Example:

```json
{
  "success": true,
  "data": {},
  "meta": {},
  "request_id": "..."
}
```

Errors must include stable machine-readable error codes.

Mobile/web clients must not depend only on human-readable English or Bangla error text.

---

# 28. PROJECT STRUCTURE

Use a clean monorepo.

Recommended structure:

```text
amarmayor/
│
├── backend/
│   ├── app/
│   │   ├── Controllers/
│   │   ├── Services/
│   │   ├── Repositories/
│   │   ├── Domain/
│   │   ├── Middleware/
│   │   ├── Validation/
│   │   ├── Auth/
│   │   ├── Support/
│   │   └── View/
│   │
│   ├── config/
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   ├── lang/
│   ├── public/
│   ├── routes/
│   ├── storage/
│   ├── tests/
│   └── workers/
│
├── mobile/
│   ├── lib/
│   ├── android/
│   ├── ios/
│   └── test/
│
├── docs/
├── infra/
├── scripts/
└── README.md
```

Do not restructure the project without a meaningful reason.

Record material structural decisions in:

```text
docs/DECISIONS.md
```

---

# 29. DEVELOPMENT ENVIRONMENT

Primary current development environment:

```text
Windows
Laragon
C:\laragon\www\amarmayor
```

Local development must remain compatible with this environment.

Do not make initial development require Linux-only tooling.

---

# 30. PRODUCTION TARGET

Production target architecture:

```text
Linux
Nginx
PHP-FPM
OPcache
MySQL 8+
Redis
HTTPS
Background Workers
Scheduled Jobs
```

The project must remain deployable to a conventional production Linux server.

---

# 31. MISSING MOBILE TOOLING

If Flutter, Android tooling, Xcode, iOS signing, or another mobile build dependency is unavailable in the current development environment:

Do NOT block backend or web development.

Document the missing requirement.

Continue all locally executable work.

For iOS specifically, if macOS/Xcode is unavailable:

```text
Prepare source
Prepare configuration
Prepare documentation
Do not falsely claim signed iOS production build success
```

---

# 32. SERVER-SIDE FIRST

Prefer server-side rendering and backend-driven business logic.

Avoid sending large data sets to browsers merely to filter or paginate client-side.

Use server-side:

```text
Pagination
Filtering
Search
Sorting where appropriate
Authorization
Business-rule enforcement
```

---

# 33. PERFORMANCE PRINCIPLES

Mandatory design principles:

```text
Server-side pagination
Server-side filtering
Indexed search
LIMIT-based queries
Query optimization
Caching
Redis session storage
Rate limiting
Background jobs
Partial HTML rendering
Lazy loading
Asset caching
Compression
Connection tuning
Worker tuning
Load testing
```

Never perform unbounded complaint-list queries.

Never load every complaint into the browser.

---

# 34. DEFAULT LIST PAGINATION

Normal admin list default:

```text
25 records
```

Reasonable selectable sizes may include:

```text
25
50
100
```

For very large datasets, use efficient pagination strategies where appropriate.

---

# 35. LARGE-DATA DESIGN

The system must be designed from the beginning to remain usable when complaint history grows substantially.

Create architecture capable of testing against fictional datasets such as:

```text
10,000 complaints
100,000 complaints
1,000,000 complaints
```

Large-scale test data must never contain real citizen information.

---

# 36. PERFORMANCE TARGETS

Use these as reasonable staging engineering targets, not absolute production guarantees:

```text
Cached public dashboard:
p95 approximately < 500ms

Typical authenticated page/API:
p95 approximately < 800ms

Indexed complaint filter/search:
p95 approximately < 1 second
under realistic staging data

Complaint creation excluding external provider latency:
p95 approximately < 1 second
```

Final performance depends on actual infrastructure.

---

# 37. MOBILE / LOW-END DEVICE PRINCIPLE

The platform must remain usable on:

```text
Low-cost Android phones
Mobile data
Weak internet
Older reasonably supported browsers
```

Avoid:

```text
Large JavaScript bundles
Huge images
Unnecessary animations
Heavy visual effects
Over-designed dashboards
```

Use:

```text
Image optimization
Lazy loading
Compressed assets
Simple interfaces
Large tap targets
```

---

# 38. DESIGN LANGUAGE

The visual design should feel:

```text
Clean
Trustworthy
Modern
Government-appropriate
Friendly
Simple
Readable
Mobile-first
```

Do NOT make it look like a complicated traditional government portal.

Do NOT make it look like a flashy startup dashboard either.

---

# 39. BANGLA TYPOGRAPHY

Bangla is the primary visual language.

Ensure:

```text
Readable font sizing
Good line height
Clear spacing
Large form controls
Readable numbers
Proper wrapping
Comfortable mobile layout
```

Avoid dense small Bengali text.

---

# 40. ACCESSIBILITY

Follow practical accessibility principles.

At minimum:

```text
Semantic HTML
Keyboard usability
Visible focus states
Adequate contrast
Large tap targets
Form labels
Accessible validation
Error summaries
Status text alongside colors/icons
```

Do not rely only on color.

---

# 41. STATUS PRESENTATION

Use color only as reinforcement.

Example:

```text
Green + Resolved
Orange + In Progress
Red + Overdue
```

The text label must always remain visible.

---

# 42. USER LANGUAGE, NOT SYSTEM LANGUAGE

Use human language in UI.

Do not show ordinary users concepts such as:

```text
transition_state
owner_id
RBAC
SLA engine
queue worker
Redis
PDO
HTTP 422
foreign key
```

Translate system complexity into understandable action/result language.

---

# 43. PLATFORM ADMINISTRATOR SIMPLICITY PRINCIPLE

The Platform Super Admin is NOT assumed to be an IT expert.

The interface must use administrative language rather than technical language.

For example, prefer:

```text
কর্মীর দায়িত্ব পরিবর্তন করুন
Change Employee Responsibility
```

instead of:

```text
Edit Scope Mapping
```

Prefer:

```text
কাকে কোন অভিযোগ যাবে সেটি ঠিক করুন
Set Who Receives This Service
```

instead of:

```text
Modify Routing Rule Foreign Key Mapping
```

Detailed Platform Admin requirements will be provided in a later specification part.

---

# 44. TECHNICAL ADMINISTRATOR SIMPLICITY PRINCIPLE

The Technical Super Admin may have technical privileges, but the first-level interface must still be understandable to a non-expert.

Example first-level health language:

```text
ওয়েবসাইট সচল
Website Working

ডাটাবেস সচল
Database Working

দ্রুত সেবা সচল
Fast Services Working

বার্তা পাঠানোর সেবা সচল
Notification Service Working

Background কাজ সচল
Background Processing Working

সর্বশেষ Backup সফল
Latest Backup Successful
```

Raw infrastructure names can appear under:

```text
Technical Details
Advanced
```

---

# 45. PROGRESSIVE DISCLOSURE

Use progressive disclosure throughout the system.

Example:

First view:

```text
বার্তা পাঠানোর সেবায় সমস্যা হয়েছে।
```

Advanced view:

```text
SMS Provider Authentication Failure
Provider: ...
Error Code: ...
```

Do not force normal users to interpret infrastructure errors.

---

# 46. AUTOMATION SHOULD REMOVE WORK, NOT CREATE WORK

Automation should be used to reduce unnecessary administrative steps.

Examples:

```text
Automatic Ward detection
Automatic complaint routing
Automatic status statistics
Automatic overdue detection
Automatic notification
Automatic dashboard aggregation
```

Do not create automation that requires staff to manage complicated automation rules during normal daily work.

---

# 47. ONE CLEAR NEXT ACTION

Every operational screen should emphasize the next important action.

Citizen:

```text
সমস্যা জানান
```

Worker:

```text
কাজ শুরু করুন
```

Supervisor:

```text
দায়িত্ব দিন
```

Administrator:

```text
দায়িত্ব পরিবর্তন করুন
```

Mayor/Admin:

```text
ব্যবস্থা নিতে বলুন
```

Avoid presenting ten equal-priority buttons.

---

# 48. DO NOT COPY PAPER BUREAUCRACY INTO SOFTWARE

Do not reproduce unnecessary manual forwarding chains simply because paper government workflows often use them.

The system should improve workflow.

Avoid:

```text
Forward
Forward again
Forward again
Print
Sign
Re-enter
Select same information again
```

The system should preserve context automatically.

---

# 49. NO REPEATED DATA ENTRY

If the system already knows:

```text
Citizen location
Ward
Zone
Department
Complaint
Current responsible unit
```

do not ask the user to type/select the same data again unless verification is genuinely necessary.

---

# 50. MOBILE-FIRST OPERATIONS

Operational interfaces should be designed first for practical field usage.

Especially:

```text
Field Worker
Supervisor
Ward-level operational staff
Inspection users
```

Executive analytics may use wider desktop layouts.

---

# 51. DASHBOARD PRINCIPLE

A dashboard is not a collection of every possible chart.

Each dashboard must show:

```text
What matters now
What needs attention
What can be acted on
```

Avoid unnecessary visual noise.

---

# 52. PUBLIC INTERFACE PRINCIPLE

The public side should be institution-first.

Primary identity:

```text
Mymensingh City Corporation
City Services
Citizen Services
```

Do not design the public system as covert personal political promotion.

Legitimate actions by elected or appointed leadership may be factually attributed where appropriate.

Detailed governance presentation will be defined in a later specification part.

---

# 53. PRIVACY BY DESIGN

Even before detailed privacy rules are specified, follow these principles:

```text
Collect only necessary data
Do not publicly expose private citizen information
Do not expose personal employee information unnecessarily
Separate public-safe information from internal information
Use least-privilege access
Preserve accountability records
```

Detailed privacy/security rules will be provided in later specification parts.

---

# 54. CORE SYSTEM MUST NOT DEPEND ON AI

The civic service platform must function without AI.

AI may later assist with:

```text
Category suggestion
Duplicate suggestion
Trend summaries
Search assistance
```

but the system must have deterministic non-AI fallback.

Never let AI become necessary for:

```text
Submitting a complaint
Routing ordinary complaints
Tracking a complaint
Worker task completion
Official responsibility
Audit history
Public statistics
```

---

# 55. EXTERNAL SERVICE ABSTRACTION

External services such as:

```text
SMS
Push Notification
Email
Maps
Future AI
```

must use clean provider abstractions.

If credentials are unavailable:

```text
Create interface
Create development/mock implementation
Create configuration placeholder
Document setup
Continue development
```

Do not stop the project merely because production credentials are unavailable.

---

# 56. SECRETS

Do not hardcode production secrets.

Use environment configuration.

Create/maintain:

```text
.env.example
```

Never commit:

```text
Production passwords
Database secrets
Redis secrets
SMS credentials
Push credentials
Private API keys
Apple signing secrets
Private cryptographic keys
```

---

# 57. GIT AND PROJECT ISOLATION

The active project is:

```text
C:\laragon\www\amarmayor
```

All work must remain inside this project unless an external tool genuinely requires otherwise.

Do NOT access, modify, copy from, or depend on:

```text
C:\laragon\www\ahospital
```

This is a hard isolation requirement.

---

# 58. DOCUMENTATION AS SOURCE OF TRUTH

The project must maintain authoritative documentation.

The master product specification is:

```text
docs/MASTER_SPEC.md
```

Other architecture documents will derive from the full specification after ingestion is complete.

Do not allow implementation to silently drift away from the documented requirements.

---

# 59. DECISION LOG

Record material architectural decisions in:

```text
docs/DECISIONS.md
```

Include:

```text
Date
Decision
Reason
Alternatives considered
Impact
```

Do not log trivial implementation details as architecture decisions.

---

# 60. IMPLEMENTATION STATUS

Maintain:

```text
docs/IMPLEMENTATION_STATUS.md
```

Later implementation phases should record:

```text
Completed
In Progress
Tests
Known Issues
External Blocks
Architecture Deviations
Next Phase
```

---

# 61. NO IMPLEMENTATION DURING SPECIFICATION INGESTION

During the current specification-ingestion process:

Do NOT:

```text
Build application features
Create production database migrations
Build frontend screens
Start Flutter implementation
Implement complaint workflow
Implement employee management
Implement governance features
```

Only update the specification and log genuine ambiguities/conflicts.

---

# 62. PART 1 SUMMARY — NON-NEGOTIABLE RULES

The following are hard requirements:

```text
Bangla is primary.
English is secondary.

PHP 8.2+ Core PHP backend.
OOP + MVC-style architecture.
PDO.
MySQL 8+.
Redis.

HTML5.
CSS3.
Bootstrap 5.
Bootstrap Icons.
HTMX.
Vanilla JavaScript.
Fetch API.
Chart.js.

Flutter + Dart.
Android + iOS.

MySQL is the source of truth.
Redis is never the permanent source of truth.

Web, Android and iOS use the same backend business rules.

No Laravel.
No React/Vue/Angular.

The system must remain simple for every role.

Platform Super Admin must be usable by non-technical staff.

Technical Super Admin must also present simple plain-language controls first.

Technical complexity must remain hidden by default.

Development environment is Windows + Laragon.

Production target is Linux + Nginx + PHP-FPM.

The core platform must work without AI.

Do not invent official government data.

Do not access the hospital project.
```

---

# END OF SPECIFICATION PART 1

---

# SPECIFICATION PART 2

## City Structure, Governance, Ward Representation, Employees, Workforce and Official Communication

This is **Specification Part 2** of the complete master specification for the Mymensingh City Citizen Service Platform.

---

# 63. CITY CORPORATION AS A FIRST-CLASS ENTITY

The platform is initially designed for:

**Mymensingh City Corporation — MCC**

Bangla:

**ময়মনসিংহ সিটি কর্পোরেশন — মসিক**

The City Corporation itself must be a configurable top-level entity.

Do not scatter City Corporation identity throughout application code.

Support configurable:

```text
Official Bangla Name
Official English Name
Short Name
Logo
Official Address
Official Phone
Official Email
Website
Public Service Hours
Timezone
Primary Language
Secondary Language
```

Future legitimate administrative changes must not require rewriting core application logic.

---

# 64. INITIAL ADMINISTRATIVE STRUCTURE

The initial MCC administrative geography contains:

```text
3 Zones
33 General Wards
11 Reserved Seats
```

These values describe the initial Mymensingh setup.

However:

> Do NOT hardcode these counts into reusable business logic.

An authorized administrator must be able to manage legitimate future changes.

---

# 65. CITY GEOGRAPHIC HIERARCHY

Use the conceptual hierarchy:

```text
City Corporation
       │
       ├── Zone
       │     │
       │     └── General Ward
       │
       ├── Reserved Representation Areas
       │
       ├── Departments
       │
       ├── Service Units
       │
       ├── Offices
       │
       └── Workforce
```

Geography and organizational departments are related but not identical.

A department may operate:

```text
Citywide
Zone-wide
Ward-specific
Multiple Wards
Special service area
```

---

# 66. INITIAL MCC ZONE-WARD MAPPING

Create the following as the initial configurable MCC administrative mapping:

## Zone 1

```text
Ward 1
Ward 2
Ward 4
Ward 6
Ward 11
Ward 12
Ward 27
Ward 28
Ward 29
Ward 30
```

## Zone 2

```text
Ward 3
Ward 5
Ward 7
Ward 8
Ward 9
Ward 10
Ward 16
Ward 17
Ward 18
Ward 31
Ward 32
Ward 33
```

## Zone 3

```text
Ward 13
Ward 14
Ward 15
Ward 19
Ward 20
Ward 21
Ward 22
Ward 23
Ward 24
Ward 25
Ward 26
```

Treat this as initial administrative configuration.

Do not embed this mapping into PHP conditionals.

The Platform Administrator must be able to change a Ward's Zone assignment if a future verified government order changes the structure.

Every such change must be historically auditable.

---

# 67. ZONE ENTITY

Each Zone must support:

```text
Zone ID
Zone Number
Bangla Name
English Name
Included Wards
Office Name
Office Address
Official Phone
Official Email
Responsible Officer
Status
Effective From
Effective To
Public Notes
Internal Notes
```

Zone information should be bilingual where applicable.

---

# 68. GENERAL WARD ENTITY

Each General Ward must support:

```text
Ward ID
Ward Number
Bangla Name
English Name
Zone
Area Name(s)
Approximate Area
Population where verified
Household Count where verified
Ward Office
Official Contact
Boundary Data
Current Representation
Service Responsibility
Status
Effective Dates
```

Never invent:

```text
Ward population
Ward boundary
Ward address
Representative
Contact information
```

Use verified/imported information only.

---

# 69. WARD BOUNDARY DATA

The system must support future import of verified Ward boundaries.

Preferred import format:

```text
GeoJSON
```

The system may also support:

```text
Latitude/Longitude polygons
MySQL spatial geometry
```

where technically appropriate.

Do NOT fabricate Ward polygons.

Until verified polygon data exists, use:

```text
GPS location
+
Manual Ward confirmation
```

as fallback.

---

# 70. GOVERNANCE MODEL MUST BE FLEXIBLE

Do NOT design the platform around the assumption that:

> Every General Ward always has an elected councillor.

MCC governance may operate under different legitimate administrative arrangements.

The system must support:

```text
Elected General Ward Councillor
Reserved Women Councillor
Responsible Officer
Acting Responsible Officer
Temporarily Assigned Officer
Other Authorized Representative
```

All assignments must be:

```text
Configurable
Time-bound
Historically preserved
Auditable
Based on official authority
```

---

# 71. MAYOR AND ADMINISTRATOR ARE SEPARATE GOVERNANCE TYPES

Do NOT permanently combine:

```text
Mayor
Administrator
```

into one technical role.

They may have overlapping permissions, but they represent different governance situations.

Support separate governance positions:

```text
Mayor
Administrator
Chief Executive Officer
```

Permissions can later be assigned according to actual administrative authority.

---

# 72. ELECTED MAYOR

Where an elected Mayor is serving, support a governance assignment containing:

```text
Person
Position = Mayor
Authority Basis = Elected
Start Date
End Date
Status
Election / Official Reference
Public Profile
Official Contact Channel
```

Do not invent a current Mayor.

---

# 73. ADMINISTRATOR

Where an Administrator is officially assigned instead of an elected Mayor, support:

```text
Person
Position = Administrator
Authority Basis = Appointed / Government Assigned
Start Date
End Date
Status
Official Order
Public Profile
Official Contact Channel
```

Do not incorrectly label an Administrator as Mayor.

---

# 74. CHIEF EXECUTIVE OFFICER

Support CEO as a distinct administrative position.

The CEO is part of administrative execution, not electoral representation.

Support:

```text
Person
Position
Department/Office
Start Date
End Date
Status
Official Contact
Acting/Regular Status
```

---

# 75. GENERAL WARD COUNCILLOR

A normal elected General Ward Councillor represents a General Ward.

Support:

```text
Person
Ward
Representation Type = General Councillor
Authority Basis = Elected
Election Date / Term Reference
Start Date
End Date
Status
Official Contact
Public Profile
```

A General Ward should never be forced to have an elected councillor when none currently exists.

---

# 76. RESPONSIBLE OFFICER WHEN COUNCILLOR IS ABSENT

When no elected General Ward Councillor is serving, an authorized government/City Corporation officer may be assigned responsibility.

The system must support:

```text
Responsible Officer
Acting Responsible Officer
Temporary Responsible Officer
```

A responsible officer may cover:

```text
One Ward
or
Multiple Wards
```

Example:

```text
Responsible Officer X

Coverage:
Ward 1
Ward 2
Ward 4
```

This is a required feature.

---

# 77. ONE REPRESENTATIVE MAY COVER MULTIPLE WARDS

Representation must use many-to-many capable coverage.

Do NOT model Ward responsibility only as:

```text
ward.councillor_id
```

because one authorized person may legitimately cover multiple Wards.

Use a proper representation assignment model.

---

# 78. RESERVED WOMEN COUNCILLORS

The system must support elected women councillors for reserved seats.

Current MCC structure:

```text
33 General Wards
11 Reserved Seats
```

A Reserved Women Councillor represents:

```text
3 General Wards
```

for the current MCC structure.

However:

> The exact Ward grouping must remain configurable.

Do NOT invent or assume the exact three-Ward groups unless verified data is entered.

---

# 79. RESERVED SEAT ENTITY

Each Reserved Seat should support:

```text
Reserved Seat ID
Reserved Seat Number
Bangla Name
English Name
Covered General Wards
Current Representative
Start Date
End Date
Status
Official Reference
```

The coverage relationship must be managed through data/configuration.

---

# 80. RESERVED WOMEN COUNCILLOR PROFILE

Support:

```text
Person
Representation Type = Reserved Women Councillor
Reserved Seat
Covered Wards
Authority Basis = Elected
Start Date
End Date
Status
Official Contact Channel
Public Profile
```

---

# 81. MULTIPLE REPRESENTATION OF ONE WARD

A General Ward may simultaneously have:

```text
General Ward Representative
+
Reserved Women Councillor
```

Example:

```text
Ward 5

General Representation:
Elected General Councillor
OR
Responsible Officer

Reserved Representation:
Reserved Women Councillor
```

Both must be visible in governance data.

---

# 82. REPRESENTATION HISTORY

Never overwrite historical representation.

Example:

```text
01 Sep 2024 – 15 Oct 2026
Responsible Officer X

16 Oct 2026 – Present
Elected Councillor Y
```

Historical complaints must preserve the responsible representation that existed at that time where applicable.

---

# 83. OFFICIAL AUTHORITY RECORD

Every formal leadership/representation assignment should support:

```text
Authority Basis
Official Order Number
Election Reference
Order Date
Effective Date
End Date
Issuing Authority
Attachment
Remarks
```

Attachment may contain:

```text
PDF
Image
Scanned official order
```

subject to security and file rules.

---

# 84. ACTING / TEMPORARY GOVERNANCE ASSIGNMENT

Support temporary assignment.

Example:

```text
Responsible Officer A unavailable

Temporary Responsible Officer B
From: 26 Aug
To: 2 Sep
```

The system should know which assignment is currently effective.

Expired assignments must remain historically visible.

---

# 85. REPRESENTATIVE STATUS

Support simple statuses such as:

```text
Active
Inactive
Term Ended
Acting
Temporary
Suspended
Vacant
```

Use language-neutral internal codes and bilingual presentation.

---

# 86. REPRESENTATIVE PERMISSIONS ARE NOT FIELD-WORKER PERMISSIONS

A Councillor or Responsible Ward Representative may:

```text
View complaints in permitted area
Monitor Ward performance
Follow up complaints
Request administrative attention
Communicate with citizens
See Ward-level trends
Raise civic issues
Send legitimate Ward notices where authorized
```

They must NOT automatically be able to:

```text
Mark field work completed
Fabricate evidence
Delete complaints
Modify audit history
Pretend to be a cleaner
Override technical evidence
```

Representation and operational execution are separate concepts.

---

# 87. GENERAL COUNCILLOR DASHBOARD

The dashboard should remain extremely simple.

Primary view:

```text
আমার ওয়ার্ড
My Ward
```

Show:

```text
Today's Complaints
Resolved
Ongoing
Overdue
Citizen Says Not Resolved
Important Ward Issues
```

Primary actions:

```text
View
Follow Up
Request Attention
Communicate
```

Do not create a complicated management console.

---

# 88. RESERVED WOMEN COUNCILLOR DASHBOARD

The Reserved Women Councillor must see all covered Wards.

Example:

```text
আমার এলাকা
My Area

Ward A
Ward B
Ward C
```

Show:

```text
Combined Overview
Ward-by-Ward Overview
Important Issues
Citizen Feedback
Overdue
Needs More Work
```

Do not require separate accounts for each covered Ward.

---

# 89. RESPONSIBLE OFFICER DASHBOARD

A Responsible Officer covering multiple Wards should have one dashboard.

Example:

```text
আমার দায়িত্বের ওয়ার্ড
My Responsible Wards

Ward 1
Ward 2
Ward 4
```

Show aggregated information first.

Allow simple Ward drill-down.

---

# 90. WORKFORCE DIRECTORY IS A CORE MODULE

The platform must contain a comprehensive City Corporation employee/workforce directory.

This is NOT optional.

The system should become the authoritative operational directory for verified City Corporation workforce data used by the platform.

Do not invent real employees.

---

# 91. WORKFORCE TYPES

Support configurable workforce types including:

```text
Officer
Permanent Employee
Temporary Employee
Daily Wage Worker
Outsourced Worker
Cleaner
Sanitation Worker
Field Worker
Driver
Supervisor
Team Leader
Engineer
Health Worker
Inspector
Administrative Staff
Technician
Other
```

The exact employment categories must remain configurable.

---

# 92. EMPLOYEE MASTER PROFILE

Each employee/workforce member should support:

```text
Internal Employee ID
Official Employee Number where available
Photo
Full Bangla Name
Full English Name
Designation
Employment Type
Department
Unit
Zone
Ward(s)
Team
Reporting Officer
Supervisor
Joining Date
Current Posting
Posting Start Date
Duty Status
Shift where applicable
Official Phone
Official Email
Skills
System Access Status
Role
Scope
Public Visibility Settings
Notes
Created At
Updated At
```

Private fields should be separated from public fields.

---

# 93. EMPLOYEE ID

Generate a safe internal identifier.

Example format may resemble:

```text
MCC-EMP-000421
```

Do not depend only on MySQL auto-increment IDs as visible identifiers.

---

# 94. EMPLOYEE PHOTO

Support employee photo where officially allowed.

Provide:

```text
Internal Photo
Public Profile Photo Visibility
```

Do not automatically expose every employee's photo publicly.

---

# 95. EMPLOYMENT TYPE AND DESIGNATION ARE DIFFERENT

Do not confuse:

```text
Employment Type
```

with:

```text
Designation
```

Example:

```text
Employment Type:
Permanent Employee

Designation:
Sanitary Inspector
```

or:

```text
Employment Type:
Daily Wage Worker

Designation:
Cleaner
```

---

# 96. EMPLOYEE MAY COVER MULTIPLE AREAS

An employee may have responsibility for:

```text
One Ward
Multiple Wards
One Zone
Multiple Zones
Citywide
Department-wide
Special Service Area
```

Do not use a data model that forces every employee into one Ward only.

---

# 97. DEPARTMENT

Create a configurable Department entity.

Initial examples may include:

```text
Administration
Waste Management
Health
Engineering
Electrical
Water
Revenue
Citizen Services
Urban Planning
Property
Social Welfare
Transport
Store / Procurement
Legal / Enforcement
ICT
Public Information
Security
Other
```

These are configurable organizational units.

Do not hardcode all complaint routing directly into department names.

---

# 98. SERVICE UNIT

A Department may contain service units.

Examples:

```text
Mosquito Control
Waste Collection
Drain Cleaning
Road Maintenance
Street Lighting
Water Maintenance
Citizen Registration
```

This provides operational routing without forcing citizens to know the internal structure.

---

# 99. ORGANIZATIONAL OFFICE

Support office entities such as:

```text
Head Office
Zone Office
Ward Office
Department Office
Service Center
Control Room
```

Each may contain:

```text
Bangla Name
English Name
Address
Official Phone
Official Email
Office Hours
Zone
Ward
Department
Public Visibility
```

---

# 100. REPORTING RELATIONSHIP

The workforce system must support:

```text
Who reports to whom?
```

Example:

```text
CEO
 ↓
Department Head
 ↓
Officer
 ↓
Supervisor
 ↓
Team Leader
 ↓
Field Worker
```

But do not hardcode this exact hierarchy.

Reporting relationships must be configurable.

---

# 101. ORGANIZATION CHART

Create a future-friendly visual organization chart.

It should allow authorized users to understand:

```text
Leadership
Departments
Zones
Wards
Officers
Supervisors
Teams
Employees
```

The UI should use simple human language.

---

# 102. POSTING HISTORY

Employee assignment changes must preserve history.

Example:

```text
2024–2025:
Ward 7

2025–2026:
Ward 11

2026–Present:
Ward 15
```

Do not overwrite previous posting history.

---

# 103. CURRENT POSTING AND HISTORICAL POSTING

Maintain both:

```text
Current Posting
Posting History
```

Current operational routing uses active posting.

Old complaint accountability must continue to show historical assignment information.

---

# 104. TRANSFER

Support official transfer/posting change.

A transfer action should support:

```text
Employee
From
To
Effective Date
Official Reference
Reason
Authorized By
```

Do not require deleting/recreating the employee.

---

# 105. TEMPORARY DUTY

Support:

```text
Temporary Assignment
Acting Assignment
Leave Replacement
Special Duty
Emergency Duty
```

Each must contain:

```text
Start
End
Area
Responsibility
Authority
```

---

# 106. LEAVE / TEMPORARY ABSENCE

Support operational availability such as:

```text
Available
On Duty
Busy
On Leave
Off Duty
Temporarily Reassigned
Inactive
```

Do not build a full payroll/HR leave management system unless later requested.

The goal is operational routing and accountability.

---

# 107. AUTOMATIC TEMPORARY REPLACEMENT

When an operational supervisor is temporarily unavailable, authorized administrators should be able to assign a replacement.

Example:

```text
Supervisor A
On Leave

Temporary Responsibility:
Supervisor B

Effective:
26 Aug – 2 Sep
```

Routing should honor the active assignment.

---

# 108. FIELD TEAM

Create a Team entity.

Example:

```text
Ward 19 Cleaning Team A
```

Support:

```text
Team Name
Department
Service Unit
Zone
Ward(s)
Supervisor
Team Leader
Members
Skills
Availability
Status
```

---

# 109. TEAM ASSIGNMENT

A Supervisor should be able to assign a complaint/task to:

```text
Individual Worker
or
Team
```

For routine municipal operations, team assignment should be easy.

---

# 110. WORKFORCE SKILLS

Support simple skills/capabilities.

Examples:

```text
Waste Collection
Street Sweeping
Drain Cleaning
Fogging
Larvicide Treatment
Electrical Repair
Plumbing
Driving
Heavy Equipment
Inspection
Road Maintenance
```

Skills are operational metadata.

Do not create unnecessary certification complexity.

---

# 111. VEHICLE / EQUIPMENT FUTURE LINK

Design workforce/team relationships so future modules can attach:

```text
Vehicle
Equipment
Tool
Machine
```

Example:

```text
Cleaning Team A
Assigned Waste Vehicle
```

Do not make vehicle management block the core complaint system.

---

# 112. PLATFORM ADMIN EMPLOYEE SEARCH

Authorized Platform Administrators should be able to search naturally.

Examples:

```text
রফিক
Ward 19 waste
Zone 2 supervisor
Cleaner
Electrical Ward 5
```

Provide filters but keep normal search simple.

---

# 113. ADD EMPLOYEE MUST BE A GUIDED WIZARD

Do NOT expose one huge employee database form.

Use guided steps.

## Step 1 — Basic Information

```text
Name
Photo
Employee ID
Official Phone
Official Email
```

## Step 2 — Work Information

```text
Employment Type
Designation
Department
Service Unit
```

## Step 3 — Responsibility

```text
Zone
Ward(s)
Team
Reports To
```

## Step 4 — System Access

```text
Does this employee need login access?
Yes / No
```

If Yes:

Use understandable role presets.

Do not expose dozens of permission codes.

---

# 114. CHANGE RESPONSIBILITY WIZARD

Provide a simple action:

```text
দায়িত্ব পরিবর্তন করুন
Change Responsibility
```

The administrator selects:

```text
Person
New Responsibility
Area
Start Date
Temporary or Permanent
```

The system handles underlying scope updates.

---

# 115. REPRESENTATIVE SETUP WIZARD

Create guided administrative actions:

```text
Assign General Councillor
Assign Responsible Officer
Assign Reserved Women Councillor
Assign Temporary Representative
End Representation
```

The administrator should not need to manually edit relationship tables.

---

# 116. PUBLIC VS INTERNAL EMPLOYEE DATA

Define clear information boundaries.

## Internal Profile May Include

```text
Employee ID
Employment Type
Posting History
Supervisor
Current Workload
Internal Phone
Official Phone
Shift
Availability
Skills
System Role
Performance Data
Internal Notes
```

## Public Profile May Include

only when allowed:

```text
Name
Official Photo
Designation
Department
Official Responsibility
Zone/Ward
Official Contact Channel
Office Hours
```

---

# 117. PRIVATE EMPLOYEE INFORMATION

Never publicly expose by default:

```text
Personal Mobile
Personal Email
Home Address
Salary
Emergency Contact
NID
Private Attendance Data
Internal Performance Notes
Security Information
Password/Auth Data
```

---

# 118. OFFICIAL CONTACT CHANNEL

Employee/representative public contact should use official channels.

Examples:

```text
In-App Message
Official Office Phone
Official Email
Ward Office Contact
Department Contact
```

Do not depend on publishing private phone numbers.

---

# 119. CITIZEN-TO-EMPLOYEE COMMUNICATION

Citizens should be able to communicate with the City Corporation through the platform.

However:

> The application must NOT become an unrestricted personal chat system with every employee.

Communication must remain structured and relevant.

---

# 120. COMPLAINT-LINKED COMMUNICATION

When a complaint is assigned, the citizen may send relevant additional information.

Example:

```text
“জায়গাটা বাজারের পেছনের গলিতে।”
```

The message remains attached to the complaint.

Authorized responsible staff may reply.

This communication becomes part of the complaint record.

---

# 121. PUBLIC EMPLOYEE CONTACT

Where policy allows, a citizen may see:

```text
Official Contact
Send Message
Office Phone
Office Email
Office Hours
```

Do not reveal personal contact details automatically.

---

# 122. “WHO IS RESPONSIBLE?” FEATURE

Create a major public feature:

```text
আমার এলাকার দায়িত্বে কে?
Who Is Responsible for My Area?
```

Citizen can use:

```text
Current Location
Ward
Area Search
```

The system may show:

```text
Ward
Zone
General Representative / Responsible Officer
Reserved Women Councillor
Ward Office
Waste Responsibility
Mosquito / Health Responsibility
Road / Drain Responsibility
Street Light Responsibility
Water Responsibility
Official Contact Channels
```

---

# 123. WARD PUBLIC PROFILE

Every Ward should have a simple public page.

Example:

```text
Ward 19
Zone 3
```

Show:

```text
General Representative / Responsible Officer
Reserved Women Councillor
Ward Office
Important Service Contacts
Today's Complaints
Resolved
Ongoing
Overdue
Common Complaint Categories
```

Do not expose sensitive employee details.

---

# 124. ZONE PUBLIC PROFILE

Zone page may show:

```text
Zone Number/Name
Included Wards
Responsible Zone Officer
Official Zone Office
Official Contact
Current Complaint Summary
Ward Comparison
```

Keep it simple.

---

# 125. DEPARTMENT PUBLIC PROFILE

Public Department page may show:

```text
Department Name
What It Does
Services
Official Contact
Service Areas
Public Performance Summary
```

Internal Department view may additionally show:

```text
Employees
Supervisors
Teams
Backlog
Complaints
Operational Metrics
```

---

# 126. MAYOR / ADMINISTRATOR PUBLIC PROFILE

Support factual public leadership profiles.

Show where appropriate:

```text
Name
Official Position
Official Photo
Official Office Contact
Official Message Channel
Term / Assignment Period
```

Do not use the public interface for covert political promotion.

---

# 127. MAYOR / ADMINISTRATOR OFFICE COMMUNICATION

Create:

```text
প্রশাসকের কাছে লিখুন
Write to the Administrator
```

or when appropriate:

```text
মেয়রের কাছে লিখুন
Write to the Mayor
```

The label must reflect the actual active governance position.

---

# 128. LEADERSHIP OFFICE INBOX

Do NOT send every citizen message directly as an intrusive personal notification.

Create an Office Inbox.

Message types may include:

```text
Complaint Concern
Suggestion
Feedback
Urgent Attention
General Civic Message
Appreciation
Other
```

Authorized office staff may triage messages.

---

# 129. LEADERSHIP OFFICE SUMMARY

Mayor/Administrator may see:

```text
Citizen Messages Today
Urgent Attention
Service Issue
Suggestions
Feedback
Other
```

This should remain simple.

---

# 130. REPRESENTATIVE-CITIZEN COMMUNICATION

A General Councillor, Reserved Women Councillor, or Responsible Officer may have an official in-app communication channel where policy allows.

Citizen must clearly see the person's official role.

All official platform communication must be auditable where appropriate.

---

# 131. DO NOT CREATE RANDOM PERSONAL CHAT

The system must prevent:

```text
Citizen randomly messaging any cleaner
Citizen messaging unrelated employees
Employee using system for unrelated private chat
```

Communication should be based on:

```text
Complaint relationship
Ward relationship
Official office
Service relationship
Authorized public communication
```

---

# 132. PLATFORM ADMIN PEOPLE MODULE

Platform Super Admin navigation should include a simple:

```text
লোকজন
People
```

module.

It may contain:

```text
সব কর্মী
All Employees

কর্মকর্তা
Officers

সুপারভাইজার
Supervisors

ফিল্ড কর্মী
Field Workers

পরিচ্ছন্নতাকর্মী
Cleaners

দল
Teams

প্রতিনিধি
Representatives

দায়িত্ব পরিবর্তন
Change Responsibility
```

Do not expose technical entity/table names.

---

# 133. PLATFORM ADMIN AREA MODULE

Provide:

```text
অঞ্চল ও ওয়ার্ড
Zones & Wards
```

Simple actions:

```text
View Zones
View Wards
Change Zone Assignment
Manage Ward Office
Manage Official Contacts
Manage Verified Boundary Data
```

---

# 134. PLATFORM ADMIN REPRESENTATION MODULE

Provide human-friendly management:

```text
বর্তমান প্রতিনিধিত্ব
Current Representation

নতুন দায়িত্ব দিন
Assign Responsibility

সংরক্ষিত আসন
Reserved Seats

পূর্বের দায়িত্ব
Representation History
```

---

# 135. PLATFORM ADMIN SHOULD NOT EDIT RAW PERMISSION TABLES

For ordinary setup, use role presets.

Example:

```text
General Councillor
Reserved Councillor
Responsible Officer
Department Head
Zone Officer
Ward Officer
Supervisor
Field Worker
Call Center Operator
```

Advanced permission customization may exist, but it should not be required for normal operations.

---

# 136. TECHNICAL ADMIN DOES NOT MANAGE GOVERNANCE BY DEFAULT

Technical Super Admin is responsible for platform technology.

Platform Super Admin is responsible for:

```text
People
Organization
Governance
Zones
Wards
Departments
Services
Operational configuration
```

Do not mix these responsibilities unnecessarily.

---

# 137. EMPLOYEE SYSTEM ACCESS IS OPTIONAL

Not every employee needs a login account.

Example:

A cleaner may be represented in the employee directory but may not have personal login access.

Support:

```text
Employee exists
System login = No
```

If later needed:

```text
Enable System Access
```

without recreating the employee.

---

# 138. USER ACCOUNT AND EMPLOYEE PROFILE ARE RELATED BUT DISTINCT

Do not assume every user is an employee.

Examples:

```text
Citizen User
Public User
Employee User
Councillor User
Administrator User
```

An employee may have:

```text
Employee Profile
+
Optional User Account
```

This separation is required.

---

# 139. ONE PERSON MAY HOLD DIFFERENT RESPONSIBILITIES OVER TIME

Do not create duplicate person records simply because someone's role changes.

Example:

```text
Person X
2025: Zone Officer
2026: Responsible Officer for Wards 1 and 2
```

Use assignment history.

---

# 140. CURRENT RESPONSIBILITY MUST BE EASY TO UNDERSTAND

Any authorized profile should clearly answer:

```text
বর্তমানে কী দায়িত্বে?
What is this person currently responsible for?
```

Avoid requiring users to interpret multiple relationship tables.

---

# 141. HISTORICAL RESPONSIBILITY MUST REMAIN AVAILABLE

Authorized internal users should be able to see:

```text
Previous Roles
Previous Postings
Previous Wards
Effective Dates
Official References
```

This is important for accountability.

---

# 142. PUBLIC CURRENT DATA VS HISTORICAL DATA

Public pages primarily show:

```text
Current Active Representation
Current Official Responsibility
```

Historical representation may be available through a simple history view where appropriate.

Do not confuse citizens by mixing inactive and active officials.

---

# 143. NO INVENTED MCC EMPLOYEE DIRECTORY

Although the system will eventually contain all verified MCC workforce information:

> Do NOT generate fake real-looking MCC employee data and present it as official.

During development use clearly fictional/demo identities.

---

# 144. VERIFIED DATA IMPORT

Prepare for future import of verified data using:

```text
CSV
Spreadsheet
Administrative entry
API/import adapter where justified
```

Potential import types:

```text
Employees
Departments
Zones
Wards
Representatives
Teams
Official contacts
```

Import must validate before final save.

---

# 145. DUPLICATE EMPLOYEE PROTECTION

When importing/adding employees, detect likely duplicates using:

```text
Employee Number
Official Email
Official Phone
Name + Designation
```

Do not automatically merge ambiguous people.

Require review.

---

# 146. EMPLOYEE DEACTIVATION

Employees should normally be:

```text
Active
Inactive
Transferred
Retired
Term Ended
Separated
```

Do not delete historical employee records merely because employment ends.

---

# 147. REPRESENTATIVE DEACTIVATION

When representation ends:

```text
End Assignment
```

Do not delete the assignment.

Preserve:

```text
Start
End
Authority
Coverage
History
```

---

# 148. ORGANIZATIONAL DATA AUDIT

Audit important changes including:

```text
Employee Added
Employee Updated
Posting Changed
Team Changed
Supervisor Changed
Ward Assignment Changed
Zone Assignment Changed
Representative Assigned
Representative Ended
Responsible Officer Assigned
Reserved Seat Coverage Changed
Official Contact Changed
```

Detailed audit architecture will be specified later.

---

# 149. CITY STRUCTURE MUST SUPPORT FUTURE CHANGE

Do not create assumptions such as:

```text
Always exactly 3 Zones forever
Always exactly 33 Wards forever
Always exactly 11 Reserved Seats forever
```

These are initial MCC configuration values.

Historical complaints and assignments must remain understandable after future structure changes.

---

# 150. EFFECTIVE-DATE MODEL

Where appropriate, administrative relationships should support:

```text
effective_from
effective_to
```

This applies particularly to:

```text
Ward-Zone assignment
Representation
Employee Posting
Temporary Responsibility
Leadership Assignment
Team Assignment
```

---

# 151. ORGANIZATIONAL SOURCE OF TRUTH

For platform operations, MySQL is the durable source of truth for:

```text
City Structure
Zones
Wards
Representation
Employees
Departments
Teams
Assignments
Posting History
```

Do not make Redis authoritative for these relationships.

---

# 152. GOVERNANCE UI MUST BE NON-TECHNICAL

Platform Administrator should see:

```text
Ward 5
বর্তমান দায়িত্বপ্রাপ্ত:
Officer X

সংরক্ষিত মহিলা কাউন্সিলর:
Councillor Y
```

Do not show:

```text
representation_assignment_id = 392
scope_type = ward
pivot relation
```

---

# 153. EMPLOYEE UI MUST BE NON-TECHNICAL

Show:

```text
নাম
পদবি
বিভাগ
অঞ্চল
ওয়ার্ড
বর্তমান দায়িত্ব
কার অধীনে কাজ করেন
যোগাযোগ
```

not database implementation details.

---

# 154. EMPTY / UNASSIGNED RESPONSIBILITY MUST BE VISIBLE

If a Ward/service has no responsible person/team, do not silently fail routing.

Show authorized administration:

```text
দায়িত্ব দেওয়া হয়নি
Responsibility Not Assigned
```

This should become an administrative attention item.

---

# 155. RESPONSIBILITY GAP DETECTION

The system should later detect:

```text
Ward with no active General Representative/Responsible Officer
Service with no operational owner
Team without Supervisor
Employee without current posting
Expired temporary responsibility without replacement
```

Platform Admin sees these in simple language.

---

# 156. PUBLIC CONTACT MUST SHOW ROLE, NOT JUST NAME

Example:

```text
মোঃ X
দায়িত্বপ্রাপ্ত কর্মকর্তা
ওয়ার্ড ১১ ও ১২
```

or:

```text
Ms. Y
সংরক্ষিত মহিলা কাউন্সিলর
Reserved Seat ...
```

The citizen should immediately understand why this person is shown.

---

# 157. PUBLIC CONTACT AVAILABILITY

Official contact information may include:

```text
In-App Message
Office Phone
Official Mobile where policy permits
Official Email
Office Location
Office Hours
```

Public visibility must be configurable per field.

---

# 158. OFFICE CONTACT MAY BE PREFERRED OVER PERSONAL OFFICER CONTACT

Where a person's direct official contact should not be public, show:

```text
Ward Office
Zone Office
Department Office
Mayor/Admin Office
```

instead.

---

# 159. ORGANIZATION SEARCH

Authorized internal users should be able to search:

```text
Person
Designation
Department
Ward
Zone
Team
Representative
Employee ID
```

Search results must respect permissions.

---

# 160. PUBLIC AREA SEARCH

Citizen should be able to search:

```text
Ward number
Area name
Current location
```

and reach:

```text
Ward Profile
Responsible Services
Official Contacts
Representatives
Complaint Statistics
```

---

# 161. NO IMPLEMENTATION DURING INGESTION

During Specification Part 2 ingestion:

Do NOT:

```text
Create database migrations
Implement employee screens
Build governance UI
Create Flutter screens
Implement routing
Create representative accounts
```

Only append this specification and log genuine conflicts/ambiguities.

---

# 162. PART 2 SUMMARY — NON-NEGOTIABLE RULES

The following are hard requirements:

```text
MCC initial structure:
3 Zones
33 General Wards
11 Reserved Seats.

Zones and Wards are configurable.

Initial Zone-Ward mapping must be stored as data, not hardcoded logic.

Support Mayor and Administrator as separate governance types.

Support elected General Ward Councillor.

When elected councillor is unavailable, support Responsible Officer.

One Responsible Officer may cover one or multiple Wards.

Support elected Reserved Women Councillor.

Current MCC reserved representation is one councillor for three General Wards.

Exact reserved-seat Ward groups must remain configurable and must not be invented.

A Ward can have both General representation and Reserved Women representation.

All governance assignments are time-bound and historically auditable.

Support acting and temporary responsibility.

All verified City Corporation employees/workers must be manageable in the workforce directory.

Employee profile and user login account are separate concepts.

Not every employee requires a login.

Employees may cover multiple Wards/Zones/services.

Maintain posting and transfer history.

Support Teams.

Support employee skills.

Support official public employee/representative profiles.

Never expose private employee data publicly by default.

Citizens may communicate through official in-app channels.

Complaint-linked messaging must stay attached to the complaint.

Do not create unrestricted random personal chat.

Provide “Who Is Responsible?” public functionality.

Provide Ward, Zone and Department public profiles.

Platform Admin organizational management must be understandable by non-technical staff.

Never invent real MCC workforce, representative, contact or GIS data.

No implementation during specification ingestion.
```

---

# END OF SPECIFICATION PART 2

---

# SPECIFICATION PART 3

## Complaint Lifecycle, Categories, Automatic Routing, Field Operations, Deadline, Overdue, Reopen and Resolution Quality

This is **Specification Part 3** of the complete master specification for the Mymensingh City Citizen Service Platform.

---

# 163. COMPLAINT MANAGEMENT IS A CORE PLATFORM MODULE

Complaint handling is one of the main operational foundations of the platform.

The complaint system must connect:

```text
Citizen
   ↓
Problem
   ↓
Location
   ↓
Ward / Zone
   ↓
Service Responsibility
   ↓
Responsible Supervisor / Unit
   ↓
Field Team / Worker
   ↓
Work
   ↓
Evidence
   ↓
Verification
   ↓
Citizen Confirmation
   ↓
Resolution
```

The system must create accountability while keeping the workflow extremely simple.

---

# 164. CITIZEN MUST NOT UNDERSTAND INTERNAL BUREAUCRACY

A citizen must never be required to understand:

```text
Department
Service Unit
Internal Officer
Supervisor
Routing Rule
Zone Administration
SLA
Internal Status
Escalation
```

The citizen reports the problem.

The system determines the administrative responsibility.

---

# 165. PRIMARY CITIZEN COMPLAINT ACTION

The main public/citizen call-to-action is:

```text
সমস্যা জানান
Report a Problem
```

This must remain one of the most prominent actions in the citizen interface.

---

# 166. NORMAL COMPLAINT SUBMISSION TARGET

A normal complaint should usually require approximately:

```text
3–4 primary screens
```

and should normally be submit-able in approximately:

```text
1 minute
```

Do not create unnecessary mandatory fields.

---

# 167. NORMAL COMPLAINT FLOW

Preferred citizen flow:

```text
1. সমস্যা নির্বাচন / বলুন
   Choose or describe the problem

2. জায়গা নিশ্চিত করুন
   Confirm location

3. ছবি / সংক্ষিপ্ত তথ্য দিন
   Add photo / short details

4. অভিযোগ পাঠান
   Submit
```

Do not require a department selection.

Do not require an officer selection.

---

# 168. CITIZEN AUTHENTICATION REQUIREMENT

Normal citizen complaint creation should support:

```text
Phone Number
OTP Verification
```

Do not require NID for a normal civic complaint.

Do not require email.

A citizen may optionally maintain:

```text
Name
Profile Photo
Preferred Language
Saved Address/Area
Notification Preference
```

where appropriate.

Detailed authentication/security rules will be finalized in later specification sections.

---

# 169. COMPLAINT PUBLIC IDENTIFIER

Every complaint must receive a human-friendly, unique public complaint number.

Example format:

```text
MCC-260826-01842
```

The exact formatting may be refined later.

Do NOT expose the database auto-increment ID as the only public complaint identifier.

---

# 170. COMPLAINT CORE DATA

A complaint should conceptually support:

```text
Internal ID
Public Complaint Number
Citizen
Category
Subcategory
Description
Location
Ward
Zone
Service Responsibility
Current Responsible Unit
Current Responsible Person/Team
Priority
Operational Classification
Current Internal Status
Citizen-facing Status
Submitted At
Deadline
Completion Attempts
Reopen Count
Evidence
Communication
Created At
Updated At
```

Exact schema will be designed after specification ingestion.

---

# 171. COMPLAINT LOCATION

A physical complaint should support:

```text
Latitude
Longitude
Approximate Address
Ward
Zone
Area / Landmark
Citizen-corrected map pin
```

Location must remain editable during submission before final confirmation.

---

# 172. AUTOMATIC WARD DETECTION

When verified Ward boundary data exists:

```text
GPS / Map Point
   ↓
Ward Polygon
   ↓
Ward
   ↓
Zone
```

The system should automatically identify the Ward.

If reliable boundary data does NOT exist:

```text
Location
+
Suggested Ward
+
Citizen / Operator confirmation
```

Use fallback rather than inventing boundaries.

---

# 173. COMPLAINT DESCRIPTION

Citizen should be able to provide:

```text
Short Text
Photo
Optional Additional Information
```

Future support may include:

```text
Voice Complaint
Video
```

but these are not required for the core complaint flow.

---

# 174. ORIGINAL CITIZEN STATEMENT MUST BE PRESERVED

Never replace the citizen's original complaint text.

If the platform later creates:

```text
Translation
Summary
Category Suggestion
AI-assisted interpretation
```

store that separately.

---

# 175. TOP-LEVEL COMPLAINT CATEGORY MODEL

Complaint categories and subcategories must be configurable.

Do not hardcode routing in large PHP switch statements.

The following categories are the initial product specification.

---

# 176. CATEGORY A — CLEANLINESS & WASTE

Initial subcategories should include:

```text
Garbage Pile
Missed Household Waste Collection
Overflowing Waste Bin
Road Not Swept
Market Waste
Illegal Dumping
Dead Animal Removal
Waste Burning
Construction Debris
Garbage Vehicle Did Not Arrive
Bad Smell From Waste
Clinical / Medical Waste Concern
Damaged Municipal Waste Bin
Other Cleanliness Issue
```

Bangla labels must be provided in the bilingual configuration.

---

# 177. CATEGORY B — MOSQUITO & PUBLIC HEALTH

Initial subcategories should include:

```text
Severe Mosquito Problem
Fogging Needed
Larvicide Treatment Needed
Standing Water
Possible Dengue Breeding Site
Water in Abandoned Tyre / Container
Dirty Stagnant Pond / Water
Unsanitary Public Place
Dirty Public Toilet
Market Hygiene Problem
Food Hygiene Concern
Slaughterhouse Sanitation
Septic / Waste Overflow
Other Public Health Issue
```

Mosquito complaints are an important first-class civic complaint type.

---

# 178. CATEGORY C — DRAINAGE & WATERLOGGING

Initial subcategories:

```text
Blocked Drain
Drain Full of Waste
Drain Overflow
Missing Drain Cover
Broken Drain Cover
Water Not Draining
Local Waterlogging
Bad Smell From Drain
Blocked Drain Outlet
Canal Blockage
Drain / Canal Encroachment Concern
Other Drainage Issue
```

---

# 179. CATEGORY D — ROADS & FOOTPATHS

Initial subcategories:

```text
Small Pothole
Large Pothole
Damaged Road
Broken Footpath
Missing Manhole Cover
Damaged Manhole
Road Obstruction
Construction Material on Road
Damaged Road Sign
Damaged Speed Breaker
Pedestrian Obstruction
Other Road Issue
```

---

# 180. CATEGORY E — STREET LIGHT & MUNICIPAL ELECTRICAL

Initial subcategories:

```text
Streetlight Off
Multiple Streetlights Off
Streetlight On During Daytime
Damaged Light Pole
Exposed Municipal Electrical Wire
Open Electrical Box
Park Light Broken
Market Light Broken
Other Municipal Electrical Issue
```

---

# 181. CATEGORY F — WATER SUPPLY

Initial subcategories:

```text
No Water
Low Water Pressure
Dirty Water
Bad-Smelling Water
Water Line Leakage
Pipe Burst
Water Wastage
Public Water Point Broken
Suspected Illegal Municipal Connection
Other Water Issue
```

---

# 182. CATEGORY G — ENCROACHMENT & URBAN ORDER

Initial subcategories:

```text
Footpath Occupation
Road Occupation
Unauthorized Roadside Stall
Drain / Canal Occupation
Illegal Municipal Space Occupation
Unauthorized Banner
Unauthorized Billboard
Construction Material Blocking Road
Parking Obstruction
Market Access Obstruction
Other Urban Order Issue
```

---

# 183. CATEGORY H — PARKS, MARKETS, TERMINALS & PUBLIC ASSETS

Initial subcategories:

```text
Dirty Park
Broken Bench
Playground Damage
Public Toilet Problem
Bus Terminal Cleanliness
Market Facility Problem
Damaged Public Waste Bin
Damaged Municipal Property
Other Public Asset Problem
```

---

# 184. CATEGORY I — ENVIRONMENT & TREES

Initial subcategories:

```text
Fallen Tree
Dangerous Tree
Tree Branch Obstruction
Smoke Pollution
Waste Burning
Noise Concern
Waterbody Pollution
Other Environmental Issue
```

---

# 185. CATEGORY J — CIVIC / ADMINISTRATIVE SERVICES

Initial subcategories:

```text
Birth Registration Delay
Death Registration Delay
Trade Licence Delay
Holding Tax Problem
Water Bill Problem
Service Counter Delay
Application Pending Too Long
Responsible Staff Unavailable
Incorrect Municipal Information
Other Administrative Service Issue
```

---

# 186. CATEGORY K — URGENT MUNICIPAL HAZARD

Initial subcategories:

```text
Open Dangerous Manhole
Dangerous Exposed Electrical Cable
Collapsed Tree Blocking Road
Dangerous Municipal Structure
Serious Road Obstruction
Other Immediate Civic Hazard
```

---

# 187. CATEGORY L — OTHER

Provide:

```text
Other Problem
```

with a simple description.

Unknown/unclear complaints may go to a controlled review queue.

Do not reject a citizen merely because they could not identify the perfect category.

---

# 188. CATEGORY SEARCH

Citizen should be able to:

```text
Browse category
Search problem
Choose from common problems
```

The interface should use citizen language.

Example:

```text
মশা
ময়লা
ড্রেন
সড়কবাতি
পানি
রাস্তা
```

not internal department terminology.

---

# 189. COMMON PROBLEM SHORTCUTS

Citizen home may show common high-frequency options such as:

```text
ময়লা
Mosquito
Drain
Road
Street Light
Water
```

The exact set should remain configurable.

---

# 190. OPERATIONAL CLASSIFICATION

Complaint Category and Operational Classification are separate concepts.

Use internal classification such as:

```text
Quick Action
Maintenance Required
Technical Assessment Required
Project Required
External Agency Referral
Administrative Service
```

Citizen does not need to select this.

---

# 191. QUICK ACTION

Quick Action refers to complaints often solvable with existing operational manpower/resources.

Examples:

```text
Garbage Pickup
Street Sweeping
Mosquito Control
Drain Cleaning
Dead Animal Removal
Public Toilet Cleaning
Minor Streetlight Work
Fallen Branch Removal
```

These complaints should avoid unnecessary senior approvals.

---

# 192. MAINTENANCE REQUIRED

Use when the problem needs ordinary repair work.

Examples may include:

```text
Minor Road Repair
Drain Cover Repair
Streetlight Hardware Repair
Water Pipe Repair
Public Asset Repair
```

---

# 193. TECHNICAL ASSESSMENT REQUIRED

Use when field staff cannot reliably determine the required action.

Example:

```text
Repeated Waterlogging
Structural Drain Damage
Road Collapse
Complex Electrical Hazard
```

The citizen sees simple language.

Do not expose the internal classification unless useful.

---

# 194. PROJECT REQUIRED

Some problems cannot be permanently solved through routine operations.

Example:

```text
Major Drain Reconstruction
Large Road Reconstruction
New Infrastructure
Structural Civil Work
```

Do NOT close the citizen complaint merely because:

```text
Budget Required
Project Required
```

Support long-term resolution tracking.

---

# 195. PROJECT-REQUIRED CITIZEN STATUS

Citizen may see a message such as:

```text
স্থায়ী সমাধানের জন্য প্রকৌশলগত / প্রকল্পভিত্তিক কাজ প্রয়োজন।
A permanent solution requires engineering/project work.
```

Possible long-term milestones:

```text
Assessment
Estimate
Approval
Scheduled
Work Started
Completed
```

Exact project-management depth may be implemented later.

---

# 196. EXTERNAL AGENCY REFERRAL

If the issue is outside MCC jurisdiction:

Do not pretend MCC resolved it.

Support:

```text
Referred to Relevant Agency
```

Record:

```text
Agency
Reason
Date
Reference
```

Citizen receives a clear explanation.

---

# 197. PRIORITY MODEL

Support configurable internal priorities:

```text
P1 Critical
P2 High
P3 Normal
P4 Low
```

Citizen does not need to understand technical priority codes.

---

# 198. PRIORITY EXAMPLES

Illustrative examples:

```text
Open Dangerous Manhole → P1
Exposed Dangerous Wire → P1
Major Pipe Burst → P1
Large Garbage Accumulation → P2
Blocked Drain → P2 or P3 depending severity
Ordinary Streetlight Fault → P3
```

These are examples.

The final priority rules must remain configurable.

---

# 199. PRIORITY OVERRIDE

Authorized administration may change priority when justified.

Every priority change must record:

```text
Who
When
Previous Priority
New Priority
Reason
```

Do not allow silent priority manipulation.

---

# 200. AUTOMATIC ROUTING ENGINE

The system must automatically determine responsibility using configurable data.

Possible routing inputs:

```text
Category
Subcategory
Ward
Zone
Service Unit
Operational Classification
Priority
Active Responsibility
Temporary Responsibility
```

---

# 201. ROUTING EXAMPLE — MOSQUITO

Example conceptual flow:

```text
Citizen reports mosquito problem
        ↓
Location detected
        ↓
Ward determined
        ↓
Mosquito / Health service identified
        ↓
Active responsible unit identified
        ↓
Active Supervisor / Team responsibility identified
        ↓
Complaint assigned
```

Citizen does not choose any of those administrative layers.

---

# 202. ROUTING EXAMPLE — WASTE

```text
Garbage complaint
   ↓
Ward
   ↓
Waste Management Service
   ↓
Responsible Supervisor / Team
```

If a temporary acting supervisor exists, routing should honor the active temporary responsibility.

---

# 203. NO HARD-CODED ROUTING CHAINS

Do NOT write logic such as:

```text
if category = mosquito and ward = 19
assign user 392
```

Use configurable responsibility/routing data.

---

# 204. ROUTING FALLBACK

If the system cannot determine a valid owner:

Status internally:

```text
routing_review_required
```

Authorized administration sees:

```text
দায়িত্ব নির্ধারণ প্রয়োজন
Responsibility Needs Assignment
```

Do not silently drop the complaint.

---

# 205. RESPONSIBILITY GAP ALERT

Routing should recognize situations such as:

```text
No active Supervisor
No active Team
Expired temporary responsibility
No service owner
```

These become administrative attention items.

---

# 206. ONE CLEAR ACCOUNTABLE OWNER

Every active complaint should have a clear current accountable owner/unit.

Conceptually:

```text
Current Responsible Unit
Current Responsible Person/Role
Execution Team
```

Do not create endless undefined forwarding.

---

# 207. RESPONSIBILITY VS EXECUTION

Example:

```text
Accountable Owner:
Ward 19 Waste Supervisor

Execution:
Cleaning Team A
```

The team may perform the task, while the Supervisor remains responsible for operational oversight.

---

# 208. OWNERSHIP HISTORY

When responsibility changes, preserve history:

```text
Previous Owner
New Owner
Reason
Changed By
Changed At
```

Never overwrite accountability history.

---

# 209. WRONG RESPONSIBILITY / TRANSFER

A responsible employee should have a simple action such as:

```text
ভুল দায়িত্বে এসেছে
Wrong Responsibility
```

The system may suggest the correct service/unit.

A transfer must record:

```text
From
To
Reason
Time
Actor
```

Do not create repeated manual forwarding chains.

---

# 210. CROSS-DEPARTMENT SUPPORT

Some complaints need more than one department/service.

Support:

```text
Primary Accountable Unit
Supporting Unit(s)
Linked Operational Tasks
Shared Evidence
```

The citizen should still see one understandable complaint case.

---

# 211. COMPLAINT INTERNAL STATE MACHINE

The backend must use one centralized complaint state machine.

Do not scatter arbitrary status changes across controllers.

Potential internal states include:

```text
submitted
review_required
routed
assigned
accepted
in_progress
work_completed
verification_required
awaiting_citizen_confirmation
closed
needs_more_work
transferred
project_required
referred_external
duplicate_linked
rejected
cancelled
```

The exact final transition matrix must be created after specification synthesis.

---

# 212. VALID STATE TRANSITIONS

The final architecture must explicitly define which transitions are valid.

Examples:

```text
submitted → routed
routed → assigned
assigned → in_progress
in_progress → work_completed
work_completed → verification_required
verification_required → awaiting_citizen_confirmation
awaiting_citizen_confirmation → closed
awaiting_citizen_confirmation → needs_more_work
needs_more_work → assigned / in_progress
```

Do not allow arbitrary:

```text
submitted → closed
```

without a legitimate defined path.

---

# 213. CITIZEN-FACING STATUS MUST REMAIN SIMPLE

The citizen should not see every internal state.

Primary citizen statuses:

```text
অভিযোগ পেয়েছি
Complaint Received

দায়িত্ব দেওয়া হয়েছে
Assigned

কাজ চলছে
Work In Progress

কাজ সম্পন্ন হয়েছে
Work Completed

আপনার নিশ্চিতকরণ প্রয়োজন
Confirmation Needed

সমাধান হয়েছে
Resolved

আবার কাজ প্রয়োজন
Needs More Work
```

---

# 214. SIMPLE CITIZEN TIMELINE

Complaint detail should show a visual timeline such as:

```text
অভিযোগ পেয়েছি ✓
দায়িত্ব দেওয়া হয়েছে ✓
কাজ চলছে ●
কাজ সম্পন্ন ○
সমাধান ○
```

Do not expose administrative bureaucracy.

---

# 215. CURRENT RESPONSIBILITY FOR CITIZEN

Citizen may see a simple responsibility label such as:

```text
বর্তমানে দায়িত্বে:
ওয়ার্ড ১৯ পরিচ্ছন্নতা সেবা
```

or:

```text
Currently responsible:
Ward 19 Waste Service
```

Do not necessarily expose an individual employee's personal contact.

---

# 216. COMPLAINT DEADLINE MODEL

Each complaint/service type should support a configurable service deadline.

This replaces technical SLA language in ordinary interfaces.

Internally the architecture may maintain:

```text
deadline_start
deadline_due
deadline_status
```

The normal user sees:

```text
সমাধানের নির্ধারিত সময়
Expected Service Time
```

where appropriate.

---

# 217. DEADLINE CONFIGURATION

Deadline may depend on:

```text
Category
Subcategory
Priority
Operational Classification
Service Unit
```

Do not hardcode every deadline into application code.

---

# 218. EXAMPLE DEADLINES

The following are illustrative initial examples only:

```text
Dead Animal Removal — 4 hours
Critical Open Manhole — 4 hours
Large Garbage Pile — 8 hours
Ordinary Garbage — 12 hours
Water Leakage — 12 hours
Blocked Drain — 24 hours
Streetlight — 48 hours
```

These are NOT permanent official MCC commitments unless later verified/approved.

They must be configurable.

---

# 219. DEADLINE STATUS

Internal deadline state may include:

```text
On Time
Due Soon
Overdue
Completed On Time
Completed Late
```

Citizen-facing language must remain simple.

---

# 220. DUE-SOON WARNING

Responsible operational staff may see:

```text
আর ১ ঘণ্টা বাকি
1 hour remaining
```

or another useful countdown.

Do not overwhelm staff with unnecessary alerts.

---

# 221. FIRST DEADLINE FAILURE — IMMEDIATE EXECUTIVE VISIBILITY

This is a HARD requirement.

When a complaint passes its configured deadline without appropriate completion:

```text
First deadline failure
        ↓
Mayor / Administrator Attention Required
```

Do NOT wait for:

```text
Second failure
Third failure
Escalation Level 3
```

The Mayor/Administrator should know from the FIRST missed deadline.

---

# 222. NO MULTI-LEVEL ESCALATION LADDER

Do NOT create a user-facing model such as:

```text
Escalation Level 1
Escalation Level 2
Escalation Level 3
Escalation Level 4
```

The project owner explicitly wants the workflow simpler.

---

# 223. VISIBILITY, NOT COMPLAINT PING-PONG

Department Head, Zone Officer, CEO, Mayor/Administrator may all see relevant overdue items based on their authorization.

But the complaint should not physically move:

```text
A → B → C → D → Mayor
```

just to create visibility.

The accountable operational owner remains clear.

Senior administration sees the issue.

---

# 224. MAYOR / ADMINISTRATOR OVERDUE ATTENTION

Mayor/Admin dashboard should aggregate:

```text
সময় পেরিয়েছে
Overdue
```

items.

Example:

```text
Attention Required

Urgent Issues — 3
Overdue — 28
Citizen Says Not Resolved — 7
Repeated Problems — 5
```

Detailed executive dashboard requirements will be specified in Part 4.

---

# 225. NO MAYOR NOTIFICATION FLOOD

Every first missed deadline becomes executive-visible.

However, do not send an intrusive popup for every normal overdue complaint.

Use:

```text
Dashboard aggregation
Priority grouping
Critical push only where justified
```

---

# 226. RESPONSIBLE STAFF AFTER DEADLINE

When overdue, responsible staff should see:

```text
এই কাজের সময় পেরিয়েছে।
This work is overdue.
```

Primary actions:

```text
কাজ সম্পন্ন করুন
Complete Work

কেন সম্ভব হয়নি?
Why Could It Not Be Completed?
```

---

# 227. SIMPLE FAILURE REASONS

If work could not be completed, offer simple structured reasons:

```text
Additional Manpower Needed
Vehicle Needed
Equipment Needed
Another Department Needed
Major Repair Required
Access Problem
Location Not Found
Safety Problem
Other
```

Do not require a long written report.

Optional short note may be allowed.

---

# 228. FIELD TASK MODEL

Operational execution should use a field task concept.

A complaint may create one or more field tasks.

Each task may contain:

```text
Complaint
Task Type
Assigned Team / Worker
Supervisor
Location
Priority
Instructions
Started At
Completed At
Evidence
Result
```

---

# 229. FIELD WORKER HOME

Field Worker home must be extremely simple.

Main view:

```text
আমার আজকের কাজ
My Tasks Today
```

Show:

```text
Current Task
Next Tasks
Completed Today
```

Avoid complicated menus.

---

# 230. FIELD TASK CARD

A normal field task card should contain only practical information:

```text
Problem
Location
Distance
Map
Priority if important
Short Instruction
```

Primary action:

```text
কাজ শুরু করুন
Start Work
```

---

# 231. FIELD TASK NORMAL FLOW

Normal field flow:

```text
Open Task
   ↓
Start Work
   ↓
Do Work
   ↓
Capture Evidence
   ↓
Complete
```

This should require very few interactions.

---

# 232. CANNOT COMPLETE

Worker can select:

```text
সমাধান করা যায়নি
Cannot Complete
```

Then choose a reason.

Do not force free-text reporting for routine failure.

---

# 233. FIELD EVIDENCE

For appropriate physical complaints, support:

```text
Before Photo
After Photo
GPS
Device Timestamp
Server Timestamp
Worker / Team Identity
Optional Short Completion Note
```

Evidence requirements may vary by complaint type.

---

# 234. LIVE CAMERA POLICY

For selected complaint types, administrators may configure:

```text
Live Camera Capture Required
```

instead of relying only on gallery uploads.

This should not be mandatory for every category.

---

# 235. ORIGINAL EVIDENCE

Original field evidence must remain stored securely according to evidence retention rules.

Do not overwrite original evidence with public derivatives.

---

# 236. WORKER CANNOT PERMANENTLY RESOLVE A CITIZEN COMPLAINT ALONE

A field worker clicking:

```text
Complete
```

means:

```text
Work Attempt Completed
```

It does NOT automatically mean:

```text
Citizen Complaint Permanently Resolved
```

The system must support verification.

---

# 237. SUPERVISOR HOME

Supervisor dashboard:

```text
নতুন
New

চলমান
Ongoing

আজ শেষ করতে হবে
Due Today

সময় পেরিয়েছে
Overdue

সম্পন্ন
Completed
```

This must remain simple.

---

# 238. SUPERVISOR ASSIGNMENT

A Supervisor may assign a complaint/task to:

```text
Team
Individual Worker
```

Assignment should be easy.

Show simple workload information such as:

```text
Team A — Available
Team B — 3 Active Tasks
Team C — Available
```

---

# 239. SUPERVISOR VERIFICATION

After field work:

```text
Worker / Team completes task
        ↓
Supervisor reviews evidence
        ↓
Accept
OR
Return for More Work
```

Do not require excessive approval steps.

---

# 240. SUPERVISOR RETURN

If evidence/work is inadequate:

```text
আরও কাজ প্রয়োজন
More Work Required
```

Record:

```text
Reason
Time
Supervisor
```

---

# 241. CITIZEN CONFIRMATION

After verified completion, notify the citizen.

Ask:

```text
আপনার সমস্যাটি কি সমাধান হয়েছে?
Has your problem been resolved?
```

Options:

```text
সমাধান হয়েছে
Resolved

আংশিক সমাধান হয়েছে
Partially Resolved

সমাধান হয়নি
Not Resolved
```

---

# 242. CITIZEN CONFIRMATION SHOULD BE EASY

Citizen should not need to write another full complaint.

If unresolved, allow a short reason:

```text
সমস্যা এখনও আছে
Problem Still Exists

আংশিক হয়েছে
Partially Resolved

কিছুদিন পর আবার হয়েছে
Problem Returned

ভুল জায়গায় কাজ হয়েছে
Wrong Location Worked

অন্যান্য
Other
```

Optional:

```text
Photo
Short Note
```

---

# 243. FIRST CITIZEN REOPEN — IMMEDIATE EXECUTIVE VISIBILITY

This is a HARD requirement.

The FIRST time a citizen says the complaint was not resolved:

```text
Needs More Work
        ↓
Mayor / Administrator Attention Required
```

Do NOT wait for a third reopen.

---

# 244. CITIZEN-FACING REOPEN LANGUAGE

Do not show:

```text
REOPENED LEVEL 1
REOPENED LEVEL 2
```

Citizen sees:

```text
আবার কাজ প্রয়োজন
Needs More Work
```

---

# 245. STAFF-FACING REOPEN LANGUAGE

Responsible staff may see:

```text
নাগরিক জানিয়েছেন সমস্যাটি সমাধান হয়নি।
Citizen reported that the problem is not resolved.
```

Keep it understandable.

---

# 246. INTERNAL REOPEN COUNT

Backend must track:

```text
reopen_count
completion_attempts
```

without exposing technical level terminology to ordinary users.

---

# 247. REPEATED FAILURE

If a complaint repeatedly fails, show:

```text
এই সমস্যাটি আগে ২ বার সমাধানের চেষ্টা করা হয়েছে।
This problem has already had 2 resolution attempts.
```

or:

```text
বারবার সমাধান ব্যর্থ হচ্ছে।
Repeated resolution failure.
```

---

# 248. REPEATED FAILURE PRIORITY

Repeated failure should rise higher in executive attention.

Do not require staff to manually manage escalation levels.

---

# 249. COMPLAINT AGE NEVER RESETS

If a complaint was submitted 7 days ago and reopened today:

Store/display internally:

```text
Total Case Age = 7 days
Current Work Cycle Age = ...
```

Do NOT make the complaint look newly created.

---

# 250. DEADLINE PERFORMANCE NEVER RESETS

Track:

```text
Original Deadline
Original On-Time / Late Result
Current Cycle
Total Case Age
Completion Attempts
Reopen Count
```

A reopen must never erase an earlier missed deadline.

---

# 251. ANTI-GAMING REQUIREMENT

Do not allow staff to improve statistics by:

```text
Marking complete repeatedly
Closing without evidence where evidence is required
Changing category merely to obtain a longer deadline
Transferring repeatedly
Resetting case age
Resetting deadline
```

Track all such actions.

---

# 252. CLOSED / RESOLVED DEFINITIONS

The system must distinguish:

```text
Work Completed
Supervisor Verified
Citizen Confirmed
Administratively Closed
```

Do not treat these as identical events.

---

# 253. RESOLUTION METRIC

For reporting, define clearly what:

```text
Resolved
```

means.

Recommended baseline:

```text
Supervisor Verified Completion
```

with a separate metric:

```text
Citizen Confirmed Resolution Rate
```

Final metric definitions will be reviewed during synthesis.

---

# 254. NO RESPONSE FROM CITIZEN

Citizen confirmation cannot remain pending forever.

After a configurable period:

The system may allow:

```text
Administrative Auto-Close
```

but must record:

```text
Citizen Did Not Respond
Auto-close Date
Responsible Rule
```

Do not pretend citizen explicitly confirmed.

---

# 255. CITIZEN REOPEN WINDOW

Create a configurable period within which a citizen may easily report that the same complaint remains unresolved.

The exact duration should remain configurable.

---

# 256. PROBLEM RETURNS LATER

If the problem genuinely returns later:

```text
Same location
Same problem
```

the platform should be able to:

```text
Create a new complaint
+
Link it to previous complaint(s)
```

rather than destroying history.

---

# 257. RECURRING PROBLEM DETECTION

Track repeated problems at the same or nearby location.

Signals may include:

```text
Same Category
Nearby Location
Repeated Time Pattern
Prior Complaint History
```

Example:

```text
Same Drain
January
February
March
April
```

Flag internally:

```text
Recurring Problem
```

---

# 258. ROOT-CAUSE ATTENTION

Repeated quick fixes may indicate a structural problem.

Example:

```text
Drain cleaned
Problem returned
Drain cleaned again
Problem returned again
```

System should help administration recognize:

```text
Potential Permanent / Project Solution Required
```

Do not require AI for this.

---

# 259. DUPLICATE COMPLAINT DETECTION

Prevent unnecessary duplicate operational tasks for one visible issue.

Initial deterministic signals:

```text
Same Category
Geographic Proximity
Recent Time Window
Same Ward
Existing Open Complaint
```

---

# 260. DUPLICATE SUGGESTION

Citizen may see:

```text
এই সমস্যাটি ইতোমধ্যে জানানো হয়েছে।
This issue may already have been reported.
```

Then:

```text
আমিও এই সমস্যায় আক্রান্ত
I Am Also Affected
```

---

# 261. CITIZEN SUPPORT / +1

Track the number of citizens affected by an existing complaint.

Example:

```text
এই সমস্যায় ৭৮ জন নাগরিক প্রভাবিত।
78 citizens are affected.
```

This may influence attention/prioritization.

---

# 262. DUPLICATE REVIEW

Do not automatically merge ambiguous complaints.

Allow review where confidence is insufficient.

Do not depend on AI.

---

# 263. SPAM / INVALID COMPLAINT REVIEW

Some complaints may need review because of:

```text
Spam
Abusive Content
Unclear Issue
Invalid Media
Likely Duplicate
Unknown Jurisdiction
Sensitive Content
```

Send only those cases to a small review/control queue.

Do not manually review every ordinary complaint.

---

# 264. REVIEW QUEUE ACTIONS

Simple actions:

```text
Approve
Duplicate
Need More Information
Refer
Reject
```

Rejection must require a reason.

---

# 265. REJECTION MUST BE EXPLAINED

Citizen should receive a human-readable explanation.

Do not use:

```text
Rejected
```

without reason.

---

# 266. CITIZEN FOLLOW-UP REQUEST

Citizen may have a simple action such as:

```text
ফলোআপ চাই
Request Follow-Up
```

when appropriate.

Rate-limit repeated requests to prevent spam.

A missed deadline must trigger administrative attention automatically even if the citizen does not press follow-up.

---

# 267. COMPLAINT-LINKED COMMUNICATION

The communication module defined in Part 2 must integrate with complaint handling.

Citizen can provide relevant updates.

Responsible authorized staff can respond.

Messages remain attached to the complaint.

---

# 268. NOTIFICATION EVENTS — CITIZEN

Citizen should receive only meaningful notifications.

Examples:

```text
Complaint Received
Important Information Needed
Work Started / Meaningful Action
Work Completed
Confirmation Required
Complaint Resolved
Needs More Work
External Referral
```

Do NOT notify the citizen for every internal administrative movement.

---

# 269. NOTIFICATION EVENTS — SUPERVISOR

Supervisor may receive:

```text
New Assigned Complaint
Urgent Complaint
Deadline Approaching
Complaint Overdue
Citizen Says Not Resolved
Worker Cannot Complete
Support Needed
```

---

# 270. NOTIFICATION EVENTS — MAYOR / ADMINISTRATOR

Mayor/Admin should not receive routine noise.

Executive attention should include:

```text
Critical Hazard
First Deadline Failure
First Citizen Reopen
Repeated Resolution Failure
Major Complaint Spike
Recurring Hotspot
Large Operational Backlog
```

Detailed executive presentation will be defined in Part 4.

---

# 271. PUBLIC COMPLAINT TRACKING

Citizen/public-safe complaint tracking should support:

```text
Complaint Number
Category
Ward
Approximate Area
Citizen-facing Status
Submitted Time
Last Meaningful Update
Responsible Municipal Service
```

Private information remains hidden.

---

# 272. PUBLIC TRACKING WITHOUT FULL ACCOUNT

A public-safe version of complaint tracking may be available using the complaint number.

Private complaint details require authentication/verification.

---

# 273. CITIZEN “MY COMPLAINTS”

Citizen portal should show:

```text
চলমান
Ongoing

সমাধান হয়েছে
Resolved

আবার কাজ প্রয়োজন
Needs More Work
```

Each card:

```text
Complaint Number
Category
Area
Status
Last Update
```

---

# 274. BEFORE / AFTER EVIDENCE FOR CITIZEN

Citizen may see relevant approved evidence showing work completion.

Do NOT automatically expose raw internal photos publicly.

Public evidence rules will be detailed later.

---

# 275. COMPLAINT COMMENTS / NOTES

Separate:

```text
Citizen-visible update
Internal operational note
Private administrative note
```

Do not expose internal notes to citizens accidentally.

---

# 276. TASK SUPPORT REQUEST

Field/Supervisor may request:

```text
Additional Staff
Vehicle
Equipment
Another Department
Technical Inspection
```

The request remains linked to the complaint/task.

---

# 277. SUPPORT REQUEST DOES NOT REMOVE ACCOUNTABILITY

Requesting another department or resource must not make the original complaint owner undefined.

Always preserve a clear accountable owner.

---

# 278. EMERGENCY / CRITICAL COMPLAINT

P1/Critical cases should be visually prominent.

Use:

```text
Text
Icon
Priority label
```

not only color.

Critical municipal hazards may justify immediate notifications to relevant senior users.

---

# 279. PUBLIC EMERGENCY DISCLAIMER

The civic complaint platform must not replace national emergency services.

For immediate threats to life/safety, the UI should provide appropriate emergency guidance/contact where configured.

Do not mislead citizens into thinking a civic complaint queue is the same as emergency response.

---

# 280. WORKER OFFLINE RESILIENCE

Field Worker mobile flow should tolerate weak internet.

Support practical pending operations:

```text
Cached Assigned Task
Start Work Pending Sync
Evidence Pending Upload
Completion Pending Sync
```

Do not implement a highly complex full offline multi-master system initially.

---

# 281. IDEMPOTENT MOBILE ACTIONS

Mobile retries must not accidentally create:

```text
Duplicate Complaint
Duplicate Completion
Duplicate Evidence Record
Duplicate Citizen Confirmation
```

Use idempotency design.

---

# 282. FIELD TIMESTAMPS

Store where relevant:

```text
Device Timestamp
Server Receipt Timestamp
```

Do not blindly trust client time as the only audit time.

---

# 283. COMPLAINT PERFORMANCE DATA

The platform should later support analysis of:

```text
Total Complaints
On-Time Resolution
Overdue
Median Resolution Time
Needs More Work
Citizen Confirmed
Completion Attempts
Recurring Issues
```

---

# 284. QUALITY IS MORE IMPORTANT THAN FAKE CLOSURE NUMBERS

Do not design staff performance around:

```text
How many complaints were marked closed
```

alone.

Quality signals must include:

```text
Deadline Performance
Citizen Confirmation
Needs More Work / Reopen Rate
Repeated Failure
Case Age
```

---

# 285. STAFF PERFORMANCE ANTI-ABUSE

Internal staff performance should not encourage:

```text
Premature completion
Invalid rejection
Unnecessary transfer
Changing category to manipulate deadline
Ignoring citizen reopen
```

Track these patterns.

---

# 286. NO PUBLIC INDIVIDUAL WORKER SHAMING

Public performance should primarily focus on:

```text
Institution
Ward
Zone
Department
Service
```

Do not create public “worst cleaner” leaderboards.

Individual staff performance may be internal according to permissions.

---

# 287. COMPLAINT HISTORY IS APPEND-ORIENTED

Maintain current state for fast queries.

Also maintain history for:

```text
Creation
Routing
Assignment
Responsibility Change
Work Start
Work Completion
Verification
Citizen Confirmation
Needs More Work
Deadline Failure
Transfer
Priority Change
Project Classification
External Referral
```

Do not overwrite history.

---

# 288. SAMPLE COMPLAINT HISTORY

Example:

```text
09:10 — Complaint Submitted
09:11 — Ward Identified
09:12 — Automatically Routed
09:18 — Supervisor Assigned Team
09:42 — Worker Started
10:30 — Worker Completed
10:40 — Supervisor Verified
10:45 — Citizen Asked to Confirm
11:00 — Citizen: Not Resolved
11:00 — Needs More Work
11:00 — Mayor/Admin Attention Created
```

---

# 289. SIMPLE USER LANGUAGE FOR HISTORY

Citizen sees:

```text
অভিযোগ পেয়েছি
দায়িত্ব দেওয়া হয়েছে
কাজ চলছে
কাজ সম্পন্ন হয়েছে
আবার কাজ প্রয়োজন
```

Internal users may see more detail.

---

# 290. MAYOR / ADMINISTRATOR ATTENTION TRIGGERS FROM COMPLAINT SYSTEM

The complaint system must create executive attention signals for at least:

```text
Critical Hazard
First Missed Deadline
First Citizen Reopen
Repeated Failure
High Number of Citizens Affected
Recurring Problem
Major Complaint Spike
```

Detailed Mayor/Admin UX will be specified in Part 4.

---

# 291. NO MANUAL ESCALATION MANAGEMENT

Normal operational users must NOT have to manage:

```text
Escalation Level
Escalation Chain
Escalation Route Number
```

The system automatically creates required visibility.

---

# 292. OPERATIONAL OWNER REMAINS ACTIVE AFTER EXECUTIVE ATTENTION

When Mayor/Admin sees an overdue complaint:

The complaint does NOT automatically become “owned” by the Mayor.

The existing operational owner remains responsible unless an authorized responsibility change is explicitly made.

Executive attention is oversight.

---

# 293. MAYOR / ADMIN ACTION SHOULD NOT DESTROY WORKFLOW

Future executive actions may include:

```text
Ask for Action
Request Explanation
Request Inspection
Provide Support
Set Priority
```

They should create auditable instructions without corrupting normal task history.

---

# 294. PUBLIC STATISTICS MUST NOT HIDE REOPENED CASES

Public statistics must eventually distinguish:

```text
Received
Work Completed
Resolved
Overdue
Citizen Confirmed
Needs More Work
```

Do not count every “work completed” event as permanent resolution.

Detailed public dashboard methodology will be specified in Part 4.

---

# 295. DEADLINE MISSED BUT COMPLETED LATER

If a complaint is completed after its deadline:

It may become:

```text
Completed Late
```

Do not rewrite it as:

```text
Completed On Time
```

---

# 296. REOPEN AFTER LATE COMPLETION

If a complaint was late and then reopened:

Preserve both facts:

```text
Original Deadline Missed
Citizen Reopened
```

Both remain part of accountability data.

---

# 297. TASK / COMPLAINT DIFFERENCE

A complaint is the citizen-facing case.

A task is operational work performed to address it.

One complaint may require:

```text
One Task
or
Multiple Linked Tasks
```

Do not expose task complexity unnecessarily to citizen.

---

# 298. COMPLAINT CLOSURE MUST NOT DELETE OPEN TASK HISTORY

When complaint is closed:

All previous field tasks, failed attempts and evidence remain historically preserved.

---

# 299. COMPLAINT CANCELLATION

Citizen/admin cancellation may be supported where legitimate.

Store:

```text
Who Cancelled
Reason
When
```

Cancellation is not deletion.

---

# 300. COMPLAINT DELETION

Normal users must NOT permanently delete civic complaint history.

Any legal/technical deletion policy must be handled separately with audit and retention rules.

---

# 301. QUICK ACTION FAST LANE

Routine operational complaints should flow approximately:

```text
Citizen
   ↓
Automatic Routing
   ↓
Supervisor
   ↓
Team / Worker
   ↓
Evidence
   ↓
Verification
```

No unnecessary CEO/Department Head approval merely to perform routine service.

---

# 302. COMPLEX ISSUE FLOW

Complex issue may flow:

```text
Citizen
   ↓
Responsible Unit
   ↓
Inspection / Assessment
   ↓
Maintenance / Project / External Agency
```

Citizen still sees a simple understandable case.

---

# 303. USER SHOULD NOT MANAGE WORKFLOW STATES

Never present staff with a dropdown containing every technical complaint state.

Show actions.

Example:

```text
Start Work
Complete Work
Need Support
Wrong Responsibility
More Work Required
```

The state machine changes automatically based on valid actions.

---

# 304. ACTION-DRIVEN DESIGN

Prefer:

```text
What do you need to do?
```

over:

```text
Select next status
```

This is a core UX rule.

---

# 305. ROUTING ADMINISTRATION MUST ALSO BE SIMPLE

Platform Administrator should manage routing through understandable questions such as:

```text
এই ধরনের অভিযোগ কার কাছে যাবে?
Who receives this type of complaint?
```

and:

```text
এই ওয়ার্ডে মশার অভিযোগের দায়িত্বে কে?
Who handles mosquito complaints in this Ward?
```

Do not expose raw routing tables to ordinary Platform Admin users.

---

# 306. DEADLINE ADMINISTRATION MUST BE SIMPLE

Platform Admin should see:

```text
এই সেবার জন্য নির্ধারিত সময়
Expected Service Time
```

Example:

```text
ময়লা অপসারণ
12 hours
```

not technical SLA-engine configuration screens for normal use.

---

# 307. ROLE-SCOPED COMPLAINT VISIBILITY

Complaint access must respect:

```text
Role
Permission
Ward
Zone
Department
Service
Assignment
```

Detailed permission matrix will be finalized after synthesis.

---

# 308. CITIZEN PRIVACY WITH LOCATION

Public complaint display must avoid exposing sensitive exact residential location.

Use:

```text
Approximate Area
Ward
Public-safe Location
```

rather than exact home coordinates where privacy risk exists.

---

# 309. SENSITIVE COMPLAINT FLAG

Support an internal sensitivity flag.

Sensitive complaints should not automatically appear publicly.

Examples may include:

```text
Personally Identifiable Administrative Issue
Sensitive Individual Allegation
Private Property Details
Protected Attachment
```

---

# 310. NO AI DEPENDENCY FOR ROUTING

Automatic routing must work deterministically using configured administrative data.

AI may later suggest:

```text
Category
Duplicate
Summary
```

but the core routing system must function without AI.

---

# 311. NO IMPLEMENTATION DURING INGESTION

During Specification Part 3 ingestion:

Do NOT:

```text
Create complaint migrations
Implement complaint controllers
Build citizen complaint UI
Build supervisor UI
Build worker UI
Implement routing
Implement deadlines
Implement reopen
Implement Mayor/Admin attention
```

Only append the specification and log genuine conflicts/ambiguities.

---

# 312. PART 3 SUMMARY — NON-NEGOTIABLE RULES

The following are hard requirements:

```text
Citizen complaint workflow must remain extremely simple.

Citizen must not choose department/officer/supervisor.

Complaint categories are configurable.

Mosquito complaints are first-class complaints.

Support Cleanliness, Mosquito/Public Health, Drainage, Roads, Streetlights, Water, Encroachment, Public Assets, Environment, Civic Services, Urgent Hazards and Other.

Use operational classification:
Quick Action
Maintenance
Technical Assessment
Project Required
External Agency
Administrative Service.

Use configurable priority.

Use automatic routing based on category + geography + active responsibility.

No hard-coded user routing.

Every active complaint has a clear accountable owner.

Execution team and accountable owner may be different.

Use one centralized state machine.

Citizen-facing statuses remain simple.

Use configurable service deadlines.

The first missed deadline immediately becomes Mayor/Admin Attention Required.

Do NOT create multi-level escalation workflow.

Do NOT require complaint forwarding through every management layer.

First citizen “Not Resolved” immediately becomes Mayor/Admin Attention Required.

Citizen sees “Needs More Work”, not Reopen Level numbers.

Track reopen_count and completion_attempts internally.

Repeated failure increases attention priority.

Complaint age never resets.

Original deadline performance never resets.

Field Worker workflow must remain extremely simple.

Worker completion does not automatically permanently resolve complaint.

Support field evidence.

Supervisor verifies work.

Citizen confirms resolution.

Public statistics must not hide reopened/failed resolution cases.

Support duplicate detection without AI dependency.

Support “I am also affected”.

Support recurring problem detection.

Support project-required and external-agency paths.

Complaint history must be preserved.

Normal users cannot delete civic complaint history.

Operational UI must be action-driven, not technical-state driven.

No implementation during specification ingestion.
```

---

# END OF SPECIFICATION PART 3

---

# SPECIFICATION PART 4

## Executive Command Center, Administrative Dashboards, Super Admin Experience, Public Accountability, Communication, Privacy and Notifications

This is **Specification Part 4** of the complete master specification for the Mymensingh City Citizen Service Platform.

---

# 313. ROLE-AWARE ADMINISTRATION IS A CORE REQUIREMENT

The administration system must NOT be one giant dashboard where every logged-in user sees the same menus, tables, charts and controls.

After login, the system must adapt according to:

```text
Role
Permission
Scope
Current Responsibility
Language
```

Every role should immediately understand:

```text
What needs my attention?
What am I responsible for?
What can I do now?
```

Technical complexity must remain hidden.

---

# 314. SAME PLATFORM, DIFFERENT EXPERIENCE

The following users may all use the same core administration platform:

```text
Mayor
Administrator
Chief Executive Officer
Department Head
Department Officer
Zone Officer
Ward Officer
General Councillor
Reserved Women Councillor
Responsible Officer
Supervisor
Call Center Operator
Verification / Control Room Officer
Communications Officer
Data / Monitoring Officer
Platform Super Admin
Technical Super Admin
Auditor / Read-Only Oversight
```

However, each user should see only relevant navigation and actions.

---

# 315. ROLE HOME SCREEN PRINCIPLE

Every administrative home screen should prioritize:

```text
My Responsibility
Needs Attention
Today
Overdue / Failed Work
Primary Action
```

Do not begin with large generic record tables.

---

# 316. COMMON RESPONSIBILITY SUMMARY

Where appropriate, show a simple card such as:

```text
আপনার দায়িত্ব
Your Responsibility

Active Work
Needs Attention
Overdue
Completed Today
```

The values must be automatically scoped to the user.

---

# 317. MAYOR AND ADMINISTRATOR REMAIN SEPARATE ROLES

Mayor and Administrator must remain technically separate roles/governance assignments.

Do NOT create one permanently combined database role called:

```text
MayorAdministrator
```

They may receive similar executive dashboard capabilities through configurable permissions.

The public label must always reflect the real current governance position.

---

# 318. MAYOR / ADMINISTRATOR COMMAND CENTER

The Mayor/Administrator dashboard must be the strongest citywide oversight interface while also being one of the simplest interfaces in the platform.

The primary question is:

> শহরের কোথায় আমার নজর বা সিদ্ধান্ত প্রয়োজন?  
> Where does the city need my attention or decision?

---

# 319. MAYOR / ADMIN FIRST VIEW

The first viewport should contain approximately no more than six major KPIs.

Recommended primary metrics:

```text
আজ অভিযোগ
Complaints Today

সমাধান
Resolved

কাজ চলছে
In Progress

সময় পেরিয়েছে
Overdue

সমাধানের হার
Resolution Rate

নাগরিক সন্তুষ্টি
Citizen Satisfaction
```

Do not overwhelm the first screen.

---

# 320. MAYOR / ADMIN “ATTENTION REQUIRED”

Immediately after the primary metrics, show:

```text
আমার নজর প্রয়োজন
Attention Required
```

This is a core executive feature.

Main attention groups should include:

```text
Urgent Hazards
First Deadline Failures
Citizen Says Not Resolved
Repeated Resolution Failures
Recurring Problems
Major Complaint Spikes
Large Operational Backlogs
Responsibility Gaps
Important Citizen Messages
```

---

# 321. FIRST DEADLINE FAILURE MUST APPEAR HERE

As specified in Part 3:

The FIRST missed complaint deadline becomes visible in Mayor/Admin Attention Required.

Do NOT wait for multiple escalation levels.

---

# 322. FIRST CITIZEN “NOT RESOLVED” MUST APPEAR HERE

As specified in Part 3:

The FIRST citizen reopen / “Not Resolved” response becomes visible in Mayor/Admin Attention Required.

Do NOT wait for repeated failure before executive visibility.

---

# 323. EXECUTIVE ATTENTION IS VISIBILITY, NOT OWNERSHIP TRANSFER

When Mayor/Admin sees an overdue or reopened complaint:

The Mayor/Admin does NOT automatically become the operational complaint owner.

The existing operational owner remains responsible.

Executive oversight is separate from operational ownership.

---

# 324. EXECUTIVE ATTENTION GROUPING

Do not present hundreds of executive attention items as one endless list.

Group and prioritize intelligently.

Example:

```text
আমার নজর প্রয়োজন

জরুরি সমস্যা — 4
সময় পেরিয়েছে — 28
নাগরিক বলেছেন সমাধান হয়নি — 7
একই সমস্যা বারবার হচ্ছে — 5
```

Allow drill-down.

---

# 325. EXECUTIVE ALERT PRIORITY

Executive attention ranking may consider:

```text
Complaint Priority
Deadline Failure
Reopen Count
Number of Citizens Affected
Recurring Location
Safety Risk
Complaint Spike
Total Case Age
Operational Classification
```

Do not require the Mayor/Admin to configure the ranking algorithm manually.

---

# 326. MAYOR / ADMIN ACTIONS MUST USE HUMAN LANGUAGE

Primary executive actions may include:

```text
বিস্তারিত দেখুন
View Details

ব্যবস্থা নিতে বলুন
Ask for Action

সহায়তা দিন
Provide Support

কারণ জানতে চান
Request Explanation

পরিদর্শনের নির্দেশ দিন
Request Inspection

অগ্রাধিকার দিন
Set Priority
```

Do not expose technical actions such as:

```text
Invoke Escalation
Transition State
Override Owner ID
Edit Workflow State
Modify SLA Engine
```

---

# 327. “ASK FOR ACTION”

When Mayor/Admin chooses:

```text
ব্যবস্থা নিতে বলুন
Ask for Action
```

create an auditable executive instruction linked to:

```text
Complaint
Area
Department
Responsible Unit
or Issue Group
```

Record:

```text
Issuer
Time
Target
Instruction
Status
Response
Outcome
```

---

# 328. “REQUEST EXPLANATION”

Senior administration may request a structured explanation.

Store:

```text
Requester
Recipient
Related Complaint / Issue
Question
Requested At
Due Date where applicable
Response
Response Time
Outcome
```

The responsible person should not need to write an unnecessarily long formal report.

---

# 329. “PROVIDE SUPPORT”

Mayor/Admin or authorized executive may help remove operational blockage.

Support may include:

```text
Additional Staff
Vehicle
Equipment
Cross-Department Support
Technical Inspection
Special Operational Team
```

The support action remains attached to the issue.

---

# 330. EXECUTIVE INTERVENTION HISTORY

Every executive intervention must be auditable.

Track:

```text
Who
When
Issue
Action
Reason
Target
Outcome
```

Do not allow silent executive manipulation.

---

# 331. EXECUTIVE INTERVENTION EFFECT — INTERNAL ANALYTICS

Support internal analysis such as:

```text
Before Intervention Backlog
After Intervention Backlog
Resolution Improvement
SLA Improvement
Affected Complaints
Completion Rate
```

Example:

```text
Before intervention:
78 overdue

24 hours later:
21 overdue

Improvement:
73%
```

This may be internal executive intelligence.

Do not automatically present it as political advertising.

---

# 332. EXECUTIVE CITY MAP

Mayor/Admin should have a simple city map.

Possible layers:

```text
Complaint Volume
Overdue
Urgent Hazards
Mosquito Complaints
Waste Complaints
Drainage Complaints
Recurring Hotspots
```

Do not make the map visually overwhelming.

Use clear selectable layers.

---

# 333. WARD STATUS ON EXECUTIVE MAP

Ward status may use understandable indicators such as:

```text
Normal
Needs Attention
Critical
```

Never rely only on color.

Always include text or accessible meaning.

---

# 334. EXECUTIVE WARD DRILL-DOWN

Selecting a Ward may show:

```text
Complaints Today
Resolved
In Progress
Overdue
Citizen Says Not Resolved
Top Problems
Current General Representative / Responsible Officer
Reserved Women Councillor
Relevant Operational Services
```

---

# 335. EXECUTIVE DEPARTMENT DRILL-DOWN

Selecting a Department may show:

```text
Received
Resolved
In Progress
Overdue
Median Resolution Time
Citizen Reopen Rate
Quick Action Performance
Current Backlog
Important Responsibility Gaps
```

---

# 336. EXECUTIVE TREND PRESENTATION

Do not make the Mayor/Admin interpret dozens of charts.

Use plain-language summaries such as:

> Mosquito complaints increased significantly in Ward 22.  
> Waste backlog decreased this week.  
> Repeated drainage problems are concentrated in three locations.

Charts may support the message but should not replace it.

The core system must not depend on AI to generate these summaries.

Deterministic summaries are acceptable.

---

# 337. MAYOR / ADMIN DAILY BRIEF

Generate a concise executive daily brief.

Suggested content:

```text
Last 24 Hours

New Complaints
Resolved
Overdue
Citizen Reopened

Top Problems

Most Affected Wards

Critical Unresolved Issues

Recurring Hotspots

Notable Improvements

Important Citizen Messages
```

Support Bangla and English.

---

# 338. EXECUTIVE NOTIFICATION POLICY

Do NOT send the Mayor/Admin a notification for every normal complaint.

Executive notifications should focus on:

```text
Critical Hazard
Major Citywide Issue
First Deadline Failure aggregation
First Reopen aggregation
Major Complaint Spike
Significant Recurring Hotspot
Critical Administrative Responsibility Gap
```

Normal overdue items remain visible on the dashboard without excessive push notifications.

---

# 339. CHIEF EXECUTIVE OFFICER DASHBOARD

The CEO dashboard focuses on administrative execution.

Primary question:

> প্রশাসনের কোথায় কাজ আটকে আছে?  
> Where is administrative execution blocked?

---

# 340. CEO HOME

Show simple indicators such as:

```text
Pending
Overdue
Department Issues
Cross-Department Issues
Important Responsibility Gaps
Action Needed
```

Do not make the CEO browse every complaint manually.

---

# 341. CEO DEPARTMENT VIEW

Show department comparison using:

```text
Current Backlog
Overdue
Median Resolution Time
Reopen Rate
Citizen Confirmation
Operational Capacity Issues
```

Allow drill-down.

---

# 342. CEO CROSS-DEPARTMENT ISSUES

Show complaints/tasks where multiple departments are required.

Make clear:

```text
Primary Accountable Unit
Supporting Unit
Current Blockage
Requested Support
Time Waiting
```

---

# 343. DEPARTMENT HEAD DASHBOARD

Department Head should primarily answer:

> আমার বিভাগের কোথায় কাজ আটকে আছে?  
> Where is my department blocked?

Show:

```text
Today Received
Resolved
In Progress
Overdue
Reopened / Needs More Work
Ward Distribution
Supervisor Workload
Important Resource Requests
```

---

# 344. DEPARTMENT HEAD SUPERVISOR VIEW

Internal supervisor comparison may show:

```text
Current Active Work
Completed
Deadline Performance
Citizen Reopen Rate
Current Workload
Support Requests
```

Do not reduce employee performance to raw closure count.

---

# 345. ZONE OFFICER DASHBOARD

Zone Officer sees only authorized Zone scope.

Main view:

```text
My Zone

Complaints Today
Resolved
In Progress
Overdue
Citizen Says Not Resolved
```

Then:

```text
Ward Comparison
Important Problems
Recurring Hotspots
Responsibility Gaps
```

---

# 346. WARD OFFICER DASHBOARD

Ward Officer sees only permitted Ward scope.

Main view:

```text
My Ward

New
In Progress
Due Today
Overdue
Needs More Work
```

Focus on operational blockers.

---

# 347. COUNCILLOR / RESPONSIBLE OFFICER DASHBOARD

Part 2 requirements remain active.

The dashboard should focus on:

```text
Ward Condition
Citizen Problems
Overdue
Needs More Work
Important Ward Issues
Follow-Up
Communication
```

Representation users must not be given field-completion powers merely because they can see complaints.

---

# 348. RESERVED WOMEN COUNCILLOR DASHBOARD

Show:

```text
All Covered Wards
Combined Summary
Ward-by-Ward Status
Overdue
Needs More Work
Citizen Feedback
Important Local Issues
```

No separate login per Ward.

---

# 349. DATA / MONITORING OFFICER

Support a read/analysis-oriented role.

Possible access:

```text
Complaint Trends
Ward Performance
Zone Performance
Department Performance
Recurring Problems
Citizen Feedback
Deadline Analytics
Public Dashboard Data
```

Do not automatically grant complaint modification rights.

---

# 350. AUDITOR / READ-ONLY OVERSIGHT

Support a read-only oversight role.

May view according to scope:

```text
Complaint History
Audit Trail
Assignments
Deadlines
Reopens
Executive Actions
Organizational History
Reports
```

Must not modify operational data.

---

# 351. PLATFORM SUPER ADMIN IS A BUSINESS ADMINISTRATOR

The Platform Super Admin should be designed for a normal non-technical administrative user.

Primary responsibilities:

```text
People
Zones & Wards
Governance Representation
Departments
Services
Complaint Categories
Responsibility
Routing
Service Deadlines
Public Notices
User Access
Reports
```

Do not make normal Platform Admin use technical infrastructure controls.

---

# 352. PLATFORM SUPER ADMIN HOME

First screen should use understandable administrative indicators.

Example:

```text
Active Employees
Employees Without Responsibility
Active Wards
Unassigned Services
Routing Problems
Expired Temporary Responsibilities
Today's Complaints
Important Attention
```

---

# 353. PLATFORM SUPER ADMIN PRIMARY ACTIONS

Primary actions may include:

```text
কর্মী যোগ করুন
Add Employee

দায়িত্ব পরিবর্তন করুন
Change Responsibility

ওয়ার্ড / অঞ্চল পরিচালনা করুন
Manage Wards / Zones

প্রতিনিধি নির্ধারণ করুন
Manage Representation

সেবা পরিচালনা করুন
Manage Services

অভিযোগের ধরন পরিচালনা করুন
Manage Complaint Types

সময়সীমা পরিবর্তন করুন
Change Service Deadline

নোটিশ প্রকাশ করুন
Publish Notice
```

---

# 354. PLATFORM ADMIN MUST NOT SEE RAW DATABASE CONCEPTS

Do not expose ordinary Platform Admin to:

```text
Foreign Keys
Pivot Tables
Role IDs
Scope IDs
Database IDs
Redis Keys
Migration Names
Queue Names
Raw API Routes
```

Use administrative language.

---

# 355. PLATFORM ADMIN “PEOPLE” MODULE

As specified in Part 2, provide:

```text
All Employees
Officers
Supervisors
Field Workers
Cleaners
Teams
Representatives
Change Responsibility
```

Provide search and guided filters.

---

# 356. PLATFORM ADMIN “AREAS” MODULE

Provide:

```text
Zones
Wards
Ward Offices
Zone Offices
Verified Boundary Data
Ward-to-Zone Assignment
Current Responsible Officials
```

Use simple map/list interfaces.

---

# 357. PLATFORM ADMIN “GOVERNANCE” MODULE

Provide:

```text
Mayor / Administrator Assignment
General Ward Representation
Reserved Women Councillors
Responsible Officers
Acting / Temporary Assignments
Representation History
Vacant Responsibilities
Official Authority Documents
```

Use guided setup.

---

# 358. PLATFORM ADMIN “SERVICES” MODULE

Provide understandable structure:

```text
Department
Service
Area Coverage
Responsible Person / Unit
Team
Deadline
```

Example question:

> এই ওয়ার্ডে মশার অভিযোগ কার কাছে যাবে?  
> Who handles mosquito complaints in this Ward?

---

# 359. PLATFORM ADMIN ROUTING CONFIGURATION

Do not expose raw routing rule tables by default.

Use guided configuration.

Example:

```text
Problem:
Mosquito

Area:
Ward 19

Responsible Service:
Mosquito Control

Supervisor:
X

Team:
Y

Expected Service Time:
24 Hours
```

---

# 360. RESPONSIBILITY GAP SCREEN

Platform Admin should have a simple:

```text
দায়িত্ব দেওয়া হয়নি
Responsibility Not Assigned
```

view.

Possible issues:

```text
Ward without General Representative / Responsible Officer
Service without Owner
Team without Supervisor
Expired Temporary Assignment
Employee without Current Posting
Complaint Type without Routing
```

---

# 361. GUIDED ADMIN SETUP

Use wizards for complex organizational actions.

Examples:

```text
Add Employee
Assign Supervisor
Assign Ward Officer
Assign Responsible Officer
Assign General Councillor
Assign Reserved Women Councillor
Change Ward Responsibility
Create Service
Change Deadline
Create Team
```

Never require database-level knowledge.

---

# 362. USER ACCESS PRESETS

For normal Platform Admin use understandable presets such as:

```text
General Councillor
Reserved Women Councillor
Responsible Officer
Department Head
Zone Officer
Ward Officer
Supervisor
Field Worker
Call Center Operator
Communications Officer
Monitoring Officer
```

Do not require manual selection of dozens of permissions.

---

# 363. ADVANCED PERMISSION CUSTOMIZATION

Advanced permission customization may exist for highly authorized administrators.

It must be separate from normal setup.

Changing permissions must be auditable.

---

# 364. TECHNICAL SUPER ADMIN IS ALSO REQUIRED TO BE SIMPLE

Technical Super Admin may manage technical platform operations.

However:

> Technical Super Admin UI must still be understandable at first glance by a non-expert.

Do NOT build a server-control-panel-style first screen.

---

# 365. TECHNICAL SUPER ADMIN HOME

Show plain-language system health.

Example:

```text
সিস্টেমের অবস্থা
System Status

Website — Working
Database — Working
Fast Services — Working
Notifications — Warning
Background Processing — Working
Backup — Completed
Security — Good
```

---

# 366. TECHNICAL DETAILS ARE SECONDARY

Raw details such as:

```text
Redis
PHP-FPM
Nginx
Worker Process
Cron
PDO
Queue Retry
HTTP Error
```

should appear under:

```text
Technical Details
Advanced
```

not as the primary dashboard language.

---

# 367. TECHNICAL ADMIN NAVIGATION

Suggested simple navigation:

```text
System Status
Users & Access
Communication Services
Backup
Security
Logs
Advanced
```

---

# 368. SYSTEM HEALTH LANGUAGE

Translate infrastructure problems into human meaning.

Instead of:

> Redis connection failed.

show first:

> কিছু দ্রুতগতির সেবা সাময়িকভাবে ধীর হতে পারে।  
> Some fast services may temporarily be slower.

Then allow:

```text
Technical Details
```

---

# 369. BACKGROUND PROCESS HEALTH

Instead of initially showing:

> Queue worker stopped.

show:

> কিছু Background কাজ বিলম্বিত হচ্ছে।  
> Some background work is delayed.

Technical details may show worker information.

---

# 370. DATABASE HEALTH

First-level status:

```text
ডাটাবেস সচল
Database Working
```

or:

```text
ডাটাবেসে সমস্যা হয়েছে
Database Problem Detected
```

Do not expose raw SQL errors to normal users.

---

# 371. COMMUNICATION SERVICE HEALTH

Show:

```text
SMS Service
Push Notification
Email
```

using:

```text
Working
Warning
Unavailable
```

Allow:

```text
Test Again
View Details
```

---

# 372. BACKUP STATUS

Technical Admin must easily understand:

```text
Latest Backup
Backup Success / Failure
Last Successful Backup Time
Restore Test Status where available
```

Do not require reading shell logs.

---

# 373. SAFE TECHNICAL ACTIONS

Where technically safe, provide guided actions such as:

```text
Test Again
Retry Service
Retry Failed Job
Check Backup
Disable Integration Temporarily
Enable Integration
Restart Safe Background Process
```

Explain the effect before performing sensitive actions.

---

# 374. DANGEROUS TECHNICAL ACTIONS

Potentially dangerous actions require:

```text
Clear Description
Impact Warning
Explicit Confirmation
Audit Record
```

Do not expose destructive actions casually.

---

# 375. NO “CLEAR ALL DATA” NORMAL BUTTON

Do not provide normal UI controls such as:

```text
Delete All Complaints
Reset Production Database
Clear All Audit History
```

These should not exist as routine administrative actions.

---

# 376. PLATFORM ADMIN AND TECHNICAL ADMIN ARE SEPARATE CONCEPTS

**Platform Admin:**

```text
People
Organization
Governance
Services
Complaint Configuration
```

**Technical Admin:**

```text
Infrastructure
Integration
Backup
Security
System Health
```

A user may hold both roles if legitimately authorized, but the interfaces remain conceptually separated.

---

# 377. ADMIN SEARCH

Provide a universal authorized search experience.

Depending on permission, users may search:

```text
Complaint Number
Employee
Representative
Ward
Zone
Department
Team
Service
Area
Official Contact
```

Results must respect scope.

---

# 378. PUBLIC ACCOUNTABILITY DASHBOARD

Create a public dashboard requiring no login.

Primary goal:

Citizens should understand how City Corporation services are performing.

The dashboard must be clear, factual and privacy-safe.

---

# 379. PUBLIC DAILY SUMMARY

Suggested public headline:

```text
আজকের নগর সেবা
Today's City Services
```

Primary indicators:

```text
Complaints Received Today
Resolved Today
In Progress
Overdue
Citizen Confirmed
Needs More Work
```

---

# 380. PUBLIC RESOLUTION RATE

If Resolution Rate is shown:

The exact definition must be documented.

Do not use a misleading definition designed to inflate performance.

---

# 381. PUBLIC COMPLAINT QUALITY METRICS

Where appropriate show:

```text
On-Time Resolution
Overdue
Median Resolution Time
Citizen Confirmed Resolution
Needs More Work / Reopened
Recurring Issues
```

Do not hide failed-resolution data.

---

# 382. PUBLIC “WORK COMPLETED” VS “RESOLVED”

Do not imply these are identical.

Possible distinction:

```text
Work Completed
Supervisor Verified
Resolved
Citizen Confirmed
```

Keep public presentation understandable.

Detailed metric labels may be simplified while preserving factual meaning.

---

# 383. PUBLIC CATEGORY BREAKDOWN

Show complaint breakdown by:

```text
Cleanliness
Mosquito / Public Health
Drainage
Roads
Street Lighting
Water
Other Major Categories
```

Allow additional category view.

---

# 384. PUBLIC WARD BREAKDOWN

Allow public viewing by Ward.

Possible metrics:

```text
Received
Resolved
Overdue
Needs More Work
Median Resolution Time
```

Do not publicly expose individual citizen information.

---

# 385. PUBLIC ZONE BREAKDOWN

Allow Zone-level summary.

Show included Wards and aggregate service statistics.

---

# 386. PUBLIC DEPARTMENT / SERVICE BREAKDOWN

Where understandable to citizens, show performance by:

```text
Department
Service
```

Do not expose confusing internal units unnecessarily.

---

# 387. PUBLIC TIME FILTERS

Support clear periods such as:

```text
Today
Last 7 Days
Last 30 Days
Custom Date Range where appropriate
```

Default public view should remain simple.

---

# 388. PUBLIC TREND VIEW

Simple trend visualization may show:

```text
Complaints Received
Resolved
Overdue
```

Avoid excessive charts.

---

# 389. WARD RANKINGS MUST NOT BE MISLEADING

Do not create simplistic “best/worst Ward” rankings based only on raw complaint totals.

Complaint volume may depend on:

```text
Population
Problem Type
Infrastructure
Complaint Adoption
Service Complexity
```

Where verified population data exists, support contextual/per-capita indicators.

---

# 390. SERVICE COMPLEXITY CONTEXT

Public reporting should distinguish when useful:

```text
Quick Action
Maintenance
Project / Structural Issue
```

A Ward with many long-term infrastructure cases should not automatically appear operationally worse than a Ward handling mainly Quick Actions.

---

# 391. PUBLIC METHODOLOGY

Provide a simple:

> এই হিসাব কীভাবে করা হয়?  
> How Are These Numbers Calculated?

section.

Define:

```text
Received
Resolved
Overdue
Citizen Confirmed
Needs More Work
Median Resolution Time
```

Transparency includes explaining the statistics.

---

# 392. PUBLIC DATA REFRESH

Public numbers should update regularly.

The exact refresh mechanism is technical and will be finalized later.

Citizens should not need to manually understand caching.

---

# 393. PUBLIC PRIVACY — HARD REQUIREMENT

Never publicly expose by default:

```text
Citizen Name
Citizen Phone
Citizen Email
NID
Exact Home Address
Exact Private Coordinates
Private Attachments
Private Complaint Messages
Internal Staff Notes
Sensitive Administrative Records
```

---

# 394. PUBLIC COMPLAINT PAGE

A public-safe complaint page may show:

```text
Complaint Number
Category
Ward
Approximate Area
Public Status
Submitted Time
Last Meaningful Update
Responsible Municipal Service
Resolution Time
Approved Public Evidence
```

---

# 395. EXACT LOCATION PRIVACY

For complaints near private residences:

Public UI should use:

```text
Ward
Approximate Area
Public-Safe Map Precision
```

rather than exposing exact household GPS coordinates.

Authorized operational staff may access accurate location where required.

---

# 396. SENSITIVE COMPLAINTS

Support:

```text
Public
Restricted
Sensitive
```

or equivalent visibility classification.

Sensitive complaints must not automatically appear in public feeds/maps.

---

# 397. PUBLIC EVIDENCE MUST BE SEPARATE FROM ORIGINAL EVIDENCE

Original evidence remains internal/secure.

Public evidence requires:

```text
Approval
Safe Derivative
or Moderation
```

Do not automatically publish original uploaded evidence.

---

# 398. PUBLIC IMAGE PRIVACY

Public derivatives should avoid exposing where possible:

```text
Faces
Children
Vehicle Number Plates
House Numbers
Personal Documents
Sensitive Private Property Details
```

Provide moderation controls.

---

# 399. PUBLIC FEED MUST NOT BECOME SOCIAL MEDIA

Do not create unmoderated public comment threads beneath complaints.

The platform is for civic service accountability, not public harassment.

---

# 400. PUBLIC INSTITUTION-FIRST PRESENTATION

Primary public identity must remain:

```text
Mymensingh City Corporation
City Services
Citizen Services
```

Do not build covert individual political promotion.

---

# 401. LEGITIMATE LEADERSHIP ATTRIBUTION

If the Mayor/Administrator actually:

```text
Issues a Directive
Publishes an Official Notice
Launches an Official City Action
Requests a Special Inspection
```

the action may be factually attributed.

Do not fabricate or artificially assign personal credit.

---

# 402. PUBLIC MAYOR / ADMINISTRATOR PROFILE

Support a factual official leadership page:

```text
Name
Official Position
Official Photo
Office Contact
Assignment / Term Period
Official Message Channel
```

Use current governance data.

---

# 403. PUBLIC GENERAL REPRESENTATIVE INFORMATION

Ward public page may show:

```text
General Councillor
or
Responsible Officer
```

depending on the active valid assignment.

Never label a Responsible Officer as an elected Councillor.

---

# 404. PUBLIC RESERVED WOMEN REPRESENTATIVE

Ward public page may additionally show the active Reserved Women Councillor covering that Ward.

---

# 405. “WHO IS RESPONSIBLE?” PUBLIC EXPERIENCE

The Part 2 feature is a major citizen convenience.

Citizen may use:

```text
Current Location
Ward
Area Search
```

Then see a simple service responsibility view.

Example:

```text
Your Ward:
Ward 19

General Representation:
Responsible Officer X

Reserved Representation:
Councillor Y

Waste:
Ward Waste Service

Mosquito:
Health / Mosquito Service

Road / Drain:
Engineering Service

Street Light:
Electrical Service
```

Use official contact channels.

---

# 406. PUBLIC OFFICIAL CONTACT

Public contacts may include:

```text
In-App Message
Office Phone
Official Email
Office Address
Office Hours
Official Mobile where explicitly allowed
```

Do not publish personal contact information by default.

---

# 407. COMPLAINT-LINKED MESSAGING

Citizen and responsible authorized staff may communicate within the complaint.

Keep communication:

```text
Relevant
Auditable
Scoped
Complaint-linked
```

Do not create unrestricted direct messaging.

---

# 408. CITIZEN MESSAGE TYPES

Complaint communication may support simple actions such as:

```text
Add Information
Send Location Detail
Add Photo
Ask for Follow-Up
```

Staff may provide:

```text
Meaningful Update
Request Information
Service Explanation
```

---

# 409. INTERNAL NOTES VS CITIZEN MESSAGES

Strictly distinguish:

```text
Citizen-visible Communication
Internal Operational Note
Private Administrative Note
```

Never expose internal notes through public/citizen API accidentally.

---

# 410. MAYOR / ADMINISTRATOR OFFICE INBOX

Create a structured leadership office communication module.

Public CTA:

```text
প্রশাসকের কাছে লিখুন
Write to the Administrator
```

or, when a Mayor is active:

```text
মেয়রের কাছে লিখুন
Write to the Mayor
```

Use actual active governance data.

---

# 411. LEADERSHIP OFFICE MESSAGE TYPES

Possible categories:

```text
Complaint Concern
Suggestion
Feedback
Urgent Attention
General Civic Message
Appreciation
Other
```

Keep submission simple.

---

# 412. OFFICE TRIAGE

Authorized office staff may triage incoming leadership messages.

Possible actions:

```text
Acknowledge
Link to Existing Complaint
Create Complaint
Send to Relevant Service
Mark for Executive Attention
Respond
Close Office Message
```

Do not silently discard citizen communication.

---

# 413. LINK OFFICE MESSAGE TO COMPLAINT

If a citizen writes to the leadership office about an existing complaint:

Link the message to the complaint where possible.

Do not create unnecessary duplicate complaints.

---

# 414. MAYOR / ADMIN OFFICE SUMMARY

Executive may see:

```text
Citizen Messages Today
Urgent
Complaint Concern
Suggestion
Feedback
Other
```

Avoid showing thousands of raw messages by default.

---

# 415. REPRESENTATIVE COMMUNICATION

General Councillor, Reserved Women Councillor and Responsible Officer may have official communication channels according to policy.

A citizen must always see:

```text
Name
Official Role
Responsible Area
```

before sending a message.

---

# 416. COMMUNICATION ANTI-ABUSE

Support controls for:

```text
Spam
Repeated Harassment
Abusive Language
Unrelated Messages
Automated Abuse
```

Do not block legitimate civic complaints merely because they are critical of municipal service.

---

# 417. CALL CENTER EXPERIENCE

Call Center home should remain:

```text
নতুন অভিযোগ
New Complaint

অভিযোগ খুঁজুন
Search Complaint
```

Optional:

```text
Citizen Search
Recent Calls / Cases
```

Do not expose operational complexity.

---

# 418. CALL CENTER COMPLAINT CREATION

Operator asks:

```text
What is the problem?
Where is the problem?
How can we contact you?
```

The system automatically handles:

```text
Ward
Zone
Department
Responsible Service
Routing
```

where possible.

---

# 419. CONTROL ROOM / REVIEW QUEUE

Review users should primarily see:

```text
Needs Review
```

with clear reasons:

```text
Possible Duplicate
Unclear Issue
Sensitive
Spam / Abuse
Unknown Responsibility
Invalid Media
```

Actions remain simple:

```text
Approve
Need Information
Duplicate
Refer
Reject
```

---

# 420. COMMUNICATIONS / PUBLIC INFORMATION ROLE

Support a dedicated role capable of publishing official city communication without gaining unrelated complaint-management permissions.

Possible responsibilities:

```text
Public Notices
Emergency Alerts
Ward Notices
Mosquito Campaign Updates
Waste Collection Updates
Road Closure
Water Service Update
Official City Action
```

---

# 421. NOTICE LANGUAGE

Official notices must support:

```text
Bangla
English
```

Bangla remains primary.

Allow Bangla-only draft if translation is pending, but publishing policy should make bilingual intent clear.

---

# 422. NOTICE TARGETING

Official notice may target:

```text
All City
Zone
Ward
Department / Service Audience
Relevant Citizens
```

Do not expose hidden citizen lists publicly.

---

# 423. EMERGENCY NOTICE

Support high-priority public city notices.

Emergency notices must be visually prominent and accessible.

They must not be confused with national emergency response services.

---

# 424. CITIZEN NOTIFICATION CENTER

Citizen notification center should contain only meaningful updates.

Examples:

```text
Complaint Received
Information Needed
Meaningful Work Update
Work Completed
Confirmation Needed
Needs More Work
Resolved
External Referral
Important Ward/City Notice
```

---

# 425. NOTIFICATION NOISE CONTROL

Do not generate citizen notifications for:

```text
Internal Transfer
Internal Officer Note
Internal Queue Change
Background Job
Technical Routing Event
```

unless it changes meaningful citizen understanding.

---

# 426. SUPERVISOR NOTIFICATIONS

Relevant examples:

```text
New Work
Urgent Work
Deadline Approaching
Overdue
Citizen Says Not Resolved
Worker Cannot Complete
Support Request
```

---

# 427. OFFICER / DEPARTMENT NOTIFICATIONS

Relevant examples:

```text
High Backlog
Overdue Cluster
Responsibility Gap
Supervisor Unavailable
Support Request
Repeated Failure
Complaint Spike
```

---

# 428. EXECUTIVE NOTIFICATIONS

Relevant examples:

```text
Critical Hazard
Important Citywide Failure
Significant Complaint Spike
Major Recurring Hotspot
Important Responsibility Gap
```

Normal first deadline/reopen cases remain dashboard-visible and may be summarized rather than individually pushed.

---

# 429. NOTIFICATION PREFERENCES

Where appropriate, allow configurable preferences.

Do not allow users to disable mandatory critical/security communications if policy requires them.

---

# 430. IN-APP NOTIFICATION STATE

Support:

```text
Unread
Read
Action Needed
Resolved
```

Keep the interface simple.

---

# 431. PUBLIC CITY NOTICE FEED

Public homepage may show:

```text
Recent City Service Updates
Important Notices
Emergency Notice
Ward-Specific Updates
```

Do not turn the feed into a political social-media timeline.

---

# 432. CITIZEN PULSE

Create an optional lightweight citizen pulse feature.

Question example:

> আপনার এলাকার বর্তমান অবস্থা কেমন?  
> How is your area today?

Simple options:

```text
Good
Average
Poor
```

---

# 433. CITIZEN PULSE TOPICS

Optional service topics:

```text
Cleanliness
Mosquito
Drainage
Road
Street Lighting
```

Keep it lightweight.

---

# 434. CITIZEN PULSE IS NOT A FORMAL COMPLAINT

Clearly distinguish:

```text
Citizen Pulse
vs
Complaint
```

If a citizen reports a specific actionable problem, guide them toward:

```text
সমস্যা জানান
Report a Problem
```

---

# 435. CITIZEN PULSE EXECUTIVE VIEW

Mayor/Admin may see:

```text
Ward Satisfaction Trend
Topic Satisfaction
Rapid Decline
Improvement
```

Do not treat pulse data as scientifically representative unless methodology supports that claim.

---

# 436. FUTURE CIVIC POLL FOUNDATION

The architecture may later support neutral civic consultation.

Example:

> Which local service issue should receive attention first?

Do not make civic polls a blocker for core implementation.

---

# 437. NO PARTISAN CAMPAIGN SYSTEM

Do not build:

```text
Political Campaign Messaging
Vote Solicitation
Partisan Targeting
Covert Political Persuasion
```

The platform is a public civic service system.

---

# 438. PUBLIC REPORTS

Support public-safe reports such as:

```text
Daily City Service Summary
Monthly Complaint Summary
Ward Summary
Zone Summary
Department / Service Summary
Deadline Performance
Citizen Confirmation
```

---

# 439. INTERNAL REPORTS

Authorized users may access:

```text
Daily City Report
Ward Report
Zone Report
Department Report
Overdue Report
Needs More Work Report
Recurring Problem Report
Quick Action Report
Citizen Feedback Report
Responsibility Gap Report
Workforce Workload Report
Executive Intervention Report
```

---

# 440. REPORT EXPORT

Support initially:

```text
CSV
```

Architect for:

```text
PDF
```

where appropriate.

Do not block the core platform on advanced report design.

---

# 441. REPORT PRIVACY

Public exports contain only public-safe/aggregate information.

Internal reports require permission.

Do not place citizen phone numbers in ordinary administrative reports unless the specific authorized purpose requires them.

---

# 442. PUBLIC OPEN-CITY DISPLAY

The architecture may support a public display screen for:

```text
City Corporation Office
Public Display
Website
Large Monitor
```

Example:

```text
Today

Complaints
Resolved
In Progress
Overdue
```

Keep public-safe.

This is optional and must not block core implementation.

---

# 443. ROLE-BASED NAVIGATION — MAYOR / ADMIN

Suggested:

```text
Overview
City Map
Attention
Wards
Departments
Citizen Pulse
Reports
Directives
```

Keep labels bilingual.

---

# 444. ROLE-BASED NAVIGATION — CEO

Suggested:

```text
Overview
Departments
Cross-Department Issues
Attention
Reports
```

---

# 445. ROLE-BASED NAVIGATION — DEPARTMENT HEAD

Suggested:

```text
My Department
Wards
Supervisors
Backlog
Attention
Reports
```

---

# 446. ROLE-BASED NAVIGATION — ZONE OFFICER

Suggested:

```text
My Zone
Wards
Attention
Performance
```

---

# 447. ROLE-BASED NAVIGATION — WARD OFFICER

Suggested:

```text
My Ward
Current Work
Overdue
Attention
```

---

# 448. ROLE-BASED NAVIGATION — COUNCILLOR / RESPONSIBLE OFFICER

Suggested:

```text
My Ward / My Area
Citizen Issues
Needs Attention
Communication
```

---

# 449. ROLE-BASED NAVIGATION — PLATFORM SUPER ADMIN

Suggested:

```text
Overview
People
Zones & Wards
Governance
Departments & Services
Complaint Settings
Responsibilities
Notices
Reports
Users & Access
```

---

# 450. ROLE-BASED NAVIGATION — TECHNICAL SUPER ADMIN

Suggested:

```text
System Status
Users & Access
Communication Services
Backup
Security
Logs
Advanced
```

---

# 451. NO MENU OVERLOAD

If a role has many functions:

Use grouped navigation and secondary views.

Do not show fifteen or twenty equal main navigation items.

---

# 452. MOBILE ADMIN NAVIGATION

Supervisor, Ward Officer and field-oriented users require mobile-friendly navigation.

Mayor/Admin may have a simplified executive mobile view.

Do not try to reproduce the entire desktop dashboard on a small phone screen.

---

# 453. DESKTOP EXECUTIVE EXPERIENCE

Mayor/Admin, CEO, Department Head and Monitoring roles may benefit from larger-screen dashboards.

Use responsive layouts.

Do not make desktop mandatory for basic access.

---

# 454. COMMON COMPLAINT DETAIL LAYOUT

Conceptually use a consistent complaint layout:

```text
Problem
Complaint Number
Status
Priority where relevant

Photo / Evidence
Location
Description

Timeline

Current Responsibility

Messages

Allowed Actions
```

The allowed actions change by role.

---

# 455. ROLE-SPECIFIC COMPLAINT ACTIONS

**Citizen:**

```text
Add Information
Request Follow-Up
Confirm Resolution
```

**Worker:**

```text
Start Work
Add Evidence
Complete
Cannot Complete
```

**Supervisor:**

```text
Assign
Request Support
Verify
Return for More Work
```

**Officer:**

```text
View
Support
Correct Responsibility where authorized
```

**Councillor / Responsible Representative:**

```text
Follow Up
Request Attention
Communicate
```

**Mayor/Admin:**

```text
Ask for Action
Provide Support
Request Explanation
Request Inspection
Set Priority
```

---

# 456. PERMISSION MUST CONTROL ACTIONS

A hidden button is not security.

Every action must be backend-authorized.

The detailed permission matrix will be created during synthesis.

---

# 457. USER SHOULD NEVER SELECT TECHNICAL STATUS MANUALLY

Do not provide generic:

```text
Change Status → dropdown with all internal states
```

for normal users.

Users perform meaningful actions.

The state machine changes automatically.

---

# 458. PUBLIC DATA MUST BE EXPLAINABLE

Every important public metric should have:

```text
Label
Simple Definition
Time Period
Data Freshness where appropriate
```

Avoid unexplained percentages.

---

# 459. NO FAKE REAL-TIME CLAIM

If public data is refreshed periodically, do not label it:

```text
Live
```

unless it actually meets the platform's real-time definition.

Use:

```text
Updated recently
Last updated...
```

when appropriate.

---

# 460. PUBLIC HISTORY AND ACCOUNTABILITY

Public-safe complaint history may include meaningful events such as:

```text
Complaint Received
Assigned
Work Started
Work Completed
Needs More Work
Resolved
```

Do not expose confidential internal administrative activity.

---

# 461. ADMINISTRATIVE HISTORY

Authorized users may see more detailed events:

```text
Routing
Ownership
Worker Assignment
Support Request
Overdue
Executive Attention
Priority Change
Verification
Citizen Reopen
Transfer
Directive
```

---

# 462. ADMINISTRATIVE ACTIONS MUST BE AUDITABLE

Changes to:

```text
People
Governance
Ward Responsibility
Service Responsibility
Deadlines
Routing
Public Notices
Permissions
Executive Instructions
```

must be auditable.

Detailed audit/security design will be specified in Part 5.

---

# 463. MAYOR / ADMIN DASHBOARD MUST NOT BECOME A PROPAGANDA TOOL

The dashboard exists to improve governance and oversight.

The public platform must not manipulate citizens into believing routine operational work was personally performed by leadership.

Leadership may receive legitimate factual credit for:

```text
Real Directive
Real Intervention
Official Program
Official Notice
```

when supported by system records.

---

# 464. INTERNAL EXECUTIVE VALUE MAY BE GREATER THAN PUBLIC VISIBILITY

It is acceptable and intended that the Mayor/Admin receives powerful internal intelligence such as:

```text
Which Ward is falling behind
Which service repeatedly fails
Which responsibility is vacant
Which complaints miss deadlines
Which complaints citizens reopen
Where complaint volume suddenly rises
Where recurring hotspots exist
```

Public users do not need access to every internal management signal.

---

# 465. SIMPLE ADMINISTRATIVE LANGUAGE — HARD REQUIREMENT

Every administrative module should prefer language such as:

```text
Who is responsible?
What is overdue?
What needs attention?
Who is available?
What service is affected?
What should happen next?
```

over:

```text
Scope mapping
Entity binding
Transition state
Assignment pivot
Routing engine node
Escalation object
```

---

# 466. SIMPLE TECHNICAL ADMIN LANGUAGE — HARD REQUIREMENT

Even Technical Admin should see first:

```text
What is working?
What has a problem?
What needs action?
Was backup successful?
```

Technical detail is available second.

---

# 467. PUBLIC ACCOUNTABILITY WITHOUT PUBLIC HARASSMENT

Transparency must not create unnecessary personal targeting.

Public performance should emphasize:

```text
City Corporation
Zone
Ward
Department
Service
```

Do not publicly rank individual cleaners, field workers or low-level staff.

---

# 468. FEEDBACK ON RESOLUTION

After citizen confirmation, optionally ask a very short satisfaction question.

Example:

> এই সেবায় আপনি কতটা সন্তুষ্ট?  
> How satisfied are you with this service?

Use a simple rating.

Do not force long surveys.

---

# 469. CITIZEN SATISFACTION METRIC

When used publicly or internally:

Clearly distinguish between:

```text
Responded Citizens
Total Complaints
```

Do not imply satisfaction ratings represent all citizens when only a subset responded.

---

# 470. EMPTY STATES MUST BE FRIENDLY

Examples:

No overdue complaints.

Bangla:

```text
সময় পেরিয়ে যাওয়া কোনো অভিযোগ নেই।
```

Do not display empty technical tables.

---

# 471. ADMIN CONFIRMATION MESSAGES

Use clear confirmation messages.

Example:

> দায়িত্ব সফলভাবে পরিবর্তন হয়েছে।  
> Responsibility changed successfully.

Not:

```text
Scope mapping persisted successfully.
```

---

# 472. ADMIN ERROR MESSAGES

Use human-readable first-level errors.

Example:

```text
এই ওয়ার্ডের জন্য কোনো সক্রিয় সুপারভাইজার পাওয়া যায়নি।
```

English:

```text
No active supervisor is currently assigned to this Ward.
```

Technical details may be logged internally.

---

# 473. SUPER ADMIN GUIDED CORRECTION

When configuration causes a responsibility gap, provide a direct fix action.

Example:

> No Supervisor Assigned

Action:

```text
সুপারভাইজার নির্ধারণ করুন
Assign Supervisor
```

Do not merely show an error code.

---

# 474. EXECUTIVE ATTENTION SHOULD LEAD TO ACTION

An executive attention item should clearly show:

```text
What happened
Where
How long
Who is responsible
Why attention is needed
What can be done
```

Avoid passive red-alert dashboards with no useful action.

---

# 475. PUBLIC HOMEPAGE PRINCIPLE

Suggested information hierarchy:

```text
সমস্যা জানান
Report a Problem

অভিযোগ খুঁজুন
Track Complaint

আজকের নগর সেবা
Today's City Services

আমার এলাকা
My Area

নগর আপডেট
City Updates
```

Do not overwhelm the public homepage.

---

# 476. CITIZEN “MY AREA”

Citizen may save or detect a Ward/area.

My Area may show:

```text
Ward
Representatives
Official Contacts
Current Notices
Common Services
Today's Complaint Summary
```

---

# 477. PUBLIC OFFICIAL DIRECTORY

Create a public-safe official directory based on permissions/visibility settings.

Search may include:

```text
Ward
Office
Department
Representative
Official Service Contact
```

Do not expose the complete internal employee directory publicly.

---

# 478. INTERNAL EMPLOYEE DIRECTORY

Authorized users may access full workforce directory according to permission.

This remains separate from public official directory.

---

# 479. COMMUNICATION RECORD RETENTION

Official in-app communication related to:

```text
Complaints
Executive Instructions
Representative Contact
Office Messages
```

must remain auditable according to retention/security policy.

Do not treat it as disposable consumer chat.

---

# 480. MESSAGE DELETION

Normal users should not be able to silently remove official civic communication history.

If moderation/removal is required:

Preserve an audit event and reason according to later security/retention rules.

---

# 481. CITIZEN BLOCKING / ABUSE RESPONSE

For abusive use, authorized administration may:

```text
Rate Limit
Restrict Messaging
Moderate Content
Suspend Account where justified
```

Do not erase legitimate complaint history as a punishment.

---

# 482. RESPONSIVE DESIGN — ALL ADMIN ROLES

Every admin interface must work at practical tablet/mobile widths.

Desktop may provide enhanced analytics.

Operational tasks must remain usable from mobile.

---

# 483. PUBLIC ACCESS WITHOUT LOGIN

Citizens should be able to access without login:

```text
Public Dashboard
Public Notices
Ward/Zone Profiles
Public Official Directory
Public-Safe Complaint Tracking
```

Creating/managing a personal complaint requires appropriate identity verification.

---

# 484. PUBLIC SEARCH PRIVACY

Public search must not allow someone to discover:

```text
Citizen Phone
Citizen Name
Citizen Email
Private Address
```

through broad lookup.

---

# 485. MAYOR / ADMIN MOBILE SNAPSHOT

Mobile executive view may show:

```text
Today's Complaints
Resolved
Overdue
Urgent
Citizen Says Not Resolved
Attention Required
```

Keep deeper analysis available through drill-down or web.

---

# 486. PLATFORM ADMIN MOBILE EXPERIENCE

Platform Admin should be able to perform common tasks from a mobile/tablet where practical:

```text
Search Employee
View Responsibility
Change Simple Responsibility
View Ward
Check Assignment Gap
```

Complex bulk configuration may remain desktop-optimized.

---

# 487. TECHNICAL ADMIN MOBILE EXPERIENCE

Technical Admin mobile view should support:

```text
System Health
Important Incident
Backup Status
Notification Service
Safe Retry
```

Do not put dangerous infrastructure controls one tap away.

---

# 488. NO IMPLEMENTATION DURING INGESTION

During Specification Part 4 ingestion:

Do NOT:

```text
Create dashboards
Create Admin UI
Create Public Dashboard
Create Communication module
Create Notification module
Implement Mayor/Admin actions
Implement Platform Admin
Implement Technical Admin
```

Only append the specification and log genuine conflicts/ambiguities.

---

# 489. PART 4 SUMMARY — NON-NEGOTIABLE RULES

The following are hard requirements:

```text
Role-aware administration.

No giant one-size-fits-all admin dashboard.

Mayor and Administrator remain separate governance roles.

Mayor/Admin Command Center must be powerful but extremely simple.

Approximately six primary KPIs in first executive viewport.

First missed deadline is immediately visible under Mayor/Admin Attention Required.

First citizen Not Resolved is immediately visible under Mayor/Admin Attention Required.

Executive attention is oversight, not automatic ownership transfer.

No multi-level escalation UI.

Mayor/Admin actions use simple language.

Executive actions are auditable.

CEO, Department Head, Zone Officer, Ward Officer, representatives and monitoring users receive scope-specific dashboards.

Platform Super Admin is designed for non-technical administrative staff.

Platform Super Admin manages people, areas, governance, services, routing, deadlines, notices and access using simple language.

Technical Super Admin must also remain simple.

Technical infrastructure details are hidden behind Advanced / Technical Details.

Platform Admin and Technical Admin responsibilities are conceptually separated.

Public Accountability Dashboard requires no login.

Public dashboard shows honest resolution, overdue and needs-more-work information.

Public metrics must be explained.

Do not create misleading Ward rankings.

Protect exact citizen location and PII.

Separate raw evidence from approved public evidence.

Public interface is institution-first.

No covert political promotion.

Legitimate leadership actions may be factually attributed.

Provide Who Is Responsible public experience.

Provide structured complaint-linked communication.

Provide Mayor/Admin Office Inbox.

Do not create unrestricted personal chat.

Notifications must be role-specific and low-noise.

Support optional Citizen Pulse.

No partisan campaign system.

Public and internal reports must respect privacy.

All administrative actions must use understandable human language.

No implementation during specification ingestion.
```

---

# END OF SPECIFICATION PART 4

---

# SPECIFICATION PART 5

## Security, Authentication, RBAC, Database Integrity, API, Background Processing, Mobile, Testing, Deployment and Final Acceptance Criteria

This is **Specification Part 5** of the complete master specification for the Mymensingh City Citizen Service Platform.

---

# 490. SECURITY IS A CORE PRODUCT REQUIREMENT

This platform will contain:

```text
Citizen Data
Government Workforce Data
Complaint Evidence
Official Communication
Administrative Responsibility
Executive Instructions
Governance History
Operational Performance Data
```

Security must therefore be designed into the system from the beginning.

Do not treat security as a final-phase cosmetic task.

---

# 491. SECURITY MUST NOT MAKE THE UI COMPLICATED

Strong security is required.

However:

> Security complexity must remain mostly invisible during normal user workflows.

Examples:

* Citizen should not need to understand token rotation.
* Supervisor should not need to understand CSRF.
* Platform Admin should not need to understand cryptographic hashing.
* Technical Admin should see understandable security status first.

---

# 492. CORE ACCESS MODEL

Use:

```text
Role
+
Permission
+
Scope
```

Conceptually:

```text
User
 ↓
Role(s)
 ↓
Permission(s)
 ↓
Scope
 ↓
Allowed Resource / Action
```

Do NOT rely only on role names.

---

# 493. ROLE-BASED ACCESS CONTROL

Core roles include at least:

```text
Citizen
Public Viewer
Mayor
Administrator
Chief Executive Officer
General Ward Councillor
Reserved Women Councillor
Responsible Officer
Department Head
Department Officer
Zone Officer
Ward Officer
Supervisor
Team Leader
Field Worker
Cleaner / Operational Worker where login exists
Call Center Operator
Verification / Control Room Officer
Communications / Public Information Officer
Data / Monitoring Officer
Auditor / Read-Only Oversight
Platform Super Admin
Technical Super Admin
```

Additional configurable roles may exist later.

---

# 494. MAYOR AND ADMINISTRATOR PERMISSIONS

Mayor and Administrator remain separate roles.

They may receive similar executive permissions through configuration.

Do not hardcode the assumption that both always have identical authority.

---

# 495. GRANULAR PERMISSIONS

Potential permission families include:

```text
complaint.create
complaint.view
complaint.view_private
complaint.add_information
complaint.assign
complaint.start
complaint.complete_work
complaint.verify
complaint.confirm_resolution
complaint.needs_more_work
complaint.transfer
complaint.request_support
complaint.change_priority
complaint.cancel
complaint.view_history

task.view
task.assign
task.start
task.complete
task.return
task.add_evidence

employee.view
employee.create
employee.update
employee.change_posting
employee.change_responsibility
employee.manage_access

governance.view
governance.assign
governance.end_assignment
governance.view_history

ward.view
ward.manage
zone.view
zone.manage

department.view
department.manage
service.view
service.manage

routing.view
routing.manage

deadline.view
deadline.manage

notice.view
notice.publish
notice.manage

report.view
report.export

dashboard.ward
dashboard.zone
dashboard.department
dashboard.citywide
dashboard.public

executive.attention.view
executive.directive.issue
executive.explanation.request
executive.support.provide

communication.send
communication.view
communication.moderate

audit.view

user.manage
role.manage
permission.manage

system.health.view
system.integration.manage
system.backup.manage
system.security.manage
```

The final permission set will be normalized during synthesis.

---

# 496. SCOPE MODEL

Permission alone is insufficient.

Support scopes conceptually including:

```text
Self
Assigned
Team
Ward
Multiple Wards
Zone
Multiple Zones
Department
Service
Reserved Seat Coverage
Representative Area
Citywide
Systemwide
```

---

# 497. EXAMPLE SCOPE

Example:

```text
Role:
Supervisor

Permission:
complaint.assign

Scope:
Ward 19 + Waste Service
```

This Supervisor must not automatically assign unrelated complaints in Ward 20 or another department.

---

# 498. RESERVED WOMEN COUNCILLOR SCOPE

Reserved Women Councillor access must reflect all currently assigned covered Wards.

One login.

Multiple Ward coverage.

Do not create a separate account for every covered Ward.

---

# 499. RESPONSIBLE OFFICER SCOPE

A Responsible Officer may have:

```text
One Ward
Multiple Wards
```

Scope must follow active effective-dated governance assignment.

---

# 500. BACKEND AUTHORIZATION IS MANDATORY

Hiding a button is NOT security.

Every protected request must perform backend authorization.

Examples:

```text
API request
HTMX request
Form submission
File access
Report export
Admin action
Mobile request
```

---

# 501. IDOR PROTECTION

Prevent Insecure Direct Object Reference.

A user must not gain access merely by changing:

```text
Complaint ID
Employee ID
Ward ID
File ID
Message ID
Report ID
```

Authorization must verify resource scope.

---

# 502. CITIZEN AUTHENTICATION

Primary citizen authentication:

```text
Phone Number
+
OTP
```

This must remain simple.

Do not require:

```text
NID
Email
Password
```

for ordinary complaint creation unless future policy explicitly changes.

---

# 503. PHONE NORMALIZATION

Store citizen phone numbers in a normalized canonical representation.

Validate Bangladesh phone formats appropriately.

Do not depend on display formatting as identity.

---

# 504. OTP PROVIDER ABSTRACTION

Create an SMS/OTP provider interface.

Production provider must be configurable.

If credentials are unavailable:

```text
Development OTP Mode
Mock Provider
Documented Provider Setup
```

Do not block development.

---

# 505. OTP RATE LIMITING

Use Redis-backed controls for:

```text
OTP Request
OTP Verification
Repeated Failure
IP / Device Abuse where appropriate
```

Prevent brute-force attacks.

---

# 506. DEVELOPMENT OTP MODE

Development mode may use a known local/test OTP.

This must NEVER be enabled accidentally in production.

Production startup/config checks should detect unsafe development auth configuration.

---

# 507. TRUSTED CITIZEN SESSION

After successful OTP verification, avoid unnecessary repeated SMS costs.

Support a secure trusted session/device strategy.

Re-verification may be required for sensitive actions according to future policy.

---

# 508. STAFF AUTHENTICATION

Staff accounts are provisioned by authorized administration.

Use secure password authentication.

Passwords must never be stored in plaintext.

---

# 509. PASSWORD HASHING

Use:

```text
Argon2id
```

where supported by the target PHP environment.

Use appropriate secure parameters.

Never implement custom password encryption.

---

# 510. PRIVILEGED ACCOUNT MFA

Architect MFA for privileged roles including at minimum:

```text
Mayor
Administrator
CEO
Platform Super Admin
Technical Super Admin
```

Department Head may also support MFA.

The system should remain extensible to broader MFA policy.

---

# 511. USER ACCOUNT REVOCATION

Authorized administrators must be able to:

```text
Disable Account
Revoke Sessions
Revoke Mobile Tokens
Reset Authentication
Remove Access Without Deleting Employee History
```

---

# 512. EMPLOYEE PROFILE VS USER ACCOUNT

As specified earlier:

```text
Employee Profile
and
User Account
```

are separate concepts.

An employee can exist without login.

Disabling login must not delete the employee.

---

# 513. WEB SESSION STORAGE

Use Redis-backed web sessions.

Production session cookies must use appropriate:

```text
Secure
HttpOnly
SameSite
```

settings.

Use HTTPS in production.

---

# 514. SESSION FIXATION

Regenerate session identifiers after:

```text
Login
Privilege-sensitive authentication events
```

Prevent session fixation.

---

# 515. MOBILE AUTHENTICATION TOKENS

Use a secure revocable mobile authentication design.

Prefer:

```text
Secure opaque random access/session tokens
```

with server-side revocation capability.

Do not rely on permanently valid non-revocable tokens.

---

# 516. MOBILE TOKEN STORAGE

Flutter app must store authentication credentials/tokens using secure platform storage.

Do not store sensitive auth tokens in plain shared preferences.

---

# 517. TOKEN REVOCATION

Support:

```text
Logout Current Device
Logout All Devices
Admin Revocation
Expired Token
Disabled Account
```

---

# 518. CSRF PROTECTION

Browser state-changing requests must use CSRF protection.

This includes HTMX and normal form submissions where relevant.

---

# 519. XSS PROTECTION

Escape output according to context.

Treat:

```text
Citizen Description
Messages
Employee Notes
Public Notices
Imported Data
```

as untrusted input unless safely processed.

Do not render arbitrary HTML from citizen input.

---

# 520. SQL INJECTION PROTECTION

Use PDO prepared statements.

Never concatenate unsafe user input into SQL.

---

# 521. MASS ASSIGNMENT PROTECTION

Do not blindly map request payloads to database fields.

Each action should explicitly permit allowed fields.

---

# 522. CORS

Configure API CORS deliberately.

Do not use unrestricted production CORS without justification.

---

# 523. RATE LIMITING

Use Redis-backed rate limiting for at least:

```text
Login
OTP Request
OTP Verify
Complaint Creation
Complaint Follow-Up
Messaging
Public Search
Media Upload
API Abuse
Report Generation where needed
```

Return human-friendly localized errors.

---

# 524. FILE UPLOAD SECURITY

Uploaded files must be treated as untrusted.

Validate:

```text
MIME Type
File Signature where practical
File Size
Allowed Extension
Image Decode Success where applicable
```

Do not trust filename extension alone.

---

# 525. RANDOM FILE NAMES

Store uploaded files using random safe storage identifiers.

Do not use citizen-supplied filenames directly as executable/storage paths.

---

# 526. MEDIA STORAGE

Store uploaded evidence outside executable application paths where practical.

Never allow uploaded PHP/script execution.

---

# 527. IMAGE PROCESSING

For image evidence:

```text
Validate
Resize where appropriate
Generate thumbnail
Optimize
Strip unnecessary EXIF from public derivatives
```

Preserve required evidence metadata separately.

---

# 528. EVIDENCE METADATA

Maintain separately:

```text
Complaint / Task
Uploader
Server Timestamp
Device Timestamp where supplied
GPS where applicable
Original File Reference
Public Derivative Reference
Visibility
Moderation Status
```

---

# 529. PUBLIC EVIDENCE DERIVATIVE

Raw field evidence is not automatically public.

Support a safe derivative/approved public version.

Public visibility must be explicit.

---

# 530. MALWARE SCANNING FOUNDATION

Architect media processing so malware scanning can be added.

Do not make unavailable antivirus tooling block development.

---

# 531. SENSITIVE CITIZEN DATA

Minimize collection.

Normal civic complaints must not require unnecessary sensitive personal data.

Do not collect NID unless a future service genuinely requires it.

---

# 532. SENSITIVE FIELD ENCRYPTION

Where justified, sensitive database fields may use application-level encryption using established PHP cryptographic facilities.

Do not invent custom cryptography.

---

# 533. SEARCHABLE PHONE PRIVACY

If encrypted phone numbers must remain searchable, use a secure design such as:

```text
Encrypted Canonical Value
+
Keyed Lookup Hash
```

where appropriate.

Do not expose raw citizen numbers in broad indexes or public APIs.

---

# 534. LOGGING PRIVACY

Application logs must not unnecessarily contain:

```text
Passwords
OTP Codes in production
Raw Auth Tokens
NID
Sensitive Complaint Evidence
Full Citizen Phone where not required
```

---

# 535. STRUCTURED LOGGING

Application logs should support:

```text
Timestamp
Request ID
Route
User ID where safe
Role
Error Code
Duration
Environment
```

Use structured logging format where practical.

---

# 536. REQUEST ID

Generate a request/correlation identifier.

Return it in API responses where appropriate.

Use it for debugging without exposing stack traces.

---

# 537. ERROR HANDLING

Normal users must not see raw:

```text
PHP Stack Trace
SQL Error
Redis Error
Filesystem Path
Secret
Server Configuration
```

Show human-readable errors.

Log technical details securely.

---

# 538. AUDIT TRAIL — CORE REQUIREMENT

Important civic and administrative actions must be auditable.

Audit is not optional.

---

# 539. AUDIT EVENTS

At minimum record important actions related to:

```text
Authentication
Login Failure
Account Disable
Role Change
Permission Change
Employee Creation
Employee Update
Posting Change
Responsibility Change
Governance Assignment
Governance End
Ward / Zone Change
Service Ownership
Routing Configuration
Deadline Configuration
Complaint Creation
Routing
Assignment
Ownership Change
Work Start
Evidence Upload
Work Completion
Supervisor Verification
Citizen Confirmation
Needs More Work
Deadline Failure
Transfer
Priority Change
Support Request
Executive Attention
Executive Directive
Explanation Request
Official Notice
Sensitive Data Access where appropriate
System Settings
Backup Actions
```

---

# 540. AUDIT RECORD STRUCTURE

Conceptually audit records should support:

```text
Actor
Action
Entity Type
Entity ID
Previous Value where useful
New Value where useful
Reason
Timestamp
Request ID
IP / Device metadata where appropriate
```

---

# 541. APPEND-ORIENTED AUDIT

Normal application users must not rewrite or delete audit history.

Audit records should be append-oriented.

---

# 542. AUDIT ADMIN PRIVILEGES

Even Technical Super Admin must not have a routine button to:

```text
Clear Audit History
```

Any legally required archival/removal policy must use special controlled procedure with its own audit.

---

# 543. AUDIT INTEGRITY

Design the audit subsystem so integrity can be strengthened later through:

```text
Restricted DB privileges
Archive copies
Optional integrity hashes / hash chaining
```

Do not overengineer this before core audit reliability exists.

---

# 544. DATABASE MIGRATION SYSTEM

Because the project uses Core PHP, create a lightweight repeatable migration system.

Support commands/workflows equivalent to:

```text
Migration Status
Migrate
Create Migration
Rollback where safely supported
Seed
```

Do not maintain production schema only through manual SQL dumps.

---

# 545. DATABASE TRANSACTIONS

Use MySQL transactions for multi-step operations where partial completion would corrupt business state.

Examples:

```text
Complaint Creation + History
Assignment + Ownership History
Employee Transfer + Posting History
Governance Assignment Change
Citizen Confirmation + State Transition
Executive Directive + Audit
```

---

# 546. FOREIGN KEYS

Use foreign keys where appropriate to preserve data integrity.

Do not rely solely on application assumptions.

---

# 547. UNIQUE CONSTRAINTS

Use appropriate uniqueness rules for:

```text
Public Complaint Number
Role/Permission mappings where needed
Employee identifiers
Idempotency keys
Provider identifiers
Other business identifiers
```

---

# 548. CHECK CONSTRAINTS

Use MySQL 8 check constraints where they meaningfully protect valid data.

Do not use constraints that make legitimate historical data impossible.

---

# 549. EFFECTIVE-DATED RELATIONSHIPS

Important relationships should respect:

```text
effective_from
effective_to
```

including:

```text
Governance Assignment
Employee Posting
Temporary Responsibility
Ward-Zone Assignment
Team Assignment
Service Responsibility
```

---

# 550. HISTORICAL RECORD PRESERVATION

Do not overwrite relationships in ways that make old complaints historically incorrect.

If Ward responsibility changes today, yesterday's complaint history must remain understandable.

---

# 551. CURRENT STATE + HISTORY PATTERN

For high-read entities such as complaints, maintain:

```text
Current State
+
Append-Oriented History
```

Current state allows fast dashboards.

History preserves accountability.

---

# 552. COMPLAINT CONCURRENCY

Prevent race conditions such as:

```text
Two supervisors assign same complaint simultaneously
Worker completes task twice
Citizen confirmation submitted twice
Two admins change responsibility simultaneously
```

Use:

```text
Transactions
Database Constraints
Optimistic/Pessimistic Locking where appropriate
Redis Locking as supplementary coordination
```

---

# 553. REDIS LOCKS ARE NOT THE ONLY PROTECTION

Redis locks may improve coordination.

Critical data integrity must still be protected by database rules/transactions.

---

# 554. IDEMPOTENCY

Support idempotency for retry-prone actions.

At minimum consider:

```text
Complaint Creation
Task Start
Task Completion
Evidence Metadata Submission
Citizen Resolution Confirmation
Important Mobile Sync Actions
Payment-like future services if ever introduced
```

---

# 555. IDEMPOTENCY KEY

Client may send an idempotency key.

Server stores result/reference long enough to safely handle retry.

A repeated request with the same valid key should not create duplicate civic actions.

---

# 556. DATABASE CORE ENTITIES

The final ERD should consider at minimum:

```text
users
user_profiles
user_sessions_or_tokens

roles
permissions
role_permissions
user_roles
user_scopes

cities
zones
wards
ward_zone_history

persons
employees
employee_postings
employee_responsibilities
employee_skills

departments
service_units
offices

teams
team_members
team_assignments

representation_types
representatives
representation_assignments
representation_areas
reserved_seats
reserved_seat_wards

complaint_categories
complaint_subcategories
operational_classifications
priorities

routing_rules
service_deadline_rules

complaints
complaint_locations
complaint_media
complaint_participants
complaint_supporters

complaint_assignments
complaint_ownership_history
complaint_status_history
complaint_actions

field_tasks
task_assignments
task_evidence

support_requests

citizen_feedback
citizen_pulse

complaint_messages
internal_notes
office_messages

notifications
notification_preferences

city_notices

executive_attention
executive_directives
explanation_requests

audit_logs

background_jobs
job_attempts

settings
```

Exact table design must be normalized during synthesis.

---

# 557. FUTURE ENTITIES

Architect future extension for:

```text
city_assets
asset_types
asset_maintenance_history
vehicles
equipment
inspections
campaigns
polls
```

Do not make these future modules block the core build.

---

# 558. NO CASCADE DELETION OF IMPORTANT CIVIC HISTORY

Do not configure destructive cascading deletion that accidentally removes:

```text
Complaint History
Evidence Metadata
Audit Logs
Governance History
Posting History
```

because a parent account is disabled.

---

# 559. SOFT DELETE / ARCHIVAL

Use archival/inactive states where appropriate.

Do not use soft delete blindly for every table.

Critical historical records should remain explicitly preserved.

---

# 560. BACKGROUND PROCESSING

Use background workers for work that should not delay user requests.

Examples:

```text
SMS
Push
Email
Image Processing
Thumbnail Generation
Dashboard Aggregation
Deadline Checks
Notification Dispatch
Recurring Problem Analysis
Large Report Generation
Safe Cleanup Jobs
```

---

# 561. DURABLE BACKGROUND JOB INTENT

Critical civic jobs must not exist only in volatile Redis memory.

Persist important job state/intent in MySQL.

Redis may accelerate dispatch/coordination.

---

# 562. BACKGROUND JOB STATES

Support understandable internal states such as:

```text
queued
processing
completed
failed
retrying
dead
```

Normal Platform Admin should not need to manage these states.

Technical Admin may see human-friendly summaries.

---

# 563. JOB RETRY

Support:

```text
Retry Count
Retry Delay
Maximum Attempts
Last Error
Next Attempt
```

for suitable background jobs.

---

# 564. JOB IDEMPOTENCY

A retried job must not accidentally:

```text
Send same SMS repeatedly
Create duplicate notification
Process same evidence repeatedly
Create duplicate executive attention
```

Design jobs to be idempotent where necessary.

---

# 565. DEAD-LETTER / FAILED JOB HANDLING

After maximum attempts:

```text
Mark failed/dead
Keep history
Show Technical Admin meaningful issue
Allow safe retry where appropriate
```

Do not silently discard critical civic jobs.

---

# 566. SCHEDULED JOBS

Scheduled work may include:

```text
Deadline Detection
Executive Attention Aggregation
Dashboard Aggregation
Recurring Problem Analysis
Notification Digest
Temporary Assignment Expiry
Backup Verification
Data Cleanup according to policy
```

---

# 567. TEMPORARY RESPONSIBILITY EXPIRY

When temporary responsibility is near expiry or expires:

```text
Notify authorized administration
Detect responsibility gap
Do not silently route new complaints to an expired assignment
```

---

# 568. API DESIGN PRINCIPLE

REST API v1 remains:

```text
/api/v1/
```

API must use:

```text
Authentication
Authorization
Validation
Consistent Responses
Machine Error Codes
Pagination
Rate Limiting
Request IDs
Idempotency where required
```

---

# 569. API RESPONSE SUCCESS

Example:

```json
{
  "success": true,
  "data": {},
  "meta": {
    "request_id": "..."
  }
}
```

---

# 570. API RESPONSE ERROR

Example:

```json
{
  "success": false,
  "error": {
    "code": "COMPLAINT_NOT_FOUND",
    "message": "localized human-readable message"
  },
  "meta": {
    "request_id": "..."
  }
}
```

Clients should use:

```text
error.code
```

for logic.

Do not depend on English text matching.

---

# 571. API PAGINATION

Use server-side pagination.

Response metadata may contain:

```text
page
per_page
total where practical
next_cursor where cursor pagination is used
```

Avoid expensive total count operations when not necessary at huge scale.

---

# 572. API FILTERING

Complaint endpoints should support authorized filtering by:

```text
Status
Ward
Zone
Department
Service
Category
Priority
Date
Current Owner
Overdue
Needs More Work
```

according to permission.

---

# 573. API SORTING

Allow only whitelisted sortable fields.

Do not pass arbitrary client-provided SQL column names directly.

---

# 574. API VERSIONING

Do not break existing mobile clients casually.

Breaking API changes should use appropriate version strategy.

Initial:

```text
/api/v1/
```

---

# 575. API CONTRACT BEFORE MOBILE IMPLEMENTATION

Before substantial Flutter implementation:

```text
Core web/backend complaint flow must be stable.
API v1 contract must be documented and reviewed.
Then mobile implementation proceeds.
```

---

# 576. OPENAPI / MACHINE-READABLE API DOCUMENTATION

Where practical, generate or maintain OpenAPI-compatible documentation for API v1.

Do not make OpenAPI tooling a blocker if unavailable.

At minimum `docs/API_SPEC.md` must remain accurate.

---

# 577. MOBILE APPLICATION CORE

Flutter application must support role-aware navigation.

At minimum initial mobile experiences:

```text
Citizen
Field Worker
Supervisor
```

Also support an essential Mayor/Admin mobile snapshot.

Other administrative roles may use responsive web initially unless mobile-specific need exists.

---

# 578. CITIZEN MOBILE HOME

Primary mobile actions:

```text
সমস্যা জানান
Report a Problem

আমার অভিযোগ
My Complaints

নগরের অবস্থা
City Status

আমার এলাকা
My Area

নোটিফিকেশন
Notifications
```

Keep it simple.

---

# 579. CITIZEN MOBILE COMPLAINT FLOW

Support:

```text
Category
Location
Map Pin
Camera / Gallery where allowed
Short Description
Submit
```

Do not require department/officer selection.

---

# 580. MOBILE CAMERA

Flutter should support:

```text
Camera Capture
Image Preview
Image Compression
Retry
Upload Progress
```

For configured tasks:

```text
Live Camera Required
```

---

# 581. MOBILE LOCATION

Support:

```text
GPS Permission
Current Location
Map Pin
Manual Correction
Ward Detection / Confirmation
```

Handle denied permission gracefully.

Citizen must still have a reasonable fallback.

---

# 582. FIELD WORKER MOBILE HOME

Show:

```text
আমার আজকের কাজ
My Tasks Today

Current Task
Next Tasks
Completed Today
```

Avoid complex menus.

---

# 583. FIELD WORKER OFFLINE RESILIENCE

Support limited practical offline resilience:

```text
Cache Assigned Tasks
Queue Start Action
Queue Evidence Upload
Queue Completion Action
```

Do NOT build a complex full offline multi-master synchronization engine initially.

---

# 584. OFFLINE CONFLICT HANDLING

If server state changed while worker was offline:

Do not silently overwrite server truth.

Show understandable message.

Example:

> এই কাজটির দায়িত্ব পরিবর্তন হয়েছে। সর্বশেষ তথ্য দেখুন।  
> English: This task responsibility has changed. Please review the latest information.

---

# 585. MOBILE PENDING ACTIONS

User should be able to see:

```text
Pending Sync
```

in simple language when necessary.

Do not expose technical queue internals.

---

# 586. SUPERVISOR MOBILE EXPERIENCE

Supervisor mobile home:

```text
New
Ongoing
Due Today
Overdue
Completed
```

Allow fast:

```text
Assign Team
View Evidence
Request Support
Verify
Return for More Work
```

---

# 587. MAYOR / ADMIN MOBILE SNAPSHOT

Show:

```text
Today's Complaints
Resolved
In Progress
Overdue
Urgent
Citizen Says Not Resolved
Attention Required
```

Do not duplicate every desktop chart.

---

# 588. MOBILE PUSH NOTIFICATIONS

Create push provider abstraction.

Support modern Android/iOS push delivery.

Production credentials remain external configuration.

---

# 589. DEEP LINKS

Mobile notification may deep-link to:

```text
Complaint
Task
Attention Item
Notice
```

after authentication/authorization.

---

# 590. MOBILE SECURITY

Use:

```text
HTTPS
Secure Token Storage
Backend Authorization
Certificate-valid connections
Safe Local Cache
No secret API keys embedded where avoidable
```

---

# 591. MOBILE LOCAL DATA PRIVACY

Do not store unnecessary sensitive citizen or employee data indefinitely on the device.

Clear protected session cache appropriately after logout/revocation.

---

# 592. ANDROID PREPARATION

Prepare Flutter Android project with appropriate:

```text
Camera Permission
Location Permission
Notification Permission
Network Configuration
Deep Link Foundation
Release Build Documentation
```

Do not commit production signing secrets.

---

# 593. IOS PREPARATION

Prepare iOS Flutter project with:

```text
Camera Usage Description
Location Usage Description
Notification Foundation
Universal Link Foundation
Release Documentation
```

Do not falsely claim production signing if Xcode/macOS/signing credentials are unavailable.

---

# 594. ACCESSIBILITY ON MOBILE

Support:

```text
Large Tap Targets
Readable Bangla
Text Scaling where practical
Clear Error States
Screen Reader-friendly labels where practical
Color + Text Status
```

---

# 595. PERFORMANCE — QUERY DESIGN

Use indexed queries based on actual workload.

Important commonly filtered complaint fields may include:

```text
public_complaint_id
status
category_id
subcategory_id
ward_id
zone_id
department_id
service_unit_id
priority
operational_classification
current_owner
submitted_at
deadline_due_at
closed_at
```

---

# 596. INDEX STRATEGY

Create indexes based on real query patterns.

Do not index every database field blindly.

Use:

```text
EXPLAIN
Query profiling
Load tests
```

to validate important queries.

---

# 597. COMPOSITE INDEXES

Use composite indexes where real filters justify them.

Examples may include combinations involving:

```text
ward + status
department + status
deadline + status
category + submitted_at
```

Final design depends on measured queries.

---

# 598. FULL TEXT SEARCH

For large free-text search, do not rely on uncontrolled:

```text
LIKE "%text%"
```

across millions of records.

Where needed consider:

```text
MySQL FULLTEXT
```

or another justified search layer later.

Core exact/indexed search remains first.

---

# 599. PUBLIC DASHBOARD CACHING

Do not calculate expensive citywide metrics independently for every visitor.

Use:

```text
Cached Aggregates
Redis Cache
Background Aggregation
Durable MySQL Source Data
```

---

# 600. DASHBOARD RECOVERY

If Redis is cleared:

Public/Admin dashboards must rebuild from MySQL.

No civic statistics source-of-truth may exist only in Redis.

---

# 601. CACHE INVALIDATION

Cache must have deliberate:

```text
Keys
TTL
Invalidation/Rebuild strategy
```

Avoid stale critical status where unacceptable.

---

# 602. HTMX POLLING

Initial live-ish web updates may use HTMX polling.

Suggested examples:

Supervisor Queue:

```text
15–30 seconds
```

Executive Dashboard:

```text
30–60 seconds
```

Make intervals configurable.

Avoid unnecessary polling from inactive views where practical.

---

# 603. NO WEBSOCKET REQUIREMENT FOR INITIAL RELEASE

Do not introduce WebSockets merely because real-time sounds modern.

Use simpler approaches first unless measured requirements justify change.

---

# 604. IMAGE PERFORMANCE

Use:

```text
Client-side resize where appropriate
Server-side validation
Server-side optimization
Thumbnail generation
Lazy loading
Reasonable resolution limits
```

Do not upload 20MB images for ordinary complaint evidence when avoidable.

---

# 605. STATIC ASSET CACHING

Use:

```text
Versioned / hashed assets where practical
Cache-Control
Compression
```

Do not incorrectly cache dynamic/private HTML.

---

# 606. SERVER COMPRESSION

Configure appropriate production compression for suitable text/static responses.

Do not recompress already compressed media unnecessarily.

---

# 607. PHP OPCACHE

Production configuration should use OPcache.

Provide documented recommended baseline.

Final values depend on server resources.

---

# 608. PHP-FPM TUNING

Provide safe documented guidance for:

```text
Worker Count
Process Management
Memory
Timeouts
```

Do not use one dangerous universal configuration.

Final tuning depends on hardware and load tests.

---

# 609. NGINX TUNING

Provide production guidance for:

```text
HTTPS
Static Assets
Compression
Upload Limits
Timeouts
Rate Limits
PHP-FPM Proxying
Security Headers
```

---

# 610. SECURITY HEADERS

Configure appropriate web security headers such as where relevant:

```text
Content-Security-Policy
X-Content-Type-Options
Referrer-Policy
Frame protections
Permissions-Policy
Strict-Transport-Security in production HTTPS
```

Final CSP must remain compatible with required assets/maps.

---

# 611. ENVIRONMENT CONFIGURATION

Create:

```text
.env.example
```

Use environment/config files for:

```text
Database
Redis
App URL
Environment
Debug Mode
Encryption Keys
SMS
Email
Push
Maps
Worker Settings
Storage
Rate Limits
```

---

# 612. PRODUCTION DEBUG MODE

Production must never run with developer debug output exposed publicly.

Startup/deployment checklist should detect unsafe settings.

---

# 613. BACKUP STRATEGY

Production documentation must include backup strategy for:

```text
MySQL
Uploaded Evidence / Media
Configuration / Required Application Data
```

---

# 614. MYSQL BACKUP

Document:

```text
Backup Frequency
Retention
Encryption
Storage Location
Off-server Copy where appropriate
Restore Procedure
```

Do not define a successful backup only as “command ran”.

---

# 615. MEDIA BACKUP

Evidence/media backup policy must match retention requirements.

Do not assume database backup includes uploaded evidence.

---

# 616. BACKUP ENCRYPTION

Production backups containing sensitive data should be encrypted.

Do not store unprotected backup copies in public web directories.

---

# 617. RESTORE TEST

A backup strategy is incomplete until restore is tested.

Track:

```text
Last Successful Backup
Last Restore Test
Restore Test Result
```

where operationally possible.

---

# 618. DISASTER RECOVERY DOCUMENTATION

Document:

```text
Application Restore
Database Restore
Redis Loss Recovery
Media Restore
Worker Recovery
Configuration Recovery
```

Remember:

> Redis loss must not cause permanent civic data loss.

---

# 619. DATA RETENTION

Create configurable retention policies for appropriate data classes.

Do not invent legal retention periods as official MCC policy.

Provide architecture/configuration so verified policy can be applied later.

---

# 620. IMPORTANT HISTORY RETENTION

Complaint history, governance history and audit records should not be casually purged.

Retention/removal requires explicit policy and auditing.

---

# 621. LOCAL DEVELOPMENT SETUP

Documentation must explain how to run locally using:

```text
Windows
Laragon
PHP
MySQL
Redis
```

within:

```text
C:\laragon\www\amarmayor
```

---

# 622. LOCAL DEVELOPMENT SHOULD BE EASY

A competent developer should be able to:

```text
Clone/Open Project
Copy .env.example
Configure Database
Run Migrations
Seed Demo Data
Run App
Run Workers
Run Tests
```

without reverse engineering the project.

---

# 623. DEMO DATA

Create clearly fictional demo data.

Do NOT create fake data that could be mistaken for real MCC officials/employees.

---

# 624. DEMO GEOGRAPHY

Seed initial:

```text
3 Zones
33 General Wards
11 Reserved Seats
```

using the verified/specification mapping provided in Part 2.

Do not seed invented GIS polygons.

---

# 625. DEMO REPRESENTATIVES

Use obviously fictional names/labels such as:

```text
Demo Responsible Officer A
Demo Councillor A
```

Do not invent believable real current office holders.

---

# 626. DEMO ACCOUNTS

Create development-only demo accounts for major roles.

Examples:

```text
Platform Admin
Technical Admin
Mayor Demo
Administrator Demo
CEO Demo
Department Head
Zone Officer
Ward Officer
Supervisor
Field Worker
Councillor
Reserved Councillor
Call Center
Monitoring
Auditor
Citizen
```

Document credentials safely for development only.

---

# 627. PRODUCTION DEMO ACCOUNT SAFETY

Demo credentials must not be enabled automatically in production.

Production setup must require secure real account provisioning.

---

# 628. DATABASE SEEDING

Separate:

```text
Structural Seed
Demo Seed
Test Seed
```

Do not mix fictional demo users into required production structural migrations.

---

# 629. TESTING IS MANDATORY

The system is not complete because screens load.

Automated tests are required.

---

# 630. BACKEND TESTING

Use PHPUnit or another justified lightweight PHP testing tool.

Test:

```text
Authentication
OTP Rate Limits
Session Security
RBAC
Scope
IDOR Protection
Complaint Creation
Routing
Assignment
State Machine
Deadlines
Overdue
First Mayor/Admin Attention
Citizen Needs More Work
First Reopen Attention
Repeated Failure
Field Task
Evidence
Supervisor Verification
Citizen Confirmation
Duplicate Handling
Recurring Problem Detection
Employee Posting
Temporary Responsibility
Governance Assignment
Representation Scope
Public Privacy
Messaging
Notification Rules
Audit
Idempotency
Background Jobs
```

---

# 631. AUTHORIZATION TEST MATRIX

Create tests proving:

* Citizen cannot access staff administration.
* Field Worker cannot perform Supervisor actions.
* Supervisor cannot access unrelated Ward/Department scope.
* General Councillor cannot mark field task complete.
* Reserved Women Councillor can see all covered Wards but not unrelated Wards.
* Responsible Officer sees assigned Ward(s).
* Department Head sees only permitted department scope.
* Platform Admin does not automatically receive Technical Admin powers.
* Public user cannot access PII.
* Technical Admin cannot silently bypass audit.

---

# 632. COMPLAINT ACCEPTANCE TEST — NORMAL SUCCESS

Prove:

```text
Citizen uses Bangla
↓
OTP verified
↓
Reports mosquito problem
↓
Location captured
↓
Ward determined/confirmed
↓
Routing automatically finds responsible service
↓
Supervisor sees complaint
↓
Supervisor assigns team
↓
Worker starts
↓
Evidence uploaded
↓
Worker completes
↓
Supervisor verifies
↓
Citizen receives confirmation request
↓
Citizen confirms resolved
↓
Public statistics update correctly
↓
History remains auditable
```

---

# 633. COMPLAINT ACCEPTANCE TEST — DEADLINE FAILURE

Prove:

```text
Complaint receives deadline
↓
Deadline passes
↓
Responsible staff sees Overdue
↓
Mayor/Admin Attention Required includes the complaint immediately
↓
Operational ownership does not automatically transfer to Mayor/Admin
↓
Original deadline failure remains permanently recorded
```

---

# 634. COMPLAINT ACCEPTANCE TEST — FIRST REOPEN

Prove:

```text
Worker completes
↓
Supervisor verifies
↓
Citizen says Not Resolved
↓
Citizen sees Needs More Work
↓
reopen_count increments
↓
Mayor/Admin Attention Required immediately receives visibility
↓
Operational team continues work
↓
Original age/deadline history does not reset
```

---

# 635. REPEATED FAILURE ACCEPTANCE TEST

Prove:

```text
Complaint repeatedly receives completion attempts
↓
Citizen reports unresolved again
↓
System preserves all attempts
↓
Repeated Failure attention rises
↓
No Reopen Level 1/2/3 user workflow appears
↓
Mayor/Admin sees clear human-language issue
↓
Potential project/technical assessment can be identified
```

---

# 636. GOVERNANCE ACCEPTANCE TEST

Prove:

```text
Ward 5 has no elected General Councillor
↓
Platform Admin assigns Responsible Officer X
↓
X covers Ward 5 and Ward 6
↓
Reserved Women Councillor Y covers three configured Wards including Ward 5
↓
Public Ward 5 page shows both correctly
↓
Later elected Councillor Z becomes General Councillor for Ward 5
↓
Current public representation changes
↓
Historical assignment X remains preserved
```

---

# 637. TEMPORARY RESPONSIBILITY ACCEPTANCE TEST

Prove:

```text
Supervisor A goes on temporary leave
↓
Platform Admin assigns Supervisor B for specified dates
↓
Routing uses Supervisor B during active period
↓
Assignment expiry is detected
↓
Historical responsibility is preserved
↓
No new complaint routes silently to expired temporary assignment
```

---

# 638. EMPLOYEE ACCEPTANCE TEST

Prove a non-technical Platform Admin can:

```text
Add Employee
Assign Department
Assign Ward(s)
Assign Team
Assign Supervisor
Enable/Disable System Access
Change Posting Later
```

without using technical permission IDs or database terminology.

---

# 639. PLATFORM ADMIN SIMPLICITY TEST

A non-technical administrator should be able to:

```text
Add Employee
Change Responsibility
Assign Ward Officer
Assign Responsible Officer
Assign General Councillor
Assign Reserved Women Councillor
Add Complaint Category
Change Expected Service Time
Publish Notice
```

using guided UI.

---

# 640. TECHNICAL ADMIN SIMPLICITY TEST

A non-expert Technical Admin should answer from first screen:

```text
Is website working?
Is database working?
Are notifications working?
Are background jobs working?
Was backup successful?
Is there an important security issue?
```

without reading raw infrastructure logs.

---

# 641. PUBLIC PRIVACY TEST

Verify public users cannot retrieve:

```text
Citizen Phone
Citizen Email
Exact Private GPS
Private Complaint Messages
Internal Notes
Unapproved Original Evidence
Private Employee Data
Auth Data
```

through:

```text
UI
API
Search
Export
Direct Object URL
```

---

# 642. FILE SECURITY TEST

Test:

```text
Invalid MIME
Executable Upload
Oversized File
Malformed Image
Path Traversal Filename
Unauthorized Evidence Access
```

---

# 643. CSRF TEST

Verify browser state-changing actions reject invalid/missing CSRF tokens where required.

---

# 644. XSS TEST

Test user-controlled text for stored/reflected XSS protection.

---

# 645. SQL INJECTION TEST

Verify key search/filter/form inputs are safe against SQL injection.

---

# 646. RATE LIMIT TEST

Test:

```text
OTP Flood
Login Flood
Complaint Spam
Message Spam
Public Search Abuse
```

---

# 647. MOBILE TESTING

Flutter tests should include:

```text
Unit Tests
Widget Tests
Integration Tests where practical
```

Cover:

```text
Bangla/English
Authentication State
Citizen Complaint
Camera/Location Error Handling
My Complaints
Field Worker Tasks
Supervisor Actions
Pending Sync
Offline Retry
Deep Link Authorization
Role Navigation
```

---

# 648. BROWSER VERIFICATION

Use Antigravity browser capabilities to inspect real UI during implementation.

Verify:

```text
Bangla
English
Desktop
Tablet
Mobile Width
```

for at least:

```text
Public Home
Citizen Complaint
Citizen Tracking
Supervisor
Field Worker web fallback where applicable
Mayor/Admin
Platform Admin
Technical Admin
Councillor / Responsible Officer
Public Dashboard
```

---

# 649. ACCESSIBILITY VERIFICATION

Check:

```text
Keyboard
Focus
Labels
Contrast
Tap Targets
Status Meaning
Bangla Readability
Error Feedback
```

---

# 650. LOAD TESTING

Create repeatable load-testing scripts/instructions.

Use fictional data.

Test representative endpoints/pages:

```text
Public Dashboard
Complaint Creation
Complaint List
Complaint Filter
Supervisor Queue
Mayor/Admin Metrics
Complaint Tracking API
Employee Search
```

---

# 651. SCALE DATA GENERATOR

Provide safe generators for:

```text
10,000 complaints
100,000 complaints
1,000,000 complaints
```

with fictional related data.

Allow deterministic/repeatable generation where practical.

---

# 652. LOAD TEST DOCUMENTATION

Record:

```text
Dataset Size
Concurrency
Endpoint
p50
p95
Error Rate
Environment
Known Bottleneck
```

Do not claim production capacity from unrealistic local tests.

---

# 653. PERFORMANCE REGRESSION

Important performance tests should be repeatable so future changes can detect major regressions.

---

# 654. SECURITY REVIEW BEFORE PRODUCTION

Perform a pre-production security review covering:

```text
Authentication
Authorization
Scope
PII
File Upload
Rate Limiting
CSRF
XSS
SQL Injection
Session/Token
Admin Powers
Audit
Secrets
Debug Configuration
Backups
HTTPS
```

---

# 655. PRODUCTION SERVER TARGET

Production remains:

```text
Linux
Nginx
PHP-FPM
OPcache
MySQL 8+
Redis
HTTPS
Background Workers
Scheduled Jobs
```

---

# 656. INFRA DIRECTORY

Use:

```text
infra/
```

for documented/configuration examples such as:

```text
Nginx
PHP-FPM
Worker Service
Cron / Scheduler
Environment
Deployment Notes
```

Do not store production secrets.

---

# 657. BACKGROUND WORKER SUPERVISION

Production workers should run under an appropriate process supervisor such as:

```text
systemd
```

or another justified process manager.

Document restart/recovery.

---

# 658. SCHEDULED JOB EXECUTION

Use production-safe scheduler configuration.

Document:

```text
Command
Frequency
Locking
Failure Handling
```

Prevent duplicate overlapping scheduled jobs where harmful.

---

# 659. HTTPS

Production access must use HTTPS.

Document certificate setup/renewal strategy.

Do not hardcode certificate paths in application logic.

---

# 660. DATABASE PRODUCTION USER

Use least-privilege database accounts where practical.

Application should not routinely connect as MySQL root.

---

# 661. REDIS PRODUCTION SECURITY

Production Redis must not be publicly exposed.

Use protected network/configuration.

Use authentication where infrastructure requires.

---

# 662. STORAGE PERMISSIONS

Production file permissions must prevent:

```text
Uploaded Script Execution
Unauthorized Source Access
Secret Exposure
```

---

# 663. DEPLOYMENT CHECKLIST

Create a production checklist including:

```text
Environment = production
Debug disabled
HTTPS enabled
Database configured
Redis configured
Migrations applied
Workers running
Scheduler running
Storage writable
Sensitive directories protected
Rate limiting enabled
Backup configured
Backup tested
SMS configured
Push configured where available
Email configured where used
Admin account secured
Demo accounts disabled
Security review passed
```

---

# 664. DEPLOYMENT MUST NOT AUTO-DESTROY DATA

Deployment scripts must never automatically:

```text
Drop Production Database
Reset Complaints
Clear Audit History
Delete Media
```

---

# 665. MIGRATION SAFETY

Production migrations should:

```text
Backup first where appropriate
Avoid destructive changes without review
Use staged migration strategy for large tables where needed
Record migration status
```

---

# 666. OBSERVABILITY

Technical Admin should have understandable system health based on real technical monitoring.

Monitor where practical:

```text
Application Health
Database
Redis
Background Workers
Failed Job Count
Storage
SMS Provider
Push Provider
Email
Backup
Scheduled Jobs
```

---

# 667. HEALTH ENDPOINTS

Create protected/safe health checks as appropriate.

Public health endpoints must not reveal secrets, internal topology or sensitive version information.

---

# 668. ERROR ALERTING

Create architecture allowing technical incidents to produce alerts.

Do not require a specific paid monitoring provider.

Use provider abstraction/documentation.

---

# 669. CHANGE MANAGEMENT

Material configuration changes should be:

```text
Auditable
Effective-dated where applicable
Reversible where sensible
Clearly confirmed
```

---

# 670. NO DIRECT PRODUCTION EDITING ASSUMPTION

Do not design the workflow around developers manually editing production database rows for normal administration.

Normal organization/service changes must be manageable through safe UI.

---

# 671. API AND DATABASE DOCUMENTATION

After synthesis create/update:

```text
docs/DATABASE_ERD.md
docs/API_SPEC.md
docs/ROLE_PERMISSION_MATRIX.md
docs/SECURITY_MODEL.md
docs/COMPLAINT_STATE_MACHINE.md
docs/ROUTING_MODEL.md
docs/UX_RULES.md
```

These must stay synchronized with implementation.

---

# 672. FINAL IMPLEMENTATION PHASE ORDER

After specification synthesis and consistency review, implement in this order.

## Phase 0 — Specification Synthesis

Complete:

```text
ERD
Governance Model
Workforce Model
RBAC + Scope Matrix
Complaint State Machine
Routing Model
Deadline / Overdue / Reopen Model
Security Model
API Contract
UX Rules
Architecture Review
```

Do not code business modules before this is reviewed internally.

---

# 673. PHASE 1 — CORE BACKEND FOUNDATION

Implement:

```text
Bootstrap
Configuration
Environment
PSR-4 Autoloading
Routing
Controllers
Service Layer
Repository Layer
PDO
Redis Abstraction
Validation
Error Handling
Request IDs
Logging
Migration System
Testing Foundation
```

---

# 674. PHASE 2 — DATABASE FOUNDATION

Implement:

```text
Core Schema
Constraints
Indexes
Migrations
Structural Seeds
Demo Seeds
ERD Validation
```

---

# 675. PHASE 3 — BILINGUAL FOUNDATION

Implement:

```text
Bangla Default
English Secondary
Translation Resources
Language Preference
Localized Validation
Localized Errors
Bangla Date/Number Presentation
```

---

# 676. PHASE 4 — AUTHENTICATION, RBAC AND SCOPE

Implement:

```text
Citizen OTP Abstraction
Staff Authentication
Sessions
Mobile Tokens
Roles
Permissions
Scopes
Backend Authorization
Audit Foundation
```

---

# 677. PHASE 5 — CITY, GOVERNANCE AND WORKFORCE

Implement:

```text
City
Zones
Wards
Departments
Service Units
Offices
Employees
Teams
Postings
Responsibilities
Governance Representation
Reserved Seats
Temporary Assignments
Guided Admin Management
```

---

# 678. PHASE 6 — COMPLAINT CONFIGURATION

Implement:

```text
Categories
Subcategories
Priorities
Operational Classification
Routing Configuration
Expected Service Time Rules
```

---

# 679. PHASE 7 — CITIZEN COMPLAINT CORE

Implement:

```text
Citizen Portal
Complaint Submission
Location
Media
Public Complaint Number
Tracking
My Complaints
Complaint Timeline
```

---

# 680. PHASE 8 — AUTOMATIC ROUTING

Implement:

```text
Ward Detection
Service Responsibility
Active Owner
Temporary Responsibility
Routing Fallback
Responsibility Gap
```

---

# 681. PHASE 9 — FIELD OPERATIONS

Implement:

```text
Supervisor Dashboard
Team Assignment
Field Tasks
Worker Flow
Evidence
Cannot Complete
Support Requests
```

---

# 682. PHASE 10 — RESOLUTION QUALITY

Implement:

```text
Supervisor Verification
Citizen Confirmation
Needs More Work
Reopen Count
Completion Attempts
Case Age
Recurring Problem Foundations
```

---

# 683. PHASE 11 — DEADLINE AND EXECUTIVE ATTENTION

Implement:

```text
Expected Service Time
Deadline Detection
Overdue
First Deadline → Mayor/Admin Attention
First Reopen → Mayor/Admin Attention
Repeated Failure
No Multi-Level Escalation Ladder
```

---

# 684. PHASE 12 — ROLE-SPECIFIC ADMINISTRATION

Implement:

```text
Ward Officer
Zone Officer
Department Head
CEO
General Councillor
Reserved Women Councillor
Responsible Officer
Monitoring
Auditor
```

---

# 685. PHASE 13 — MAYOR / ADMINISTRATOR COMMAND CENTER

Implement:

```text
Executive KPIs
Attention Required
City Map
Drill-Down
Ask for Action
Provide Support
Request Explanation
Request Inspection
Priority
Daily Brief
```

---

# 686. PHASE 14 — PLATFORM SUPER ADMIN

Implement:

```text
People
Areas
Governance
Services
Routing
Deadlines
Responsibility Gaps
Notices
User Access Presets
Guided Wizards
```

Keep completely non-technical.

---

# 687. PHASE 15 — TECHNICAL SUPER ADMIN

Implement:

```text
Simple System Health
Communication Services
Backup Status
Security
Logs
Advanced Technical Details
Safe Retry Actions
```

Keep first-level UI understandable to non-experts.

---

# 688. PHASE 16 — COMMUNICATION

Implement:

```text
Complaint Messaging
Representative Contact
Mayor/Admin Office Inbox
Office Triage
Official Contact Directory
Anti-Abuse Controls
```

---

# 689. PHASE 17 — PUBLIC ACCOUNTABILITY

Implement:

```text
Public Dashboard
Public Complaint Tracking
Ward Profiles
Zone Profiles
Department Profiles
Who Is Responsible
Public Official Directory
City Notices
```

---

# 690. PHASE 18 — BACKGROUND PROCESSING AND NOTIFICATIONS

Implement:

```text
Durable Jobs
Worker Processing
Retry
Scheduled Jobs
SMS
Push Abstraction
Email Abstraction
Role-Specific Notifications
```

---

# 691. PHASE 19 — STABILIZE API V1

Before Flutter:

```text
Review API
Fix Inconsistent Contracts
Complete API Documentation
Freeze Core v1 Contracts
```

---

# 692. PHASE 20 — FLUTTER MOBILE

Implement:

```text
Citizen
Field Worker
Supervisor
Mayor/Admin Snapshot

Android
iOS
```

with shared backend.

---

# 693. PHASE 21 — ADVANCED OPERATIONAL INTELLIGENCE

Implement:

```text
Duplicate Suggestions
I Am Also Affected
Recurring Problems
Hotspots
Citizen Pulse
Advanced Trends
Executive Intervention Analytics
```

Do not make AI mandatory.

---

# 694. PHASE 22 — SECURITY HARDENING

Run complete:

```text
Authorization Review
Privacy Review
Upload Review
CSRF/XSS/SQLi Tests
Rate Limit Review
Audit Review
Token/Session Review
Secrets Review
```

Fix failures.

---

# 695. PHASE 23 — PERFORMANCE HARDENING

Run:

```text
Large Dataset Tests
Index Review
Query Review
Cache Review
Load Tests
Media Optimization
Server Configuration Review
```

---

# 696. PHASE 24 — PRODUCTION PREPARATION

Create/finalize:

```text
Nginx
PHP-FPM
OPcache
Workers
Scheduler
HTTPS
Backup
Restore
Monitoring
Deployment Checklist
Environment Documentation
```

---

# 697. PHASE 25 — FINAL END-TO-END VERIFICATION

Run:

```text
Backend Tests
Security Tests
Role/Scope Tests
Browser Tests
Bangla Tests
English Tests
Flutter Tests
Load Tests
Backup/Restore Checks
Deployment Checklist
```

Do not declare production-ready with failing critical tests.

---

# 698. PHASE CHECKPOINT

After every major implementation phase update:

```text
docs/IMPLEMENTATION_STATUS.md
```

Include:

```text
Completed
Tests
Files/Modules
Known Issues
Architecture Deviations
External Blocks
Next Phase
```

---

# 699. DO NOT STOP AT A PLAN

After specification synthesis is complete and implementation is authorized:

Do not merely generate:

```text
Plan
Scaffolding
Mock UI
```

and stop.

Implement working modules phase-by-phase.

---

# 700. DO NOT SILENTLY SKIP REQUIREMENTS

If a requirement cannot be implemented because of:

```text
Credential
Operating System Limitation
Missing External Service
Missing Verified Government Data
Missing GIS Data
```

record:

```text
Requirement
Reason Blocked
What Was Implemented Instead
External Step Required
```

Then continue other work.

---

# 701. EXTERNAL CREDENTIALS ARE NOT PROJECT BLOCKERS

Examples:

```text
SMS Provider
Push Provider
Email Provider
Map Provider
Apple Signing
Production Server
```

Create:

```text
Adapter
Mock
Config Placeholder
Documentation
```

and continue.

---

# 702. DO NOT INVENT EXTERNAL SUCCESS

Do not claim:

> SMS sent successfully

if only mock mode ran.

Do not claim:

> iOS production build signed

without actual signing.

Do not claim:

> production deployed

if only local development exists.

---

# 703. SOURCE OF TRUTH AFTER INGESTION

The authoritative product specification is:

```text
docs/MASTER_SPEC.md
```

Derived architecture documents must remain consistent with it.

---

# 704. PROJECT CONSTITUTION

```text
docs/PROJECT_CONSTITUTION.md
```

must contain concise permanent non-negotiable engineering rules derived from the final specification.

It must not replace MASTER_SPEC.md.

---

# 705. DECISION LOG

When material ambiguity requires a decision:

Record it in:

```text
docs/DECISIONS.md
```

with:

```text
Decision
Reason
Alternatives
Impact
```

---

# 706. NO UNAUTHORIZED STACK CHANGES

Do not replace:

```text
Core PHP
MySQL
Redis
HTMX
Vanilla JavaScript
Bootstrap
Flutter
```

with another stack because it is easier for the agent.

---

# 707. NO OVERENGINEERING

Prefer:

```text
Simple
Explicit
Maintainable
Understandable
Testable
```

Avoid:

```text
Unnecessary abstractions
General-purpose framework creation
Excessive dependency injection complexity
Unnecessary event systems
Unnecessary microservices
```

This system should remain maintainable by competent PHP developers.

---

# 708. MONOLITH FIRST

Use a well-structured modular monolith for the backend.

Do not prematurely split the platform into microservices.

External integrations may use adapters.

---

# 709. ONE BACKEND SOURCE OF BUSINESS RULES

```text
Web
Android
iOS
```

must not independently implement authoritative:

```text
Routing
Permission
Deadline
Complaint State
Executive Attention
```

Backend remains authoritative.

---

# 710. ACTION-DRIVEN UI

Users perform meaningful actions.

Do not ask normal users to manually manipulate technical state codes.

---

# 711. SIMPLE ADMIN — FINAL HARD REQUIREMENT

The following must remain simple enough for non-technical users:

```text
Platform Super Admin
Technical Super Admin
Mayor/Admin
Councillors
Responsible Officers
Supervisors
Field Workers
```

Backend sophistication must never become dashboard sophistication.

---

# 712. IMMEDIATE MAYOR / ADMIN OVERSIGHT — FINAL HARD REQUIREMENT

The following immediately become executive-visible:

```text
First Missed Deadline
First Citizen Not Resolved / Needs More Work
```

Do NOT reintroduce a multi-level escalation ladder during implementation.

---

# 713. OPERATIONAL OWNERSHIP — FINAL HARD REQUIREMENT

Executive visibility does not automatically remove the accountable operational owner.

The responsible service remains accountable unless responsibility is explicitly changed.

---

# 714. PUBLIC ACCOUNTABILITY — FINAL HARD REQUIREMENT

Public statistics must show honest service reality.

Do not hide:

```text
Overdue
Needs More Work
Citizen Reopen
Late Completion
```

to inflate performance.

---

# 715. PRIVACY — FINAL HARD REQUIREMENT

Do not expose:

```text
Citizen PII
Exact private location
Private messages
Internal notes
Unapproved evidence
Private employee information
```

through public interfaces.

---

# 716. GOVERNANCE — FINAL HARD REQUIREMENT

Support:

```text
Mayor
Administrator
CEO
General Councillor
Reserved Women Councillor
Responsible Officer
Acting/Temporary Responsibility
```

with:

```text
Effective Dates
History
Official Authority
Multiple Ward Coverage where required
```

---

# 717. WORKFORCE — FINAL HARD REQUIREMENT

The platform must manage all verified MCC workforce data needed for operations.

Support:

```text
Employees
Officers
Supervisors
Cleaners
Field Workers
Teams
Posting
Transfer
Temporary Responsibility
Skills
Official Contacts
```

without requiring every employee to have a login.

---

# 718. BILINGUAL — FINAL HARD REQUIREMENT

Bangla remains primary.

English remains secondary.

Every major final interface must be verified in both languages.

---

# 719. THREE CLIENTS — FINAL HARD REQUIREMENT

Deliver:

```text
Responsive Web
Android
iOS
```

using one authoritative backend.

---

# 720. FINAL DEFINITION OF DONE

The project is not complete until the locally executable system demonstrates:

```text
Citizen Complaint
Automatic Routing
Supervisor Assignment
Worker Execution
Evidence
Verification
Citizen Resolution Check
Needs More Work
Deadline Failure
Mayor/Admin Immediate Attention
Public Statistics
Governance Assignment
Employee Management
Platform Admin
Technical Admin
RBAC/Scope
Audit
Bangla/English
Web
Android
iOS Source/Build Readiness
Security Tests
Performance Tests
Documentation
```

---

# 721. FINAL IMPLEMENTATION REPORT

At the end of implementation produce a concise report containing:

```text
Completed Modules
Incomplete Modules
External Credentials Required
Test Results
Security Results
Performance Results
Web Status
Android Status
iOS Status
Database Status
Redis Status
Background Worker Status
Backup/Restore Status
Demo Accounts
Local Run Commands
Production Deployment Steps
Known Limitations
Recommended Next Actions
```

Do not hide failures.

---

# 722. SPECIFICATION INGESTION RULE

During Part 5 ingestion:

Do NOT begin application implementation.

Only append this specification and record genuine conflicts/ambiguities.

Implementation begins only after the user sends:

```text
SPECIFICATION COMPLETE
```

and the required specification synthesis/consistency review is completed.

---

# 723. PART 5 SUMMARY — NON-NEGOTIABLE RULES

The following are hard requirements:

```text
Use Role + Permission + Scope authorization.

Backend authorization is mandatory.

Protect against IDOR.

Citizen auth uses Phone + OTP.

Staff passwords use Argon2id.

Support privileged MFA architecture.

Web sessions use Redis.

Mobile tokens are secure and revocable.

Protect against CSRF, XSS, SQL injection, mass assignment and unsafe CORS.

Use rate limiting.

Secure file upload and evidence storage.

Protect citizen PII.

Maintain append-oriented audit history.

Use database migrations.

Use transactions, foreign keys, constraints and idempotency.

Preserve historical effective-dated relationships.

Critical background job intent is durable.

Redis is not the durable job source of truth.

Use API v1 with consistent error codes.

Stabilize API before Flutter.

Flutter supports Android and iOS.

Support limited safe offline field operations.

Use server-side pagination/filtering.

Use measured indexing and caching.

Create 10k / 100k / 1M fictional scale-test data.

Provide backup and tested restore documentation.

Use Linux + Nginx + PHP-FPM + OPcache + MySQL + Redis in production.

Testing is mandatory.

Authorization/privacy/security tests are mandatory.

First deadline failure immediately becomes Mayor/Admin-visible.

First citizen Not Resolved immediately becomes Mayor/Admin-visible.

No multi-level escalation ladder.

Platform Super Admin must remain non-technical.

Technical Super Admin must also remain simple.

Bangla is primary.

English is secondary.

Web + Android + iOS share backend business rules.

Do not change the required technology stack.

Do not overengineer.

Do not invent official government data.

Do not falsely claim external integration/deployment success.

Do not start implementation until SPECIFICATION COMPLETE is sent and synthesis is finished.
```

---

# END OF SPECIFICATION PART 5
