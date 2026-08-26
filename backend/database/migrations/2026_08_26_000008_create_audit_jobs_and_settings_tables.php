<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        // 1. Append-Only Audit Logs Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS audit_logs (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            actor_user_id BIGINT UNSIGNED NULL,
            event_category VARCHAR(64) NOT NULL,
            action VARCHAR(64) NOT NULL,
            entity_type VARCHAR(64) NOT NULL,
            entity_id BIGINT UNSIGNED NULL,
            old_values JSON NULL,
            new_values JSON NULL,
            reason TEXT NULL,
            request_id VARCHAR(64) NULL,
            ip_address VARCHAR(45) NULL,
            user_agent VARCHAR(255) NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_audit_user FOREIGN KEY (actor_user_id) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_audit_entity (entity_type, entity_id),
            INDEX idx_audit_category_action (event_category, action),
            INDEX idx_audit_request (request_id),
            INDEX idx_audit_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 2. Durable Background Jobs Table (MySQL source of truth)
        $pdo->exec("CREATE TABLE IF NOT EXISTS background_jobs (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            queue_name VARCHAR(64) NOT NULL DEFAULT 'default',
            job_handler VARCHAR(128) NOT NULL,
            payload LONGTEXT NOT NULL,
            status VARCHAR(32) NOT NULL DEFAULT 'queued',
            attempts INT UNSIGNED NOT NULL DEFAULT 0,
            max_attempts INT UNSIGNED NOT NULL DEFAULT 3,
            last_error TEXT NULL,
            idempotency_key VARCHAR(64) NULL UNIQUE,
            available_at DATETIME NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_bj_queue_status_avail (queue_name, status, available_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 3. Background Job Attempts Table (Detailed Execution & Retry Log)
        $pdo->exec("CREATE TABLE IF NOT EXISTS background_job_attempts (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            job_id BIGINT UNSIGNED NOT NULL,
            attempt_number INT UNSIGNED NOT NULL,
            started_at DATETIME NOT NULL,
            finished_at DATETIME NULL,
            status VARCHAR(32) NOT NULL DEFAULT 'running',
            error_message TEXT NULL,
            result_summary TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_bja_job FOREIGN KEY (job_id) REFERENCES background_jobs(id) ON DELETE CASCADE,
            INDEX idx_bja_job_attempt (job_id, attempt_number)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 4. System Configuration Settings Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
            key_name VARCHAR(64) NOT NULL PRIMARY KEY,
            value_text TEXT NULL,
            value_type VARCHAR(32) NOT NULL DEFAULT 'string',
            is_public TINYINT(1) NOT NULL DEFAULT 0,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS settings;");
        $pdo->exec("DROP TABLE IF EXISTS background_job_attempts;");
        $pdo->exec("DROP TABLE IF EXISTS background_jobs;");
        $pdo->exec("DROP TABLE IF EXISTS audit_logs;");
    }
};
