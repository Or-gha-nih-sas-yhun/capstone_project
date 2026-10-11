<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\Resident;
use App\Models\Request as CertificateRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResidentDemographicsReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_monthly_report_includes_resident_gender_civil_status_and_pwd_status()
    {
        $admin = User::create([
            'username' => 'reportadmin',
            'email' => 'reportadmin@example.com',
            'password' => 'password',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $resident = Resident::create([
            'first_name' => 'Alex',
            'last_name' => 'Santos',
            'gender' => 'Female',
            'birthdate' => '1990-01-01',
            'civil_status' => 'Widowed',
            'is_pwd' => true,
            'address' => 'Barangay Pili',
        ]);

        $certificate = Certificate::create([
            'name' => 'Barangay Clearance',
            'category' => 'General',
            'fee' => 50,
            'status' => 'active',
        ]);

        CertificateRequest::create([
            'tracking_number' => 'DEMO-REPORT-001',
            'resident_id' => $resident->id,
            'certificate_id' => $certificate->id,
            'purpose' => 'Testing',
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports', [
            'month' => now()->format('Y-m'),
        ]));

        $response->assertOk();
        $response->assertSee('Gender');
        $response->assertSee('Civil Status');
        $response->assertSee('PWD');
        $response->assertSee('Female');
        $response->assertSee('Widowed');
        $response->assertSee('Yes');

        $csvResponse = $this->actingAs($admin)->get(route('admin.reports', [
            'month' => now()->format('Y-m'),
            'export' => 'csv',
        ]));

        $csvResponse->assertOk();
        $csv = $csvResponse->streamedContent();
        $this->assertStringContainsString('Gender,Civil Status,PWD', $csv);
        $this->assertStringContainsString('Female,Widowed,Yes', $csv);
    }
}
