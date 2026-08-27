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

        // 1. Persons: Citizen profile & Provenance
        $addColumn('persons', 'home_ward_id', 'INT UNSIGNED NULL');
        $addColumn('persons', 'home_area', 'VARCHAR(191) NULL');
        $addColumn('persons', 'notification_prefs', 'TEXT NULL');
        $addColumn('persons', 'source_name', 'VARCHAR(255) NULL');
        $addColumn('persons', 'source_url', 'VARCHAR(255) NULL');
        $addColumn('persons', 'source_checked_at', 'DATETIME NULL');
        $addColumn('persons', 'verification_status', "VARCHAR(32) NOT NULL DEFAULT 'verified_current'");
        $addColumn('persons', 'is_demo', 'TINYINT(1) NOT NULL DEFAULT 0');

        // 2. Employees: Provenance & Demo flag
        $addColumn('employees', 'source_name', 'VARCHAR(255) NULL');
        $addColumn('employees', 'source_url', 'VARCHAR(255) NULL');
        $addColumn('employees', 'source_checked_at', 'DATETIME NULL');
        $addColumn('employees', 'verification_status', "VARCHAR(32) NOT NULL DEFAULT 'verified_current'");
        $addColumn('employees', 'is_demo', 'TINYINT(1) NOT NULL DEFAULT 0');

        // 3. Representation Assignments: Provenance & Demo flag
        $addColumn('representation_assignments', 'source_name', 'VARCHAR(255) NULL');
        $addColumn('representation_assignments', 'source_url', 'VARCHAR(255) NULL');
        $addColumn('representation_assignments', 'source_checked_at', 'DATETIME NULL');
        $addColumn('representation_assignments', 'verification_status', "VARCHAR(32) NOT NULL DEFAULT 'verified_current'");
        $addColumn('representation_assignments', 'is_demo', 'TINYINT(1) NOT NULL DEFAULT 0');

        // 4. Users: Demo flag
        $addColumn('users', 'is_demo', 'TINYINT(1) NOT NULL DEFAULT 0');

        // 5. Teams: Demo flag
        $addColumn('teams', 'is_demo', 'TINYINT(1) NOT NULL DEFAULT 0');

        // 6. Complaints: Demo flag & Origin & Complainant Contact
        $addColumn('complaints', 'is_demo', 'TINYINT(1) NOT NULL DEFAULT 0');
        $addColumn('complaints', 'data_origin', "VARCHAR(32) NOT NULL DEFAULT 'production'");
        $addColumn('complaints', 'complainant_name', 'VARCHAR(128) NULL');
        $addColumn('complaints', 'complainant_phone', 'VARCHAR(32) NULL');

        // 7. Field Tasks: Demo flag
        $addColumn('field_tasks', 'is_demo', 'TINYINT(1) NOT NULL DEFAULT 0');

        // 8. Complaint Media: Demo flag
        $addColumn('complaint_media', 'is_demo', 'TINYINT(1) NOT NULL DEFAULT 0');

        // 9. Complaint Status History: Demo flag
        $addColumn('complaint_status_history', 'is_demo', 'TINYINT(1) NOT NULL DEFAULT 0');

        // 10. Executive Attention: Demo flag
        $addColumn('executive_attention', 'is_demo', 'TINYINT(1) NOT NULL DEFAULT 0');

        // 11. Complaint Messages: Demo flag
        $addColumn('complaint_messages', 'is_demo', 'TINYINT(1) NOT NULL DEFAULT 0');

        // 12. Notifications: Demo flag
        $addColumn('notifications', 'is_demo', 'TINYINT(1) NOT NULL DEFAULT 0');
    }

    public function down(PDO $pdo): void
    {
        // Non-destructive fallback for column additions
    }
};
