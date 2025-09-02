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
        Schema::create('recruitments', function (Blueprint $table) {
            $table->id();
            $table->string('position');
            $table->unsignedInteger('id_posisi')->nullable();
            $table->text('description');
            $table->text('requirements');
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->date('application_deadline');
            $table->integer('slots')->default(1);
            $table->decimal('salary_min', 10, 2)->nullable();
            $table->decimal('salary_max', 10, 2)->nullable();
            $table->enum('employment_type', ['full_time', 'part_time', 'contract'])->default('full_time');
            $table->integer('age_min')->unsigned()->nullable();
            $table->integer('age_max')->unsigned()->nullable();
            $table->timestamps();
            
            $table->foreign('id_posisi')->references('id_posisi')->on('tb_posisi')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitments');
    }
};
