<?php

declare(strict_types=1);

namespace AmarMayor\Http\Controllers;

use AmarMayor\Domain\ComplaintConfig\DeadlineConfigService;
use AmarMayor\Domain\ComplaintConfig\RoutingConfigService;
use AmarMayor\Domain\ComplaintConfig\TaxonomyService;
use AmarMayor\Http\Request;
use AmarMayor\Http\Response;

class ComplaintConfigController
{
    private TaxonomyService $taxonomyService;
    private RoutingConfigService $routingService;
    private DeadlineConfigService $deadlineService;

    public function __construct(
        ?TaxonomyService $taxonomyService = null,
        ?RoutingConfigService $routingService = null,
        ?DeadlineConfigService $deadlineService = null
    ) {
        $this->taxonomyService = $taxonomyService ?: new TaxonomyService();
        $this->routingService = $routingService ?: new RoutingConfigService();
        $this->deadlineService = $deadlineService ?: new DeadlineConfigService();
    }

    public function getCategories(Request $request): Response
    {
        $includeSub = $request->query('include_subcategories', 'true') !== 'false';
        $categories = $this->taxonomyService->getCategories($includeSub);
        return Response::json($categories);
    }

    public function getSubcategory(Request $request, string|int $id = null): Response
    {
        $subId = (int)($id ?: $request->getAttribute('id', 0));
        $sub = $this->taxonomyService->getSubcategory($subId);

        if (!$sub) {
            return Response::error('NOT_FOUND', 'Subcategory not found', 404);
        }

        return Response::json($sub);
    }

    public function getRoutingGaps(Request $request): Response
    {
        $gaps = $this->routingService->detectRoutingGaps();
        return Response::json([
            'total_gaps' => count($gaps),
            'gaps' => $gaps,
        ]);
    }

    public function getDeadlines(Request $request): Response
    {
        $catId = $request->query('category_id') ? (int)$request->query('category_id') : null;
        $rules = $this->deadlineService->getDeadlineRules($catId);
        return Response::json($rules);
    }
}
