<?php

declare(strict_types=1);

namespace AmarMayor\Http\Controllers;

use AmarMayor\Domain\Intelligence\OperationalIntelligenceService;
use AmarMayor\Http\Request;
use AmarMayor\Http\Response;

class IntelligenceController
{
    private OperationalIntelligenceService $intelService;

    public function __construct(?OperationalIntelligenceService $intelService = null)
    {
        $this->intelService = $intelService ?: new OperationalIntelligenceService();
    }

    public function getRecurring(Request $request): Response
    {
        $days = (int)$request->query('days', 30);
        $threshold = (int)$request->query('threshold', 3);
        $recurring = $this->intelService->detectRecurringComplaints($days, $threshold);
        return Response::json($recurring);
    }

    public function getHotspots(Request $request): Response
    {
        $limit = (int)$request->query('limit', 10);
        $hotspots = $this->intelService->getHotspots($limit);
        return Response::json($hotspots);
    }

    public function evaluateProject(Request $request): Response
    {
        $wardId = (int)$request->query('ward_id', 0);
        $subcategoryId = (int)$request->query('subcategory_id', 0);

        if (!$wardId || !$subcategoryId) {
            return Response::error('VALIDATION_ERROR', 'ward_id and subcategory_id are required', 422);
        }

        $result = $this->intelService->evaluateProjectRequiredIndicators($wardId, $subcategoryId);
        return Response::json($result);
    }
}
