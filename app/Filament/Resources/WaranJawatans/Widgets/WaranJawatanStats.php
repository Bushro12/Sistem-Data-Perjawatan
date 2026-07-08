<?php

namespace App\Filament\Resources\WaranJawatans\Widgets;

use App\Models\WaranJawatan;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;

class WaranJawatanStats extends StatsOverviewWidget
{
    protected string $view = 'filament.widgets.waran-jawatan-stats';

    public int $currentTotal = 0;

    public int $pengisian = 0;

    public int $kekosongan = 0;

    public float $pengisianPct = 0;

    public float $kekosonganPct = 0;

    public ?float $yoyChangePct = null;

    public int $previousYear = 0;

    public function mount(): void
    {
        $currentYear = Carbon::now()->year;
        $this->previousYear = $currentYear - 1;

        $this->currentTotal = WaranJawatan::whereYear('created_at', $currentYear)->count();
        $previousTotal = WaranJawatan::whereYear('created_at', $this->previousYear)->count();
        $total = WaranJawatan::count();

        $this->pengisian = WaranJawatan::whereNotNull('pegawai_id')->count();
        $this->kekosongan = $total - $this->pengisian;

        $this->yoyChangePct = $previousTotal
            ? round((($this->currentTotal - $previousTotal) / $previousTotal) * 100, 1)
            : null;

        $this->pengisianPct = $total ? round(($this->pengisian / $total) * 100, 1) : 0;
        $this->kekosonganPct = $total ? round(($this->kekosongan / $total) * 100, 1) : 0;
    }
}
