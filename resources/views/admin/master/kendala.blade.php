@extends('layouts.admin')

@section('content')
<div class="container-fluid mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="fas fa-balance-scale text-danger me-2"></i>Master Bobot Kendala
            </h3>
            <p class="text-muted small mb-0">Parameter nilai klasifikasi kendala untuk algoritma K-Means</p>
        </div>
        <div class="text-end">
            <div class="card bg-dark text-white shadow-sm border-0 px-4 py-2">
                <div class="small opacity-75 text-uppercase" style="font-size: 0.65rem;">Total Parameter</div>
                <div class="h4 fw-bold mb-0 text-danger">{{ $count }}</div>
            </div>
        </div>
    </div>

    {{-- <div class="alert alert-warning border-start border-warning border-4 shadow-sm bg-white" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-lock fa-2x text-warning me-3"></i>
            <div>
                <h6 class="fw-bold mb-1 text-dark">Data Parameter Dikunci (Read-Only)</h6>
                <p class="mb-0 small text-muted">
                    Nilai bobot pada tabel ini digunakan sebagai titik tumpu (centroid awal) perhitungan normalisasi algoritma <strong>K-Means Clustering</strong>. Perubahan nilai secara manual dinonaktifkan untuk menjaga stabilitas dan validitas hasil klastering wilayah.
                </p>
            </div>
        </div>
    </div> --}}

    <div class="card shadow-sm border-0 rounded-3 overflow-hidden mt-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="m-0 fw-bold text-dark">
                <i class="fas fa-list-ul text-danger me-2"></i>Daftar Parameter Bobot
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark shadow-sm" style="font-size: 0.75rem;">
                        <tr>
                            <th class="text-center" style="width: 15%;">NILAI BOBOT</th>
                            <th class="text-center" style="width: 25%;">KATEGORI KENDALA</th>
                            <th class="ps-4">DESKRIPSI & INDIKATOR LAPANGAN</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 0.85rem;">
                        @forelse($kendalas as $k)
                        <tr class="border-bottom">
                            <td class="text-center">
                                <h4 class="fw-bold text-danger mb-0">{{ $k->bobot ?? $k->nilai ?? $k->id }}</h4>
                            </td>
                            <td class="text-center">
                                @php
                                    // Bikin badge warna sesuai kategori
                                    $nama = $k->nama_kendala ?? $k->kategori ?? 'Tidak Diketahui';
                                    $badge = 'bg-secondary';
                                    if(strtolower($nama) == 'ringan') $badge = 'bg-info text-dark';
                                    if(strtolower($nama) == 'sedang') $badge = 'bg-primary';
                                    if(strtolower($nama) == 'berat') $badge = 'bg-dark';
                                @endphp
                                <span class="badge {{ $badge }} px-4 py-2 shadow-sm" style="font-size: 0.75rem;">
                                    {{ strtoupper($nama) }}
                                </span>
                            </td>
                            <td class="text-muted ps-4 pe-3" style="line-height: 1.6;">
                                {{ $k->deskripsi ?? $k->keterangan ?? 'Panduan lapangan: Responden sibuk, menolak, atau kondisi geografis yang menghambat pendataan.' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-5 text-muted">
                                <i class="fas fa-database fa-2x mb-3 opacity-50"></i>
                                <p>Data master kendala belum diisi (Tabel kosong).</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .table th { white-space: nowrap; letter-spacing: 0.5px; }
    .table td { vertical-align: middle; }
</style>
@endsection