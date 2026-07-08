<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Ptj;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Illuminate\Support\Str;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Maklumat Pengguna')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->dehydrateStateUsing(fn(string $state): string => strtoupper($state))
                            ->extraInputAttributes(['style' => 'text-transform:uppercase'])
                            ->columnSpanFull(),

                        Select::make('ptj_id')
                            ->label('PTJ')
                            ->relationship('ptj', 'nama_ptj')
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),

                        TextInput::make('email')
                            ->label('Email')
                            ->required()
                            ->email(),

                        TextInput::make('nokp')
                            ->label('No Kad Pengenalan')
                            // ->numeric()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->rule('digits:12')
                            ->live(onBlur: true)
                            ->validationMessages([
                                'unique' => 'No kad pengenalan ini sudah wujud.',
                                'digits' => 'Sila masukkan No kad pengenalan yang sah.'
                            ]),

                        TextInput::make('phone_number')
                            ->label('No Telefon')
                            // ->numeric()
                            ->required(),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                '1' => 'Aktif',
                                '0' => 'Tidak Aktif',
                            ])
                            ->default('1')
                            ->searchable(),

                        Select::make('role')
                            ->label('Peranan')
                            ->searchable()
                            ->options([
                                '1' => 'Super Admin',
                                '2' => 'Admin',
                                '3' => 'User',
                            ])
                            ->default('3'),

                        TextInput::make('password')
                            ->label('Kata Laluan')
                            ->password()
                            ->revealable()
                            ->suffixAction(
                                Action::make('generate')
                                    ->icon('heroicon-o-arrow-path')
                                    ->action(fn($set) => $set('password', Str::random(10)))
                                    ->tooltip("Generate Password")
                            )
                            ->hiddenOn('view')
                            ->required(fn(string $context) => $context === 'create')
                            ->visible(
                                fn(string $context) =>
                                $context === 'create' || auth()->user()->role === 1
                            )
                    ])
                    ->columns(2)
                    ->columnSpanFull()

            ]);
    }
}
