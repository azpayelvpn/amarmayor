<?php

declare(strict_types=1);

namespace AmarMayor\Http\Controllers;

use AmarMayor\Domain\PublicAccountability\PublicAccountabilityService;
use AmarMayor\Http\Request;
use AmarMayor\Http\Response;

class PublicAccountabilityController
{
    private PublicAccountabilityService $publicService;

    public function __construct(?PublicAccountabilityService $publicService = null)
    {
        $this->publicService = $publicService ?: new PublicAccountabilityService();
    }

    public function getMetrics(Request $request): Response
    {
        $metrics = $this->publicService->getPublicMetrics();
        return Response::json($metrics);
    }

    public function getWhoIsResponsible(Request $request): Response
    {
        $wardId = $request->query('ward_id') !== null ? (int)$request->query('ward_id') : null;
        $directory = $this->publicService->getWhoIsResponsible($wardId);
        return Response::json($directory);
    }

    public function getNotices(Request $request): Response
    {
        $category = $request->query('category');
        $notices = $this->publicService->getPublicNotices($category);
        return Response::json($notices);
    }
}
