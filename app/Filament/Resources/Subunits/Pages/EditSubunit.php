<?php

namespace App\Filament\Resources\Subunits\Pages;

use App\Filament\Resources\Subunits\SubunitResource;
use App\Models\Subunit;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditSubunit extends EditRecord
{
    protected static string $resource = SubunitResource::class;

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
        return SubunitResource::getUrl('index');
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $unitId = $record->unit_id;

        $items = $data['subunits'] ?? null;

        if (is_array($items) && count($items) > 0) {
            $existing = Subunit::where('unit_id', $unitId)->get()->keyBy('id');

            $keepIds = [];

            foreach ($items as $row) {
                $id = $row['id'] ?? null;
                $nama = strtoupper(trim((string) ($row['nama_subunit'] ?? '')));

                if ($nama === '') {
                    continue;
                }

                $payload = [
                    'unit_id' => $unitId,
                    'nama_subunit' => $nama,
                    'parlimen_id' => $row['parlimen_id'] ?? null,
                    'dun_id' => $row['dun_id'] ?? null,
                ];

                if ($id && $existing->has($id)) {
                    $existing->get($id)->update($payload);
                    $keepIds[] = $id;
                } else {
                    $new = Subunit::create($payload);
                    $keepIds[] = $new->id;
                }
            }

            $toDelete = $existing->keys()->diff($keepIds);

            if ($toDelete->isNotEmpty()) {
                Subunit::whereIn('id', $toDelete)->delete();
            }

            return $record->refresh();
        }

        unset($data['subunits']);

        if (isset($data['nama_subunit'])) {
            $data['nama_subunit'] = strtoupper((string) $data['nama_subunit']);
        }

        $record->update($data);

        return $record;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        unset($data['nama_subunit'], $data['parlimen_id'], $data['dun_id']);

        return $data;
    }
}
