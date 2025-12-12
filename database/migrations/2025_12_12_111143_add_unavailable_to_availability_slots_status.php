<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE availability_slots MODIFY COLUMN status ENUM('available', 'booked', 'unavailable') DEFAULT 'available'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE availability_slots MODIFY COLUMN status ENUM('available', 'booked') DEFAULT 'available'");
    }
};
