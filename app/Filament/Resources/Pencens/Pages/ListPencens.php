<?php

namespace App\Filament\Resources\Pencens\Pages;

use App\Filament\Resources\Pencens\PencenResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;



class ListPencens extends ListRecords
{
    protected static string $resource = PencenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Penamatan Perkhidmatan'),
            Action::make('export')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('export')
                ->modalHeading('Export Laporan Penamatan Perkhidmatan')
                ->modalSubmitActionLabel('Export')
                ->modalCancelActionLabel('Batal')
                ->form([
                    Select::make('jenis_pencen_id')
                        ->label('Jenis Penamapatan Perkhidmatan')
                        // ->relationship('jenisPencen', 'jenis')
                        ->multiple()
                        ->options(\App\Models\JenisPencen::pluck('jenis', 'id'))
                ])
                ->action(function (array $data) {
                    return redirect()->route('export.penamatanPerkhidmatan', [
                        'jenis_pencen_id' => $data['jenis_pencen_id']
                    ]);
                })

        ];
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



    public function getBreadcrumb(): string
    {
        return 'Senarai';
    }
}
