<?php

namespace App\Filament\Resources\Units\Pages;

use App\Filament\Resources\Units\UnitResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use illuminate\Support\HtmlString;
use Illuminate\Contracts\Support\Htmlable;

class ListUnits extends ListRecords
{
    protected static string $resource = UnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
            ->label('Tambah Unit')
            // // ->modal()
            // ->createAnother(false),
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
