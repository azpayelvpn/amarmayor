<?php

declare(strict_types=1);

namespace AmarMayor\Http;

use AmarMayor\Support\Translator;
use AmarMayor\View\View;
use Closure;
use RuntimeException;

/**
 * Lightweight Explicit REST & Web Router.
 */
class Router
{
    private array $routes = [];
    private array $groupStack = [];

    public function get(string $path, Closure|array|string $handler, array $middleware = []): self
    {
        return $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, Closure|array|string $handler, array $middleware = []): self
    {
        return $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function put(string $path, Closure|array|string $handler, array $middleware = []): self
    {
        return $this->addRoute('PUT', $path, $handler, $middleware);
    }

    public function patch(string $path, Closure|array|string $handler, array $middleware = []): self
    {
        return $this->addRoute('PATCH', $path, $handler, $middleware);
    }

    public function delete(string $path, Closure|array|string $handler, array $middleware = []): self
    {
        return $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    public function group(array $attributes, callable $callback): void
    {
        $this->groupStack[] = $attributes;
        $callback($this);
        array_pop($this->groupStack);
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->getMethod();
        $path = $request->getPath();

        $allowedMethods = [];
        $matchedRoute = null;
        $matchedParams = [];

        foreach ($this->routes as $route) {
            $pattern = $this->compilePattern($route['path']);
            if (preg_match($pattern, $path, $matches)) {
                $allowedMethods[] = $route['method'];
                if ($route['method'] === $method) {
                    $matchedRoute = $route;
                    // Extract named parameters
                    foreach ($matches as $key => $val) {
                        if (is_string($key)) {
                            $matchedParams[$key] = $val;
                        }
                    }
                    break;
                }
            }
        }

        if ($matchedRoute !== null) {
            return $this->runPipeline($request, $matchedRoute, $matchedParams);
        }

        if (!empty($allowedMethods)) {
            if ($request->isJson() || str_starts_with($path, '/api/')) {
                return Response::error('METHOD_NOT_ALLOWED', 'Method not allowed for this route.', 405, [
                    'allowed_methods' => array_unique($allowedMethods)
                ]);
            }
            return Response::html(View::render('errors/404', ['locale' => Translator::getLocale()]), 405);
        }

        if ($request->isJson() || str_starts_with($path, '/api/')) {
            return Response::error('ROUTE_NOT_FOUND', 'Requested endpoint not found.', 404);
        }

        return Response::html(View::render('errors/404', ['locale' => Translator::getLocale()]), 404);
    }

    private function addRoute(string $method, string $path, Closure|array|string $handler, array $middleware): self
    {
        $prefix = '';
        $groupMiddleware = [];

        foreach ($this->groupStack as $group) {
            if (isset($group['prefix'])) {
                $prefix .= '/' . trim($group['prefix'], '/');
            }
            if (isset($group['middleware'])) {
                $groupMiddleware = array_merge($groupMiddleware, (array)$group['middleware']);
            }
        }

        $fullPath = '/' . trim($prefix . '/' . trim($path, '/'), '/');
        if ($fullPath === '') {
            $fullPath = '/';
        }

        $this->routes[] = [
            'method' => $method,
            'path' => $fullPath,
            'handler' => $handler,
            'middleware' => array_merge($groupMiddleware, $middleware),
        ];

        return $this;
    }

    private function compilePattern(string $path): string
    {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#u';
    }

    private function runPipeline(Request $request, array $route, array $params): Response
    {
        $middlewareList = $route['middleware'];
        $handler = $route['handler'];

        $pipeline = function (Request $req) use ($handler, $params): Response {
            return $this->executeHandler($handler, $req, $params);
        };

        while ($middlewareClass = array_pop($middlewareList)) {
            $next = $pipeline;
            $pipeline = function (Request $req) use ($middlewareClass, $next): Response {
                $middleware = is_string($middlewareClass) ? new $middlewareClass() : $middlewareClass;
                return $middleware->handle($req, $next);
            };
        }

        foreach ($params as $k => $v) {
            $request->setAttribute($k, $v);
        }

        return $pipeline($request);
    }

    private function executeHandler(Closure|array|string $handler, Request $request, array $params): Response
    {
        if ($handler instanceof Closure) {
            $result = $handler($request, ...array_values($params));
        } elseif (is_array($handler)) {
            [$class, $method] = $handler;
            $controller = is_string($class) ? new $class() : $class;
            $result = $controller->$method($request, ...array_values($params));
        } elseif (is_string($handler) && str_contains($handler, '@')) {
            [$class, $method] = explode('@', $handler, 2);
            $controller = new $class();
            $result = $controller->$method($request, ...array_values($params));
        } else {
            throw new RuntimeException("Invalid route handler format.");
        }

        if ($result instanceof Response) {
            return $result;
        }

        if (is_array($result)) {
            return Response::json($result);
        }

        if (is_string($result)) {
            return Response::html($result);
        }

        return Response::noContent();
    }
}
