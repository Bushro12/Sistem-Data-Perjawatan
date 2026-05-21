<?php

namespace App\Filament\Resources\Programs\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProgramForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Program')
                    ->schema([
                        TextInput::make('nama_program')
                            ->label('No Program')
                            ->required()
                            ->dehydrateStateUsing(fn(string $state): string => strtoupper($state))
                            ->extraInputAttributes(['style' => 'text-transform:uppercase'])
                            ->columnSpan(1)
                            ->unique(),
                             TextInput::make('desc_program')
                            ->label('Nama Program')
                            // ->required()
                            ->dehydrateStateUsing(fn(string $state): string => strtoupper($state))
                            ->extraInputAttributes(['style' => 'text-transform:uppercase'])
                            ->columnSpan(1)
                            ->unique(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('Aktiviti')
                    ->schema([
                        Repeater::make('aktiviti')
                            ->label('Aktiviti')
                            ->relationship('aktiviti')
                            ->columnSpanFull()
                            ->columns(2)
                            ->addActionLabel('Tambah Aktiviti')
                            ->addAction(function (Action $action) {
                                return $action
                                    ->color('info')
                                    ->icon('heroicon-m-plus');
                            })
                            ->schema([
                                TextInput::make('no_aktivit')
                                    ->label('No Aktiviti')
                                    ->required()
                                    ->dehydrateStateUsing(fn($state) => $state ? strtoupper($state) : null)
                                    ->extraInputAttributes(['style' => 'text-transform:uppercase']),
                                TextInput::make('nama_aktiviti')
                                    ->label('Nama Aktiviti')
                                    ->dehydrateStateUsing(fn($state) => $state ? strtoupper($state) : null)
                                    ->extraInputAttributes(['style' => 'text-transform:uppercase']),
                            ])
                            ->itemLabel(
                                fn(array $state): ?string =>
                                filled($state['no_aktivit'] ?? null) || filled($state['nama_aktiviti'] ?? null)
                                ? ($state['no_aktivit'] ?? '') . ' - ' . ($state['nama_aktiviti'] ?? '')
                                : 'Tambah Aktiviti'
                            )->collapsed()
                    ])
                    ->columnSpanFull()
            ]);



    }
}
