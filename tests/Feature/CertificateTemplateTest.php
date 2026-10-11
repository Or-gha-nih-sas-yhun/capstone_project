<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\Official;
use App\Models\Request as CertificateRequest;
use App\Models\Resident;
use App\Models\Setting;
use App\Models\User;
use App\Services\CertificateRenderer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers barangay-authored document wording: token substitution, escaping,
 * and the fact that a custom template overrides the built-in text.
 */
class CertificateTemplateTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Resident $resident;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'username' => 'admin',
            'email'    => 'admin@example.test',
            'password' => bcrypt('password'),
            'role'     => 'admin',
            'status'   => 'active',
        ]);

        Official::create(['name' => 'HON. JUAN DELA CRUZ', 'position' => 'Punong Barangay', 'status' => 'active']);

        $this->resident = Resident::create([
            'first_name'         => 'Maria',
            'last_name'          => 'Santos',
            'gender'             => 'Female',
            'birthdate'          => '1990-05-05',
            'civil_status'       => 'Single',
            'address'            => '12 Mabini St.',
            'purok'              => 'Purok 1',
            'years_of_residency' => 7,
            'status'             => 'active',
        ]);
    }

    private function makeRequest(array $certAttributes = []): CertificateRequest
    {
        $certificate = Certificate::create(array_merge([
            'name'            => 'Barangay Clearance',
            'category'        => 'Clearance',
            'fee'             => 50,
            'processing_days' => 1,
            'template_file'   => 'certificate_clearance.php',
            'status'          => 'active',
        ], $certAttributes));

        return CertificateRequest::create([
            'resident_id'     => $this->resident->id,
            'certificate_id'  => $certificate->id,
            'tracking_number' => 'PILI-20261009-ABC123',
            'purpose'         => 'employment requirement',
            'status'          => 'approved',
            'quantity'        => 1,
        ]);
    }

    public function test_tokens_are_substituted(): void
    {
        $request = $this->makeRequest();

        $html = (new CertificateRenderer())->render(
            '<p>{{full_name}} of {{address}}, {{barangay_label}}, aged {{age}}, '
            . 'resident for {{years_residency}} year(s), for {{purpose_upper}}.</p>',
            $request
        );

        $this->assertStringContainsString('MARIA SANTOS', $html);
        $this->assertStringContainsString('12 Mabini St.', $html);
        $this->assertStringContainsString('Barangay Pili', $html);
        $this->assertStringContainsString('7 year(s)', $html);
        $this->assertStringContainsString('EMPLOYMENT REQUIREMENT', $html);
    }

    public function test_blank_template_renders_nothing(): void
    {
        $request = $this->makeRequest();
        $renderer = new CertificateRenderer();

        $this->assertNull($renderer->render(null, $request));
        $this->assertNull($renderer->render('   ', $request));
    }

    public function test_unknown_token_is_left_visible(): void
    {
        $request = $this->makeRequest();

        $html = (new CertificateRenderer())->render('<p>{{not_a_token}}</p>', $request);

        $this->assertStringContainsString('{{not_a_token}}', $html);
    }

    /** A resident must not be able to inject markup via their own profile. */
    public function test_resident_supplied_values_are_escaped(): void
    {
        $this->resident->update([
            'first_name' => 'Maria<script>alert(1)</script>',
            'address'    => '12 Mabini St. <img src=x onerror=alert(1)>',
        ]);

        $request = $this->makeRequest();

        $html = (new CertificateRenderer())->render('<p>{{full_name}} — {{address}}</p>', $request);

        // No tag can form: every angle bracket from resident data is escaped,
        // so the payload renders as visible text rather than markup.
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('<img', $html);
        $this->assertStringContainsString('&lt;img src=x onerror=alert(1)&gt;', $html);
        $this->assertStringContainsString('&lt;/SCRIPT&gt;', $html);

        // Only the template's own tags survive.
        $this->assertSame(2, substr_count($html, '<'), 'Only the <p> open/close tags should remain.');
    }

    /** Dangerous tags in the template itself are stripped too. */
    public function test_disallowed_tags_in_template_are_stripped(): void
    {
        $request = $this->makeRequest();

        $html = (new CertificateRenderer())->render(
            '<p>Hello <strong>{{first_name}}</strong></p><script>alert(1)</script><iframe src="x"></iframe>',
            $request
        );

        $this->assertStringContainsString('<strong>', $html);
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('<iframe', $html);
    }

    /** A custom body replaces the built-in wording on the printed page. */
    public function test_custom_body_overrides_builtin_wording(): void
    {
        $request = $this->makeRequest([
            'body_template' => '<p>CUSTOM WORDING for {{full_name}} of {{barangay_label}}.</p>',
            'header_title'  => 'KATIBAYAN NG PANINIRAHAN',
        ]);

        $response = $this->actingAs($this->admin)->get("/print/certificate/{$request->id}");

        $response->assertOk();
        $response->assertSee('CUSTOM WORDING for MARIA SANTOS');
        $response->assertSee('KATIBAYAN NG PANINIRAHAN');

        // The built-in clearance wording must not also appear.
        $response->assertDontSee('has no derogatory record on file in this barangay');
    }

    /** Without a custom body, the existing document is untouched. */
    public function test_builtin_wording_still_used_when_no_template(): void
    {
        $request = $this->makeRequest();

        $response = $this->actingAs($this->admin)->get("/print/certificate/{$request->id}");

        $response->assertOk();
        $response->assertSee('Barangay Clearance');
        $response->assertDontSee('CUSTOM WORDING');
    }

    /** The custom body follows the configured barangay, not Pili. */
    public function test_custom_body_follows_barangay_settings(): void
    {
        Setting::putMany([
            'barangay.name'         => 'San Isidro',
            'barangay.municipality' => 'Minalabac',
            'barangay.province'     => 'Camarines Sur',
        ], 'identity');

        $request = $this->makeRequest([
            'body_template' => '<p>Issued by {{barangay_label}} of {{location}}.</p>',
        ]);

        $response = $this->actingAs($this->admin)->get("/print/certificate/{$request->id}");

        $response->assertOk();
        $response->assertSee('Issued by Barangay San Isidro of San Isidro, Minalabac, Camarines Sur');
    }

    /** Saving a blank textarea must clear the override, not store "". */
    public function test_blank_body_template_is_stored_as_null(): void
    {
        $certificate = Certificate::create([
            'name'            => 'Custom Certificate',
            'category'        => 'Certification',
            'fee'             => 0,
            'processing_days' => 1,
            'template_file'   => 'certificate.php',
            'body_template'   => '<p>something</p>',
            'status'          => 'active',
        ]);

        $this->actingAs($this->admin)->post('/admin/certificates/store', [
            'certificate_id'  => $certificate->id,
            'name'            => 'Custom Certificate',
            'category'        => 'Certification',
            'fee'             => 0,
            'processing_days' => 1,
            'body_template'   => '   ',
        ])->assertRedirect();

        $this->assertNull($certificate->fresh()->body_template);
    }
}
