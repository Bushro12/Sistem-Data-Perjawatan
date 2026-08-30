<?php

namespace App\Filament\Resources\Pegawais\Pages;

use App\Filament\Resources\Pegawais\PegawaiResource;
use App\Models\PegawaiKontrak;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;

class EditPegawai extends EditRecord
{
    protected static string $resource = PegawaiResource::class;

    /**
     * The bottom-bar save button is hidden — saving is done through the
     * wizard's "Simpan" button (see PegawaiForm) which starts the
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
            // DeleteAction::make(),

            Action::make('confirmSave')
                ->label('Simpan')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Pengesahan')
                ->modalDescription('Adakah anda pasti mahu simpan perubahan ini?')
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

    protected function getRedirectUrl(): string
    {
        return PegawaiResource::getUrl('index');
    }

    protected function afterSave(): void
    {
        $isKontrak = ! empty($this->data['is_kontrak']) || ! empty($this->data['is_kontrak_isi_tetap']);

        if ($isKontrak) {
            // Kontrak Isi Tetap gets program/aktiviti from its waran, not this form.
            $hasProgramAktiviti = ! empty($this->data['is_kontrak']);

            PegawaiKontrak::updateOrCreate(
                ['pegawai_id' => $this->record->id],
                [
                    'program_id' => $hasProgramAktiviti ? ($this->data['program_id'] ?? null) : null,
                    'aktiviti_id' => $hasProgramAktiviti ? ($this->data['aktiviti_id'] ?? null) : null,
                    'tarikh_lantikan1' => $this->data['tarikh_lantikan1'] ?? null,
                    'tarikh_tamat1' => $this->data['tarikh_tamat1'] ?? null,
                    'tarikh_lantikan2' => $this->data['tarikh_lantikan2'] ?? null,
                    'tarikh_tamat2' => $this->data['tarikh_tamat2'] ?? null,
                    'tarikh_lantikan3' => $this->data['tarikh_lantikan3'] ?? null,
                    'tarikh_tamat3' => $this->data['tarikh_tamat3'] ?? null,
                    'tarikh_lantikan4' => $this->data['tarikh_lantikan4'] ?? null,
                    'tarikh_tamat4' => $this->data['tarikh_tamat4'] ?? null,
                    'tarikh_lantikan5' => $this->data['tarikh_lantikan5'] ?? null,
                    'tarikh_tamat5' => $this->data['tarikh_tamat5'] ?? null,
                ]
            );
        } else {
            PegawaiKontrak::where('pegawai_id', $this->record->id)->delete();
        }

        Log::info('Pegawai updated', [
            'pegawai_id' => $this->record->id,
            'user_id' => auth()->id(),
            'changes' => $this->record->getChanges(),
        ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $kontrak = PegawaiKontrak::where('pegawai_id', $this->record->id)->first();

        if ($kontrak) {
            $data = array_merge($data, $kontrak->toArray());
        }

        return $data;
    }

    public function getTitle(): string
    {
        return 'Kemaskini Maklumat Pegawai';
    }

    public function getBreadcrumbs(): array
    {
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
