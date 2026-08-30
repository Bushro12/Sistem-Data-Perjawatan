<?php

namespace App\Filament\Resources\Subunits\Pages;

use App\Filament\Resources\Subunits\SubunitResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSubunit extends CreateRecord
{
    protected static string $resource = SubunitResource::class;

    public function getTitle(): string
    {
        return 'Tambah Sub Unit';
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
        return SubunitResource::getUrl('index');
    }

    protected function handleRecordCreation(array $data): Model
    {
        $items = $data['subunits'] ?? null;

        if (is_array($items) && count($items) > 0) {
            $unitId = $data['unit_id'] ?? null;

            $first = null;

            foreach ($items as $row) {
                $nama = strtoupper(trim((string) ($row['nama_subunit'] ?? '')));

                if ($nama === '') {
                    continue;
                }

                $record = static::getModel()::create([
                    'unit_id' => $unitId,
                    'nama_subunit' => $nama,
                    'parlimen_id' => $row['parlimen_id'] ?? null,
                    'dun_id' => $row['dun_id'] ?? null,
                ]);

                $first ??= $record;
            }

            return $first ?? static::getModel()::create([
                'unit_id' => $unitId,
                'nama_subunit' => strtoupper((string) ($data['nama_subunit'] ?? '')),
                'parlimen_id' => $data['parlimen_id'] ?? null,
                'dun_id' => $data['dun_id'] ?? null,
            ]);
        }

        unset($data['subunits']);

        if (isset($data['nama_subunit'])) {
            $data['nama_subunit'] = strtoupper((string) $data['nama_subunit']);
        }

        return static::getModel()::create($data);
    }
}
