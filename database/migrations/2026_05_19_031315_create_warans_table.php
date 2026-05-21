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
        Schema::create('warans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('no_waran', 100);
            $table->string('jenis', 10)->nullable();
            $table->integer('jik')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('parent_id')->nullable()->index('warans_parent_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warans');
    }
};
