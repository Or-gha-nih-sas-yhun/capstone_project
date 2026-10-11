<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key', 100)->primary();
            $table->text('value')->nullable();
            $table->string('type', 20)->default('string');
            $table->string('group', 50)->default('general');
            $table->timestamps();
        });

        // Seed the values the system previously hard-coded, so an existing
        // deployment behaves identically right after migrating.
        $now = now();
        $rows = [];
        foreach (self::defaults() as $key => $meta) {
            $rows[] = [
                'key'        => $key,
                'value'      => $meta[0],
                'type'       => $meta[1],
                'group'      => $meta[2],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('settings')->insert($rows);
    }

    public function down()
    {
        Schema::dropIfExists('settings');
    }

    /**
     * key => [value, type, group]
     */
    public static function defaults(): array
    {
        return [
            // ── Identity ──────────────────────────────────────────────
            'barangay.name'            => ['Pili', 'string', 'identity'],
            'barangay.municipality'    => ['Madridejos', 'string', 'identity'],
            'barangay.municipality_type' => ['Municipality', 'string', 'identity'],
            'barangay.province'        => ['Cebu', 'string', 'identity'],
            'barangay.region'          => ['Region VII (Central Visayas)', 'string', 'identity'],
            'barangay.address'         => ['Barangay Pili, Madridejos, Cebu', 'string', 'identity'],
            'barangay.email'           => ['brgy.pili.mad@gmail.com', 'string', 'identity'],
            'barangay.contact'         => ['', 'string', 'identity'],
            'barangay.hall_name'       => ['Barangay Pili Hall', 'string', 'identity'],
            'barangay.office_title'    => ['Office of the Barangay Captain', 'string', 'identity'],
            'barangay.session_room'    => ['Barangay Pili Hall, Session Room', 'string', 'identity'],

            // ── Branding ──────────────────────────────────────────────
            'brand.app_title'          => ['Barangay Pili Clearance & Certificate System', 'string', 'branding'],
            'brand.short_name'         => ['Brgy. Pili', 'string', 'branding'],
            'brand.portal_name'        => ['Barangay Pili Portal', 'string', 'branding'],
            'brand.logo'               => ['assets/images/pili_logo.png', 'image', 'branding'],
            'brand.municipality_logo'  => ['assets/images/municipality_logo.png', 'image', 'branding'],
            'brand.favicon'            => ['assets/images/pili_logo.png', 'image', 'branding'],

            // ── System ────────────────────────────────────────────────
            'system.tracking_prefix'   => ['PILI', 'string', 'system'],
            'system.sms_signature'     => ['Barangay Pili', 'string', 'system'],
            'system.admin_email'       => ['admin@brgy-pili.gov.ph', 'string', 'system'],
            'system.apk_path'          => ['downloads/barangay-pili-resident-portal-v1.0.3.apk', 'string', 'system'],
            'system.setup_complete'    => ['0', 'boolean', 'system'],
        ];
    }
};
