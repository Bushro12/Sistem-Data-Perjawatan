<?php

namespace App\Console\Commands;

use App\Models\LetakJawatan;
use App\Models\Pegawai;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

// #[Signature('app:delete-pegawai-by-tarikh-kuatkuasa')]
#[Description('Command description')]
class DeletePegawaiByTarikhKuatkuasa extends Command
{
protected $signature = 'pegawai:delete-letak-jawatan';
    protected $description = 'Soft delete pegawai when tarikh_kuatkuasa is reached';

    public function handle()
{
    $today = Carbon::today()->toDateString();

    $records = LetakJawatan::whereDate('tarikh_kuatkuasa', $today)
        ->get();

    foreach ($records as $record) {

        $pegawai = Pegawai::where('nokp', $record->nokp)->first();

        if ($pegawai && ! $pegawai->trashed()) {

            $pegawai->delete();

            $this->info("Pegawai {$pegawai->nama} deleted.");
        }
    }

    return Command::SUCCESS;
}
}
