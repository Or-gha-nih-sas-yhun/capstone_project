<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->string('category', 80)->default('General');
            $table->decimal('fee', 10, 2)->default(0.00);
            $table->integer('processing_days')->default(1);
            $table->string('template_file', 255)->nullable();
            $table->text('requirements')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->dateTime('archived_at')->nullable();
            $table->integer('archived_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('certificates');
    }
};
