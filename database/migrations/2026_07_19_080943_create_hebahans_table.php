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
        Schema::create('hebahans', function (Blueprint $table) {
            $table->id();
            $table->string('tajuk');
            $table->longText('kandungan')->nullable();
            $table->date('tarikh_hebahan');
            $table->string('lampiran')->nullable();
            $table->string('status')->default('draft');
            $table->date('dipaparkan_sehingga')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hebahans');
    }
};
