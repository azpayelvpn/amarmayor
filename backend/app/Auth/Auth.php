<?php

declare(strict_types=1);

namespace AmarMayor\Auth;

use AmarMayor\Http\Request;
use AmarMayor\Support\Container;

class Auth
{
    private static ?User $user = null;

    public static function setUser(?User $user): void
    {
        self::$user = $user;
    }

    public static function user(): ?User
    {
        if (self::$user !== null) {
            return self::$user;
        }

        // Check active session
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user_id'])) {
            self::$user = User::findById((int)$_SESSION['user_id']);
        }

        return self::$user;
    }

    public static function id(): ?int
    {
        $u = self::user();
        return $u?->id;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function hasRole(string|array $roles): bool
    {
        $u = self::user();
        return $u ? $u->hasRole($roles) : false;
    }

    public static function can(string $permission): bool
    {
        $u = self::user();
        return $u ? $u->can($permission) : false;
    }

    public static function login(User $user): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            @session_regenerate_id(true);
        }

        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_uuid'] = $user->uuid;
        $_SESSION['user_type'] = $user->userType;
        $_SESSION['locale'] = $user->preferredLanguage;

        self::$user = $user;
    }

    public static function logout(): void
    {
        self::$user = null;

        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params["path"],
                    $params["domain"],
                    $params["secure"],
                    $params["httponly"]
                );
            }
            session_destroy();
        }
    }
}
