<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Unit\Auth;

use AmarMayor\Auth\AuthService;
use AmarMayor\Auth\User;
use AmarMayor\Database\DatabaseManager;
use AmarMayor\Support\Security;
use AmarMayor\Tests\TestCase;
use PDO;

class AuthServiceTest extends TestCase
{
    private AuthService $authService;

    public function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthService();
    }

    public function testPasswordAuthentication(): void
    {
        $pdo = DatabaseManager::getConnection();
        $suffix = bin2hex(random_bytes(4));
        $email = "officer_{$suffix}@amarmayor.gov.bd";
        $password = "SecretPassword123!";
        $passwordHash = Security::hashPassword($password);
        $uuid = Security::uuid();

        $userId = 0;
        try {
            $pdo->prepare("
                INSERT INTO users (uuid, email, password_hash, user_type, status, preferred_language, created_at)
                VALUES (?, ?, ?, 'staff', 'active', 'bn', NOW())
            ")->execute([$uuid, $email, $passwordHash]);
            $userId = (int)$pdo->lastInsertId();

            // Successful authentication
            $user = $this->authService->authenticateWithPassword($email, $password);
            $this->assertNotNull($user, "Valid credentials must authenticate user");
            $this->assertEquals($userId, $user->id);
            $this->assertEquals('staff', $user->userType);

            // Invalid password
            $invalidUser = $this->authService->authenticateWithPassword($email, "WrongPassword");
            $this->assertNull($invalidUser, "Invalid password must return null");

            // Non-existent email
            $unknownUser = $this->authService->authenticateWithPassword("nonexistent@example.com", $password);
            $this->assertNull($unknownUser, "Non-existent user must return null");
        } finally {
            if ($userId) {
                $pdo->exec("DELETE FROM users WHERE id = {$userId}");
            }
        }
    }

    public function testCitizenOtpRequestAndVerification(): void
    {
        $pdo = DatabaseManager::getConnection();
        $phone = '01712' . random_int(100000, 999999);

        // 1. Request OTP
        $result = $this->authService->requestOtp($phone);
        $this->assertTrue($result['success'], "OTP request must succeed for valid BD phone");
        $this->assertNotNull($result['mock_otp'] ?? null, "Mock OTP must be returned in dev/test environment");

        $mockOtp = $result['mock_otp'];

        // 2. Invalid OTP verification must fail
        $invalidVerify = $this->authService->verifyOtp($phone, '000000');
        $this->assertNull($invalidVerify, "Wrong OTP must fail");

        // 3. Valid OTP verification creates or finds citizen user
        $user = $this->authService->verifyOtp($phone, $mockOtp);
        $this->assertNotNull($user, "Valid OTP must authenticate citizen");
        $this->assertEquals('citizen', $user->userType);
        $this->assertTrue($user->hasRole('citizen'), "Citizen user must have default citizen role");

        // Cleanup
        $pdo->prepare("DELETE FROM user_roles WHERE user_id = ?")->execute([$user->id]);
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$user->id]);
    }

    public function testApiTokenIssuanceAndAuthentication(): void
    {
        $pdo = DatabaseManager::getConnection();
        $suffix = bin2hex(random_bytes(4));
        $uuid = Security::uuid();

        $userId = 0;
        try {
            $pdo->prepare("
                INSERT INTO users (uuid, email, user_type, status, preferred_language, created_at)
                VALUES (?, ?, 'citizen', 'active', 'bn', NOW())
            ")->execute([$uuid, "apitest_{$suffix}@example.com"]);
            $userId = (int)$pdo->lastInsertId();
            $user = User::findById($userId);

            // Issue Token
            $plainToken = $this->authService->issueApiToken($user, 'Test Mobile App');
            $this->assert(strlen($plainToken) >= 32, "Plain token must be secure length");

            // Authenticate with Token
            $authViaToken = $this->authService->authenticateWithToken($plainToken);
            $this->assertNotNull($authViaToken, "Valid bearer token must authenticate user");
            $this->assertEquals($userId, $authViaToken->id);

            // Revoke Token
            $this->authService->revokeApiToken($plainToken);
            $revokedAuth = $this->authService->authenticateWithToken($plainToken);
            $this->assertNull($revokedAuth, "Revoked token must not authenticate");
        } finally {
            if ($userId) {
                $pdo->exec("DELETE FROM user_tokens WHERE user_id = {$userId}");
                $pdo->exec("DELETE FROM users WHERE id = {$userId}");
            }
        }
    }
}
