<?php

declare(strict_types=1);

namespace AmarMayor\Domain\Intelligence;

use AmarMayor\Database\DatabaseManager;
use PDO;

class OperationalIntelligenceService
{
    /**
     * Detects recurring complaint patterns in a specific ward and subcategory.
     */
    public function detectRecurringComplaints(int $daysWindow = 30, int $threshold = 3): array
    {
        $pdo = DatabaseManager::getConnection();

        $stmt = $pdo->prepare("
            SELECT 
                c.ward_id, w.ward_number, w.name_bn as ward_name_bn,
                c.subcategory_id, cs.name_bn as subcategory_name_bn,
                COUNT(*) as complaint_count,
                MAX(c.submitted_at) as latest_complaint_at
            FROM complaints c
            INNER JOIN wards w ON w.id = c.ward_id
            INNER JOIN complaint_subcategories cs ON cs.id = c.subcategory_id
            WHERE c.submitted_at >= NOW() - INTERVAL ? DAY
            GROUP BY c.ward_id, c.subcategory_id, w.ward_number, w.name_bn, cs.name_bn
            HAVING COUNT(*) >= ?
            ORDER BY complaint_count DESC
        ");
        $stmt->execute([$daysWindow, $threshold]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Identifies geographic and category hotspots across the city.
     */
    public function getHotspots(int $limit = 10): array
    {
        $pdo = DatabaseManager::getConnection();

        $stmt = $pdo->prepare("
            SELECT 
                w.ward_number, w.name_bn as ward_name_bn,
                z.zone_number, z.name_bn as zone_name_bn,
                cc.name_bn as category_name_bn,
                COUNT(*) as total_cases,
                SUM(CASE WHEN c.reopen_count > 0 THEN 1 ELSE 0 END) as reopen_cases,
                SUM(CASE WHEN c.deadline_at < NOW() AND c.closed_at IS NULL THEN 1 ELSE 0 END) as overdue_cases
            FROM complaints c
            INNER JOIN wards w ON w.id = c.ward_id
            INNER JOIN zones z ON z.id = c.zone_id
            INNER JOIN complaint_categories cc ON cc.id = c.category_id
            WHERE c.submitted_at >= NOW() - INTERVAL 60 DAY
            GROUP BY w.id, w.ward_number, w.name_bn, z.zone_number, z.name_bn, cc.id, cc.name_bn
            ORDER BY total_cases DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Evaluates whether a location/subcategory exhibits chronic issues indicating a full municipal project is required.
     */
    public function evaluateProjectRequiredIndicators(int $wardId, int $subcategoryId): array
    {
        $pdo = DatabaseManager::getConnection();

        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_attempts,
                SUM(reopen_count) as total_reopens,
                COUNT(DISTINCT id) as total_complaints
            FROM complaints
            WHERE ward_id = ? AND subcategory_id = ? AND submitted_at >= NOW() - INTERVAL 90 DAY
        ");
        $stmt->execute([$wardId, $subcategoryId]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $totalReopens = (int)($stats['total_reopens'] ?? 0);
        $totalComplaints = (int)($stats['total_complaints'] ?? 0);

        $recommendProject = ($totalReopens >= 4 || $totalComplaints >= 8);

        return [
            'ward_id' => $wardId,
            'subcategory_id' => $subcategoryId,
            'total_complaints_90d' => $totalComplaints,
            'total_reopens_90d' => $totalReopens,
            'recommend_project_required' => $recommendProject,
            'rationale_bn' => $recommendProject 
                ? 'বারবার মেরামত সত্ত্বেও সমস্যাটি পুনরাবৃত্তি হচ্ছে। দীর্ঘমেয়াদী প্রকৌশল বা উন্নয়ন প্রকল্প গ্রহণ করা প্রয়োজন।' 
                : 'স্বাভাবিক রক্ষণাবেক্ষণ কাজের আওতায় রয়েছে।',
        ];
    }
}
