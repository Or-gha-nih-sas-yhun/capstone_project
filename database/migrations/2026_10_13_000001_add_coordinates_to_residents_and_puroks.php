<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('residents')) {
            Schema::table('residents', function (Blueprint $table) {
                if (! Schema::hasColumn('residents', 'latitude')) {
                    $table->decimal('latitude', 10, 8)->nullable()->after('address');
                }
                if (! Schema::hasColumn('residents', 'longitude')) {
                    $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
                }
            });
        }

        if (Schema::hasTable('puroks')) {
            Schema::table('puroks', function (Blueprint $table) {
                if (! Schema::hasColumn('puroks', 'latitude')) {
                    $table->decimal('latitude', 10, 8)->nullable()->after('name');
                }
                if (! Schema::hasColumn('puroks', 'longitude')) {
                    $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
                }
                if (! Schema::hasColumn('puroks', 'color')) {
                    $table->string('color', 20)->nullable()->after('longitude');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('residents')) {
            Schema::table('residents', function (Blueprint $table) {
                if (Schema::hasColumn('residents', 'latitude')) {
                    $table->dropColumn('latitude');
                }
                if (Schema::hasColumn('residents', 'longitude')) {
                    $table->dropColumn('longitude');
                }
            });
        }

        if (Schema::hasTable('puroks')) {
            Schema::table('puroks', function (Blueprint $table) {
                if (Schema::hasColumn('puroks', 'latitude')) {
                    $table->dropColumn('latitude');
                }
                if (Schema::hasColumn('puroks', 'longitude')) {
                    $table->dropColumn('longitude');
                }
                if (Schema::hasColumn('puroks', 'color')) {
                    $table->dropColumn('color');
                }
            });
        }
    }
};
