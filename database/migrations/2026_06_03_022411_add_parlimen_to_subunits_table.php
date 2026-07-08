<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subunits', function (Blueprint $table) {
            $table->foreignId('parlimen_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('subunits', function (Blueprint $table) {
            $table->dropForeign(['parlimen_id']);
            $table->dropColumn('parlimen_id');
        });
    }
};
