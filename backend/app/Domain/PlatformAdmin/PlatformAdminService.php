<?php

declare(strict_types=1);

namespace AmarMayor\Domain\PlatformAdmin;

use AmarMayor\Database\DatabaseManager;
use AmarMayor\Domain\ComplaintConfig\RoutingConfigService;
use PDO;

class PlatformAdminService
{
    private RoutingConfigService $routingConfigService;

    public function __construct(?RoutingConfigService $routingConfigService = null)
    {
        $this->routingConfigService = $routingConfigService ?: new RoutingConfigService();
    }

    /**
     * Plain-language summary of Platform Administration modules and pending gap alerts.
     */
    public function getAdminOverview(): array
    {
        $pdo = DatabaseManager::getConnection();

        $totalWards = (int)$pdo->query("SELECT COUNT(*) FROM wards WHERE status = 'active'")->fetchColumn();
        $totalZones = (int)$pdo->query("SELECT COUNT(*) FROM zones WHERE is_active = 1")->fetchColumn();
        $totalEmployees = (int)$pdo->query("SELECT COUNT(*) FROM employees WHERE is_active = 1")->fetchColumn();
        $totalCategories = (int)$pdo->query("SELECT COUNT(*) FROM complaint_categories WHERE is_active = 1")->fetchColumn();
        $routingGaps = $this->routingConfigService->detectRoutingGaps();

        return [
            'total_active_wards' => $totalWards,
            'total_active_zones' => $totalZones,
            'total_active_staff' => $totalEmployees,
            'total_service_categories' => $totalCategories,
            'routing_gap_count' => count($routingGaps),
            'routing_gaps' => $routingGaps,
        ];
    }
}
