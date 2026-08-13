<?php

namespace App\Filament\Resources\Pegawais\Widgets;

use App\Models\Pegawai;
use Filament\Widgets\StatsOverviewWidget;
use Illuminate\Database\Eloquent\Builder;

class PegawaiStats extends StatsOverviewWidget
{
    protected string $view = 'filament.widgets.pegawai-stats';

    public int $totalPegawai = 0;

    public int $lengkap = 0;

    public int $tidakLengkap = 0;

    public function mount(): void
    {
        $this->totalPegawai = Pegawai::count();

        // Same definition as the "Status" column in PegawaisTable
        // (ptj/bahagian missing, expected unit/subunit missing, or
        // non-JTW non-kontrak without a waran).
        $this->tidakLengkap = Pegawai::where(function (Builder $q) {
            $q->whereNull('ptj_id')
                ->orWhereNull('bahagian_id')
                ->orWhere(fn (Builder $q) => $q->whereNull('subunit_id')->where('ada_unit', 0))
                ->orWhere(fn (Builder $q) => $q->whereNull('unit_id')->where('ada_subunit', 0))
                ->orWhere(fn (Builder $q) => $q->where('is_jtw', 0)
                    ->where('is_kontrak', 0)
                    ->whereDoesntHave('waranJawatan.waran'));
        })->count();

        $this->lengkap = $this->totalPegawai - $this->tidakLengkap;
    }
}
