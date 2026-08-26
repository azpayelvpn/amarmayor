<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        // 1. Departments Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS departments (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            city_id INT UNSIGNED NOT NULL,
            slug VARCHAR(64) NOT NULL,
            name_bn VARCHAR(128) NOT NULL,
            name_en VARCHAR(128) NOT NULL,
            description_bn TEXT NULL,
            description_en TEXT NULL,
            official_phone VARCHAR(32) NULL,
            official_email VARCHAR(128) NULL,
            status VARCHAR(32) NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_city_department_slug (city_id, slug),
            CONSTRAINT fk_dept_city FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 2. Service Units Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS service_units (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            department_id INT UNSIGNED NOT NULL,
            slug VARCHAR(64) NOT NULL,
            name_bn VARCHAR(128) NOT NULL,
            name_en VARCHAR(128) NOT NULL,
            status VARCHAR(32) NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_dept_unit_slug (department_id, slug),
            CONSTRAINT fk_su_dept FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 3. Employees Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS employees (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            person_id BIGINT UNSIGNED NOT NULL UNIQUE,
            employee_code VARCHAR(32) NOT NULL UNIQUE,
            official_service_no VARCHAR(64) NULL,
            employment_type VARCHAR(64) NOT NULL DEFAULT 'permanent',
            designation_bn VARCHAR(128) NOT NULL,
            designation_en VARCHAR(128) NOT NULL,
            joining_date DATE NULL,
            duty_status VARCHAR(32) NOT NULL DEFAULT 'available',
            reports_to_employee_id BIGINT UNSIGNED NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_emp_person FOREIGN KEY (person_id) REFERENCES persons(id) ON DELETE RESTRICT,
            CONSTRAINT fk_emp_reports FOREIGN KEY (reports_to_employee_id) REFERENCES employees(id) ON DELETE SET NULL,
            INDEX idx_emp_duty_status (duty_status),
            INDEX idx_emp_type (employment_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 4. Employee Postings (Historical & Current Postings)
        $pdo->exec("CREATE TABLE IF NOT EXISTS employee_postings (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            employee_id BIGINT UNSIGNED NOT NULL,
            department_id INT UNSIGNED NOT NULL,
            service_unit_id INT UNSIGNED NULL,
            office_id INT UNSIGNED NULL,
            posting_type VARCHAR(32) NOT NULL DEFAULT 'regular',
            effective_from DATETIME NOT NULL,
            effective_to DATETIME NULL,
            transfer_order_ref VARCHAR(128) NULL,
            transfer_reason TEXT NULL,
            authorized_by_user_id BIGINT UNSIGNED NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT chk_ep_dates CHECK (effective_to IS NULL OR effective_to >= effective_from),
            CONSTRAINT fk_ep_emp FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE RESTRICT,
            CONSTRAINT fk_ep_dept FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE RESTRICT,
            CONSTRAINT fk_ep_unit FOREIGN KEY (service_unit_id) REFERENCES service_units(id) ON DELETE SET NULL,
            CONSTRAINT fk_ep_office FOREIGN KEY (office_id) REFERENCES offices(id) ON DELETE SET NULL,
            CONSTRAINT fk_ep_user FOREIGN KEY (authorized_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_ep_emp_dates (employee_id, effective_from, effective_to),
            INDEX idx_ep_dept (department_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 5. Employee Responsibilities (Geographic & Functional Scopes)
        $pdo->exec("CREATE TABLE IF NOT EXISTS employee_responsibilities (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            employee_id BIGINT UNSIGNED NOT NULL,
            area_type VARCHAR(32) NOT NULL,
            area_id BIGINT UNSIGNED NULL,
            service_unit_id INT UNSIGNED NULL,
            effective_from DATETIME NOT NULL,
            effective_to DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT chk_er_dates CHECK (effective_to IS NULL OR effective_to >= effective_from),
            CONSTRAINT fk_er_emp FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE RESTRICT,
            CONSTRAINT fk_er_unit FOREIGN KEY (service_unit_id) REFERENCES service_units(id) ON DELETE SET NULL,
            INDEX idx_er_lookup (employee_id, area_type, area_id, effective_from, effective_to)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 6. Skills Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS skills (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(64) NOT NULL UNIQUE,
            name_bn VARCHAR(128) NOT NULL,
            name_en VARCHAR(128) NOT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 7. Employee Skills Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS employee_skills (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            employee_id BIGINT UNSIGNED NOT NULL,
            skill_id INT UNSIGNED NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_emp_skill (employee_id, skill_id),
            CONSTRAINT fk_es_emp FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
            CONSTRAINT fk_es_skill FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 8. Teams Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS teams (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            department_id INT UNSIGNED NOT NULL,
            service_unit_id INT UNSIGNED NULL,
            zone_id INT UNSIGNED NULL,
            ward_id INT UNSIGNED NULL,
            name_bn VARCHAR(128) NOT NULL,
            name_en VARCHAR(128) NOT NULL,
            supervisor_employee_id BIGINT UNSIGNED NULL,
            team_leader_employee_id BIGINT UNSIGNED NULL,
            status VARCHAR(32) NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_teams_dept FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE RESTRICT,
            CONSTRAINT fk_teams_unit FOREIGN KEY (service_unit_id) REFERENCES service_units(id) ON DELETE SET NULL,
            CONSTRAINT fk_teams_zone FOREIGN KEY (zone_id) REFERENCES zones(id) ON DELETE SET NULL,
            CONSTRAINT fk_teams_ward FOREIGN KEY (ward_id) REFERENCES wards(id) ON DELETE SET NULL,
            CONSTRAINT fk_teams_sup FOREIGN KEY (supervisor_employee_id) REFERENCES employees(id) ON DELETE SET NULL,
            CONSTRAINT fk_teams_lead FOREIGN KEY (team_leader_employee_id) REFERENCES employees(id) ON DELETE SET NULL,
            INDEX idx_teams_ward (ward_id),
            INDEX idx_teams_sup (supervisor_employee_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 9. Team Members Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS team_members (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            team_id INT UNSIGNED NOT NULL,
            employee_id BIGINT UNSIGNED NOT NULL,
            effective_from DATETIME NOT NULL,
            effective_to DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT chk_tm_dates CHECK (effective_to IS NULL OR effective_to >= effective_from),
            CONSTRAINT fk_tm_team FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
            CONSTRAINT fk_tm_emp FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE RESTRICT,
            UNIQUE KEY uq_team_emp_period (team_id, employee_id, effective_from),
            INDEX idx_tm_emp_lookup (employee_id, effective_from, effective_to)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS team_members;");
        $pdo->exec("DROP TABLE IF EXISTS teams;");
        $pdo->exec("DROP TABLE IF EXISTS employee_skills;");
        $pdo->exec("DROP TABLE IF EXISTS skills;");
        $pdo->exec("DROP TABLE IF EXISTS employee_responsibilities;");
        $pdo->exec("DROP TABLE IF EXISTS employee_postings;");
        $pdo->exec("DROP TABLE IF EXISTS employees;");
        $pdo->exec("DROP TABLE IF EXISTS service_units;");
        $pdo->exec("DROP TABLE IF EXISTS departments;");
    }
};
