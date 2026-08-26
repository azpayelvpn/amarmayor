<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Feature;

use AmarMayor\Database\DatabaseManager;
use AmarMayor\Support\Security;
use AmarMayor\Tests\TestCase;
use PDO;

class AuthApiTest extends TestCase
{
    public function testOtpFlowViaApi(): void
    {
        $phone = '01811' . random_int(100000, 999999);

        // 1. Request OTP via POST /api/v1/auth/otp/request
        $reqResponse = $this->postJson('/api/v1/auth/otp/request', ['phone' => $phone]);
        $this->assertEquals(200, $reqResponse->getStatusCode());

        $reqData = json_decode($reqResponse->getBody(), true);
        $this->assertTrue($reqData['success'] ?? false, "API request must be successful. Body: " . $reqResponse->getBody());
        $this->assertNotNull($reqData['data']['mock_otp'] ?? null, "mock_otp must not be null. Body: " . $reqResponse->getBody());

        $mockOtp = $reqData['data']['mock_otp'];

        // 2. Verify OTP via POST /api/v1/auth/otp/verify
        $verifyResponse = $this->postJson('/api/v1/auth/otp/verify', [
            'phone' => $phone,
            'otp_code' => $mockOtp,
            'device_name' => 'Automated Test Suite',
        ]);
        $this->assertEquals(200, $verifyResponse->getStatusCode());

        $verifyData = json_decode($verifyResponse->getBody(), true);
        $this->assertTrue($verifyData['success']);
        $this->assertNotNull($verifyData['data']['token']);
        $this->assertEquals('Bearer', $verifyData['data']['token_type']);

        $token = $verifyData['data']['token'];
        $userId = (int)$verifyData['data']['user']['id'];

        // 3. Access Protected Route GET /api/v1/auth/me
        $meResponse = $this->get('/api/v1/auth/me', ['authorization' => "Bearer {$token}"]);
        $this->assertEquals(200, $meResponse->getStatusCode());

        $meData = json_decode($meResponse->getBody(), true);
        $this->assertTrue($meData['success']);
        $this->assertEquals($userId, $meData['data']['user']['id']);

        // 4. Revoke Token via POST /api/v1/auth/logout
        $logoutResponse = $this->postJson('/api/v1/auth/logout', [], ['authorization' => "Bearer {$token}"]);
        $this->assertEquals(200, $logoutResponse->getStatusCode());

        // 5. Subsequent access with revoked token must fail with 401
        $unauthResponse = $this->get('/api/v1/auth/me', ['authorization' => "Bearer {$token}"]);
        $this->assertEquals(401, $unauthResponse->getStatusCode());

        // Cleanup
        $pdo = DatabaseManager::getConnection();
        $pdo->prepare("DELETE FROM user_roles WHERE user_id = ?")->execute([$userId]);
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
    }
}
