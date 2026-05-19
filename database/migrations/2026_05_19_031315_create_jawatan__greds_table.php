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
        Schema::create('jawatan__greds', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('jawatan_id');
            $table->integer('gred_id');
            $table->integer('kumpulan_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jawatan__greds');
    }
};
