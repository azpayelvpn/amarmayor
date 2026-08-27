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

        // If pure citizen or user without staff roles, always redirect to citizen portal
        $roles = $user->getRoleSlugs();
        $staffRoles = array_diff($roles, ['citizen', 'public_viewer']);
        if ($user->userType === 'citizen' || empty($staffRoles)) {
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
            case 'administrator':
                $data['kpis'] = $this->commandCenterService->getExecutiveKpis();
                $data['dailyBrief'] = $this->commandCenterService->getDailyBrief();
                $data['attentionQueue'] = $this->executiveAttentionService->getAttentionQueue();
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
                break;

            case 'field_worker':
            case 'team_leader':
                $data['tasks'] = $this->fieldTaskService->getTasksForWorker($user->id);
                break;

            case 'supervisor':
                $data['tasks'] = $this->fieldTaskService->getTasksForSupervisor($user->id);
                $data['kpis'] = $this->commandCenterService->getExecutiveKpis();
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
            case 'department_officer':
                $deptId = 1; // Waste Management default
                $data['departmentNameBn'] = 'বর্জ্য ব্যবস্থাপনা বিভাগ';
                $data['departmentNameEn'] = 'Waste Management Department';
                $data['kpis'] = $this->commandCenterService->getExecutiveKpis(null, null, $deptId);
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
            case 'responsible_officer':
            case 'reserved_women_councillor':
                $data['wardNumber'] = ($primaryRole === 'responsible_officer') ? 2 : 1;
                $data['complaints'] = $pdo->query("
                    SELECT c.*, w.ward_number, sc.name_bn as subcategory_name_bn, sc.name_en as subcategory_name_en,
                           cl.landmark, cl.public_safe_address
                    FROM complaints c
                    LEFT JOIN wards w ON w.id = c.ward_id
                    LEFT JOIN complaint_subcategories sc ON sc.id = c.subcategory_id
                    LEFT JOIN complaint_locations cl ON cl.complaint_id = c.id
                    WHERE c.ward_id = {$data['wardNumber']}
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
                break;

            case 'control_room_officer':
                $data['gapAlerts'] = (new RoutingConfigService())->detectRoutingGaps();
                $data['activeTriage'] = $pdo->query("
                    SELECT c.*, w.ward_number, sc.name_bn as subcategory_name_bn, cl.landmark
                    FROM complaints c
                    LEFT JOIN wards w ON w.id = c.ward_id
                    LEFT JOIN complaint_subcategories sc ON sc.id = c.subcategory_id
                    LEFT JOIN complaint_locations cl ON cl.complaint_id = c.id
                    WHERE c.internal_status IN ('submitted', 'assigned', 'in_progress')
                    ORDER BY c.id DESC LIMIT 15
                ")->fetchAll(PDO::FETCH_ASSOC);
                break;

            case 'public_info_officer':
                $data['notices'] = $pdo->query("SELECT * FROM city_notices ORDER BY id DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
                break;

            case 'data_monitoring_officer':
                $data['kpis'] = $this->commandCenterService->getExecutiveKpis();
                $data['hotspots'] = $pdo->query("
                    SELECT w.ward_number, COUNT(c.id) as complaint_count
                    FROM complaints c
                    INNER JOIN wards w ON w.id = c.ward_id
                    GROUP BY w.ward_number
                    ORDER BY complaint_count DESC LIMIT 5
                ")->fetchAll(PDO::FETCH_ASSOC);
                break;

            case 'auditor':
                $data['auditLogs'] = $pdo->query("
                    SELECT al.*, u.email as actor_email
                    FROM audit_logs al
                    LEFT JOIN users u ON u.id = al.actor_user_id
                    ORDER BY al.id DESC LIMIT 25
                ")->fetchAll(PDO::FETCH_ASSOC);
                break;

            case 'platform_super_admin':
                $data['platform'] = $this->platformAdminService->getAdminOverview();
                $data['kpis'] = $this->commandCenterService->getExecutiveKpis();
                break;

            case 'technical_super_admin':
                $data['health'] = $this->systemHealthService->getSystemHealth(true);
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
            $this->resolutionService->supervisorVerify($complaintId, $user->id, true, 'সুপারভাইজার কর্তৃক মাঠ পর্যায়ের কাজ যাচাই ও অনুমোদন করা হয়েছে।');
            return Response::redirect('/dashboard?msg=task_verified');
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
}
