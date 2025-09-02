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
        Schema::table('attendances', function (Blueprint $table) {
            // Rename existing columns to match new naming convention
            $table->renameColumn('check_in', 'clock_in');
            $table->renameColumn('check_out', 'clock_out');
            
            // Add new location fields
            $table->decimal('clock_in_latitude', 10, 8)->nullable()->after('clock_in');
            $table->decimal('clock_in_longitude', 11, 8)->nullable()->after('clock_in_latitude');
            $table->decimal('clock_out_latitude', 10, 8)->nullable()->after('clock_out');
            $table->decimal('clock_out_longitude', 11, 8)->nullable()->after('clock_out_latitude');
            $table->text('clock_in_address')->nullable()->after('clock_in_longitude');
            $table->text('clock_out_address')->nullable()->after('clock_out_longitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Remove added columns
            $table->dropColumn([
                'clock_in_latitude',
                'clock_in_longitude', 
                'clock_out_latitude',
                'clock_out_longitude',
                'clock_in_address',
                'clock_out_address'
            ]);
            
            // Rename back to original column names
            $table->renameColumn('clock_in', 'check_in');
            $table->renameColumn('clock_out', 'check_out');
        });
    }
};
