<?php

declare(strict_types=1);

use AmarMayor\Support\Env;

return [
    'sms' => [
        'driver' => Env::get('SMS_DRIVER', 'mock'),
    ],

    'email' => [
        'driver' => Env::get('EMAIL_DRIVER', 'mock'),
    ],

    'push' => [
        'driver' => Env::get('PUSH_DRIVER', 'mock'),
    ],

    'maps' => [
        'driver' => Env::get('MAPS_DRIVER', 'osm'),
    ],
];
