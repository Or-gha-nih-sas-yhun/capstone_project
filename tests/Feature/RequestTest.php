<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Resident;
use App\Models\Certificate;
use App\Models\Request as CertificateRequest;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RequestTest extends TestCase
{
    use RefreshDatabase;

    protected $residentUser;
    protected $residentProfile;
    protected $adminUser;
    protected $certificate;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a resident profile and user
        $this->residentProfile = Resident::create([
            'first_name' => 'Maria Clara',
            'last_name' => 'Santos',
            'gender' => 'Female',
            'birthdate' => '2000-01-01',
            'civil_status' => 'Single',
            'email' => 'clara@santos.com',
            'contact_number' => '09123456789',
            'address' => 'Barangay Pili, Madridejos, Cebu',
            'status' => 'active'
        ]);

        $this->residentUser = User::create([
            'username' => 'mariaclara',
            'email' => 'clara@santos.com',
            'password' => bcrypt('password123'),
            'role' => 'resident',
            'status' => 'active',
            'resident_id' => $this->residentProfile->id,
        ]);

        // Create an admin user
        $this->adminUser = User::create([
            'username' => 'admin_test',
            'email' => 'admin_test@email.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Create a certificate type
        $this->certificate = Certificate::create([
            'name' => 'Barangay Clearance Test',
            'description' => 'A test certificate',
            'category' => 'Clearance',
            'fee' => 50.00,
            'processing_days' => 1,
            'template_file' => 'certificate_clearance.php',
            'requirements' => 'None',
            'status' => 'active',
        ]);
    }

    public function test_resident_can_view_my_requests_page()
    {
        $response = $this->actingAs($this->residentUser)->get('/resident/my-requests');
        $response->assertStatus(200);
        $response->assertSee('My Requests');
    }

    public function test_resident_can_submit_certificate_request()
    {
        $response = $this->actingAs($this->residentUser)->post('/resident/request', [
            'certificate_id' => $this->certificate->id,
            'purpose' => 'For employment purposes',
        ]);

        $response->assertRedirect(route('resident.my_requests'));
        $response->assertSessionHas('success');

        // Check if request exists in database
        $request = CertificateRequest::where('resident_id', $this->residentProfile->id)->first();
        $this->assertNotNull($request);
        $this->assertEquals('pending', $request->status);
        $this->assertEquals('For employment purposes', $request->purpose);

        // Check if payment was created
        $payment = Payment::where('request_id', $request->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals(50.00, $payment->amount);
        $this->assertEquals('unpaid', $payment->payment_status);
    }

    public function test_admin_can_view_requests_management_page()
    {
        $response = $this->actingAs($this->adminUser)->get('/admin/requests');
        $response->assertStatus(200);
        $response->assertSee('Manage Requests');
    }

    public function test_admin_can_process_and_approve_request()
    {
        // 1. Create a pending request
        $certReq = CertificateRequest::create([
            'tracking_number' => 'PILI-2026-TEST',
            'resident_id' => $this->residentProfile->id,
            'certificate_id' => $this->certificate->id,
            'purpose' => 'Test purpose',
            'status' => 'pending',
        ]);
        
        $payment = Payment::create([
            'request_id' => $certReq->id,
            'amount' => 50.00,
            'payment_method' => 'cash',
            'payment_status' => 'unpaid',
        ]);

        // 2. Process request
        $response = $this->actingAs($this->adminUser)->post('/admin/requests/update-status', [
            'request_id' => $certReq->id,
            'action' => 'process',
        ]);
        $response->assertRedirect(route('admin.requests'));
        $this->assertEquals('processing', $certReq->fresh()->status);

        // 3. Approve request
        $response = $this->actingAs($this->adminUser)->post('/admin/requests/update-status', [
            'request_id' => $certReq->id,
            'action' => 'approve',
            'remarks' => 'Approved after review',
        ]);
        $response->assertRedirect(route('admin.requests'));
        
        $freshReq = $certReq->fresh();
        $this->assertEquals('approved', $freshReq->status);
        $this->assertEquals('Approved after review', $freshReq->remarks);
    }

    public function test_certificate_approval_sends_an_sms_to_the_resident()
    {
        config([
            'services.philsms.enabled' => true,
            'services.philsms.api_token' => 'test-token',
            'services.philsms.api_url' => 'https://sms.test/api/send',
        ]);
        Http::fake(['sms.test/*' => Http::response([], 200)]);

        $certReq = CertificateRequest::create([
            'tracking_number' => 'PILI-2026-SMS',
            'resident_id' => $this->residentProfile->id,
            'certificate_id' => $this->certificate->id,
            'purpose' => 'Employment',
            'status' => 'processing',
        ]);

        $this->actingAs($this->adminUser)->post('/admin/requests/update-status', [
            'request_id' => $certReq->id,
            'action' => 'approve',
        ])->assertSessionHas('success');

        Http::assertSent(function ($request) {
            return $request['recipient'] === '639123456789'
                && str_contains($request['message'], 'PILI-2026-SMS')
                && str_contains($request['message'], 'APPROVED');
        });
    }

    public function test_paid_payment_auto_approval_sends_a_combined_sms()
    {
        config([
            'services.philsms.enabled' => true,
            'services.philsms.api_token' => 'test-token',
            'services.philsms.api_url' => 'https://sms.test/api/send',
        ]);
        Http::fake(['sms.test/*' => Http::response([], 200)]);

        $certReq = CertificateRequest::create([
            'tracking_number' => 'PILI-2026-PAYMENT-SMS',
            'resident_id' => $this->residentProfile->id,
            'certificate_id' => $this->certificate->id,
            'purpose' => 'Employment',
            'status' => 'processing',
        ]);
        $payment = Payment::create([
            'request_id' => $certReq->id,
            'amount' => 50,
            'payment_method' => 'cash',
            'payment_status' => 'unpaid',
        ]);

        $this->actingAs($this->adminUser)->post('/admin/payments/update', [
            'payment_id' => $payment->id,
            'amount' => 50,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'receipt_number' => 'OR-1001',
        ])->assertSessionHas('success');

        $this->assertEquals('approved', $certReq->fresh()->status);
        Http::assertSent(function ($request) {
            return $request['recipient'] === '639123456789'
                && str_contains($request['message'], 'PILI-2026-PAYMENT-SMS')
                && str_contains($request['message'], 'PAID')
                && str_contains($request['message'], 'APPROVED');
        });
    }
}
