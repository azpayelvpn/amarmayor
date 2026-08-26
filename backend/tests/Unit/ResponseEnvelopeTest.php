<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Unit;

use AmarMayor\Http\Response;
use AmarMayor\Tests\TestCase;

class ResponseEnvelopeTest extends TestCase
{
    public function testStandardSuccessEnvelope(): void
    {
        Response::setGlobalRequestId('req-test-1234');
        $res = Response::json(['key' => 'value'], 200);

        $this->assertEquals(200, $res->getStatusCode());
        $payload = json_decode($res->getContent(), true);

        $this->assertTrue($payload['success']);
        $this->assertEquals('value', $payload['data']['key']);
        $this->assertEquals('req-test-1234', $payload['meta']['request_id']);
        $this->assertTrue(isset($payload['meta']['timestamp']));
    }

    public function testStandardErrorEnvelope(): void
    {
        Response::setGlobalRequestId('req-err-5678');
        $res = Response::error('COMPLAINT_NOT_FOUND', 'অভিযোগটি পাওয়া যায়নি।', 404, ['id' => 99]);

        $this->assertEquals(404, $res->getStatusCode());
        $payload = json_decode($res->getContent(), true);

        $this->assertFalse($payload['success']);
        $this->assertEquals('COMPLAINT_NOT_FOUND', $payload['error']['code']);
        $this->assertEquals('অভিযোগটি পাওয়া যায়নি।', $payload['error']['message']);
        $this->assertEquals(99, $payload['error']['details']['id']);
        $this->assertEquals('req-err-5678', $payload['meta']['request_id']);
    }
}
