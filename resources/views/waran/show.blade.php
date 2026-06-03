@extends('layouts.app')
@section('title', 'Butiran Waran')

@push('styles')
<style>
    .badge-aktif { background-color: #198754; color: #fff; }
    .badge-removed { background-color: #dc3545; color: #fff; }
</style>
@endpush

@section('content')

{{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('waran.index') }}" class="text-muted text-decoration-none">
            <i class="bx bx-arrow-back me-1"></i> Kembali
        </a>
        <h4 class="fw-bold mb-0 mt-1">{{ $waran->no_waran }}</h4>
        <small class="text-muted">Butiran Waran</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('waran.edit', $waran) }}" class="btn btn-warning btn-sm">
            <i class="bx bx-edit me-1"></i> Edit Waran
        </a>
    </div>
</div>

{{-- Waran Info Cards --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-md-3">
        <div class="card text-center h-100">
            <div class="card-body py-3">
                <small class="text-muted d-block mb-1">No. Waran</small>
                <h5 class="fw-bold mb-0">{{ $waran->no_waran }}</h5>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="card text-center h-100">
            <div class="card-body py-3">
                <small class="text-muted d-block mb-1">Jenis</small>
                <span class="badge {{ $waran->jenis === 'tambah' ? 'bg-label-success' : 'bg-label-warning' }} fs-6">
                    {{ ucfirst($waran->jenis) }}
                </span>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="card text-center h-100">
            <div class="card-body py-3">
                <small class="text-muted d-block mb-1">Jumlah Jawatan (JIK)</small>
                <h5 class="fw-bold mb-0">{{ $waran->jik }}</h5>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="card text-center h-100">
            <div class="card-body py-3">
                <small class="text-muted d-block mb-1">Status</small>
                @php
                    $badgeClass = match($waran->status_jik) {
                        'Lebih' => 'bg-label-success',
                        'Kurang' => 'bg-label-danger',
                        'Seimbang' => 'bg-label-info',
                        default => 'bg-label-secondary',
                    };
                @endphp
                <span class="badge {{ $badgeClass }} fs-6">{{ $waran->status_jik }}</span>
            </div>
        </div>
    </div>
</div>

@if($waran->catatan)
<div class="alert alert-info mb-4">
    <i class="bx bx-info-circle me-1"></i> <strong>Catatan:</strong> {{ $waran->catatan }}
</div>
@endif

{{-- Waran Jawatan Table --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Senarai Jawatan</h5>
        @if($waran->jenis === 'tambah')
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahJawatan">
            <i class="bx bx-plus me-1"></i> Tambah Jawatan
        </button>
        @endif
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Butiran</th>
                    <th>Aktiviti</th>
                    <th>Jawatan / Gred</th>
                    <th>PTJ</th>
                    <th>Pegawai</th>
                    <th>Status</th>
                    <th class="text-center">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($waran->waranJawatan as $jaw)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $jaw->butiran ?? '-' }}</td>
                    <td>
                        @if($jaw->aktiviti)
                            <small>{{ $jaw->aktiviti->no_aktivit }} - {{ $jaw->aktiviti->nama_aktiviti }}</small>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        <small>{{ $jaw->jawatan_list ?: '-' }}</small><br>
                        <small class="text-muted">{{ $jaw->gred_list ?: '-' }}</small>
                    </td>
                    <td>
                        <small>{{ $jaw->ptj->nama_ptj ?? '-' }}</small>
                    </td>
                    <td>
                        <small>{{ $jaw->pegawai->nama ?? '-' }}</small>
                    </td>
                    <td>
                        <span class="badge {{ $jaw->status === 'removed' ? 'bg-label-danger' : 'bg-label-success' }}">
                            {{ $jaw->status === 'removed' ? 'Dibuang' : 'Aktif' }}
                        </span>
                    </td>
                    <td class="text-center">
                        @if($jaw->status !== 'removed')
                        <div class="d-flex justify-content-center gap-1">
                            <button class="btn btn-sm btn-icon btn-text-warning"
                                onclick="openEditModal({{ $jaw->id }})"
                                title="Edit">
                                <i class="bx bx-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-icon btn-text-danger"
                                onclick="confirmDelete('{{ route('waran-jawatan.destroy', $jaw) }}', 'Jawatan ini')"
                                title="Padam">
                                <i class="bx bx-trash"></i>
                            </button>
                        </div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">
                        Tiada jawatan ditambah lagi.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Tambah Jawatan --}}
<div class="modal fade" id="modalTambahJawatan" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('waran-jawatan.store', $waran) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Jawatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('waran._jawatan-form', ['jaw' => null, 'programs' => $programs, 'ptjs' => $ptjs])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Edit Jawatan --}}
<div class="modal fade" id="modalEditJawatan" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Jawatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="editModalBody">
                <div class="text-center py-4"><i class="bx bx-loader-alt bx-spin"></i> Memuatkan...</div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openEditModal(id) {
    const modal = new bootstrap.Modal(document.getElementById('modalEditJawatan'));
    modal.show();
    fetch(`/waran-jawatan/${id}/edit`)
        .then(r => r.text())
        .then(html => {
            document.getElementById('editModalBody').innerHTML = html;
            initCascading();
        });
}

function initCascading() {
    // PTJ → Bahagian
    const ptjSelect = document.querySelector('[name="ptj_id"]');
    if (ptjSelect) {
        ptjSelect.addEventListener('change', function() {
            fetchOptions('/api/bahagian?ptj_id=' + this.value, 'bahagian_id');
        });
    }
}
</script>
@endpush