@extends('layouts.admin')

@section('content')
<div class="container-fluid mb-5">
    {{-- Header & Filter --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <i class="fas fa-check-circle me-2"></i> <strong>Mantap!</strong> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    <div class="card shadow-sm border-0 mb-4 bg-dark text-white p-4" style="border-radius: 15px;">
        <form action="{{ route('hasil.klaster') }}" method="GET" class="row g-3 align-items-center">
            
            {{-- Branding & Action Button --}}
            <div class="col-md-4 border-end border-secondary pe-4">
                <div class="d-flex align-items-center mb-3">
                    <i class="fas fa-brain fa-2x text-success me-3"></i>
                    <div>
                        <h4 class="fw-bold mb-0 text-success" style="letter-spacing: 1px;">Hasil Klaster AI</h4>
                        <p class="small mb-0 opacity-75">Status: <span class="text-warning fw-bold">{{ $statusFilter }}</span></p>
                    </div>
                </div>
                
                {{-- Tombol Klaster--}}
                <a href="/tes-kmeans" class="btn btn-success btn-sm w-100 py-2 shadow-lg fw-bold btn-klaster-ai" 
                   style="border-radius: 10px; transition: 0.3s; border: none;">
                    <i class="fas fa-robot me-2"></i> Jalankan Klasterisasi Terbaru
                </a>
            </div>
    
            {{-- Grouping Filter --}}
            <div class="col-md-6 px-4">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="x-small opacity-75 d-block mb-1" style="font-size: 0.7rem;">Filter Tanggal</label>
                        <input type="date" name="tgl" class="form-control form-control-sm bg-secondary text-white border-0" value="{{ request('tgl') }}" onchange="this.form.submit()">
                    </div>
                    <div class="col-md-4">
                        <label class="x-small opacity-75 d-block mb-1" style="font-size: 0.7rem;">Kabupaten</label>
                        <select name="kab" class="form-select form-select-sm border-0" onchange="this.form.submit()">
                            <option value="">-- Semua --</option>
                            @foreach($kabupatens as $k)
                                <option value="{{ $k->kode_kab }}" {{ request('kab') == $k->kode_kab ? 'selected' : '' }}>{{ $k->nama_kab }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="x-small opacity-75 d-block mb-1" style="font-size: 0.7rem;">Kecamatan</label>
                        <select name="kec" class="form-select form-select-sm border-0" onchange="this.form.submit()">
                            <option value="">-- Semua --</option>
                            @foreach($kecamatans as $kec)
                                <option value="{{ $kec->id }}" {{ request('kec') == $kec->id ? 'selected' : '' }}>{{ $kec->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Reset Filter  --}}
                    <div class="col-12 mt-2">
                        <a href="{{ route('hasil.klaster') }}" class="text-decoration-none text-light opacity-50 small hover-opacity-100">
                            <i class="fas fa-sync-alt me-1"></i> Reset Semua Filter
                        </a>
                    </div>
                </div>
            </div>
    
            {{-- Counter Total --}}
            <div class="col-md-2 text-center border-start border-secondary ps-4">
                <div class="small opacity-75 mb-1">Total Wilayah</div>
                <div class="h3 fw-bold text-success mb-0" style="text-shadow: 0 0 10px rgba(25, 135, 84, 0.3);">
                    {{ number_format($totalData) }}
                </div>
                <small class="text-muted" style="font-size: 0.6rem;">Terklasterisasi</small>
            </div>
        </form>
    </div>
    
    <style>
        .btn-klaster-ai:hover {
            background-color: #1ea96d !important;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(25, 135, 84, 0.4) !important;
        }
        .hover-opacity-100:hover {
            opacity: 1 !important;
            color: #ffc107 !important;
        }
    </style>

    {{-- Grafik Sampingan --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 border-0"><h6 class="fw-bold mb-0"><i class="fas fa-chart-pie me-2 text-success"></i>Proporsi Klaster Wilayah</h6></div>
                <div class="card-body"><div id="chartDistribusi"></div></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 border-0"><h6 class="fw-bold mb-0"><i class="fas fa-chart-line me-2 text-primary"></i>Rata-Rata Progres (%) per Klaster</h6></div>
                <div class="card-body"><div id="chartProgres"></div></div>
            </div>
        </div>
    </div>

    {{-- Big Chart Utama --}}
    <div class="card shadow-sm border-0 mb-4 overflow-hidden">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center gap-3">
            <div class="text-truncate pe-3">
                <h6 class="fw-bold mb-0 text-truncate"><i class="fas fa-map-marked-alt text-danger me-2"></i>Analisis {{ $labelLevel }}</h6>
                <small class="text-muted">Mode tampilan sesuai tanggal terpilih</small>
            </div>
            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                <select id="sortChart" class="form-select form-select-sm border-secondary shadow-sm" style="width: auto; cursor: pointer;">
                    <option value="az">Urutkan: A - Z</option>
                    <option value="high">Urutkan: Tertinggi</option>
                    <option value="low">Urutkan: Terendah</option>
                </select>
                <div class="btn-group shadow-sm">
                    <button type="button" class="btn btn-sm btn-outline-dark active" data-mode="klaster">Hasil Klaster</button>
                    <button type="button" class="btn btn-sm btn-outline-dark" data-mode="progres">Rata-rata Progres</button>
                    <button type="button" class="btn btn-sm btn-outline-dark" data-mode="kendala">Total Kendala</button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div style="height: 500px; overflow-y: auto; overflow-x: hidden; padding: 15px;">
                <div id="bigMainChart"></div>
            </div>
        </div>
    </div>

    {{-- TABEL DETAIL - SESUAI TANGGAL --}}
    <div class="card-header bg-dark text-white py-3 px-4 d-flex justify-content-between align-items-center">
        <div class="pe-3">
            <h6 class="fw-bold mb-0 text-white">
                <i class="fas fa-calendar-alt me-2 text-info"></i>Detail Data Per Hari: {{ request('tgl') ?? date('d/m/Y') }}
            </h6>
            <small class="text-info fw-bold opacity-100">Unit Analisis: {{ $statusFilter }}</small>
        </div>
            <form action="{{ route('hasil.klaster') }}" method="GET" class="col-md-3 m-0">
                <input type="hidden" name="tgl" value="{{ request('tgl') }}">
                <input type="hidden" name="kab" value="{{ request('kab') }}">
                <input type="hidden" name="kec" value="{{ request('kec') }}">
                <div class="input-group input-group-sm shadow-sm">
                    <input type="text" name="cari" class="form-control border-0" placeholder="Cari SLS / Klaster..." value="{{ request('cari') }}">
                    <button class="btn btn-info border-0" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="min-width: 1200px;">
                    <thead class="bg-light fw-bold">
                        <tr class="text-dark small text-uppercase">
                            <th class="ps-4" style="width: 200px;">Lokasi (Kec / Desa)</th>
                            <th style="width: 150px;">Nama SLS / ID</th>
                            <th class="text-center">Muatan</th>
                            <th class="text-center text-success">Selesai</th>
                            <th class="text-center text-primary">Diperiksa</th>
                            <th style="width: 200px;">Persentase (%)</th>
                            <th class="text-center">Kendala</th>
                            <th class="text-center pe-4">Klaster AI</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse($detailKlaster as $d)
                        
                        @php 
                            $persenSelesai = ($d->muatan > 0) ? round(($d->selesai / $d->muatan) * 100) : 0;
                            $persenPeriksa = ($d->muatan > 0) ? round(($d->diperiksa / $d->muatan) * 100) : 0;
                            $colorClass = $d->cluster_label == 'Lancar' ? 'bg-success' : ($d->cluster_label == 'Waspada' ? 'bg-warning text-dark' : 'bg-danger text-white');
                            
                            $labelKendala = 'Aman';
                            $badgeColor = 'success';
                            if(($d->bobot_kendala ?? 0) == 1) { $labelKendala = 'Ringan'; $badgeColor = 'info text-dark'; }
                            elseif(($d->bobot_kendala ?? 0) == 2) { $labelKendala = 'Sedang'; $badgeColor = 'primary'; }
                            elseif(($d->bobot_kendala ?? 0) >= 3) { $labelKendala = 'Berat'; $badgeColor = 'dark'; }
                        @endphp
                        <tr class="border-bottom">
                            <td class="ps-4">
                                <div class="fw-bold text-dark">{{ $d->nama_kec }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">Desa {{ $d->nama_desa }}</div>
                            </td>
                            <td>
                                <span class="fw-bold d-block mb-0">{{ $d->nama_sls }}</span>
                                <code class="text-muted" style="font-size: 0.7rem;">{{ $d->id_sub_sls }}</code>
                            </td>
                            <td class="text-center">{{ number_format($d->muatan) }}</td>
                            <td class="text-center fw-bold text-success">{{ number_format($d->selesai) }}</td>
                            <td class="text-center fw-bold text-primary">{{ number_format($d->diperiksa) }}</td>
                            <td>
                                {{-- Progress bar --}}
                                <div class="d-flex align-items-center mb-1">
                                    <div class="progress flex-grow-1" style="height: 6px; border-radius: 10px;">
                                        <div class="progress-bar bg-success" style="width: {{ $persenSelesai }}%"></div>
                                    </div>
                                    <span class="ms-2 fw-bold text-success" style="font-size: 0.65rem; min-width: 45px;">S:{{ $persenSelesai }}%</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1" style="height: 6px; border-radius: 10px;">
                                        <div class="progress-bar bg-primary" style="width: {{ $persenPeriksa }}%"></div>
                                    </div>
                                    <span class="ms-2 fw-bold text-primary" style="font-size: 0.65rem; min-width: 45px;">P:{{ $persenPeriksa }}%</span>
                                </div>
                            </td>
                            <td class="text-center">
                                {{-- Logika Badge Kendala --}}
                                @if(($d->bobot_kendala ?? 0) > 0)
                                    <span class="badge bg-{{ $badgeColor }} mb-1 shadow-sm">{{ $labelKendala }}</span>
                                    @if(!empty(trim($d->keterangan_kendala)))
                                        
                                    @endif
                                @else
                                    <span class="text-success x-small"><i class="fas fa-check-circle"></i> Aman</span>
                                @endif
                            </td>
                            <td class="text-center pe-4">
                                <span class="badge {{ $colorClass }} px-3 py-2 shadow-sm" style="min-width: 100px;">
                                    {{ strtoupper($d->cluster_label) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center py-5 text-muted">Data history tidak ditemukan.</td></tr>
                        
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3 bg-light border-top">
                {{ $detailKlaster->links() }}
            </div>
        </div>
    </div>
</div>

<div id="chart-storage" style="display:none;" 
    data-summary='{"lancar":{{$totalLancar}},"waspada":{{$totalWaspada}},"terkendala":{{$totalTerkendala}}}'
    data-progres-avg='{"lancar":{{$avgProgresLancar}},"waspada":{{$avgProgresWaspada}},"terkendala":{{$avgProgresTerkendala}}}'
    data-main-labels="{{ json_encode($mainChartData->pluck('name')) }}"
    data-main-selesai="{{ json_encode($mainChartData->pluck('progres_selesai_avg')) }}"
    data-main-diperiksa="{{ json_encode($mainChartData->pluck('progres_diperiksa_avg')) }}"
    data-main-ringan="{{ json_encode($mainChartData->pluck('jml_ringan')) }}"
    data-main-sedang="{{ json_encode($mainChartData->pluck('jml_sedang')) }}"
    data-main-berat="{{ json_encode($mainChartData->pluck('jml_berat')) }}"
    data-main-lancar="{{ json_encode($mainChartData->pluck('jml_lancar')) }}"
    data-main-waspada="{{ json_encode($mainChartData->pluck('jml_waspada')) }}"
    data-main-terkendala="{{ json_encode($mainChartData->pluck('jml_terkendala')) }}">
</div>

<style>
    .badge-ringan { background-color: #17a2b8; color: white; }
    .badge-sedang { background-color: #0d6efd; color: white; }
    .badge-berat { background-color: #343a40; color: white; }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const s = document.getElementById('chart-storage');
        const summary = JSON.parse(s.dataset.summary);
        const avg = JSON.parse(s.dataset.progresAvg);
        
        // Data Mentah
        const rawLabels = JSON.parse(s.dataset.mainLabels);
        const rawSelesai = JSON.parse(s.dataset.mainSelesai);
        const rawDiperiksa = JSON.parse(s.dataset.mainDiperiksa);
        const rawLancar = JSON.parse(s.dataset.mainLancar);
        const rawWaspada = JSON.parse(s.dataset.mainWaspada);
        const rawTerkendala = JSON.parse(s.dataset.mainTerkendala);
        const rawRingan = JSON.parse(s.dataset.mainRingan);
        const rawSedang = JSON.parse(s.dataset.mainSedang);
        const rawBerat = JSON.parse(s.dataset.mainBerat);
    
        let currentMode = 'klaster'; // Default
    
        // 1. Chart Sampingan
        new ApexCharts(document.querySelector("#chartDistribusi"), {
            series: [summary.lancar, summary.waspada, summary.terkendala],
            labels: ['Lancar', 'Waspada', 'Terkendala'],
            chart: { type: 'pie', height: 250 },
            colors: ['#198754', '#ffc107', '#dc3545'],
            legend: { position: 'bottom' },
            tooltip: { y: { formatter: (v) => v + " Wilayah" } }
        }).render();
    
        new ApexCharts(document.querySelector("#chartProgres"), {
            series: [{ name: 'Rata-rata % Selesai', data: [avg.lancar, avg.waspada, avg.terkendala] }],
            chart: { type: 'bar', height: 250, toolbar: {show:false} },
            colors: ['#198754', '#ffc107', '#dc3545'],
            plotOptions: { bar: { distributed: true, borderRadius: 4, columnWidth: '50%' } },
            xaxis: { categories: ['Lancar', 'Waspada', 'Terkendala'] },
            yaxis: { min: 0, max: 100 },
            dataLabels: { enabled: true, formatter: (v) => v + "%" }
        }).render();
    
        // 2. FUNGSI SORTIR 
        function getSortedData() {
            let sortType = document.getElementById('sortChart').value;
            
            // SEMUA DATA JADI ANGKA MURNI PAKAI Number()
            let combined = rawLabels.map((lbl, i) => ({
                label: lbl,
                selesai: Number(rawSelesai[i]) || 0,
                diperiksa: Number(rawDiperiksa[i]) || 0,
                lancar: Number(rawLancar[i]) || 0, 
                waspada: Number(rawWaspada[i]) || 0, 
                terkendala: Number(rawTerkendala[i]) || 0,
                ringan: Number(rawRingan[i]) || 0, 
                sedang: Number(rawSedang[i]) || 0, 
                berat: Number(rawBerat[i]) || 0
            }));
    
            combined.sort((a, b) => {
                if (sortType === 'az') return a.label.localeCompare(b.label);
                
                let valA = 0, valB = 0;
                // Gunakan nilai yang benar sesuai mode yang sedang aktif
                if (currentMode === 'progres') {
                    valA = a.selesai; 
                    valB = b.selesai;
                } else if (currentMode === 'kendala') {
                    valA = a.ringan + a.sedang + a.berat; 
                    valB = b.ringan + b.sedang + b.berat;
                } else {
                    valA = a.lancar + a.waspada + a.terkendala; 
                    valB = b.lancar + b.waspada + b.terkendala;
                }
    
                if (sortType === 'high') return valB - valA; // Tertinggi di atas (Piramida Terbalik)
                if (sortType === 'low') return valA - valB;  // Terendah di atas
                return 0;
            });
    
            return {
                labels: combined.map(i => i.label),
                selesai: combined.map(i => i.selesai), 
                diperiksa: combined.map(i => i.diperiksa),
                lancar: combined.map(i => i.lancar), 
                waspada: combined.map(i => i.waspada), 
                terkendala: combined.map(i => i.terkendala),
                ringan: combined.map(i => i.ringan), 
                sedang: combined.map(i => i.sedang), 
                berat: combined.map(i => i.berat),
            };
        }
    
        // 3. Init Big Chart
        const innerChartHeight = Math.max(450, rawLabels.length * 40); 
        const bigChart = new ApexCharts(document.querySelector("#bigMainChart"), {
            series: [], 
            chart: { type: 'bar', height: innerChartHeight, stacked: true, toolbar: { show: true } },
            plotOptions: { bar: { horizontal: true, barHeight: '70%', dataLabels: { position: 'center' } } },
            dataLabels: { style: { colors: ['#fff'] }, formatter: (val) => val > 0 ? val : "" },
            stroke: { width: 1.5, colors: ['#fff'] },
            xaxis: { categories: [] },
            legend: { position: 'top', horizontalAlign: 'center' }
        });
        bigChart.render();
    
        // 4. Render Logic
        function renderMainChart() {
            let sorted = getSortedData();
            
            // Update kategori (Label Sumbu Y)
            bigChart.updateOptions({ xaxis: { categories: sorted.labels } });
    
            if (currentMode === 'progres') {
                bigChart.updateOptions({ 
                    chart: { stacked: false }, // Es Loli Berdampingan
                    colors: ['#0d6efd', '#28a745'], 
                    xaxis: { categories: sorted.labels, min: 0, max: 100 },
                    dataLabels: { formatter: (val) => val > 0 ? val + "%" : "" },
                    stroke: { width: 1 }
                });
                bigChart.updateSeries([
                    { name: 'Progres Selesai', data: sorted.selesai },
                    { name: 'Progres Diperiksa', data: sorted.diperiksa }
                ]);
            } else if (currentMode === 'kendala') {
                bigChart.updateOptions({ 
                    chart: { stacked: true },
                    colors: ['#17a2b8', '#0d6efd', '#343a40'],
                    xaxis: { categories: sorted.labels, min: undefined, max: undefined },
                    dataLabels: { formatter: (val) => val > 0 ? val : "" },
                    stroke: { width: 1.5 }
                });
                bigChart.updateSeries([
                    { name: 'Kendala Ringan', data: sorted.ringan },
                    { name: 'Kendala Sedang', data: sorted.sedang },
                    { name: 'Kendala Berat', data: sorted.berat }
                ]);
            } else {
                bigChart.updateOptions({ 
                    chart: { stacked: true },
                    colors: ['#329c68', '#f1c40f', '#e74c3c'],
                    xaxis: { categories: sorted.labels, min: undefined, max: undefined },
                    dataLabels: { formatter: (val) => val > 0 ? val : "" },
                    stroke: { width: 1.5 }
                });
                bigChart.updateSeries([
                    { name: 'Lancar', data: sorted.lancar },
                    { name: 'Waspada', data: sorted.waspada },
                    { name: 'Terkendala', data: sorted.terkendala }
                ]);
            }
        }
    
        renderMainChart();
    
        // 5. Events Click Tombol
        document.querySelectorAll('.btn-group button').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.btn-group button').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                currentMode = this.getAttribute('data-mode');
                renderMainChart();
            });
        });
    
        // 6. Event Pindah Dropdown Urutkan
        document.getElementById('sortChart').addEventListener('change', function() {
            renderMainChart();
        });

    // Simpan posisi layar sesaat sebelum halaman loading/refresh
    window.addEventListener('beforeunload', function() {
        sessionStorage.setItem('scrollPosition', window.scrollY);
    });

    //Kembalikan layar ke posisi semula setelah halaman selesai dimuat
    let scrollPos = sessionStorage.getItem('scrollPosition');
    if (scrollPos) {
        window.scrollTo({ top: parseInt(scrollPos), behavior: 'instant' });
        sessionStorage.removeItem('scrollPosition');
    }
    
    });
    </script>
@endsection