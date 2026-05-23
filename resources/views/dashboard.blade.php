@extends('layouts.admin')

@section('content')
<div class="container-fluid mb-5">
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i> <strong>Mantap!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-alert="close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> <strong>Waduh!</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <h3 class="fw-bold">Ringkasan Progres Lapangan</h3>
            <div class="d-flex gap-3">
                <p class="text-muted small">
                    <i class="fas fa-database text-info me-1"></i> Data Masuk: <strong>{{ $waktuLaporan }} WIB</strong>
                </p>
                <p class="text-muted small">
                    <i class="fas fa-robot text-primary me-1"></i> Update Klaster: <strong>{{ $waktuAI }} WIB</strong>
                </p>
            </div>
        </div>
        
        <div class="col-md-4 d-flex flex-column align-items-end gap-2">
            <div class="bg-dark rounded-3 py-2 px-3 text-center shadow-sm" style="border: 1px solid #343a40; min-width: 140px;">
                <div class="fw-bold" style="color: #adb5bd; font-size: 0.65rem; letter-spacing: 0.5px;">TOTAL WILAYAH</div>
                <div class="fw-bold text-success mb-0" style="font-size: 1.5rem; line-height: 1.2;">{{ number_format($wilayahs->total()) }}</div>
            </div>
            
            <a href="/tes-kmeans" class="btn btn-primary shadow-sm btn-sm" style="min-width: 140px;">
                <i class="fas fa-robot me-1"></i> Klasterisasi Wilayah
            </a>
        </div>
    </div>

    <div class="row g-3 mb-3 text-center">
        <div class="col-6 col-md-3">
            <div class="card p-3 h-100 d-flex flex-column {{ request('status_progres') ? 'active-filter' : '' }}">
                <h6 class="text-muted small fw-bold mt-2">TOTAL PROGRES MUATAN</h6>
                <div class="my-auto">
                    <div id="chartProgres"></div>
                    <div class="fw-bold text-primary small">{{ number_format($totalSelesai) }} / {{ number_format($totalMuatan) }}</div>
                </div>
                
                <div class="mt-3 pt-3 border-top d-flex justify-content-center gap-2">
                    <a href="{{ request()->fullUrlWithQuery(['status_progres' => '100', 'cluster' => null, 'bobot' => null, 'belum_verifikasi' => null]) }}" 
                       class="badge bg-success text-decoration-none py-2 px-2 shadow-sm {{ request('status_progres') == '100' ? 'border border-2 border-dark' : '' }}" style="font-size: 0.65rem;">
                        <i class="fas fa-check-double"></i> Tuntas 100%
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['status_progres' => '0', 'cluster' => null, 'bobot' => null, 'belum_verifikasi' => null]) }}" 
                       class="badge bg-secondary text-decoration-none py-2 px-2 shadow-sm {{ request('status_progres') == '0' ? 'border border-2 border-dark' : '' }}" style="font-size: 0.65rem;">
                        <i class="fas fa-hourglass-start"></i> 0% (Belum)
                    </a>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card bg-success text-white p-3 h-100 d-flex flex-column {{ request('cluster') == 'Lancar' ? 'active-filter' : '' }}">
                <h6 class="text-white text-opacity-75 small fw-bold mt-2">WILAYAH LANCAR</h6>
                <div class="my-auto">
                    <h1 class="fw-bold mb-0" style="font-size: 3rem;">{{ $totalLancar }}</h1>
                </div>
                <div class="mt-3 pt-3 border-top border-white border-opacity-25 d-flex justify-content-center">
                    <a href="{{ request()->fullUrlWithQuery(['cluster' => 'Lancar', 'status_progres' => null, 'bobot' => null, 'belum_verifikasi' => null]) }}" 
                       class="badge bg-white text-success text-decoration-none py-2 px-3 shadow-sm {{ request('cluster') == 'Lancar' ? 'border border-2 border-dark' : '' }}" style="font-size: 0.7rem; min-width: 100px;">
                        <i class="fas fa-filter"></i> Lihat Semua
                    </a>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card bg-warning text-dark p-3 h-100 d-flex flex-column {{ request('cluster') == 'Waspada' ? 'active-filter' : '' }}">
                <h6 class="text-dark text-opacity-75 small fw-bold mt-2">WILAYAH WASPADA</h6>
                <div class="my-auto">
                    <h1 class="fw-bold mb-0" style="font-size: 3rem;">{{ $totalWaspada }}</h1>
                </div>
                <div class="mt-3 pt-3 border-top border-dark border-opacity-10 d-flex justify-content-center">
                    <a href="{{ request()->fullUrlWithQuery(['cluster' => 'Waspada', 'status_progres' => null, 'bobot' => null, 'belum_verifikasi' => null]) }}" 
                       class="badge bg-dark text-white text-decoration-none py-2 px-3 shadow-sm {{ request('cluster') == 'Waspada' ? 'border border-2 border-warning' : '' }}" style="font-size: 0.7rem; min-width: 100px;">
                        <i class="fas fa-filter"></i> Lihat Semua
                    </a>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card bg-danger text-white p-3 h-100 d-flex flex-column {{ request('cluster') == 'Terkendala' && !request('bobot') ? 'active-filter' : '' }}">
                <h6 class="text-white text-opacity-75 small fw-bold mt-2">WILAYAH TERKENDALA</h6>
                <div class="my-auto">
                    <h1 class="fw-bold mb-0" style="font-size: 3rem;">{{ $totalTerkendala }}</h1>
                </div>
                <div class="mt-3 pt-3 border-top border-white border-opacity-25 d-flex justify-content-center">
                    <a href="{{ request()->fullUrlWithQuery(['cluster' => 'Terkendala', 'status_progres' => null, 'bobot' => null, 'belum_verifikasi' => null]) }}" 
                       class="badge bg-white text-danger text-decoration-none py-2 px-3 shadow-sm {{ request('cluster') == 'Terkendala' && !request('bobot') ? 'border border-2 border-dark' : '' }}" style="font-size: 0.7rem; min-width: 100px;">
                        <i class="fas fa-filter"></i> Lihat Semua
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex align-items-center flex-wrap gap-2 mb-4 bg-white p-2 rounded-3 shadow-sm border" style="min-height: 55px;">
        <span class="small fw-bold text-muted ms-2 me-2 align-self-center"><i class="fas fa-exclamation-circle me-1"></i>Kategori Kendala:</span>
        <a href="{{ request()->fullUrlWithQuery(['bobot' => '0', 'cluster' => null, 'status_progres' => null, 'belum_verifikasi' => null]) }}" 
           class="badge bg-success text-white text-decoration-none py-2 px-3 shadow-sm align-self-center {{ request()->input('bobot') === '0' && !request('belum_verifikasi') ? 'border border-2 border-dark' : '' }}">
            <i class="fas fa-check-circle me-1"></i> Tidak Ada Kendala
        </a>
        <a href="{{ request()->fullUrlWithQuery(['bobot' => '1', 'cluster' => null, 'status_progres' => null, 'belum_verifikasi' => null]) }}" 
           class="badge bg-info text-dark text-decoration-none py-2 px-3 shadow-sm align-self-center {{ request('bobot') == '1' ? 'border border-2 border-dark' : '' }}">
            Kendala Ringan ({{ $countRingan ?? 0 }})
        </a>
        <a href="{{ request()->fullUrlWithQuery(['bobot' => '2', 'cluster' => null, 'status_progres' => null, 'belum_verifikasi' => null]) }}" 
           class="badge bg-primary text-white text-decoration-none py-2 px-3 shadow-sm align-self-center {{ request('bobot') == '2' ? 'border border-2 border-dark' : '' }}">
            Kendala Sedang ({{ $countSedang ?? 0 }})
        </a>
        <a href="{{ request()->fullUrlWithQuery(['bobot' => '3', 'cluster' => null, 'status_progres' => null, 'belum_verifikasi' => null]) }}" 
           class="badge bg-dark text-white text-decoration-none py-2 px-3 shadow-sm align-self-center {{ request('bobot') == '3' ? 'border border-2 border-secondary' : '' }}">
            Kendala Berat ({{ $countBerat ?? 0 }})
        </a>

        @if(isset($countBelumVerifikasi) && $countBelumVerifikasi > 0)
            <a href="{{ request()->fullUrlWithQuery(['belum_verifikasi' => '1', 'bobot' => null, 'cluster' => null, 'status_progres' => null]) }}" 
               class="badge text-decoration-none py-2 px-3 shadow-sm align-self-center animate__animated animate__pulse animate__infinite {{ request('belum_verifikasi') == '1' ? 'border border-3 border-dark shadow-lg' : '' }}" 
               style="background: linear-gradient(45deg, #ff4e50, #f9d423); color: #fff; font-size: 0.85rem; font-weight: 800; border-radius: 6px; letter-spacing: 0.3px;">
                <i class="fas fa-gavel me-1"></i> ⚠️ BUTUH VERIFIKASI ({{ $countBelumVerifikasi }})
            </a>
        @endif
    </div>

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body p-3 bg-light rounded-3">
            <form action="/dashboard" method="GET" class="row g-2">
                <input type="hidden" name="cluster" value="{{ request('cluster') }}">
                <input type="hidden" name="status_progres" value="{{ request('status_progres') }}">
                <input type="hidden" name="bobot" value="{{ request('bobot') }}">
                <input type="hidden" name="belum_verifikasi" value="{{ request('belum_verifikasi') }}">

                <div class="col-md-3">
                    <label class="small fw-bold text-muted">Kabupaten/Kota</label>
                    <select name="kab" class="form-select form-select-sm border-0 shadow-sm" onchange="this.form.submit()">
                        <option value="">-- Semua Kabupaten --</option>
                        @foreach($listKab as $kab)
                            <option value="{{ $kab->kode_kab }}" {{ request('kab') == $kab->kode_kab ? 'selected' : '' }}>
                                {{ $kab->nama_kab }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="small fw-bold text-muted">Kecamatan</label>
                    <select name="kec" class="form-select form-select-sm border-0 shadow-sm" onchange="this.form.submit()" {{ $listKec->isEmpty() ? 'disabled' : '' }}>
                        <option value="">-- Semua Kecamatan --</option>
                        @foreach($listKec as $kec)
                            <option value="{{ $kec->kode_kec }}" {{ request('kec') == $kec->kode_kec ? 'selected' : '' }}>
                                {{ $kec->nama_kec }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="small fw-bold text-muted">Cari SLS / Desa</label>
                    <input type="text" name="search" class="form-control form-control-sm border-0 shadow-sm" placeholder="Ketik nama SLS..." value="{{ request('search') }}" maxlength="20" autocomplete="off">
                </div>
                <div class="col-md-2 d-flex align-items-end gap-1">
                    <button type="submit" class="btn btn-dark btn-sm w-100 shadow-sm">Cari</button>
                    <a href="/dashboard" class="btn btn-outline-secondary btn-sm shadow-sm" title="Reset Semua Filter"><i class="fas fa-undo"></i></a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="table-container shadow-sm bg-white p-4 rounded-3 border-0">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h5 class="fw-bold mb-0"><i class="fas fa-map-marked-alt text-primary"></i> Detail Progres Wilayah</h5>
                @if(request()->hasAny(['cluster', 'status_progres', 'kab', 'kec', 'search', 'bobot']) || request('belum_verifikasi'))
                    <small class="text-muted"><i class="fas fa-info-circle"></i> Sedang menampilkan hasil filter.</small>
                @endif
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                @if(request('cluster'))
                    <span class="badge bg-light text-dark border px-2 py-1 shadow-sm"><i class="fas fa-filter text-secondary"></i> Status: <strong>{{ request('cluster') }}</strong></span>
                @endif
                @if(request()->has('bobot') && request('bobot') != '')
                    <span class="badge bg-light text-dark border px-2 py-1 shadow-sm">
                        <i class="fas fa-filter text-secondary"></i> Bobot: 
                        <strong>
                            @if(request('bobot') == '1') Ringan
                            @elseif(request('bobot') == '2') Sedang
                            @elseif(request('bobot') == '3') Berat
                            @elseif(request('bobot') == '0') Tidak Ada Kendala
                            @endif
                        </strong>
                    </span>
                @endif
                @if(request('status_progres') == '100')
                    <span class="badge bg-light text-dark border px-2 py-1 shadow-sm"><i class="fas fa-filter text-secondary"></i> Progres: <strong>Tuntas 100%</strong></span>
                @endif
                @if(request('status_progres') == '0')
                    <span class="badge bg-light text-dark border px-2 py-1 shadow-sm"><i class="fas fa-filter text-secondary"></i> Progres: <strong>Belum (0%)</strong></span>
                @endif
                @if(request('belum_verifikasi') == '1')
                    <span class="badge bg-light text-dark border px-2 py-1 shadow-sm"><i class="fas fa-gavel text-warning"></i> Filter: <strong>Belum Diverifikasi PCL</strong></span>
                @endif

                @if(request()->hasAny(['cluster', 'status_progres', 'bobot', 'search']) || request('belum_verifikasi'))
                    <a href="{{ request()->fullUrlWithQuery(['cluster' => null, 'status_progres' => null, 'bobot' => null, 'belum_verifikasi' => null]) }}" class="btn btn-sm btn-danger shadow-sm fw-bold px-3">
                        <i class="fas fa-times me-1"></i> Hapus Filter
                    </a>
                @endif
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle" style="min-width: 1000px;">
                <thead class="table-light">
                    <tr class="small text-uppercase">
                        <th>Lokasi (Kab / Kec / Desa)</th>
                        <th>Nama SLS / ID</th>
                        <th class="text-center">Muatan</th>
                        <th class="text-center text-success">Selesai</th>
                        <th class="text-center text-primary">Diperiksa</th>
                        <th class="text-center">Persentase (%)</th>
                        <th class="text-center">Kendala</th>
                        <th class="text-center">Klaster Wilayah</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($wilayahs as $w)
                    @php 
                    $persenSelesai = ($w->muatan > 0) ? round(($w->selesai / $w->muatan) * 100) : 0;
                    $persenPeriksa = ($w->muatan > 0) ? round(($w->diperiksa / $w->muatan) * 100) : 0;
                    
                    $colorClass = $w->cluster_label == 'Lancar' ? 'bg-success' : ($w->cluster_label == 'Waspada' ? 'bg-warning text-dark' : 'bg-danger text-white');
                    
                    $labelKendala = '-';
                    $badgeColor = 'secondary';
                    if(isset($w->bobot_kendala) && $w->bobot_kendala > 0) {
                        if($w->bobot_kendala == 1) { 
                            $labelKendala = 'Ringan'; 
                            $badgeColor = 'info text-dark'; 
                        }
                        elseif($w->bobot_kendala == 2) { 
                            $labelKendala = 'Sedang'; 
                            $badgeColor = 'primary'; 
                        }
                        elseif($w->bobot_kendala >= 3) { 
                            $labelKendala = 'Berat'; 
                            $badgeColor = 'dark'; 
                        }
                    }
                    @endphp
                        <tr class="border-bottom">
                            <td>
                                <small class="text-muted d-block" style="font-size: 0.65rem;">{{ $w->nama_kab }}</small>
                                <div class="fw-bold text-dark" style="font-size: 0.85rem;">{{ $w->nama_kec }}</div>
                                <div class="text-muted small">Desa {{ $w->nama_desa }}</div>
                            </td>
                            <td>
                                <span class="fw-bold d-block mb-0" style="font-size: 0.85rem;">{{ $w->nama_sls }}</span>
                                <code class="text-muted" style="font-size: 0.7rem;">{{ $w->id_sub_sls }}</code>
                            </td>
                            <td class="text-center small">{{ number_format($w->muatan) }}</td>
                            <td class="text-center fw-bold text-success small">{{ number_format($w->selesai) }}</td>
                            <td class="text-center fw-bold text-primary small">{{ number_format($w->diperiksa) }}</td>
                            <td>
                                <div class="d-flex align-items-center mb-1">
                                    <div class="progress flex-grow-1" style="height: 6px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $persenSelesai }}%"></div>
                                    </div>
                                    <span class="ms-2 fw-bold text-success" style="font-size: 0.7rem;">S:{{ $persenSelesai }}%</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1" style="height: 6px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $persenPeriksa }}%"></div>
                                    </div>
                                    <span class="ms-2 fw-bold text-primary" style="font-size: 0.7rem;">P:{{ $persenPeriksa }}%</span>
                                </div>
                            </td>
                            <td class="align-middle text-center">
                                @if(isset($w->bobot_kendala) && $w->bobot_kendala > 0)
                                    <div class="mb-1">
                                        <span class="badge bg-{{ $badgeColor }} shadow-sm px-2 py-1" style="font-size: 0.65rem;">
                                            Bobot: {{ $labelKendala }}
                                        </span>
                                    </div>
                                    
                                    @if(!empty(trim($w->keterangan_kendala)))
                                        <button type="button" class="btn btn-light btn-sm border shadow-sm mt-1" style="font-size: 0.65rem; border-radius: 6px;" data-bs-toggle="modal" data-bs-target="#modal-{{ $w->id_sub_sls }}">
                                            <i class="fas fa-list-ul text-primary"></i> Lihat Kendala
                                        </button>
                                        
                                        <form action="{{ route('kendala.selesai', $w->id_sub_sls) }}" method="POST" id="form-kendala-{{ $w->id_sub_sls }}">
                                            @csrf
                                            @method('PATCH')
                                        
                                            <div class="modal fade" id="modal-{{ $w->id_sub_sls }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
                                                    <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                                                        
                                                        <div class="modal-header border-0 pb-0 pt-4 px-4 bg-white">
                                                            <h6 class="modal-title fw-bold text-dark d-flex align-items-center" style="letter-spacing: 0.3px;">
                                                                <i class="fas fa-history text-primary me-2"></i> Riwayat & Log Kendala
                                                            </h6>
                                                            <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" style="font-size: 0.8rem;"></button>
                                                        </div>
                                        
                                                        <div class="modal-body p-4 text-start bg-white">
                                                            <div class="p-2 mb-3 text-center" style="background-color: #f8fafc; border-radius: 12px; border: 1px dashed #cbd5e1;">
                                                                <span class="fw-bold text-secondary" style="font-size: 0.8rem;">
                                                                    <i class="fas fa-map-marker-alt text-danger me-1"></i> {{ $w->nama_sls }}
                                                                </span>
                                                            </div>
                                        
                                                            @php 
                                                                $riwayats = explode("\n", trim($w->keterangan_kendala)); 
                                                                $totalKendala = 0;
                                                                $totalSelesai = 0;
                                                                $totalBelum = 0;
                                                                
                                                                foreach($riwayats as $r) {
                                                                    if(trim($r) != '') {
                                                                        $totalKendala++;
                                                                        if(str_contains($r, '✅')) {
                                                                            $totalSelesai++;
                                                                        } else {
                                                                            $totalBelum++;
                                                                        }
                                                                    }
                                                                }
                                                            @endphp
                                        
                                                            <div class="row g-2 mb-3 text-center small">
                                                                <div class="col-4">
                                                                    <div class="p-2 border rounded-3 bg-light">
                                                                        <div class="text-muted text-uppercase fw-bold mb-0.5" style="font-size: 0.58rem; letter-spacing: 0.3px;">Total Log</div>
                                                                        <div class="fw-bold text-dark fs-6">{{ $totalKendala }}</div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-4">
                                                                    <div class="p-2 border rounded-3" style="background-color: #e8f5e9; border-color: #c8e6c9 !important;">
                                                                        <div class="text-success text-uppercase fw-bold mb-0.5" style="font-size: 0.58rem; letter-spacing: 0.3px;">Telah Diatasi</div>
                                                                        <div class="fw-bold text-success fs-6">{{ $totalSelesai }}</div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-4">
                                                                    <div class="p-2 border rounded-3" style="background-color: #ffeef0; border-color: #ffcdd2 !important;">
                                                                        <div class="text-danger text-uppercase fw-bold mb-0.5" style="font-size: 0.58rem; letter-spacing: 0.3px;">Belum Diatasi</div>
                                                                        <div class="fw-bold text-danger fs-6">{{ $totalBelum }}</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                        
                                                            <p class="text-primary fw-bold edit-mode-element d-none mb-3 animate__animated animate__fadeIn" style="font-size: 0.72rem; letter-spacing: 0.2px;">
                                                                <i class="fas fa-mouse-pointer me-1"></i> Pilih baris kendala yang sudah selesai diatasi:
                                                            </p>
                                        
                                                            <div class="timeline-wrapper" style="max-height: 260px; overflow-y: auto; padding-right: 2px;">
                                                                @foreach($riwayats as $index => $r)
                                                                    @if(trim($r) != '')
                                                                        @php
                                                                            $isResolved = str_contains($r, '✅');
                                                                            $cleanText = str_replace(' ✅', '', $r);
                                                                            
                                                                            // Ekstrak Waktu harian
                                                                            $timeStr = '';
                                                                            if (preg_match('/^\[(.*?)\]/', $cleanText, $matches)) {
                                                                                $timeStr = $matches[1];
                                                                                $cleanText = trim(str_replace('['.$timeStr.']', '', $cleanText));
                                                                            }
                                                                            
                                                                            // Ekstrak Kode Kategori
                                                                            $severityLabel = '';
                                                                            $severityColor = 'secondary';
                                                                            if (preg_match('/^(B\d+|B0 \(Manual\)):?/', $cleanText, $weightMatches)) {
                                                                                $matchCode = $weightMatches[1];
                                                                                $cleanText = trim(str_replace($matchCode, '', $cleanText));
                                                                                $cleanText = ltrim($cleanText, ': ');
                                                                                
                                                                                if (str_contains($matchCode, 'B3')) { $severityLabel = 'Berat'; $severityColor = 'danger'; }
                                                                                elseif (str_contains($matchCode, 'B2')) { $severityLabel = 'Sedang'; $severityColor = 'primary'; }
                                                                                elseif (str_contains($matchCode, 'B1')) { $severityLabel = 'Ringan'; $severityColor = 'info text-dark'; }
                                                                                else { $severityLabel = 'Manual'; $severityColor = 'warning text-dark'; }
                                                                            }
                                                                        @endphp
                                                                        
                                                                        <div class="p-3 mb-2 rounded-3 border d-flex align-items-start gap-2" 
                                                                             style="background-color: {{ $isResolved ? '#f1f5f9' : '#ffffff' }}; border-color: #e2e8f0 !important; border-left: 4px solid {{ $isResolved ? '#94a3b8' : ($severityColor == 'danger' ? '#dc3545' : ($severityColor == 'primary' ? '#0d6efd' : '#0dcaf0')) }} !important;">
                                                                            
                                                                            @if(!$isResolved)
                                                                                <div class="form-check edit-mode-element d-none pt-1">
                                                                                    <input class="form-check-input cursor-pointer border-secondary" type="checkbox" name="selected_lines[]" value="{{ $index }}" style="width: 1.1rem; height: 1.1rem;">
                                                                                </div>
                                                                            @endif
                                        
                                                                            <div class="flex-grow-1">
                                                                                <div class="d-flex align-items-center gap-2 flex-wrap mb-1.5">
                                                                                    @if($severityLabel)
                                                                                        <span class="badge bg-{{ $severityColor }} px-2 py-0.5" style="font-size: 0.6rem; font-weight: 700; border-radius: 4px;">{{ $severityLabel }}</span>
                                                                                    @endif
                                                                                    
                                                                                    @if($timeStr)
                                                                                        <small class="text-muted fw-semibold" style="font-size: 0.65rem;"><i class="far fa-clock me-1"></i>{{ $timeStr }}</small>
                                                                                    @endif
                                        
                                                                                    @if($isResolved)
                                                                                        <span class="badge bg-secondary text-white px-2 py-0.5 ms-auto" style="font-size: 0.6rem; font-weight: 700; border-radius: 4px;"><i class="fas fa-check-circle me-1"></i>Selesai</span>
                                                                                    @endif
                                                                                </div>
                                                                                
                                                                                <p class="mb-0 small" style="font-size: 0.8rem; line-height: 1.45; color: {{ $isResolved ? '#64748b' : '#1e293b' }} !important; font-weight: {{ $isResolved ? '500' : '600' }};">
                                                                                    {{ $cleanText }}
                                                                                </p>
                                                                            </div>
                                                                        </div>
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        </div>
                                        
                                                        <div class="modal-footer border-0 p-3" style="background-color: #f8fafc;">
                                                            <button type="button" class="btn btn-edit-toggle text-primary fw-bold small border-0 bg-transparent" onclick="toggleEditMode(this)" style="font-size: 0.8rem;">
                                                                <i class="fas fa-edit me-1"></i> Edit Kendala
                                                            </button>
                                                            
                                                            <button type="button" class="btn btn-success btn-sm fw-bold px-3 d-none btn-save-changes" data-bs-toggle="modal" data-bs-target="#modal-confirm-{{ $w->id_sub_sls }}" style="border-radius: 8px; font-size: 0.8rem;">
                                                                <i class="fas fa-save me-1"></i> Simpan Perubahan
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        
                                            <div class="modal fade" id="modal-confirm-{{ $w->id_sub_sls }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                                                <div class="modal-dialog modal-sm modal-dialog-centered" style="max-width: 360px;">
                                                    <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                                                        <div class="modal-body p-4 text-center">
                                                            <div class="text-warning mb-3">
                                                                <i class="fas fa-exclamation-triangle fa-3x animate__animated animate__bounceIn"></i>
                                                            </div>
                                                            
                                                            <h6 class="fw-bold text-dark mb-2" style="letter-spacing: 0.2px;">Konfirmasi Pembaruan Log</h6>
                                                            <p class="text-muted small mb-4" style="line-height: 1.45;">Apakah Anda yakin ingin menyimpan perubahan? Kendala yang dipilih akan ditandai telah diatasi dan merubah status klaster wilayah.</p>
                                                            
                                                            <div class="d-flex justify-content-center gap-2">
                                                                <button type="button" class="btn btn-light btn-sm fw-bold px-3 text-secondary border text-dark" data-bs-toggle="modal" data-bs-target="#modal-{{ $w->id_sub_sls }}" style="border-radius: 8px; font-size: 0.78rem;">
                                                                    <i class="fas fa-arrow-left me-1"></i> Batal
                                                                </button>
                                                                
                                                                <button type="submit" class="btn btn-success btn-sm fw-bold px-3 text-white" style="border-radius: 8px; font-size: 0.78rem;">
                                                                    Ya, Simpan <i class="fas fa-check ms-1"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>

                                        @if(session('open_modal'))
                                        <script>
                                            document.addEventListener("DOMContentLoaded", function() {
                                                var myModal = new bootstrap.Modal(document.getElementById('modal-{{ session('open_modal') }}'));
                                                myModal.show();
                                            });
                                        </script>
                                        @endif
                                    @endif

                                @elseif(!empty($w->keterangan_kendala) && str_contains($w->keterangan_kendala, 'B0 (Manual)'))
                                    <div class="mb-1">
                                        <span class="badge bg-warning text-dark shadow-sm px-2 py-1 animate__animated animate__pulse animate__infinite" style="font-size: 0.65rem; font-weight: 600;">
                                            <i class="fas fa-clock"></i> Butuh Verifikasi
                                        </span>
                                    </div>
                                    <button type="button" class="btn btn-warning btn-sm shadow-sm mt-1 text-dark fw-bold border-0" style="font-size: 0.65rem; border-radius: 6px; background-color: #ffca28;" data-bs-toggle="modal" data-bs-target="#modal-verifikasi-{{ $w->id_sub_sls }}">
                                        <i class="fas fa-gavel"></i> Beri Bobot
                                    </button>

                                    <div class="modal fade" id="modal-verifikasi-{{ $w->id_sub_sls }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-sm modal-dialog-centered" style="max-width: 400px;">
                                            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; font-family: 'Inter', sans-serif;">
                                                <form action="{{ route('kendala.verifikasi', $w->id_sub_sls) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header border-0 pb-0 pt-4 px-4">
                                                        <h6 class="modal-title fw-bold text-dark d-flex align-items-center">
                                                            <i class="fas fa-gavel text-warning me-2"></i> Verifikasi Kendala Manual
                                                        </h6>
                                                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                                                    </div>

                                                    <div class="modal-body p-4 text-start">
                                                        <div class="p-2 mb-3 text-center" style="background: #f8f9fa; border-radius: 10px; border: 1px dashed #ccc;">
                                                            <span class="fw-bold text-dark small"><i class="fas fa-map-marker-alt text-danger me-1"></i> {{ $w->nama_sls }}</span>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="small fw-bold text-muted mb-1 d-block"><i class="fas fa-comment-alt text-secondary me-1"></i> Laporan Kendala Lapangan PCL:</label>
                                                            <div class="p-2 border rounded bg-light small text-dark" style="max-height: 100px; overflow-y: auto; font-style: italic; font-size: 0.75rem; line-height: 1.4;">
                                                                @php 
                                                                    $riwayatsManual = explode("\n", trim($w->keterangan_kendala)); 
                                                                    $barisTerakhir = end($riwayatsManual);
                                                                @endphp
                                                                {{ str_replace(strstr($barisTerakhir, 'B0 (Manual): ', true), '', $barisTerakhir) }}
                                                            </div>
                                                        </div>

                                                        <div class="mb-2">
                                                            <label class="small fw-bold text-muted mb-1 d-block"><i class="fas fa-weight text-secondary me-1"></i> Tentukan Skala Kerawanan:</label>
                                                            <select name="bobot_pilihan" class="form-select form-select-sm border shadow-sm fw-medium" required style="font-size: 0.8rem; border-radius: 8px;">
                                                                <option value="1">🟢 Kendala Ringan (Bobot 1)</option>
                                                                <option value="2" selected>🟡 Kendala Sedang (Bobot 2)</option>
                                                                <option value="3">🔴 Kendala Berat (Bobot 3)</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="modal-footer border-0 p-3 bg-light bg-opacity-50">
                                                        <button type="button" class="btn btn-secondary btn-sm fw-bold px-3" data-bs-dismiss="modal" style="border-radius: 8px;">Batal</button>
                                                        <button type="submit" class="btn btn-warning btn-sm fw-bold px-3 text-dark" style="border-radius: 8px; background-color: #ffca28; border: 0变量;">
                                                            <i class="fas fa-save me-1"></i> Simpan Bobot
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                @else
                                    <span class="text-muted fw-bold" style="font-size: 0.65rem;"><i class="fas fa-check text-success"></i> Tidak ada kendala</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge status-badge {{ $colorClass }} px-3 py-2 shadow-sm" style="font-size: 0.65rem; border-radius: 6px;">
                                    {{ strtoupper($w->cluster_label) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-5 text-muted">Data tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-3 bg-light d-flex justify-content-between align-items-center border-top flex-wrap gap-3 mt-3 rounded-bottom">
            <div class="m-0 pagination-wrapper">
                {{ $wilayahs->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<style>
    .bg-success-soft { background-color: rgba(25, 135, 84, 0.1); }
    .form-check-input:checked { background-color: #198754; border-color: #198754; }
    .cursor-pointer { cursor: pointer; }
    .transition-all:hover { transform: translateX(5px); }
    .timeline-wrapper::-webkit-scrollbar { width: 4px; }
    .timeline-wrapper::-webkit-scrollbar-track { background: #f1f1f1; }
    .timeline-wrapper::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px; }
</style>

<script>
    function toggleEditMode(btn) {
        const modal = btn.closest('.modal');
        const editElements = modal.querySelectorAll('.edit-mode-element');
        const saveBtn = modal.querySelector('.btn-save-changes');
        
        editElements.forEach(el => el.classList.toggle('d-none'));
        saveBtn.classList.toggle('d-none');
        
        if (saveBtn.classList.contains('d-none')) {
            btn.innerHTML = '<i class="fas fa-edit me-1"></i> Edit Kendala';
            btn.classList.replace('btn-primary', 'btn-outline-primary');
        } else {
            btn.innerHTML = '<i class="fas fa-times me-1"></i> Batal';
            btn.classList.replace('btn-outline-primary', 'btn-primary');
        }
    }

    var options = {
        series: [{{ $persentaseTotal }}],
        chart: { height: 180, type: 'radialBar' },
        plotOptions: {
            radialBar: {
                hollow: { size: '60%' },
                dataLabels: {
                    name: { show: false },
                    value: { 
                        fontSize: '18px', 
                        fontWeight: 'bold', 
                        offsetY: 7,
                        formatter: function (val) { return val + "%" }
                    }
                }
            }
        },
        colors: ['#0d6efd'],
        stroke: { lineCap: 'round' }
    };

    var chart = new ApexCharts(document.querySelector("#chartProgres"), options);
    chart.render();
</script>

<script>
    window.addEventListener('beforeunload', function() {
        localStorage.setItem('scrollPosition', window.scrollY);
    });

    window.addEventListener('load', function() {
        if (localStorage.getItem('scrollPosition') !== null) {
            window.scrollTo({ top: parseInt(localStorage.getItem('scrollPosition')), behavior: 'instant' });
            localStorage.removeItem('scrollPosition'); 
        }
    });
</script>
@endsection