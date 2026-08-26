<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Unit;

use AmarMayor\Tests\TestCase;
use AmarMayor\Validation\Validator;

class ValidatorTest extends TestCase
{
    public function testValidationPasses(): void
    {
        $data = [
            'phone' => '01712345678',
            'ward_id' => '19',
            'description' => 'রাস্তায় ময়লার স্তূপ।',
        ];

        $rules = [
            'phone' => 'required|phone',
            'ward_id' => 'required|integer',
            'description' => 'required|string|min:5',
        ];

        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->passes());
        $this->assertFalse($validator->fails());
    }

    public function testValidationFailsWithInvalidPhone(): void
    {
        $data = [
            'phone' => '12345',
        ];

        $rules = [
            'phone' => 'required|phone',
        ];

        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->fails());
        $this->assertTrue(isset($validator->getErrors()['phone']));
    }

    public function testRequiredFieldFailure(): void
    {
        $data = [];
        $rules = ['phone' => 'required'];

        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->fails());
        $this->assertStringContains('মোবাইল নম্বর', $validator->getFirstError());
    }
}
