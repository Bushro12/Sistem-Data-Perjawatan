<?php

namespace App\Filament\Pages\Auth;

use Filament\Actions\Action;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Stack;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Concerns\HasMaxWidth;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\Enums\Width;

class EditProfile extends BaseEditProfile
{

    public function getMaxContentWidth(): Width
    {
        return Width::FiveExtraLarge;
    }

    protected function getSaveFormAction(): Action
    {
        return Action::make('save')
            ->label('Simpan')
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading('Pengesahan')
            ->modalDescription('Adakah anda pasti mahu simpan perubahan ini?')
            ->action(fn() => $this->save());
    }


    protected function getCancelFormAction(): Action
    {
       return Action::make('cancel')
        ->label('Kembali ke Laman Utama')
        ->url('/app')
        ->color(false);
    }
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                FileUpload::make('avatar')
                                    ->label('Gambar Profil')
                                    ->image()
                                    ->avatar()
                                    ->disk('public')
                                    ->directory('avatars')
                                    ->imageEditor()
                                    ->circleCropper()
                                    ->maxSize(2048)
                                    ->extraAttributes([
                                        'class' => 'flex justify-center items-center',
                                    ]),
                            ])
                            ->columnSpan(1),
                        Grid::make(1)
                            ->schema([
                                $this->getNameFormComponent()->readOnly()->label('Nama'),

                                TextInput::make('ptj')
                                    ->label('PTJ')
                                    ->readOnly()
                                    ->dehydrated(false)
                                    ->afterStateHydrated(function (TextInput $component, $record) {
                                        $component->state($record->ptj?->nama_ptj);
                                    }),
                            ])
                            ->columnSpan(1),
                        TextInput::make('nokp')
                            ->label('No Kad Pengenalan')
                            ->readOnly()
                            ->dehydrated(false),


                        $this->getEmailFormComponent()->label('Email'),

                        TextInput::make('phone_number')
                            ->label('No Telefon')
                            ->readOnly()
                            ->dehydrated(false),

                        TextInput::make('role')
                            ->label('Peranan')
                            ->readOnly()
                            ->dehydrated(false)
                            ->formatStateUsing(fn($state) => match ($state) {
                                1 => 'Superadmin',
                                2 => 'Admin',
                                3 => 'PTJ',
                                default => 'Pelawat',
                            }),

                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                        $this->getCurrentPasswordFormComponent(),

                    ])
            ]);
    }
}
