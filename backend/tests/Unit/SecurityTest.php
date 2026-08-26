<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Unit;

use AmarMayor\Support\Security;
use AmarMayor\Tests\TestCase;

class SecurityTest extends TestCase
{
    public function testHtmlEscaping(): void
    {
        $input = '<script>alert("XSS")</script>';
        $escaped = Security::escape($input);

        $this->assertEquals('&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;', $escaped);
        $this->assertFalse(str_contains($escaped, '<script>'));
    }

    public function testPasswordHashing(): void
    {
        $password = 'SecretPass123!';
        $hash = Security::hashPassword($password);

        $this->assertTrue(is_string($hash) && strlen($hash) > 20);
        $this->assertTrue(Security::verifyPassword($password, $hash));
        $this->assertFalse(Security::verifyPassword('WrongPassword', $hash));
    }

    public function testTimingSafeEquals(): void
    {
        $a = 'token_abc123';
        $b = 'token_abc123';
        $c = 'token_xyz999';

        $this->assertTrue(Security::timingSafeEquals($a, $b));
        $this->assertFalse(Security::timingSafeEquals($a, $c));
    }

    public function testCsrfTokenValidation(): void
    {
        $token = Security::generateCsrfToken();
        $this->assertTrue(is_string($token) && strlen($token) === 64);
        $this->assertTrue(Security::validateCsrfToken($token));
        $this->assertFalse(Security::validateCsrfToken('invalid_token'));
    }
}
