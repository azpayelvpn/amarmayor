<?php

declare(strict_types=1);

namespace AmarMayor\Http\Controllers;

use AmarMayor\Domain\Governance\GovernanceService;
use AmarMayor\Http\Request;
use AmarMayor\Http\Response;

class GovernanceController
{
    private GovernanceService $governanceService;

    public function __construct(?GovernanceService $governanceService = null)
    {
        $this->governanceService = $governanceService ?: new GovernanceService();
    }

    public function getLeadership(Request $request): Response
    {
        $scope = (string)$request->query('scope', 'citywide');
        $scopeId = $request->query('scope_id') ? (int)$request->query('scope_id') : null;

        $leadership = $this->governanceService->getActiveRepresentatives($scope, $scopeId);
        return Response::json($leadership);
    }

    public function getWardRepresentatives(Request $request): Response
    {
        $wardId = (int)$request->getAttribute('wardId', 0);
        $reps = $this->governanceService->getActiveRepresentatives('ward', $wardId);
        return Response::json($reps);
    }

    public function getWardHistory(Request $request): Response
    {
        $wardId = (int)$request->getAttribute('wardId', 0);
        $history = $this->governanceService->getRepresentativeHistory($wardId);
        return Response::json($history);
    }
}
