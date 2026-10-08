<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('residents', function (Blueprint $table) {
            $table->boolean('is_pwd')->default(false)->after('civil_status');
            $table->boolean('is_single_parent')->default(false)->after('is_pwd');
        });
    }

    public function down()
    {
        Schema::table('residents', function (Blueprint $table) {
            $table->dropColumn(['is_pwd', 'is_single_parent']);
        });
    }
};
