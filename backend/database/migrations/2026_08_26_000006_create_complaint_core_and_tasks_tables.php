<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        // 1. Core Complaints Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS complaints (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            public_complaint_number VARCHAR(32) NOT NULL UNIQUE,
            citizen_user_id BIGINT UNSIGNED NOT NULL,
            created_by_user_id BIGINT UNSIGNED NULL,
            category_id INT UNSIGNED NOT NULL,
            subcategory_id INT UNSIGNED NOT NULL,
            ward_id INT UNSIGNED NOT NULL,
            zone_id INT UNSIGNED NOT NULL,
            department_id INT UNSIGNED NULL,
            service_unit_id INT UNSIGNED NULL,
            current_supervisor_employee_id BIGINT UNSIGNED NULL,
            current_team_id INT UNSIGNED NULL,
            priority VARCHAR(32) NOT NULL DEFAULT 'p3_normal',
            operational_classification VARCHAR(64) NOT NULL DEFAULT 'quick_action',
            internal_status VARCHAR(64) NOT NULL DEFAULT 'submitted',
            citizen_status VARCHAR(64) NOT NULL DEFAULT 'received',
            description TEXT NOT NULL,
            is_sensitive TINYINT(1) NOT NULL DEFAULT 0,
            is_recurring TINYINT(1) NOT NULL DEFAULT 0,
            parent_complaint_id BIGINT UNSIGNED NULL,
            submitted_at DATETIME NOT NULL,
            deadline_at DATETIME NULL,
            deadline_missed_at DATETIME NULL,
            completion_attempts INT UNSIGNED NOT NULL DEFAULT 0,
            reopen_count INT UNSIGNED NOT NULL DEFAULT 0,
            first_reopened_at DATETIME NULL,
            verified_at DATETIME NULL,
            citizen_confirmed_at DATETIME NULL,
            closed_at DATETIME NULL,
            idempotency_key VARCHAR(64) NULL UNIQUE,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT chk_complaints_attempts CHECK (completion_attempts >= 0),
            CONSTRAINT chk_complaints_reopens CHECK (reopen_count >= 0),
            CONSTRAINT fk_comp_citizen FOREIGN KEY (citizen_user_id) REFERENCES users(id) ON DELETE RESTRICT,
            CONSTRAINT fk_comp_creator FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
            CONSTRAINT fk_comp_category FOREIGN KEY (category_id) REFERENCES complaint_categories(id) ON DELETE RESTRICT,
            CONSTRAINT fk_comp_subcat FOREIGN KEY (subcategory_id) REFERENCES complaint_subcategories(id) ON DELETE RESTRICT,
            CONSTRAINT fk_comp_ward FOREIGN KEY (ward_id) REFERENCES wards(id) ON DELETE RESTRICT,
            CONSTRAINT fk_comp_zone FOREIGN KEY (zone_id) REFERENCES zones(id) ON DELETE RESTRICT,
            CONSTRAINT fk_comp_dept FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
            CONSTRAINT fk_comp_unit FOREIGN KEY (service_unit_id) REFERENCES service_units(id) ON DELETE SET NULL,
            CONSTRAINT fk_comp_sup FOREIGN KEY (current_supervisor_employee_id) REFERENCES employees(id) ON DELETE SET NULL,
            CONSTRAINT fk_comp_team FOREIGN KEY (current_team_id) REFERENCES teams(id) ON DELETE SET NULL,
            CONSTRAINT fk_comp_parent FOREIGN KEY (parent_complaint_id) REFERENCES complaints(id) ON DELETE SET NULL,
            INDEX idx_comp_internal_status (internal_status),
            INDEX idx_comp_citizen_status (citizen_status),
            INDEX idx_comp_submitted (submitted_at),
            INDEX idx_comp_deadline (deadline_at),
            INDEX idx_comp_ward_status (ward_id, internal_status),
            INDEX idx_comp_zone_status (zone_id, internal_status),
            INDEX idx_comp_dept_status (department_id, internal_status),
            INDEX idx_comp_deadline_status (deadline_at, internal_status),
            INDEX idx_comp_reopens (reopen_count)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 2. Complaint Locations Table (1:1 with Complaints, GPS + Public Obfuscation)
        $pdo->exec("CREATE TABLE IF NOT EXISTS complaint_locations (
            complaint_id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
            latitude DECIMAL(10,8) NOT NULL,
            longitude DECIMAL(11,8) NOT NULL,
            approximate_address TEXT NULL,
            landmark VARCHAR(191) NULL,
            public_safe_address VARCHAR(191) NULL,
            public_latitude DECIMAL(8,4) NULL,
            public_longitude DECIMAL(8,4) NULL,
            CONSTRAINT chk_loc_lat CHECK (latitude >= -90.0 AND latitude <= 90.0),
            CONSTRAINT chk_loc_lng CHECK (longitude >= -180.0 AND longitude <= 180.0),
            CONSTRAINT fk_loc_complaint FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE,
            INDEX idx_loc_coords (latitude, longitude)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 3. Complaint Media Table (Original vs Public Derivative, Camera Metadata)
        $pdo->exec("CREATE TABLE IF NOT EXISTS complaint_media (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            complaint_id BIGINT UNSIGNED NOT NULL,
            uploader_user_id BIGINT UNSIGNED NOT NULL,
            media_type VARCHAR(32) NOT NULL,
            original_file_path VARCHAR(255) NOT NULL,
            public_derivative_path VARCHAR(255) NULL,
            mime_type VARCHAR(64) NOT NULL,
            file_size_bytes INT UNSIGNED NOT NULL,
            device_timestamp DATETIME NULL,
            gps_latitude DECIMAL(10,8) NULL,
            gps_longitude DECIMAL(11,8) NULL,
            is_live_capture TINYINT(1) NOT NULL DEFAULT 0,
            moderation_status VARCHAR(32) NOT NULL DEFAULT 'pending',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_media_complaint FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE,
            CONSTRAINT fk_media_user FOREIGN KEY (uploader_user_id) REFERENCES users(id) ON DELETE RESTRICT,
            INDEX idx_media_complaint (complaint_id),
            INDEX idx_media_moderation (moderation_status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 4. Complaint Status History Table (Append-Only State Transitions)
        $pdo->exec("CREATE TABLE IF NOT EXISTS complaint_status_history (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            complaint_id BIGINT UNSIGNED NOT NULL,
            from_internal_status VARCHAR(64) NULL,
            to_internal_status VARCHAR(64) NOT NULL,
            from_citizen_status VARCHAR(64) NULL,
            to_citizen_status VARCHAR(64) NOT NULL,
            action_name VARCHAR(64) NOT NULL,
            actor_user_id BIGINT UNSIGNED NULL,
            reason TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_csh_complaint FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE,
            CONSTRAINT fk_csh_actor FOREIGN KEY (actor_user_id) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_csh_complaint (complaint_id),
            INDEX idx_csh_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 5. Complaint Ownership History Table (Append-Only Transfer Log)
        $pdo->exec("CREATE TABLE IF NOT EXISTS complaint_ownership_history (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            complaint_id BIGINT UNSIGNED NOT NULL,
            from_supervisor_employee_id BIGINT UNSIGNED NULL,
            to_supervisor_employee_id BIGINT UNSIGNED NOT NULL,
            from_department_id INT UNSIGNED NULL,
            to_department_id INT UNSIGNED NOT NULL,
            transfer_reason TEXT NULL,
            transferred_by_user_id BIGINT UNSIGNED NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_coh_complaint FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE,
            CONSTRAINT fk_coh_from_sup FOREIGN KEY (from_supervisor_employee_id) REFERENCES employees(id) ON DELETE RESTRICT,
            CONSTRAINT fk_coh_to_sup FOREIGN KEY (to_supervisor_employee_id) REFERENCES employees(id) ON DELETE RESTRICT,
            CONSTRAINT fk_coh_from_dept FOREIGN KEY (from_department_id) REFERENCES departments(id) ON DELETE RESTRICT,
            CONSTRAINT fk_coh_to_dept FOREIGN KEY (to_department_id) REFERENCES departments(id) ON DELETE RESTRICT,
            CONSTRAINT fk_coh_user FOREIGN KEY (transferred_by_user_id) REFERENCES users(id) ON DELETE RESTRICT,
            INDEX idx_coh_complaint (complaint_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 6. Complaint Supporters ('I am also affected')
        $pdo->exec("CREATE TABLE IF NOT EXISTS complaint_supporters (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            complaint_id BIGINT UNSIGNED NOT NULL,
            citizen_user_id BIGINT UNSIGNED NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_comp_supporter (complaint_id, citizen_user_id),
            CONSTRAINT fk_csup_complaint FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE,
            CONSTRAINT fk_csup_user FOREIGN KEY (citizen_user_id) REFERENCES users(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 7. Field Tasks Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS field_tasks (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            complaint_id BIGINT UNSIGNED NOT NULL,
            task_code VARCHAR(32) NOT NULL UNIQUE,
            assigned_team_id INT UNSIGNED NULL,
            assigned_worker_employee_id BIGINT UNSIGNED NULL,
            supervisor_employee_id BIGINT UNSIGNED NOT NULL,
            task_status VARCHAR(32) NOT NULL DEFAULT 'pending',
            failure_reason_code VARCHAR(64) NULL,
            failure_notes TEXT NULL,
            instructions TEXT NULL,
            started_at DATETIME NULL,
            completed_at DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_ft_complaint FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE,
            CONSTRAINT fk_ft_team FOREIGN KEY (assigned_team_id) REFERENCES teams(id) ON DELETE SET NULL,
            CONSTRAINT fk_ft_worker FOREIGN KEY (assigned_worker_employee_id) REFERENCES employees(id) ON DELETE SET NULL,
            CONSTRAINT fk_ft_sup FOREIGN KEY (supervisor_employee_id) REFERENCES employees(id) ON DELETE RESTRICT,
            INDEX idx_ft_complaint (complaint_id),
            INDEX idx_ft_status (task_status),
            INDEX idx_ft_worker (assigned_worker_employee_id),
            INDEX idx_ft_team (assigned_team_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 8. Field Task Assignments History Table (Historical Reassignment Log)
        $pdo->exec("CREATE TABLE IF NOT EXISTS field_task_assignments (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            field_task_id BIGINT UNSIGNED NOT NULL,
            assigned_team_id INT UNSIGNED NULL,
            assigned_worker_employee_id BIGINT UNSIGNED NULL,
            assigned_by_user_id BIGINT UNSIGNED NULL,
            assignment_notes TEXT NULL,
            effective_from DATETIME NOT NULL,
            effective_to DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT chk_fta_dates CHECK (effective_to IS NULL OR effective_to >= effective_from),
            CONSTRAINT fk_fta_task FOREIGN KEY (field_task_id) REFERENCES field_tasks(id) ON DELETE CASCADE,
            CONSTRAINT fk_fta_team FOREIGN KEY (assigned_team_id) REFERENCES teams(id) ON DELETE SET NULL,
            CONSTRAINT fk_fta_worker FOREIGN KEY (assigned_worker_employee_id) REFERENCES employees(id) ON DELETE SET NULL,
            CONSTRAINT fk_fta_user FOREIGN KEY (assigned_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_fta_task_dates (field_task_id, effective_from, effective_to)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 9. Task Evidence Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS task_evidence (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            field_task_id BIGINT UNSIGNED NOT NULL,
            media_id BIGINT UNSIGNED NOT NULL,
            evidence_stage VARCHAR(32) NOT NULL,
            device_timestamp DATETIME NULL,
            server_timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_te_task FOREIGN KEY (field_task_id) REFERENCES field_tasks(id) ON DELETE CASCADE,
            CONSTRAINT fk_te_media FOREIGN KEY (media_id) REFERENCES complaint_media(id) ON DELETE CASCADE,
            INDEX idx_te_task (field_task_id),
            INDEX idx_te_stage (evidence_stage)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 10. Support Requests Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS support_requests (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            complaint_id BIGINT UNSIGNED NOT NULL,
            field_task_id BIGINT UNSIGNED NULL,
            requested_by_employee_id BIGINT UNSIGNED NOT NULL,
            support_type VARCHAR(64) NOT NULL,
            target_department_id INT UNSIGNED NULL,
            details TEXT NOT NULL,
            status VARCHAR(32) NOT NULL DEFAULT 'pending',
            responded_by_user_id BIGINT UNSIGNED NULL,
            response_notes TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_sr_complaint FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE,
            CONSTRAINT fk_sr_task FOREIGN KEY (field_task_id) REFERENCES field_tasks(id) ON DELETE SET NULL,
            CONSTRAINT fk_sr_req_emp FOREIGN KEY (requested_by_employee_id) REFERENCES employees(id) ON DELETE RESTRICT,
            CONSTRAINT fk_sr_dept FOREIGN KEY (target_department_id) REFERENCES departments(id) ON DELETE SET NULL,
            CONSTRAINT fk_sr_resp_user FOREIGN KEY (responded_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_sr_complaint (complaint_id),
            INDEX idx_sr_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 11. Citizen Feedback Table (1:1 with Complaints, Post-Resolution Rating)
        $pdo->exec("CREATE TABLE IF NOT EXISTS citizen_feedback (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            complaint_id BIGINT UNSIGNED NOT NULL UNIQUE,
            citizen_user_id BIGINT UNSIGNED NOT NULL,
            resolution_confirmation VARCHAR(64) NOT NULL,
            unresolved_reason_code VARCHAR(64) NULL,
            rating_score TINYINT UNSIGNED NULL,
            comment TEXT NULL,
            confirmed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT chk_cf_rating CHECK (rating_score IS NULL OR (rating_score >= 1 AND rating_score <= 5)),
            CONSTRAINT fk_cf_complaint FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE,
            CONSTRAINT fk_cf_citizen FOREIGN KEY (citizen_user_id) REFERENCES users(id) ON DELETE RESTRICT,
            INDEX idx_cf_confirmation (resolution_confirmation)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS citizen_feedback;");
        $pdo->exec("DROP TABLE IF EXISTS support_requests;");
        $pdo->exec("DROP TABLE IF EXISTS task_evidence;");
        $pdo->exec("DROP TABLE IF EXISTS field_task_assignments;");
        $pdo->exec("DROP TABLE IF EXISTS field_tasks;");
        $pdo->exec("DROP TABLE IF EXISTS complaint_supporters;");
        $pdo->exec("DROP TABLE IF EXISTS complaint_ownership_history;");
        $pdo->exec("DROP TABLE IF EXISTS complaint_status_history;");
        $pdo->exec("DROP TABLE IF EXISTS complaint_media;");
        $pdo->exec("DROP TABLE IF EXISTS complaint_locations;");
        $pdo->exec("DROP TABLE IF EXISTS complaints;");
    }
};
