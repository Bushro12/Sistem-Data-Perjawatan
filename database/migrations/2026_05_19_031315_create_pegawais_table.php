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
        Schema::create('pegawais', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('ptj_id');
            $table->integer('bahagian_id');
            $table->integer('unit_id');
            $table->integer('subunit_id');
            $table->integer('jawatan_gred_id');
            $table->integer('opsyen_pencen_id')->nullable();
            $table->string('nama');
            $table->string('nokp');
            $table->string('jantina');
            $table->date('tarikh_lantikan')->nullable();
            $table->date('tarikh_sah_jawatan')->nullable();
            $table->date('tarikh_pencen')->nullable();
            $table->boolean('is_kontrak')->default(false);
            $table->boolean('is_kup')->default(false);
            $table->boolean('is_kupj')->default(false);
            $table->boolean('is_jtw')->default(false);
            $table->timestamps();
            $table->string('emel', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pegawais');
    }
};
