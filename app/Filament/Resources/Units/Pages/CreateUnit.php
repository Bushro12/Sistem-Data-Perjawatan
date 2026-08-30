<?php

namespace App\Filament\Resources\Units\Pages;

use App\Filament\Resources\Units\UnitResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateUnit extends CreateRecord
{
    protected static string $resource = UnitResource::class;

    public function getTitle(): string
    {
        return 'Tambah Unit';
    }

    public function getBreadcrumb(): string
    {
        return 'Tambah';
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Tambah');
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
            ->hidden();
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Batal');
    }

    protected function getRedirectUrl(): string
    {
        return UnitResource::getUrl('index');
    }

    protected function handleRecordCreation(array $data): Model
    {
        $units = $data['units'] ?? null;

        // Bulk creation via Repeater - each unit has its own parlimen/dun
        if (is_array($units) && count($units) > 0) {
            $bahagianId = $data['bahagian_id'] ?? null;

            $firstRecord = null;

            foreach ($units as $unitData) {
                $nama = strtoupper(trim((string) ($unitData['nama_unit'] ?? '')));

                if ($nama === '') {
                    continue;
                }

                $payload = [
                    'bahagian_id' => $bahagianId,
                    'nama_unit' => $nama,
                    'parlimen_id' => $unitData['parlimen_id'] ?? null,
                    'dun_id' => $unitData['dun_id'] ?? null,
                ];

                $record = static::getModel()::create($payload);

                $firstRecord ??= $record;
            }

            return $firstRecord ?? static::getModel()::create([
                'bahagian_id' => $bahagianId,
                'nama_unit' => strtoupper((string) ($data['nama_unit'] ?? '')),
                'parlimen_id' => $data['parlimen_id'] ?? null,
                'dun_id' => $data['dun_id'] ?? null,
            ]);
        }

        // Fallback single create (edit-form compatibility)
        unset($data['units'], $data['ptj_id']);

        if (isset($data['nama_unit'])) {
            $data['nama_unit'] = strtoupper((string) $data['nama_unit']);
        }

        return static::getModel()::create($data);
    }
}
