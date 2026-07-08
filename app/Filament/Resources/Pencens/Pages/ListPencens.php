<?php

namespace App\Filament\Resources\Pencens\Pages;

use App\Filament\Resources\Pencens\PencenResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;

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

    public function getBreadcrumb(): string
    {
        return 'Senarai';
    }
}
