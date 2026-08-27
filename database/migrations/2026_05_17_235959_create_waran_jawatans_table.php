<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Baseline creation of waran_jawatans. The table originally existed only
     * in the live database with no matching migration, which made fresh
     * migrates (and RefreshDatabase tests) fail because
     * add_soft_deletes_to_waran_jawatans_table alters a non-existent table.
     * Guarded with hasTable() so databases where it already exists are untouched.
     */
    public function up(): void
    {
        if (Schema::hasTable('waran_jawatans')) {
            return;
        }

        Schema::create('waran_jawatans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('waran_id');
            $table->integer('ptj_id')->nullable();
            $table->integer('bahagian_id')->nullable();
            $table->integer('unit_id')->nullable();
            $table->integer('subunit_id')->nullable();
            $table->integer('aktiviti_id')->nullable();
            $table->integer('pegawai_id')->nullable();
            $table->json('jawatan_ids')->nullable();
            $table->json('gred_ids')->nullable();
            $table->integer('jawatan_gred_id')->nullable();
            $table->boolean('is_kup')->default(false);
            $table->string('butiran')->nullable();
            $table->integer('waran_tolak_id')->nullable();
            $table->text('catatan_jawatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waran_jawatans');
    }
};
