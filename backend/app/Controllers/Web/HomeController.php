<?php

declare(strict_types=1);

namespace AmarMayor\Controllers\Web;

use AmarMayor\Database\DatabaseManager;
use AmarMayor\Http\Request;
use AmarMayor\Http\Response;
use AmarMayor\Support\RedisClient;
use AmarMayor\Support\Translator;
use AmarMayor\View\View;

/**
 * Foundation Web Landing & HTMX Verification Controller.
 */
class HomeController
{
    public function index(Request $request): Response
    {
        $dbHealth = DatabaseManager::checkHealth();
        $redisHealth = RedisClient::checkHealth();

        return view('home', [
            'locale' => Translator::getLocale(),
            'dbHealth' => $dbHealth,
            'redisHealth' => $redisHealth,
            'requestId' => $request->getRequestId(),
        ]);
    }

    public function htmxStatusCheck(Request $request): Response
    {
        $dbHealth = DatabaseManager::checkHealth();
        $redisHealth = RedisClient::checkHealth();

        // Render partial HTML snippet for HTMX DOM swap
        $html = View::partial('partials/status_card', [
            'dbHealth' => $dbHealth,
            'redisHealth' => $redisHealth,
            'timestamp' => date('H:i:s'),
            'requestId' => $request->getRequestId(),
        ]);

        return Response::html($html);
    }
}
