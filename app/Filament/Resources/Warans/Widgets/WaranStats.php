<?php

namespace App\Filament\Resources\Warans\Widgets;

use App\Models\Waran;
use DB;
use Filament\Widgets\StatsOverviewWidget;

class WaranStats extends StatsOverviewWidget
{
    protected string $view = 'filament.widgets.waran-stats';

    public int $totalWaran = 0;
    public int $seimbang = 0;
    public int $tidakSeimbang = 0;
    public float $seimbangPct = 0;
    public float $tidakSeimbangPct = 0;

    public function mount(): void
    {
        $seimbang = Waran::leftJoin('waran_jawatans', function ($join) {
            $join->on(function ($query) {
                $query->on('warans.id', '=', 'waran_jawatans.waran_id')
                    ->orOn('warans.id', '=', 'waran_jawatans.waran_tolak_id');
            });
        })
            ->select(
                'warans.id',
                'warans.jik',
                'warans.jenis',
                DB::raw("
        SUM(
            CASE
                WHEN warans.jenis = 'Tambah'
                     AND waran_jawatans.waran_id = warans.id
                     AND waran_jawatans.pegawai_id IS NOT NULL
                THEN 1

                WHEN warans.jenis = 'Tolak'
                     AND waran_jawatans.waran_tolak_id = warans.id
                THEN 1

                ELSE 0
            END
        ) AS jumlah
    ")
            )
            ->groupBy('warans.id', 'warans.jik', 'warans.jenis')
            ->havingRaw('jumlah = warans.jik')
            ->count();

        $this->totalWaran = Waran::count();
        $this->seimbang = $seimbang;
        $this->tidakSeimbang = $this->totalWaran - $seimbang;
        $this->seimbangPct = $this->totalWaran ? round(($seimbang / $this->totalWaran) * 100, 1) : 0;
        $this->tidakSeimbangPct = $this->totalWaran ? round(($this->tidakSeimbang / $this->totalWaran) * 100, 1) : 0;
    }
}
