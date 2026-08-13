<?php

namespace App\Filament\Resources\Subunits\Schemas;

use App\Models\Bahagian;
use App\Models\Dun;
use App\Models\Ptj;
use App\Models\Unit;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;


class SubunitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Maklumat Sub Unit')
                    ->schema([

                        Select::make('ptj_id')
                            ->label('PTJ')
                            ->required()
                            ->options(
                                Ptj::query()
                                    ->orderBy('nama_ptj')
                                    ->pluck('nama_ptj', 'id')
                            )
                            ->afterStateHydrated(function ($component, $state, $record) {
                                $component->state(
                                    $record?->unit?->bahagian?->ptj_id
                                );
                            })
                            ->live()
                            ->searchable()
                            ->dehydrated(false)
                            ->visible(fn($record) => $record === null)
                            ->columnSpanFull(),

                        TextInput::make('ptj_id')
                            ->label('PTJ')
                            ->afterStateHydrated(function ($component, $state, $record) {
                                $component->state(
                                    $record?->unit?->bahagian?->ptj?->nama_ptj
                                );
                            })
                            ->readOnly()
                            ->visible(fn($record) => $record !== null)
                            ->columnSpanFull(),

                        Select::make('bahagian_id')
                            ->label('Bahagian')
                            ->required()
                            ->options(function (Get $get) {
                                $ptjId = $get('ptj_id');

                                return $ptjId
                                    ? Bahagian::where('ptj_id', $ptjId)
                                        ->orderBy('nama_bahagian')
                                        ->pluck('nama_bahagian', 'id')
                                    : [];
                            })
                            ->afterStateHydrated(function ($component, $state, $record) {
                                $component->state(
                                    $record?->unit?->bahagian_id
                                );
                            })
                            ->live()
                            ->searchable()
                            ->visible(fn($record) => $record === null)
                            ->dehydrated(false),

                        TextInput::make('bahagian_id')
                            ->label('Bahagian')
                            ->afterStateHydrated(function ($component, $state, $record) {
                                $component->state(
                                    $record?->unit?->bahagian?->nama_bahagian
                                );
                            })
                            ->readOnly()
                            ->visible(fn($record) => $record !== null),

                        Select::make('unit_id')
                            ->label('Unit')
                            ->required()
                            ->options(function (Get $get) {
                                $bahagianId = $get('bahagian_id');

                                return $bahagianId
                                    ? Unit::where('bahagian_id', $bahagianId)
                                        ->orderBy('nama_unit')
                                        ->pluck('nama_unit', 'id')
                                    : [];
                            })
                            ->default(fn($record) => $record?->unit_id)
                            ->visible(fn($record) => $record === null)

                            ->searchable(),

                        TextInput::make('unit_id')
                            ->label('Unit')
                            ->afterStateHydrated(function ($component, $state, $record) {
                                $component->state(
                                    $record?->unit?->nama_unit
                                );
                            })
                            ->readOnly()
                            ->visible(fn($record) => $record !== null)
                            ->dehydrated(false),

                        TextInput::make('nama_subunit')
                            ->label('Sub Unit')
                            ->dehydrateStateUsing(fn(string $state): string => strtoupper($state))
                            ->extraInputAttributes(['style' => 'text-transform:uppercase'])
                            ->columnSpanFull(),

                        Select::make('parlimen_id')
                            ->label('Parlimen')
                            ->required()
                            ->relationship('parlimen', 'nama_parlimen')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn(Set $set) => $set('dun_id', null)),

                        Select::make('dun_id')
                            ->label('DUN')
                            ->required()
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
