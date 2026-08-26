<?php

declare(strict_types=1);

namespace AmarMayor\Http\Controllers;

use AmarMayor\Domain\Background\BackgroundJobService;
use AmarMayor\Domain\PlatformAdmin\PlatformAdminService;
use AmarMayor\Domain\TechnicalAdmin\SystemHealthService;
use AmarMayor\Http\Request;
use AmarMayor\Http\Response;

class AdminOpsController
{
    private PlatformAdminService $platformService;
    private SystemHealthService $systemHealthService;
    private BackgroundJobService $jobService;

    public function __construct(
        ?PlatformAdminService $platformService = null,
        ?SystemHealthService $systemHealthService = null,
        ?BackgroundJobService $jobService = null
    ) {
        $this->platformService = $platformService ?: new PlatformAdminService();
        $this->systemHealthService = $systemHealthService ?: new SystemHealthService();
        $this->jobService = $jobService ?: new BackgroundJobService();
    }

    public function getPlatformOverview(Request $request): Response
    {
        $overview = $this->platformService->getAdminOverview();
        return Response::json($overview);
    }

    public function getSystemHealth(Request $request): Response
    {
        $includeDetails = (bool)$request->query('details', false);
        $health = $this->systemHealthService->getSystemHealth($includeDetails);
        return Response::json($health);
    }

    public function runScheduler(Request $request): Response
    {
        $result = $this->jobService->runScheduledMaintenance();
        return Response::json($result);
    }
}
