<?php

declare(strict_types=1);

namespace AmarMayor\Controllers\Web;

use AmarMayor\Auth\Auth;
use AmarMayor\Database\DatabaseManager;
use AmarMayor\Http\Request;
use AmarMayor\Http\Response;
use AmarMayor\Support\Translator;
use PDO;

class ReportWebController
{
    private const ALLOWED_ROLES = [
        'mayor',
        'administrator',
        'ceo',
        'secretary',
        'department_head',
        'general_councillor',
        'reserved_women_councillor',
        'ward_officer',
        'zone_officer',
        'platform_super_admin',
        'technical_super_admin',
        'responsible_officer',
        'auditor',
        'data_monitoring_officer',
        'control_room_officer',
    ];

    /**
     * Interactive civic report dashboard with filters.
     */
    public function index(Request $request): Response
    {
        $authCheck = $this->authorizeExecutive();
        if ($authCheck !== null) {
            return $authCheck;
        }

        $locale = Translator::getLocale();
        $reportData = $this->compileReportData($request);

        return view('reports/index', array_merge($reportData, [
            'locale' => $locale,
            'user' => Auth::user(),
        ]));
    }

    /**
     * Official print-ready layout formatted for A4 paper and coordination meetings.
     */
    public function printView(Request $request): Response
    {
        $authCheck = $this->authorizeExecutive();
        if ($authCheck !== null) {
            return $authCheck;
        }

        $locale = Translator::getLocale();
        $reportData = $this->compileReportData($request);

        return view('reports/print', array_merge($reportData, [
            'locale' => $locale,
            'user' => Auth::user(),
        ]));
    }

