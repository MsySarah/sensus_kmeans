@extends('layouts.admin')

@section('content')
<div class="container-fluid mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="fas fa-map-marked-alt text-success me-2"></i>Master Wilayah Sensus Ekonomi
            </h3>
            <p class="text-muted small">Kelola data referensi kode wilayah (Kabupaten, Kecamatan, Desa)</p>
        </div>
        <div class="text-end">
            <div class="card bg-dark text-white shadow-sm border-0 px-4 py-2">
                <div class="small opacity-75 text-uppercase" style="font-size: 0.65rem;">Total Wilayah</div>
                <div class="h4 fw-bold mb-0 text-success">{{ number_format($count) }}</div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button class="btn btn-success fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahWilayah">
            <i class="fas fa-plus-circle me-2"></i>Tambah Wilayah
        </button>
    </div>

    <div class="card shadow-sm border-0 mb-4 p-3 bg-light">
        <form action="{{ route('master.wilayah') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="small fw-bold mb-1">Kabupaten</label>
                <select name="kab" id="filter-kab" class="form-select form-select-sm border-0 shadow-sm">
                    <option value="">-- Semua --</option>
                    @foreach($kabupatens as $k)
                        <option value="{{ $k->kode_kab }}" {{ request('kab') == $k->kode_kab ? 'selected' : '' }}>{{ $k->nama_kab }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="small fw-bold mb-1">Kecamatan</label>
                <select name="kec" id="filter-kec" class="form-select form-select-sm border-0 shadow-sm">
                    <option value="">-- Semua --</option>
                    @foreach($kecamatans as $kec)
                        <option value="{{ $kec->id }}" {{ request('kec') == $kec->id ? 'selected' : '' }}>{{ $kec->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="small fw-bold mb-1">Desa/Kel</label>
                <select name="desa" id="filter-desa" class="form-select form-select-sm border-0 shadow-sm">
                    <option value="">-- Semua --</option>
                    @foreach($desas as $desa)
                        <option value="{{ $desa->id }}" {{ request('desa') == $desa->id ? 'selected' : '' }}>{{ $desa->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-success px-3 fw-bold shadow-sm w-100">Filter Data</button>
                <a href="{{ route('master.wilayah') }}" class="btn btn-sm btn-outline-secondary px-3 shadow-sm w-100">Reset</a>
            </div>
        </form>
    </div>

    <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-dark text-white shadow-sm" style="font-size: 0.7rem;">
                        <tr>
                            <th class="text-center">KODE KAB/KOTA</th>
                            <th class="text-center">KABUPATEN / KOTA</th>
                            <th class="text-center">KODE KEC</th>
                            <th>KECAMATAN</th>
                            <th class="text-center">KODE DESA</th>
                            <th>DESA / KELURAHAN</th>
                            <th class="text-center pe-4">AKSI</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 0.8rem;">
                        @forelse($wilayahs as $w)
                        <tr class="border-bottom">
                            <td class="text-center">{{ $w->kode_kab }}</td>
                            <td class="text-center">{{ $w->nama_kab }}</td>
                            <td class="text-center">{{ $w->kode_kec }}</td>
                            <td>{{ $w->nama_kec }}</td>
                            <td class="text-center">{{ $w->id_desa }}</td>
                            <td>{{ $w->nama_desa }}</td>
                            <td class="text-center pe-4">
                                <div class="btn-group shadow-sm">
                                    <button class="btn btn-sm btn-outline-warning border-0" title="Edit"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-outline-danger border-0" title="Hapus"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">Data wilayah tidak ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-3 d-flex justify-content-center">
            {{ $wilayahs->appends(request()->input())->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const kabSelect  = document.getElementById('filter-kab');
        const kecSelect  = document.getElementById('filter-kec');
        const desaSelect = document.getElementById('filter-desa');
     
        // ── Helper ────────────────────────────────────────────────────────────
        function loadDropdown(el, url, defaultText) {
            el.innerHTML = '<option value="">Memuat...</option>';
            el.disabled  = true;
            fetch(url)
                .then(r => r.json())
                .then(data => {
                    let html = `<option value="">-- ${defaultText} --</option>`;
                    data.forEach(d => {
                        html += `<option value="${d.id}">${d.nama}</option>`;
                    });
                    el.innerHTML = html;
                    el.disabled  = data.length === 0;
                })
                .catch(() => {
                    el.innerHTML = `<option value="">Gagal memuat</option>`;
                    el.disabled  = false;
                });
        }
     
        function resetDropdown(el, defaultText) {
            el.innerHTML = `<option value="">-- ${defaultText} --</option>`;
            el.disabled  = false;
        }
     
        // ── Kabupaten → Kecamatan ────────────────────────────────────────────
        kabSelect.addEventListener('change', function () {
            resetDropdown(desaSelect, 'Semua');
            if (!this.value) {
                resetDropdown(kecSelect, 'Semua');
                return;
            }
            // FIX: endpoint yang benar /get-kecamatan/ bukan /api/kecamatan/
            loadDropdown(kecSelect, `/get-kecamatan/${this.value}`, 'Semua');
        });
     
        // ── Kecamatan → Desa ─────────────────────────────────────────────────
        kecSelect.addEventListener('change', function () {
            if (!this.value) {
                resetDropdown(desaSelect, 'Semua');
                return;
            }
            // FIX: endpoint yang benar /get-desa/ bukan /api/desa/
            loadDropdown(desaSelect, `/get-desa/${this.value}`, 'Semua');
        });
    });
    </script>
    

<style>
    .table th { white-space: nowrap; }
    .table td { white-space: nowrap; }
    .btn-group .btn:hover { background-color: #f8f9fa; }
</style>
@endsection