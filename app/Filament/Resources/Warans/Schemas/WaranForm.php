<?php

namespace App\Filament\Resources\Warans\Schemas;

use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WaranForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema

            ->components([
                Section::make('Maklumat Waran')
                    ->schema([
                        TextInput::make('no_waran')
                            ->label('No Waran')
                            ->unique(ignoreRecord: true)
                            ->reactive()
                            ->readonly(
                                fn() =>
                                ! auth()->user()?->isSuperadmin()
                                    && ! auth()->user()?->isAdmin()
                            ),

                        TextInput::make('jik')
                            ->label('Jumlah Jawatan')
                            ->required()
                            ->numeric()
                            ->readonly(
                                fn() =>
                                ! auth()->user()?->isSuperadmin()
                                    && ! auth()->user()?->isAdmin()
                            ),

                        Textarea::make('catatan')
                            ->label('Catatan')
                            ->columnSpanFull()
                            ->readonly(
                                fn() =>
                                ! auth()->user()?->isSuperadmin()
                                    && ! auth()->user()?->isAdmin()
                            ),
                        Radio::make('jenis')
                            ->label('Jenis Waran')
                            ->options([
                                'tambah' => 'Tambah Jawatan',
                                'tolak' => 'Tolak Jawatan',
                            ])
                            ->descriptions([
                                'tambah' => 'Menambah jawatan baru.',
                                'tolak' => 'Mengurangkan jawatan sedia ada.'
                            ])
                            ->disabled(
                                fn(string $operation) =>
                                $operation === 'edit'
                            )
                    ])
                    ->columns(2)
                    ->columnSpanFull()
            ]);
    }
}
