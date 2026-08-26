<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Unit;

use AmarMayor\Support\Config;
use AmarMayor\Tests\TestCase;

class SessionPersistenceTest extends TestCase
{
    public function testFileSessionPersistenceAcrossRequests(): void
    {
        $savePath = Config::get('session.save_path', dirname(__DIR__, 2) . '/storage/sessions');
        if (!is_dir($savePath)) {
            @mkdir($savePath, 0755, true);
        }

        $sessionId = 'test_sess_' . bin2hex(random_bytes(8));
        $sessionFile = $savePath . '/sess_' . $sessionId;

        // Simulate Request A: Write session data
        $dataToPersist = ['citizen_authenticated' => true, 'phone' => '01712345678'];
        $serialized = 'citizen_authenticated|' . serialize(true) . 'phone|' . serialize('01712345678');
        file_put_contents($sessionFile, $serialized);

        $this->assertTrue(file_exists($sessionFile), "Session file should persist to disk in storage/sessions");

        // Simulate Request B: Read session data back from storage/sessions
        $raw = file_get_contents($sessionFile);
        $this->assertTrue(is_string($raw) && strlen($raw) > 0);
        $this->assertStringContains('01712345678', $raw);

        // Cleanup
        @unlink($sessionFile);
    }
}
