<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

use App\Models\Jawatan;
use App\Models\Kumpulan;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
class L4Export implements FromCollection
{
    /**
     * @return Collection<int, array<int, mixed>>
     */
    public function collection(): Collection
    {
        return collect();
    }
}
