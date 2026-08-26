<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        // 1. Users Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            uuid CHAR(36) NOT NULL UNIQUE,
            phone VARCHAR(20) NULL UNIQUE,
            phone_lookup_hash CHAR(64) NULL,
            email VARCHAR(191) NULL UNIQUE,
            password_hash VARCHAR(255) NULL,
            user_type VARCHAR(32) NOT NULL DEFAULT 'citizen',
            status VARCHAR(32) NOT NULL DEFAULT 'active',
            preferred_language VARCHAR(5) NOT NULL DEFAULT 'bn',
            mfa_secret VARCHAR(255) NULL,
            mfa_enabled_at DATETIME NULL,
            last_login_at DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_users_phone_lookup (phone_lookup_hash),
            INDEX idx_users_user_type (user_type),
            INDEX idx_users_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 2. Roles Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS roles (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(64) NOT NULL UNIQUE,
            name_bn VARCHAR(128) NOT NULL,
            name_en VARCHAR(128) NOT NULL,
            is_system TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 3. Permissions Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS permissions (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(100) NOT NULL UNIQUE,
            category VARCHAR(64) NOT NULL,
            description VARCHAR(255) NULL,
            INDEX idx_permissions_category (category)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 4. Role Permissions Mapping
        $pdo->exec("CREATE TABLE IF NOT EXISTS role_permissions (
            role_id INT UNSIGNED NOT NULL,
            permission_id INT UNSIGNED NOT NULL,
            PRIMARY KEY (role_id, permission_id),
            CONSTRAINT fk_rp_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
            CONSTRAINT fk_rp_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 5. User Roles Mapping
        $pdo->exec("CREATE TABLE IF NOT EXISTS user_roles (
            user_id BIGINT UNSIGNED NOT NULL,
            role_id INT UNSIGNED NOT NULL,
            PRIMARY KEY (user_id, role_id),
            CONSTRAINT fk_ur_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            CONSTRAINT fk_ur_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 6. User Scopes
        $pdo->exec("CREATE TABLE IF NOT EXISTS user_scopes (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            scope_type VARCHAR(32) NOT NULL,
            scope_id BIGINT UNSIGNED NULL,
            effective_from DATETIME NOT NULL,
            effective_to DATETIME NULL,
            CONSTRAINT chk_user_scopes_dates CHECK (effective_to IS NULL OR effective_to >= effective_from),
            CONSTRAINT fk_us_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_us_user_lookup (user_id, scope_type, scope_id, effective_from, effective_to)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 7. User Tokens
        $pdo->exec("CREATE TABLE IF NOT EXISTS user_tokens (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            token_hash CHAR(64) NOT NULL UNIQUE,
            device_name VARCHAR(128) NULL,
            device_id VARCHAR(128) NULL,
            ip_address VARCHAR(45) NULL,
            user_agent VARCHAR(255) NULL,
            expires_at DATETIME NOT NULL,
            revoked_at DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_ut_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_ut_device (device_id),
            INDEX idx_ut_expires (expires_at),
            INDEX idx_ut_revoked (revoked_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS user_tokens;");
        $pdo->exec("DROP TABLE IF EXISTS user_scopes;");
        $pdo->exec("DROP TABLE IF EXISTS user_roles;");
        $pdo->exec("DROP TABLE IF EXISTS role_permissions;");
        $pdo->exec("DROP TABLE IF EXISTS permissions;");
        $pdo->exec("DROP TABLE IF EXISTS roles;");
        $pdo->exec("DROP TABLE IF EXISTS users;");
    }
};
