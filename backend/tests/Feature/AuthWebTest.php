<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Feature;

use AmarMayor\Database\DatabaseManager;
use AmarMayor\Support\Security;
use AmarMayor\Tests\TestCase;
use PDO;

class AuthWebTest extends TestCase
{
    public function testWebLoginPageLoads(): void
    {
        $response = $this->get('/login');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContains('লগইন', $response->getBody());
        $this->assertStringContains('নাগরিক', $response->getBody());
        $this->assertStringContains('কর্মকর্তা ও কর্মচারী', $response->getBody());
    }

    public function testWebStaffPasswordLoginAndLogout(): void
    {
        $pdo = DatabaseManager::getConnection();
        $suffix = bin2hex(random_bytes(4));
        $email = "webstaff_{$suffix}@amarmayor.gov.bd";
        $password = "SecretPass123!";
        $passwordHash = Security::hashPassword($password);
        $uuid = Security::uuid();

        $userId = 0;
        try {
            $pdo->prepare("
                INSERT INTO users (uuid, email, password_hash, user_type, status, preferred_language, created_at)
                VALUES (?, ?, ?, 'staff', 'active', 'bn', NOW())
            ")->execute([$uuid, $email, $passwordHash]);
            $userId = (int)$pdo->lastInsertId();

            // Submit POST /login/password
            $response = $this->post('/login/password', [
                'identifier' => $email,
                'password' => $password,
                '_csrf_token' => Security::generateCsrfToken(),
            ]);

            // Expect 302 Redirect to /dashboard
            $this->assertEquals(302, $response->getStatusCode());
            $this->assertEquals('/dashboard', $response->getHeader('Location'));

            // Submit POST /logout
            $logoutResponse = $this->post('/logout', [
                '_csrf_token' => Security::generateCsrfToken(),
            ]);
            $this->assertEquals(302, $logoutResponse->getStatusCode());
            $this->assertEquals('/login', $logoutResponse->getHeader('Location'));
        } finally {
            if ($userId) {
                $pdo->exec("DELETE FROM users WHERE id = {$userId}");
            }
        }
    }
}
