<?php

namespace App\Console\Commands;


use App\Filament\Resources\Pencens\PencenResource;
use App\Models\Pegawai;
use App\Models\Pencen;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

// #[Signature('app:delete-pegawai-by-tarikh-kuatkuasa')]
#[Description('Command description')]
class DeletePegawaiTamatPerkhidmatan extends Command
{
    protected $signature = 'pegawai:delete-tamat-perkhidmatan';
    protected $description = 'Soft delete pegawai when tarikh_kuatkuasa or tarikh_pencen is reached';

    public function handle()
    {
        $today = Carbon::today()->toDateString();

        $records = Pencen::where(function ($query) use ($today) {
            $query->whereDate('tarikh_kuatkuasa', $today)
                ->orWhere(function ($query) use ($today) {
                    $query->whereNull('tarikh_kuatkuasa')
                        ->whereDate('tarikh_pencen', $today);
                });
        })->get();

        foreach ($records as $record) {

            $pegawai = Pegawai::where('nokp', $record->nokp)->first();

            if ($pegawai && !$pegawai->trashed()) {

                $pegawai->delete();


                $recipients = User::whereIn('role', [1, 2])->get();
                Notification::make()
                    ->title('Pegawai Tamat Perkhidmatan')
                    ->body("{$pegawai->nama} telah ditamatkan perkhidmatan secara automatik.")
                    ->danger()
                    ->actions([
                        Action::make('view')
                            ->label('Lihat Pegawai')
                            ->url(
                                PencenResource::getUrl('edit', [
                                    'record' => $record,
                                ])
                            )
                            ->markAsRead(),
                    ])
                    ->sendToDatabase($recipients);


                $this->info("Pegawai {$pegawai->nama} deleted.");
            }
        }

        return Command::SUCCESS;
    }
}
