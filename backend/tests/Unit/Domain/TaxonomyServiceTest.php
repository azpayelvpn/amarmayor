<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Unit\Domain;

use AmarMayor\Database\DatabaseManager;
use AmarMayor\Domain\ComplaintConfig\TaxonomyService;
use AmarMayor\Tests\TestCase;
use PDO;

class TaxonomyServiceTest extends TestCase
{
    private TaxonomyService $taxonomyService;

    public function setUp(): void
    {
        parent::setUp();
        $this->taxonomyService = new TaxonomyService();
    }

    public function testGetCategoriesWithSubcategories(): void
    {
        $categories = $this->taxonomyService->getCategories(true);
        $this->assert(count($categories) >= 10, "Should have seeded complaint categories");

        $cleanlinessCat = null;
        foreach ($categories as $cat) {
            if ($cat['slug'] === 'cleanliness') {
                $cleanlinessCat = $cat;
                break;
            }
        }

        $this->assertNotNull($cleanlinessCat);
        $this->assert(count($cleanlinessCat['subcategories']) >= 4);

        $dustbinSub = null;
        foreach ($cleanlinessCat['subcategories'] as $sub) {
            if ($sub['slug'] === 'dustbin_overflow') {
                $dustbinSub = $sub;
                break;
            }
        }

        $this->assertNotNull($dustbinSub);
        $this->assertEquals('p3_normal', $dustbinSub['default_priority']);
        $this->assertEquals('quick_action', $dustbinSub['default_classification']);
    }

    public function testGetSubcategoryDetails(): void
    {
        $pdo = DatabaseManager::getConnection();
        $subId = (int)$pdo->query("SELECT id FROM complaint_subcategories WHERE slug = 'dead_animal'")->fetchColumn();

        $sub = $this->taxonomyService->getSubcategory($subId);
        $this->assertNotNull($sub);
        $this->assertEquals('dead_animal', $sub['slug']);
        $this->assertEquals('cleanliness', $sub['category_slug']);
        $this->assertEquals('p1_critical', $sub['default_priority']);
    }

    public function testGetClassificationsAndPriorities(): void
    {
        $classifications = $this->taxonomyService->getClassifications();
        $this->assert(count($classifications) >= 4);

        $priorities = $this->taxonomyService->getPriorities();
        $this->assertCount(4, $priorities);
    }
}
