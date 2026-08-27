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

    public function testWebCitizenOtpLoginAndVerificationFlow(): void
    {
        $phone = '01711' . random_int(100000, 999999);

        // 1. Request OTP via POST /auth/otp/request
        $reqResponse = $this->post('/auth/otp/request', [
            'phone' => $phone,
            '_csrf_token' => Security::generateCsrfToken(),
        ]);
        $this->assertEquals(302, $reqResponse->getStatusCode());
        $location = $reqResponse->getHeader('Location');
        $this->assertStringContains('/login?step=verify', $location);

        // 2. Fetch the generated OTP from DevOtpInbox
        $otps = \AmarMayor\Auth\Otp\DevOtpInboxService::getRecentOtps();
        $normalizedPhone = Security::normalizePhone($phone);
        $foundOtp = null;
        foreach ($otps as $entry) {
            if (Security::normalizePhone($entry['phone']) === $normalizedPhone && !$entry['used']) {
                $foundOtp = $entry['otp'];
                break;
            }
        }
        $this->assertNotNull($foundOtp, "Active OTP must exist in DevOtpInbox for {$phone}");

        // 3. Test wrong OTP rejection
        $wrongResponse = $this->post('/auth/otp/verify', [
            'phone' => $phone,
            'otp_code' => '999999',
            '_csrf_token' => Security::generateCsrfToken(),
        ]);
        $this->assertEquals(302, $wrongResponse->getStatusCode());
        $this->assertStringContains('error=invalid_otp', $wrongResponse->getHeader('Location'));

        // 4. Verify with exact correct OTP
        $verifyResponse = $this->post('/auth/otp/verify', [
            'phone' => $phone,
            'otp_code' => $foundOtp,
            '_csrf_token' => Security::generateCsrfToken(),
        ]);
        $this->assertEquals(302, $verifyResponse->getStatusCode());
        $this->assertEquals('/dashboard', $verifyResponse->getHeader('Location'));

        // 5. Check that session is created
        $this->assertTrue(\AmarMayor\Auth\Auth::check(), "Citizen must be authenticated in session");
        $user = \AmarMayor\Auth\Auth::user();
        $this->assertNotNull($user);
        $this->assertEquals('citizen', $user->userType);

        // 6. Test OTP cannot be reused
        $reuseResponse = $this->post('/auth/otp/verify', [
            'phone' => $phone,
            'otp_code' => $foundOtp,
            '_csrf_token' => Security::generateCsrfToken(),
        ]);
        $this->assertEquals(302, $reuseResponse->getStatusCode());
        $this->assertStringContains('error=invalid_otp', $reuseResponse->getHeader('Location'));

        // Cleanup
        $pdo = DatabaseManager::getConnection();
        $pdo->prepare("DELETE FROM user_roles WHERE user_id = ?")->execute([$user->id]);
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$user->id]);
        \AmarMayor\Auth\Auth::logout();
    }

    public function testPhoneNormalizationAcrossFormats(): void
    {
        $raw1 = '01711000001';
        $raw2 = '8801711000001';
        $raw3 = '+8801711000001';
        $raw4 = '০১৭১১০০০০০১';

        $this->assertEquals('+8801711000001', Security::normalizePhone($raw1));
        $this->assertEquals('+8801711000001', Security::normalizePhone($raw2));
        $this->assertEquals('+8801711000001', Security::normalizePhone($raw3));
        $this->assertEquals('+8801711000001', Security::normalizePhone($raw4));
    }
}
