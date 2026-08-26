<?php

declare(strict_types=1);

use AmarMayor\Support\Env;

return [
    'channel' => Env::get('LOG_CHANNEL', 'daily'),
    'level' => Env::get('LOG_LEVEL', 'debug'),
    'path' => dirname(__DIR__) . '/storage/logs/app.log',
];
