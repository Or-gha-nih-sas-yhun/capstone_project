<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_number', 50)->unique();
            $table->foreignId('resident_id')->constrained('residents')->onDelete('cascade');
            $table->foreignId('certificate_id')->constrained('certificates');
            $table->text('purpose');
            $table->enum('status', ['pending', 'processing', 'approved', 'rejected', 'released'])->default('pending');
            $table->text('remarks')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->dateTime('requested_at')->useCurrent();
            $table->dateTime('processed_at')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('released_at')->nullable();
            $table->dateTime('archived_at')->nullable();
            $table->integer('archived_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('requests');
    }
};
