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
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;

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

    public function getBreadcrumbs(): array
    {
        // The back button (see getHeading()) replaces the need for a
        // breadcrumb trail on this page.
        return [];
    }

    public function getHeading(): string | Htmlable
    {
        return new HtmlString(
            '<a href="' . e(\App\Filament\Pages\Dashboard::getUrl()) . '" class="mystaff-back-btn" aria-label="Kembali ke Dashboard">' .
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>' .
            '</a>' .
            '<span>' . e($this->getTitle()) . '</span>'
        );
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('ALL'),
            // ->badge(Pegawai::count()),

            'tetap' => Tab::make('TETAP')
                ->extraAttributes(['class' => 'fi-tabs-item-tetap'])
                ->modifyQueryUsing(
                    fn(Builder $query) => $query->where('is_tetap', 1)
                ),
            // ->badge(Pegawai::where('is_tetap', 1)->count()),

            'kontrak_interim' => Tab::make('KONTRAK INTERIM')
                ->extraAttributes(['class' => 'fi-tabs-item-kontrak-interim'])
                ->modifyQueryUsing(
                    fn(Builder $query) => $query->where('is_kontrak_interim', 1)
                ),
            // ->badge(Pegawai::where('is_kontrak_interim', 1)->count()),

            'kontrak' => Tab::make('KONTRAK')
                ->extraAttributes(['class' => 'fi-tabs-item-kontrak'])
                ->modifyQueryUsing(
                    fn(Builder $query) => $query->where('is_kontrak', 1)
                ),
            // ->badge(Pegawai::where('is_kontrak', 1)->count()),


            'kontrak_isi_tetap' => Tab::make('KONTRAK ISI TETAP')
            ->extraAttributes(['class' => 'fi-tabs-item-kontrak'])
            ->modifyQueryUsing(
                fn(Builder $query) => $query->where('is_kontrak_isi_tetap', 1)
            )
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
