<?php

namespace App\Filament\Resources\Warans\Widgets;

use App\Models\Waran;
use App\Models\WaranJawatan;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WaranStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $kosong = WaranJawatan::whereNull('pegawai_id')->count();
        $terisi = WaranJawatan::whereNotNull('pegawai_id')->count();
        $total = WaranJawatan::count();

        return [
            Stat::make('Jumlah Waran', Waran::count())
            ->chart([10, 12, 9, 14, Waran::count(), Waran::count() + 2, Waran::count() + 1]),

            Stat::make('Jawatan Diisi', $terisi)
                ->description(
                    $total
                    ? round(($terisi / $total) * 100, 1) . '% dari keseluruhan'
                    : '0%'
                )
                // ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([5, 8, 10, 12, $terisi, $terisi + 1, $terisi + 3])
                ->color('success'),

            Stat::make('Kekosongan', $kosong)
                ->description(
                    $total
                    ? round(($kosong / $total) * 100, 1) . '% dari keseluruhan'
                    : '0%'
                )
                // ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->chart([10, 12, 9, 14, $kosong, $kosong + 2, $kosong + 1])
                ->color('danger'),
        ];

    }
}
