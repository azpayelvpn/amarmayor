<?php

declare(strict_types=1);

namespace AmarMayor\Auth\Otp;

use AmarMayor\Support\Logger;

/**
 * Mock OTP Provider for local development, CI, and test environments.
 * Logs the generated OTP code safely to the application log without pretending external gateway success.
 */
class MockOtpProvider implements OtpProviderInterface
{
    public function sendOtp(string $phone, string $otpCode): bool
    {
        Logger::info("Mock OTP Dispatched (DEV/TEST)", [
            'phone' => substr($phone, 0, 7) . '****',
            'code' => $otpCode,
        ]);

        return true;
    }
}
