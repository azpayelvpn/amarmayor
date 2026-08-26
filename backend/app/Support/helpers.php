<?php

declare(strict_types=1);

use AmarMayor\Http\Response;
use AmarMayor\Support\Config;
use AmarMayor\Support\Container;
use AmarMayor\Support\Env;
use AmarMayor\Support\Security;
use AmarMayor\Support\Translator;
use AmarMayor\View\View;

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return Env::get($key, $default);
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        return Config::get($key, $default);
    }
}

if (!function_exists('app')) {
    function app(?string $abstract = null): mixed
    {
        $container = Container::getInstance();
        if ($abstract === null) {
            return $container;
        }
        return $container->get($abstract);
    }
}

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return Security::escape($value);
    }
}

if (!function_exists('__')) {
    function __(string $key, array $replace = [], ?string $locale = null): string
    {
        return Translator::get($key, $replace, $locale);
    }
}

if (!function_exists('trans')) {
    function trans(string $key, array $replace = [], ?string $locale = null): string
    {
        return Translator::get($key, $replace, $locale);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return Security::generateCsrfToken();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        $token = csrf_token();
        return '<input type="hidden" name="_csrf_token" value="' . e($token) . '">';
    }
}

if (!function_exists('view')) {
    function view(string $template, array $data = [], ?string $layout = 'layouts/app'): Response
    {
        $html = View::render($template, $data, $layout);
        return Response::html($html);
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url, int $statusCode = 302): Response
    {
        return Response::redirect($url, $statusCode);
    }
}

if (!function_exists('now_dhaka')) {
    function now_dhaka(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new DateTimeZone('Asia/Dhaka'));
    }
}
