<?php

namespace App\Filament\Resources\Units\Schemas;

use App\Models\Dun;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class UnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('bahagian_id')
                    ->label('Bahagian')
                    ->relationship('bahagian', 'nama_bahagian')
                    ->searchable()
                    ->required()
                    ->preload()
                    ->columnSpanFull(),
                TextInput::make('nama_unit')
                    ->label('Unit')
                    ->required()
                    ->dehydrateStateUsing(fn(string $state): string => strtoupper($state))
                    ->extraInputAttributes(['style' => 'text-transform:uppercase'])
                    ->columnSpanFull(),

                Select::make('parlimen_id')
                    ->label('Parlimen')
                    ->relationship('parlimen', 'nama_parlimen')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(fn(Set $set) => $set('dun_id', null)),

                Select::make('dun_id')
                    ->label('DUN')
                    ->searchable()
                    ->options(function (Get $get): array {
                        $parlimenId = $get('parlimen_id');
                        if (blank($parlimenId)) return [];
                        return Dun::where('parlimen_id', $parlimenId)
                            ->pluck('nama_dun', 'id')
                            ->toArray();
                    })
                    ->disabled(fn(Get $get) => blank($get('parlimen_id')))
                    ->helperText('Sila pilih Parlimen dahulu'),
            ]);
    }
}
