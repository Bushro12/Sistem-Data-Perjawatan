<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            $table->boolean('ada_unit')
                ->default(false)
                ->comment('true = ada unit');

            $table->boolean('ada_subunit')
                ->default(false)
                ->comment('true = ada subunit');


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            $table->dropColumn([
                'ada_unit',
                'ada_subunit',
            ]);


        });
    }
};
