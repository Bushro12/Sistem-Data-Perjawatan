<?php

namespace App\Filament\Resources\OpsyenPencens\Pages;

use App\Filament\Resources\OpsyenPencens\OpsyenPencenResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use illuminate\Support\HtmlString;
use illuminate\Contracts\Support\Htmlable;

class ListOpsyenPencens extends ListRecords
{
    protected static string $resource = OpsyenPencenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Opsyen Pencem')
                ->modal()
                ->createAnother(false)
                ->modalHeading('Tambah Opsyen Pencen')
                ->modalSubmitActionLabel('Tambah')
                ->modalCancelActionLabel('Batal'),
        ];
    }

     public function getBreadcrumb(): string
    {
        return 'Senarai';
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
            '<a href="' . e(\App\Filament\Pages\Dashboard::getUrl()) . '" class="mystaff-back-btn" aria-label="Kembali ke Dashboard">' .
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>' .
            '</a>' .
            '<span>' . e($this->getTitle()) . '</span>'
        );
    }

}
