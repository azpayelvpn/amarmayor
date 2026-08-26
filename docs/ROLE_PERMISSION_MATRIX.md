# Role-Based Access Control (RBAC) & Scope Matrix

> **Document Status:** Authoritative Security & Authorization Specification  
> **Source of Truth:** [docs/MASTER_SPEC.md](file:///C:/laragon/www/amarmayor/docs/MASTER_SPEC.md) (Sections 313–377, 490–501)

---

## 1. Architectural Access Model

The platform enforces a three-tier authorization model:

$$\text{User} \longrightarrow \text{Role(s)} \longrightarrow \text{Permission(s)} \longrightarrow \text{Scope} \longrightarrow \text{Target Resource / Action}$$

1. **Role:** High-level functional persona assigned to a user (e.g., `supervisor`, `mayor`, `field_worker`).
2. **Permission:** Atomic capability code (e.g., `complaint.verify`, `executive.directive.issue`).
3. **Scope:** Contextual boundary restricting *where* or *on what data* the permission applies (e.g., `Ward 19 + Waste Management Department`).
4. **Mandatory Backend Enforcement:** Hiding UI buttons does not constitute security. Every API endpoint, HTMX partial, and form submission verifies permissions and active scope at the backend layer.
5. **Insecure Direct Object Reference (IDOR) Protection:** Changing IDs (`complaint_id`, `employee_id`, `ward_id`, `file_id`) without active scope clearance results in strict `403 Forbidden` response.

---

## 2. Standard Defined Roles

| Role Code (`slug`) | Role Title (Bangla) | Role Title (English) | Primary Purpose & Responsibility Scope |
|---|---|---|---|
| `public_viewer` | সাধারণ দর্শনার্থী | Public Viewer | Unauthenticated public visitor; views public dashboard, notices, profiles. |
| `citizen` | নাগরিক | Citizen | Authenticated citizen (Phone+OTP); submits, tracks, confirms complaints. |
| `mayor` | মেয়র | Mayor | Elected city executive; citywide oversight, top KPIs, executive directives. |
| `administrator` | প্রশাসক | Administrator | Appointed city executive; citywide oversight, executive directives. |
| `ceo` | প্রধান নির্বাহী কর্মকর্তা | Chief Executive Officer | Senior executive; operational management, department/zone coordination. |
| `general_councillor` | সাধারণ ওয়ার্ড কাউন্সিলর | General Ward Councillor | Elected representative; monitors complaints, public advocacy for assigned Ward. |
| `reserved_women_councillor` | সংরক্ষিত নারী কাউন্সিলর | Reserved Women Councillor | Elected representative; monitors complaints across 3 assigned Wards. |
| `responsible_officer` | দায়িত্বপ্রাপ্ত কর্মকর্তা | Responsible Officer | Appointed ward representative; monitors complaints for 1 or more assigned Wards. |
| `department_head` | বিভাগীয় প্রধান | Department Head | Operational lead for an entire department (e.g., Waste, Engineering). |
| `department_officer` | বিভাগীয় কর্মকর্তা | Department Officer | Operational assistant / officer within a department scope. |
| `zone_officer` | আঞ্চলিক কর্মকর্তা | Zone Officer | Operational coordination across all Wards in an administrative Zone (1, 2, or 3). |
| `ward_officer` | ওয়ার্ড কর্মকর্তা / পরিদর্শক | Ward Officer / Inspector | On-the-ground operational monitoring for a specific Ward. |
| `supervisor` | সুপারভাইজার | Supervisor | Responsible operational owner; assigns teams, inspects & verifies field work. |
| `team_leader` | দলনেতা | Team Leader | Leads a field crew; updates task execution progress. |
| `field_worker` | মাঠকর্মী / পরিচ্ছন্নতাকর্মী | Field Worker / Cleaner | Executes field tasks, uploads photo evidence, marks completion. |
| `call_center_operator` | কল সেন্টার অপারেটর | Call Center Operator | Submits complaints on behalf of citizens via phone helpline. |
| `control_room_officer` | নিয়ন্ত্রণ কক্ষ / যাচাই কর্মকর্তা | Control Room / Triage Officer | Reviews incoming unclassified complaints, handles emergency alerts. |
| `public_info_officer` | জনসংযোগ কর্মকর্তা | Public Information Officer | Publishes city notices, news, emergency announcements. |
| `data_monitoring_officer`| তথ্য ও পর্যবেক্ষণ কর্মকর্তা | Data & Monitoring Officer | Citywide statistical analysis, trend monitoring, report generation. |
| `auditor` | নিরীক্ষক | Auditor / Oversight | Read-only compliance auditor across all civic, operational, and audit logs. |
| `platform_super_admin` | প্ল্যাটফর্ম অ্যাডমিন | Platform Super Admin | Non-technical business admin; manages employees, areas, routing, deadlines. |
| `technical_super_admin`| কারিগরি অ্যাডমিন | Technical Super Admin | Technical admin; monitors health, logs, backups, background queues, security. |

---

## 3. Granular Permission Catalog

```text
complaint.create                      # Create new civic complaint
complaint.view                        # View complaint basic details
complaint.view_private                # View private citizen details & raw evidence
complaint.add_information             # Add citizen follow-up info / messages
complaint.assign                      # Assign complaint to team / worker
complaint.start                       # Mark field work in progress
complaint.complete_work               # Mark field work complete + submit evidence
complaint.verify                      # Verify field resolution as Supervisor
complaint.confirm_resolution          # Confirm resolution as citizen
complaint.needs_more_work             # Flag resolution incomplete (reopen)
complaint.transfer                    # Transfer ownership between departments/units
complaint.request_support             # Request additional equipment/manpower
complaint.change_priority             # Adjust priority rank (P1-P4)
complaint.cancel                      # Cancel invalid/duplicate complaint
complaint.view_history                # View timeline & status history

task.view                             # View assigned field tasks
task.assign                           # Assign field worker to task
task.start                            # Start field task
task.complete                         # Mark field task completed
task.return                           # Return task (cannot complete)
task.add_evidence                     # Upload before/after photos

employee.view                         # View workforce directory
employee.create                       # Add new employee profile
employee.update                       # Update employee profile & skills
employee.change_posting               # Transfer / update posting
employee.change_responsibility        # Update geographic/functional scope
employee.manage_access                # Enable/disable login user account

governance.view                       # View representative directory
governance.assign                     # Assign Mayor/Councillor/Responsible Officer
governance.end_assignment             # End governance tenure
governance.view_history               # View historical governance tenures

ward.view                             # View ward profile
ward.manage                           # Edit ward details & area boundaries
zone.view                             # View zone profile
zone.manage                           # Edit zone configuration

department.view                       # View department directory
department.manage                     # Edit department & service units
service.view                          # View civic services
service.manage                        # Configure service taxonomy

routing.view                          # View routing rules
routing.manage                        # Update automatic routing rules

deadline.view                         # View service deadline rules
deadline.manage                       # Configure expected service hours

notice.view                           # View public notices
notice.publish                        # Create & publish public notice
notice.manage                         # Unpublish / edit notice

report.view                           # View statistical analytics
report.export                         # Export CSV / PDF reports

dashboard.public                      # Public accountability dashboard
dashboard.ward                        # Ward-level dashboard
dashboard.zone                        # Zone-level dashboard
dashboard.department                  # Department-level dashboard
dashboard.citywide                    # Citywide executive dashboard

executive.attention.view              # View 'Attention Required' executive queue
executive.directive.issue             # Issue binding executive directive
executive.explanation.request         # Demand written explanation from officer
executive.support.provide             # Approve executive resource intervention

communication.send                    # Send complaint-linked message
communication.view                    # View message history
communication.moderate                # Moderate abusive communications

audit.view                            # View append-only system audit logs

user.manage                           # Manage user accounts & roles
role.manage                           # Manage role assignments
permission.manage                     # Manage permissions

system.health.view                    # View technical system health
system.integration.manage             # Manage SMS/Push/Map configurations
system.backup.manage                  # Trigger/inspect database backups
system.security.manage                # Manage security settings & rate limits
```

---

## 4. Master Role $\times$ Permission $\times$ Scope Matrix

| Role | Allowed Scopes | Core Permissions Granted |
|---|---|---|
| **Public Viewer** | `public` | `dashboard.public`, `ward.view`, `department.view`, `notice.view`, `governance.view` |
| **Citizen** | `self` | `complaint.create`, `complaint.view` (own), `complaint.add_information` (own), `complaint.confirm_resolution` (own), `complaint.needs_more_work` (own), `communication.send` (own), `dashboard.public`, `notice.view` |
| **Field Worker** | `assigned` | `task.view`, `task.start`, `task.complete`, `task.return`, `task.add_evidence` |
| **Supervisor** | `ward` + `service_unit` | `complaint.view`, `complaint.view_private`, `complaint.assign`, `complaint.verify`, `complaint.request_support`, `task.view`, `task.assign`, `task.return`, `communication.view`, `communication.send`, `dashboard.ward` |
| **Ward Officer / Inspector** | `ward` | `complaint.view`, `complaint.view_private`, `complaint.request_support`, `dashboard.ward`, `employee.view`, `communication.view`, `communication.send` |
| **Zone Officer** | `zone` | `complaint.view`, `complaint.view_private`, `complaint.transfer`, `dashboard.zone`, `employee.view`, `report.view`, `communication.view` |
| **Department Head** | `department` | `complaint.view`, `complaint.view_private`, `complaint.transfer`, `complaint.change_priority`, `dashboard.department`, `employee.view`, `report.view`, `report.export`, `communication.view`, `communication.send` |
| **General Councillor** | `ward` | `complaint.view`, `complaint.view_private`, `complaint.request_support`, `dashboard.ward`, `communication.view`, `communication.send`, `notice.view` *(Cannot mark tasks complete)* |
| **Reserved Women Councillor**| `multiple_wards` (Assigned 3 Wards) | `complaint.view`, `complaint.view_private`, `complaint.request_support`, `dashboard.ward` (across covered Wards), `communication.view`, `communication.send` *(Cannot mark tasks complete)* |
| **Responsible Officer** | `ward` or `multiple_wards` | `complaint.view`, `complaint.view_private`, `complaint.request_support`, `dashboard.ward` (assigned Wards), `communication.view`, `communication.send` *(Cannot mark tasks complete)* |
| **Chief Executive Officer (CEO)**| `citywide` | `complaint.view`, `complaint.view_private`, `complaint.transfer`, `complaint.change_priority`, `dashboard.citywide`, `dashboard.department`, `dashboard.zone`, `dashboard.ward`, `employee.view`, `report.view`, `report.export`, `executive.attention.view`, `executive.directive.issue`, `executive.support.provide` |
| **Mayor / Administrator** | `citywide` | `dashboard.citywide`, `executive.attention.view`, `executive.directive.issue`, `executive.explanation.request`, `executive.support.provide`, `complaint.view`, `complaint.view_private`, `complaint.change_priority`, `report.view`, `report.export`, `communication.view` *(Oversight, not field task completion)* |
| **Call Center Operator** | `citywide` | `complaint.create` (on behalf of citizen), `complaint.view`, `complaint.add_information` |
| **Control Room Officer** | `citywide` | `complaint.view`, `complaint.view_private`, `complaint.transfer`, `complaint.change_priority`, `complaint.cancel`, `notice.view` |
| **Public Information Officer**| `citywide` | `notice.view`, `notice.publish`, `notice.manage`, `communication.moderate`, `dashboard.public` |
| **Data & Monitoring Officer** | `citywide` | `dashboard.citywide`, `dashboard.department`, `dashboard.zone`, `dashboard.ward`, `report.view`, `report.export`, `complaint.view` |
| **Auditor / Oversight** | `systemwide` (Read-Only) | `audit.view`, `complaint.view`, `complaint.view_private`, `complaint.view_history`, `employee.view`, `governance.view`, `governance.view_history`, `report.view`, `report.export` |
| **Platform Super Admin** | `systemwide` | `employee.*`, `governance.*`, `ward.*`, `zone.*`, `department.*`, `service.*`, `routing.*`, `deadline.*`, `notice.*`, `user.manage`, `role.manage`, `report.*`, `audit.view` *(Business configuration, non-technical interface)* |
| **Technical Super Admin** | `systemwide` | `system.health.view`, `system.integration.manage`, `system.backup.manage`, `system.security.manage`, `audit.view`, `user.manage` *(Infrastructure, health, queues, backups)* |

---

## 5. Scope Verification Logic

When an authorized user performs an action on an entity:

```php
function authorize(User $user, string $permission, ?Entity $resource = null): bool
{
    // 1. Verify user holds role with given permission
    if (!$user->hasPermission($permission)) {
        return false;
    }

    // 2. If action is global / systemwide (e.g. system.health.view)
    if ($resource === null) {
        return true;
    }

    // 3. Verify resource matches user's active scopes
    return $user->matchesScope($resource);
}
```

### Scope Match Examples:
* **Supervisor** assigned to `Ward 19` and `Waste Management`:
  * Can verify Complaint #101 (`Ward 19`, `Waste Management`) $\rightarrow$ **Allowed**.
  * Cannot verify Complaint #102 (`Ward 20`, `Waste Management`) $\rightarrow$ **Forbidden (403)**.
  * Cannot verify Complaint #103 (`Ward 19`, `Street Lights`) $\rightarrow$ **Forbidden (403)**.
* **Reserved Women Councillor** covering `Wards 01, 02, 03`:
  * Can view complaints and dashboards for Ward 01, Ward 02, and Ward 03 $\rightarrow$ **Allowed**.
  * Cannot access Ward 04 private complaint data $\rightarrow$ **Forbidden (403)**.
* **Responsible Officer** covering `Wards 05, 06`:
  * Can follow up complaints in Ward 05 and Ward 06 $\rightarrow$ **Allowed**.
  * Cannot access Ward 07 $\rightarrow$ **Forbidden (403)**.
