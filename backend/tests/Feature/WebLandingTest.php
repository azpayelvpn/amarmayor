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
        $this->assertStringContains('অভিযোগ করুন', $res->getContent());
        $this->assertStringContains('আমার ওয়ার্ড', $res->getContent());
        $this->assertStringContains('কে দায়িত্বে আছেন?', $res->getContent());
    }

    public function testHtmxStatusPartial(): void
    {
        $this->setUp();
        $res = $this->get('/htmx/status-check', ['hx-request' => 'true']);

        $this->assertEquals(200, $res->getStatusCode());
        $this->assertStringContains('মোট অভিযোগ', $res->getContent());
        $this->assertStringContains('কাজ চলছে', $res->getContent());
    }
}
