<?php

namespace App\Filament\Resources\Warans\Widgets;

use App\Models\Waran;
use DB;
use Filament\Widgets\StatsOverviewWidget;

class WaranStats extends StatsOverviewWidget
{
    protected string $view = 'filament.widgets.waran-stats';

    public int $totalWaran     = 0;
    public int $seimbang       = 0;
    public int $tidakSeimbang  = 0;
    public float $seimbangPct     = 0;
    public float $tidakSeimbangPct = 0;

    public function mount(): void
    {
        $seimbang = Waran::leftJoin('waran_jawatans', function ($join) {
            $join->on('warans.id', '=', 'waran_jawatans.waran_id')
                ->whereNotNull('waran_jawatans.pegawai_id');
        })
            ->select('warans.id', 'warans.jik', DB::raw('COUNT(waran_jawatans.id) as jumlah_isi'))
            ->groupBy('warans.id', 'warans.jik')
            ->havingRaw('COUNT(waran_jawatans.id) = warans.jik')
            ->count();

        $this->totalWaran        = Waran::count();
        $this->seimbang          = $seimbang;
        $this->tidakSeimbang     = $this->totalWaran - $seimbang;
        $this->seimbangPct       = $this->totalWaran ? round(($seimbang / $this->totalWaran) * 100, 1) : 0;
        $this->tidakSeimbangPct  = $this->totalWaran ? round(($this->tidakSeimbang / $this->totalWaran) * 100, 1) : 0;
    }
}
