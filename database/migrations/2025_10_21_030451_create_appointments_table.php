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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_id'); // 关联 Staff
            $table->string('visitor_name');
            $table->string('nric_passport');
            $table->string('phone_number');
            $table->string('email');
            $table->string('purpose');
            $table->string('personal_in_charge');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', ['pending', 'approved', 'cancelled', 'completed'])->default('pending');
            $table->timestamps();

            $table->foreign('staff_id')
                  ->references('id')
                  ->on('staff')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
