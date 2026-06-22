<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Exports\UserExporter;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
            ->label('Tambah Pengguna'),
            // ->modal()
            // ->createAnother(false),
          Action::make('export')
            ->label('Export Excel')
            ->icon('heroicon-o-arrow-down-tray')
            // ->color('')
            ->url(route('export.users'))
            ->openUrlInNewTab(), // optional
        ];
    }

    public function getBreadcrumb(): string
    {
        return 'Senarai';
    }

    //  protected function afterDelete(): void
    // {
    //     $actor = auth()->user();

    //     $recipients = User::whereIn('role', [1, 2])->get();

    //     Notification::make()
    //         ->title('User Deleted')
    //         ->body("User {$record->name} was deleted by {$actor->name}")
    //         ->danger()
    //         ->sendToDatabase($recipients);
    // }
}
