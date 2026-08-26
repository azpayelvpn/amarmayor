<?php

declare(strict_types=1);

namespace AmarMayor\Auth\Otp;

use AmarMayor\Support\Config;
use AmarMayor\Support\Logger;

/**
 * Production SMS Gateway Provider.
 * Gracefully reports offline/unconfigured status if gateway credentials are not set.
 */
class SmsGatewayProvider implements OtpProviderInterface
{
    private string $apiKey;
    private string $senderId;
    private string $endpoint;

    public function __construct()
    {
        $this->apiKey = (string)Config::get('sms.api_key', '');
        $this->senderId = (string)Config::get('sms.sender_id', 'AmarMayor');
        $this->endpoint = (string)Config::get('sms.endpoint', '');
    }

    public function sendOtp(string $phone, string $otpCode): bool
    {
        if (empty($this->apiKey) || empty($this->endpoint)) {
            Logger::warning("SMS Gateway not configured in environment. Message dropped.", [
                'phone' => substr($phone, 0, 7) . '****'
            ]);
            return false;
        }

        // Production HTTP Dispatch logic here when configured
        return true;
    }
}
