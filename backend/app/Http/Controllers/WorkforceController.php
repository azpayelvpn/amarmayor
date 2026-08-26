<?php

declare(strict_types=1);

namespace AmarMayor\Http\Controllers;

use AmarMayor\Domain\Workforce\WorkforceService;
use AmarMayor\Http\Request;
use AmarMayor\Http\Response;

class WorkforceController
{
    private WorkforceService $workforceService;

    public function __construct(?WorkforceService $workforceService = null)
    {
        $this->workforceService = $workforceService ?: new WorkforceService();
    }

    public function getDepartments(Request $request): Response
    {
        $departments = $this->workforceService->getDepartments(true);
        return Response::json($departments);
    }

    public function getTeams(Request $request): Response
    {
        $departmentId = $request->query('department_id') ? (int)$request->query('department_id') : null;
        $wardId = $request->query('ward_id') ? (int)$request->query('ward_id') : null;

        $teams = $this->workforceService->getTeams($departmentId, $wardId);
        return Response::json($teams);
    }
}
