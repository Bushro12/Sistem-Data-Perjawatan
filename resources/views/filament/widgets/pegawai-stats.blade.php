<x-filament-widgets::widget class="fi-wi-stats-overview col-span-full">
    <div class="sneat-stats-row">

        {{-- Total Pegawai --}}
        <div class="sneat-stat-card">
            <div class="sneat-stat-inner">
                <div>
                    <p class="sneat-stat-label">Jumlah Pegawai</p>
                    <h3 class="sneat-stat-value">{{ $totalPegawai }}</h3>
                </div>
                <div class="sneat-stat-icon sneat-stat-icon--default">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Pegawai Lengkap --}}
        <div class="sneat-stat-card">
            <div class="sneat-stat-inner">
                <div>
                    <p class="sneat-stat-label">Pegawai Lengkap</p>
                    <h3 class="sneat-stat-value">{{ $lengkap }}</h3>
                    <p class="sneat-stat-desc sneat-stat-desc--success">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 17a.75.75 0 0 1-.75-.75V5.612L5.29 9.77a.75.75 0 0 1-1.08-1.04l5.25-5.5a.75.75 0 0 1 1.08 0l5.25 5.5a.75.75 0 1 1-1.08 1.04l-3.96-4.158V16.25A.75.75 0 0 1 10 17Z" clip-rule="evenodd" />
                        </svg>
                        {{ $totalPegawai ? round(($lengkap / $totalPegawai) * 100, 1) : 0 }}% dari keseluruhan
                    </p>
                </div>
                <div class="sneat-stat-icon sneat-stat-icon--success">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Pegawai Tidak Lengkap --}}
        <div class="sneat-stat-card">
            <div class="sneat-stat-inner">
                <div>
                    <p class="sneat-stat-label">Pegawai Tidak Lengkap</p>
                    <h3 class="sneat-stat-value">{{ $tidakLengkap }}</h3>
                    <p class="sneat-stat-desc sneat-stat-desc--danger">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a.75.75 0 0 1 .75.75v10.638l3.96-4.158a.75.75 0 1 1 1.08 1.04l-5.25 5.5a.75.75 0 0 1-1.08 0l-5.25-5.5a.75.75 0 1 1 1.08-1.04l3.96 4.158V3.75A.75.75 0 0 1 10 3Z" clip-rule="evenodd" />
                        </svg>
                        {{ $totalPegawai ? round(($tidakLengkap / $totalPegawai) * 100, 1) : 0 }}% dari keseluruhan
                    </p>
                </div>
                <div class="sneat-stat-icon sneat-stat-icon--danger">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>
        </div>

    </div>
</x-filament-widgets::widget>
