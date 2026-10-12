<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('residents', 'is_pwd')) {
            Schema::table('residents', function (Blueprint $table) {
                $table->boolean('is_pwd')->default(false)->after('civil_status');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('residents', 'is_pwd')) {
            Schema::table('residents', function (Blueprint $table) {
                $table->dropColumn('is_pwd');
            });
        }
    }
};
