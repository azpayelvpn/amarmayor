<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Unit;

use AmarMayor\Support\Translator;
use AmarMayor\Tests\TestCase;
use DateTime;

class BilingualTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Translator::setLocale('bn');
    }

    public function testBanglaDefaultAndFallback(): void
    {
        Translator::setLocale('bn');
        $this->assertEquals('bn', Translator::getLocale());

        $appName = Translator::get('app.name');
        $this->assertEquals('আমার ময়মনসিংহ', $appName);

        // Fallback or explicit english
        $appNameEn = Translator::get('app.name', [], 'en');
        $this->assertEquals('Amar Mymensingh', $appNameEn);
    }

    public function testNumeralConversion(): void
    {
        $this->assertEquals('০১২৩৪৫৬৭৮৯', Translator::toBanglaNumber('0123456789'));
        $this->assertEquals('১২৩৪৫', Translator::toBanglaNumber(12345));
        $this->assertEquals('0123456789', Translator::toEnglishNumber('০১২৩৪৫৬৭৮৯'));
    }

    public function testDateFormatting(): void
    {
        $dt = new DateTime('2026-08-26 14:30:00');

        $bnFormatted = Translator::formatDate($dt, 'default', 'bn');
        $this->assertStringContainsString('২৬', $bnFormatted);
        $this->assertStringContainsString('আগস্ট', $bnFormatted);
        $this->assertStringContainsString('২০২৬', $bnFormatted);
        $this->assertStringContainsString('২:৩০', $bnFormatted);
        $this->assertStringContainsString('অপরাহ্ন', $bnFormatted);

        $enFormatted = Translator::formatDate($dt, 'default', 'en');
        $this->assertStringContainsString('August', $enFormatted);
        $this->assertStringContainsString('26', $enFormatted);
        $this->assertStringContainsString('2026', $enFormatted);
        $this->assertStringContainsString('2:30 PM', $enFormatted);
    }

    public function testCurrencyFormatting(): void
    {
        $this->assertEquals('৳১,৫০০.০০', Translator::formatMoney(1500, 'bn'));
        $this->assertEquals('BDT 1,500.00', Translator::formatMoney(1500, 'en'));
    }

    public function testComplaintStatusTranslations(): void
    {
        // 7 Citizen presentation statuses
        $this->assertEquals('অভিযোগ জমা হয়েছে', Translator::get('complaints.citizen_status.received', [], 'bn'));
        $this->assertEquals('Complaint Received', Translator::get('complaints.citizen_status.received', [], 'en'));
        $this->assertEquals('পুনরায় কাজ প্রয়োজন', Translator::get('complaints.citizen_status.needs_more_work', [], 'bn'));
        $this->assertEquals('Needs More Work', Translator::get('complaints.citizen_status.needs_more_work', [], 'en'));

        // 17 Internal statuses
        $this->assertEquals('নতুন দাখিলকৃত', Translator::get('complaints.internal_status.submitted', [], 'bn'));
        $this->assertEquals('Newly Submitted', Translator::get('complaints.internal_status.submitted', [], 'en'));
        $this->assertEquals('সুপারভাইজার যাচাই অপেক্ষমাণ', Translator::get('complaints.internal_status.verification_required', [], 'bn'));
    }

    public function testGovernanceAndWorkforceTranslations(): void
    {
        $this->assertEquals('মাননীয় মেয়র', Translator::get('governance.roles.mayor', [], 'bn'));
        $this->assertEquals('Honorable Mayor', Translator::get('governance.roles.mayor', [], 'en'));
        $this->assertEquals('সাধারণ ওয়ার্ড কাউন্সিলর', Translator::get('governance.roles.general_councillor', [], 'bn'));
        $this->assertEquals('General Ward Councillor', Translator::get('governance.roles.general_councillor', [], 'en'));

        $this->assertEquals('পরিচ্ছন্নতা পরিদর্শক', Translator::get('workforce.designations.conservancy_inspector', [], 'bn'));
        $this->assertEquals('Conservancy Inspector', Translator::get('workforce.designations.conservancy_inspector', [], 'en'));
    }
}
