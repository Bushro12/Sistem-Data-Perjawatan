<?php

namespace App\Filament\Resources\Warans\Widgets;

use App\Models\Waran;
use App\Models\WaranJawatan;
use DB;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WaranStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {

        $total = WaranJawatan::count();
        $seimbang = Waran::leftJoin('waran_jawatans', function ($join) {
            $join->on('warans.id', '=', 'waran_jawatans.waran_id')
                ->whereNotNull('waran_jawatans.pegawai_id');
        })
            ->select(
                'warans.id',
                'warans.jik',
                DB::raw('COUNT(waran_jawatans.id) as jumlah_isi')
            )
            ->groupBy('warans.id', 'warans.jik')
            ->havingRaw('COUNT(waran_jawatans.id) = warans.jik')
            ->count();

        $tidak_seimbang = $total - $seimbang;

        return [
            Stat::make('Jumlah Waran', Waran::count())
                ->chart([10, 12, 9, 14, Waran::count(), Waran::count() + 2, Waran::count() + 1]),

            Stat::make('Waran Seimbang', $seimbang)
                ->description(
                    $total
                    ? round(($seimbang / $total) * 100, 1) . '% dari keseluruhan'
                    : '0%'
                )
                // ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->chart([10, 12, 9, 14, $seimbang, $seimbang + 2, $seimbang + 1])
                ->color('success'),

            Stat::make('Waran Tidak Seimbang', $tidak_seimbang)
                ->description(
                    $total
                    ? round(($tidak_seimbang / $total) * 100, 1) . '% dari keseluruhan'
                    : '0%'
                )
                // ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([5, 8, 10, 12, $tidak_seimbang, $tidak_seimbang + 1, $tidak_seimbang + 3])
                ->color('danger'),

        ];

    }
}
