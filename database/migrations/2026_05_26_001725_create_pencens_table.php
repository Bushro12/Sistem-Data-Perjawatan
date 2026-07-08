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
        Schema::create('pencens', function (Blueprint $table) {
            $table->id();
            $table->integer('ptj_id');
            $table->integer('jawatan-gred_id');
            $table->integer('opsyen_pencen_id');
            $table->string('nama');
            $table->string('nokp');
            $table->date('tarikh_lantikan');
            $table->date('tarikh_sah_jawatan');
            $table->integer('umur_pencen');
            $table->date('tarikh_pencen');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pencens');
    }
};