    /**
     * Export report data as UTF-8 CSV with BOM for Microsoft Excel.
     */
    public function exportCsv(Request $request): Response
    {
        $authCheck = $this->authorizeExecutive();
        if ($authCheck !== null) {
            return $authCheck;
        }

        $reportData = $this->compileReportData($request);
        $complaints = $reportData['complaints'];

        $output = "\xEF\xBB\xBF"; // UTF-8 BOM
        $output .= "ট্র্যাকিং নম্বর,ওয়ার্ড নং,অঞ্চল,পৌর বিভাগ,সমস্যার ধরন,দায়িত্বপ্রাপ্ত সুপারভাইজার,কাজের অবস্থা,দাখিলের তারিখ,সময়সীমা,সুনির্দিষ্ট এলাকা ও ঠিকানা\n";

        foreach ($complaints as $c) {
            $tracking = '"' . str_replace('"', '""', $c['public_complaint_number'] ?? $c['tracking_number'] ?? '') . '"';
            $ward = '"' . ($c['ward_number'] ?? '') . '"';
            $zone = '"' . str_replace('"', '""', $c['zone_name_bn'] ?? '') . '"';
            $dept = '"' . str_replace('"', '""', $c['category_name_bn'] ?? '') . '"';
            $subcat = '"' . str_replace('"', '""', $c['subcategory_name_bn'] ?? '') . '"';
            $supervisor = '"' . str_replace('"', '""', $c['supervisor_name_bn'] ?? 'অনির্ধারিত') . '"';
            $status = '"' . str_replace('"', '""', human_status($c['internal_status'] ?? '', 'bn')) . '"';
            $createdAt = '"' . ($c['created_at'] ?? '') . '"';
            $deadline = '"' . ($c['deadline_at'] ?? '') . '"';
            $address = '"' . str_replace('"', '""', ($c['landmark'] ? $c['landmark'] . ', ' : '') . ($c['public_safe_address'] ?? '')) . '"';

            $output .= "{$tracking},{$ward},{$zone},{$dept},{$subcat},{$supervisor},{$status},{$createdAt},{$deadline},{$address}\n";
        }

        $filename = 'MCC_Report_' . date('Y-m-d_His') . '.csv';

        return new Response($output, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Verify caller has executive / administrative reporting rights.
     */
    private function authorizeExecutive(): ?Response
    {
        if (!Auth::check()) {
            return Response::redirect('/login?error=auth_required');
        }

        $user = Auth::user();
        if (!$user) {
            return Response::redirect('/login');
        }

        $roles = $user->getRoleSlugs();
        $allowed = array_intersect($roles, self::ALLOWED_ROLES);

        if (empty($allowed)) {
            return Response::redirect('/dashboard');
        }

        return null;
    }

    /**
     * Compile metrics, aggregations, and complaint registers based on filters.
     */
    private function compileReportData(Request $request): array
    {
        $pdo = DatabaseManager::getConnection();

        // 1. Filter parameters
        $period = (string)$request->query('period', 'all');
        $year = (string)$request->query('year', '');
        $month = (string)$request->query('month', '');
        $startDate = (string)$request->query('start_date', '');
        $endDate = (string)$request->query('end_date', '');
        $zoneId = (string)$request->query('zone_id', '');
        $wardId = (string)$request->query('ward_id', '');
        $categoryId = (string)$request->query('category_id', '');
        $supervisorId = (string)$request->query('supervisor_id', '');
        $status = (string)$request->query('status', 'all');

        $banglaMonths = [
            1 => 'জানুয়ারি', 2 => 'ফেব্রুয়ারি', 3 => 'মার্চ', 4 => 'এপ্রিল',
            5 => 'মে', 6 => 'জুন', 7 => 'জুলাই', 8 => 'আগস্ট',
            9 => 'সেপ্টেম্বর', 10 => 'অক্টোবর', 11 => 'নভেম্বর', 12 => 'ডিসেম্বর'
        ];

        // Date calculation
        $whereClauses = ['1=1'];
        $params = [];

        if (!empty($year) && is_numeric($year) && !empty($month) && is_numeric($month)) {
            $whereClauses[] = "YEAR(c.created_at) = ? AND MONTH(c.created_at) = ?";
            $params[] = (int)$year;
            $params[] = (int)$month;
            $period = 'month_year';
            $periodLabelBn = ($banglaMonths[(int)$month] ?? '') . ' ' . to_bn_number((string)$year) . '-এর প্রতিবেদন';
        } elseif (!empty($year) && is_numeric($year)) {
            $whereClauses[] = "YEAR(c.created_at) = ?";
            $params[] = (int)$year;
            $period = 'year';
            $periodLabelBn = to_bn_number((string)$year) . ' সালের বার্ষিক প্রতিবেদন';
        } elseif ($period === 'today') {
            $whereClauses[] = "c.created_at >= CURDATE()";
            $periodLabelBn = 'আজকের প্রতিবেদন (' . to_bn_number(date('d-m-Y')) . ')';
        } elseif ($period === '7days') {
            $whereClauses[] = "c.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            $periodLabelBn = 'বিগত ৭ দিনের প্রতিবেদন';
        } elseif ($period === 'this_month') {
            $whereClauses[] = "c.created_at >= DATE_FORMAT(NOW() ,'%Y-%m-01')";
            $periodLabelBn = 'চলতি মাসের প্রতিবেদন (' . to_bn_number(date('F Y')) . ')';
        } elseif ($period === 'custom' && !empty($startDate) && !empty($endDate)) {
            $whereClauses[] = "c.created_at >= ? AND c.created_at <= ?";
            $params[] = $startDate . ' 00:00:00';
            $params[] = $endDate . ' 23:59:59';
            $periodLabelBn = to_bn_number($startDate) . ' হতে ' . to_bn_number($endDate);
        } else {
            $period = 'all';
            $periodLabelBn = 'সার্বিক প্রতিবেদন (সকল সময়)';
        }

        // Zone Filter
        if (!empty($zoneId) && is_numeric($zoneId)) {
            $whereClauses[] = "c.zone_id = ?";
            $params[] = (int)$zoneId;
        }

        // Ward Filter
        if (!empty($wardId) && is_numeric($wardId)) {
            $whereClauses[] = "c.ward_id = ?";
            $params[] = (int)$wardId;
        }

        // Category Filter
        if (!empty($categoryId) && is_numeric($categoryId)) {
            $whereClauses[] = "c.category_id = ?";
            $params[] = (int)$categoryId;
        }

        // Supervisor Filter
        if (!empty($supervisorId) && is_numeric($supervisorId)) {
            $whereClauses[] = "c.current_supervisor_employee_id = ?";
            $params[] = (int)$supervisorId;
        }

        // Status Filter
        if ($status === 'in_progress') {
            $whereClauses[] = "c.internal_status IN ('in_progress', 'dispatched', 'work_completed', 'supervisor_verified')";
        } elseif ($status === 'overdue') {
            $whereClauses[] = "c.deadline_at < NOW() AND c.closed_at IS NULL";
        } elseif ($status === 'resolved') {
            $whereClauses[] = "c.internal_status IN ('citizen_confirmed', 'closed')";
        } elseif ($status === 'reopened') {
            $whereClauses[] = "c.reopen_count > 0";
        }

        $whereSql = implode(' AND ', $whereClauses);

        // 2. Fetch Aggregated Metrics
        $summarySql = "
            SELECT 
                COUNT(c.id) as total_complaints,
                SUM(CASE WHEN c.internal_status IN ('citizen_confirmed', 'closed') THEN 1 ELSE 0 END) as resolved_count,
                SUM(CASE WHEN c.internal_status IN ('in_progress', 'dispatched', 'work_completed', 'supervisor_verified') THEN 1 ELSE 0 END) as in_progress_count,
                SUM(CASE WHEN c.deadline_at < NOW() AND c.closed_at IS NULL THEN 1 ELSE 0 END) as overdue_count,
                SUM(CASE WHEN c.reopen_count > 0 THEN 1 ELSE 0 END) as reopened_count,
                AVG(CASE WHEN c.closed_at IS NOT NULL THEN TIMESTAMPDIFF(HOUR, c.created_at, c.closed_at) ELSE NULL END) as avg_resolution_hours
            FROM complaints c
            WHERE {$whereSql}
        ";
        $stmt = $pdo->prepare($summarySql);
        $stmt->execute($params);
        $metrics = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $total = (int)($metrics['total_complaints'] ?? 0);
        $resolved = (int)($metrics['resolved_count'] ?? 0);
        $resolutionRate = $total > 0 ? round(($resolved / $total) * 100, 1) : 0.0;

        // Citizen satisfaction
        $feedbackSql = "
            SELECT 
                COUNT(cf.id) as total_feedback,
                SUM(CASE WHEN cf.resolution_confirmation = 'confirmed_resolved' OR cf.rating_score >= 4 THEN 1 ELSE 0 END) as satisfied_count
            FROM citizen_feedback cf
            JOIN complaints c ON c.id = cf.complaint_id
            WHERE {$whereSql}
        ";
        $fStmt = $pdo->prepare($feedbackSql);
        $fStmt->execute($params);
        $fMetrics = $fStmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $totalFeedback = (int)($fMetrics['total_feedback'] ?? 0);
        $satisfiedCount = (int)($fMetrics['satisfied_count'] ?? 0);
        $satisfactionRate = $totalFeedback > 0 ? round(($satisfiedCount / $totalFeedback) * 100, 1) : null;

        // 3. Zonal Breakdown (Zones 1, 2, 3)
        $zoneSql = "
            SELECT 
                z.id as zone_id,
                z.zone_number,
                z.name_bn,
                z.name_bn as zone_name_bn,
                z.name_en,
                z.name_en as zone_name_en,
                COUNT(c.id) as total_complaints,
                SUM(CASE WHEN c.internal_status IN ('citizen_confirmed', 'closed') THEN 1 ELSE 0 END) as resolved_count,
                SUM(CASE WHEN c.internal_status IN ('in_progress', 'dispatched', 'work_completed', 'supervisor_verified') THEN 1 ELSE 0 END) as in_progress_count,
                SUM(CASE WHEN c.deadline_at < NOW() AND c.closed_at IS NULL THEN 1 ELSE 0 END) as overdue_count,
                AVG(CASE WHEN c.closed_at IS NOT NULL THEN TIMESTAMPDIFF(HOUR, c.created_at, c.closed_at) ELSE NULL END) as avg_resolution_hours
            FROM zones z
            LEFT JOIN complaints c ON c.zone_id = z.id AND {$whereSql}
            GROUP BY z.id, z.zone_number, z.name_bn, z.name_en
            ORDER BY z.zone_number ASC
        ";
        $zoneStmt = $pdo->prepare($zoneSql);
        $zoneStmt->execute($params);
        $zoneBreakdown = $zoneStmt->fetchAll(PDO::FETCH_ASSOC);

        // 4. Supervisor Performance Scorecard (All 33 Wards or Scoped to Zone/Ward)
        $supWhereConditions = ["(er.effective_to IS NULL OR er.effective_to >= NOW())"];
        $supFilterParams = [];
        if (!empty($wardId) && is_numeric($wardId)) {
            $supWhereConditions[] = "w.id = ?";
            $supFilterParams[] = (int)$wardId;
        } elseif (!empty($zoneId) && is_numeric($zoneId)) {
            $supWhereConditions[] = "z.id = ?";
            $supFilterParams[] = (int)$zoneId;
        }
        if (!empty($supervisorId) && is_numeric($supervisorId)) {
            $supWhereConditions[] = "e.id = ?";
            $supFilterParams[] = (int)$supervisorId;
        }
        $supWhereExtra = "WHERE " . implode(' AND ', $supWhereConditions);
        $supStmtParams = array_merge($params, $supFilterParams);

        $supSql = "
            SELECT 
                e.id as supervisor_id,
                p.full_name_bn as supervisor_name_bn,
                p.official_phone,
                e.employee_code,
                w.ward_number,
                w.id as ward_id,
                z.zone_number,
                z.name_bn as zone_name_bn,
                COUNT(c.id) as total_complaints,
                SUM(CASE WHEN c.internal_status IN ('citizen_confirmed', 'closed') THEN 1 ELSE 0 END) as resolved_count,
                SUM(CASE WHEN c.internal_status IN ('in_progress', 'dispatched', 'work_completed', 'supervisor_verified') THEN 1 ELSE 0 END) as in_progress_count,
                SUM(CASE WHEN c.deadline_at < NOW() AND c.closed_at IS NULL THEN 1 ELSE 0 END) as overdue_count,
                SUM(CASE WHEN c.reopen_count > 0 THEN 1 ELSE 0 END) as reopened_count,
                AVG(CASE WHEN c.closed_at IS NOT NULL THEN TIMESTAMPDIFF(HOUR, c.created_at, c.closed_at) ELSE NULL END) as avg_resolution_hours
            FROM employees e
            JOIN persons p ON p.id = e.person_id
            JOIN user_roles ur ON ur.user_id = p.user_id
            JOIN roles r ON r.id = ur.role_id AND r.slug = 'supervisor'
            JOIN employee_responsibilities er ON er.employee_id = e.id AND er.area_type = 'ward'
            JOIN wards w ON w.ward_number = er.area_id
            JOIN zones z ON z.id = w.zone_id
            LEFT JOIN complaints c ON c.ward_id = w.id AND {$whereSql}
            {$supWhereExtra}
            GROUP BY e.id, p.full_name_bn, p.official_phone, e.employee_code, w.ward_number, w.id, z.zone_number, z.name_bn
            ORDER BY w.ward_number ASC
        ";
        $supStmt = $pdo->prepare($supSql);
        $supStmt->execute($supStmtParams);
        $supervisorBreakdown = $supStmt->fetchAll(PDO::FETCH_ASSOC);

        // 5. Department Breakdown
        $catSql = "
            SELECT 
                cat.id, cat.name_bn, cat.name_en, cat.icon_name,
                COUNT(c.id) as total,
                SUM(CASE WHEN c.internal_status IN ('citizen_confirmed', 'closed') THEN 1 ELSE 0 END) as resolved,
                SUM(CASE WHEN c.internal_status IN ('in_progress', 'dispatched', 'work_completed', 'supervisor_verified') THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN c.deadline_at < NOW() AND c.closed_at IS NULL THEN 1 ELSE 0 END) as overdue
            FROM complaint_categories cat
            LEFT JOIN complaints c ON c.category_id = cat.id AND {$whereSql}
            GROUP BY cat.id
            ORDER BY total DESC
        ";
        $catStmt = $pdo->prepare($catSql);
        $catStmt->execute($params);
        $categoryBreakdown = $catStmt->fetchAll(PDO::FETCH_ASSOC);

        // 6. Ward Breakdown (All 33 Wards)
        $wardSql = "
            SELECT 
                w.id, w.ward_number, w.zone_id, z.name_bn as zone_name_bn,
                COUNT(c.id) as total,
                SUM(CASE WHEN c.internal_status IN ('citizen_confirmed', 'closed') THEN 1 ELSE 0 END) as resolved,
                SUM(CASE WHEN c.internal_status IN ('in_progress', 'dispatched', 'work_completed', 'supervisor_verified') THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN c.deadline_at < NOW() AND c.closed_at IS NULL THEN 1 ELSE 0 END) as overdue
            FROM wards w
            LEFT JOIN zones z ON z.id = w.zone_id
            LEFT JOIN complaints c ON c.ward_id = w.id AND {$whereSql}
            GROUP BY w.id, w.ward_number, w.zone_id, z.name_bn
            ORDER BY w.ward_number ASC
        ";
        $wardStmt = $pdo->prepare($wardSql);
        $wardStmt->execute($params);
        $wardBreakdown = $wardStmt->fetchAll(PDO::FETCH_ASSOC);

        // 7. Complaints List (up to 150 items)
        $listSql = "
            SELECT c.*, w.ward_number, z.name_bn as zone_name_bn,
                   cat.name_bn as category_name_bn, cat.name_en as category_name_en,
                   sc.name_bn as subcategory_name_bn, sc.name_en as subcategory_name_en,
                   cl.landmark, cl.public_safe_address,
                   p_sup.full_name_bn as supervisor_name_bn
            FROM complaints c
            LEFT JOIN wards w ON w.id = c.ward_id
            LEFT JOIN zones z ON z.id = c.zone_id
            LEFT JOIN complaint_categories cat ON cat.id = c.category_id
            LEFT JOIN complaint_subcategories sc ON sc.id = c.subcategory_id
            LEFT JOIN complaint_locations cl ON cl.complaint_id = c.id
            LEFT JOIN employees e_sup ON e_sup.id = c.current_supervisor_employee_id
            LEFT JOIN persons p_sup ON p_sup.id = e_sup.person_id
            WHERE {$whereSql}
            ORDER BY c.id DESC
            LIMIT 150
        ";
        $listStmt = $pdo->prepare($listSql);
        $listStmt->execute($params);
        $complaints = $listStmt->fetchAll(PDO::FETCH_ASSOC);

        // Dropdown data
        $allZones = $pdo->query("SELECT id, zone_number, name_bn, name_en FROM zones ORDER BY zone_number ASC")->fetchAll(PDO::FETCH_ASSOC);
        $allWards = $pdo->query("SELECT id, ward_number, zone_id FROM wards ORDER BY ward_number ASC")->fetchAll(PDO::FETCH_ASSOC);
        $allCategories = $pdo->query("SELECT id, name_bn, name_en FROM complaint_categories ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
        $allSupervisors = $pdo->query("
            SELECT e.id as employee_id, p.full_name_bn, p.official_phone, er.area_id as ward_number
            FROM employees e
            JOIN persons p ON p.id = e.person_id
            JOIN user_roles ur ON ur.user_id = p.user_id
            JOIN roles r ON r.id = ur.role_id AND r.slug = 'supervisor'
            JOIN employee_responsibilities er ON er.employee_id = e.id AND er.area_type = 'ward'
            ORDER BY CAST(er.area_id AS UNSIGNED) ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        return [
            'period' => $period,
            'periodLabelBn' => $periodLabelBn,
            'year' => $year,
            'month' => $month,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'zoneId' => $zoneId,
            'wardId' => $wardId,
            'categoryId' => $categoryId,
            'supervisorId' => $supervisorId,
            'status' => $status,
            'metrics' => [
                'total_complaints' => $total,
                'resolved_count' => $resolved,
                'in_progress_count' => (int)($metrics['in_progress_count'] ?? 0),
                'overdue_count' => (int)($metrics['overdue_count'] ?? 0),
                'reopened_count' => (int)($metrics['reopened_count'] ?? 0),
                'resolution_rate' => $resolutionRate,
                'avg_resolution_hours' => round((float)($metrics['avg_resolution_hours'] ?? 0), 1),
                'satisfaction_rate' => $satisfactionRate,
                'total_feedback' => $totalFeedback,
            ],
            'zoneBreakdown' => $zoneBreakdown,
            'supervisorBreakdown' => $supervisorBreakdown,
            'categoryBreakdown' => $categoryBreakdown,
            'wardBreakdown' => $wardBreakdown,
            'complaints' => $complaints,
            'allZones' => $allZones,
            'allWards' => $allWards,
            'allCategories' => $allCategories,
            'allSupervisors' => $allSupervisors,
            'banglaMonths' => $banglaMonths,
        ];
    }
}
