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
        Schema::create('letak_jawatans', function (Blueprint $table) {
            $table->id();
            $table->integer('ptj_id');
            $table->integer('jawatan_gred_id');
            $table->string('nama');
            $table->string('nokp');
            $table->date('tarikh_notis');
            $table->date('tarikh_kuatkuasa');
            $table->string('jenis_notis');
            $table->text('alasan');
            $table->date('tarikh_lantik');
            $table->string('lantikan', 20);
            $table->boolean('ikatan_jpa');
            $table->boolean('ikatan_bpl');
            $table->boolean('pinjaman_lppsa');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('letak_jawatans');
    }
};
