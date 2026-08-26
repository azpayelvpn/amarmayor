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

if (!function_exists('to_bn_number')) {
    function to_bn_number(int|float|string $number): string
    {
        return Translator::toBanglaNumber($number);
    }
}

if (!function_exists('format_date_bn')) {
    function format_date_bn(string|DateTimeInterface|null $dateTime, string $format = 'medium', ?string $locale = null): string
    {
        return Translator::formatDate($dateTime, $format, $locale);
    }
}

if (!function_exists('format_bn_date')) {
    function format_bn_date(string|DateTimeInterface|null $dateTime, string $format = 'medium', ?string $locale = null): string
    {
        return Translator::formatDate($dateTime, $format, $locale);
    }
}

if (!function_exists('format_money')) {
    function format_money(int|float $amount, ?string $locale = null): string
    {
        return Translator::formatMoney($amount, $locale);
    }
}

if (!function_exists('now_dhaka')) {
    function now_dhaka(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new DateTimeZone('Asia/Dhaka'));
    }
}

if (!function_exists('human_status')) {
    function human_status(string $status, ?string $locale = null): string
    {
        $loc = $locale ?: Translator::getLocale();
        $transKey = "complaints.internal_status.{$status}";
        $trans = __($transKey, [], $loc);
        if ($trans !== $transKey) {
            return $trans;
        }
        $citizenKey = "complaints.citizen_status.{$status}";
        $transCitizen = __($citizenKey, [], $loc);
        if ($transCitizen !== $citizenKey) {
            return $transCitizen;
        }
        $commonBn = [
            'deadline_breach' => 'সময়সীমা পেরিয়েছে',
            'citizen_reopen' => 'নাগরিক কর্তৃক পুনরায় চালু',
            'overdue' => 'সময়সীমা পেরিয়েছে',
            'pending' => 'অপেক্ষমাণ',
            'completed' => 'কাজ সম্পন্ন (যাচাই বাকি)',
            'in_progress' => 'কাজ চলছে',
            'verified' => 'যাচাই সম্পন্ন',
            'active' => 'সক্রিয়',
            'inactive' => 'নিষ্ক্রিয়',
            'healthy' => 'স্বাস্থ্যকর / সচল',
        ];
        $commonEn = [
            'deadline_breach' => 'Deadline Breached',
            'citizen_reopen' => 'Citizen Reopened',
            'overdue' => 'Overdue',
            'pending' => 'Pending',
            'completed' => 'Completed',
            'in_progress' => 'In Progress',
            'verified' => 'Verified',
            'active' => 'Active',
            'inactive' => 'Inactive',
            'healthy' => 'Healthy',
        ];
        if ($loc === 'bn' && isset($commonBn[$status])) {
            return $commonBn[$status];
        }
        if ($loc === 'en' && isset($commonEn[$status])) {
            return $commonEn[$status];
        }
        return ucwords(str_replace('_', ' ', $status));
    }
}
