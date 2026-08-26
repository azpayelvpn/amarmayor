<?php

declare(strict_types=1);

use AmarMayor\Database\DatabaseManager;
use AmarMayor\Http\Request;
use AmarMayor\Http\Response;
use AmarMayor\Http\Router;
use AmarMayor\Middleware\CorsMiddleware;
use AmarMayor\Middleware\CsrfMiddleware;
use AmarMayor\Middleware\LocaleMiddleware;
use AmarMayor\Middleware\RequestIdMiddleware;
use AmarMayor\Middleware\SessionMiddleware;
use AmarMayor\Support\Config;
use AmarMayor\Support\Container;
use AmarMayor\Support\Env;
use AmarMayor\Support\ErrorHandler;
use AmarMayor\Support\Logger;
use AmarMayor\Support\RedisClient;
use AmarMayor\Support\Translator;
use AmarMayor\View\View;

$baseDir = dirname(__DIR__);

// 1. Load Composer Autoloader if present
if (file_exists($baseDir . '/vendor/autoload.php')) {
    require_once $baseDir . '/vendor/autoload.php';
}

// Ensure explicit PSR-4 mapping for AmarMayor and AmarMayor\Tests
spl_autoload_register(function (string $class) use ($baseDir) {
    $prefix = 'AmarMayor\\';
    if (str_starts_with($class, $prefix)) {
        $relativeClass = substr($class, strlen($prefix));
        if (str_starts_with($relativeClass, 'Tests\\')) {
            $testRelative = substr($relativeClass, strlen('Tests\\'));
            $file = $baseDir . '/tests/' . str_replace('\\', '/', $testRelative) . '.php';
        } else {
            $file = $baseDir . '/app/' . str_replace('\\', '/', $relativeClass) . '.php';
        }
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

require_once $baseDir . '/app/Support/helpers.php';

// 2. Load Environment Variables (.env)
Env::load($baseDir . '/.env');

// 3. Initialize Paths & Centralized Subsystems
Config::setConfigPath($baseDir . '/config');
Translator::setLangPath($baseDir . '/lang');
Translator::setLocale((string)Config::get('app.locale', 'bn'));
View::setViewsPath($baseDir . '/views');
Logger::setLogPath((string)Config::get('logging.path', $baseDir . '/storage/logs/app.log'));

// 4. Centralized Error Handling
ErrorHandler::register();

// 5. Dependency Container Initialization
$container = Container::getInstance();
$container->singleton(Router::class, fn () => new Router());
$container->singleton(DatabaseManager::class, fn () => new DatabaseManager());
$container->singleton(RedisClient::class, fn () => new RedisClient());

// 6. Router Setup & Route Definitions
/** @var Router $router */
$router = $container->get(Router::class);

// Web Routes Group (with Session, CSRF, Locale, RequestID)
$router->group([
    'prefix' => '',
    'middleware' => [
        RequestIdMiddleware::class,
        SessionMiddleware::class,
        LocaleMiddleware::class,
        CsrfMiddleware::class,
    ]
], function (Router $r) use ($baseDir) {
    require $baseDir . '/routes/web.php';
});

// API v1 Routes Group (with CORS, RequestID, Locale)
$router->group([
    'prefix' => 'api/v1',
    'middleware' => [
        RequestIdMiddleware::class,
        CorsMiddleware::class,
        LocaleMiddleware::class,
    ]
], function (Router $r) use ($baseDir) {
    require $baseDir . '/routes/api.php';
});

return [
    'router' => $router,
    'container' => $container,
    'handle' => function (Request $request) use ($router): Response {
        return $router->dispatch($request);
    }
];
