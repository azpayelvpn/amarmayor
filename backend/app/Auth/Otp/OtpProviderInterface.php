<?php

declare(strict_types=1);

namespace AmarMayor\Auth\Otp;

interface OtpProviderInterface
{
    /**
     * Send 6-digit OTP code to the given Bangladeshi E.164 phone number.
     *
     * @param string $phone Formatted phone number (e.g. +8801700000000)
     * @param string $otpCode 6-digit numeric OTP code
     * @return bool True if successfully dispatched/queued, false otherwise
     */
    public function sendOtp(string $phone, string $otpCode): bool;
}
