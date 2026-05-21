<?php

namespace App\Filament\Resources\Kumpulans\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class KumpulanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_kumpulan')
                ->label('Nama Kumpulan')
                ->dehydrateStateUsing(fn(string $state): string => strtoupper($state))
                ->extraInputAttributes(['style' => 'text-transform:uppercase'])
                ->columnSpanFull()
            ]);
    }
}
