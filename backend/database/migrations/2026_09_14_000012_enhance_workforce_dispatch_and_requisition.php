<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $addColumn = function (string $table, string $column, string $definition) use ($pdo) {
            $check = $pdo->query("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'")->fetchColumn();
            if (!$check) {
                $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
            }
        };

        // 1. field_tasks enhancements for Squad Dispatching
        $addColumn('field_tasks', 'team_leader_employee_id', 'BIGINT UNSIGNED NULL AFTER `assigned_worker_employee_id`');
        $addColumn('field_tasks', 'worker_count', 'INT UNSIGNED NOT NULL DEFAULT 1 AFTER `team_leader_employee_id`');
        $addColumn('field_tasks', 'estimated_hours', 'DECIMAL(4,1) NULL AFTER `worker_count`');

        try {
            $pdo->exec("ALTER TABLE `field_tasks` ADD CONSTRAINT `fk_ft_team_leader` FOREIGN KEY (`team_leader_employee_id`) REFERENCES `employees`(`id`) ON DELETE SET NULL");
        } catch (\Throwable $e) {
            // Constraint might already exist
        }

        try {
            $pdo->exec("ALTER TABLE `field_tasks` ADD INDEX `idx_ft_team_leader` (`team_leader_employee_id`)");
        } catch (\Throwable $e) {
            // Index might already exist
        }

        // 2. support_requests enhancements for Workforce Shortage & Deputation
        $addColumn('support_requests', 'requested_worker_count', 'INT UNSIGNED NULL AFTER `support_type`');
        $addColumn('support_requests', 'allocated_worker_count', 'INT UNSIGNED NULL AFTER `allocated_resource`');
        $addColumn('support_requests', 'source_ward_id', 'INT UNSIGNED NULL AFTER `target_department_id`');

        try {
            $pdo->exec("ALTER TABLE `support_requests` ADD CONSTRAINT `fk_sr_source_ward` FOREIGN KEY (`source_ward_id`) REFERENCES `wards`(`id`) ON DELETE SET NULL");
        } catch (\Throwable $e) {
            // Constraint might already exist
        }
    }

    public function down(PDO $pdo): void
    {
        // Safe non-destructive
    }
};
