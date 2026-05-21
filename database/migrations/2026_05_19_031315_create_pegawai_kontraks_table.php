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
        Schema::create('pegawai_kontraks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('pegawai_id');
            $table->date('tarikh_lantikan1');
            $table->date('tarikh_tamat1');
            $table->date('tarikh_lantikan2')->nullable();
            $table->date('tarikh_tamat2')->nullable();
            $table->date('tarikh_lantikan3')->nullable();
            $table->date('tarikh_tamat3')->nullable();
            $table->date('tarikh_lantikan4')->nullable();
            $table->date('tarikh_tamat4')->nullable();
            $table->date('tarikh_lantikan5')->nullable();
            $table->date('tarikh_tamat5')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pegawai_kontraks');
    }
};
