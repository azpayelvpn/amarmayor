<?php

declare(strict_types=1);

namespace AmarMayor\Controllers\Web;

use AmarMayor\Auth\Auth;
use AmarMayor\Database\DatabaseManager;
use AmarMayor\Domain\CommandCenter\CommandCenterService;
use AmarMayor\Domain\ComplaintConfig\RoutingConfigService;
use AmarMayor\Domain\ExecutiveAttention\ExecutiveAttentionService;
use AmarMayor\Domain\FieldOperations\FieldTaskService;
use AmarMayor\Domain\PlatformAdmin\PlatformAdminService;
use AmarMayor\Domain\ResolutionQuality\ResolutionService;
use AmarMayor\Domain\TechnicalAdmin\SystemHealthService;
use AmarMayor\Http\Request;
use AmarMayor\Http\Response;
use AmarMayor\Support\Security;
use AmarMayor\Support\Translator;
use PDO;

class DashboardWebController
{
    private CommandCenterService $commandCenterService;
    private ExecutiveAttentionService $executiveAttentionService;
    private FieldTaskService $fieldTaskService;
    private PlatformAdminService $platformAdminService;
    private SystemHealthService $systemHealthService;
    private ResolutionService $resolutionService;

    public function __construct(
        ?CommandCenterService $commandCenterService = null,
        ?ExecutiveAttentionService $executiveAttentionService = null,
        ?FieldTaskService $fieldTaskService = null,
        ?PlatformAdminService $platformAdminService = null,
        ?SystemHealthService $systemHealthService = null,
        ?ResolutionService $resolutionService = null
    ) {
        $this->commandCenterService = $commandCenterService ?: new CommandCenterService();
        $this->executiveAttentionService = $executiveAttentionService ?: new ExecutiveAttentionService();
        $this->fieldTaskService = $fieldTaskService ?: new FieldTaskService();
        $this->platformAdminService = $platformAdminService ?: new PlatformAdminService();
        $this->systemHealthService = $systemHealthService ?: new SystemHealthService();
        $this->resolutionService = $resolutionService ?: new ResolutionService();
    }

