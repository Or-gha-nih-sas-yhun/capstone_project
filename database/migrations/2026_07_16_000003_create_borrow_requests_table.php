<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('borrow_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->constrained('residents')->onDelete('cascade');
            $table->string('item_type', 100); // e.g. tent, chair, or both
            $table->integer('tent_quantity')->default(0);
            $table->integer('chair_quantity')->default(0);
            $table->date('borrow_date');
            $table->date('return_date');
            $table->text('purpose');
            $table->string('verification_document', 255); // path/filename of uploaded document
            $table->enum('status', ['pending', 'approved', 'rejected', 'returned'])->default('pending');
            $table->text('remarks')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('borrow_requests');
    }
};
