<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use App\Models\Waran;
use App\Models\Pegawai;
use App\Models\Ptj;
use App\Models\Program;
use App\Models\WaranJawatan;

class Dashboard extends Page
{
protected string $view = 'filament.pages.dashboard';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?int $navigationSort = -2;

    public function getViewData(): array
    {
        $allWarans = Waran::with(['waranJawatan'])->get();

        $totalWaran    = $allWarans->count();
        $totalLebih    = $allWarans->filter(fn($w) => $w->status_jik === 'Lebih')->count();
        $totalKurang   = $allWarans->filter(fn($w) => $w->status_jik === 'Kurang')->count();
        $totalSeimbang = $allWarans->filter(fn($w) => $w->status_jik === 'Seimbang')->count();

        $recentWarans = Waran::with(['waranJawatan'])->latest()->take(5)->get();

        $waranByProgram = collect();
        $programs = Program::with('aktiviti')->get();
        foreach ($programs as $program) {
            $count = WaranJawatan::whereIn('aktiviti_id', $program->aktiviti->pluck('id'))->count();
            if ($count > 0) {
                $waranByProgram->push((object)[
                    'nama_program' => $program->nama_program,
                    'desc_program' => $program->desc_program,
                    'waran_count'  => $count,
                ]);
            }
        }

        return [
            'totalWaran'     => $totalWaran,
            'totalLebih'     => $totalLebih,
            'totalKurang'    => $totalKurang,
            'totalSeimbang'  => $totalSeimbang,
            'recentWarans'   => $recentWarans,
            'waranByProgram' => $waranByProgram->sortByDesc('waran_count')->values(),
            'totalPtj'       => Ptj::count(),
            'totalPegawai'   => Pegawai::count(),
        ];
    }
}
