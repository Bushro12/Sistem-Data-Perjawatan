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
        Schema::create('butiran__jawatans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('butiran_id');
            $table->integer('jawatan_gred_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('butiran__jawatans');
    }
};
