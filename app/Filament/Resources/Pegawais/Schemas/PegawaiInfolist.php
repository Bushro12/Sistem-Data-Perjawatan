<?php

namespace App\Filament\Resources\Pegawais\Schemas;

use App\Models\Pegawai;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class PegawaiInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->extraAttributes(fn (Pegawai $record) => [
                        'class' => static::lantikanSlug($record)
                            ? 'fi-modal-window-' . static::lantikanSlug($record)
                            : null,
                    ])
                    ->tabs([
                        Tab::make('Maklumat Pegawai')
                            ->icon('heroicon-o-identification')
                            ->schema([
                                ViewEntry::make('nama')
                                    ->view('filament.infolists.maklumat-pegawai-table')
                                    ->columnSpanFull(),
                            ]),



                        Tab::make('Jenis Lantikan')
                            ->icon('heroicon-o-document-check')
                            ->schema([
                                ViewEntry::make('jenis_lantikan')
                                    ->view('filament.infolists.jenis-lantikan-table')
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('Penempatan')
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                ViewEntry::make('penempatan')
                                    ->view('filament.infolists.penempatan-table')
                                    ->columnSpanFull(),
                            ]),

                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    protected static function lantikanSlug(Pegawai $record): ?string
    {
        return match (true) {
            $record->is_tetap == 1 => 'tetap',
            $record->is_kontrak_interim == 1 => 'kontrak-interim',
            $record->is_kontrak == 1 => 'kontrak',
            $record->is_kontrak_isi_tetap == 1 => 'kontrak-isi-tetap',
            default => null,
        };
    }
}