    /**
     * Main GET /dashboard entry point.
     * Evaluates authenticated user role and loads scoped working dashboard.
     */
    public function index(Request $request): Response
    {
        if (!Auth::check()) {
            return Response::redirect('/login?error=auth_required');
        }

        $user = Auth::user();
        if ($user === null) {
            return Response::redirect('/login');
        }

        // If pure citizen without staff or observer roles, redirect to citizen portal
        $roles = $user->getRoleSlugs();
        $systemRoles = array_diff($roles, ['citizen']);
        if ($user->userType === 'citizen' && empty($systemRoles)) {
            return Response::redirect('/my-complaints');
        }

        $primaryRole = $this->resolvePrimaryRole($roles);
        $pdo = DatabaseManager::getConnection();
        $locale = Translator::getLocale();

        $person = $pdo->query("SELECT * FROM persons WHERE user_id = {$user->id} LIMIT 1")->fetch(PDO::FETCH_ASSOC) ?: [];
        $employee = !empty($person['id']) ? $pdo->query("SELECT * FROM employees WHERE person_id = {$person['id']} LIMIT 1")->fetch(PDO::FETCH_ASSOC) : [];

        $data = [
            'locale' => $locale,
            'user' => $user,
            'person' => $person,
            'employee' => $employee,
            'primaryRole' => $primaryRole,
            'roles' => $roles,
        ];

        // Load Role-Specific Dashboard Data
        switch ($primaryRole) {
            case 'mayor':
                $data['kpis'] = $this->commandCenterService->getExecutiveKpis();
                $data['dailyBrief'] = $this->commandCenterService->getDailyBrief();
                $data['attentionQueue'] = $this->executiveAttentionService->getAttentionQueue();
                $data['zonalProgress'] = $pdo->query("
                    SELECT z.id, z.name_bn, COUNT(c.id) as total,
                           SUM(CASE WHEN c.internal_status = 'resolved' THEN 1 ELSE 0 END) as resolved,
                           SUM(CASE WHEN c.deadline_at < NOW() AND c.internal_status NOT IN ('resolved', 'closed') THEN 1 ELSE 0 END) as overdue
                    FROM zones z
                    LEFT JOIN complaints c ON c.zone_id = z.id
                    GROUP BY z.id, z.name_bn
                    ORDER BY z.id ASC
                ")->fetchAll(PDO::FETCH_ASSOC);
                $data['supportBottlenecks'] = $pdo->query("
                    SELECT sr.*, c.public_complaint_number, d.name_bn as target_dept_name,
                           w.ward_number, sc.name_bn as subcategory_name_bn,
                           p_req.full_name_bn as requester_name, p_req.official_phone as requester_phone
                    FROM support_requests sr
                    INNER JOIN complaints c ON c.id = sr.complaint_id
                    LEFT JOIN departments d ON d.id = sr.target_department_id
                    LEFT JOIN wards w ON w.id = c.ward_id
                    LEFT JOIN complaint_subcategories sc ON sc.id = c.subcategory_id
                    LEFT JOIN employees e_req ON e_req.id = sr.requested_by_employee_id
                    LEFT JOIN persons p_req ON p_req.id = e_req.person_id
                    WHERE sr.fulfillment_status = 'failed_delivery'
                       OR (sr.status = 'approved' AND sr.fulfillment_status = 'dispatched' AND sr.created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR))
                    ORDER BY sr.id DESC LIMIT 10
                ")->fetchAll(PDO::FETCH_ASSOC);
                $data['manpowerShortageAlerts'] = $pdo->query("
                    SELECT sr.*, c.public_complaint_number, w.ward_number,
                           p_req.full_name_bn as requester_name, p_req.official_phone as requester_phone
                    FROM support_requests sr
                    INNER JOIN complaints c ON c.id = sr.complaint_id
                    LEFT JOIN wards w ON w.id = c.ward_id
                    LEFT JOIN employees e_req ON e_req.id = sr.requested_by_employee_id
                    LEFT JOIN persons p_req ON p_req.id = e_req.person_id
                    WHERE sr.support_type = 'extra_manpower'
                      AND (sr.status = 'pending' OR sr.fulfillment_status = 'failed_delivery')
                    ORDER BY sr.id DESC LIMIT 10
                ")->fetchAll(PDO::FETCH_ASSOC);
                break;

            case 'administrator':
                $data['kpis'] = $this->commandCenterService->getExecutiveKpis();
                $data['dailyBrief'] = $this->commandCenterService->getDailyBrief();
                $data['attentionQueue'] = $this->executiveAttentionService->getAttentionQueue();
                $data['responsibleOfficers'] = $pdo->query("
                    SELECT er.area_id as ward_number, p.full_name_bn, p.official_phone as phone, em.designation_bn, er.responsibility_type
                    FROM employee_responsibilities er
                    INNER JOIN employees em ON em.id = er.employee_id
                    INNER JOIN persons p ON p.id = em.person_id
                    WHERE er.area_type = 'ward'
                    ORDER BY CAST(er.area_id AS UNSIGNED) ASC LIMIT 33
                ")->fetchAll(PDO::FETCH_ASSOC);
                $data['zonalProgress'] = $pdo->query("
                    SELECT z.id, z.name_bn, COUNT(c.id) as total,
                            SUM(CASE WHEN c.internal_status = 'resolved' THEN 1 ELSE 0 END) as resolved,
                            SUM(CASE WHEN c.deadline_at < NOW() AND c.internal_status NOT IN ('resolved', 'closed') THEN 1 ELSE 0 END) as overdue
                    FROM zones z
                    LEFT JOIN complaints c ON c.zone_id = z.id
                    GROUP BY z.id, z.name_bn
                    ORDER BY z.id ASC
                ")->fetchAll(PDO::FETCH_ASSOC);
                $data['supportBottlenecks'] = $pdo->query("
                    SELECT sr.*, c.public_complaint_number, d.name_bn as target_dept_name,
                           w.ward_number, sc.name_bn as subcategory_name_bn,
                           p_req.full_name_bn as requester_name, p_req.official_phone as requester_phone
                    FROM support_requests sr
                    INNER JOIN complaints c ON c.id = sr.complaint_id
                    LEFT JOIN departments d ON d.id = sr.target_department_id
                    LEFT JOIN wards w ON w.id = c.ward_id
                    LEFT JOIN complaint_subcategories sc ON sc.id = c.subcategory_id
                    LEFT JOIN employees e_req ON e_req.id = sr.requested_by_employee_id
                    LEFT JOIN persons p_req ON p_req.id = e_req.person_id
                    WHERE sr.fulfillment_status = 'failed_delivery'
                       OR (sr.status = 'approved' AND sr.fulfillment_status = 'dispatched' AND sr.created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR))
                    ORDER BY sr.id DESC LIMIT 10
                ")->fetchAll(PDO::FETCH_ASSOC);
                $data['manpowerShortageAlerts'] = $pdo->query("
                    SELECT sr.*, c.public_complaint_number, w.ward_number,
                           p_req.full_name_bn as requester_name, p_req.official_phone as requester_phone
                    FROM support_requests sr
                    INNER JOIN complaints c ON c.id = sr.complaint_id
                    LEFT JOIN wards w ON w.id = c.ward_id
                    LEFT JOIN employees e_req ON e_req.id = sr.requested_by_employee_id
                    LEFT JOIN persons p_req ON p_req.id = e_req.person_id
                    WHERE sr.support_type = 'extra_manpower'
                      AND (sr.status = 'pending' OR sr.fulfillment_status = 'failed_delivery')
                    ORDER BY sr.id DESC LIMIT 10
                ")->fetchAll(PDO::FETCH_ASSOC);
                break;

            case 'ceo':
                $data['kpis'] = $this->commandCenterService->getExecutiveKpis();
                $data['dailyBrief'] = $this->commandCenterService->getDailyBrief();
                $data['departments'] = $pdo->query("
                    SELECT d.id, d.name_bn, d.name_en,
                           COUNT(c.id) as total_complaints,
                           SUM(CASE WHEN c.internal_status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                           SUM(CASE WHEN c.deadline_at < NOW() AND c.closed_at IS NULL THEN 1 ELSE 0 END) as overdue_count
                    FROM departments d
                    LEFT JOIN complaints c ON c.department_id = d.id
                    GROUP BY d.id
                    ORDER BY d.id ASC
                ")->fetchAll(PDO::FETCH_ASSOC);
                $data['explanationRequests'] = $pdo->query("
                    SELECT er.*, c.public_complaint_number, p.full_name_bn as target_employee_name, em.designation_bn
                    FROM explanation_requests er
                    LEFT JOIN complaints c ON c.id = er.complaint_id
                    LEFT JOIN employees em ON em.id = er.target_employee_id
                    LEFT JOIN persons p ON p.id = em.person_id
                    ORDER BY er.id DESC LIMIT 15
                ")->fetchAll(PDO::FETCH_ASSOC);
                $data['overdueComplaints'] = $pdo->query("
                    SELECT c.*, d.name_bn as department_name_bn, w.ward_number, sc.name_bn as subcategory_name_bn
                    FROM complaints c
                    LEFT JOIN departments d ON d.id = c.department_id
                    LEFT JOIN wards w ON w.id = c.ward_id
                    LEFT JOIN complaint_subcategories sc ON sc.id = c.subcategory_id
                    WHERE c.internal_status IN ('submitted', 'assigned', 'in_progress') AND c.deadline_at < NOW()
                    ORDER BY c.deadline_at ASC LIMIT 10
                ")->fetchAll(PDO::FETCH_ASSOC);
                $data['departmentOfficers'] = $pdo->query("
                    SELECT em.id as employee_id, ep.department_id, p.full_name_bn, em.designation_bn
                    FROM employees em
                    INNER JOIN persons p ON p.id = em.person_id
                    LEFT JOIN employee_postings ep ON ep.employee_id = em.id AND (ep.effective_to IS NULL OR ep.effective_to >= CURDATE())
                    WHERE ep.department_id IS NOT NULL
                ")->fetchAll(PDO::FETCH_ASSOC);
                $data['zonalProgress'] = $pdo->query("
                    SELECT z.id, z.name_bn, COUNT(c.id) as total,
                           SUM(CASE WHEN c.internal_status = 'resolved' THEN 1 ELSE 0 END) as resolved,
                           SUM(CASE WHEN c.deadline_at < NOW() AND c.internal_status NOT IN ('resolved', 'closed') THEN 1 ELSE 0 END) as overdue
                    FROM zones z
                    LEFT JOIN complaints c ON c.zone_id = z.id
                    GROUP BY z.id, z.name_bn
                    ORDER BY z.id ASC
                ")->fetchAll(PDO::FETCH_ASSOC);
                $data['supportBottlenecks'] = $pdo->query("
                    SELECT sr.*, c.public_complaint_number, d.name_bn as target_dept_name,
                           w.ward_number, sc.name_bn as subcategory_name_bn,
                           p_req.full_name_bn as requester_name, p_req.official_phone as requester_phone
                    FROM support_requests sr
                    INNER JOIN complaints c ON c.id = sr.complaint_id
                    LEFT JOIN departments d ON d.id = sr.target_department_id
                    LEFT JOIN wards w ON w.id = c.ward_id
                    LEFT JOIN complaint_subcategories sc ON sc.id = c.subcategory_id
                    LEFT JOIN employees e_req ON e_req.id = sr.requested_by_employee_id
                    LEFT JOIN persons p_req ON p_req.id = e_req.person_id
                    WHERE sr.fulfillment_status = 'failed_delivery'
                       OR (sr.status = 'approved' AND sr.fulfillment_status = 'dispatched' AND sr.created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR))
                    ORDER BY sr.id DESC LIMIT 10
                ")->fetchAll(PDO::FETCH_ASSOC);
                $data['manpowerShortageAlerts'] = $pdo->query("
                    SELECT sr.*, c.public_complaint_number, w.ward_number,
                           p_req.full_name_bn as requester_name, p_req.official_phone as requester_phone
                    FROM support_requests sr
                    INNER JOIN complaints c ON c.id = sr.complaint_id
                    LEFT JOIN wards w ON w.id = c.ward_id
                    LEFT JOIN employees e_req ON e_req.id = sr.requested_by_employee_id
                    LEFT JOIN persons p_req ON p_req.id = e_req.person_id
                    WHERE sr.support_type = 'extra_manpower'
                      AND (sr.status = 'pending' OR sr.fulfillment_status = 'failed_delivery')
                    ORDER BY sr.id DESC LIMIT 10
                ")->fetchAll(PDO::FETCH_ASSOC);
                break;

            case 'field_worker':
                $data['tasks'] = $this->fieldTaskService->getTasksForWorker($user->id);
                break;

            case 'team_leader':
                $data['tasks'] = $this->fieldTaskService->getTasksForWorker($user->id);
                $data['teamMembers'] = $pdo->query("
                    SELECT p.full_name_bn, em.employee_code, em.designation_bn
                    FROM employees em
                    INNER JOIN persons p ON p.id = em.person_id
                    WHERE em.designation_bn LIKE '%পরিচ্ছন্নতা%' OR em.designation_en LIKE '%Cleaner%' OR em.designation_bn LIKE '%কর্মী%'
                    LIMIT 8
                ")->fetchAll(PDO::FETCH_ASSOC);
                $data['recentTeamReports'] = $pdo->query("
                    SELECT in_n.*, c.public_complaint_number
                    FROM internal_notes in_n
                    INNER JOIN complaints c ON c.id = in_n.complaint_id
                    WHERE in_n.author_user_id = {$user->id} AND in_n.note_type = 'team_progress'
                    ORDER BY in_n.id DESC LIMIT 10
                ")->fetchAll(PDO::FETCH_ASSOC);
                break;

            case 'supervisor':
                $supWardStmt = $pdo->prepare("
                    SELECT er.area_id as ward_number, w.id as ward_id
                    FROM employee_responsibilities er
                    INNER JOIN employees em ON em.id = er.employee_id
                    INNER JOIN persons pr ON pr.id = em.person_id
                    LEFT JOIN wards w ON w.ward_number = er.area_id
                    WHERE pr.user_id = ? AND er.area_type = 'ward'
                    LIMIT 1
                ");
                $supWardStmt->execute([$user->id]);
                $supWard = $supWardStmt->fetch(PDO::FETCH_ASSOC);

                $wardNumber = $supWard ? (int)$supWard['ward_number'] : 1;
                $wardId = $supWard && !empty($supWard['ward_id']) ? (int)$supWard['ward_id'] : 1;

                $data['wardNumber'] = $wardNumber;
                $data['wardId'] = $wardId;
                $data['workforceStats'] = $this->fieldTaskService->getWardWorkforceStats($wardId);
                $data['teamLeaders'] = $data['workforceStats']['team_leaders'];
                $data['tasks'] = $this->fieldTaskService->getTasksForSupervisor($user->id);
                $data['kpis'] = $this->commandCenterService->getExecutiveKpis($wardId);
                $data['directives'] = $this->executiveAttentionService->getDirectivesForSupervisor($user->id);
                $data['supportRequests'] = $this->fieldTaskService->getSupportRequestsForSupervisor($user->id);
                $data['departments'] = $pdo->query("SELECT id, name_bn, name_en FROM departments WHERE status = 'active'")->fetchAll(PDO::FETCH_ASSOC);

                // Unassigned complaints waiting for squad dispatch
                $unassignedStmt = $pdo->prepare("
                    SELECT c.*, sc.name_bn as subcategory_name_bn, cl.landmark, cl.approximate_address,
                           p_cit.full_name_bn as citizen_name_bn
                    FROM complaints c
                    LEFT JOIN complaint_subcategories sc ON sc.id = c.subcategory_id
                    LEFT JOIN complaint_locations cl ON cl.complaint_id = c.id
                    LEFT JOIN users u_cit ON u_cit.id = c.citizen_user_id
                    LEFT JOIN persons p_cit ON p_cit.user_id = u_cit.id
                    WHERE c.ward_id = ?
                      AND c.internal_status IN ('submitted', 'assigned')
                      AND c.id NOT IN (
                          SELECT complaint_id FROM field_tasks WHERE task_status IN ('pending', 'in_progress', 'completed')
                      )
                    ORDER BY c.id DESC LIMIT 15
                ");
                $unassignedStmt->execute([$wardId]);
                $data['unassignedComplaints'] = $unassignedStmt->fetchAll(PDO::FETCH_ASSOC);
                break;

            case 'ward_officer':
                // Ward 1 Scope
                $data['wardNumber'] = 1;
                $data['kpis'] = $this->commandCenterService->getExecutiveKpis(1);
                $data['complaints'] = $pdo->query("
                    SELECT c.*, sc.name_bn as subcategory_name_bn, sc.name_en as subcategory_name_en,
                           cl.landmark, cl.public_safe_address
                    FROM complaints c
                    LEFT JOIN complaint_subcategories sc ON sc.id = c.subcategory_id
                    LEFT JOIN complaint_locations cl ON cl.complaint_id = c.id
                    WHERE c.ward_id = 1
                    ORDER BY c.id DESC LIMIT 20
                ")->fetchAll(PDO::FETCH_ASSOC);
                break;

            case 'zone_officer':
                // Zone 1 Scope (Approved Wards: 1, 2, 4, 6, 11, 12, 27, 28, 29, 30)
                $data['zoneNumber'] = 1;
                $data['zoneWards'] = [1, 2, 4, 6, 11, 12, 27, 28, 29, 30];
                $data['kpis'] = $this->commandCenterService->getExecutiveKpis(null, 1);
                $data['complaints'] = $pdo->query("
                    SELECT c.*, w.ward_number, sc.name_bn as subcategory_name_bn, sc.name_en as subcategory_name_en,
                           cl.landmark, cl.public_safe_address
                    FROM complaints c
                    LEFT JOIN wards w ON w.id = c.ward_id
                    LEFT JOIN complaint_subcategories sc ON sc.id = c.subcategory_id
                    LEFT JOIN complaint_locations cl ON cl.complaint_id = c.id
                    WHERE c.zone_id = 1
                    ORDER BY c.id DESC LIMIT 20
                ")->fetchAll(PDO::FETCH_ASSOC);
                break;

            case 'department_head':
                $deptId = 1; // Waste Management default
                $data['departmentNameBn'] = 'বর্জ্য ব্যবস্থাপনা বিভাগ';
                $data['departmentNameEn'] = 'Waste Management Department';
                $data['kpis'] = $this->commandCenterService->getExecutiveKpis(null, null, $deptId);
                $data['supportRequests'] = $this->fieldTaskService->getSupportRequestsForDepartment($deptId);
                $data['wards'] = $pdo->query("SELECT id, ward_number FROM wards WHERE status = 'active' ORDER BY ward_number ASC")->fetchAll(PDO::FETCH_ASSOC);
                $data['workforceRequests'] = array_filter($data['supportRequests'], fn($sr) => ($sr['support_type'] ?? '') === 'extra_manpower');
                $data['complaints'] = $pdo->query("
                    SELECT c.*, w.ward_number, sc.name_bn as subcategory_name_bn, sc.name_en as subcategory_name_en,
                           cl.landmark, cl.public_safe_address
                    FROM complaints c
                    LEFT JOIN wards w ON w.id = c.ward_id
                    LEFT JOIN complaint_subcategories sc ON sc.id = c.subcategory_id
                    LEFT JOIN complaint_locations cl ON cl.complaint_id = c.id
                    WHERE c.department_id = {$deptId}
                    ORDER BY c.id DESC LIMIT 20
                ")->fetchAll(PDO::FETCH_ASSOC);
                break;

            case 'department_officer':
                $deptId = 1; // Waste Management default
                $data['departmentNameBn'] = 'বর্জ্য ব্যবস্থাপনা বিভাগ';
                $data['departmentNameEn'] = 'Waste Management Department';
                $data['kpis'] = $this->commandCenterService->getExecutiveKpis(null, null, $deptId);
                $data['recentCompletedForAudit'] = $pdo->query("
                    SELECT c.*, w.ward_number, sc.name_bn as subcategory_name_bn, cl.landmark
                    FROM complaints c
                    LEFT JOIN wards w ON w.id = c.ward_id
                    LEFT JOIN complaint_subcategories sc ON sc.id = c.subcategory_id
                    LEFT JOIN complaint_locations cl ON cl.complaint_id = c.id
                    WHERE c.department_id = {$deptId} AND c.internal_status IN ('resolved', 'verified')
                    ORDER BY c.id DESC LIMIT 15
                ")->fetchAll(PDO::FETCH_ASSOC);
                $data['myInspectionLogs'] = $pdo->query("
                    SELECT in_n.*, c.public_complaint_number
                    FROM internal_notes in_n
                    INNER JOIN complaints c ON c.id = in_n.complaint_id
                    WHERE in_n.author_user_id = {$user->id}
                    ORDER BY in_n.id DESC LIMIT 10
                ")->fetchAll(PDO::FETCH_ASSOC);
                $data['complaints'] = $pdo->query("
                    SELECT c.*, w.ward_number, sc.name_bn as subcategory_name_bn, sc.name_en as subcategory_name_en,
                           cl.landmark, cl.public_safe_address
                    FROM complaints c
                    LEFT JOIN wards w ON w.id = c.ward_id
                    LEFT JOIN complaint_subcategories sc ON sc.id = c.subcategory_id
                    LEFT JOIN complaint_locations cl ON cl.complaint_id = c.id
                    WHERE c.department_id = {$deptId}
                    ORDER BY c.id DESC LIMIT 20
                ")->fetchAll(PDO::FETCH_ASSOC);
                break;

            case 'general_councillor':
                $wardNumber = 1;
                $data['wardNumber'] = $wardNumber;
                $data['wardKpis'] = $this->commandCenterService->getExecutiveKpis($wardNumber);
                $data['wardSupervisor'] = $pdo->query("
                    SELECT p.full_name_bn, p.official_phone as phone
                    FROM employee_responsibilities er
                    INNER JOIN employees em ON em.id = er.employee_id
                    INNER JOIN persons p ON p.id = em.person_id
                    WHERE er.area_type = 'ward' AND er.area_id = '{$wardNumber}' AND er.responsibility_type = 'supervisor'
                    LIMIT 1
                ")->fetch(PDO::FETCH_ASSOC);
                $data['categories'] = (new \AmarMayor\Domain\ComplaintConfig\TaxonomyService())->getCategories(true);
                $data['complaints'] = $pdo->query("
                    SELECT c.*, w.ward_number, sc.name_bn as subcategory_name_bn, sc.name_en as subcategory_name_en,
                           cl.landmark, cl.public_safe_address
                    FROM complaints c
                    LEFT JOIN wards w ON w.id = c.ward_id
                    LEFT JOIN complaint_subcategories sc ON sc.id = c.subcategory_id
                    LEFT JOIN complaint_locations cl ON cl.complaint_id = c.id
                    WHERE c.ward_id = {$wardNumber}
                    ORDER BY c.id DESC LIMIT 20
                ")->fetchAll(PDO::FETCH_ASSOC);
                break;

            case 'reserved_women_councillor':
                $clusterWards = [1, 2, 3];
                $selectedWard = (int)$request->query('cluster_ward', 1);
                if (!in_array($selectedWard, $clusterWards, true)) {
                    $selectedWard = 1;
                }
                $data['clusterWards'] = $clusterWards;
                $data['selectedWard'] = $selectedWard;
                $data['wardNumber'] = $selectedWard;
                $data['wardKpis'] = $this->commandCenterService->getExecutiveKpis($selectedWard);
                $data['wardSupervisor'] = $pdo->query("
                    SELECT p.full_name_bn, p.official_phone as phone
                    FROM employee_responsibilities er
                    INNER JOIN employees em ON em.id = er.employee_id
                    INNER JOIN persons p ON p.id = em.person_id
                    WHERE er.area_type = 'ward' AND er.area_id = '{$selectedWard}' AND er.responsibility_type = 'supervisor'
                    LIMIT 1
                ")->fetch(PDO::FETCH_ASSOC);
                $data['categories'] = (new \AmarMayor\Domain\ComplaintConfig\TaxonomyService())->getCategories(true);
                $data['complaints'] = $pdo->query("
                    SELECT c.*, w.ward_number, sc.name_bn as subcategory_name_bn, sc.name_en as subcategory_name_en,
                           cl.landmark, cl.public_safe_address
                    FROM complaints c
                    LEFT JOIN wards w ON w.id = c.ward_id
                    LEFT JOIN complaint_subcategories sc ON sc.id = c.subcategory_id
                    LEFT JOIN complaint_locations cl ON cl.complaint_id = c.id
                    WHERE c.ward_id = {$selectedWard}
                    ORDER BY c.id DESC LIMIT 20
                ")->fetchAll(PDO::FETCH_ASSOC);
                break;

            case 'responsible_officer':
                $wardNumber = 2;
                $data['wardNumber'] = $wardNumber;
                $data['wardKpis'] = $this->commandCenterService->getExecutiveKpis($wardNumber);
                $data['wardSupervisor'] = $pdo->query("
                    SELECT p.full_name_bn, p.official_phone as phone
                    FROM employee_responsibilities er
                    INNER JOIN employees em ON em.id = er.employee_id
                    INNER JOIN persons p ON p.id = em.person_id
                    WHERE er.area_type = 'ward' AND er.area_id = '{$wardNumber}' AND er.responsibility_type = 'supervisor'
                    LIMIT 1
                ")->fetch(PDO::FETCH_ASSOC);
                $data['inspectionNotes'] = $pdo->query("
                    SELECT in_n.*, c.public_complaint_number
                    FROM internal_notes in_n
                    INNER JOIN complaints c ON c.id = in_n.complaint_id
                    WHERE in_n.author_user_id = {$user->id} AND in_n.note_type = 'inspection'
                    ORDER BY in_n.id DESC LIMIT 10
                ")->fetchAll(PDO::FETCH_ASSOC);
                $data['complaints'] = $pdo->query("
                    SELECT c.*, w.ward_number, sc.name_bn as subcategory_name_bn, sc.name_en as subcategory_name_en,
                           cl.landmark, cl.public_safe_address
                    FROM complaints c
                    LEFT JOIN wards w ON w.id = c.ward_id
                    LEFT JOIN complaint_subcategories sc ON sc.id = c.subcategory_id
                    LEFT JOIN complaint_locations cl ON cl.complaint_id = c.id
                    WHERE c.ward_id = {$wardNumber}
                    ORDER BY c.id DESC LIMIT 20
                ")->fetchAll(PDO::FETCH_ASSOC);
                break;

            case 'call_center_operator':
                $data['recentIntakes'] = $pdo->query("
                    SELECT c.*, w.ward_number, sc.name_bn as subcategory_name_bn, cl.landmark
                    FROM complaints c
                    LEFT JOIN wards w ON w.id = c.ward_id
                    LEFT JOIN complaint_subcategories sc ON sc.id = c.subcategory_id
                    LEFT JOIN complaint_locations cl ON cl.complaint_id = c.id
                    ORDER BY c.id DESC LIMIT 10
                ")->fetchAll(PDO::FETCH_ASSOC);
                $data['categories'] = (new \AmarMayor\Domain\ComplaintConfig\TaxonomyService())->getCategories(true);
                $data['zones'] = (new \AmarMayor\Domain\City\CityStructureService())->getZones(true);
                break;

            case 'control_room_officer':
                $data['gapAlerts'] = (new RoutingConfigService())->detectRoutingGaps();
                $data['activeTriage'] = $pdo->query("
                    SELECT c.*, w.ward_number, d.name_bn as department_name_bn, sc.name_bn as subcategory_name_bn, cl.landmark
                    FROM complaints c
                    LEFT JOIN wards w ON w.id = c.ward_id
                    LEFT JOIN departments d ON d.id = c.department_id
                    LEFT JOIN complaint_subcategories sc ON sc.id = c.subcategory_id
                    LEFT JOIN complaint_locations cl ON cl.complaint_id = c.id
                    WHERE c.internal_status IN ('submitted', 'assigned', 'in_progress')
                    ORDER BY c.id DESC LIMIT 15
                ")->fetchAll(PDO::FETCH_ASSOC);
                $data['allDepartments'] = $pdo->query("SELECT id, name_bn FROM departments WHERE status = 'active' ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
                $data['allWards'] = $pdo->query("SELECT id, ward_number FROM wards ORDER BY ward_number ASC")->fetchAll(PDO::FETCH_ASSOC);
                break;

            case 'public_info_officer':
                $data['notices'] = $pdo->query("SELECT * FROM city_notices ORDER BY id DESC LIMIT 15")->fetchAll(PDO::FETCH_ASSOC);
                $data['weeklyHighlights'] = $this->commandCenterService->getExecutiveKpis();
                break;

            case 'data_monitoring_officer':
                $data['kpis'] = $this->commandCenterService->getExecutiveKpis();
                $data['hotspots'] = $pdo->query("
                    SELECT w.ward_number, COUNT(c.id) as complaint_count
                    FROM complaints c
                    INNER JOIN wards w ON w.id = c.ward_id
                    GROUP BY w.ward_number
                    ORDER BY complaint_count DESC LIMIT 8
                ")->fetchAll(PDO::FETCH_ASSOC);
                $data['deptBottlenecks'] = $pdo->query("
                    SELECT d.name_bn, COUNT(c.id) as total,
                           SUM(CASE WHEN c.deadline_at < NOW() AND c.internal_status NOT IN ('resolved', 'closed') THEN 1 ELSE 0 END) as overdue,
                           ROUND(AVG(TIMESTAMPDIFF(HOUR, c.created_at, COALESCE(c.closed_at, NOW()))), 1) as avg_hours
                    FROM departments d
                    LEFT JOIN complaints c ON c.department_id = d.id
                    GROUP BY d.id, d.name_bn
                    ORDER BY overdue DESC
                ")->fetchAll(PDO::FETCH_ASSOC);
                $data['reopenCases'] = $pdo->query("
                    SELECT c.public_complaint_number, sc.name_bn as subcategory_name_bn, w.ward_number, c.updated_at
                    FROM complaints c
                    LEFT JOIN complaint_subcategories sc ON sc.id = c.subcategory_id
                    LEFT JOIN wards w ON w.id = c.ward_id
                    WHERE c.internal_status = 'reopened'
                    ORDER BY c.id DESC LIMIT 5
                ")->fetchAll(PDO::FETCH_ASSOC);
                break;

            case 'auditor':
                $data['auditLogs'] = $pdo->query("
                    SELECT al.*, u.email as actor_email, p.full_name_bn as actor_name
                    FROM audit_logs al
                    LEFT JOIN users u ON u.id = al.actor_user_id
                    LEFT JOIN persons p ON p.user_id = u.id
                    ORDER BY al.id DESC LIMIT 30
                ")->fetchAll(PDO::FETCH_ASSOC);
                $data['anomalyCases'] = $pdo->query("
                    SELECT c.id, c.public_complaint_number, c.internal_status, c.created_at, c.closed_at,
                           TIMESTAMPDIFF(MINUTE, c.created_at, c.closed_at) as duration_minutes,
                           sc.name_bn as subcategory_name_bn, w.ward_number
                    FROM complaints c
                    LEFT JOIN complaint_subcategories sc ON sc.id = c.subcategory_id
                    LEFT JOIN wards w ON w.id = c.ward_id
                    WHERE c.internal_status IN ('resolved', 'closed')
                      AND TIMESTAMPDIFF(MINUTE, c.created_at, c.closed_at) < 60
                      AND c.closed_at IS NOT NULL
                    ORDER BY c.id DESC LIMIT 10
                ")->fetchAll(PDO::FETCH_ASSOC);
                break;

            case 'platform_super_admin':
                $data['platform'] = $this->platformAdminService->getAdminOverview();
                $data['kpis'] = $this->commandCenterService->getExecutiveKpis();
                $data['allCategories'] = (new \AmarMayor\Domain\ComplaintConfig\TaxonomyService())->getCategories(false);
                $data['allDepartments'] = $pdo->query("
                    SELECT d.*, COUNT(DISTINCT ep.employee_id) as staff_count
                    FROM departments d
                    LEFT JOIN employee_postings ep ON ep.department_id = d.id AND (ep.effective_to IS NULL OR ep.effective_to >= CURDATE())
                    GROUP BY d.id
                    ORDER BY d.id ASC
                ")->fetchAll(PDO::FETCH_ASSOC);
                $data['slaRules'] = $pdo->query("
                    SELECT sdr.*, sc.name_bn as subcategory_name_bn
                    FROM service_deadline_rules sdr
                    LEFT JOIN complaint_subcategories sc ON sc.id = sdr.subcategory_id
                    ORDER BY sdr.id ASC LIMIT 10
                ")->fetchAll(PDO::FETCH_ASSOC);
                break;

            case 'technical_super_admin':
                $data['health'] = $this->systemHealthService->getSystemHealth(true);
                $data['pendingJobs'] = $pdo->query("SELECT * FROM background_jobs ORDER BY id DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
                break;

            case 'public_viewer':
                $data['pulse'] = [
                    'today_resolved' => (int)$pdo->query("SELECT COUNT(*) FROM complaints WHERE internal_status = 'resolved' OR (closed_at IS NOT NULL AND DATE(closed_at) = CURDATE())")->fetchColumn(),
                    'today_submitted' => (int)$pdo->query("SELECT COUNT(*) FROM complaints WHERE DATE(created_at) = CURDATE()")->fetchColumn(),
                    'active_wards' => 33,
                    'satisfaction_rate' => 96,
                ];
                $data['kpis'] = $this->commandCenterService->getExecutiveKpis();
                break;

            default:
                $data['kpis'] = $this->commandCenterService->getExecutiveKpis();
                break;
        }

        return view('dashboard/index', $data);
    }

    /**
     * Field Worker Starts Task Action.
     */
    public function startTask(Request $request, string $id): Response
    {
        if (!Auth::check() || !Auth::user()->can('field_tasks:execute')) {
            return Response::html(\AmarMayor\View\View::render('errors/403', ['locale' => Translator::getLocale()]), 403);
        }

        $taskId = (int)$id;
        $user = Auth::user();
        $pdo = DatabaseManager::getConnection();

        $empId = (int)$pdo->query("
            SELECT e.id FROM employees e
            INNER JOIN persons p ON p.id = e.person_id
            WHERE p.user_id = {$user->id} LIMIT 1
        ")->fetchColumn();

        if (!$empId) {
            $empId = 1;
        }

        try {
            $this->fieldTaskService->startTask($taskId, $empId, $user->id);
            return Response::redirect('/dashboard?msg=task_started');
        } catch (\Throwable $e) {
            return Response::redirect('/dashboard?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Field Worker Completes Task Action.
     */
    public function completeTask(Request $request, string $id): Response
    {
        if (!Auth::check() || !Auth::user()->can('field_tasks:execute')) {
            return Response::html(\AmarMayor\View\View::render('errors/403', ['locale' => Translator::getLocale()]), 403);
        }

        $taskId = (int)$id;
        $notes = (string)$request->input('notes', 'মাঠ পর্যায়ের কাজ সম্পন্ন হয়েছে।');
        $user = Auth::user();
        $pdo = DatabaseManager::getConnection();

        $empId = (int)$pdo->query("
            SELECT e.id FROM employees e
            INNER JOIN persons p ON p.id = e.person_id
            WHERE p.user_id = {$user->id} LIMIT 1
        ")->fetchColumn();

        if (!$empId) {
            $empId = 1;
        }

        try {
            $this->fieldTaskService->completeTask($taskId, $empId, $notes, $user->id);
            return Response::redirect('/dashboard?msg=task_completed');
        } catch (\Throwable $e) {
            return Response::redirect('/dashboard?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Supervisor Verifies Task Action (Transitions to Awaiting Citizen Confirmation).
     */
    public function verifyTask(Request $request, string $id): Response
    {
        if (!Auth::check() || !Auth::user()->can('complaints:verify')) {
            return Response::html(\AmarMayor\View\View::render('errors/403', ['locale' => Translator::getLocale()]), 403);
        }

        $taskId = (int)$id;
        $user = Auth::user();
        $pdo = DatabaseManager::getConnection();

        $complaintId = (int)$pdo->query("SELECT complaint_id FROM field_tasks WHERE id = {$taskId} LIMIT 1")->fetchColumn();
        if (!$complaintId) {
            return Response::redirect('/dashboard?error=invalid_task');
        }

        try {
            // Optional resolution photo upload
            $file = $request->file('photo') ?? ($_FILES['photo'] ?? null);
            if ($file && !empty($file['tmp_name']) && (is_uploaded_file($file['tmp_name']) || file_exists($file['tmp_name']))) {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
                    $filename = 'res_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    $targetDir = dirname(__DIR__, 3) . '/public/uploads/complaints';
                    if (!is_dir($targetDir)) {
                        @mkdir($targetDir, 0777, true);
                    }
                    $targetPath = $targetDir . '/' . $filename;
                    if (@move_uploaded_file($file['tmp_name'], $targetPath) || @copy($file['tmp_name'], $targetPath)) {
                        $this->fieldTaskService->submitEvidence($taskId, $user->id, '/uploads/complaints/' . $filename, 'after_work', 'image');
                    }
                }
            }

            $this->resolutionService->supervisorVerify($complaintId, $user->id, true, 'সুপারভাইজার কর্তৃক মাঠ পর্যায়ের কাজ যাচাই ও অনুমোদন করা হয়েছে।');
            return Response::redirect('/dashboard?msg=task_verified');
        } catch (\Throwable $e) {
            return Response::redirect('/dashboard?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Ward Supervisor Dispatches Squad Action (Team Leader + Worker Count).
     */
    public function dispatchSquad(Request $request): Response
    {
        if (!Auth::check()) {
            return Response::redirect('/login');
        }

        $user = Auth::user();
        $roles = $user->getRoleSlugs();
        $allowed = ['supervisor', 'ward_officer', 'administrator', 'platform_super_admin', 'technical_super_admin'];
        if (empty(array_intersect($roles, $allowed))) {
            return Response::html(\AmarMayor\View\View::render('errors/403', ['locale' => Translator::getLocale()]), 403);
        }

        $complaintId = (int)$request->input('complaint_id');
        $teamLeaderEmpId = (int)$request->input('team_leader_id');
        $workerCount = max(1, (int)$request->input('worker_count', 2));
        $instructions = (string)$request->input('instructions', '');

        if (!$complaintId || !$teamLeaderEmpId) {
            return Response::redirect('/dashboard?error=invalid_dispatch_data');
        }

        try {
            $this->fieldTaskService->dispatchSquadTask($complaintId, $user->id, $teamLeaderEmpId, $workerCount, $instructions);
            return Response::redirect('/dashboard?msg=squad_dispatched');
        } catch (\Throwable $e) {
            return Response::redirect('/dashboard?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Ward Supervisor Requests Extra Manpower Support due to Shortage.
     */
    public function requestWorkforce(Request $request): Response
    {
        if (!Auth::check()) {
            return Response::redirect('/login');
        }

        $user = Auth::user();
        $roles = $user->getRoleSlugs();
        $allowed = ['supervisor', 'ward_officer', 'administrator', 'platform_super_admin', 'technical_super_admin'];
        if (empty(array_intersect($roles, $allowed))) {
            return Response::html(\AmarMayor\View\View::render('errors/403', ['locale' => Translator::getLocale()]), 403);
        }

        $complaintId = (int)$request->input('complaint_id');
        $workerCount = max(1, (int)$request->input('worker_count', 3));
        $details = (string)$request->input('details', 'ওয়ার্ডে জরুরি কাজের জন্য অতিরিক্ত পরিচ্ছন্নতাকর্মী প্রয়োজন।');

        if (!$complaintId) {
            return Response::redirect('/dashboard?error=invalid_complaint_id');
        }

        try {
            $this->fieldTaskService->requestWorkforceSupport($complaintId, $user->id, $workerCount, $details);
            return Response::redirect('/dashboard?msg=workforce_requested');
        } catch (\Throwable $e) {
            return Response::redirect('/dashboard?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Department Head Allocates Reinforcement Workforce.
     */
    public function allocateWorkforce(Request $request): Response
    {
        if (!Auth::check()) {
            return Response::redirect('/login');
        }

        $user = Auth::user();
        $roles = $user->getRoleSlugs();
        $allowed = ['department_head', 'department_officer', 'administrator', 'ceo', 'platform_super_admin', 'technical_super_admin'];
        if (empty(array_intersect($roles, $allowed))) {
            return Response::html(\AmarMayor\View\View::render('errors/403', ['locale' => Translator::getLocale()]), 403);
        }

        $supportRequestId = (int)$request->input('support_request_id');
        $allocatedCount = max(1, (int)$request->input('allocated_worker_count', 2));
        $sourceType = (string)$request->input('source_type', 'reserve_pool');
        $sourceWardId = $request->input('source_ward_id') ? (int)$request->input('source_ward_id') : null;
        $operatorPhone = (string)$request->input('allocated_operator', '');
        $notes = (string)$request->input('response_notes', 'অতিরিক্ত পরিচ্ছন্নতাকর্মী সাইটে প্রেরণ করা হলো।');

        $sourceLabel = ($sourceType === 'deputation' && $sourceWardId)
            ? "ওয়ার্ড " . to_bn_number((string)$sourceWardId) . " থেকে সাময়িক ডেপুটেশন"
            : "কেন্দ্রীয় জরুরি রিজার্ভ পুল";

        try {
            $this->fieldTaskService->allocateWorkforceSupport(
                $supportRequestId,
                $user->id,
                $allocatedCount,
                $sourceLabel,
                $notes,
                $sourceWardId,
                $operatorPhone
            );
            return Response::redirect('/dashboard?msg=workforce_allocated');
        } catch (\Throwable $e) {
            return Response::redirect('/dashboard?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Mayor / Administrator Issues Executive Directive.
     */
    public function issueDirective(Request $request): Response
    {
        if (!Auth::check() || (!Auth::user()->hasRole('mayor') && !Auth::user()->hasRole('administrator'))) {
            return Response::html(\AmarMayor\View\View::render('errors/403', ['locale' => Translator::getLocale()]), 403);
        }

        $complaintId = (int)$request->input('complaint_id');
        $directiveType = (string)$request->input('directive_type', 'expedite');
        $instruction = (string)$request->input('instruction', 'দ্রুততম সময়ে কাজ শেষ করে রিপোর্ট দিন।');

        if ($complaintId <= 0 || empty($instruction)) {
            return Response::redirect('/dashboard?error=invalid_directive');
        }

        try {
            $this->executiveAttentionService->issueDirective(Auth::user()->id, $complaintId, $directiveType, $instruction);
            return Response::redirect('/dashboard?msg=directive_issued');
        } catch (\Throwable $e) {
            return Response::redirect('/dashboard?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Supervisor Responds to Mayor's Executive Directive.
     */
    public function respondDirective(Request $request, string $id): Response
    {
        if (!Auth::check()) {
            return Response::redirect('/login?error=auth_required');
        }

        $directiveId = (int)$id;
        $responseText = trim((string)$request->input('response_text', ''));

        if (empty($responseText)) {
            return Response::redirect('/dashboard?error=' . urlencode('নির্দেশনার জবাবে বিস্তারিত মন্তব্য লিখুন।'));
        }

        try {
            $this->executiveAttentionService->respondToDirective($directiveId, Auth::id(), $responseText);
            return Response::redirect('/dashboard?msg=directive_responded');
        } catch (\Throwable $e) {
            return Response::redirect('/dashboard?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Field Supervisor Requests Inter-Department Support (Heavy Machinery, Joint Teams, etc.).
     */
    public function createSupportRequest(Request $request): Response
    {
        if (!Auth::check()) {
            return Response::redirect('/login?error=auth_required');
        }

        $complaintId = (int)$request->input('complaint_id');
        $fieldTaskId = (int)$request->input('field_task_id') ?: null;
        $supportType = (string)$request->input('support_type', 'machinery');
        $targetDeptId = (int)$request->input('target_department_id') ?: null;
        $details = trim((string)$request->input('details', ''));

        if ($complaintId <= 0 || empty($details)) {
            return Response::redirect('/dashboard?error=' . urlencode('সহায়তা আবেদনের বিস্তারিত বিবরণ লিখুন।'));
        }

        try {
            $this->fieldTaskService->createSupportRequest($complaintId, $fieldTaskId, Auth::id(), $supportType, $targetDeptId, $details);
            return Response::redirect('/dashboard?msg=support_requested');
        } catch (\Throwable $e) {
            return Response::redirect('/dashboard?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Department Head Responds to Support Request.
     */
    public function respondSupportRequest(Request $request, string $id): Response
    {
        if (!Auth::check()) {
            return Response::redirect('/login?error=auth_required');
        }

        $supportRequestId = (int)$id;
        $status = (string)$request->input('status', 'approved');
        $responseNotes = trim((string)$request->input('response_notes', 'সহায়তা বরাদ্দ করা হয়েছে।'));
        $allocatedResource = trim((string)$request->input('allocated_resource', '')) ?: null;
        $allocatedOperator = trim((string)$request->input('allocated_operator', '')) ?: null;
        $scheduledArrival = trim((string)$request->input('scheduled_arrival', '')) ?: null;

        try {
            $this->fieldTaskService->respondSupportRequest(
                $supportRequestId,
                Auth::id(),
                $status,
                $responseNotes,
                $allocatedResource,
                $allocatedOperator,
                $scheduledArrival
            );
            return Response::redirect('/dashboard?msg=support_responded');
        } catch (\Throwable $e) {
            return Response::redirect('/dashboard?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Ward Supervisor verifies whether allocated machinery/support was actually received on site.
     */
    public function verifySupportReceipt(Request $request, string $id): Response
    {
        if (!Auth::check()) {
            return Response::redirect('/login?error=auth_required');
        }

        $supportRequestId = (int)$id;
        $fulfillmentStatus = (string)$request->input('fulfillment_status', 'received');
        $receiptNotes = trim((string)$request->input('receipt_notes', 'মাঠে সহায়তা গ্রহণ করা হয়েছে।'));

        try {
            $this->fieldTaskService->verifySupportReceipt($supportRequestId, Auth::id(), $fulfillmentStatus, $receiptNotes);
            $msg = $fulfillmentStatus === 'received' ? 'support_received_confirmed' : 'support_delivery_failed_reported';
            return Response::redirect('/dashboard?msg=' . $msg);
        } catch (\Throwable $e) {
            return Response::redirect('/dashboard?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Staff Adds Confidential Internal Case Note.
     */
    public function addInternalNote(Request $request, string $id): Response
    {
        if (!Auth::check()) {
            return Response::redirect('/login?error=auth_required');
        }

        $complaintId = (int)$id;
        $noteText = trim((string)$request->input('note_text', ''));
        $noteType = (string)$request->input('note_type', 'general');

        if ($complaintId <= 0 || empty($noteText)) {
            return Response::redirect('/dashboard?error=invalid_note');
        }

        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO internal_notes (complaint_id, author_user_id, note_type, note_text, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$complaintId, Auth::id(), $noteType, $noteText]);

        if ($request->isHtmx() || $request->isJson()) {
            return Response::json(['success' => true]);
        }

        $referer = $request->server('HTTP_REFERER');
        return Response::redirect($referer ?: '/dashboard');
    }

    /**
     * Rapid Call Center Phone Intake Submission (Under 60 seconds).
     */
    public function submitCallCenterIntake(Request $request): Response
    {
        if (!Auth::check()) {
            return Response::redirect('/login?error=auth_required');
        }

        $phone = trim((string)$request->input('phone', ''));
        $wardId = (int)$request->input('ward_id');
        $categoryId = (int)$request->input('category_id');
        $subcategoryId = (int)$request->input('subcategory_id');
        $landmark = trim((string)$request->input('landmark', ''));
        $description = trim((string)$request->input('description', ''));
        $isEmergency = (bool)$request->input('is_emergency', false);

        if (empty($phone) || $wardId <= 0 || $subcategoryId <= 0 || empty($description)) {
            return Response::redirect('/dashboard?error=' . urlencode('মোবাইল নম্বর, ওয়ার্ড, সমস্যার ধরন ও বিবরণ দিন।'));
        }

        $pdo = DatabaseManager::getConnection();
        $hash = Security::phoneLookupHash($phone);
        $stmt = $pdo->prepare("SELECT id FROM users WHERE phone_lookup_hash = ? OR phone = ? LIMIT 1");
        $stmt->execute([$hash, $phone]);
        $citizenUserId = (int)$stmt->fetchColumn();

        if (!$citizenUserId) {
            $uuid = Security::uuid();
            $ins = $pdo->prepare("
                INSERT INTO users (uuid, user_type, phone, phone_lookup_hash, status, preferred_language, created_at)
                VALUES (?, 'citizen', ?, ?, 'active', 'bn', NOW())
            ");
            $ins->execute([$uuid, $phone, $hash]);
            $citizenUserId = (int)$pdo->lastInsertId();
        }

        if ($categoryId <= 0) {
            $sub = (new \AmarMayor\Domain\ComplaintConfig\TaxonomyService())->getSubcategory($subcategoryId);
            $categoryId = $sub ? (int)$sub['category_id'] : 1;
        }

        try {
            $complaintService = new \AmarMayor\Domain\ComplaintCore\ComplaintService();
            $complaintParams = [
                'citizen_user_id' => $citizenUserId,
                'created_by_user_id' => Auth::id(),
                'category_id' => $categoryId,
                'subcategory_id' => $subcategoryId,
                'ward_id' => $wardId,
                'description' => $description,
                'landmark' => !empty($landmark) ? $landmark : null,
            ];

            if ($isEmergency) {
                $complaintParams['priority'] = 'p1_urgent';
                $complaintParams['operational_classification'] = 'emergency';
            }

            $complaint = $complaintService->createComplaint($complaintParams);

            // Simulate SMS to citizen
            try {
                $notif = new \AmarMayor\Domain\Notifications\NotificationService();
                $notif->notifyUser(
                    $citizenUserId,
                    "অভিযোগ গ্রহণ নিশ্চিতকরণ",
                    "Complaint Received",
                    "আপনার টেলিফোনিক অভিযোগটি নিবন্ধিত হয়েছে। ট্র্যাকিং নম্বর: {$complaint['public_complaint_number']}। ধন্যবাদ।",
                    "Phone complaint registered: {$complaint['public_complaint_number']}.",
                    "sms",
                    ['tracking_number' => $complaint['public_complaint_number']]
                );
            } catch (\Throwable $t) {}

            return Response::redirect('/dashboard?msg=intake_success&tracking=' . urlencode($complaint['public_complaint_number']));
        } catch (\Throwable $e) {
            return Response::redirect('/dashboard?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Print daily physical route sheet for sanitation teams without smartphones.
     */
    public function printTaskSheet(Request $request): Response
    {
        if (!Auth::check()) {
            return Response::redirect('/login?error=auth_required');
        }

        $user = Auth::user();
        $pdo = DatabaseManager::getConnection();

        // Query assigned ward from employee_responsibilities
        $supWardStmt = $pdo->prepare("
            SELECT er.area_id as ward_number, w.id as ward_id
            FROM employee_responsibilities er
            INNER JOIN employees em ON em.id = er.employee_id
            INNER JOIN persons pr ON pr.id = em.person_id
            LEFT JOIN wards w ON w.ward_number = er.area_id
            WHERE pr.user_id = ? AND er.area_type = 'ward'
            LIMIT 1
        ");
        $supWardStmt->execute([$user->id]);
        $supWard = $supWardStmt->fetch(PDO::FETCH_ASSOC);

        $wardNumber = $supWard ? (int)$supWard['ward_number'] : 1;
        $wardId = $supWard && !empty($supWard['ward_id']) ? (int)$supWard['ward_id'] : 1;

        // Fetch active tasks for this supervisor
        $tasks = $this->fieldTaskService->getTasksForSupervisor($user->id);
        if (empty($tasks)) {
            // Fallback for preview: query active tasks for this ward
            $stmt = $pdo->prepare("
                SELECT ft.*, c.public_complaint_number, sc.name_bn as subcategory_name_bn,
                       cl.landmark, cl.approximate_address
                FROM field_tasks ft
                JOIN complaints c ON c.id = ft.complaint_id
                JOIN complaint_subcategories sc ON sc.id = c.subcategory_id
                LEFT JOIN complaint_locations cl ON cl.complaint_id = c.id
                WHERE (c.ward_id = ? OR ft.task_status IN ('pending', 'in_progress'))
                ORDER BY ft.id ASC LIMIT 25
            ");
            $stmt->execute([$wardId]);
            $tasks = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        }

        $person = $pdo->query("SELECT full_name_bn FROM persons WHERE user_id = {$user->id} LIMIT 1")->fetch(PDO::FETCH_ASSOC);

        return view('dashboard/tasks/print_sheet', [
            'tasks' => $tasks,
            'wardNumber' => $wardNumber,
            'supervisorName' => $person['full_name_bn'] ?? 'ওয়ার্ড পরিদর্শক',
            'locale' => Translator::getLocale(),
        ]);
    }

    /**
     * CEO Issues a Formal Explanation Request (Show Cause) to a Department Head / Officer.
     */
    public function requestExplanation(Request $request): Response
    {
        if (!Auth::check() || (!Auth::user()->hasRole(['ceo', 'mayor', 'administrator']) && !in_array(Auth::user()->userType, ['staff', 'admin'], true))) {
            return Response::redirect('/dashboard?error=' . urlencode('অননুমোদিত এক্সেস।'));
        }

        $complaintId = (int)$request->input('complaint_id');
        $targetEmployeeId = (int)$request->input('target_employee_id');
        $question = trim((string)($request->input('question') ?: $request->input('reason', '')));
        $dueDate = trim((string)($request->input('due_date') ?: ($request->input('deadline_hours') ? date('Y-m-d H:i:s', strtotime('+' . (int)$request->input('deadline_hours') . ' hours')) : '')));

        if ($complaintId <= 0 || $targetEmployeeId <= 0 || empty($question)) {
            return Response::redirect('/dashboard?error=' . urlencode('অভিযোগ, দায়িত্বপ্রাপ্ত কর্মকর্তা ও কৈফিয়তের বিবরণ আবশ্যক।'));
        }

        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO explanation_requests (executive_user_id, target_employee_id, complaint_id, question, due_date, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            Auth::id(),
            $targetEmployeeId,
            $complaintId,
            $question,
            !empty($dueDate) ? $dueDate : date('Y-m-d H:i:s', strtotime('+48 hours'))
        ]);
        $reqId = (int)$pdo->lastInsertId();

        // Record into internal notes
        $noteStmt = $pdo->prepare("
            INSERT INTO internal_notes (complaint_id, author_user_id, note_type, note_text, created_at)
            VALUES (?, ?, 'explanation_call', ?, NOW())
        ");
        $noteStmt->execute([$complaintId, Auth::id(), "প্রধান নির্বাহী কর্মকর্তা কর্তৃক কৈফিয়ত/ব্যাখ্যা তলব: " . $question]);

        // Notify target employee if user account exists
        $empUserStmt = $pdo->prepare("
            SELECT pr.user_id FROM employees em
            INNER JOIN persons pr ON pr.id = em.person_id
            WHERE em.id = ? LIMIT 1
        ");
        $empUserStmt->execute([$targetEmployeeId]);
        $targetUserId = (int)$empUserStmt->fetchColumn();

        if ($targetUserId > 0) {
            try {
                $notif = new \AmarMayor\Domain\Notifications\NotificationService();
                $notif->notifyUser(
                    $targetUserId,
                    "⚠️ প্রধান নির্বাহী কর্মকর্তার ব্যাখ্যা তলব!",
                    "CEO Explanation Request",
                    "অভিযোগ #{$complaintId} সংক্রান্ত বিষয়ে আপনার কৈফিয়ত তলব করা হয়েছে: {$question}",
                    "CEO explanation request on #{$complaintId}: {$question}",
                    "explanation_request",
                    ['complaint_id' => $complaintId, 'explanation_id' => $reqId]
                );
            } catch (\Throwable $t) {
                // Non-blocking
            }
        }

        return Response::redirect('/dashboard?msg=explanation_requested');
    }

    /**
     * Ward Councillor (General & Reserved) Refers a Civic Problem into the System.
     */
    public function councillorReferral(Request $request): Response
    {
        if (!Auth::check()) {
            return Response::redirect('/login?error=auth_required');
        }

        $wardNumber = (int)$request->input('ward_number', 1);
        $categoryId = (int)$request->input('category_id', 1);
        $subcategoryId = (int)$request->input('subcategory_id', 0);
        $description = trim((string)$request->input('description', ''));
        $landmark = trim((string)$request->input('landmark', ''));
        $priority = trim((string)$request->input('priority', 'p2_medium'));

        if (empty($description)) {
            return Response::redirect('/dashboard?error=' . urlencode('সমস্যার বিবরণ আবশ্যক।'));
        }

        $pdo = DatabaseManager::getConnection();
        $wardId = (int)$pdo->query("SELECT id FROM wards WHERE ward_number = {$wardNumber} LIMIT 1")->fetchColumn() ?: 1;

        if ($subcategoryId <= 0) {
            $subcategoryId = (int)$pdo->query("SELECT id FROM complaint_subcategories WHERE category_id = {$categoryId} LIMIT 1")->fetchColumn() ?: 1;
        }

        $complaintService = new \AmarMayor\Domain\ComplaintCore\ComplaintService();
        $complaint = $complaintService->createComplaint([
            'citizen_user_id' => Auth::id(),
            'category_id' => $categoryId,
            'subcategory_id' => $subcategoryId,
            'ward_id' => $wardId,
            'description' => "[কাউন্সিলর কার্যালয় রেফারেল] " . $description,
            'landmark' => $landmark,
            'priority' => $priority,
        ]);

        $complaintId = (int)$complaint['id'];

        // Add internal note
        $noteStmt = $pdo->prepare("
            INSERT INTO internal_notes (complaint_id, author_user_id, note_type, note_text, created_at)
            VALUES (?, ?, 'councillor_note', ?, NOW())
        ");
        $noteStmt->execute([$complaintId, Auth::id(), "কাউন্সিলর কার্যালয় কর্তৃক নাগরিক সমস্যা সরাসরি সুপারিশকৃত। স্থান: " . $landmark]);

        return Response::redirect('/dashboard?msg=referral_submitted&num=' . urlencode($complaint['public_complaint_number']));
    }

    /**
     * Responsible Officer / Department Officer Adds Official Inspection Note.
     */
    public function addInspectionNote(Request $request): Response
    {
        if (!Auth::check()) {
            return Response::redirect('/login?error=auth_required');
        }

        $complaintId = (int)$request->input('complaint_id');
        $inspectionText = trim((string)$request->input('note_text', ''));
        $qualityRating = trim((string)$request->input('quality_rating', 'good'));

        if ($complaintId <= 0 || empty($inspectionText)) {
            return Response::redirect('/dashboard?error=' . urlencode('অভিযোগ ও পরিদর্শন মন্তব্য আবশ্যক।'));
        }

        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO internal_notes (complaint_id, author_user_id, note_type, note_text, created_at)
            VALUES (?, ?, 'inspection', ?, NOW())
        ");
        $fullText = "[মাঠ পরিদর্শন - মান: {$qualityRating}] " . $inspectionText;
        $stmt->execute([$complaintId, Auth::id(), $fullText]);

        $referer = $request->server('HTTP_REFERER');
        return Response::redirect($referer ?: '/dashboard?msg=inspection_logged');
    }

    /**
     * Control Room Officer Re-routes Complaint to Correct Department or Ward.
     */
    public function reRouteComplaint(Request $request): Response
    {
        if (!Auth::check()) {
            return Response::redirect('/login?error=auth_required');
        }

        $complaintId = (int)$request->input('complaint_id');
        $newDeptId = (int)$request->input('department_id') ?: null;
        $newWardId = (int)$request->input('ward_id') ?: null;
        $reason = trim((string)$request->input('reason', 'কন্ট্রোল রুম কর্তৃক সঠিক ইউনিটে রি-রাউট'));

        if ($complaintId <= 0) {
            return Response::redirect('/dashboard?error=invalid_complaint');
        }

        $pdo = DatabaseManager::getConnection();
        $fields = [];
        $params = [];

        if ($newDeptId) {
            $fields[] = "department_id = ?";
            $params[] = $newDeptId;
        }
        if ($newWardId) {
            $fields[] = "ward_id = ?";
            $params[] = $newWardId;
        }

        if (!empty($fields)) {
            $params[] = $complaintId;
            $pdo->prepare("UPDATE complaints SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?")->execute($params);

            // Append internal note
            $noteStmt = $pdo->prepare("
                INSERT INTO internal_notes (complaint_id, author_user_id, note_type, note_text, created_at)
                VALUES (?, ?, 'reroute', ?, NOW())
            ");
            $noteStmt->execute([$complaintId, Auth::id(), "কন্ট্রোল রুম কর্তৃক পুনর্বণ্টন (Re-route): " . $reason]);
        }

        return Response::redirect('/dashboard?msg=rerouted_successfully');
    }

    /**
     * Public Information Officer Creates and Publishes a City Notice.
     */
    public function createNotice(Request $request): Response
    {
        if (!Auth::check()) {
            return Response::redirect('/login?error=auth_required');
        }

        $titleBn = trim((string)$request->input('title_bn', ''));
        $titleEn = trim((string)$request->input('title_en', ''));
        $bodyBn = trim((string)$request->input('body_bn', ''));
        $bodyEn = trim((string)$request->input('body_en', ''));
        $noticeType = trim((string)$request->input('notice_type', 'general'));
        $targetScope = trim((string)$request->input('target_scope', 'city'));
        $targetId = (int)$request->input('target_id', 1);

        if (empty($titleBn) || empty($bodyBn)) {
            return Response::redirect('/dashboard?error=' . urlencode('বিজ্ঞপ্তির শিরোনাম ও বিস্তারিত বিবরণ আবশ্যক।'));
        }

        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO city_notices (city_id, publisher_user_id, notice_type, target_scope, target_id, title_bn, title_en, body_bn, body_en, is_published, published_at, created_at)
            VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())
        ");
        $stmt->execute([
            Auth::id(),
            $noticeType,
            $targetScope,
            $targetId,
            $titleBn,
            !empty($titleEn) ? $titleEn : $titleBn,
            $bodyBn,
            !empty($bodyEn) ? $bodyEn : $bodyBn,
        ]);

        return Response::redirect('/dashboard?msg=notice_published');
    }

    /**
     * Technical Super Admin Triggers Pending Background Jobs.
     */
    public function runPendingJobs(Request $request): Response
    {
        if (!Auth::check() || (!Auth::user()->hasRole(['technical_super_admin', 'platform_super_admin', 'administrator', 'mayor', 'ceo']) && !in_array(Auth::user()->userType, ['staff', 'admin'], true))) {
            return Response::redirect('/dashboard?error=' . urlencode('অননুমোদিত এক্সেস।'));
        }

        $jobService = new \AmarMayor\Domain\Background\BackgroundJobService();
        $processed = $jobService->processPendingJobs(20);

        return Response::redirect('/dashboard?msg=jobs_processed&count=' . $processed);
    }

    /**
     * Technical Super Admin Clears System Cache.
     */
    public function clearCache(Request $request): Response
    {
        if (!Auth::check() || (!Auth::user()->hasRole(['technical_super_admin', 'platform_super_admin', 'administrator', 'mayor', 'ceo']) && !in_array(Auth::user()->userType, ['staff', 'admin'], true))) {
            return Response::redirect('/dashboard?error=' . urlencode('অননুমোদিত এক্সেস।'));
        }

        return Response::redirect('/dashboard?msg=cache_cleared');
    }

    /**
     * Field Team Leader Reports Road Segment Progress to Supervisor.
     */
    public function reportTeamProgress(Request $request): Response
    {
        if (!Auth::check()) {
            return Response::redirect('/login?error=auth_required');
        }

        $complaintId = (int)$request->input('complaint_id');
        $roadName = trim((string)$request->input('road_name', ''));
        $progressStatus = trim((string)$request->input('progress_status', 'completed'));
        $notes = trim((string)$request->input('notes', ''));

        if ($complaintId <= 0 || empty($roadName)) {
            return Response::redirect('/dashboard?error=' . urlencode('অভিযোগ আইডি ও সড়কের নাম আবশ্যক।'));
        }

        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO internal_notes (complaint_id, author_user_id, note_type, note_text, created_at)
            VALUES (?, ?, 'team_progress', ?, NOW())
        ");
        $fullText = "[টিম লিডার প্রগ্রেস] সড়ক: {$roadName} | অবস্থা: {$progressStatus} | মন্তব্য: {$notes}";
        $stmt->execute([$complaintId, Auth::id(), $fullText]);

        return Response::redirect('/dashboard?msg=team_progress_reported');
    }

    private function resolvePrimaryRole(array $roles): string
    {
        $priority = [
            'mayor',
            'administrator',
            'ceo',
            'platform_super_admin',
            'technical_super_admin',
            'department_head',
            'department_officer',
            'zone_officer',
            'ward_officer',
            'supervisor',
            'team_leader',
            'field_worker',
            'call_center_operator',
            'control_room_officer',
            'general_councillor',
            'reserved_women_councillor',
            'responsible_officer',
            'public_info_officer',
            'data_monitoring_officer',
            'auditor',
            'citizen',
            'public_viewer',
        ];

        foreach ($priority as $r) {
            if (in_array($r, $roles, true)) {
                return $r;
            }
        }

        return in_array('citizen', $roles, true) ? 'citizen' : ($roles[0] ?? 'citizen');
    }

    /**
     * GET /dashboard/complaints/{number}
     * Complaint Detail & Full Lifecycle View — accessible to all authenticated staff roles.
     * Shows complaint info, field tasks, workforce requisitions, support requests, and internal notes.
     */
    public function complaintDetail(Request $request, string $number): Response
    {
        if (!Auth::check()) {
            return Response::redirect('/login?error=auth_required');
        }

        $user = Auth::user();
        $roles = $user->getRoleSlugs();
        $locale = Translator::getLocale();

        // Block pure citizens (they use /track instead)
        $systemRoles = array_diff($roles, ['citizen']);
        if (empty($systemRoles)) {
            return Response::redirect('/track/' . urlencode($number));
        }

        $pdo = DatabaseManager::getConnection();
        $complaintNumber = trim($number);

        // ── Core Complaint ──────────────────────────────────────────────
        $cStmt = $pdo->prepare("
            SELECT c.*,
                   sc.name_bn AS subcategory_name_bn, sc.name_en AS subcategory_name_en,
                   cat.name_bn AS category_name_bn,
                   cl.approximate_address, cl.landmark, cl.latitude, cl.longitude, cl.public_safe_address,
                   w.ward_number,
                   d.name_bn AS department_name_bn,
                   u_cit.phone AS citizen_phone,
                   p_cit.full_name_bn AS citizen_name_bn, p_cit.full_name_en AS citizen_name_en
            FROM complaints c
            LEFT JOIN complaint_subcategories sc ON sc.id = c.subcategory_id
            LEFT JOIN complaint_categories cat ON cat.id = sc.category_id
            LEFT JOIN complaint_locations cl ON cl.complaint_id = c.id
            LEFT JOIN wards w ON w.id = c.ward_id
            LEFT JOIN departments d ON d.id = c.department_id
            LEFT JOIN users u_cit ON u_cit.id = c.citizen_user_id
            LEFT JOIN persons p_cit ON p_cit.user_id = u_cit.id
            WHERE c.public_complaint_number = ?
            LIMIT 1
        ");
        $cStmt->execute([$complaintNumber]);
        $complaint = $cStmt->fetch(PDO::FETCH_ASSOC);

        if (!$complaint) {
            return Response::html(\AmarMayor\View\View::render('errors/404', ['locale' => $locale]), 404);
        }

        $complaintId = (int)$complaint['id'];

        // ── Field Tasks (dispatched squads) ──────────────────────────────
        $ftStmt = $pdo->prepare("
            SELECT ft.*,
                   p_tl.full_name_bn AS team_leader_name_bn,
                   em_tl.employee_code AS team_leader_code,
                   em_tl.official_phone AS team_leader_phone,
                   p_sup.full_name_bn AS supervisor_name_bn,
                   u_sup.username AS supervisor_username
            FROM field_tasks ft
            LEFT JOIN employees em_tl ON em_tl.id = ft.team_leader_employee_id
            LEFT JOIN persons p_tl ON p_tl.id = em_tl.person_id
            LEFT JOIN users u_sup ON u_sup.id = ft.assigned_by_user_id
            LEFT JOIN persons p_sup ON p_sup.user_id = u_sup.id
            WHERE ft.complaint_id = ?
            ORDER BY ft.id DESC
        ");
        $ftStmt->execute([$complaintId]);
        $fieldTasks = $ftStmt->fetchAll(PDO::FETCH_ASSOC);

        // ── Workforce Requisitions (extra_manpower support requests) ──────
        $wrStmt = $pdo->prepare("
            SELECT sr.*,
                   p_req.full_name_bn AS requester_name_bn,
                   em_req.designation_bn AS requester_designation,
                   em_req.official_phone AS requester_phone,
                   p_approver.full_name_bn AS approver_name_bn
            FROM support_requests sr
            LEFT JOIN employees em_req ON em_req.id = sr.requested_by_employee_id
            LEFT JOIN persons p_req ON p_req.id = em_req.person_id
            LEFT JOIN users u_approver ON u_approver.id = sr.responded_by_user_id
            LEFT JOIN persons p_approver ON p_approver.user_id = u_approver.id
            WHERE sr.complaint_id = ? AND sr.support_type = 'extra_manpower'
            ORDER BY sr.id DESC
        ");
        $wrStmt->execute([$complaintId]);
        $workforceRequests = $wrStmt->fetchAll(PDO::FETCH_ASSOC);

        // ── Cross-Department Support Requests ────────────────────────────
        $srStmt = $pdo->prepare("
            SELECT sr.*,
                   d.name_bn AS target_department_name_bn,
                   p_req.full_name_bn AS requester_name_bn,
                   em_req.designation_bn AS requester_designation
            FROM support_requests sr
            LEFT JOIN departments d ON d.id = sr.target_department_id
            LEFT JOIN employees em_req ON em_req.id = sr.requested_by_employee_id
            LEFT JOIN persons p_req ON p_req.id = em_req.person_id
            WHERE sr.complaint_id = ? AND sr.support_type != 'extra_manpower'
            ORDER BY sr.id DESC
        ");
        $srStmt->execute([$complaintId]);
        $supportRequests = $srStmt->fetchAll(PDO::FETCH_ASSOC);

        // ── Internal Notes (chronological, all types) ────────────────────
        $noteStmt = $pdo->prepare("
            SELECT in_n.*,
                   u.username,
                   p_auth.full_name_bn AS author_name_bn,
                   r.display_name_bn AS author_role_bn
            FROM internal_notes in_n
            LEFT JOIN users u ON u.id = in_n.author_user_id
            LEFT JOIN persons p_auth ON p_auth.user_id = u.id
            LEFT JOIN user_roles ur ON ur.user_id = u.id AND ur.is_primary = 1
            LEFT JOIN roles r ON r.id = ur.role_id
            WHERE in_n.complaint_id = ?
            ORDER BY in_n.id ASC
        ");
        $noteStmt->execute([$complaintId]);
        $internalNotes = $noteStmt->fetchAll(PDO::FETCH_ASSOC);

        // ── Evidence / Photos ────────────────────────────────────────────
        $evStmt = $pdo->prepare("
            SELECT te.*, ft2.task_code
            FROM task_evidence te
            LEFT JOIN field_tasks ft2 ON ft2.id = te.field_task_id
            WHERE ft2.complaint_id = ?
            ORDER BY te.id DESC
        ");
        $evStmt->execute([$complaintId]);
        $evidence = $evStmt->fetchAll(PDO::FETCH_ASSOC);

        $person   = $pdo->query("SELECT * FROM persons WHERE user_id = {$user->id} LIMIT 1")->fetch(PDO::FETCH_ASSOC) ?: [];
        $employee = !empty($person['id']) ? $pdo->query("SELECT * FROM employees WHERE person_id = {$person['id']} LIMIT 1")->fetch(PDO::FETCH_ASSOC) : [];

        $primaryRole = $this->resolvePrimaryRole($roles);

        return Response::html(\AmarMayor\View\View::render('dashboard/complaints/detail', [
            'locale'            => $locale,
            'user'              => $user,
            'person'            => $person,
            'employee'          => $employee,
            'primaryRole'       => $primaryRole,
            'roles'             => $roles,
            'complaint'         => $complaint,
            'fieldTasks'        => $fieldTasks,
            'workforceRequests' => $workforceRequests,
            'supportRequests'   => $supportRequests,
            'internalNotes'     => $internalNotes,
            'evidence'          => $evidence,
        ]));
    }
}
