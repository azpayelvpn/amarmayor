<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        // 1. Executive Attention Table (Mayor / Administrator Focus Trigger Log)
        $pdo->exec("CREATE TABLE IF NOT EXISTS executive_attention (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            complaint_id BIGINT UNSIGNED NOT NULL,
            trigger_type VARCHAR(64) NOT NULL,
            severity VARCHAR(32) NOT NULL DEFAULT 'p2_high',
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            resolved_at DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_ea_complaint FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE RESTRICT,
            INDEX idx_ea_complaint (complaint_id),
            INDEX idx_ea_trigger (trigger_type),
            INDEX idx_ea_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 2. Executive Directives Table (Mayor / Administrator Specific Orders)
        $pdo->exec("CREATE TABLE IF NOT EXISTS executive_directives (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            executive_user_id BIGINT UNSIGNED NOT NULL,
            complaint_id BIGINT UNSIGNED NULL,
            ward_id INT UNSIGNED NULL,
            department_id INT UNSIGNED NULL,
            directive_type VARCHAR(64) NOT NULL,
            instruction TEXT NOT NULL,
            status VARCHAR(32) NOT NULL DEFAULT 'issued',
            response_text TEXT NULL,
            responded_at DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_ed_exec FOREIGN KEY (executive_user_id) REFERENCES users(id) ON DELETE RESTRICT,
            CONSTRAINT fk_ed_comp FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE SET NULL,
            CONSTRAINT fk_ed_ward FOREIGN KEY (ward_id) REFERENCES wards(id) ON DELETE SET NULL,
            CONSTRAINT fk_ed_dept FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
            INDEX idx_ed_status (status),
            INDEX idx_ed_complaint (complaint_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 3. Explanation Requests Table (Formal Query to Officers on Failure)
        $pdo->exec("CREATE TABLE IF NOT EXISTS explanation_requests (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            executive_user_id BIGINT UNSIGNED NOT NULL,
            target_employee_id BIGINT UNSIGNED NOT NULL,
            complaint_id BIGINT UNSIGNED NULL,
            question TEXT NOT NULL,
            due_date DATETIME NULL,
            explanation_response TEXT NULL,
            responded_at DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_erq_exec FOREIGN KEY (executive_user_id) REFERENCES users(id) ON DELETE RESTRICT,
            CONSTRAINT fk_erq_emp FOREIGN KEY (target_employee_id) REFERENCES employees(id) ON DELETE RESTRICT,
            CONSTRAINT fk_erq_comp FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE SET NULL,
            INDEX idx_erq_emp (target_employee_id),
            INDEX idx_erq_comp (complaint_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 4. Complaint Messages Table (Citizen <-> Staff 2-Way Dialog)
        $pdo->exec("CREATE TABLE IF NOT EXISTS complaint_messages (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            complaint_id BIGINT UNSIGNED NOT NULL,
            sender_user_id BIGINT UNSIGNED NOT NULL,
            message_type VARCHAR(64) NOT NULL,
            body TEXT NOT NULL,
            is_moderated TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_cm_complaint FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE RESTRICT,
            CONSTRAINT fk_cm_sender FOREIGN KEY (sender_user_id) REFERENCES users(id) ON DELETE RESTRICT,
            INDEX idx_cm_complaint (complaint_id),
            INDEX idx_cm_sender (sender_user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 5. Internal Notes Table (Strict Staff-Only Notes)
        $pdo->exec("CREATE TABLE IF NOT EXISTS internal_notes (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            complaint_id BIGINT UNSIGNED NOT NULL,
            author_user_id BIGINT UNSIGNED NOT NULL,
            note_type VARCHAR(64) NOT NULL,
            note_text TEXT NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_in_complaint FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE RESTRICT,
            CONSTRAINT fk_in_author FOREIGN KEY (author_user_id) REFERENCES users(id) ON DELETE RESTRICT,
            INDEX idx_in_complaint (complaint_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 6. Office Messages Table (Public Leadership Contact)
        $pdo->exec("CREATE TABLE IF NOT EXISTS office_messages (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            citizen_user_id BIGINT UNSIGNED NOT NULL,
            office_type VARCHAR(64) NOT NULL,
            category VARCHAR(64) NOT NULL,
            linked_complaint_id BIGINT UNSIGNED NULL,
            subject VARCHAR(191) NULL,
            body TEXT NOT NULL,
            status VARCHAR(32) NOT NULL DEFAULT 'received',
            triaged_by_user_id BIGINT UNSIGNED NULL,
            reply_text TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_om_citizen FOREIGN KEY (citizen_user_id) REFERENCES users(id) ON DELETE RESTRICT,
            CONSTRAINT fk_om_comp FOREIGN KEY (linked_complaint_id) REFERENCES complaints(id) ON DELETE SET NULL,
            CONSTRAINT fk_om_triage FOREIGN KEY (triaged_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_om_office (office_type),
            INDEX idx_om_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 7. City Notices Table (Public Announcements with Geo Scopes)
        $pdo->exec("CREATE TABLE IF NOT EXISTS city_notices (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            city_id INT UNSIGNED NOT NULL,
            publisher_user_id BIGINT UNSIGNED NOT NULL,
            notice_type VARCHAR(64) NOT NULL,
            target_scope VARCHAR(32) NOT NULL,
            target_id BIGINT UNSIGNED NULL,
            title_bn VARCHAR(255) NOT NULL,
            title_en VARCHAR(255) NULL,
            body_bn TEXT NOT NULL,
            body_en TEXT NULL,
            is_published TINYINT(1) NOT NULL DEFAULT 1,
            published_at DATETIME NOT NULL,
            expires_at DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT chk_cn_dates CHECK (expires_at IS NULL OR expires_at >= published_at),
            CONSTRAINT fk_cn_city FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE RESTRICT,
            CONSTRAINT fk_cn_publisher FOREIGN KEY (publisher_user_id) REFERENCES users(id) ON DELETE RESTRICT,
            INDEX idx_cn_publish (is_published, published_at, expires_at),
            INDEX idx_cn_scope (target_scope, target_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 8. Citizen Pulse Table (Lightweight Sentiment Tracking)
        $pdo->exec("CREATE TABLE IF NOT EXISTS citizen_pulse (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            citizen_user_id BIGINT UNSIGNED NOT NULL,
            ward_id INT UNSIGNED NOT NULL,
            topic VARCHAR(64) NOT NULL,
            sentiment VARCHAR(32) NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_cp_citizen FOREIGN KEY (citizen_user_id) REFERENCES users(id) ON DELETE RESTRICT,
            CONSTRAINT fk_cp_ward FOREIGN KEY (ward_id) REFERENCES wards(id) ON DELETE RESTRICT,
            INDEX idx_cp_ward_topic (ward_id, topic)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 9. Notifications Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            notification_type VARCHAR(64) NOT NULL,
            title_bn VARCHAR(255) NOT NULL,
            title_en VARCHAR(255) NULL,
            body_bn TEXT NOT NULL,
            body_en TEXT NULL,
            data_payload JSON NULL,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            read_at DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_notif_user_read (user_id, is_read, created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 10. Notification Preferences Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS notification_preferences (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            channel VARCHAR(32) NOT NULL,
            is_enabled TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_user_channel (user_id, channel),
            CONSTRAINT fk_np_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS notification_preferences;");
        $pdo->exec("DROP TABLE IF EXISTS notifications;");
        $pdo->exec("DROP TABLE IF EXISTS citizen_pulse;");
        $pdo->exec("DROP TABLE IF EXISTS city_notices;");
        $pdo->exec("DROP TABLE IF EXISTS office_messages;");
        $pdo->exec("DROP TABLE IF EXISTS internal_notes;");
        $pdo->exec("DROP TABLE IF EXISTS complaint_messages;");
        $pdo->exec("DROP TABLE IF EXISTS explanation_requests;");
        $pdo->exec("DROP TABLE IF EXISTS executive_directives;");
        $pdo->exec("DROP TABLE IF EXISTS executive_attention;");
    }
};
