<?php

declare(strict_types=1);

namespace AmarMayor\Domain\CommandCenter;

use AmarMayor\Auth\User;
use AmarMayor\Database\DatabaseManager;
use PDO;

class CommandCenterService
{
    /**
     * Computes the 6 top-level executive KPIs with optional hierarchical scoping (Ward/Zone/Dept).
     *
     * @return array{
     *   total_complaints: int,
     *   in_progress: int,
     *   overdue_count: int,
     *   reopen_rate_percent: float,
     *   avg_resolution_hours: float,
     *   citizen_satisfaction_percent: float
     * }
     */
    public function getExecutiveKpis(?int $wardId = null, ?int $zoneId = null, ?int $deptId = null): array
    {
        $pdo = DatabaseManager::getConnection();

        $where = "WHERE 1=1";
        $params = [];

        if ($wardId !== null) {
            $where .= " AND c.ward_id = ?";
            $params[] = $wardId;
        } elseif ($zoneId !== null) {
            $where .= " AND c.zone_id = ?";
            $params[] = $zoneId;
        }

        if ($deptId !== null) {
            $where .= " AND c.department_id = ?";
            $params[] = $deptId;
        }

        // 1. Total Complaints & In Progress
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_complaints,
                SUM(CASE WHEN c.internal_status IN ('in_progress', 'work_completed', 'awaiting_citizen_confirmation') THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN c.reopen_count > 0 THEN 1 ELSE 0 END) as reopened_count,
                SUM(CASE WHEN c.closed_at IS NOT NULL THEN 1 ELSE 0 END) as closed_count
            FROM complaints c
            {$where}
        ");
        $stmt->execute($params);
        $counts = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $total = (int)($counts['total_complaints'] ?? 0);
        $inProgress = (int)($counts['in_progress'] ?? 0);
        $reopened = (int)($counts['reopened_count'] ?? 0);
        $closed = (int)($counts['closed_count'] ?? 0);

        // 2. Overdue Count (Active unresolved deadline breaches)
        $stmtOverdue = $pdo->prepare("
            SELECT COUNT(*) FROM complaints c
            {$where} AND c.deadline_at IS NOT NULL AND c.deadline_at < NOW() AND c.closed_at IS NULL
        ");
        $stmtOverdue->execute($params);
        $overdueCount = (int)$stmtOverdue->fetchColumn();

        // 3. Reopen Rate %
        $reopenRate = $total > 0 ? round(($reopened / $total) * 100, 1) : 0.0;

        // 4. Average Resolution Hours
        $stmtAvg = $pdo->prepare("
            SELECT AVG(TIMESTAMPDIFF(HOUR, c.submitted_at, c.closed_at)) as avg_hours
            FROM complaints c
            {$where} AND c.closed_at IS NOT NULL
        ");
        $stmtAvg->execute($params);
        $avgHours = (float)$stmtAvg->fetchColumn();

        // 5. Citizen Satisfaction % (Ratings 4 or 5)
        $stmtSat = $pdo->prepare("
            SELECT 
                COUNT(*) as total_feedback,
                SUM(CASE WHEN cf.rating_score >= 4 THEN 1 ELSE 0 END) as positive_feedback
            FROM citizen_feedback cf
            INNER JOIN complaints c ON c.id = cf.complaint_id
            {$where} AND cf.rating_score IS NOT NULL
        ");
        $stmtSat->execute($params);
        $satData = $stmtSat->fetch(PDO::FETCH_ASSOC) ?: [];
        $totalFeedback = (int)($satData['total_feedback'] ?? 0);
        $positiveFeedback = (int)($satData['positive_feedback'] ?? 0);
        $satisfactionPercent = $totalFeedback > 0 ? round(($positiveFeedback / $totalFeedback) * 100, 1) : null;

        return [
            'total_complaints' => $total,
            'in_progress' => $inProgress,
            'overdue_count' => $overdueCount,
            'reopen_rate_percent' => $reopenRate,
            'avg_resolution_hours' => round($avgHours, 1),
            'citizen_satisfaction_percent' => $satisfactionPercent,
            'total_feedback' => $totalFeedback,
        ];
    }

    /**
     * 24-hour Executive Daily Brief for Mayor and Administrator.
     */
    public function getDailyBrief(): array
    {
        $pdo = DatabaseManager::getConnection();

        $new24h = (int)$pdo->query("SELECT COUNT(*) FROM complaints WHERE submitted_at >= NOW() - INTERVAL 24 HOUR")->fetchColumn();
        $resolved24h = (int)$pdo->query("SELECT COUNT(*) FROM complaints WHERE closed_at >= NOW() - INTERVAL 24 HOUR")->fetchColumn();
        $overdue24h = (int)$pdo->query("SELECT COUNT(*) FROM complaints WHERE deadline_missed_at >= NOW() - INTERVAL 24 HOUR")->fetchColumn();
        $reopened24h = (int)$pdo->query("SELECT COUNT(*) FROM complaints WHERE first_reopened_at >= NOW() - INTERVAL 24 HOUR")->fetchColumn();
        $directives24h = (int)$pdo->query("SELECT COUNT(*) FROM executive_directives WHERE created_at >= NOW() - INTERVAL 24 HOUR")->fetchColumn();

        return [
            'new_complaints_24h' => $new24h,
            'resolved_24h' => $resolved24h,
            'overdue_breaches_24h' => $overdue24h,
            'reopened_24h' => $reopened24h,
            'directives_issued_24h' => $directives24h,
        ];
    }

    /**
     * Generates a context-specific, scoped dashboard dataset for any authenticated user role.
     */
    public function getRoleDashboard(User $user): array
    {
        $roles = $user->getRoleSlugs();
        $primaryRole = $roles[0] ?? 'public_viewer';

        if (in_array('mayor', $roles, true) || in_array('administrator', $roles, true) || in_array('ceo', $roles, true)) {
            return [
                'role' => $primaryRole,
                'view_type' => 'executive_command_center',
                'kpis' => $this->getExecutiveKpis(),
                'daily_brief' => $this->getDailyBrief(),
            ];
        }

        if (in_array('supervisor', $roles, true)) {
            return [
                'role' => 'supervisor',
                'view_type' => 'supervisor_workforce',
                'kpis' => $this->getExecutiveKpis(),
            ];
        }

        if (in_array('field_worker', $roles, true) || in_array('team_leader', $roles, true)) {
            return [
                'role' => $primaryRole,
                'view_type' => 'field_worker_tasks',
            ];
        }

        if (in_array('platform_super_admin', $roles, true)) {
            return [
                'role' => 'platform_super_admin',
                'view_type' => 'platform_admin_overview',
                'kpis' => $this->getExecutiveKpis(),
            ];
        }

        if (in_array('technical_super_admin', $roles, true)) {
            return [
                'role' => 'technical_super_admin',
                'view_type' => 'system_health_overview',
            ];
        }

        // Default citizen or generic staff
        return [
            'role' => $primaryRole,
            'view_type' => 'citizen_portal',
        ];
    }
}
