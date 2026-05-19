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
        Schema::create('ptjs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nama_ptj');
            $table->integer('kod_ptj');
            $table->text('alamat');
            $table->string('pengarah');
            $table->tinyInteger('is_jkn')->default(0);
            $table->string('rujukan_surat', 35)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ptjs');
    }
};
