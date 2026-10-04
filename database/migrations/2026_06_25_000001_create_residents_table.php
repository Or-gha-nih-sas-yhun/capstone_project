<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('residents', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100);
            $table->string('suffix', 20)->nullable();
            $table->enum('gender', ['Male', 'Female', 'Other']);
            $table->date('birthdate');
            $table->enum('civil_status', ['Single', 'Married', 'Widowed', 'Separated', 'Divorced'])->default('Single');
            $table->string('nationality', 80)->default('Filipino');
            $table->string('religion', 100)->nullable();
            $table->string('occupation', 150)->nullable();
            $table->string('contact_number', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('address');
            $table->string('purok', 100)->nullable();
            $table->enum('voter_status', ['Registered', 'Not Registered'])->default('Not Registered');
            $table->integer('years_of_residency')->default(0);
            $table->string('photo', 255)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->dateTime('archived_at')->nullable();
            $table->integer('archived_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('residents');
    }
};
