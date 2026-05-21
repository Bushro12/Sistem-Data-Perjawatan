<?php

namespace App\Filament\Resources\Jawatans\Schemas;

use App\Models\Kumpulan;
// use Filament\Actions\Action;
// use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Schema;

class JawatanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                TextInput::make('kod_jawatan')
                    ->label('Kod Jawatan')
                    ->required()
                    ->columnSpan(1)
                    ->dehydrateStateUsing(fn(string $state): string => strtoupper($state))
                    ->extraInputAttributes(['style' => 'text-transform:uppercase']),
                TextInput::make('desc_jawatan')
                    ->label('Jawatan')
                    ->required()
                    ->columnSpan(2)
                    ->dehydrateStateUsing(fn(string $state): string => strtoupper($state))
                    ->extraInputAttributes(['style' => 'text-transform:uppercase'])
                    ->unique(),
                Select::make('gred_id')
                    ->label('Gred')
                    ->multiple()
                    ->relationship('greds', 'kod_gred')
                    ->preload()
                    ->saveRelationshipsUsing(function ($component, $state, $record, $get) {

                        $syncData = [];

                        foreach ($state as $gredId) {
                            $syncData[$gredId] = [
                                'kumpulan_id' => $get('kumpulan_id'),
                            ];
                        }

                        $record->greds()->sync($syncData);
                    })
                    ->columnSpanFull(),

                ToggleButtons::make('kumpulan_id')
                    ->label('Kumpulan')
                    ->options(
                        Kumpulan::pluck('nama_kumpulan', 'id')->toArray()
                    )
                    ->colors([
                        1 => 'danger',
                        2 => 'info',
                        3 => 'tertiary',
                        4 => 'primary',
                        5 => 'secondary'
                    ])
                    ->inline()
                    ->afterStateHydrated(function ($component, $record) {

                        if (!$record) {
                            return;
                        }

                        $pivot = $record->greds->first()?->pivot;

                        if ($pivot) {
                            $component->state($pivot->kumpulan_id);
                        }
                    })
                    ->columnSpanFull(),
            ]);
    }
}
