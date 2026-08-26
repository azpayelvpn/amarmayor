<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        // 1. Persons Table (Real human entity distinct from users and employees)
        $pdo->exec("CREATE TABLE IF NOT EXISTS persons (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NULL UNIQUE,
            full_name_bn VARCHAR(128) NOT NULL,
            full_name_en VARCHAR(128) NOT NULL,
            photo_url VARCHAR(255) NULL,
            official_phone VARCHAR(32) NULL,
            official_email VARCHAR(128) NULL,
            is_public_visible TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_persons_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_persons_visibility (is_public_visible)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 2. Representation Types Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS representation_types (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(64) NOT NULL UNIQUE,
            name_bn VARCHAR(128) NOT NULL,
            name_en VARCHAR(128) NOT NULL,
            is_electoral TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 3. Representation Assignments Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS representation_assignments (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            person_id BIGINT UNSIGNED NOT NULL,
            representation_type_id INT UNSIGNED NOT NULL,
            authority_basis VARCHAR(32) NOT NULL DEFAULT 'elected',
            official_order_no VARCHAR(128) NULL,
            order_document_url VARCHAR(255) NULL,
            effective_from DATETIME NOT NULL,
            effective_to DATETIME NULL,
            status VARCHAR(32) NOT NULL DEFAULT 'active',
            created_by_user_id BIGINT UNSIGNED NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT chk_ra_dates CHECK (effective_to IS NULL OR effective_to >= effective_from),
            CONSTRAINT fk_ra_person FOREIGN KEY (person_id) REFERENCES persons(id) ON DELETE RESTRICT,
            CONSTRAINT fk_ra_type FOREIGN KEY (representation_type_id) REFERENCES representation_types(id) ON DELETE RESTRICT,
            CONSTRAINT fk_ra_user FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_ra_person (person_id),
            INDEX idx_ra_type_status (representation_type_id, status),
            INDEX idx_ra_effective (effective_from, effective_to)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 4. Representation Areas Table (Supports single ward, multi-ward, reserved seat, or citywide coverage)
        $pdo->exec("CREATE TABLE IF NOT EXISTS representation_areas (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            representation_assignment_id BIGINT UNSIGNED NOT NULL,
            area_type VARCHAR(32) NOT NULL,
            area_id BIGINT UNSIGNED NULL,
            CONSTRAINT fk_rarea_assignment FOREIGN KEY (representation_assignment_id) REFERENCES representation_assignments(id) ON DELETE CASCADE,
            INDEX idx_rarea_lookup (area_type, area_id),
            INDEX idx_rarea_assignment (representation_assignment_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS representation_areas;");
        $pdo->exec("DROP TABLE IF EXISTS representation_assignments;");
        $pdo->exec("DROP TABLE IF EXISTS representation_types;");
        $pdo->exec("DROP TABLE IF EXISTS persons;");
    }
};
