<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum Kumpulan: string implements HasLabel, HasColor
{
    case PP = 'P&P';
    case Pelaksana = 'Pelaksana';
    case Paramedik = 'Paramedik';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PP => 'P&P',
            self::Pelaksana => 'Pelaksana',
            self::Paramedik => 'Paramedik',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PP => 'info',
            self::Pelaksana => 'success',
            self::Paramedik => 'danger',
        };
    }
}
