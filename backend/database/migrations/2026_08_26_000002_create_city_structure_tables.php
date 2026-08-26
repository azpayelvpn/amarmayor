<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        // 1. Cities Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS cities (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(64) NOT NULL UNIQUE,
            name_bn VARCHAR(128) NOT NULL,
            name_en VARCHAR(128) NOT NULL,
            short_name_bn VARCHAR(32) NOT NULL,
            short_name_en VARCHAR(32) NOT NULL,
            logo_url VARCHAR(255) NULL,
            official_address_bn TEXT NULL,
            official_address_en TEXT NULL,
            official_phone VARCHAR(32) NULL,
            official_email VARCHAR(128) NULL,
            website VARCHAR(128) NULL,
            service_hours VARCHAR(128) NULL,
            timezone VARCHAR(64) NOT NULL DEFAULT 'Asia/Dhaka',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 2. Zones Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS zones (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            city_id INT UNSIGNED NOT NULL,
            zone_number INT UNSIGNED NOT NULL,
            name_bn VARCHAR(128) NOT NULL,
            name_en VARCHAR(128) NOT NULL,
            office_address TEXT NULL,
            official_phone VARCHAR(32) NULL,
            official_email VARCHAR(128) NULL,
            status VARCHAR(32) NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_city_zone_number (city_id, zone_number),
            CONSTRAINT fk_zones_city FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 3. Wards Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS wards (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            city_id INT UNSIGNED NOT NULL,
            zone_id INT UNSIGNED NOT NULL,
            ward_number INT UNSIGNED NOT NULL,
            name_bn VARCHAR(128) NOT NULL,
            name_en VARCHAR(128) NOT NULL,
            area_names_bn TEXT NULL,
            area_names_en TEXT NULL,
            approximate_area_sqkm DECIMAL(8,3) NULL,
            verified_population INT UNSIGNED NULL,
            household_count INT UNSIGNED NULL,
            office_address TEXT NULL,
            official_contact VARCHAR(64) NULL,
            boundary_geojson LONGTEXT NULL,
            status VARCHAR(32) NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_city_ward_number (city_id, ward_number),
            CONSTRAINT fk_wards_city FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE RESTRICT,
            CONSTRAINT fk_wards_zone FOREIGN KEY (zone_id) REFERENCES zones(id) ON DELETE RESTRICT,
            INDEX idx_wards_zone_status (zone_id, status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 4. Ward-Zone History Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS ward_zone_history (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            ward_id INT UNSIGNED NOT NULL,
            zone_id INT UNSIGNED NOT NULL,
            effective_from DATETIME NOT NULL,
            effective_to DATETIME NULL,
            authority_order VARCHAR(128) NULL,
            changed_by_user_id BIGINT UNSIGNED NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT chk_wzh_dates CHECK (effective_to IS NULL OR effective_to >= effective_from),
            CONSTRAINT fk_wzh_ward FOREIGN KEY (ward_id) REFERENCES wards(id) ON DELETE RESTRICT,
            CONSTRAINT fk_wzh_zone FOREIGN KEY (zone_id) REFERENCES zones(id) ON DELETE RESTRICT,
            CONSTRAINT fk_wzh_user FOREIGN KEY (changed_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_wzh_ward_dates (ward_id, effective_from, effective_to)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 5. Offices Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS offices (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            city_id INT UNSIGNED NOT NULL,
            office_type VARCHAR(64) NOT NULL,
            zone_id INT UNSIGNED NULL,
            ward_id INT UNSIGNED NULL,
            name_bn VARCHAR(128) NOT NULL,
            name_en VARCHAR(128) NOT NULL,
            address TEXT NULL,
            official_phone VARCHAR(32) NULL,
            official_email VARCHAR(128) NULL,
            office_hours VARCHAR(128) NULL,
            is_public_visible TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_offices_city FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE RESTRICT,
            CONSTRAINT fk_offices_zone FOREIGN KEY (zone_id) REFERENCES zones(id) ON DELETE SET NULL,
            CONSTRAINT fk_offices_ward FOREIGN KEY (ward_id) REFERENCES wards(id) ON DELETE SET NULL,
            INDEX idx_offices_type (office_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 6. Reserved Seats Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS reserved_seats (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            city_id INT UNSIGNED NOT NULL,
            seat_number INT UNSIGNED NOT NULL,
            name_bn VARCHAR(128) NOT NULL,
            name_en VARCHAR(128) NOT NULL,
            status VARCHAR(32) NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_city_seat_number (city_id, seat_number),
            CONSTRAINT fk_rs_city FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 7. Reserved Seat Wards Mapping Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS reserved_seat_wards (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            reserved_seat_id INT UNSIGNED NOT NULL,
            ward_id INT UNSIGNED NOT NULL,
            effective_from DATETIME NOT NULL,
            effective_to DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT chk_rsw_dates CHECK (effective_to IS NULL OR effective_to >= effective_from),
            CONSTRAINT fk_rsw_seat FOREIGN KEY (reserved_seat_id) REFERENCES reserved_seats(id) ON DELETE RESTRICT,
            CONSTRAINT fk_rsw_ward FOREIGN KEY (ward_id) REFERENCES wards(id) ON DELETE RESTRICT,
            UNIQUE KEY uq_seat_ward_period (reserved_seat_id, ward_id, effective_from),
            INDEX idx_rsw_ward_lookup (ward_id, effective_from, effective_to)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS reserved_seat_wards;");
        $pdo->exec("DROP TABLE IF EXISTS reserved_seats;");
        $pdo->exec("DROP TABLE IF EXISTS offices;");
        $pdo->exec("DROP TABLE IF EXISTS ward_zone_history;");
        $pdo->exec("DROP TABLE IF EXISTS wards;");
        $pdo->exec("DROP TABLE IF EXISTS zones;");
        $pdo->exec("DROP TABLE IF EXISTS cities;");
    }
};
