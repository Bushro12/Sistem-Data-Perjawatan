<?php

namespace App\Filament\Resources\JenisPencens\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class JenisPencenForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('jenis')
                    ->label('Jenis Pencen')
                    ->required()
                    ->columnSpanFull(),

                Radio::make('kategori')
                    ->options([
                        'Paksa' => 'Persaraan Paksa',
                        'Pilihan' => 'Persaraan Pilihan',
                    ])
                    ->required(),
            ]);
    }
}
