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
        Schema::table('pegawai_kontraks', function (Blueprint $table) {
            $table->integer('program_id')->nullable()->after('pegawai_id');
            $table->integer('aktiviti_id')->nullable()->after('program_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pegawai_kontraks', function (Blueprint $table) {
            //
        });
    }
};
