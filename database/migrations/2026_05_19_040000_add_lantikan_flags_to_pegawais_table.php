<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Baseline addition of the jenis lantikan flags is_tetap and
     * is_kontrak_interim. They existed only in the live database with no
     * matching migration, so fresh migrates produced a schema that did not
     * match the Pegawai model. Guarded with hasColumn() so databases where
     * they already exist are untouched.
     */
    public function up(): void
    {
        if (! Schema::hasTable('pegawais')) {
            return;
        }

        Schema::table('pegawais', function (Blueprint $table) {
            if (! Schema::hasColumn('pegawais', 'is_tetap')) {
                $table->boolean('is_tetap')->default(false)->after('tarikh_pencen');
            }

            if (! Schema::hasColumn('pegawais', 'is_kontrak_interim')) {
                $table->boolean('is_kontrak_interim')->default(false)->after('is_kontrak');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            foreach (['is_kontrak_interim', 'is_tetap'] as $column) {
                if (Schema::hasColumn('pegawais', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
