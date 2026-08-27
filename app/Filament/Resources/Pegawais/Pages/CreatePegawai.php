<?php

namespace App\Filament\Resources\Pegawais\Pages;

use App\Filament\Resources\Pegawais\PegawaiResource;
use App\Models\PegawaiKontrak;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;

class CreatePegawai extends CreateRecord
{
    protected static string $resource = PegawaiResource::class;

    public function getTitle(): string
    {
        return 'Tambah Pegawai';
    }

    public function getBreadcrumb(): string
    {
        return 'Tambah';
    }

    protected function afterCreate(): void
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
        }

        Log::info('Pegawai Created', [
            'pegawai_id' => $this->record->id,
            'user_id' => auth()->id(),
        ]);

        $creator = auth()->user();
        $pegawai = $this->record;

        $recipients = User::whereIn('role', [1, 2])->get();

        Notification::make()
            ->title('Pegawai Baru Ditambah')
            ->body("Pegawai baru telah ditambah oleh {$creator->name}")
            ->success()
            ->actions([
                Action::make('view')
                    ->label('Lihat Pegawai')
                    ->url(
                        PegawaiResource::getUrl('view', [
                            'record' => $pegawai,
                        ])
                    )
                    ->markAsRead(),
            ])
            ->sendToDatabase($recipients);
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Batal');
    }

    /**
     * The bottom-bar create button is hidden — saving is done through the
     * wizard's "Simpan" button (see PegawaiForm) which starts the
     * validateBeforeSubmit() flow below.
     */
    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->hidden();
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
            ->hidden();
    }

    /**
     * Hidden header action that performs the actual create after the
     * confirmation modal is confirmed.
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('confirmCreate')
                ->label('Simpan')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Pengesahan')
                ->modalDescription('Adakah anda pasti mahu tambah maklumat ini?')
                ->modalSubmitActionLabel('Ya, Simpan')
                ->extraAttributes([
                    'class' => 'hidden',
                ])
                ->action(function () {
                    parent::create();
                }),
        ];
    }

    public function validateBeforeSubmit(): void
    {
        $this->form->validate();

        $this->mountAction('confirmCreate');
    }

    protected function getRedirectUrl(): string
    {
        return PegawaiResource::getUrl('index');
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
