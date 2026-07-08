<?php

namespace App\Filament\Resources\LetakJawatans\Schemas;

use Carbon\Carbon;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class LetakJawatanInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->tabs([
                        Tab::make('Maklumat Pegawai')
                            ->schema([
                                TextEntry::make('nama')
                                    ->label('Nama Pegawai')
                                    ->columnSpanFull(),

                                TextEntry::make('nokp')
                                    ->label('No Kad Pengenalan'),

                                TextEntry::make('jawatan_gred')
                                    ->label('Jawatan & Gred')
                                    ->state(function ($record) {
                                        return $record->jawatan_gred?->jawatan?->desc_jawatan . ' (' .
                                            $record->jawatan_gred?->gred?->kod_gred . ')';
                                    }),

                                TextEntry::make('ptj.nama_ptj')
                                    ->label('Tempat Bertugas')
                                    ->columnSpanFull(),

                                TextEntry::make('lantikan')
                                    ->label('Jenis Lantikan'),

                                TextEntry::make('tarikh_lantik')
                                    ->label('Tarikh Lantikan')
                                    ->formatStateUsing(function ($record) {
                                        return Carbon::parse($record->tarikh_lantik)->format('d F Y');
                                    })

                            ]),
                        Tab::make('Maklumat Peletakkan Jawatan')
                            ->schema([
                                TextEntry::make('jenis_notis')
                                    ->label('Notis (24 Jam @ 30 Hari)'),

                                TextEntry::make('tarikh_notis')
                                    ->label('Tarikh Mula Notis')
                                    ->formatStateUsing(function ($record) {
                                        return Carbon::parse($record->tarikh_notis)->format('d F Y');
                                    }),

                                TextEntry::make('tarikh_kuatkuasa')
                                    ->label('Tarikh Kuatkuasa')
                                    ->columnSpanFull()
                                    ->formatStateUsing(function ($record) {
                                        return Carbon::parse($record->tarikh_kuatkuasa)->format('d F Y');
                                    }),

                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('ikatan_jpa')
                                        ->label('Ikatan JPA')
                                        ->state(function ($record) {
                                            if ($record->ikatan_jpa == 1) {
                                                return 'Ada';
                                            } else {
                                                return 'Tiada';
                                            }
                                        })
                                        ->badge()
                                        ->size('large'),
                                        TextEntry::make('ikatan_bpl')
                                        ->label('Ikatan BPL')
                                        ->state(function ($record) {
                                            if ($record->ikatan_bpl == 1) {
                                                return 'Ada';
                                            } else {
                                                return 'Tiada';
                                            }
                                        })
                                        ->badge()
                                        ->size('large'),
                                        TextEntry::make('pinjaman_lppsa')
                                        ->label('Pinjaman LPPSA (Perumahan)')
                                        ->state(function ($record) {
                                            if ($record->pinjaman_lppsa == 1) {
                                                return 'Ada';
                                            } else {
                                                return 'Tiada';
                                            }
                                        })
                                        ->badge()
                                        ->size('large'),
                                    ])
                                    ->columnSpanFull(),

                                TextEntry::make('alasan')
                                    ->label('Alasan')
                                    ->columnSpanFull()

                            ])
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

            ]);
    }
}
