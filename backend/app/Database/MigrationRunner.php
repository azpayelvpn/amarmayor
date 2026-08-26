<?php

declare(strict_types=1);

namespace AmarMayor\Database;

use PDO;
use RuntimeException;
use Throwable;

/**
 * Lightweight Repeatable MySQL Migration Engine.
 *
 * NOTE ON MYSQL DDL TRANSACTIONS:
 * In MySQL/InnoDB, DDL statements (such as CREATE TABLE, ALTER TABLE, DROP TABLE) trigger
 * an implicit commit and cannot be rolled back atomically via standard PDO transactions.
 * Therefore, migration reliability is guaranteed through:
 * 1. Strict dependency ordering (numbered prefixes).
 * 2. Immutable tracking in the _migrations table.
 * 3. Atomic single-responsibility migration files.
 * 4. Verified, symmetrical down() rollback routines.
 */
class MigrationRunner
{
    private string $migrationsPath;

    public function __construct(?string $migrationsPath = null)
    {
        $this->migrationsPath = $migrationsPath ?: dirname(__DIR__, 2) . '/database/migrations';
    }

    public function ensureMigrationTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS _migrations (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(191) NOT NULL UNIQUE,
            batch INT UNSIGNED NOT NULL,
            executed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        DatabaseManager::getConnection()->exec($sql);
    }

    public function getStatus(): array
    {
        $this->ensureMigrationTable();
        $applied = $this->getAppliedMigrations();
        $files = $this->getMigrationFiles();

        $status = [];
        foreach ($files as $file) {
            $name = basename($file, '.php');
            $status[] = [
                'migration' => $name,
                'applied' => isset($applied[$name]),
                'batch' => $applied[$name]['batch'] ?? null,
                'executed_at' => $applied[$name]['executed_at'] ?? null,
            ];
        }

        return $status;
    }

    public function migrate(): array
    {
        $this->ensureMigrationTable();
        $applied = $this->getAppliedMigrations();
        $files = $this->getMigrationFiles();

        $nextBatch = $this->getNextBatchNumber();
        $executed = [];

        foreach ($files as $file) {
            $name = basename($file, '.php');
            if (isset($applied[$name])) {
                continue;
            }

            $migration = require $file;
            if (!is_object($migration) || !method_exists($migration, 'up')) {
                throw new RuntimeException("Invalid migration structure in file: [{$file}]. Must return an object with an up() method.");
            }

            DatabaseManager::transaction(function (PDO $pdo) use ($migration, $name, $nextBatch) {
                $migration->up($pdo);
                $stmt = $pdo->prepare("INSERT INTO _migrations (migration, batch, executed_at) VALUES (?, ?, NOW())");
                $stmt->execute([$name, $nextBatch]);
            });

            $executed[] = $name;
        }

        return $executed;
    }

    public function rollback(): array
    {
        $this->ensureMigrationTable();
        $latestBatch = $this->getLatestBatchNumber();
        if ($latestBatch === 0) {
            return [];
        }

        $stmt = DatabaseManager::getConnection()->prepare("SELECT migration FROM _migrations WHERE batch = ? ORDER BY id DESC");
        $stmt->execute([$latestBatch]);
        $migrationsToRollback = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $rolledBack = [];
        foreach ($migrationsToRollback as $name) {
            $filePath = $this->migrationsPath . DIRECTORY_SEPARATOR . $name . '.php';
            if (!file_exists($filePath)) {
                throw new RuntimeException("Migration file for rollback not found: [{$filePath}]");
            }

            $migration = require $filePath;
            if (!is_object($migration) || !method_exists($migration, 'down')) {
                throw new RuntimeException("Migration [{$name}] lacks down() method for rollback.");
            }

            DatabaseManager::transaction(function (PDO $pdo) use ($migration, $name) {
                $migration->down($pdo);
                $delStmt = $pdo->prepare("DELETE FROM _migrations WHERE migration = ?");
                $delStmt->execute([$name]);
            });

            $rolledBack[] = $name;
        }

        return $rolledBack;
    }

    public function createMigration(string $name): string
    {
        $timestamp = date('Y_m_d_His');
        $safeName = preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($name));
        $filename = "{$timestamp}_{$safeName}.php";
        $targetPath = $this->migrationsPath . DIRECTORY_SEPARATOR . $filename;

        if (!is_dir($this->migrationsPath)) {
            @mkdir($this->migrationsPath, 0755, true);
        }

        $template = <<<PHP
<?php

declare(strict_types=1);

return new class {
    public function up(PDO \$pdo): void
    {
        // \$pdo->exec("CREATE TABLE ... ");
    }

    public function down(PDO \$pdo): void
    {
        // \$pdo->exec("DROP TABLE IF EXISTS ... ");
    }
};
PHP;

        file_put_contents($targetPath, $template);
        return $filename;
    }

    private function getAppliedMigrations(): array
    {
        $stmt = DatabaseManager::getConnection()->query("SELECT migration, batch, executed_at FROM _migrations ORDER BY id ASC");
        $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        $applied = [];
        foreach ($rows as $row) {
            $applied[$row['migration']] = $row;
        }
        return $applied;
    }

    private function getMigrationFiles(): array
    {
        if (!is_dir($this->migrationsPath)) {
            return [];
        }

        $files = glob($this->migrationsPath . '/*.php');
        if ($files === false) {
            return [];
        }

        sort($files);
        return $files;
    }

    private function getNextBatchNumber(): int
    {
        return $this->getLatestBatchNumber() + 1;
    }

    private function getLatestBatchNumber(): int
    {
        $stmt = DatabaseManager::getConnection()->query("SELECT MAX(batch) AS max_batch FROM _migrations");
        $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
        return (int)($row['max_batch'] ?? 0);
    }
}
