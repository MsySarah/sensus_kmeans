<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\KmeansController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\KlasterController;

Route::get('/', function () {
    return view('welcome');
});

// Rute untuk Login & Logout
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'authenticate']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Bot
Route::post('/bot-sensus', [TelegramController::class, 'webhook'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);

// Route untuk Python K-Means
Route::get('/tes-kmeans', [KmeansController::class, 'jalankanClustering']);

// Route Kecamatan (Berdasarkan Kabupaten)
Route::get('/get-kecamatan/{kode_kab}', function($kode_kab) {
    return DB::table('wilayah_master')
            ->select('kode_kec as id', 'nama_kec as nama')
            ->where('kode_kab', trim($kode_kab))
            ->distinct()
            ->get();
});

// Route Desa (Berdasarkan Kecamatan)
Route::get('/get-desa/{kode_kec}', function($kode_kec) {
    return DB::table('wilayah_master')
            ->select('id_desa as id', 'nama_desa as nama')
            ->where('kode_kec', trim($kode_kec))
            ->groupBy('id_desa', 'nama_desa')
            ->get();
});

// Route SLS (Berdasarkan Desa)
Route::get('/get-sls/{id_desa}', function($id_desa) {
    return DB::table('wilayahs')
            ->select('id_sub_sls as id', 'nama_sls as nama')
            ->where('id_desa', trim($id_desa))
            ->get();
});

Route::middleware(['auth'])->group(function () {
    // Rute Dashboard (sudah ada)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Rute CRUD User (Hanya Admin yang bisa akses)
    Route::resource('admin/users', UserController::class);
});


Route::get('/hasil-klaster', [KlasterController::class, 'index'])->name('hasil.klaster');


Route::prefix('admin/master')->group(function () {
    Route::get('/dataset', [MasterDataController::class, 'dataset'])->name('master.dataset');
    Route::get('/wilayah', [MasterDataController::class, 'wilayah'])->name('master.wilayah');
    Route::get('/kendala', [MasterDataController::class, 'kendala'])->name('master.kendala');
});

// Route Atasi Kendala
Route::patch('/kendala/selesai/{id}', [App\Http\Controllers\DashboardController::class, 'selesaikanKendala'])->name('kendala.selesai');