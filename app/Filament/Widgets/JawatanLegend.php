<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class JawatanLegend extends Widget
{
    protected static bool $isDiscovered = false;
    protected string $view = 'filament.widgets.jawatan-legend';

    protected int | string | array $columnSpan = 'full';
}
