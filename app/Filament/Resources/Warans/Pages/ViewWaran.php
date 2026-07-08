<?php

namespace App\Filament\Resources\Warans\Pages;

use App\Filament\Resources\Warans\WaranResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewWaran extends ViewRecord
{
    protected static string $resource = WaranResource::class;

    public string $viewMode = 'active';

    public function mount($record): void
    {
        parent::mount($record);

        $this->viewMode = 'active';
    }
    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
