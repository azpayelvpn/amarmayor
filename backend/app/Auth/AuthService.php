<?php

declare(strict_types=1);

namespace AmarMayor\Auth;

use AmarMayor\Auth\Otp\DevOtpInboxService;
use AmarMayor\Auth\Otp\MockOtpProvider;
use AmarMayor\Auth\Otp\OtpProviderInterface;
use AmarMayor\Auth\Otp\SmsGatewayProvider;
use AmarMayor\Database\DatabaseManager;
use AmarMayor\Support\Config;
use AmarMayor\Support\RedisClient;
use AmarMayor\Support\Security;
use PDO;

class AuthService
{
    private const OTP_TTL_SECONDS = 300; // 5 Minutes
    private const OTP_RATE_LIMIT_MAX = 5; // Max 5 requests per 10 minutes
    private const TOKEN_LIFETIME_DAYS = 30;

    private OtpProviderInterface $otpProvider;

    public function __construct(?OtpProviderInterface $otpProvider = null)
    {
        if ($otpProvider !== null) {
            $this->otpProvider = $otpProvider;
        } else {
            $driver = Config::get('services.sms_driver', 'mock');
            $this->otpProvider = ($driver === 'gateway') ? new SmsGatewayProvider() : new MockOtpProvider();
        }
    }

    /**
     * Authenticates a user by email or phone with password (Staff / Officer / Admin).
     */
    public function authenticateWithPassword(string $identifier, string $password): ?User
    {
        $identifier = trim($identifier);
        $user = null;

        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $user = User::findByEmail($identifier);
        } else {
            $normalizedPhone = Security::normalizePhone($identifier);
            if ($normalizedPhone) {
                $user = User::findByPhone($normalizedPhone);
            }
        }

        if (!$user || !$user->passwordHash) {
            return null;
        }

        if ($user->status !== 'active') {
            return null;
        }

        if (!Security::verifyPassword($password, $user->passwordHash)) {
            return null;
        }

        // Update last login
        $pdo = DatabaseManager::getConnection();
        $pdo->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?")->execute([$user->id]);
        $user->lastLoginAt = date('Y-m-d H:i:s');

        return $user;
    }

    /**
     * Checks if a user requires multi-factor authentication (Mayor, Administrator, CEO, Super Admins).
     */
    public function requiresMfa(User $user): bool
    {
        $privilegedRoles = [
            'mayor',
            'administrator',
            'ceo',
            'platform_super_admin',
            'technical_super_admin',
        ];

        return $user->hasRole($privilegedRoles);
    }

    /**
     * Requests a 6-digit OTP for citizen phone authentication.
     *
     * @return array{success: bool, message: string, mock_otp?: string}
     */
    public function requestOtp(string $rawPhone, ?string $ipAddress = null): array
    {
        $normalizedPhone = Security::normalizePhone($rawPhone);
        if (!$normalizedPhone) {
            return [
                'success' => false,
                'message' => 'invalid_phone_format',
            ];
        }

        // Rate limiting check
        $rateLimitKey = "otp_rate_limit:{$normalizedPhone}";
        $attempts = (int)RedisClient::get($rateLimitKey);
        if ($attempts >= self::OTP_RATE_LIMIT_MAX) {
            return [
                'success' => false,
                'message' => 'rate_limited',
            ];
        }

        // Generate 6-digit numeric OTP
        $otp = (string)random_int(100000, 999999);
        $otpStorageKey = "otp:{$normalizedPhone}";

        RedisClient::set($otpStorageKey, $otp, self::OTP_TTL_SECONDS);
        RedisClient::set($rateLimitKey, (string)($attempts + 1), 600); // 10 minute window

        // Send OTP via configured provider
        $sent = $this->otpProvider->sendOtp($normalizedPhone, $otp);
        if (!$sent) {
            return [
                'success' => false,
                'message' => 'sms_not_configured',
            ];
        }

        $response = [
            'success' => true,
            'message' => 'otp_sent',
        ];

        // In local or test environment, return mock_otp for automated testing
        if (Config::get('app.env') !== 'production') {
            $response['mock_otp'] = $otp;
        }

        return $response;
    }

    /**
     * Verifies citizen OTP and returns the authenticated User instance.
     */
    public function verifyOtp(string $rawPhone, string $otpCode): ?User
    {
        $normalizedPhone = Security::normalizePhone($rawPhone);
        if (!$normalizedPhone) {
            return null;
        }

        $otpStorageKey = "otp:{$normalizedPhone}";
        $cachedOtp = RedisClient::get($otpStorageKey);

        if (!$cachedOtp || !Security::timingSafeEquals($cachedOtp, trim($otpCode))) {
            return null;
        }

        // Invalidate OTP immediately after successful verification
        RedisClient::delete($otpStorageKey);

        // Mark OTP used in Dev Inbox if in dev mode
        DevOtpInboxService::markUsed($normalizedPhone, trim($otpCode));

        // Find or create citizen user
        $user = User::findByPhone($normalizedPhone);
        $pdo = DatabaseManager::getConnection();

        if (!$user) {
            $uuid = Security::uuid();
            $lookupHash = Security::phoneLookupHash($normalizedPhone);

            $stmt = $pdo->prepare("
                INSERT INTO users (uuid, phone, phone_lookup_hash, user_type, status, preferred_language, last_login_at, created_at)
                VALUES (?, ?, ?, 'citizen', 'active', 'bn', NOW(), NOW())
            ");
            $stmt->execute([$uuid, $normalizedPhone, $lookupHash]);
            $userId = (int)$pdo->lastInsertId();

            // Assign default 'citizen' role
            $roleId = (int)$pdo->query("SELECT id FROM roles WHERE slug = 'citizen'")->fetchColumn();
            if ($roleId) {
                $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)")->execute([$userId, $roleId]);
            }

            $user = User::findById($userId);
        } else {
            $pdo->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?")->execute([$user->id]);
            $user->lastLoginAt = date('Y-m-d H:i:s');
        }

        return $user;
    }

    /**
     * Issues an API Bearer token for mobile or headless API clients.
     */
    public function issueApiToken(User $user, string $deviceName = 'Mobile App', ?string $deviceId = null): string
    {
        $plainToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $plainToken);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . self::TOKEN_LIFETIME_DAYS . ' days'));

        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO user_tokens (user_id, token_hash, device_id, device_name, expires_at, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$user->id, $tokenHash, $deviceId, $deviceName, $expiresAt]);

        return $plainToken;
    }

    /**
     * Authenticates an API request via Bearer token.
     */
    public function authenticateWithToken(string $plainToken): ?User
    {
        $tokenHash = hash('sha256', trim($plainToken));
        $pdo = DatabaseManager::getConnection();

        $stmt = $pdo->prepare("
            SELECT ut.user_id, ut.id as token_id, ut.expires_at
            FROM user_tokens ut
            WHERE ut.token_hash = ? AND ut.expires_at > NOW() AND ut.revoked_at IS NULL
            LIMIT 1
        ");
        $stmt->execute([$tokenHash]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return User::findById((int)$row['user_id']);
    }

    /**
     * Revokes an API token.
     */
    public function revokeApiToken(string $plainToken): void
    {
        $tokenHash = hash('sha256', trim($plainToken));
        $pdo = DatabaseManager::getConnection();
        $pdo->prepare("UPDATE user_tokens SET revoked_at = NOW() WHERE token_hash = ?")->execute([$tokenHash]);
    }
}
