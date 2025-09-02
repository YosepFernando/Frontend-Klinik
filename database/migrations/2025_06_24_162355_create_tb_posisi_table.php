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
        Schema::create('tb_posisi', function (Blueprint $table) {
            $table->increments('id_posisi');
            $table->string('nama_posisi', 50)->unique();
            $table->decimal('gaji_pokok', 12, 2);
            $table->decimal('persen_bonus', 5, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_posisi');
    }
};
