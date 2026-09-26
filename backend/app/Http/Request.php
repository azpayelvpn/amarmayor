<?php

declare(strict_types=1);

namespace AmarMayor\Http;

use AmarMayor\Support\Security;

/**
 * Lightweight Explicit HTTP Request Abstraction.
 */
class Request
{
    private string $method;
    private string $uri;
    private array $query;
    private array $post;
    private array $json = [];
    private array $headers;
    private array $cookies;
    private array $files;
    private array $server;
    private string $requestId;
    private array $attributes = [];

    public function __construct(
        string $method,
        string $uri,
        array $query = [],
        array $post = [],
        array $headers = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        ?string $rawBody = null,
        ?string $requestId = null
    ) {
        $this->method = strtoupper($method);
        if (empty($query) && str_contains($uri, '?')) {
            $queryString = parse_url($uri, PHP_URL_QUERY);
            if ($queryString) {
                parse_str($queryString, $parsedQuery);
                $query = is_array($parsedQuery) ? $parsedQuery : [];
            }
        }
        $this->uri = $this->sanitizeUri($uri);
        $this->query = $query;
        $this->post = $post;
        $this->headers = $this->normalizeHeaders($headers);
        $this->cookies = $cookies;
        $this->files = $files;
        $this->server = $server;

        if ($rawBody !== null && $this->isJson()) {
            $decoded = json_decode($rawBody, true);
            if (is_array($decoded)) {
                $this->json = $decoded;
            }
        }

        $this->requestId = $requestId ?: ($this->header('x-request-id') ?: Security::uuid());
    }

    public static function capture(): self
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $headers = function_exists('getallheaders') ? (getallheaders() ?: []) : [];
        $rawBody = file_get_contents('php://input');

        return new self(
            $method,
            $uri,
            $_GET,
            $_POST,
            $headers,
            $_COOKIE,
            $_FILES,
            $_SERVER,
            $rawBody !== false ? $rawBody : null
        );
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getPath(): string
    {
        $path = parse_url($this->uri, PHP_URL_PATH);
        return '/' . trim((string)$path, '/');
    }

    public function getRequestId(): string
    {
        return $this->requestId;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $this->json)) {
            return $this->json[$key];
        }
        if (array_key_exists($key, $this->post)) {
            return $this->post[$key];
        }
        if (array_key_exists($key, $this->query)) {
            return $this->query[$key];
        }
        return $default;
    }

    public function all(): array
    {
        return array_merge($this->query, $this->post, $this->json);
    }

    public function getJsonBody(): array
    {
        return !empty($this->json) ? $this->json : array_merge($this->post, $this->query);
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    public function header(string $name, mixed $default = null): mixed
    {
        $normalized = strtolower(str_replace('_', '-', $name));
        return $this->headers[$normalized] ?? $default;
    }

    public function files(): array
    {
        return $this->files;
    }

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function isJson(): bool
    {
        $contentType = (string)$this->header('content-type', '');
        return str_contains(strtolower($contentType), 'application/json');
    }

    public function isHtmx(): bool
    {
        return strtolower((string)$this->header('hx-request', '')) === 'true';
    }

    public function getIp(): string
    {
        return (string)($this->server['REMOTE_ADDR'] ?? '127.0.0.1');
    }

    public function ip(): string
    {
        return $this->getIp();
    }

    public function getClientIp(): string
    {
        return $this->getIp();
    }

    public function getHeader(string $name, mixed $default = null): mixed
    {
        return $this->header($name, $default);
    }

    public function server(string $key, mixed $default = null): mixed
    {
        if (isset($this->server[$key])) {
            return $this->server[$key];
        }
        if (str_starts_with($key, 'HTTP_')) {
            $headerKey = strtolower(str_replace('_', '-', substr($key, 5)));
            return $this->header($headerKey, $default);
        }
        return $default;
    }

    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function getAttribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    private function sanitizeUri(string $uri): string
    {
        $parsed = parse_url($uri, PHP_URL_PATH);
        return $parsed !== false ? $parsed : '/';
    }

    private function normalizeHeaders(array $headers): array
    {
        $normalized = [];
        foreach ($headers as $key => $value) {
            $normalized[strtolower(str_replace('_', '-', (string)$key))] = $value;
        }
        return $normalized;
    }
}
