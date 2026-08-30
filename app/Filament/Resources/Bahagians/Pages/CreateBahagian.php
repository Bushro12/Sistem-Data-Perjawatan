<?php

namespace App\Filament\Resources\Bahagians\Pages;

use App\Filament\Resources\Bahagians\BahagianResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateBahagian extends CreateRecord
{
    protected static string $resource = BahagianResource::class;

    public function getTitle(): string
    {
        return 'Tambah Bahagian';
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
        return BahagianResource::getUrl('index');
    }

    protected function handleRecordCreation(array $data): Model
    {
        $items = $data['bahagians'] ?? null;

        if (is_array($items) && count($items) > 0) {
            $ptjId = $data['ptj_id'] ?? null;

            $first = null;

            foreach ($items as $row) {
                $nama = strtoupper(trim((string) ($row['nama_bahagian'] ?? '')));

                if ($nama === '') {
                    continue;
                }

                $record = static::getModel()::create([
                    'ptj_id' => $ptjId,
                    'nama_bahagian' => $nama,
                ]);

                $first ??= $record;
            }

            return $first ?? static::getModel()::create([
                'ptj_id' => $ptjId,
                'nama_bahagian' => strtoupper((string) ($data['nama_bahagian'] ?? '')),
            ]);
        }

        unset($data['bahagians']);

        if (isset($data['nama_bahagian'])) {
            $data['nama_bahagian'] = strtoupper((string) $data['nama_bahagian']);
        }

        return static::getModel()::create($data);
    }
}
