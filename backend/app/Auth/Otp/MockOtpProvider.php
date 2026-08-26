<?php

declare(strict_types=1);

namespace AmarMayor\Auth\Otp;

use AmarMayor\Support\Logger;

/**
 * Mock OTP Provider for local development, CI, and test environments.
 * Logs the generated OTP code and registers it transiently with the Developer OTP Inbox.
 */
class MockOtpProvider implements OtpProviderInterface
{
    public function sendOtp(string $phone, string $otpCode): bool
    {
        Logger::info("Mock OTP Dispatched (DEV/TEST)", [
            'phone' => substr($phone, 0, 7) . '****',
        ]);

        // Record for Developer OTP Inbox inspection in development mode
        DevOtpInboxService::recordOtp($phone, $otpCode);

        return true;
    }
}
