<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Feature;

use AmarMayor\Database\DatabaseManager;
use AmarMayor\Support\Security;
use AmarMayor\Tests\TestCase;

class CitizenWebUxTest extends TestCase
{
    public function testCitizenComplaintCreationAndTrackingFlow(): void
    {
        $this->setUp();

        // 1. Load submission page
        $res = $this->get('/complaints/create');
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertStringContains('নাগরিক অভিযোগ দাখিল', $res->getContent());
        $this->assertStringContains('সমস্যার ধরন', $res->getContent());

        // 2. Submit new complaint via web form
        $postRes = $this->post('/complaints/create', [
            '_csrf_token' => Security::generateCsrfToken(),
            'category_id' => 1,
            'subcategory_id' => 1,
            'ward_id' => 1,
            'description' => 'টেস্ট রাস্তা ভাঙা নাগরিক অভিযোগ ওয়েব সাবমিশন',
            'landmark' => 'টাউন হল মোড়',
            'approximate_address' => 'সেনবাড়ি রোড',
            'phone' => '01711998877',
        ]);

        $this->assertEquals(200, $postRes->getStatusCode());
        $this->assertStringContains('অভিযোগ সফলভাবে গ্রহণ করা হয়েছে', $postRes->getContent());
        $this->assertStringContains('MCC-', $postRes->getContent());

        // Extract tracking number
        preg_match('/MCC-\d{4}-\d{5}/', $postRes->getContent(), $matches);
        $this->assert(!empty($matches[0]), 'Tracking number must be present in response');
        $trackingNumber = $matches[0];

        // 3. Track complaint by tracking number
        $trackRes = $this->get("/track/{$trackingNumber}");
        $this->assertEquals(200, $trackRes->getStatusCode());
        $this->assertStringContains($trackingNumber, $trackRes->getContent());
        $this->assertStringContains('অভিযোগের অবস্থা ট্র্যাক করুন', $trackRes->getContent());
    }

    public function testCivicDirectoryAndWardsPagesLoad(): void
    {
        $this->setUp();

        // Wards overview
        $wardsRes = $this->get('/wards');
        $this->assertEquals(200, $wardsRes->getStatusCode());
        $this->assertStringContains('ওয়ার্ড নির্দেশিকা', $wardsRes->getContent());

        // Who is Responsible directory
        $whoRes = $this->get('/who-is-responsible');
        $this->assertEquals(200, $whoRes->getStatusCode());
        $this->assertStringContains('কে দায়িত্বে আছেন?', $whoRes->getContent());

        // Public notices
        $noticesRes = $this->get('/notices');
        $this->assertEquals(200, $noticesRes->getStatusCode());
        $this->assertStringContains('পৌর নোটিশ ও ঘোষণা', $noticesRes->getContent());
    }
}
