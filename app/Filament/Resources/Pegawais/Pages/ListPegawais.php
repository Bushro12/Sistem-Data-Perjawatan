<?php

namespace App\Filament\Resources\Pegawais\Pages;

use App\Filament\Resources\Pegawais\PegawaiResource;
use App\Models\Pegawai;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class ListPegawais extends ListRecords
{
    protected static string $resource = PegawaiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
            ->label('Tambah Pegawai'),
        ];
    }

    public function getBreadcrumb(): string
    {
        return 'Senarai';
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('ALL'),
            // ->badge(Pegawai::count()),

            'tetap' => Tab::make('TETAP')
                ->modifyQueryUsing(
                    fn(Builder $query) => $query->where('is_tetap', 1)
                ),
            // ->badge(Pegawai::where('is_tetap', 1)->count()),

            'kontrak_interim' => Tab::make('KONTRAK INTERIM')
                ->modifyQueryUsing(
                    fn(Builder $query) => $query->where('is_kontrak_interim', 1)
                ),
            // ->badge(Pegawai::where('is_kontrak_interim', 1)->count()),

            'kontrak' => Tab::make('KONTRAK')
                ->modifyQueryUsing(
                    fn(Builder $query) => $query->where('is_kontrak', 1)
                ),
            // ->badge(Pegawai::where('is_kontrak', 1)->count()),


        ];
    }

    // protected function afterDelete(): void
    // {
    //     Log::info('Pegawai Deleted', [
    //     'pegawai_id' => $this->record->id,
    //     'user_id' => auth()->id(),
    //     ]);

    //      $creator = auth()->user();
    //     $pegawai = $this->record;

    //     $recipients = User::whereIn('role', [1, 2])->get();

    //     Notification::make()
    //         ->title('Pegawai Telah Dipadam')
    //         ->body("Pegawai telah dipadam oleh {$creator->name}")
    //         ->danger()
    //         ->actions([
    //             Action::make('view')
    //                 ->label('Lihat Pegawai')
    //                 ->url(
    //                     PegawaiResource::getUrl('view', [
    //                         'record' => $pegawai,
    //                     ])
    //                 )
    //                 ->markAsRead(),
    //         ])
    //         ->sendToDatabase($recipients);
    // }



}
