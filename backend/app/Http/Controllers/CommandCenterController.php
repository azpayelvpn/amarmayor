<?php

declare(strict_types=1);

namespace AmarMayor\Http\Controllers;

use AmarMayor\Auth\Auth;
use AmarMayor\Domain\CommandCenter\CommandCenterService;
use AmarMayor\Http\Request;
use AmarMayor\Http\Response;

class CommandCenterController
{
    private CommandCenterService $commandCenterService;

    public function __construct(?CommandCenterService $commandCenterService = null)
    {
        $this->commandCenterService = $commandCenterService ?: new CommandCenterService();
    }

    public function getKpis(Request $request): Response
    {
        $wardId = $request->query('ward_id') !== null ? (int)$request->query('ward_id') : null;
        $zoneId = $request->query('zone_id') !== null ? (int)$request->query('zone_id') : null;
        $deptId = $request->query('dept_id') !== null ? (int)$request->query('dept_id') : null;

        $kpis = $this->commandCenterService->getExecutiveKpis($wardId, $zoneId, $deptId);
        return Response::json($kpis);
    }

    public function getDailyBrief(Request $request): Response
    {
        $brief = $this->commandCenterService->getDailyBrief();
        return Response::json($brief);
    }

    public function getDashboard(Request $request): Response
    {
        $user = Auth::user();
        if (!$user) {
            return Response::error('UNAUTHORIZED', 'Authentication required', 401);
        }

        $dashboard = $this->commandCenterService->getRoleDashboard($user);
        return Response::json($dashboard);
    }
}
