<?php

namespace App\Filament\Resources\Pencens\Pages;

use App\Filament\Resources\Pencens\PencenResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\CancelAction;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

use Filament\Resources\Pages\EditRecord;

class EditPencen extends EditRecord
{
    protected static string $resource = PencenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // DeleteAction::make(),
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

    public function getTitle(): string
    {
        return 'Kemaskini Maklumat Penamatan Perkhidmatan';
    }

    protected function getFormActions(): array
    {
        return [
        Action::make('cancel')
            ->label('Batal')
            ->color('gray')
            ->url($this->getResource()::getUrl('index')),
    ];
    }
}
