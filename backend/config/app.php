<?php

declare(strict_types=1);

use AmarMayor\Support\Env;

return [
    'name' => Env::get('APP_NAME', 'আমার ময়মনসিংহ'),
    'name_en' => 'My Mymensingh',
    'env' => Env::get('APP_ENV', 'local'),
    'debug' => (bool)Env::get('APP_DEBUG', true),
    'url' => Env::get('APP_URL', 'http://localhost:8000'),
    'timezone' => Env::get('APP_TIMEZONE', 'Asia/Dhaka'),
    'locale' => Env::get('APP_LOCALE', 'bn'),
    'fallback_locale' => Env::get('APP_FALLBACK_LOCALE', 'en'),
    'key' => Env::get('APP_KEY', 'default_secret_key_32_bytes_min_long!'),
];
