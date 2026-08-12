<x-filament-panels::page>

    {{-- Hebahan Terkini --}}
    <div style="margin-bottom:16px;">
        <x-filament::section heading="Hebahan Terkini">
            @forelse($recentHebahans as $hebahan)
                <div
                    style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; padding:10px 0; {{ !$loop->last ? 'border-bottom:1px solid #f3f4f6;' : '' }}">
                    <div>
                        <p style="margin:0; font-weight:600; font-size:13px;">{{ $hebahan->tajuk }}</p>
                        @if ($hebahan->kandungan)
                            <p style="margin:2px 0 0; font-size:12px; color:#6b7280;">
                                {{ \Illuminate\Support\Str::limit(strip_tags($hebahan->kandungan), 100) }}</p>
                        @endif
                        @if ($hebahan->lampiran)
                            <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($hebahan->lampiran) }}"
                                target="_blank" style="font-size:12px; color:#0ea5e9; text-decoration:underline;">
                                Lihat Lampiran
                            </a>
                        @endif
                    </div>
                    <span
                        style="font-size:12px; color:#6b7280; white-space:nowrap;">{{ $hebahan->tarikh_hebahan->translatedFormat('d M Y') }}</span>
                </div>
            @empty
                <p style="padding:8px 0; text-align:center; color:#6b7280; font-size:13px;">Tiada hebahan buat masa ini.
                </p>
            @endforelse
        </x-filament::section>
    </div>

    {{-- Stats Cards --}}
    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:16px;">
        <div class="sneat-stat-card sneat-stat-card--blue">
            <div class="sneat-stat-inner">
                <div>
                    <p class="sneat-stat-label">Jumlah Perjawatan Mengikut Waran</p>
                    <p class="sneat-stat-value">{{ $totalWaran }}</p>
                </div>
                <div class="sneat-stat-icon sneat-stat-icon--blue">
                    <x-filament::icon icon="heroicon-o-document-text" />
                </div>
            </div>
        </div>
        <div class="sneat-stat-card sneat-stat-card--green">
            <div class="sneat-stat-inner">
                <div>
                    <p class="sneat-stat-label">Pengisian Semasa</p>
                    <p class="sneat-stat-value">{{ $totalPengisianSemasa }}</p>
                </div>
                <div class="sneat-stat-icon sneat-stat-icon--success">
                    <x-filament::icon icon="heroicon-o-users" />
                </div>
            </div>
        </div>
        <div class="sneat-stat-card sneat-stat-card--red">
            <div class="sneat-stat-inner">
                <div>
                    <p class="sneat-stat-label">Kekosongan</p>
                    <p class="sneat-stat-value">{{ $totalKekosongan }}</p>
                </div>
                <div class="sneat-stat-icon sneat-stat-icon--danger">
                    <x-filament::icon icon="heroicon-o-user-minus" />
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">

        {{-- Donut Chart --}}
        <x-filament::section heading="Status Waran">
            @php
                $statusTotal = $totalLebih + $totalKurang + $totalSeimbang;
                $pct = fn ($n) => $statusTotal > 0 ? round(($n / $statusTotal) * 100) : 0;
            @endphp
            <div style="display:flex; flex-direction:column; align-items:center; gap:16px;">
                <div id="statusChart"></div>
                <div style="display:flex; gap:16px;">
                    <div style="display:flex; align-items:center; gap:6px;">
                        <div style="width:12px; height:12px; border-radius:50%; background:#10b981;"></div>
                        <span style="font-size:12px;">Lebih ({{ $pct($totalLebih) }}%)</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:6px;">
                        <div style="width:12px; height:12px; border-radius:50%; background:#f43f5e;"></div>
                        <span style="font-size:12px;">Kurang ({{ $pct($totalKurang) }}%)</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:6px;">
                        <div style="width:12px; height:12px; border-radius:50%; background:#2563eb;"></div>
                        <span style="font-size:12px;">Seimbang ({{ $pct($totalSeimbang) }}%)</span>
                    </div>
                </div>
            </div>
        </x-filament::section>

        {{-- Bar Chart --}}
        <x-filament::section heading="Jumlah Pengisian Waran Perjawatan Mengikut Program">
            <canvas id="programChart" style="max-height:220px;"></canvas>
        </x-filament::section>

    </div>

    {{-- Recent Waran --}}
    <div style="margin-bottom:16px;">

        <x-filament::section heading="Waran Terbaharu">
            <div style="border:1px solid #e5e7eb; border-radius:8px; overflow:hidden;">
                <table style="width:100%; border-collapse:collapse; font-size:13px;">
                    <thead>
                        <tr style="background:#f9fafb;">
                            <th style="text-align:left; padding:10px 12px; color:#6b7280; font-weight:600; font-size:11px; text-transform:uppercase; letter-spacing:.03em; border:1px solid #e5e7eb;">
                                <span style="display:inline-flex; align-items:center; gap:6px;">
                                    <x-filament::icon icon="heroicon-o-document-text" class="w-3.5 h-3.5 text-fg-indigo" />
                                    No. Waran
                                </span>
                            </th>
                            <th style="text-align:left; padding:10px 12px; color:#6b7280; font-weight:600; font-size:11px; text-transform:uppercase; letter-spacing:.03em; border:1px solid #e5e7eb;">Jenis</th>
                            <th style="text-align:center; padding:10px 12px; color:#6b7280; font-weight:600; font-size:11px; text-transform:uppercase; letter-spacing:.03em; border:1px solid #e5e7eb;">J</th>
                            <th style="text-align:center; padding:10px 12px; color:#6b7280; font-weight:600; font-size:11px; text-transform:uppercase; letter-spacing:.03em; border:1px solid #e5e7eb;">I</th>
                            <th style="text-align:center; padding:10px 12px; color:#6b7280; font-weight:600; font-size:11px; text-transform:uppercase; letter-spacing:.03em; border:1px solid #e5e7eb;">K</th>
                            <th style="text-align:left; padding:10px 12px; color:#6b7280; font-weight:600; font-size:11px; text-transform:uppercase; letter-spacing:.03em; border:1px solid #e5e7eb;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentWarans as $waran)
                            @php $status = $waran->status_jik; @endphp
                            <tr style="transition:background-color .15s ease;"
                                onmouseover="this.style.backgroundColor='#f9fafb'" onmouseout="this.style.backgroundColor='transparent'">
                                <td style="padding:10px 12px; border:1px solid #e5e7eb;">
                                    <a href="{{ \App\Filament\Resources\Warans\WaranResource::getUrl('view', ['record' => $waran]) }}"
                                        style="color:#4f46e5; font-weight:600; text-decoration:none;">
                                        {{ $waran->no_waran }}
                                    </a>
                                </td>
                                <td style="padding:10px 12px; border:1px solid #e5e7eb;">
                                    @if ($waran->jenis === 'Tambah')
                                        <x-filament::badge color="success" icon="heroicon-o-plus">Tambah</x-filament::badge>
                                    @else
                                        <x-filament::badge color="warning" icon="heroicon-o-minus">Tolak</x-filament::badge>
                                    @endif
                                </td>
                                <td style="padding:10px 12px; text-align:center; border:1px solid #e5e7eb;">
                                    <span style="display:inline-flex; min-width:24px; justify-content:center; padding:2px 8px; border-radius:999px; font-weight:600; font-size:12px; background:#f3f4f6; color:#374151;">{{ $waran->jik }}</span>
                                </td>
                                <td style="padding:10px 12px; text-align:center; border:1px solid #e5e7eb;">
                                    <span style="display:inline-flex; min-width:24px; justify-content:center; padding:2px 8px; border-radius:999px; font-weight:600; font-size:12px; background:#dcfce7; color:#15803d;">{{ $waran->isi_count }}</span>
                                </td>
                                <td style="padding:10px 12px; text-align:center; border:1px solid #e5e7eb;">
                                    @if ($waran->kosong_count < 0)
                                        <span style="display:inline-flex; min-width:24px; justify-content:center; padding:2px 8px; border-radius:999px; font-weight:600; font-size:12px; background:#fee2e2; color:#b91c1c;">{{ $waran->kosong_count }}</span>
                                    @else
                                        <span style="display:inline-flex; min-width:24px; justify-content:center; padding:2px 8px; border-radius:999px; font-weight:600; font-size:12px; background:#fef3c7; color:#b45309;">{{ $waran->kosong_count }}</span>
                                    @endif
                                </td>
                                <td style="padding:10px 12px; border:1px solid #e5e7eb;">
                                    @if ($status === 'Lebih')
                                        <x-filament::badge color="success">{{ $status }}</x-filament::badge>
                                    @elseif($status === 'Kurang')
                                        <x-filament::badge color="danger">{{ $status }}</x-filament::badge>
                                    @else
                                        <x-filament::badge color="info">{{ $status }}</x-filament::badge>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="padding:16px 0; text-align:center; color:#6b7280; border:1px solid #e5e7eb;">Tiada waran
                                    ditemui.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        let statusApexChart = null;

        function initDashboardCharts() {
            const statusEl = document.getElementById('statusChart');
            const programEl = document.getElementById('programChart');

            if (!statusEl || !programEl) {
                return;
            }

            // Destroy any chart already bound to these elements (Filament's
            // SPA navigation can re-run this without a full page reload).
            statusApexChart?.destroy();
            Chart.getChart(programEl)?.destroy();

            // Donut Chart — Sneat CRM demo style (ApexCharts): compact,
            // no built-in legend, 75% cutout, single aggregate total
            // centered instead of per-segment labels.
            statusApexChart = new ApexCharts(statusEl, {
                chart: { type: 'donut', height: 200, width: 200 },
                series: [{{ $totalLebih }}, {{ $totalKurang }}, {{ $totalSeimbang }}],
                labels: ['Lebih', 'Kurang', 'Seimbang'],
                colors: ['#10b981', '#f43f5e', '#2563eb'],
                legend: { show: false },
                dataLabels: { enabled: false },
                stroke: { width: 0 },
                tooltip: {
                    // Custom HTML tooltip instead of ApexCharts' default
                    // template — the app's global CSS was clobbering the
                    // default markup, leaving just the colour swatch icon
                    // visible with no text.
                    custom: function ({ series, seriesIndex, w }) {
                        const label = w.globals.labels[seriesIndex];
                        const value = series[seriesIndex];
                        const total = series.reduce((a, b) => a + b, 0);
                        const pct = total > 0 ? Math.round((value / total) * 100) : 0;
                        const color = w.globals.colors[seriesIndex];
                        return '<div style="padding:6px 10px; font-size:12px; font-weight:600; background:#1f2937; color:#fff; border-radius:6px; display:flex; align-items:center; gap:6px; white-space:nowrap;">'
                            + '<span style="width:8px; height:8px; border-radius:50%; background:' + color + ';"></span>'
                            + label + ': ' + value + ' (' + pct + '%)'
                            + '</div>';
                    },
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    showAlways: true,
                                    label: 'Jumlah',
                                    fontSize: '12px',
                                    color: '#6b7280',
                                    formatter: () => '{{ $totalWaran }}',
                                },
                                value: {
                                    fontSize: '24px',
                                    fontWeight: 700,
                                    color: '#32475c',
                                },
                            },
                        },
                    },
                },
            });
            statusApexChart.render();

            // Bar Chart — one colour per program instead of a single flat
            // fill, cycling through the palette if there are more programs
            // than colours.
            const programBarPalette = ['#14b8a6', '#2563eb', '#9333ea', '#db2777', '#d97706', '#16a34a', '#0284c7', '#e11d48'];
            const programLabels = {!! json_encode($waranByProgram->pluck('desc_program')) !!};

            new Chart(programEl, {
                type: 'bar',
                data: {
                    labels: programLabels,
                    datasets: [{
                        label: 'Jumlah',
                        data: {!! json_encode($waranByProgram->pluck('waran_count')) !!},
                        backgroundColor: programLabels.map((_, i) => programBarPalette[i % programBarPalette.length]),
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const value = context.parsed.y;
                                    const pct = total > 0 ? Math.round((value / total) * 100) : 0;
                                    return 'Jumlah: ' + value + ' (' + pct + '%)';
                                },
                            },
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        // The Chart.js/ApexCharts <script src> tags above load from a CDN
        // asynchronously. On a hard page load the browser happens to finish
        // that before DOMContentLoaded fires, but on Filament's SPA
        // navigation (livewire:navigated) there's no such guarantee — the
        // chart init code can run before the libraries actually exist yet,
        // silently doing nothing. Poll briefly until both are ready instead
        // of assuming they already are.
        function whenChartLibsReady(callback, attemptsLeft = 30) {
            if (typeof Chart !== 'undefined' && typeof ApexCharts !== 'undefined') {
                callback();
                return;
            }

            if (attemptsLeft <= 0) {
                return;
            }

            setTimeout(() => whenChartLibsReady(callback, attemptsLeft - 1), 100);
        }

        document.addEventListener('DOMContentLoaded', () => whenChartLibsReady(initDashboardCharts));
        document.addEventListener('livewire:navigated', () => whenChartLibsReady(initDashboardCharts));
    </script>

</x-filament-panels::page>
