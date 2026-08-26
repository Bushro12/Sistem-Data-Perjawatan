<?php

namespace App\Filament\Resources\LetakJawatans\Pages;

use App\Filament\Resources\LetakJawatans\LetakJawatanResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class EditLetakJawatan extends EditRecord
{
    protected static string $resource = LetakJawatanResource::class;

    /**
     * The bottom-bar save button is hidden — saving is done through the
     * wizard's "Simpan" button (see LetakJawatanForm) which starts the
     * validateBeforeSubmit() flow below.
     */
    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->hidden();
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Batal');
    }

    /**
     * Hidden header action that performs the actual save after the
     * confirmation modal is confirmed.
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('confirmSave')
                ->label('Simpan')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Pengesahan')
                ->modalDescription('Adakah anda pasti mahu menyimpan maklumat ini?')
                ->modalSubmitActionLabel('Ya, Simpan')
                ->extraAttributes([
                    'class' => 'hidden',
                ])
                ->action(function () {
                    parent::save();
                }),
        ];
    }

    public function validateBeforeSubmit(): void
    {
        $this->form->validate();

        $this->mountAction('confirmSave');
    }

    /**
     * Unchecked checkboxes arrive as null (the columns are NOT NULL) and the
     * "ikatan_lppsa" checkbox is stored in the "pinjaman_lppsa" column.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['ikatan_jpa'] = (bool) ($data['ikatan_jpa'] ?? false);
        $data['ikatan_bpl'] = (bool) ($data['ikatan_bpl'] ?? false);
        $data['pinjaman_lppsa'] = (bool) ($data['pinjaman_lppsa'] ?? $data['ikatan_lppsa'] ?? false);

        return $data;
    }

    protected function getRedirectUrl(): ?string
    {
        return LetakJawatanResource::getUrl('index');
    }

    public function getTitle(): string
    {
        return 'Kemaskini Maklumat Letak Jawatan';
    }

    public function getBreadcrumbs(): array
    {
        // The back button (see getHeading()) replaces the need for a
        // breadcrumb trail on this page.
        return [];
    }

    public function getHeading(): string|Htmlable
    {
        return new HtmlString(
            '<button type="button" onclick="window.history.back()" class="mystaff-back-btn" aria-label="Kembali">'.
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>'.
            '</button>'.
            '<span>'.e($this->getTitle()).'</span>'
        );
    }
}
