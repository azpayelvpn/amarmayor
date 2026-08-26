<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap/app.php';

use AmarMayor\Database\Seeders\DemoSeeder;
use AmarMayor\Support\Config;

if (Config::get('app.env') === 'production') {
    echo "ERROR: Demo seeder is strictly disabled in production!\n";
    exit(1);
}

echo "Seeding Development Demo Accounts (All 22 Canonical Roles)...\n";
DemoSeeder::run();
echo "✅ All 22 demo roles seeded successfully! Password: " . DemoSeeder::DEMO_PASSWORD . "\n";
