<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Unit;

use AmarMayor\Support\Translator;
use AmarMayor\Tests\TestCase;

class TranslatorTest extends TestCase
{
    public function testBanglaDefault(): void
    {
        Translator::setLocale('bn');
        $this->assertEquals('bn', Translator::getLocale());
        $line = Translator::get('common.app_name');
        $this->assertEquals('আমার ময়মনসিংহ', $line);
    }

    public function testEnglishSwitch(): void
    {
        Translator::setLocale('en');
        $this->assertEquals('en', Translator::getLocale());
        $line = Translator::get('common.app_name');
        $this->assertEquals('My Mymensingh', $line);
    }

    public function testPlaceholderReplacement(): void
    {
        Translator::setLocale('bn');
        $line = Translator::get('validation.required', ['field' => 'ফোন']);
        $this->assertEquals('ফোন তথ্যটি পূরণ করা আবশ্যক।', $line);
    }
}
