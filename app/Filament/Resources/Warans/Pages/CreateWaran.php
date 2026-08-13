<?php

namespace App\Filament\Resources\Warans\Pages;

use App\Filament\Resources\Warans\WaranResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class CreateWaran extends CreateRecord
{
    protected static string $resource = WaranResource::class;

    protected static bool $canCreateAnother = false;

    public function getTitle(): string
    {
        return 'Tambah Waran';
    }

    public function getBreadcrumb(): string
    {
        return 'Tambah';
    }
    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Tambah'),

            $this->getCancelFormAction()
                ->label('Batal'),
        ];
    }
    
    public function getBreadcrumbs(): array
    {
        // The back button (see getHeading()) replaces the need for a
        // breadcrumb trail on this page.
        return [];
    }

    public function getHeading(): string | Htmlable
    {
        return new HtmlString(
            '<button type="button" onclick="window.history.back()" class="mystaff-back-btn" aria-label="Kembali">' .
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>' .
            '</button>' .
            '<span>' . e($this->getTitle()) . '</span>'
        );
    }

    protected function afterCreate(): void
    {
        Log::info('Waran Created', [
            'waran_id' => $this->record->id,
            'user_id' => auth()->id(),
        ]);

        $creator = auth()->user();
        $waran = $this->record;
        $no_waran = $this->record->no_waran;

        $superadmin = User::where('role', 1 )->get();

        Notification::make()
        ->title('Waran Baru Ditambah')
        ->body("Waran {$no_waran} telah ditambah oleh {$creator->name}")
        ->success()
        ->actions([
            Action::make('view')
            ->label('Lihat Waran')
            ->url(
                WaranResource::getUrl('edit', [
                    'record' => $waran,
                ])
            )
            ->markAsRead(),
        ])
        ->sendToDatabase($superadmin);

        // $user = User::where('role', 3 )->get();

        // Notification::make()
        // ->title('Waran Baru Ditambah')
        // ->body("Waran {$no_waran} telah ditambah")
        // ->success()
        // ->actions([
        //     Action::make('view')
        //     ->label('Lihat Waran')
        //     ->url(
        //         WaranResource::getUrl('edit', [
        //             'record' => $waran,
        //         ])
        //     )
        //     ->markAsRead(),
        // ])
        // ->sendToDatabase($user);
    }
}
