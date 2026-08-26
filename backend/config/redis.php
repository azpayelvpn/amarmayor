<?php

declare(strict_types=1);

use AmarMayor\Support\Env;

return [
    'host' => Env::get('REDIS_HOST', '127.0.0.1'),
    'port' => (int)Env::get('REDIS_PORT', 6379),
    'password' => Env::get('REDIS_PASSWORD', null),
    'database' => (int)Env::get('REDIS_DATABASE', 0),
    'prefix' => Env::get('REDIS_PREFIX', 'amarmayor:'),
    'timeout' => 1.5,
    'retry_interval' => 100,
];
