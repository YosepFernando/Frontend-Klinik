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
        Schema::table('recruitment_applications', function (Blueprint $table) {
            // Add missing fields needed for the application
            $table->string('cv_path')->after('cover_letter')->nullable();
            $table->string('cover_letter_path')->after('cv_path')->nullable();
            $table->string('additional_documents_path')->after('cover_letter_path')->nullable();
            $table->datetime('interview_date')->after('interview_status')->nullable();
            $table->foreignId('interview_scheduled_by')->after('interview_conducted_by')->nullable()->constrained('users');
            $table->date('start_date')->after('final_decided_by')->nullable();
            
            // Update final_status to include waiting_list
            $table->dropColumn('final_status');
        });
        
        // Add the updated enum after dropping the column
        Schema::table('recruitment_applications', function (Blueprint $table) {
            $table->enum('final_status', ['pending', 'accepted', 'rejected', 'waiting_list'])->default('pending')->after('interview_conducted_by');
        });
        
        // Update interview status to include passed/failed
        Schema::table('recruitment_applications', function (Blueprint $table) {
            $table->dropColumn('interview_status');
        });
        
        Schema::table('recruitment_applications', function (Blueprint $table) {
            $table->enum('interview_status', ['pending', 'scheduled', 'passed', 'failed'])->default('pending')->after('document_reviewed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recruitment_applications', function (Blueprint $table) {
            $table->dropColumn([
                'cv_path',
                'cover_letter_path', 
                'additional_documents_path',
                'interview_date',
                'interview_scheduled_by',
                'start_date'
            ]);
            
            // Restore original enums
            $table->dropColumn('final_status');
            $table->dropColumn('interview_status');
        });
        
        Schema::table('recruitment_applications', function (Blueprint $table) {
            $table->enum('final_status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->enum('interview_status', ['pending', 'scheduled', 'completed', 'accepted', 'rejected'])->default('pending');
        });
    }
};
