<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        // 1. Complaint Categories Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS complaint_categories (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(64) NOT NULL UNIQUE,
            name_bn VARCHAR(128) NOT NULL,
            name_en VARCHAR(128) NOT NULL,
            icon_name VARCHAR(64) NULL,
            display_order INT UNSIGNED NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_cc_active (is_active, display_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 2. Operational Classifications Table (Configurable)
        $pdo->exec("CREATE TABLE IF NOT EXISTS operational_classifications (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(64) NOT NULL UNIQUE,
            name_bn VARCHAR(128) NOT NULL,
            name_en VARCHAR(128) NOT NULL,
            description_bn TEXT NULL,
            description_en TEXT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 3. Priorities Table (Configurable)
        $pdo->exec("CREATE TABLE IF NOT EXISTS priorities (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(32) NOT NULL UNIQUE,
            name_bn VARCHAR(128) NOT NULL,
            name_en VARCHAR(128) NOT NULL,
            display_order INT UNSIGNED NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 4. Complaint Subcategories Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS complaint_subcategories (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            category_id INT UNSIGNED NOT NULL,
            slug VARCHAR(64) NOT NULL,
            name_bn VARCHAR(128) NOT NULL,
            name_en VARCHAR(128) NOT NULL,
            default_priority VARCHAR(32) NOT NULL DEFAULT 'p3_normal',
            default_classification VARCHAR(64) NOT NULL DEFAULT 'quick_action',
            requires_live_camera TINYINT(1) NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_cat_subcat_slug (category_id, slug),
            CONSTRAINT fk_csc_category FOREIGN KEY (category_id) REFERENCES complaint_categories(id) ON DELETE RESTRICT,
            INDEX idx_csc_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 5. Service Deadline Rules Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS service_deadline_rules (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            category_id INT UNSIGNED NULL,
            subcategory_id INT UNSIGNED NULL,
            priority VARCHAR(32) NULL,
            operational_classification VARCHAR(64) NULL,
            expected_hours INT UNSIGNED NOT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_sdr_category FOREIGN KEY (category_id) REFERENCES complaint_categories(id) ON DELETE CASCADE,
            CONSTRAINT fk_sdr_subcategory FOREIGN KEY (subcategory_id) REFERENCES complaint_subcategories(id) ON DELETE CASCADE,
            INDEX idx_sdr_lookup (category_id, subcategory_id, priority, is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 6. Routing Rules Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS routing_rules (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            category_id INT UNSIGNED NOT NULL,
            subcategory_id INT UNSIGNED NULL,
            ward_id INT UNSIGNED NULL,
            department_id INT UNSIGNED NOT NULL,
            service_unit_id INT UNSIGNED NULL,
            assigned_supervisor_employee_id BIGINT UNSIGNED NULL,
            assigned_team_id INT UNSIGNED NULL,
            effective_from DATETIME NOT NULL,
            effective_to DATETIME NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT chk_rr_dates CHECK (effective_to IS NULL OR effective_to >= effective_from),
            CONSTRAINT fk_rr_category FOREIGN KEY (category_id) REFERENCES complaint_categories(id) ON DELETE RESTRICT,
            CONSTRAINT fk_rr_subcat FOREIGN KEY (subcategory_id) REFERENCES complaint_subcategories(id) ON DELETE SET NULL,
            CONSTRAINT fk_rr_ward FOREIGN KEY (ward_id) REFERENCES wards(id) ON DELETE SET NULL,
            CONSTRAINT fk_rr_dept FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE RESTRICT,
            CONSTRAINT fk_rr_unit FOREIGN KEY (service_unit_id) REFERENCES service_units(id) ON DELETE SET NULL,
            CONSTRAINT fk_rr_sup FOREIGN KEY (assigned_supervisor_employee_id) REFERENCES employees(id) ON DELETE SET NULL,
            CONSTRAINT fk_rr_team FOREIGN KEY (assigned_team_id) REFERENCES teams(id) ON DELETE SET NULL,
            INDEX idx_rr_lookup (category_id, subcategory_id, ward_id, is_active, effective_from, effective_to)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS routing_rules;");
        $pdo->exec("DROP TABLE IF EXISTS service_deadline_rules;");
        $pdo->exec("DROP TABLE IF EXISTS complaint_subcategories;");
        $pdo->exec("DROP TABLE IF EXISTS priorities;");
        $pdo->exec("DROP TABLE IF EXISTS operational_classifications;");
        $pdo->exec("DROP TABLE IF EXISTS complaint_categories;");
    }
};
