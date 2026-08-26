<?php

declare(strict_types=1);

namespace AmarMayor\Domain\ComplaintConfig;

use AmarMayor\Database\DatabaseManager;
use InvalidArgumentException;
use PDO;

class RoutingConfigService
{
    /**
     * Resolves the deterministic routing rule for a given category, subcategory, and ward.
     * Tiered Resolution Hierarchy:
     *   1. Subcategory + Ward
     *   2. Category + Ward
     *   3. Subcategory Default (ward_id IS NULL)
     *   4. Category Default (subcategory_id IS NULL, ward_id IS NULL)
     */
    public function resolveRoutingRule(int $categoryId, ?int $subcategoryId = null, ?int $wardId = null): ?array
    {
        $pdo = DatabaseManager::getConnection();

        // Tier 1: Specific Subcategory + Ward
        if ($subcategoryId !== null && $wardId !== null) {
            $stmt = $pdo->prepare("
                SELECT * FROM routing_rules 
                WHERE category_id = ? AND subcategory_id = ? AND ward_id = ? 
                  AND is_active = 1 
                  AND (effective_to IS NULL OR effective_to >= NOW())
                  AND effective_from <= NOW()
                LIMIT 1
            ");
            $stmt->execute([$categoryId, $subcategoryId, $wardId]);
            $rule = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($rule) {
                return $rule;
            }
        }

        // Tier 2: Category + Ward
        if ($wardId !== null) {
            $stmt = $pdo->prepare("
                SELECT * FROM routing_rules 
                WHERE category_id = ? AND subcategory_id IS NULL AND ward_id = ? 
                  AND is_active = 1 
                  AND (effective_to IS NULL OR effective_to >= NOW())
                  AND effective_from <= NOW()
                LIMIT 1
            ");
            $stmt->execute([$categoryId, $wardId]);
            $rule = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($rule) {
                return $rule;
            }
        }

        // Tier 3: Subcategory Default
        if ($subcategoryId !== null) {
            $stmt = $pdo->prepare("
                SELECT * FROM routing_rules 
                WHERE category_id = ? AND subcategory_id = ? AND ward_id IS NULL 
                  AND is_active = 1 
                  AND (effective_to IS NULL OR effective_to >= NOW())
                  AND effective_from <= NOW()
                LIMIT 1
            ");
            $stmt->execute([$categoryId, $subcategoryId]);
            $rule = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($rule) {
                return $rule;
            }
        }

        // Tier 4: Category Default
        $stmt = $pdo->prepare("
            SELECT * FROM routing_rules 
            WHERE category_id = ? AND subcategory_id IS NULL AND ward_id IS NULL 
              AND is_active = 1 
              AND (effective_to IS NULL OR effective_to >= NOW())
              AND effective_from <= NOW()
            LIMIT 1
        ");
        $stmt->execute([$categoryId]);
        $rule = $stmt->fetch(PDO::FETCH_ASSOC);

        return $rule ?: null;
    }

    /**
     * Create a routing rule configuration.
     */
    public function createRoutingRule(array $data): int
    {
        $pdo = DatabaseManager::getConnection();

        $stmt = $pdo->prepare("
            INSERT INTO routing_rules (
                category_id, subcategory_id, ward_id, department_id, service_unit_id,
                assigned_supervisor_employee_id, assigned_team_id, effective_from, effective_to, is_active, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
        ");
        $stmt->execute([
            $data['category_id'],
            $data['subcategory_id'] ?? null,
            $data['ward_id'] ?? null,
            $data['department_id'],
            $data['service_unit_id'] ?? null,
            $data['assigned_supervisor_employee_id'] ?? null,
            $data['assigned_team_id'] ?? null,
            $data['effective_from'] ?? date('Y-m-d H:i:s'),
            $data['effective_to'] ?? null,
        ]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Detects coverage gaps where categories/subcategories in specific wards lack a designated department or team.
     */
    public function detectRoutingGaps(): array
    {
        $pdo = DatabaseManager::getConnection();

        $categories = $pdo->query("SELECT id, slug, name_bn, name_en FROM complaint_categories WHERE is_active = 1")->fetchAll(PDO::FETCH_ASSOC);
        $wards = $pdo->query("SELECT id, ward_number, name_bn, name_en FROM wards WHERE status = 'active'")->fetchAll(PDO::FETCH_ASSOC);

        $gaps = [];

        foreach ($categories as $cat) {
            foreach ($wards as $ward) {
                $rule = $this->resolveRoutingRule((int)$cat['id'], null, (int)$ward['id']);
                if (!$rule) {
                    $gaps[] = [
                        'category_id' => $cat['id'],
                        'category_name_bn' => $cat['name_bn'],
                        'ward_id' => $ward['id'],
                        'ward_number' => $ward['ward_number'],
                        'gap_type' => 'missing_routing_rule',
                    ];
                }
            }
        }

        return $gaps;
    }
}
