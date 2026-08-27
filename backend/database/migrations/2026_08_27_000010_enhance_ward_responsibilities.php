<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        // Helper to safely add column if not exists
        $addColumn = function (string $table, string $column, string $definition) use ($pdo) {
            $check = $pdo->query("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'")->fetchColumn();
            if (!$check) {
                $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
            }
        };

        // 1. Employee Responsibilities: Support primary vs substitute, provenance & demo flags
        $addColumn('employee_responsibilities', 'responsibility_type', "VARCHAR(32) NOT NULL DEFAULT 'primary'");
        $addColumn('employee_responsibilities', 'substitute_employee_id', 'BIGINT UNSIGNED NULL');
        $addColumn('employee_responsibilities', 'raw_source_title', 'VARCHAR(128) NULL');
        $addColumn('employee_responsibilities', 'source_name', 'VARCHAR(255) NULL');
        $addColumn('employee_responsibilities', 'source_url', 'VARCHAR(255) NULL');
        $addColumn('employee_responsibilities', 'source_checked_at', 'DATETIME NULL');
        $addColumn('employee_responsibilities', 'verification_status', "VARCHAR(32) NOT NULL DEFAULT 'verified_current'");
        $addColumn('employee_responsibilities', 'is_demo', 'TINYINT(1) NOT NULL DEFAULT 0');

        // Add foreign key for substitute_employee_id if not exists
        try {
            $pdo->exec("ALTER TABLE `employee_responsibilities` ADD CONSTRAINT `fk_er_substitute` FOREIGN KEY (`substitute_employee_id`) REFERENCES `employees`(`id`) ON DELETE SET NULL");
        } catch (\Throwable $e) {
            // Constraint might already exist
        }

        // 2. Representation Assignments: Support raw source title and status notes (transferred, deceased, etc.)
        $addColumn('representation_assignments', 'raw_source_title', 'VARCHAR(128) NULL');
        $addColumn('representation_assignments', 'status_note', 'VARCHAR(128) NULL');

        // 3. Persons: Support status note (e.g. transferred, deceased, external posting)
        $addColumn('persons', 'status_note', 'VARCHAR(128) NULL');
    }

    public function down(PDO $pdo): void
    {
        // Non-destructive fallback
    }
};
