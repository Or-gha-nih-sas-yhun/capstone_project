<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('admin_otp_code', 6)->nullable()->after('verification_code');
            $table->timestamp('admin_otp_expires_at')->nullable()->after('admin_otp_code');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['admin_otp_code', 'admin_otp_expires_at']);
        });
    }
};
