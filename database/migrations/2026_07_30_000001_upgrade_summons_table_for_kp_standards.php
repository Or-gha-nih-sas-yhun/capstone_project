<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('summons', function (Blueprint $table) {
            $table->string('case_type', 50)->default('summon')->after('case_number');
            $table->dateTime('incident_date')->nullable()->after('complain_details');
            $table->string('incident_location', 255)->nullable()->after('incident_date');
            $table->string('nature_of_complaint', 255)->nullable()->after('incident_location');
            $table->dateTime('archived_at')->nullable();
            $table->integer('archived_by')->nullable();
        });

        // Use raw statements to modify existing columns to support SQLite and MySQL without doctrine/dbal issues
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE summons MODIFY schedule_date DATETIME NULL');
            DB::statement("ALTER TABLE summons MODIFY status VARCHAR(50) NOT NULL DEFAULT 'pending'");
        } else {
            // Fallback/standard schema changes for other drivers (like sqlite if used in tests)
            // SQLite doesn't enforce null/not null or enums strictly, but we do this for safety
            try {
                Schema::table('summons', function (Blueprint $table) {
                    $table->dateTime('schedule_date')->nullable()->change();
                    $table->string('status', 50)->default('pending')->change();
                });
            } catch (\Exception $e) {
                // Ignore if driver doesn't support changing columns natively
            }
        }
    }

    public function down()
    {
        Schema::table('summons', function (Blueprint $table) {
            $table->dropColumn([
                'case_type',
                'incident_date',
                'incident_location',
                'nature_of_complaint',
                'archived_at',
                'archived_by'
            ]);
        });
    }
};
