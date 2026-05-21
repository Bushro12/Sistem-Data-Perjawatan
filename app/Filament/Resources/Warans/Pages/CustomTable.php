<?php

namespace App\Filament\Resources\Warans\Pages;

use App\Filament\Resources\Warans\WaranResource;
use Filament\Resources\Pages\Page;

class CustomTable extends Page
{
    protected static string $resource = WaranResource::class;

    protected string $view = 'filament.resources.warans.pages.custom-table';

        protected static ?string $title = 'Buku Waran';

}
