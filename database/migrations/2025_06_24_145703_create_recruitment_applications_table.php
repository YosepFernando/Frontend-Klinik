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
        Schema::create('recruitment_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recruitment_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('cover_letter')->nullable();
            $table->string('cv_file')->nullable();
            $table->json('additional_documents')->nullable();
            
            // Stage 1: Document Selection
            $table->enum('document_status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->text('document_notes')->nullable();
            $table->timestamp('document_reviewed_at')->nullable();
            $table->foreignId('document_reviewed_by')->nullable()->constrained('users');
            
            // Stage 2: Interview
            $table->enum('interview_status', ['pending', 'scheduled', 'completed', 'accepted', 'rejected'])->default('pending');
            $table->datetime('interview_scheduled_at')->nullable();
            $table->string('interview_location')->nullable();
            $table->text('interview_notes')->nullable();
            $table->integer('interview_score')->nullable(); // 1-100
            $table->timestamp('interview_completed_at')->nullable();
            $table->foreignId('interview_conducted_by')->nullable()->constrained('users');
            
            // Stage 3: Final Result
            $table->enum('final_status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->text('final_notes')->nullable();
            $table->timestamp('final_decided_at')->nullable();
            $table->foreignId('final_decided_by')->nullable()->constrained('users');
            
            // Overall application status
            $table->enum('overall_status', ['applied', 'document_review', 'interview_stage', 'final_review', 'accepted', 'rejected'])->default('applied');
            
            $table->timestamps();
            
            // Ensure user can only apply once per recruitment
            $table->unique(['recruitment_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitment_applications');
    }
};
