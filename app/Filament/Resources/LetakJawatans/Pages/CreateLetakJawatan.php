<?php

namespace App\Filament\Resources\LetakJawatans\Pages;

use App\Filament\Resources\LetakJawatans\LetakJawatanResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Stringable;

class CreateLetakJawatan extends CreateRecord
{
    protected static string $resource = LetakJawatanResource::class;

    protected function getCreateFormAction() : Action
    {
        return Action::make('create')
        ->label('Tambah')
        ->color('primary')
        ->requiresConfirmation()
        ->modalHeading('Pengesahan')
            ->modalDescription('Adakah anda pasti mahu tambah maklumat ini?')
            ->action(fn() => $this->create());
    }


    protected function getCreateAnotherFormAction() : Action
    {
        return parent::getCreateAnotherFormAction()
        ->hidden();
    }

    protected function getCancelFormAction() : Action
    {
        return parent::getCancelFormAction()
        ->label('Batal');
    }

    public function getTitle(): string
    {
        return 'Tambah Letak Jawatan';
    }

    public function getBreadcrumb(): string
    {
        return 'Tambah';
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

