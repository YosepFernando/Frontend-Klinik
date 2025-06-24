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
        Schema::create('tb_gaji', function (Blueprint $table) {
            $table->increments('id_gaji');
            $table->unsignedInteger('id_pegawai');
            $table->tinyInteger('periode_bulan')->unsigned();
            $table->integer('periode_tahun')->unsigned();
            $table->decimal('gaji_pokok', 12, 2)->default(0.00);
            $table->decimal('gaji_bonus', 12, 2)->default(0.00);
            $table->decimal('gaji_kehadiran', 12, 2)->default(0.00);
            $table->decimal('gaji_total', 12, 2);
            $table->date('tanggal_pembayaran')->nullable();
            $table->enum('status', ['Terbayar', 'Belum Terbayar'])->default('Belum Terbayar');
            $table->timestamps();
            
            $table->foreign('id_pegawai')->references('id_pegawai')->on('tb_pegawai')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_gaji');
    }
};
