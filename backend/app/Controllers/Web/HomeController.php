<?php

declare(strict_types=1);

namespace AmarMayor\Controllers\Web;

use AmarMayor\Domain\PublicAccountability\PublicAccountabilityService;
use AmarMayor\Http\Request;
use AmarMayor\Http\Response;
use AmarMayor\Support\Translator;
use AmarMayor\View\View;

class HomeController
{
    private PublicAccountabilityService $publicService;

    public function __construct(?PublicAccountabilityService $publicService = null)
    {
        $this->publicService = $publicService ?: new PublicAccountabilityService();
    }

    public function index(Request $request): Response
    {
        $metrics = $this->publicService->getPublicMetrics();

        return view('home', [
            'locale' => Translator::getLocale(),
            'metrics' => $metrics,
        ]);
    }

    public function htmxStatusCheck(Request $request): Response
    {
        $metrics = $this->publicService->getPublicMetrics();

        // Render partial HTML snippet for HTMX DOM swap
        $html = View::partial('partials/public_metrics_snapshot', [
            'metrics' => $metrics,
            'locale' => Translator::getLocale(),
        ]);

        return Response::html($html);
    }
}
