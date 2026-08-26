<?php

declare(strict_types=1);

namespace AmarMayor\Domain\ComplaintConfig;

use AmarMayor\Database\DatabaseManager;
use DateTimeImmutable;
use DateTimeInterface;
use PDO;

class DeadlineConfigService
{
    /**
     * Resolves the configured SLA expected hours for a complaint from active municipal rules.
     * Returns null if no rule is configured (does not invent official MCC policy).
     */
    public function getExpectedHours(int $categoryId, ?int $subcategoryId, string $priority, string $classification): ?int
    {
        $pdo = DatabaseManager::getConnection();

        // 1. Subcategory + Priority + Classification
        if ($subcategoryId !== null) {
            $stmt = $pdo->prepare("
                SELECT expected_hours FROM service_deadline_rules 
                WHERE subcategory_id = ? AND priority = ? AND operational_classification = ? AND is_active = 1
                LIMIT 1
            ");
            $stmt->execute([$subcategoryId, $priority, $classification]);
            $hours = $stmt->fetchColumn();
            if ($hours !== false) {
                return (int)$hours;
            }

            // 2. Subcategory + Priority (any classification)
            $stmt = $pdo->prepare("
                SELECT expected_hours FROM service_deadline_rules 
                WHERE subcategory_id = ? AND priority = ? AND operational_classification IS NULL AND is_active = 1
                LIMIT 1
            ");
            $stmt->execute([$subcategoryId, $priority]);
            $hours = $stmt->fetchColumn();
            if ($hours !== false) {
                return (int)$hours;
            }
        }

        // 3. Category + Priority + Classification
        $stmt = $pdo->prepare("
            SELECT expected_hours FROM service_deadline_rules 
            WHERE category_id = ? AND subcategory_id IS NULL AND priority = ? AND operational_classification = ? AND is_active = 1
            LIMIT 1
        ");
        $stmt->execute([$categoryId, $priority, $classification]);
        $hours = $stmt->fetchColumn();
        if ($hours !== false) {
            return (int)$hours;
        }

        // 4. Category + Priority
        $stmt = $pdo->prepare("
            SELECT expected_hours FROM service_deadline_rules 
            WHERE category_id = ? AND subcategory_id IS NULL AND priority = ? AND operational_classification IS NULL AND is_active = 1
            LIMIT 1
        ");
        $stmt->execute([$categoryId, $priority]);
        $hours = $stmt->fetchColumn();
        if ($hours !== false) {
            return (int)$hours;
        }

        // Return null if no official municipal rule is configured
        return null;
    }

    /**
     * Calculates the exact deadline timestamp for a complaint based on active configured rules.
     * Returns null if no deadline rule exists for the complaint type.
     */
    public function calculateDeadline(
        int $categoryId,
        ?int $subcategoryId,
        string $priority,
        string $classification,
        ?DateTimeInterface $submissionTime = null
    ): ?DateTimeImmutable {
        $hours = $this->getExpectedHours($categoryId, $subcategoryId, $priority, $classification);
        if ($hours === null || $hours <= 0) {
            return null;
        }

        $base = $submissionTime ? DateTimeImmutable::createFromInterface($submissionTime) : now_dhaka();

        return $base->modify("+{$hours} hours");
    }

    /**
     * Create a specific SLA deadline rule.
     */
    public function createDeadlineRule(array $data): int
    {
        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO service_deadline_rules (
                category_id, subcategory_id, priority, operational_classification, expected_hours, is_active, created_at
            ) VALUES (?, ?, ?, ?, ?, 1, NOW())
        ");
        $stmt->execute([
            $data['category_id'] ?? null,
            $data['subcategory_id'] ?? null,
            $data['priority'] ?? null,
            $data['operational_classification'] ?? null,
            $data['expected_hours'],
        ]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Get all active deadline rules.
     */
    public function getDeadlineRules(?int $categoryId = null): array
    {
        $pdo = DatabaseManager::getConnection();
        $query = "
            SELECT sdr.*, cc.name_bn as category_name_bn, cc.name_en as category_name_en,
                   cs.name_bn as subcategory_name_bn, cs.name_en as subcategory_name_en
            FROM service_deadline_rules sdr
            LEFT JOIN complaint_categories cc ON cc.id = sdr.category_id
            LEFT JOIN complaint_subcategories cs ON cs.id = sdr.subcategory_id
            WHERE sdr.is_active = 1
        ";
        $params = [];
        if ($categoryId !== null) {
            $query .= " AND sdr.category_id = ?";
            $params[] = $categoryId;
        }

        $query .= " ORDER BY sdr.id ASC";

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
