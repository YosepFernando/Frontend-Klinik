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
        Schema::table('trainings', function (Blueprint $table) {
            // Drop columns that are no longer needed
            $table->dropForeign(['trainer_id']);
            $table->dropColumn(['trainer_id', 'max_participants', 'materials']);
            
            // Make location nullable (since it's only for offline training)
            $table->string('location')->nullable()->change();
            
            // Add new columns
            $table->string('estimated_duration')->after('description');
            $table->enum('training_type', ['video', 'document', 'offline'])->after('estimated_duration');
            $table->string('url')->nullable()->after('training_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trainings', function (Blueprint $table) {
            // Restore original columns
            $table->foreignId('trainer_id')->constrained('users')->onDelete('cascade');
            $table->integer('max_participants')->default(20);
            $table->text('materials')->nullable();
            
            // Remove new columns
            $table->dropColumn(['estimated_duration', 'training_type', 'url']);
            
            // Make location required again
            $table->string('location')->nullable(false)->change();
        });
    }
};
