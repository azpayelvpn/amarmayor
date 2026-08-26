<?php

declare(strict_types=1);

use AmarMayor\Database\DatabaseManager;

require_once __DIR__ . '/../bootstrap/app.php';

$pdo = DatabaseManager::getConnection();

// 1. Table inventory
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo "=== TABLE COUNT: " . count($tables) . " ===\n";
echo implode("\n", $tables) . "\n\n";

// 2. Structural counts
$counts = [
    'cities' => (int)$pdo->query("SELECT COUNT(*) FROM cities")->fetchColumn(),
    'zones' => (int)$pdo->query("SELECT COUNT(*) FROM zones")->fetchColumn(),
    'wards' => (int)$pdo->query("SELECT COUNT(*) FROM wards")->fetchColumn(),
    'reserved_seats' => (int)$pdo->query("SELECT COUNT(*) FROM reserved_seats")->fetchColumn(),
    'reserved_seat_wards' => (int)$pdo->query("SELECT COUNT(*) FROM reserved_seat_wards")->fetchColumn(),
    'roles' => (int)$pdo->query("SELECT COUNT(*) FROM roles")->fetchColumn(),
    'permissions' => (int)$pdo->query("SELECT COUNT(*) FROM permissions")->fetchColumn(),
    'representation_types' => (int)$pdo->query("SELECT COUNT(*) FROM representation_types")->fetchColumn(),
    'complaint_categories' => (int)$pdo->query("SELECT COUNT(*) FROM complaint_categories")->fetchColumn(),
    'complaint_subcategories' => (int)$pdo->query("SELECT COUNT(*) FROM complaint_subcategories")->fetchColumn(),
    'operational_classifications' => (int)$pdo->query("SELECT COUNT(*) FROM operational_classifications")->fetchColumn(),
    'priorities' => (int)$pdo->query("SELECT COUNT(*) FROM priorities")->fetchColumn(),
    'skills' => (int)$pdo->query("SELECT COUNT(*) FROM skills")->fetchColumn(),
];

echo "=== STRUCTURAL COUNTS ===\n";
foreach ($counts as $entity => $count) {
    echo sprintf("%-30s: %d\n", $entity, $count);
}

// 3. Check for any ENUM columns across the schema
$enumQuery = "SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE 
              FROM information_schema.COLUMNS 
              WHERE TABLE_SCHEMA = DATABASE() AND DATA_TYPE = 'enum'";
$enums = $pdo->query($enumQuery)->fetchAll(PDO::FETCH_ASSOC);

echo "\n=== ENUM COLUMNS IN SCHEMA: " . count($enums) . " ===\n";
foreach ($enums as $e) {
    echo "{$e['TABLE_NAME']}.{$e['COLUMN_NAME']}: {$e['COLUMN_TYPE']}\n";
}
