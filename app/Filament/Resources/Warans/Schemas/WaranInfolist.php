<?php

namespace App\Filament\Resources\Warans\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WaranInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Maklumat Waran')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('no_waran')
                            ->label('No Waran'),
                        TextEntry::make('jik')
                            ->label('Bilangan Jawatan'),
                        TextEntry::make('jenis')
                            ->label('Jenis Waran')
                            ->badge()
                            ->size('lg'),
                        TextEntry::make('catatan')
                            ->columnSpanFull()
                            ->visible(fn ($record) => filled($record->catatan)),
                    ]),

                // Section::make('Senarai Penempatan')
                //     ->columnSpanFull()
                //     ->schema([
                //         RepeatableEntry::make('waranJawatan')
                //             ->schema([
                //                 TextEntry::make('butiran')
                //                     ->label('Butiran'),

                //                 TextEntry::make('tarikh_kuatkuasa')
                //                     ->label('Tarikh Kuatkuasa Waran')
                //                     ->date('d F Y')
                //                     ->placeholder('Tiada'),

                //                 TextEntry::make('ptj.nama_ptj')
                //                     ->label('PTJ'),

                //                 TextEntry::make('pegawai.nama')
                //                     ->label('Pegawai')
                //                     ->placeholder('Tiada Penyandang'),
                //             ])
                //             ->columns(2)
                //     ])

            ]);

    }
}
