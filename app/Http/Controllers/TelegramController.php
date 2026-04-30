<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class TelegramController extends Controller
{
    private $alasan_templates = [
        1 => ['Responden sibuk', 'Responden ragu/curiga', 'Butuh izin khusus'],
        2 => ['Responden tidak di tempat', 'Responden di luar kota'],
        3 => ['Responden menolak', 'Petugas diusir']
    ];

    public function webhook(Request $request)
    {
        try {
            $update = $request->all();

            if (isset($update['callback_query'])) {
                $callback = $update['callback_query'];
                $chatId = $callback['message']['chat']['id'];
                $data = $callback['data'];

                if ($data == 'info_progres') {
                    $msg = "📊 *PANDUAN LAPORAN PROGRES*\n\nFormat: `P-IDSLS-JumlahSelesai-JumlahDiperiksa` \nContoh: `P-1601052005000300-50-40`";
                    return $this->sendMessage($chatId, $msg);
                } 
                
                if ($data == 'info_kendala') {
                    $msg = "⚠️ *PANDUAN LAPORAN KENDALA*\n\nCukup ketik: `K-IDSLS`\nContoh: `K-1601052005000300`\n\nNanti menu pilihan kendala akan otomatis muncul.";
                    return $this->sendMessage($chatId, $msg);
                }

                // KLIK TOMBOL KENDALA INSTAN
                if (str_starts_with($data, 'btnK-')) {
                    $cb_parts = explode('-', $data);
                    $id_sls = $cb_parts[1];
                    $bobotBaru = $cb_parts[2];
                    $idx = $cb_parts[3];

                    $alasanBaru = $this->alasan_templates[$bobotBaru][$idx] ?? 'Kendala Lainnya';
                    
                    $wilayah = DB::table('wilayahs')->where('id_sub_sls', $id_sls)->first();
                    if($wilayah) {
                        $waktuLapor = now()->format('d/m H:i'); 
                        $teksBaru = "[$waktuLapor] B{$bobotBaru}: {$alasanBaru}";
                        $keteranganGabungan = $wilayah->keterangan_kendala 
                            ? $wilayah->keterangan_kendala . "\n" . $teksBaru 
                            : $teksBaru;

                        DB::table('wilayahs')->where('id_sub_sls', $id_sls)->update([
                            'bobot_kendala' => $bobotBaru, 
                            'keterangan_kendala' => $keteranganGabungan,
                            'updated_at' => now()
                        ]);

                        $lokasi = "📍 *Wilayah:* {$wilayah->nama_kab}, Kec. {$wilayah->nama_kec}, {$wilayah->nama_sls}";
                        
                        $this->sendRequest('editMessageText', [
                            'chat_id' => $chatId,
                            'message_id' => $callback['message']['message_id'],
                            'text' => "✅ *LAPORAN KENDALA DITERIMA*\n{$lokasi}\n📝 *Kendala:* {$alasanBaru}\n📊 *Progres Saat Ini:* {$wilayah->selesai}/{$wilayah->muatan}",
                            'parse_mode' => 'Markdown'
                        ]);
                    }
                    return response()->json(['status' => 'ok']);
                }

                return response()->json(['status' => 'ok']);
            }

            if (isset($update['message']['text'])) {
                $chatId = $update['message']['chat']['id'];
                $text = trim($update['message']['text']);

                if (str_starts_with($text, '/start')) {
                    return $this->sendStartMenu($chatId);
                }

                $parts = explode('-', $text);
                $tipe = strtoupper(preg_replace('/[^A-Z]/', '', $parts[0] ?? ''));

                if ($tipe !== 'P' && $tipe !== 'K') {
                    return $this->sendMessage($chatId, "❌ *Format Tidak Dikenali*\nGunakan awalan *P* untuk Progres atau *K* untuk Kendala.");
                }

                $id_sls = preg_replace('/[^0-9]/', '', $parts[1] ?? '');

                if (strlen($id_sls) < 15) {
                    return $this->sendMessage($chatId, "❌ *ID SLS Tidak Valid*\nPastikan ID berjumlah 16 digit.");
                }

                $wilayah = DB::table('wilayahs')->where('id_sub_sls', $id_sls)->first();
                if (!$wilayah) {
                    return $this->sendMessage($chatId, "❌ *ID SLS Tidak Terdaftar*\nID tidak ditemukan dalam data.");
                }

                $lokasi = "📍 *Wilayah:* {$wilayah->nama_kab}, Kec. {$wilayah->nama_kec}, {$wilayah->nama_sls}";

                // --- LOGIKA KENDALA ---
                if ($tipe === 'K') {
                    if (count($parts) > 2) {
                        return $this->sendMessage($chatId, "❌ *Format Kendala Salah*\nCukup ketik `K-{$id_sls}` saja.");
                    }

                    $keyboard = ['inline_keyboard' => []];
                    foreach ($this->alasan_templates as $bobot => $list_alasan) {
                        foreach ($list_alasan as $idx => $teks_alasan) {
                                $keyboard['inline_keyboard'][] = [
                                    ['text' => "[B{$bobot}] {$teks_alasan}", 'callback_data' => "btnK-{$id_sls}-{$bobot}-{$idx}"]
                                ];
                        }
                        if (!empty($row)) { $keyboard['inline_keyboard'][] = $row; }
                    }
                    
                    return $this->sendRequest('sendMessage', [
                        'chat_id' => $chatId,
                        'text' => "⚠️ *PILIH KENDALA UNTUK SLS INI*\n{$lokasi}\n\nSilakan klik tombol di bawah:",
                        'reply_markup' => json_encode($keyboard),
                        'parse_mode' => 'Markdown'
                    ]);
                }
                
                // --- LOGIKA PROGRES
                else if ($tipe === 'P') {
                    if (count($parts) !== 4) {
                        return $this->sendMessage($chatId, "❌ *Format Progres Salah*\nFormat: `P-ID-Selesai-Diperiksa`.");
                    }

                    $selesai_raw = trim($parts[2]);
                    $diperiksa_raw = trim($parts[3]);

                    if (!ctype_digit($selesai_raw) || !ctype_digit($diperiksa_raw)) {
                        return $this->sendMessage($chatId, "❌ *Data Mengandung Typo*\nNilai harus berupa angka murni.");
                    }

                    $selesai = (int) $selesai_raw;
                    $diperiksa = (int) $diperiksa_raw;

                    // VALIDASI AGAR TIDAK MELEBIHI MUATAN
                    if ($selesai > $wilayah->muatan || $diperiksa > $wilayah->muatan) {
                        return $this->sendMessage($chatId, "❌ *Angka Melebihi Target*\nJumlah Selesai ({$selesai}) atau Diperiksa ({$diperiksa}) tidak boleh melebihi total muatan wilayah ({$wilayah->muatan}).\n\nMohon periksa kembali data Anda.");
                    }

                    DB::table('wilayahs')->where('id_sub_sls', $id_sls)->update([
                        'selesai' => $selesai,
                        'diperiksa' => $diperiksa,
                        'updated_at' => now()
                    ]);

                    return $this->sendMessage($chatId, "✅ *LAPORAN PROGRES DITERIMA*\n{$lokasi}\n📊 *Capaian:* {$selesai} / {$wilayah->muatan}");
                }
            }

        } catch (\Exception $e) {
            return $this->sendMessage($chatId, "⚠️ *Gangguan:* " . $e->getMessage());
        }
        return response()->json(['status' => 'ok']);
    }

    private function sendStartMenu($chatId) {
        $keyboard = ['inline_keyboard' => [[
            ['text' => '📈 Lapor Progres', 'callback_data' => 'info_progres'],
            ['text' => '⚠️ Lapor Kendala', 'callback_data' => 'info_kendala']
        ]]];
        return $this->sendRequest('sendMessage', [
            'chat_id' => $chatId,
            'text' => "Selamat Datang di Bot Pelaporan Sensus Ekonomi BPS Sumsel.\nSilakan pilih kategori laporan Anda:",
            'reply_markup' => json_encode($keyboard)
        ]);
    }

    private function sendMessage($chatId, $text) {
        return $this->sendRequest('sendMessage', ['chat_id' => $chatId, 'text' => $text, 'parse_mode' => 'Markdown']);
    }

    private function sendRequest($method, $params) {
        $url = "https://api.telegram.org/bot" . env('TELEGRAM_BOT_TOKEN') . "/" . $method;
        return Http::post($url, $params);
    }
}