<?php

declare(strict_types=1);

namespace AmarMayor\Database\Seeders;

/**
 * Complete Fictional Development Demo Seeder.
 * Delegates to RealisticDemoSeeder.
 * Strictly blocked in production.
 */
class DemoSeeder
{
    public const DEMO_PASSWORD = RealisticDemoSeeder::DEMO_PASSWORD;

    public static function run(): void
    {
        RealisticDemoSeeder::run();
        WardSupervisorsSeeder::run();
        WardTeamsAndLeadersSeeder::run();
    }
}
