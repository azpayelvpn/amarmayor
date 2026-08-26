# REST API v1 Specification

> **Document Status:** Authoritative API Contract  
> **Base URL:** `/api/v1/`  
> **Source of Truth:** [docs/MASTER_SPEC.md](file:///C:/laragon/www/amarmayor/docs/MASTER_SPEC.md) (Sections 568–576, 671, 691)

---

## 1. API Architecture & Standard Envelopes

1. **Protocol:** HTTPS RESTful JSON API.
2. **Authentication:** Bearer token (`Authorization: Bearer <opaque_token>`) for Mobile / API clients, session cookie for Web clients.
3. **Idempotency:** State-changing requests accept optional header `X-Idempotency-Key: <uuid>` to safely prevent duplicate submissions on retry.
4. **Correlation:** Every response includes `meta.request_id` for end-to-end debugging without leaking stack traces.

### 1.1 Standard Success Envelope
```json
{
  "success": true,
  "data": {},
  "meta": {
    "request_id": "c1f7a8b9-8e42-4f33-9123-456789abcdef",
    "timestamp": "2026-08-26T18:35:00+06:00"
  }
}
```

### 1.2 Standard Error Envelope
```json
{
  "success": false,
  "error": {
    "code": "COMPLAINT_NOT_FOUND",
    "message": "অভিযোগটি খুঁজে পাওয়া যায়নি। (The requested complaint was not found.)",
    "details": {}
  },
  "meta": {
    "request_id": "c1f7a8b9-8e42-4f33-9123-456789abcdef",
    "timestamp": "2026-08-26T18:35:00+06:00"
  }
}
```

### 1.3 Standard Machine Error Codes
* `UNAUTHENTICATED` (401)
* `FORBIDDEN_SCOPE` (403)
* `RATE_LIMIT_EXCEEDED` (429)
* `VALIDATION_FAILED` (422)
* `INVALID_OTP` (400)
* `OTP_EXPIRED` (400)
* `COMPLAINT_NOT_FOUND` (404)
* `TASK_ALREADY_COMPLETED` (409)
* `EVIDENCE_REQUIRED` (422)
* `INVALID_FILE_TYPE` (415)
* `FILE_TOO_LARGE` (413)
* `INTERNAL_SERVER_ERROR` (500)

---

## 2. Authentication Endpoints

### `POST /api/v1/auth/otp/request`
* **Purpose:** Request a 6-digit OTP code for citizen login.
* **Rate Limit:** 3 requests / 10 minutes per phone/IP.
* **Request:**
  ```json
  {
    "phone": "01712345678"
  }
  ```
* **Response:**
  ```json
  {
    "success": true,
    "data": {
      "message": "OTP কোড পাঠানো হয়েছে।",
      "expires_in_seconds": 180
    }
  }
  ```

### `POST /api/v1/auth/otp/verify`
* **Purpose:** Verify OTP and return session token.
* **Request:**
  ```json
  {
    "phone": "01712345678",
    "otp": "482910",
    "device_name": "Pixel 7 Pro",
    "device_id": "hw-device-uuid-1234"
  }
  ```
* **Response:**
  ```json
  {
    "success": true,
    "data": {
      "token": "tok_9f82a1b7c3d4e5f6...",
      "user": {
        "id": 42,
        "phone": "01712345678",
        "role": "citizen"
      }
    }
  }
  ```

### `POST /api/v1/auth/login`
* **Purpose:** Password login for staff / administrators.
* **Request:**
  ```json
  {
    "username_or_email": "supervisor.ward19@amarmayor.gov.bd",
    "password": "SecurePassword123!",
    "mfa_code": "123456"
  }
  ```

### `POST /api/v1/auth/logout`
* **Purpose:** Revoke current bearer token / invalidate session.

---

## 3. Citizen Complaint Endpoints

### `GET /api/v1/categories`
* **Purpose:** Retrieve active complaint categories and subcategories for the submission form.

### `POST /api/v1/complaints`
* **Purpose:** Submit a new civic complaint.
* **Headers:** `X-Idempotency-Key: <uuid>`
* **Request:**
  ```json
  {
    "category_id": 1,
    "subcategory_id": 3,
    "ward_id": 19,
    "latitude": 24.757821,
    "longitude": 90.407234,
    "landmark": "কাঁচাবাজারের পেছনে",
    "description": "রাস্তার পাশে ময়লার বড় স্তূপ জমে আছে, দুর্গন্ধ ছড়াচ্ছে।",
    "is_sensitive": false
  }
  ```
* **Response (201 Created):**
  ```json
  {
    "success": true,
    "data": {
      "public_complaint_number": "MCC-260826-01842",
      "citizen_status": "received",
      "submitted_at": "2026-08-26T18:35:00+06:00",
      "expected_resolution_hours": 8
    }
  }
  ```

### `GET /api/v1/complaints/my`
* **Purpose:** List all complaints submitted by the authenticated citizen.
* **Parameters:** `page=1`, `per_page=15`, `status=active|resolved`

### `GET /api/v1/complaints/{public_complaint_number}`
* **Purpose:** Retrieve complete complaint details, timeline history, and photo evidence.

### `POST /api/v1/complaints/{public_complaint_number}/support`
* **Purpose:** Citizen clicks *"I am also affected"* (+1 supporter count).

### `POST /api/v1/complaints/{public_complaint_number}/confirm-resolution`
* **Purpose:** Citizen confirms work was satisfactorily resolved.
* **Request:**
  ```json
  {
    "rating_score": 5,
    "comment": "খুব দ্রুত পরিষ্কার করা হয়েছে, ধন্যবাদ।"
  }
  ```

### `POST /api/v1/complaints/{public_complaint_number}/needs-more-work`
* **Purpose:** Citizen reports problem is still unresolved (reopen).
* **Request:**
  ```json
  {
    "unresolved_reason_code": "problem_still_exists",
    "comment": "ড্রেন পরিষ্কার করা হয়নি, পানি এখনও জমে আছে।"
  }
  ```

---

## 4. Field Operations Endpoints (Mobile / Worker)

### `GET /api/v1/field/tasks`
* **Purpose:** List tasks assigned to the authenticated field worker / team.
* **Parameters:** `status=pending|in_progress|completed`, `date=today`

### `POST /api/v1/field/tasks/{task_id}/start`
* **Purpose:** Worker marks field task as started (`in_progress`).

### `POST /api/v1/field/tasks/{task_id}/evidence`
* **Purpose:** Upload before/after photographic evidence.
* **Content-Type:** `multipart/form-data`
* **Fields:** `image` (binary), `evidence_stage` (`before` | `after`), `latitude`, `longitude`, `is_live_capture` (bool).

### `POST /api/v1/field/tasks/{task_id}/complete`
* **Purpose:** Worker marks field work completed.

### `POST /api/v1/field/tasks/{task_id}/cannot-complete`
* **Purpose:** Worker reports obstacle / inability to complete task.
* **Request:**
  ```json
  {
    "failure_reason_code": "vehicle_needed",
    "failure_notes": "ভারী ময়লা তোলার জন্য পে-লোডার প্রয়োজন।"
  }
  ```

---

## 5. Supervisor Operations Endpoints

### `GET /api/v1/supervisor/complaints`
* **Purpose:** Scoped list of complaints under supervisor's Ward/Department.
* **Parameters:** `status`, `is_overdue=1`, `needs_attention=1`, `page=1`

### `POST /api/v1/supervisor/complaints/{id}/assign`
* **Purpose:** Assign field team / worker.
* **Request:**
  ```json
  {
    "team_id": 12,
    "worker_employee_id": 48,
    "instructions": "আজ বিকালের মধ্যে পরিষ্কার সম্পন্ন করুন।"
  }
  ```

### `POST /api/v1/supervisor/complaints/{id}/verify`
* **Purpose:** Supervisor reviews evidence and verifies resolution.

### `POST /api/v1/supervisor/complaints/{id}/return`
* **Purpose:** Supervisor rejects field work and returns task to worker.

### `POST /api/v1/supervisor/complaints/{id}/transfer`
* **Purpose:** Transfer complaint to another department/unit.

---

## 6. Executive Command Center Endpoints (Mayor / Administrator)

### `GET /api/v1/executive/kpis`
* **Purpose:** Retrieve citywide primary KPIs:
  * Total Complaints Today, Resolved %, Average Resolution Hours, Overdue Count, Citizen Reopen Count, Active Hazards.

### `GET /api/v1/executive/attention-required`
* **Purpose:** List critical executive queue items (1st deadline failures, 1st citizen reopens, recurring hotspots).

### `POST /api/v1/executive/directives`
* **Purpose:** Mayor / Administrator issues binding directive on a complaint or ward.
* **Request:**
  ```json
  {
    "complaint_id": 1842,
    "directive_type": "ask_for_action",
    "instruction": "২৪ ঘণ্টার মধ্যে ময়লা অপসারণ করে প্রতিবেদন দাখিল করুন।"
  }
  ```

### `POST /api/v1/executive/explanation-requests`
* **Purpose:** Executive demands formal written explanation from responsible officer.

---

## 7. Public Accountability Endpoints (No Authentication Required)

### `GET /api/v1/public/kpis`
* **Purpose:** Honest public citywide statistics (Received, Resolved, In Progress, Overdue, Citizen Satisfaction %).

### `GET /api/v1/public/wards`
* **Purpose:** List 33 General Wards with active representative information.

### `GET /api/v1/public/wards/{ward_number}`
* **Purpose:** Ward service profile, dual representatives (General + Reserved), resolution rate, recent approved public updates.

### `GET /api/v1/public/notices`
* **Purpose:** Active public announcements, emergency alerts, and civic notices.
