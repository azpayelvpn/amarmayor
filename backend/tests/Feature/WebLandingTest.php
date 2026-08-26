<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Feature;

use AmarMayor\Tests\TestCase;

class WebLandingTest extends TestCase
{
    public function testWebLandingPageLoads(): void
    {
        $this->setUp();
        $res = $this->get('/');

        $this->assertEquals(200, $res->getStatusCode());
        $this->assertStringContains('আমার ময়মনসিংহ', $res->getContent());
        $this->assertStringContains('Phase 1 — Core Backend Foundation Ready', $res->getContent());
    }

    public function testHtmxStatusPartial(): void
    {
        $this->setUp();
        $res = $this->get('/htmx/status-check', ['hx-request' => 'true']);

        $this->assertEquals(200, $res->getStatusCode());
        $this->assertStringContains('সিস্টেম আর্কিটেকচার ও সংযোগ স্থিতি', $res->getContent());
    }
}
