@extends('layouts.admin')

@section('content')
<div class="container-fluid mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="fas fa-database text-success me-2"></i>Dataset Sub-SLS
            </h3>
            {{-- <p class="text-muted small">Kelola data mentah satuan lingkungan setempat untuk proses klastering</p> --}}
        </div>
        <div class="text-end">
            <div class="card bg-dark text-white shadow-sm border-0 px-4 py-2">
                <div class="small opacity-75 text-uppercase" style="font-size: 0.65rem;">Total Dataset</div>
                <div class="h4 fw-bold mb-0 text-success">{{ number_format($count) }}</div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end ">
        <button class="btn btn-success fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahDataset">
            <i class="fas fa-plus-circle me-2"></i>Tambah Data SLS
        </button>
    </div>

    <div class="card shadow-sm border-0 mb-4 p-3 bg-light">
        <form action="{{ route('master.dataset') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="small fw-bold mb-1">Kabupaten</label>
                <select name="kab" id="filter-kab" class="form-select form-select-sm border-0 shadow-sm">
                    <option value="">-- Semua --</option>
                    @foreach($kabupatens as $k)
                        <option value="{{ $k->kode_kab }}"
                            {{ request('kab') == $k->kode_kab ? 'selected' : '' }}>
                            {{ $k->nama_kab }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="small fw-bold mb-1">Kecamatan</label>
                <select name="kec" id="filter-kec" class="form-select form-select-sm border-0 shadow-sm">
                    <option value="">-- Semua --</option>
                    {{-- FIX: repopulate kecamatan dari server saat ada filter aktif --}}
                    @foreach($kecamatans as $kc)
                        <option value="{{ $kc->id }}"
                            {{ request('kec') == $kc->id ? 'selected' : '' }}>
                            {{ $kc->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="small fw-bold mb-1">Desa/Kel</label>
                <select name="desa" id="filter-desa" class="form-select form-select-sm border-0 shadow-sm">
                    <option value="">-- Semua --</option>
                    {{-- FIX: repopulate desa dari server saat ada filter aktif --}}
                    @foreach($desas as $ds)
                        <option value="{{ $ds->id }}"
                            {{ request('desa') == $ds->id ? 'selected' : '' }}>
                            {{ $ds->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="small fw-bold mb-1">Cari Nama SLS/Ketua</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-0 shadow-sm">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-0 shadow-sm"
                           placeholder="Ketik nama..." value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-success px-3 fw-bold shadow-sm w-100">
                    Filter Data
                </button>
                <a href="{{ route('master.dataset') }}" class="btn btn-sm btn-outline-secondary px-3 shadow-sm w-100">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-dark text-white shadow-sm" style="font-size: 0.7rem;">
                        <tr>
                            <th class="ps-4">ID SUBSLS</th>
                            <th>NAMA SLS</th>
                            <th>KETUA SLS</th>
                            <th class="text-center">KODE KAB</th>
                            <th class="text-center">KODE KEC</th>
                            <th class="text-center">KODE DESA</th>
                            <th class="text-center">KK</th>
                            <th class="text-center">MUATAN</th>
                            <th class="text-center pe-4">AKSI</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 0.8rem;">
                        @forelse($datasets as $d)
                        <tr class="border-bottom">
                            <td class="ps-4 fw-bold">{{ $d->id_subsls }}</td>
                            <td class="fw-bold">{{ $d->nama_sls }}</td>
                            <td>{{ $d->nama_ketua_sls }}</td>
                            <td class="text-center small">{{ $d->kode_kab }}</td>
                            <td class="text-center small">{{ $d->kode_kec }}</td>
                            <td class="text-center small">{{ $d->kode_desa }}</td>
                            <td class="text-center small">{{ $d->jumlah_kk }}</td>
                            <td class="text-center zmall">{{ $d->jumlah_muatan }}</td>
                            <td class="text-center pe-4">
                                <div class="btn-group shadow-sm">
                                    <button class="btn btn-sm btn-outline-warning border-0" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger border-0" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                                Data tidak ditemukan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-3 d-flex justify-content-center">
            {{ $datasets->appends(request()->input())->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const kabSelect  = document.getElementById('filter-kab');
    const kecSelect  = document.getElementById('filter-kec');
    const desaSelect = document.getElementById('filter-desa');

    // ── Helper: fetch JSON dan isi dropdown ──────────────────────────────
    function fetchOptions(url, selectEl, activeValue) {
        selectEl.innerHTML = '<option value="">Loading...</option>';
        selectEl.disabled  = true;

        fetch(url)
            .then(r => r.json())
            .then(data => {
                let html = '<option value="">-- Semua --</option>';
                data.forEach(item => {
                    const sel = String(item.id) === String(activeValue) ? 'selected' : '';
                    html += `<option value="${item.id}" ${sel}>${item.nama}</option>`;
                });
                selectEl.innerHTML = html;
                selectEl.disabled  = false;
            })
            .catch(() => {
                selectEl.innerHTML = '<option value="">Gagal memuat data</option>';
                selectEl.disabled  = false;
            });
    }

    // ── Event: Kabupaten berubah → muat ulang Kecamatan ──────────────────
    kabSelect.addEventListener('change', function () {
        desaSelect.innerHTML = '<option value="">-- Semua --</option>';
        desaSelect.disabled  = false;

        if (!this.value) {
            kecSelect.innerHTML = '<option value="">-- Semua --</option>';
            kecSelect.disabled  = false;
            return;
        }

        fetchOptions(`/get-kecamatan/${this.value}`, kecSelect, '');
    });

    // ── Event: Kecamatan berubah → muat ulang Desa ───────────────────────
    kecSelect.addEventListener('change', function () {
        if (!this.value) {
            desaSelect.innerHTML = '<option value="">-- Semua --</option>';
            desaSelect.disabled  = false;
            return;
        }

        fetchOptions(`/get-desa/${this.value}`, desaSelect, '');
    });

});
</script>

<style>
    .table th, .table td { white-space: nowrap; }
</style>
@endsection