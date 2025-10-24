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
        Schema::create('availability_frames', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->onDelete('cascade');
            $table->date('date')->nullable();
            $table->string('day_of_week')->nullable();
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('duration'); // 单位：分钟
            $table->integer('interval')->default(0); // 可选：间隔分钟
            $table->boolean('is_recurring')->default(false);
            $table->uuid('repeat_group_id')->nullable(); // 用于批量删除同一组 recurring frame
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('availability_frames');
    }
};
