<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\Official;
use App\Models\Purok;
use App\Models\Request as CertificateRequest;
use App\Models\Resident;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Proves the system carries no hard-coded barangay identity: reconfiguring
 * the settings table must re-brand pages and printed documents.
 */
class BarangayConfigurationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

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
    }

    /** The migration ships the previous hard-coded values as defaults. */
    public function test_settings_are_seeded_by_the_migration(): void
    {
        $this->assertSame('Pili', setting('barangay.name'));
        $this->assertSame('Barangay Pili', barangay_label());
        $this->assertSame('Pili, Madridejos, Cebu', barangay_location());
        $this->assertSame('Province of Cebu', barangay_province_line());
        $this->assertSame('Municipality of Madridejos', barangay_municipality_line());
    }

    /** Letterhead must not read "City of Cebu City". */
    public function test_municipality_line_avoids_duplicating_the_type(): void
    {
        Setting::putMany([
            'barangay.municipality'      => 'Cebu City',
            'barangay.municipality_type' => 'City',
        ], 'identity');
        $this->assertSame('Cebu City', barangay_municipality_line());

        Setting::putMany([
            'barangay.municipality'      => 'Cebu',
            'barangay.municipality_type' => 'City',
        ], 'identity');
        $this->assertSame('City of Cebu', barangay_municipality_line());

        Setting::putMany([
            'barangay.municipality'      => 'Madridejos',
            'barangay.municipality_type' => 'Municipality',
        ], 'identity');
        $this->assertSame('Municipality of Madridejos', barangay_municipality_line());
    }

    /** An unset province or municipality drops the line entirely. */
    public function test_blank_jurisdiction_lines_are_omitted(): void
    {
        Setting::putMany([
            'barangay.municipality' => '',
            'barangay.province'     => '',
        ], 'identity');

        $this->assertSame('', barangay_municipality_line());
        $this->assertSame('', barangay_province_line());
        $this->assertSame('Pili', barangay_location());
    }

    /** An unset or blank setting falls back to the caller's default. */
    public function test_blank_setting_falls_back_to_default(): void
    {
        Setting::put('barangay.contact', '');

        $this->assertSame('n/a', setting('barangay.contact', 'n/a'));
        $this->assertSame('fallback', setting('does.not.exist', 'fallback'));
    }

    /** Helpers must reflect a write immediately, i.e. the cache is busted. */
    public function test_writing_a_setting_busts_the_cache(): void
    {
        $this->assertSame('Pili', barangay_name());

        Setting::put('barangay.name', 'Bagong Silang');

        $this->assertSame('Bagong Silang', barangay_name());
        $this->assertSame('Barangay Bagong Silang', barangay_label());
    }

    /** Public pages must show the configured barangay, not Pili. */
    public function test_public_pages_rebrand(): void
    {
        Setting::putMany([
            'barangay.name'         => 'San Isidro',
            'barangay.municipality' => 'Minalabac',
            'barangay.province'     => 'Camarines Sur',
        ], 'identity');

        foreach (['/login', '/track'] as $url) {
            $response = $this->get($url);

            $response->assertOk();
            $response->assertSee('Barangay San Isidro');
            $response->assertDontSee('Barangay Pili');
            $response->assertDontSee('Madridejos');
        }
    }

    /** Printed certificates are the output that matters most. */
    public function test_printed_certificate_rebrands(): void
    {
        Setting::putMany([
            'barangay.name'         => 'San Isidro',
            'barangay.municipality' => 'Minalabac',
            'barangay.province'     => 'Camarines Sur',
            'barangay.office_title' => 'Office of the Punong Barangay',
        ], 'identity');

        Official::create(['name' => 'HON. JUAN DELA CRUZ', 'position' => 'Punong Barangay', 'status' => 'active']);

        $resident = Resident::create([
            'first_name'    => 'Maria',
            'last_name'     => 'Santos',
            'gender'        => 'Female',
            'birthdate'     => '1990-05-05',
            'civil_status'  => 'Single',
            'address'       => '12 Mabini St.',
            'purok'         => 'Purok 1',
            'status'        => 'active',
        ]);

        $certificate = Certificate::create([
            'name'            => 'Barangay Clearance',
            'category'        => 'Clearance',
            'fee'             => 50,
            'processing_days' => 1,
            'template_file'   => 'certificate_clearance.php',
            'status'          => 'active',
        ]);

        $request = CertificateRequest::create([
            'resident_id'     => $resident->id,
            'certificate_id'  => $certificate->id,
            'tracking_number' => 'TEST-0001',
            'purpose'         => 'employment',
            'status'          => 'approved',
            'quantity'        => 1,
        ]);

        $response = $this->actingAs($this->admin)->get("/print/certificate/{$request->id}");

        $response->assertOk();
        $response->assertSee('BARANGAY SAN ISIDRO');
        $response->assertSee('Province of Camarines Sur');
        $response->assertSee('Municipality of Minalabac');
        $response->assertSee('Office of the Punong Barangay');
        $response->assertDontSee('BARANGAY PILI');
        $response->assertDontSee('Madridejos');
    }

    /** The tracking prefix follows the configured barangay. */
    public function test_tracking_prefix_is_configurable(): void
    {
        Setting::put('system.tracking_prefix', 'SISI', 'string', 'system');

        $this->assertSame('SISI', setting('system.tracking_prefix', 'BRGY'));
    }

    /** Both new admin pages must render. */
    public function test_admin_pages_render(): void
    {
        Purok::create(['name' => 'Purok 1', 'sort_order' => 0, 'status' => 'active']);

        $this->actingAs($this->admin)->get('/admin/settings')
            ->assertOk()
            ->assertSee('Barangay Identity')
            ->assertSee('Tracking Number Prefix')
            ->assertSee('settings[barangay__name]', false);

        $this->actingAs($this->admin)->get('/admin/puroks')
            ->assertOk()
            ->assertSee('Purok 1');
    }

    /** The purok dropdown appears on the registration form once defined. */
    public function test_purok_dropdown_replaces_free_text(): void
    {
        $this->get('/login')->assertOk()->assertSee('name="purok"', false);

        Purok::create(['name' => 'Purok Mangga', 'sort_order' => 0, 'status' => 'active']);

        $this->get('/login')
            ->assertOk()
            ->assertSee('Purok Mangga')
            ->assertSee('— Select Purok —');
    }

    /** The settings form writes through and is admin-only. */
    public function test_admin_can_save_settings(): void
    {
        $payload = [
            'settings' => [
                'barangay__name'              => 'Bagong Silang',
                'barangay__municipality'      => 'Minalabac',
                'barangay__municipality_type' => 'Municipality',
                'barangay__province'          => 'Camarines Sur',
                'system__tracking_prefix'     => 'BSIL',
            ],
        ];

        $this->actingAs($this->admin)
            ->post('/admin/settings', $payload)
            ->assertRedirect('/admin/settings');

        $this->assertSame('Bagong Silang', setting('barangay.name'));
        $this->assertSame('BSIL', setting('system.tracking_prefix'));
    }

    public function test_settings_page_rejects_a_non_admin(): void
    {
        $staff = User::create([
            'username' => 'staff1',
            'email'    => 'staff@example.test',
            'password' => bcrypt('password'),
            'role'     => 'staff',
            'status'   => 'active',
        ]);

        // RoleMiddleware denies by redirecting back to the admin login with
        // an error, rather than returning a 403.
        $this->actingAs($staff)
            ->get('/admin/settings')
            ->assertRedirect(route('admin.login'))
            ->assertSessionHas('error', 'Unauthorized access.');
    }

    /** A bad tracking prefix must not reach the database. */
    public function test_tracking_prefix_is_validated(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/settings', [
                'settings' => [
                    'barangay__name'              => 'Pili',
                    'barangay__municipality'      => 'Madridejos',
                    'barangay__municipality_type' => 'Municipality',
                    'barangay__province'          => 'Cebu',
                    'system__tracking_prefix'     => 'bad prefix!',
                ],
            ])
            ->assertSessionHasErrors('settings.system__tracking_prefix');

        $this->assertSame('PILI', setting('system.tracking_prefix'));
    }

    /** The migration endpoint stays closed unless a key is configured. */
    public function test_migration_endpoint_is_disabled_without_a_key(): void
    {
        config(['barangay.migration_key' => null]);

        $this->get('/run-migrations')->assertNotFound();

        // The key that used to be hard-coded in the source must not work.
        $this->get('/run-migrations?key=pili2026')->assertNotFound();
    }

    public function test_migration_endpoint_rejects_a_wrong_key(): void
    {
        config(['barangay.migration_key' => 'a-real-secret']);

        $this->get('/run-migrations')->assertForbidden();
        $this->get('/run-migrations?key=guess')->assertForbidden();
    }

    /** The mobile check honours the configured token and the legacy one. */
    public function test_mobile_user_agent_detection(): void
    {
        config(['barangay.mobile_ua_token' => 'BrgyPortalApp']);

        $this->assertFalse(is_mobile_app(request()->replace([])));

        $make = fn (string $ua) => \Illuminate\Http\Request::create('/', 'GET', [], [], [], ['HTTP_USER_AGENT' => $ua]);

        $this->assertTrue(is_mobile_app($make('Mozilla/5.0 BrgyPortalApp/1.0')));
        $this->assertTrue(is_mobile_app($make('Mozilla/5.0 BrgyPiliApp/1.0')), 'legacy APKs must keep working');
        $this->assertFalse(is_mobile_app($make('Mozilla/5.0 Chrome/120')));
    }

    /** Renaming a purok must carry across to the residents that use it. */
    public function test_renaming_a_purok_updates_residents(): void
    {
        $purok = Purok::create(['name' => 'Purok 1', 'sort_order' => 0, 'status' => 'active']);

        $resident = Resident::create([
            'first_name'   => 'Pedro',
            'last_name'    => 'Reyes',
            'gender'       => 'Male',
            'birthdate'    => '1985-01-01',
            'civil_status' => 'Married',
            'address'      => '5 Rizal St.',
            'purok'        => 'Purok 1',
            'status'       => 'active',
        ]);

        $this->actingAs($this->admin)->post('/admin/puroks/store', [
            'purok_id'   => $purok->id,
            'name'       => 'Purok Mangga',
            'sort_order' => 0,
            'status'     => 'active',
        ])->assertRedirect('/admin/puroks');

        $this->assertSame('Purok Mangga', $resident->fresh()->purok);
    }

    /** A purok still in use is deactivated rather than deleted. */
    public function test_purok_in_use_is_deactivated_not_deleted(): void
    {
        $purok = Purok::create(['name' => 'Purok 2', 'sort_order' => 1, 'status' => 'active']);

        Resident::create([
            'first_name'   => 'Ana',
            'last_name'    => 'Cruz',
            'gender'       => 'Female',
            'birthdate'    => '1995-02-02',
            'civil_status' => 'Single',
            'address'      => '7 Luna St.',
            'purok'        => 'Purok 2',
            'status'       => 'active',
        ]);

        $this->actingAs($this->admin)->get("/admin/puroks/delete/{$purok->id}");

        $this->assertDatabaseHas('puroks', ['id' => $purok->id, 'status' => 'inactive']);
    }

    /** An unused purok is removed outright. */
    public function test_unused_purok_is_deleted(): void
    {
        $purok = Purok::create(['name' => 'Purok 9', 'sort_order' => 9, 'status' => 'active']);

        $this->actingAs($this->admin)->get("/admin/puroks/delete/{$purok->id}");

        $this->assertDatabaseMissing('puroks', ['id' => $purok->id]);
    }
}
