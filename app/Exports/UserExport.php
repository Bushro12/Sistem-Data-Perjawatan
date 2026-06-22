<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;

class UserExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
{
    return User::with('ptj')->get()->map(function ($user) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'ptj' => $user->ptj?->nama_ptj,
        ];
    });
}

    public function headings(): array
    {
        return [
            'ID',
            'Nama',
            'PTJ',
        ];
    }
}
