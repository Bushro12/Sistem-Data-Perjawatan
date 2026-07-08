<?php

namespace App\Filament\Resources\Bahagians\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BahagianForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Maklumat Bahagian')
                    ->schema([
                        Select::make('ptj_id')
                            ->label('Ptj')
                            ->relationship('ptj', 'nama_ptj')
                            ->searchable()
                            ->preload()
                            ->required()
                            // ->unique()
                            ->columnSpanFull(),
                        TextInput::make('nama_bahagian')
                            ->label('Bahagian')
                            ->required()
                            ->columnSpanFull()
                            ->unique()
                            ->dehydrateStateUsing(fn(string $state): string => strtoupper(($state)))
                            ->extraInputAttributes((['style' => 'text-transform:uppercase'])),

                        Repeater::make('units')
                            ->label('Unit')
                            ->relationship()
                            ->addActionLabel('Tambah Unit')
                            ->addAction(function (Action $action) {
                                return $action
                                    ->color('info')
                                    ->icon('heroicon-m-plus');
                            })
                            ->schema([
                                TextInput::make('nama_unit')
                                    ->required()
                                    ->unique()
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
