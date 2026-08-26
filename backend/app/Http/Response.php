<?php

declare(strict_types=1);

namespace AmarMayor\Http;

/**
 * Lightweight Explicit HTTP Response Abstraction.
 * Enforces standard API envelopes for all JSON responses.
 */
class Response
{
    private int $statusCode;
    private array $headers;
    private string $content;
    private static string $globalRequestId = '';

    public function __construct(string $content = '', int $statusCode = 200, array $headers = [])
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    public static function setGlobalRequestId(string $requestId): void
    {
        self::$globalRequestId = $requestId;
    }

    public static function html(string $html, int $statusCode = 200, array $headers = []): self
    {
        $headers['Content-Type'] = 'text/html; charset=UTF-8';
        return new self($html, $statusCode, $headers);
    }

    public static function json(array $data = [], int $statusCode = 200, array $headers = [], array $meta = []): self
    {
        $headers['Content-Type'] = 'application/json; charset=UTF-8';

        $payload = [
            'success' => true,
            'data' => $data,
            'meta' => array_merge([
                'request_id' => self::$globalRequestId ?: 'sys-anon',
                'timestamp' => date('Y-m-d\TH:i:sP'),
            ], $meta),
        ];

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return new self($json !== false ? $json : '{}', $statusCode, $headers);
    }

    public static function error(
        string $code,
        string $message,
        int $statusCode = 400,
        array $details = [],
        array $headers = [],
        array $meta = []
    ): self {
        $headers['Content-Type'] = 'application/json; charset=UTF-8';

        $payload = [
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => $details,
            ],
            'meta' => array_merge([
                'request_id' => self::$globalRequestId ?: 'sys-anon',
                'timestamp' => date('Y-m-d\TH:i:sP'),
            ], $meta),
        ];

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return new self($json !== false ? $json : '{}', $statusCode, $headers);
    }

    public static function redirect(string $url, int $statusCode = 302, array $headers = []): self
    {
        $headers['Location'] = $url;
        return new self('', $statusCode, $headers);
    }

    public static function noContent(): self
    {
        return new self('', 204);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getBody(): string
    {
        return $this->content;
    }

    public function getHeader(string $name): ?string
    {
        foreach ($this->headers as $k => $v) {
            if (strcasecmp($k, $name) === 0) {
                return $v;
            }
        }
        return null;
    }

    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function send(): void
    {
        if (!headers_sent()) {
            http_response_code($this->statusCode);
            foreach ($this->headers as $name => $value) {
                header("{$name}: {$value}");
            }
        }

        echo $this->content;
    }
}
