<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class Info extends Page
{
    protected string $view = 'filament.pages.info';

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedInformationCircle;

    protected static ?string $navigationLabel = 'Info';

    protected static ?string $title = 'Info';

    protected static string|\UnitEnum|null $navigationGroup = 'Hebahan & Info';

    protected static ?int $navigationSort = 17;
}
