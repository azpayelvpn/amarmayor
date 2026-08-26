<?php

declare(strict_types=1);

namespace AmarMayor\Domain\ComplaintConfig;

use AmarMayor\Database\DatabaseManager;
use InvalidArgumentException;
use PDO;

class TaxonomyService
{
    /**
     * Get all active complaint categories with nested subcategories.
     */
    public function getCategories(bool $includeSubcategories = true): array
    {
        $pdo = DatabaseManager::getConnection();
        $categories = $pdo->query("
            SELECT id, slug, name_bn, name_en, icon_name, display_order, is_active 
            FROM complaint_categories 
            WHERE is_active = 1 
            ORDER BY display_order ASC, id ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        if (!$includeSubcategories) {
            return $categories;
        }

        foreach ($categories as &$cat) {
            $stmt = $pdo->prepare("
                SELECT id, category_id, slug, name_bn, name_en, default_priority, default_classification, requires_live_camera, is_active
                FROM complaint_subcategories 
                WHERE category_id = ? AND is_active = 1 
                ORDER BY id ASC
            ");
            $stmt->execute([$cat['id']]);
            $cat['subcategories'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $categories;
    }

    /**
     * Get single subcategory details including parent category and defaults.
     */
    public function getSubcategory(int $subcategoryId): ?array
    {
        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("
            SELECT cs.*, cc.name_bn as category_name_bn, cc.name_en as category_name_en, cc.slug as category_slug
            FROM complaint_subcategories cs
            INNER JOIN complaint_categories cc ON cc.id = cs.category_id
            WHERE cs.id = ?
            LIMIT 1
        ");
        $stmt->execute([$subcategoryId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * Get all operational classifications.
     */
    public function getClassifications(): array
    {
        $pdo = DatabaseManager::getConnection();
        return $pdo->query("
            SELECT id, slug, name_bn, name_en, description_bn, is_active 
            FROM operational_classifications 
            WHERE is_active = 1 
            ORDER BY id ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all complaint priorities.
     */
    public function getPriorities(): array
    {
        $pdo = DatabaseManager::getConnection();
        return $pdo->query("
            SELECT id, slug, name_bn, name_en, display_order, is_active 
            FROM priorities 
            WHERE is_active = 1 
            ORDER BY display_order ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }
}
