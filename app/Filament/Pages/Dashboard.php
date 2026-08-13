<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Panel;
use App\Models\Hebahan;
use App\Models\Waran;
use App\Models\Program;
use App\Models\WaranJawatan;

class Dashboard extends Page
{
protected string $view = 'filament.pages.dashboard';
    protected static string|BackedEnum|null $navigationIcon = null;
    // protected static ?string $navigationLabel = 'Dashboard';
    protected static ?int $navigationSort = -2;

    public static function getRoutePath(Panel $panel): string
    {
        return '/';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('today')
                ->label(now()->locale('ms')->translatedFormat('d F Y'))
                ->disabled()
                ->color('gray')
                ->extraAttributes([
                    'style' => 'background:transparent;border:none;box-shadow:none;padding:0;cursor:default;opacity:1;font-weight:500;',
                ]),
        ];
    }

    public function getViewData(): array
    {
        $allWarans = Waran::with(['waranJawatan'])->get();

        $totalWaran           = $allWarans->count();
        $totalLebih           = $allWarans->filter(fn($w) => $w->status_jik === 'Lebih')->count();
        $totalKurang          = $allWarans->filter(fn($w) => $w->status_jik === 'Kurang')->count();
        $totalSeimbang        = $allWarans->filter(fn($w) => $w->status_jik === 'Seimbang')->count();
        $totalPengisianSemasa = $allWarans->sum('isi_count');
        $totalKekosongan      = $allWarans->sum('kosong_count');

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

        $recentHebahans = Hebahan::where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('dipaparkan_sehingga')
                    ->orWhere('dipaparkan_sehingga', '>=', now()->toDateString());
            })
            ->latest('tarikh_hebahan')
            ->take(5)
            ->get();

        return [
            'totalWaran'           => $totalWaran,
            'totalLebih'           => $totalLebih,
            'totalKurang'          => $totalKurang,
            'totalSeimbang'        => $totalSeimbang,
            'totalPengisianSemasa' => $totalPengisianSemasa,
            'totalKekosongan'      => $totalKekosongan,
            'recentWarans'   => $recentWarans,
            'waranByProgram' => $waranByProgram->sortByDesc('waran_count')->values(),
            'recentHebahans' => $recentHebahans,
        ];
    }
}
