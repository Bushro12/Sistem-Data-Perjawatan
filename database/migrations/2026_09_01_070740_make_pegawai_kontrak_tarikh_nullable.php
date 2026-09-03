<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Make tarikh_lantikan1 and tarikh_tamat1 nullable so that
        // auto-creation via Filament Group relationship (program_id/aktiviti_id only)
        // does not fail when those dates are filled later via API or afterCreate.
        // Use raw SQL to avoid requiring doctrine/dbal for ->change().
        DB::statement('ALTER TABLE pegawai_kontraks MODIFY tarikh_lantikan1 DATE NULL');
        DB::statement('ALTER TABLE pegawai_kontraks MODIFY tarikh_tamat1 DATE NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE pegawai_kontraks MODIFY tarikh_lantikan1 DATE NOT NULL');
        DB::statement('ALTER TABLE pegawai_kontraks MODIFY tarikh_tamat1 DATE NOT NULL');
    }
};
