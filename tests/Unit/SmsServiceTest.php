<?php

namespace Tests\Unit;

use App\Services\SmsService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SmsServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.philsms.enabled' => true,
            'services.philsms.api_token' => 'test-token',
            'services.philsms.api_url' => 'https://sms.test/api/send',
            'services.philsms.sender_id' => 'BarangayPili',
        ]);
    }

    public function test_it_normalizes_a_philippine_number_and_sends_the_expected_payload()
    {
        Http::fake([
            'sms.test/*' => Http::response(['status' => 'success'], 200),
        ]);

        $sent = SmsService::send('+63 912-345-6789', 'Status updated.');

        $this->assertTrue($sent);
        Http::assertSent(function ($request) {
            return $request->url() === 'https://sms.test/api/send'
                && $request->hasHeader('Authorization', 'Bearer test-token')
                && $request['recipient'] === '639123456789'
                && $request['sender_id'] === 'BarangayPili'
                && $request['type'] === 'plain'
                && $request['message'] === 'Status updated.';
        });
    }

    public function test_it_does_not_call_the_gateway_for_an_invalid_number()
    {
        Http::fake();

        $this->assertFalse(SmsService::send('12345', 'Status updated.'));
        Http::assertNothingSent();
    }

    public function test_it_does_not_call_the_gateway_when_sms_is_disabled()
    {
        config(['services.philsms.enabled' => false]);
        Http::fake();

        $this->assertFalse(SmsService::send('09123456789', 'Status updated.'));
        Http::assertNothingSent();
    }

    public function test_it_treats_a_200_api_error_response_as_failed()
    {
        Http::fake([
            'sms.test/*' => Http::response([
                'status' => 'error',
                'message' => 'Unauthenticated.',
            ], 200),
        ]);

        $this->assertFalse(SmsService::send('09123456789', 'Status updated.'));
        Http::assertSentCount(1);
    }
}
