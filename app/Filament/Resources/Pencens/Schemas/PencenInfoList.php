<?php

namespace App\Filament\Resources\Pencens\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class PencenInfoList
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
                                TextEntry::make('ptj.nama_ptj')
                                    ->label('PTJ')
                                    ->columnSpanFull(),
                                TextEntry::make('jawatan_gred.jawatan.desc_jawatan')
                                    ->label('Jawatan'),
                                TextEntry::make('jawatan_gred.gred.kod_gred')
                                    ->label('Gred'),
                            ]),
                        Tab::make('Maklumat Persaran')
                            ->schema([
                                TextEntry::make('tarikh_lantikan')
                                    ->label('Tarikh Lantikan')
                                    ->date('d F Y'),
                                TextEntry::make('tarikh_sah_jawatan')
                                    ->label('Tarikh Sah Jawatan')
                                    ->date('d F Y'),
                                TextEntry::make('jenisPencen.jenis')
                                    ->label('Jenis Penamatan Perkhidmatan')
                                    ->columnSpanFull(),
                                TextEntry::make('opsyenPencen.opsyen')
                                    ->label('Opsyen (Umur Bersara)')
                                    ->visible(fn($record) => $record?->jenis_lantikan !== 'Kontrak'),
                                TextEntry::make('tarikh_pencen')
                                    ->label('Tarikh Bersara')
                                    ->date('d F Y')
                                    ->visible(fn($record) => $record?->jenis_lantikan !== 'Kontrak'),
                                TextEntry::make('tempoh_perkhidmatan')
                                    ->label('Tempoh Perkhidmatan'),
                                TextEntry::make('tarikh_kuatkuasa')
                                    ->label('Tarikh Kuatkuasa')
                                    ->date('d F Y'),
                                TextEntry::make('catatan')
                                    ->label('Catatan')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->columns(2),
            ]);
    }
}
