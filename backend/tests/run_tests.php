<?php

declare(strict_types=1);

/**
 * Amar Mayor — Standalone Lightweight Test Suite Runner.
 */

$baseDir = dirname(__DIR__);
require_once $baseDir . '/bootstrap/app.php';

echo "\n========================================================\n";
echo " Amar Mayor — Phase 1 Core Backend Test Suite Runner\n";
echo "========================================================\n\n";

$testClasses = [
    \AmarMayor\Tests\Unit\RouterTest::class,
    \AmarMayor\Tests\Unit\ResponseEnvelopeTest::class,
    \AmarMayor\Tests\Unit\ValidatorTest::class,
    \AmarMayor\Tests\Unit\SecurityTest::class,
    \AmarMayor\Tests\Unit\ContainerTest::class,
    \AmarMayor\Tests\Unit\TranslatorTest::class,
    \AmarMayor\Tests\Unit\SessionPersistenceTest::class,
    \AmarMayor\Tests\Unit\Database\SchemaIntegrityTest::class,
    \AmarMayor\Tests\Unit\Database\StructuralSeedDataTest::class,
    \AmarMayor\Tests\Unit\Database\GovernanceAndWorkforceTest::class,
    \AmarMayor\Tests\Unit\Database\ComplaintHistoryAndAuditTest::class,
    \AmarMayor\Tests\Feature\HealthApiTest::class,
    \AmarMayor\Tests\Feature\HealthApiHardeningTest::class,
    \AmarMayor\Tests\Feature\WebLandingTest::class,
];

$totalTests = 0;
$passedTests = 0;
$failedTests = 0;
$failures = [];

foreach ($testClasses as $testClass) {
    $reflection = new ReflectionClass($testClass);
    $instance = new $testClass();

    echo "Running " . $reflection->getShortName() . "...\n";

    foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        if (!str_starts_with($method->getName(), 'test')) {
            continue;
        }

        $totalTests++;
        $methodName = $method->getName();

        try {
            if (method_exists($instance, 'setUp')) {
                $instance->setUp();
            }
            $instance->$methodName();
            $passedTests++;
            echo "  [PASS] {$methodName}\n";
        } catch (\Throwable $e) {
            $failedTests++;
            $failures[] = [
                'class' => $testClass,
                'method' => $methodName,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];
            echo "  [FAIL] {$methodName} - " . $e->getMessage() . "\n";
        }
    }
    echo "\n";
}

echo "--------------------------------------------------------\n";
echo "Test Execution Summary:\n";
echo "  Total Tests:  {$totalTests}\n";
echo "  Passed:       {$passedTests}\n";
echo "  Failed:       {$failedTests}\n";
echo "--------------------------------------------------------\n";

if ($failedTests > 0) {
    echo "\n❌ Failures Detected:\n";
    foreach ($failures as $f) {
        echo "  - {$f['class']}::{$f['method']} ({$f['file']}:{$f['line']})\n";
        echo "    Message: {$f['error']}\n";
    }
    exit(1);
} else {
    echo "\n✅ All Phase 1 Core Backend Tests Passed Successfully!\n\n";
    exit(0);
}
