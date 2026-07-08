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
                                            'pindaan nama' => 'info',
                                            'batal nama' => 'primary',
                                            default => 'success',
                                        }
                                    )

                            ]),
                        Tab::make('Maklumat Penyandang')
                            ->schema([
                                TextEntry::make('pegawai.nama')
                                    ->label('Nama Pegawai')
                                    ->state(function ($record) {
                                        if ($record->pegawai_id == null) {
                                            return 'Tiada Penyandang';
                                        } else {
                                            return $record->pegawai?->nama;
                                        }
                                    })
                                    ->columnSpanFull(),
                                TextEntry::make('pegawai.nokp')
                                    ->label('No Kad Pengenalan')
                                    ->state(function ($record) {
                                        if ($record->pegawai_id == null) {
                                            return 'Tiada';
                                        } else {
                                            return $record->pegawai?->nokp;
                                        }
                                    }),
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
                                    ->label('Bahagian')
                                    ->state(function ($record) {
                                        if ($record->bahagian_id == null) {
                                            return 'Tiada';
                                        } else {
                                            return $record->bahagian?->nama_bahagian;
                                        }
                                    }),
                                TextEntry::make('unit.nama_unit')
                                    ->label('Unit')
                                    ->state(function ($record) {
                                        if ($record->unit_id == null) {
                                            return 'Tiada';
                                        } else {
                                            return $record->unit?->nama_unit;
                                        }
                                    }),
                                TextEntry::make('subunit.nama_subunit')
                                    ->label('Subunit')
                                    ->state(function ($record) {
                                        if ($record->subunit_id == null) {
                                            return 'Tiada';
                                        } else {
                                            return $record->subunit?->nama_subunit;
                                        }
                                    }),

                            ]),

                    ])
                    ->columns(2)
                    ->columnSpanFull()


            ]);
    }
}
