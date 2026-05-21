{{-- @foreach ($warans as $waran)
{{ $waran->no_waran }}

@foreach ($waran->waranJawatans as $jawatan)
{{ $jawatan->butiran }}
{{ $jawatan->aktiviti }}
@endforeach
@endforeach --}}
@php $i = 0; @endphp
<div class="overflow-x-auto rounded-xl border border-gray-200">
    <table class="w-full text-sm">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-4 py-3 text-left">Bil</th>
                <th class="px-4 py-3 text-left">Waran</th>
                <th class="px-4 py-3 text-left">Butiran</th>
                <th class="px-4 py-3 text-left">Aktiviti</th>
                <th class="px-4 py-3 text-left">PTJ</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3 text-left">Tindakan</th>

            </tr>
        </thead>

        <tbody>
            @foreach ($warans as $waran)
            {{-- PARENT ROW --}}
            <tr class="border-t">
                <td class="px-4 py-3"> {{ ++$i }}</td>

                <td class="px-4 py-3 font-semibold">
                    {{ $waran->no_waran }}
                </td>

                <td class="px-4 py-3">
                    <ul class="list-disc pl-5">
                        @foreach ($waran->waranJawatans as $jawatan)
                        {{ $jawatan->butiran }}
                        @endforeach
                    </ul>
                </td>

                {{-- <td class="px-4 py-3">
                    {{ $waran->children->count() > 0 ? '+' . $waran->children->count() : '' }}
                </td> --}}

                <td class="px-4 py-3">
                    <ul class="list-disc pl-5">
                        @foreach ($waran->waranJawatans as $jawatan)
                        {{ $jawatan->aktiviti?->no_aktivit }} - {{ $jawatan->aktiviti?->nama_aktiviti }}
                        @endforeach
                    </ul>
                </td>

                <td class="px-4 py-3">
                    @php
                    $grouped = $waran->waranJawatans->groupBy(fn($j) => $j->ptj?->nama_ptj);
                    @endphp

                    @foreach ($grouped as $ptjName => $items)
                    <div class="mb-2">
                        <div class="font-semibold">
                            {{ $ptjName ?? 'PTJ Tidak Diketahui' }}
                        </div>

                        <ol class="list-decimal pl-6">
                            @foreach ($items as $jawatan)
                            <li>
                                {{ $jawatan->pegawai?->nama ?? 'Kosong' }}
                            </li>
                            @endforeach
                        </ol>
                    </div>
                    @endforeach
                </td>
                <td class="px-4 py-3">
                    @php
                    $total = $waran->waranJawatans->count();
                    $filled = $waran->waranJawatans->whereNotNull('pegawai_id')->count();
                    @endphp

                    @if ($total > 0 && $filled === $total)
                    <span class="text-green-600 font-semibold">Seimbang</span>
                    @else
                    <span class="text-red-600 font-semibold">Kurang</span>
                    @endif
                </td>
            </tr>

            {{-- CHILD ROWS --}}
            @foreach ($waran->children as $child)
            <tr class="border-t">
                <td class="px-4 py-3">
                    {{ ++$i }}
                </td>

                <td class="px-4 py-3 pl-8">
                    ↳ {{ $child->no_waran }}
                </td>

                <td class="px-4 py-3">
                    <ul class="list-disc pl-5">
                        @foreach ($child->waranJawatans as $jawatan)
                        {{ $jawatan->butiran }}
                        @endforeach
                    </ul>
                </td>

                <td class="px-4 py-3">
                    <ul class="list-disc pl-5">
                        @foreach ($child->waranJawatans as $jawatan)
                        {{ $jawatan->aktiviti?->no_aktivit }} - {{ $jawatan->aktiviti?->nama_aktiviti }}
                        @endforeach
                    </ul>
                </td>


                <td class="px-4 py-3">
                    @php
                    $grouped = $child->waranJawatans->groupBy(fn($j) => $j->ptj?->nama_ptj);
                    @endphp

                    @foreach ($grouped as $ptjName => $items)
                    <div class="mb-2">
                        <div class="font-semibold">
                            {{ $ptjName ?? 'PTJ Tidak Diketahui' }}
                        </div>

                        <ol class="list-decimal pl-6">
                            @foreach ($items as $jawatan)
                            <li>
                                {{ $jawatan->pegawai?->nama ?? 'Kosong' }}
                            </li>
                            @endforeach
                        </ol>
                    </div>
                    @endforeach
                </td>
                <td class="px-4 py-3">
                    @php
                    $total = $child->waranJawatans->count();
                    $filled = $child->waranJawatans->whereNotNull('pegawai_id')->count();
                    @endphp

                    @if ($total > 0 && $filled === $total)
                    <span class="text-green-600 font-semibold">Seimbang</span>
                    @else
                    <span class="text-red-600 font-semibold">Kurang</span>
                    @endif
                </td>
                <td>

                </td>
            </tr>
            @endforeach
            @endforeach
        </tbody>
    </table>
</div>
