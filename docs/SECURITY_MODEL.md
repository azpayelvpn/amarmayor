# Security, Authentication & Privacy Architecture Model

> **Document Status:** Authoritative Security Architecture Specification  
> **Source of Truth:** [docs/MASTER_SPEC.md](file:///C:/laragon/www/amarmayor/docs/MASTER_SPEC.md) (Sections 490–543, 641–646, 654)

---

## 1. Security Principles & Threat Model

The platform processes sensitive citizen complaints, municipal workforce records, executive directives, and location evidence. Security is designed into every layer without increasing user-interface friction.

$$\begin{array}{rcccl}
\text{Client Request} & \longrightarrow & \mathbf{WAF\ /\ Rate\ Limiter} & \longrightarrow & \text{Redis Token Bucket} \\
& & \mathbf{CSRF\ /\ CORS\ Check} & \longrightarrow & \text{HMAC Session Token} \\
& & \mathbf{Authentication} & \longrightarrow & \text{Argon2id / Phone+OTP} \\
& & \mathbf{RBAC\ +\ Scope} & \longrightarrow & \text{Backend Scope Verification} \\
& & \mathbf{Data\ Access} & \longrightarrow & \text{PDO Prepared Statements} \\
& & \mathbf{Audit\ Logging} & \longrightarrow & \text{Append-Only MySQL Audit Log}
\end{array}$$

---

## 2. Authentication & Credential Management

### 2.1 Citizen Authentication (Phone + OTP)
1. **Flow:** Citizen enters normalized Bangladesh phone number (`01XXXXXXXXX` / `+8801XXXXXXXXX`).
2. **OTP Generation:** Secure 6-digit random code generated, stored in Redis with 3-minute TTL.
3. **Rate Limiting:** Maximum 3 OTP requests per phone / IP per 10 minutes; maximum 5 verification attempts before temporary lock.
4. **Trusted Sessions:** Upon successful OTP verification, a secure session cookie (Web) or revocable token (Mobile) is issued. Citizens do not need repeated OTPs for standard complaint tracking on the same trusted device.
5. **Private Phone Lookup:** Canonical phones are stored with HMAC-SHA256 lookup hashes (`phone_lookup_hash`) to enable database indexing without exposing raw numbers in logs or public queries.

### 2.2 Staff & Administrative Authentication
1. **Password Hashing:** `Argon2id` (PHP `password_hash($pass, PASSWORD_ARGON2ID, ['memory_cost' => 65536, 'time_cost' => 4, 'threads' => 2])`).
2. **Session Regeneration:** `session_regenerate_id(true)` executed upon every successful login and privilege change to prevent session fixation.
3. **Privileged Account MFA:** Multi-Factor Authentication (TOTP) supported for Mayor, Administrator, CEO, Platform Super Admin, and Technical Super Admin.

### 2.3 Mobile App Token Security
1. **Token Architecture:** Secure opaque random 64-character bearer tokens stored in MySQL (`user_tokens`).
2. **Client Storage:** Mobile Flutter client stores tokens strictly in native secure platform keystores (iOS Keychain / Android EncryptedSharedPreferences).
3. **Revocation:** Tokens can be instantly revoked individually (logout device), globally (logout all devices), or administratively upon account suspension.

---

## 3. Web & API Hardening

1. **Cross-Site Request Forgery (CSRF):**
   * All state-changing POST, PUT, DELETE requests (standard HTML forms and HTMX partials) require a cryptographic CSRF token embedded in headers (`X-CSRF-Token`) or hidden fields.
2. **Cross-Site Scripting (XSS):**
   * All dynamic user content (descriptions, notes, messages) is contextually HTML-escaped (`htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8')`).
   * No raw unescaped HTML injection permitted.
3. **SQL Injection Prevention:**
   * 100% of database queries utilize **PDO prepared statements** with bound parameters. Zero dynamic SQL string concatenation.
4. **Mass-Assignment Defense:**
   * Controllers explicitly permit incoming fields using validated Data Transfer Objects (DTOs). Request bodies are never blindly mapped to database models.
5. **Security Headers:**
   * Production Nginx transmits: `Content-Security-Policy`, `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy`, and `Strict-Transport-Security: max-age=31536000; includeSubDomains`.

---

## 4. File Upload & Media Protection

1. **Untrusted Treatment:** All uploaded images and attachments are treated as potentially hostile.
2. **Validation Matrix:**
   * File extension check against whitelist (`jpg`, `jpeg`, `png`, `webp`, `pdf`).
   * MIME type check via `finfo_file` (not client headers).
   * Image decode verification via `imagecreatefromstring` to detect polyglot exploits.
   * Maximum file size: 10 MB per image.
3. **Safe Storage:**
   * Stored using cryptographically random UUID filenames (`storage/uploads/{uuid}.webp`).
   * Stored outside the public web root; accessed strictly via an authorized media delivery controller.
   * Direct script execution (`.php`, `.phtml`, `.cgi`) is strictly disabled in upload directories at Nginx level.
4. **Derivative Processing & Privacy:**
   * Raw evidence undergoes server-side resizing, thumbnail generation, and EXIF metadata stripping before generating public derivatives.
   * Original raw evidence with device GPS is accessible only to authorized operational staff.

---

## 5. Citizen Privacy & Data Protection

1. **PII Masking:** Citizen phone numbers, emails, and full identities are hidden from public accountability dashboards and public tracking pages.
2. **Geographic Obfuscation:** Public complaint maps display **coarse coordinates** (`public_latitude`, `public_longitude` rounded to ~100m) and neighborhood names (e.g., *"Ganginar Par, Ward 19"*), never exact residential doorstep GPS coordinates.
3. **Sensitive Classification:** Complaints marked `is_sensitive = 1` are completely excluded from public activity feeds.

---

## 6. Immutable Append-Only Audit Trail

Every material system action produces an auditable entry in `audit_logs`:
* **Logged Events:** Authentication attempts, role/permission changes, employee postings, governance assignments, complaint state transitions, ownership transfers, executive directives, explanation requests, and backup actions.
* **Audit Record Schema:** `actor_user_id`, `event_category`, `action`, `entity_type`, `entity_id`, `old_values` (JSON), `new_values` (JSON), `reason`, `request_id`, `ip_address`, `user_agent`, `created_at`.
* **Zero Deletion:** Normal and technical administrator interfaces have no capability to clear or modify audit logs.
