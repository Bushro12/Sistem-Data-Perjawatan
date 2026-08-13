<?php

namespace App\Filament\Resources\Units\Schemas;

use App\Models\Dun;
use App\Models\Ptj;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class UnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Maklumat Unit')
                    ->schema([
                        Select::make('ptj_id')
                            ->label('PTJ')
                            ->required()
                            ->options(
                                Ptj::query()
                                    ->orderBy('nama_ptj')
                                    ->pluck('nama_ptj', 'id')
                            )
                            ->live()
                            ->searchable()
                            ->dehydrated(false)
                            ->visible(fn($record) => $record === null)
                            ->columnSpanFull(),

                        TextInput::make('ptj_id')
                            ->label('PTJ')
                            ->afterStateHydrated(function ($component, $state, $record) {
                                $component->state(
                                    $record?->bahagian?->ptj?->nama_ptj
                                );
                            })
                            ->readOnly()
                            ->visible(fn($record) => $record !== null)
                            ->columnSpanFull(),

                        Select::make('bahagian_id')
                            ->label('Bahagian')
                            ->options(function (Get $get) {
                                return \App\Models\Bahagian::query()
                                    ->where('ptj_id', $get('ptj_id'))
                                    ->orderBy('nama_bahagian')
                                    ->pluck('nama_bahagian', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->columnSpanFull()
                            ->visible(fn($record) => $record === null),

                        TextInput::make('bahagian_id')
                            ->label('Bahagian')
                            ->afterStateHydrated(function ($component, $state, $record) {
                                $component->state(
                                    $record?->bahagian?->nama_bahagian
                                );
                            })
                            ->readOnly()
                            ->visible(fn($record) => $record !== null)
                            ->dehydrated(false)
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
                                if (blank($parlimenId))
                                    return [];
                                return Dun::where('parlimen_id', $parlimenId)
                                    ->pluck('nama_dun', 'id')
                                    ->toArray();
                            })
                            ->disabled(fn(Get $get) => blank($get('parlimen_id')))
                            ->helperText('Sila pilih Parlimen dahulu'),
                    ])
                    ->columns(2)
                    ->columnSpanFull()

            ]);
    }
}
