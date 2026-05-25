<x-filament-panels::page>

    {{-- Stats Cards --}}
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:16px;">
        <div style="background:#eff6ff; border-radius:12px; padding:20px;">
            <p style="font-size:13px; color:#3b82f6; font-weight:500; margin:0 0 4px;">Jumlah Waran</p>
            <p style="font-size:28px; font-weight:700; color:#1d4ed8; margin:0;">{{ $totalWaran }}</p>
        </div>
        <div style="background:#f0fdf4; border-radius:12px; padding:20px;">
            <p style="font-size:13px; color:#16a34a; font-weight:500; margin:0 0 4px;">Waran Lebih</p>
            <p style="font-size:28px; font-weight:700; color:#15803d; margin:0;">{{ $totalLebih }}</p>
        </div>
        <div style="background:#fef2f2; border-radius:12px; padding:20px;">
            <p style="font-size:13px; color:#dc2626; font-weight:500; margin:0 0 4px;">Waran Kurang</p>
            <p style="font-size:28px; font-weight:700; color:#b91c1c; margin:0;">{{ $totalKurang }}</p>
        </div>
        <div style="background:#f0f9ff; border-radius:12px; padding:20px;">
            <p style="font-size:13px; color:#0284c7; font-weight:500; margin:0 0 4px;">Waran Seimbang</p>
            <p style="font-size:28px; font-weight:700; color:#0369a1; margin:0;">{{ $totalSeimbang }}</p>
        </div>
    </div>

    {{-- Charts Row --}}
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">

        {{-- Donut Chart --}}
        <x-filament::section heading="Status Waran">
            <div style="display:flex; flex-direction:column; align-items:center; gap:16px;">
                <div style="position:relative; width:200px; height:200px;">
                    <canvas id="statusChart" width="200" height="200"></canvas>
                    <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); text-align:center;">
                        <p style="font-size:24px; font-weight:700; margin:0;">{{ $totalWaran }}</p>
                        <p style="font-size:12px; color:#6b7280; margin:0;">Jumlah</p>
                    </div>
                </div>
                <div style="display:flex; gap:16px;">
                    <div style="display:flex; align-items:center; gap:6px;">
                        <div style="width:12px; height:12px; border-radius:50%; background:#16a34a;"></div>
                        <span style="font-size:12px;">Lebih ({{ $totalLebih }})</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:6px;">
                        <div style="width:12px; height:12px; border-radius:50%; background:#dc2626;"></div>
                        <span style="font-size:12px;">Kurang ({{ $totalKurang }})</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:6px;">
                        <div style="width:12px; height:12px; border-radius:50%; background:#0284c7;"></div>
                        <span style="font-size:12px;">Seimbang ({{ $totalSeimbang }})</span>
                    </div>
                </div>
            </div>
        </x-filament::section>

        {{-- Bar Chart --}}
        <x-filament::section heading="Waran Mengikut Program">
            <canvas id="programChart" style="max-height:220px;"></canvas>
        </x-filament::section>

    </div>

    {{-- Recent Waran + System Stats --}}
    <div style="display:grid; grid-template-columns:2fr 1fr; gap:16px;">

        {{-- Recent Waran --}}
        <x-filament::section heading="Waran Terbaharu">
            <table style="width:100%; border-collapse:collapse; font-size:13px;">
                <thead>
                    <tr style="border-bottom:1px solid #e5e7eb;">
                        <th style="text-align:left; padding:8px 0; color:#6b7280; font-weight:500;">No. Waran</th>
                        <th style="text-align:left; padding:8px 0; color:#6b7280; font-weight:500;">Jenis</th>
                        <th style="text-align:center; padding:8px 0; color:#6b7280; font-weight:500;">J</th>
                        <th style="text-align:center; padding:8px 0; color:#6b7280; font-weight:500;">I</th>
                        <th style="text-align:center; padding:8px 0; color:#6b7280; font-weight:500;">K</th>
                        <th style="text-align:left; padding:8px 0; color:#6b7280; font-weight:500;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentWarans as $waran)
                    @php $status = $waran->status_jik; @endphp
                    <tr style="border-bottom:1px solid #f3f4f6;">
                        <td style="padding:10px 0; color:#6366f1; font-weight:600;">{{ $waran->no_waran }}</td>
                        <td style="padding:10px 0;">
                            @if($waran->jenis === 'tambah')
                                <x-filament::badge color="success">Tambah</x-filament::badge>
                            @else
                                <x-filament::badge color="warning">Tolak</x-filament::badge>
                            @endif
                        </td>
                        <td style="padding:10px 0; text-align:center; font-weight:600;">{{ $waran->jik }}</td>
                        <td style="padding:10px 0; text-align:center; font-weight:600; color:#16a34a;">{{ $waran->isi_count }}</td>
                        <td style="padding:10px 0; text-align:center; font-weight:600; color:{{ $waran->kosong_count < 0 ? '#dc2626' : '#d97706' }};">{{ $waran->kosong_count }}</td>
                        <td style="padding:10px 0;">
                            @if($status === 'Lebih')
                                <x-filament::badge color="success">{{ $status }}</x-filament::badge>
                            @elseif($status === 'Kurang')
                                <x-filament::badge color="danger">{{ $status }}</x-filament::badge>
                            @else
                                <x-filament::badge color="info">{{ $status }}</x-filament::badge>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" style="padding:16px 0; text-align:center; color:#6b7280;">Tiada waran ditemui.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-filament::section>

        {{-- System Stats --}}
        <x-filament::section heading="Ringkasan Sistem">
            <div style="display:flex; flex-direction:column; gap:16px;">
                <div style="display:flex; justify-content:space-between; align-items:center; padding:12px; background:#f9fafb; border-radius:8px;">
                    <span style="font-size:13px; color:#6b7280;">Jumlah PTJ</span>
                    <span style="font-size:20px; font-weight:700; color:#1d4ed8;">{{ $totalPtj }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; padding:12px; background:#f9fafb; border-radius:8px;">
                    <span style="font-size:13px; color:#6b7280;">Jumlah Pegawai</span>
                    <span style="font-size:20px; font-weight:700; color:#15803d;">{{ $totalPegawai }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; padding:12px; background:#f9fafb; border-radius:8px;">
                    <span style="font-size:13px; color:#6b7280;">Waran Aktif</span>
                    <span style="font-size:20px; font-weight:700; color:#6366f1;">{{ $totalWaran }}</span>
                </div>
            </div>
        </x-filament::section>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // Donut Chart
            new Chart(document.getElementById('statusChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Lebih', 'Kurang', 'Seimbang'],
                    datasets: [{
                        data: [{{ $totalLebih }}, {{ $totalKurang }}, {{ $totalSeimbang }}],
                        backgroundColor: ['#16a34a', '#dc2626', '#0284c7'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: false,
                    cutout: '70%',
                    plugins: { legend: { display: false }, tooltip: { enabled: true } }
                }
            });

            // Bar Chart
            new Chart(document.getElementById('programChart'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($waranByProgram->pluck('desc_program')) !!},
                    datasets: [{
                        label: 'Jumlah',
                        data: {!! json_encode($waranByProgram->pluck('waran_count')) !!},
                        backgroundColor: '#6366f1',
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } },
                        x: { grid: { display: false } }
                    }
                }
            });

        });
    </script>

</x-filament-panels::page>
