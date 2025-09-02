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
        Schema::create('religious_study_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('religious_study_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('attended')->default(false);
            $table->text('feedback')->nullable();
            $table->timestamps();
            
            $table->unique(['religious_study_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('religious_study_participants');
    }
};
