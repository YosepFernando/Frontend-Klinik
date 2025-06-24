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
            $table->string('full_name')->after('user_id');
            $table->string('nik', 16)->after('full_name');
            $table->string('email')->after('nik');
            $table->string('phone')->after('email');
            $table->text('address')->after('phone');
            $table->enum('education', ['SD', 'SMP', 'SMA', 'D1', 'D2', 'D3', 'D4', 'S1', 'S2', 'S3'])->after('address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recruitment_applications', function (Blueprint $table) {
            $table->dropColumn(['full_name', 'nik', 'email', 'phone', 'address', 'education']);
        });
    }
};
