<?php

namespace App\Filament\Resources\Units\Pages;

use App\Filament\Resources\Units\UnitResource;
use App\Models\Unit;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditUnit extends EditRecord
{
    protected static string $resource = UnitResource::class;

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
        return UnitResource::getUrl('index');
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $bahagianId = $record->bahagian_id;

        $unitsData = $data['units'] ?? [];

        if (is_array($unitsData) && count($unitsData) > 0) {
            $existing = Unit::where('bahagian_id', $bahagianId)->get()->keyBy('id');

            $keepIds = [];

            foreach ($unitsData as $item) {
                $id = $item['id'] ?? null;
                $payload = [
                    'bahagian_id' => $bahagianId,
                    'nama_unit' => strtoupper(trim((string) ($item['nama_unit'] ?? ''))),
                    'parlimen_id' => $item['parlimen_id'] ?? null,
                    'dun_id' => $item['dun_id'] ?? null,
                ];

                if ($payload['nama_unit'] === '') {
                    continue;
                }

                if ($id && $existing->has($id)) {
                    $existing->get($id)->update($payload);
                    $keepIds[] = $id;
                } else {
                    $new = Unit::create($payload);
                    $keepIds[] = $new->id;
                }
            }

            // Delete units removed from repeater
            $toDelete = $existing->keys()->diff($keepIds);

            if ($toDelete->isNotEmpty()) {
                Unit::whereIn('id', $toDelete)->delete();
            }

            return $record->refresh();
        }

        // Fallback single update
        unset($data['units'], $data['ptj_id']);

        if (isset($data['nama_unit'])) {
            $data['nama_unit'] = strtoupper((string) $data['nama_unit']);
        }

        $record->update($data);

        return $record;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Units are hydrated via Repeater afterStateHydrated, prevent single nama_unit overwrite
        unset($data['nama_unit'], $data['parlimen_id'], $data['dun_id']);

        return $data;
    }
}
