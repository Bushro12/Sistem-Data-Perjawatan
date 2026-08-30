<?php

namespace App\Filament\Resources\Bahagians\Pages;

use App\Filament\Resources\Bahagians\BahagianResource;
use App\Models\Bahagian;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditBahagian extends EditRecord
{
    protected static string $resource = BahagianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // DeleteAction::make(),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label('Simpan');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Batal');
    }

    protected function getRedirectUrl(): ?string
    {
        return BahagianResource::getUrl('index');
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $ptjId = $record->ptj_id;

        $items = $data['bahagians'] ?? null;

        if (is_array($items) && count($items) > 0) {
            $existing = Bahagian::where('ptj_id', $ptjId)->get()->keyBy('id');

            $keepIds = [];

            foreach ($items as $row) {
                $id = $row['id'] ?? null;
                $nama = strtoupper(trim((string) ($row['nama_bahagian'] ?? '')));

                if ($nama === '') {
                    continue;
                }

                $payload = [
                    'ptj_id' => $ptjId,
                    'nama_bahagian' => $nama,
                ];

                if ($id && $existing->has($id)) {
                    $existing->get($id)->update($payload);
                    $keepIds[] = $id;
                } else {
                    $new = Bahagian::create($payload);
                    $keepIds[] = $new->id;
                }
            }

            $toDelete = $existing->keys()->diff($keepIds);

            if ($toDelete->isNotEmpty()) {
                Bahagian::whereIn('id', $toDelete)->delete();
            }

            return $record->refresh();
        }

        unset($data['bahagians'], $data['ptj_id']);

        if (isset($data['nama_bahagian'])) {
            $data['nama_bahagian'] = strtoupper((string) $data['nama_bahagian']);
        }

        $record->update($data);

        return $record;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        unset($data['nama_bahagian']);

        return $data;
    }
}
