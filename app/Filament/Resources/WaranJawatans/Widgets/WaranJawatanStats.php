<?php

namespace App\Filament\Resources\WaranJawatans\Widgets;

use App\Models\WaranJawatan;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WaranJawatanStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $currentYear = Carbon::now()->year;
        $previousYear = $currentYear - 1;

        $currentTotal = WaranJawatan::whereYear('created_at', $currentYear)->count();
        $previousTotal = WaranJawatan::whereYear('created_at', $previousYear)->count();
        $total = WaranJawatan::count();
        $pengisian = WaranJawatan::whereNotNull('pegawai_id')->count();
        $kekosongan = $total - $pengisian;

        return [
            Stat::make('Jumlah Penempatan', $currentTotal)
                // ->description("vs {$previousYear}: {$previousTotal}")
                ->description(
                    $previousTotal
                    ? round((($currentTotal - $previousTotal) / $previousTotal) * 100, 1)
                    . '% berbanding tahun ' . $previousYear
                    : 'Tiada data tahun ' . $previousYear
                )
                ->chart([$previousTotal, $currentTotal]),

            Stat::make('Pengisian', $pengisian)
                ->description(
                    $total
                    ? round(($pengisian / $total) * 100, 1) . '% dari keseluruhan' : '0%'
                )
                ->chart([$total, $pengisian])
                ->color('success'),

            Stat::make('Kekosongan', $kekosongan)
                ->description(
                    $total
                    ? round(($kekosongan / $total) * 100, 1) . '% dari keseluruhan' : '0%'
                )
                ->chart([$pengisian, $kekosongan])
                ->color('danger')

        ];
    }
}
