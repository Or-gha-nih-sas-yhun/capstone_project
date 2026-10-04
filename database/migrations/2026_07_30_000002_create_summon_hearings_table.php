<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('summon_hearings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('summon_id')->constrained('summons')->onDelete('cascade');
            $table->integer('hearing_number')->default(1);
            $table->dateTime('schedule_date');
            $table->text('remarks')->nullable();
            $table->string('conducted_by', 150)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('summon_hearings');
    }
};
