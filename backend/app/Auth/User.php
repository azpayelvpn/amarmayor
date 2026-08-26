<?php

declare(strict_types=1);

namespace AmarMayor\Auth;

use AmarMayor\Database\DatabaseManager;
use PDO;

class User
{
    public int $id;
    public string $uuid;
    public ?string $phone;
    public ?string $phoneLookupHash;
    public ?string $email;
    public ?string $passwordHash;
    public string $userType; // citizen, staff, representative, admin
    public string $status; // active, inactive, suspended
    public string $preferredLanguage;
    public ?string $mfaSecret;
    public ?string $mfaEnabledAt;
    public ?string $lastLoginAt;
    public string $createdAt;

    /** @var string[] Cached role slugs */
    private ?array $roleSlugs = null;

    /** @var string[] Cached permission slugs */
    private ?array $permissionSlugs = null;

    /** @var UserScope[] Cached user scopes */
    private ?array $scopes = null;

    public function __construct(array $attributes)
    {
        $this->id = (int)$attributes['id'];
        $this->uuid = (string)$attributes['uuid'];
        $this->phone = $attributes['phone'] ?? null;
        $this->phoneLookupHash = $attributes['phone_lookup_hash'] ?? null;
        $this->email = $attributes['email'] ?? null;
        $this->passwordHash = $attributes['password_hash'] ?? null;
        $this->userType = (string)($attributes['user_type'] ?? 'citizen');
        $this->status = (string)($attributes['status'] ?? 'active');
        $this->preferredLanguage = (string)($attributes['preferred_language'] ?? 'bn');
        $this->mfaSecret = $attributes['mfa_secret'] ?? null;
        $this->mfaEnabledAt = $attributes['mfa_enabled_at'] ?? null;
        $this->lastLoginAt = $attributes['last_login_at'] ?? null;
        $this->createdAt = (string)($attributes['created_at'] ?? date('Y-m-d H:i:s'));
    }

    public static function findById(int $id): ?self
    {
        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new self($row) : null;
    }

    public static function findByEmail(string $email): ?self
    {
        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([strtolower(trim($email))]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new self($row) : null;
    }

    public static function findByPhone(string $phone): ?self
    {
        $hash = \AmarMayor\Support\Security::phoneLookupHash($phone);
        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE phone_lookup_hash = ? OR phone = ? LIMIT 1");
        $stmt->execute([$hash, $phone]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new self($row) : null;
    }

    public static function findByUuid(string $uuid): ?self
    {
        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE uuid = ? LIMIT 1");
        $stmt->execute([$uuid]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new self($row) : null;
    }

    public function getRoleSlugs(): array
    {
        if ($this->roleSlugs !== null) {
            return $this->roleSlugs;
        }

        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("
            SELECT r.slug 
            FROM roles r 
            INNER JOIN user_roles ur ON ur.role_id = r.id 
            WHERE ur.user_id = ?
        ");
        $stmt->execute([$this->id]);
        $this->roleSlugs = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

        return $this->roleSlugs;
    }

    public function hasRole(string|array $roles): bool
    {
        $userRoles = $this->getRoleSlugs();
        $checkRoles = is_array($roles) ? $roles : [$roles];

        foreach ($checkRoles as $r) {
            if (in_array($r, $userRoles, true)) {
                return true;
            }
        }

        return false;
    }

    public function getPermissionSlugs(): array
    {
        if ($this->permissionSlugs !== null) {
            return $this->permissionSlugs;
        }

        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("
            SELECT DISTINCT p.slug 
            FROM permissions p
            INNER JOIN role_permissions rp ON rp.permission_id = p.id
            INNER JOIN user_roles ur ON ur.role_id = rp.role_id
            WHERE ur.user_id = ?
        ");
        $stmt->execute([$this->id]);
        $this->permissionSlugs = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

        return $this->permissionSlugs;
    }

    public function can(string $permission): bool
    {
        // Technical & Platform Super Admins have all permissions
        if ($this->hasRole(['platform_super_admin', 'technical_super_admin'])) {
            return true;
        }

        return in_array($permission, $this->getPermissionSlugs(), true);
    }

    /**
     * @return UserScope[]
     */
    public function getScopes(): array
    {
        if ($this->scopes !== null) {
            return $this->scopes;
        }

        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM user_scopes 
            WHERE user_id = ? 
              AND (effective_to IS NULL OR effective_to >= NOW())
              AND effective_from <= NOW()
            ORDER BY id ASC
        ");
        $stmt->execute([$this->id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->scopes = array_map(fn($r) => new UserScope($r), $rows);
        return $this->scopes;
    }

    public function getPrimaryScope(): ?UserScope
    {
        $scopes = $this->getScopes();
        return $scopes[0] ?? null;
    }

    public function toArray(bool $includePrivate = false): array
    {
        $data = [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'user_type' => $this->userType,
            'status' => $this->status,
            'preferred_language' => $this->preferredLanguage,
            'roles' => $this->getRoleSlugs(),
            'created_at' => $this->createdAt,
        ];

        if ($includePrivate) {
            $data['phone'] = $this->phone;
            $data['email'] = $this->email;
            $data['last_login_at'] = $this->lastLoginAt;
        }

        return $data;
    }
}
