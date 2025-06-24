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
        Schema::table('users', function (Blueprint $table) {
            // Tambah kolom baru sesuai tb_user schema
            $table->string('no_telp')->unique()->after('email');
            
            // Update role enum untuk menambahkan role baru
            $table->dropColumn('role');
        });
        
        // Tambah ulang kolom role dengan enum yang lengkap
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', [
                'pelanggan', 
                'dokter', 
                'beautician', 
                'front_office', 
                'kasir', 
                'admin',
                'hrd'
            ])->default('pelanggan')->after('password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['no_telp']);
            $table->dropColumn('role');
        });
        
        // Kembalikan role yang lama
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('pelanggan')->after('password');
        });
    }
};
