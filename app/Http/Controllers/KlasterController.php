<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KlasterController extends Controller
{
    public function index(Request $request)
        {
            // 1. DEFINISI QUERY AWAL & FILTER TANGGAL
            if ($request->filled('tgl')) {
                $tglFilter = $request->tgl;
                // Jika ada tanggal, ambil snapshot dari history (h)
                $query = DB::table('history_kendala as h')
                    ->join('wilayahs as w', 'h.id_sub_sls', '=', 'w.id_sub_sls')
                    ->where('h.tanggal_catat', $tglFilter)
                    ->select(
                        'w.nama_sls', 'w.nama_desa', 'w.nama_kec', 'w.nama_kab', 'w.kode_kab', 'w.kode_kec',
                        'h.*'
                    );
                
                // Inisial tabel untuk kolom yang ada di dua tabel
                $prefix = 'h.'; 
            } else {
                // Default: Ambil data real-time dari tabel wilayahs (w)
                $query = DB::table('wilayahs as w');
                $prefix = 'w.';
            }
    
            // 2. FILTER WILAYAH (Gunakan prefix w.)
            if ($request->filled('kab')) {
                $query->where('w.kode_kab', $request->kab);
            }
            if ($request->filled('kec')) {
                $query->where('w.kode_kec', $request->kec);
            }
    
            // 3. INFO TOTAL DATA
            $totalData = (clone $query)->count();
            $statusFilter = "Seluruh Wilayah";

            if($request->filled('kec')) {
                $dataFirst = (clone $query)->first();
                $statusFilter = "Kec. " . ($dataFirst->nama_kec ?? '') . " (" . ($dataFirst->nama_kab ?? '') . ")";
            } elseif($request->filled('kab')) {
                $statusFilter = "Kabupaten " . (clone $query)->value('w.nama_kab');
            }
    
            // 4. DATA GRAFIK SAMPINGAN (Pakai $prefix agar tidak ambiguous)
            $totalLancar = (clone $query)->where($prefix.'cluster_label', 'Lancar')->count();
            $totalWaspada = (clone $query)->where($prefix.'cluster_label', 'Waspada')->count();
            $totalTerkendala = (clone $query)->where($prefix.'cluster_label', 'Terkendala')->count();
    
            // Rata-rata Progres
            $avgProgresLancar = round((clone $query)->where($prefix.'cluster_label', 'Lancar')->avg(DB::raw('('.$prefix.'selesai / NULLIF('.$prefix.'muatan, 0)) * 100')) ?? 0);
            $avgProgresWaspada = round((clone $query)->where($prefix.'cluster_label', 'Waspada')->avg(DB::raw('('.$prefix.'selesai / NULLIF('.$prefix.'muatan, 0)) * 100')) ?? 0);
            $avgProgresTerkendala = round((clone $query)->where($prefix.'cluster_label', 'Terkendala')->avg(DB::raw('('.$prefix.'selesai / NULLIF('.$prefix.'muatan, 0)) * 100')) ?? 0);
    
            // 5. DATA GRAFIK UTAMA (BIG CHART)
            if ($request->filled('kec')) {
                $groupBy = 'w.nama_desa'; 
                $labelLevel = "Perbandingan Antar Desa/Kelurahan";
            } elseif ($request->filled('kab')) {
                $groupBy = 'w.nama_kec';
                $labelLevel = "Perbandingan Antar Kecamatan";
            } else {
                $groupBy = 'w.nama_kab';
                $labelLevel = "Perbandingan Antar Kabupaten";
            }
    
            $mainChartData = (clone $query)
                ->select(DB::raw($groupBy . ' as name'), 
                    DB::raw('AVG(('.$prefix.'selesai / NULLIF('.$prefix.'muatan, 0)) * 100) as progres_selesai_avg'),
                    DB::raw('AVG(('.$prefix.'diperiksa / NULLIF('.$prefix.'muatan, 0)) * 100) as progres_diperiksa_avg'),
                    DB::raw('SUM(case when '.$prefix.'bobot_kendala = 3 then 1 else 0 end) as jml_berat'),
                    DB::raw('SUM(case when '.$prefix.'bobot_kendala = 2 then 1 else 0 end) as jml_sedang'),
                    DB::raw('SUM(case when '.$prefix.'bobot_kendala = 1 then 1 else 0 end) as jml_ringan'),
                    DB::raw("SUM(case when ".$prefix."cluster_label = 'Lancar' then 1 else 0 end) as jml_lancar"),
                    DB::raw("SUM(case when ".$prefix."cluster_label = 'Waspada' then 1 else 0 end) as jml_waspada"),
                    DB::raw("SUM(case when ".$prefix."cluster_label = 'Terkendala' then 1 else 0 end) as jml_terkendala")
                )
                ->groupBy(DB::raw($groupBy))
                ->get();
    
            $mainChartData->transform(function ($item) {
                $item->progres_selesai_avg = round($item->progres_selesai_avg);
                $item->progres_diperiksa_avg = round($item->progres_diperiksa_avg);
                return $item;
            });
    
            // 6. DROPDOWN WILAYAH
            $kabupatens = DB::table('wilayah_master')->select('kode_kab', 'nama_kab')->distinct()->orderBy('nama_kab')->get();
            $kecamatans = collect();
            if ($request->filled('kab')) {
                $kecamatans = DB::table('wilayah_master')->select('kode_kec as id', 'nama_kec as nama')->where('kode_kab', (string)$request->kab)->distinct()->get();
            }
    
            // 7. LOGIKA PENCARIAN TABEL (Search Universal)
            $tableQuery = clone $query;
            if ($request->filled('cari')) {
                $cari = trim($request->cari);
                $cariLow = strtolower($cari);
                $isAngka = is_numeric($cari);
    
                $tableQuery->where(function ($q) use ($cari, $cariLow, $isAngka, $prefix) {
                    $q->where('w.nama_sls', 'LIKE', "%{$cari}%")
                      ->orWhere('w.nama_desa', 'LIKE', "%{$cari}%")
                      ->orWhere('w.nama_kec', 'LIKE', "%{$cari}%")
                      ->orWhere('w.nama_kab', 'LIKE', "%{$cari}%")
                      ->orWhere($prefix.'cluster_label', 'LIKE', "%{$cari}%");
    
                    if ($isAngka) {
                        $angka = (int) $cari;
                        $q->orWhere(DB::raw('ROUND(('.$prefix.'selesai / NULLIF('.$prefix.'muatan, 0)) * 100)'), '=', $angka)
                          ->orWhere($prefix.'bobot_kendala', '=', $angka);
                    }
                });
            }
    
            $detailKlaster = $tableQuery->paginate(20)->withQueryString();
    
            return view('admin.hasil_klaster', compact(
                'totalData', 'statusFilter', 'totalLancar', 'totalWaspada', 'totalTerkendala',
                'avgProgresLancar', 'avgProgresWaspada', 'avgProgresTerkendala',
                'mainChartData', 'labelLevel', 'kabupatens', 'kecamatans', 'detailKlaster'
            ));
        }
}