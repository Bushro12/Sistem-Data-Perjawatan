<x-filament-widgets::widget class="fi-wi-stats-overview col-span-full">
    <div class="sneat-stats-row">

        {{-- Jumlah Penempatan --}}
        <div class="sneat-stat-card">
            <div class="sneat-stat-inner">
                <div>
                    <p class="sneat-stat-label">Jumlah Penempatan</p>
                    <h3 class="sneat-stat-value">{{ $currentTotal }}</h3>
                    <p class="sneat-stat-desc">
                        @if($yoyChangePct === null)
                            Tiada data tahun {{ $previousYear }}
                        @else
                            {{ $yoyChangePct }}% berbanding tahun {{ $previousYear }}
                        @endif
                    </p>
                </div>
                <div class="sneat-stat-icon sneat-stat-icon--default">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Pengisian --}}
        <div class="sneat-stat-card">
            <div class="sneat-stat-inner">
                <div>
                    <p class="sneat-stat-label">Pengisian</p>
                    <h3 class="sneat-stat-value">{{ $pengisian }}</h3>
                    <p class="sneat-stat-desc sneat-stat-desc--success">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 17a.75.75 0 0 1-.75-.75V5.612L5.29 9.77a.75.75 0 0 1-1.08-1.04l5.25-5.5a.75.75 0 0 1 1.08 0l5.25 5.5a.75.75 0 1 1-1.08 1.04l-3.96-4.158V16.25A.75.75 0 0 1 10 17Z" clip-rule="evenodd" />
                        </svg>
                        {{ $pengisianPct }}% dari keseluruhan
                    </p>
                </div>
                <div class="sneat-stat-icon sneat-stat-icon--success">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Kekosongan --}}
        <div class="sneat-stat-card">
            <div class="sneat-stat-inner">
                <div>
                    <p class="sneat-stat-label">Kekosongan</p>
                    <h3 class="sneat-stat-value">{{ $kekosongan }}</h3>
                    <p class="sneat-stat-desc sneat-stat-desc--danger">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a.75.75 0 0 1 .75.75v10.638l3.96-4.158a.75.75 0 1 1 1.08 1.04l-5.25 5.5a.75.75 0 0 1-1.08 0l-5.25-5.5a.75.75 0 1 1 1.08-1.04l3.96 4.158V3.75A.75.75 0 0 1 10 3Z" clip-rule="evenodd" />
                        </svg>
                        {{ $kekosonganPct }}% dari keseluruhan
                    </p>
                </div>
                <div class="sneat-stat-icon sneat-stat-icon--danger">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                </div>
            </div>
        </div>

    </div>
</x-filament-widgets::widget>
