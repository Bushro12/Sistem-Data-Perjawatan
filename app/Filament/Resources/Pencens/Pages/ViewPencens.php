<?php

namespace App\Filament\Resources\Pencens\Pages;

use App\Filament\Resources\Pencens\PencenResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPencen extends ViewRecord
{
    protected static string $resource = PencenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function getBreadCrumb(): string
    {
        return 'Paparan';
    }

    public function getTitle(): string
    {
        return ($this->record->nama);
    }

}
