<?php

namespace App\Filament\Resources\Warans\Pages;

use App\Filament\Resources\Warans\WaranResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class ViewWaran extends ViewRecord
{
    protected static string $resource = WaranResource::class;

    public string $viewMode = 'active';

    public function mount($record): void
    {
        parent::mount($record);

        $this->viewMode = 'active';
    }
    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return ($this->record->no_waran);
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
}
