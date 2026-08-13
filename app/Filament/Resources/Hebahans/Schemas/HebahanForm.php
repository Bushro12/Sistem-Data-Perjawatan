<?php

namespace App\Filament\Resources\Hebahans\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class HebahanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->columnSpanFull()
                    ->components([
                        TextInput::make('tajuk')
                            ->label('Tajuk')
                            ->required()
                            ->columnSpanFull(),

                        RichEditor::make('kandungan')
                            ->label('Kandungan')
                            ->columnSpanFull(),

                        FileUpload::make('lampiran')
                            ->label('Lampiran')
                            ->disk('public')
                            ->directory('hebahan')
                            ->acceptedFileTypes(['application/pdf'])
                            ->openable()
                            ->downloadable(),

                        DatePicker::make('tarikh_hebahan')
                            ->label('Tarikh Hebahan')
                            ->required()
                            ->default(now()),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draf',
                                'published' => 'Diterbitkan',
                            ])
                            ->default('draft')
                            ->required(),

                        DatePicker::make('dipaparkan_sehingga')
                            ->label('Dipaparkan Sehingga')
                            ->helperText('Biarkan kosong jika hebahan ini tiada tarikh luput.'),
                    ]),
            ]);
    }
}
