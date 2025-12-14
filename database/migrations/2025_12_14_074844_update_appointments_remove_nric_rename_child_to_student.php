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
        Schema::table('appointments', function (Blueprint $table) {
            // Remove nric_passport column
            $table->dropColumn('nric_passport');

            // Rename child_name to student_name
            $table->renameColumn('child_name', 'student_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Restore nric_passport column
            $table->string('nric_passport', 50)->nullable()->after('visitor_name');

            // Rename student_name back to child_name
            $table->renameColumn('student_name', 'child_name');
        });
    }
};
