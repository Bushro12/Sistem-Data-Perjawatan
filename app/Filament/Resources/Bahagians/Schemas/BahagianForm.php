<?php

namespace App\Filament\Resources\Bahagians\Schemas;

use App\Models\Dun;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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
                            ->dehydrateStateUsing(fn(string $state): string => strtoupper(($state)))
                            ->extraInputAttributes((['style' => 'text-transform:uppercase'])),

                        // Repeater::make('units')

                        //     ->label('Unit')
                        //     ->relationship()
                        //     ->addActionLabel('Tambah Unit')
                        //     ->addAction(function (Action $action) {
                        //         return $action
                        //             ->color('info')
                        //             ->icon('heroicon-m-plus');
                        //     })
                        //     ->simple(
                        //         TextInput::make('nama_unit')
                        //             ->required()
                        //             ->dehydrateStateUsing(fn($state) => $state ? strtoupper($state) : null)
                        //             ->extraInputAttributes(['style' => 'text-transform:uppercase']),
                        //     )
                        //     ->columnSpanFull(),

                        // Select::make('parlimen_id')
                        //     ->label('Parlimen')
                        //     ->relationship('parlimen', 'nama_parlimen')
                        //     ->searchable()
                        //     ->preload()
                        //     ->live()
                        //     ->afterStateUpdated(fn(Set $set) => $set('dun_id', null)),

                        // Select::make('dun_id')
                        //     ->label('DUN')
                        //     ->searchable()
                        //     ->options(function (Get $get): array {
                        //         $parlimenId = $get('parlimen_id');
                        //         if (blank($parlimenId))
                        //             return [];
                        //         return Dun::where('parlimen_id', $parlimenId)
                        //             ->pluck('nama_dun', 'id')
                        //             ->toArray();
                        //     })
                        //     ->disabled(fn(Get $get) => blank($get('parlimen_id')))
                        //     ->helperText('Sila pilih Parlimen dahulu'),


                    ])
                    ->columns(2)
                    ->columnSpanFull()

            ]);
    }
}
