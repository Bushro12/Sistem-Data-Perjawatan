<?php

namespace App\Filament\Resources\Greds\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class GredForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
        ->columns(3)
            ->components([
                TextInput::make('kod_gred')
                ->label('Kod')
                ->required()
                ->columnSpan(1),
                TextInput::make('desc_gred')
                ->label('Kod')
                ->required()
                ->columnSpan(2),
            ]);
    }
}
