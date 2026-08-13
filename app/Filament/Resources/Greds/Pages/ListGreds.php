<?php

namespace App\Filament\Resources\Greds\Pages;

use App\Filament\Resources\Greds\GredResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use illuminate\Support\HtmlString;
use illuminate\Contracts\Support\Htmlable;

class ListGreds extends ListRecords
{
    protected static string $resource = GredResource::class;

     protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Gred')
                ->modal()
                ->createAnother(false)
                ->modalHeading('Tambah Gred')
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
