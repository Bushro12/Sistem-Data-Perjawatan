<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no')
                    ->label('Bil')
                    ->rowIndex(),
                // TextColumn::make('name')
                //     ->label('Nama / IC / No Tel.')
                //     ->formatStateUsing(
                //         fn($record) =>
                //         '<strong>' . $record->name . '</strong><br>' .
                //         $record->email . '<br>' .
                //         $record->phone_number
                //     )
                //     ->html()
                //     ->searchable()
                //     ->sortable(),
                TextColumn::make('name')
                    ->label('Nama')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('ptj.nama_ptj')
                    ->label('Ptj')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    // ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make()
                        ->label('Padam')
                        ->modalHeading(fn($record) => "Padam {$record->name}")
                        ->modalDescription('Adakah anda pasti mahu memadam rekod ini? Tindakan ini tidak boleh dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Padam')
                        ->modalCancelActionLabel('Batal')
                        ->after(function ($record) {
                            $actor = auth()->user();

                            Notification::make()
                                ->title('User Deleted')
                                ->body("User {$record->name} was deleted by {$actor->name}")
                                ->danger()
                                ->sendToDatabase(User::whereIn('role', [1, 2])->get());
                        }),
                ])

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
