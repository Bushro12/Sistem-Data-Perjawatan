<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ptjs', function (Blueprint $table) {
            $table->foreignId('parlimen_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('dun_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ptjs', function (Blueprint $table) {
            $table->dropForeign(['parlimen_id']);
            $table->dropForeign(['dun_id']);
            $table->dropColumn(['parlimen_id', 'dun_id']);
        });
    }
};
