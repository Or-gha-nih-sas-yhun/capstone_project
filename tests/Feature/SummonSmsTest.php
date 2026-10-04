<?php

namespace Tests\Feature;

use App\Models\Summon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SummonSmsTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::create([
            'username' => 'summon_admin',
            'email' => 'summon_admin@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        config([
            'services.philsms.enabled' => true,
            'services.philsms.api_token' => 'test-token',
            'services.philsms.api_url' => 'https://sms.test/api/send',
        ]);

        Http::fake(['sms.test/*' => Http::response(['status' => 'success'], 200)]);
        Mail::fake();
    }

    public function test_new_summons_sends_sms_to_both_parties()
    {
        $this->actingAs($this->adminUser)->post('/admin/summons/store', [
            'case_type' => 'summon',
            'complainant_name' => 'Juan Dela Cruz',
            'complainant_contact' => '09123456789',
            'respondent_name' => 'Pedro Santos',
            'respondent_contact' => '09987654321',
            'complain_details' => 'Test complaint details',
            'nature_of_complaint' => 'Test complaint',
            'schedule_date' => now()->addDay()->format('Y-m-d H:i:s'),
        ])->assertSessionHas('success');

        $summon = Summon::firstOrFail();

        Http::assertSentCount(2);
        Http::assertSent(function ($request) use ($summon) {
            return in_array($request['recipient'], ['639123456789', '639987654321'], true)
                && str_contains($request['message'], $summon->case_number)
                && str_contains($request['message'], 'Hearing schedule');
        });
    }

    public function test_new_blotter_sends_an_acknowledgment_sms_to_both_parties()
    {
        $this->actingAs($this->adminUser)->post('/admin/summons/store', [
            'case_type' => 'blotter',
            'complainant_name' => 'Maria Reyes',
            'complainant_contact' => '09123456789',
            'respondent_name' => 'Ana Gomez',
            'respondent_contact' => '09987654321',
            'complain_details' => 'Test blotter details',
            'nature_of_complaint' => 'Test incident',
        ])->assertSessionHas('success');

        $summon = Summon::firstOrFail();

        Http::assertSentCount(2);
        Http::assertSent(function ($request) use ($summon) {
            return in_array($request['recipient'], ['639123456789', '639987654321'], true)
                && str_contains($request['message'], $summon->case_number)
                && str_contains($request['message'], 'blotter report')
                && !str_contains($request['message'], 'Hearing schedule');
        });
    }

    public function test_case_status_update_sends_sms_to_both_parties()
    {
        $summon = Summon::create([
            'case_number' => 'SUMMON-2026-SMS',
            'case_type' => 'blotter',
            'complainant_name' => 'Maria Reyes',
            'complainant_contact' => '09123456789',
            'respondent_name' => 'Ana Gomez',
            'respondent_contact' => '09987654321',
            'complain_details' => 'Test blotter details',
            'status' => 'pending',
        ]);

        $this->actingAs($this->adminUser)->post('/admin/summons/update', [
            'summon_id' => $summon->id,
            'status' => 'dismissed',
        ])->assertSessionHas('success');

        Http::assertSentCount(2);
        Http::assertSent(function ($request) use ($summon) {
            return in_array($request['recipient'], ['639123456789', '639987654321'], true)
                && str_contains($request['message'], $summon->case_number)
                && str_contains($request['message'], 'Dismissed');
        });
    }
}
