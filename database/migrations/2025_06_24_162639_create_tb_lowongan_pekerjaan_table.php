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
        Schema::create('tb_lowongan_pekerjaan', function (Blueprint $table) {
            $table->increments('id_lowongan_pekerjaan');
            $table->string('judul_pekerjaan', 100);
            $table->unsignedInteger('id_posisi');
            $table->integer('jumlah_lowongan')->unsigned();
            $table->string('pengalaman_minimal', 50)->nullable();
            $table->integer('usia_minimal')->unsigned()->nullable();
            $table->integer('usia_maksimal')->unsigned()->nullable();
            $table->decimal('gaji_minimal', 12, 2)->nullable();
            $table->decimal('gaji_maksimal', 12, 2)->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->text('deskripsi')->nullable();
            $table->text('persyaratan')->nullable();
            $table->timestamps();
            
            $table->foreign('id_posisi')->references('id_posisi')->on('tb_posisi')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_lowongan_pekerjaan');
    }
};
