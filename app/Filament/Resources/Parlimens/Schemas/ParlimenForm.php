<?php

namespace App\Filament\Resources\Parlimens\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ParlimenForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Maklumat Parlimen')
                    ->schema([
                        TextInput::make('nama_parlimen')
                            ->label('Parlimen')
                            ->required()
                            ->dehydrateStateUsing(fn(string $state): string => strtoupper($state))
                            ->extraInputAttributes(['style' => 'text-transform:uppercase'])
                            ->columnSpanFull()
                            ->unique(),
                        Repeater::make('duns')
                            ->label('Dun')
                            ->relationship('duns')
                            ->addActionLabel('Tambah Dun')
                            ->addAction(function (Action $action) {
                                return $action
                                    ->color('info')
                                    ->icon('heroicon-m-plus');
                            })
                            ->schema([
                                TextInput::make('nama_dun')
                                    ->required()
                                    ->dehydrateStateUsing(fn($state) => $state ? strtoupper($state) : null)
                                    ->extraInputAttributes(['style' => 'text-transform:uppercase']),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull()

            ]);
    }
}
