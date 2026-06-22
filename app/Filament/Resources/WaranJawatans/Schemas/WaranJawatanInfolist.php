<?php

namespace App\Filament\Resources\WaranJawatans\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class WaranJawatanInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Section::make('Maklumat Waran')
                //     ->schema([
                //         TextEntry::make('waran.no_waran')
                //             ->hiddenLabel()
                //             ->formatStateUsing(fn($state) => "No Waran: {$state}"),
                //         TextEntry::make('butiran')
                //             ->hiddenLabel()
                //             ->formatStateUsing(fn($state) => "Butiran: {$state}"),
                //             TextEntry::make('aktiviti')
                //             ->hiddenLabel()
                //             ->formatStateUsing(fn($record) =>
                //             'Aktiviti: ' . ($record->aktiviti?->no_aktivit) . ' - ' . ($record->aktiviti->nama_aktiviti))
                //     ])
                //     ->columns(2),

                // Section::make('Maklumat Penyandang')
                //     ->schema([
                //         TextEntry::make('butiran')
                //             ->hiddenLabel()
                //             ->formatStateUsing(fn($state) => "Butiran: {$state}"),
                //     ])
                Tabs::make('Tabs')
                    ->tabs([
                        Tab::make('Maklumat Waran')
                            ->schema([
                                TextEntry::make('waran.no_waran')
                                    ->label('No Waran'),
                                TextEntry::make('butiran')
                                    ->label('Butiran'),
                                TextEntry::make('aktiviti')
                                    ->label('Aktiviti')
                                    ->formatStateUsing(
                                        fn($record) =>
                                        ($record->aktiviti?->no_aktivit) . ' - ' . ($record->aktiviti->nama_aktiviti)
                                    ),
                                TextEntry::make('jawatan_gred_display')
                                    ->label('Jawatan / Gred')
                                    ->state(function ($record) {
                                        return $record->jawatan_list . ' , GRED ' . $record->gred_list;
                                    })
                                    ->html()
                                    ->wrap(),
                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->size('lg')
                                    ->formatStateUsing(fn($state) => match ($state) {
                                        'removed' => 'Dibuang',
                                        'pindaan nama' => 'Pindaan Nama',
                                        'batal nama' => 'Batal Nama',
                                        default => 'Aktif',
                                    })
                                    ->color(
                                        fn($state) => match ($state) {
                                            'removed' => 'danger',
                                            'pindaan nama' => 'neutral',
                                            'batal nama' => 'quartenary',
                                            default => 'success',
                                        }
                                    )

                            ]),
                        Tab::make('Maklumat Penyandang')
                            ->schema([
                                TextEntry::make('pegawai.nama')
                                    ->label('Nama Pegawai')
                                    ->columnSpanFull(),
                                TextEntry::make('pegawai.nokp')
                                ->label('No Kad Pengenalan'),
                                TextEntry::make('pegawai')
                                    ->label('Jawatan / Gred')
                                    ->formatStateUsing(
                                        fn($record) =>
                                        $record->pegawai?->jawatan_gred?->jawatan?->desc_jawatan . ', ' .
                                        $record->pegawai?->jawatan_gred?->gred?->kod_gred
                                    )
                                    ->wrap(),
                                TextEntry::make('ptj.nama_ptj')
                                    ->label('PTJ'),
                                TextEntry::make('bahagian.nama_bahagian')
                                    ->label('Bahagian'),
                                TextEntry::make('unit.nama_unit')
                                    ->label('Unit'),
                                TextEntry::make('subunit.nama_subunit')
                                    ->label('Subunit'),

                            ]),

                    ])
                    ->columns(2)
                    ->columnSpanFull()


            ]);
    }
}
