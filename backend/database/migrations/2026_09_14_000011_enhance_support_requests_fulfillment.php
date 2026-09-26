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

        // Add explicit resource allocation and ground receipt tracking to support_requests
        $addColumn('support_requests', 'allocated_resource', 'VARCHAR(255) NULL');
        $addColumn('support_requests', 'allocated_operator', 'VARCHAR(255) NULL');
        $addColumn('support_requests', 'scheduled_arrival', 'VARCHAR(128) NULL');
        $addColumn('support_requests', 'fulfillment_status', "VARCHAR(32) NOT NULL DEFAULT 'pending'");
        $addColumn('support_requests', 'received_by_user_id', 'BIGINT UNSIGNED NULL');
        $addColumn('support_requests', 'received_at', 'DATETIME NULL');
        $addColumn('support_requests', 'receipt_notes', 'TEXT NULL');

        try {
            $pdo->exec("ALTER TABLE `support_requests` ADD CONSTRAINT `fk_sr_rec_user` FOREIGN KEY (`received_by_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL");
        } catch (\Throwable $e) {
            // Constraint might already exist
        }

        try {
            $pdo->exec("ALTER TABLE `support_requests` ADD INDEX `idx_sr_fulfillment` (`fulfillment_status`)");
        } catch (\Throwable $e) {
            // Index might already exist
        }
    }

    public function down(PDO $pdo): void
    {
        // Safe non-destructive
    }
};
