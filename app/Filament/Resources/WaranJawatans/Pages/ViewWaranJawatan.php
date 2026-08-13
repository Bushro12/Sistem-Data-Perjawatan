<?php

namespace App\Filament\Resources\WaranJawatans\Pages;

use App\Filament\Resources\WaranJawatans\WaranJawatanResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewWaranJawatan extends ViewRecord
{
    protected static string $resource = WaranJawatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function getTitle(): string
    {
        // return 'Paparan ' . ($this->record->butiran) . ' - ' . ($this->record->aktiviti?->no_aktivit) . ' ' . ($this->record->aktiviti?->nama_aktiviti);
        return 'Butiran ' . ($this->record->butiran);

    }

    public function getBreadCrumb(): string
    {
        return 'Paparan';
    }
}
