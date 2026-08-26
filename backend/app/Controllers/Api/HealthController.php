<?php

declare(strict_types=1);

namespace AmarMayor\Controllers\Api;

use AmarMayor\Database\DatabaseManager;
use AmarMayor\Http\Request;
use AmarMayor\Http\Response;
use AmarMayor\Support\Config;
use AmarMayor\Support\RedisClient;
use AmarMayor\Support\Translator;

/**
 * Health & Infrastructure Status API Controller.
 */
class HealthController
{
    public function check(Request $request): Response
    {
        $dbHealth = DatabaseManager::checkHealth();
        $redisHealth = RedisClient::checkHealth();

        $overallStatus = ($dbHealth['connected']) ? 'healthy' : 'degraded';
        $isDebug = (bool)Config::get('app.debug', false);
        $isProduction = Config::get('app.env') === 'production';

        // Production-safe public response: minimal without internal topology leak
        if ($isProduction || !$isDebug) {
            return Response::json([
                'status' => $overallStatus,
            ]);
        }

        // Local development debug response
        return Response::json([
            'status' => $overallStatus,
            'app' => [
                'name' => Config::get('app.name'),
                'env' => Config::get('app.env'),
                'locale' => Translator::getLocale(),
                'timezone' => Config::get('app.timezone'),
            ],
            'services' => [
                'database' => [
                    'status' => $dbHealth['status'],
                ],
                'cache' => [
                    'status' => $redisHealth['status'],
                ],
            ],
        ]);
    }
}
