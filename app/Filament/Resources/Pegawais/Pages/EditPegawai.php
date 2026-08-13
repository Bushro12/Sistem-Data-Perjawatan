<?php

namespace App\Filament\Resources\Pegawais\Pages;

use App\Filament\Resources\Pegawais\PegawaiResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;
use illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class EditPegawai extends EditRecord
{
    protected static string $resource = PegawaiResource::class;

    protected function getSaveFormAction(): Action
    {
        return Action::make('save')
            ->label('Simpan')
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading('Pengesahan')
            ->modalDescription('Adakah anda pasti mahu simpan perubahan ini?')
            ->action(fn() => $this->save());
    }
    

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Batal');
    }
    protected function afterSave(): void
    {
        if ($this->data['is_kontrak']) {

            \App\Models\PegawaiKontrak::updateOrCreate(
                ['pegawai_id' => $this->record->id],
                [
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
            \App\Models\PegawaiKontrak::where('pegawai_id', $this->record->id)->delete();
        }

        Log::info('Pegawai updated', [
            'pegawai_id' => $this->record->id,
            'user_id' => auth()->id(),
            'changes' => $this->record->getChanges(),
        ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $kontrak = \App\Models\PegawaiKontrak::where('pegawai_id', $this->record->id)->first();

        if ($kontrak) {
            $data = array_merge($data, $kontrak->toArray());
        }

        return $data;
    }
    protected function getHeaderActions(): array
    {
        return [
            // DeleteAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return 'Kemaskini Maklumat Pegawai';
    }

    public function getBreadcrumbs(): array
    {
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
