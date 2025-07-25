<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tb_pelatihan', function (Blueprint $table) {
            $table->increments('id_pelatihan');
            $table->string('judul', 100);
            $table->text('deskripsi')->nullable();
            $table->string('jenis_pelatihan', 50)->nullable();
            $table->longText('konten')->nullable();
            $table->string('link_url', 255)->nullable();
            $table->integer('durasi')->unsigned()->nullable();
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_pelatihan');
    }
};
