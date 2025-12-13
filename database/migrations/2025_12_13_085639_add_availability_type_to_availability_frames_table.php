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
        Schema::table('availability_frames', function (Blueprint $table) {
            $table->enum('availability_type', ['public', 'private'])->default('public')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('availability_frames', function (Blueprint $table) {
            $table->dropColumn('availability_type');
        });
    }
};
