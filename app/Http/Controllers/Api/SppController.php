<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Snap;
use Midtrans\Config;

class SppController extends Controller
{
    public function updateStatus(Request $request)
    {
        try {
            Log::info('🔥 updateStatus dipanggil dengan:', $request->all());

            $request->validate([
                'id_spp' => 'required|integer|exists:tb_spp,id_spp',
            ]);

            $updated = DB::table('tb_spp')
                ->where('id_spp', $request->id_spp)
                ->update(['status_bayar' => 'Lunas']);

            if ($updated) {
                Log::info("✅ Status berhasil diupdate untuk id_spp: {$request->id_spp}");
                return response()->json(['status' => 'success', 'message' => 'Status berhasil diupdate']);
            }

            Log::error("❌ Gagal update status untuk id_spp: {$request->id_spp}");
            return response()->json(['status' => 'error', 'message' => 'Gagal update status, id_spp tidak ditemukan atau sudah Lunas'], 404);
        } catch (\Exception $e) {
            Log::error('❌ Exception updateStatus: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan pada server', 'detail' => $e->getMessage()], 500);
        }
    }



    public function createSnap(Request $request)
    {
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = false;
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $request->validate([
            'id_spp' => 'required|integer',
            'nama' => 'required|string',
        ]);

        // Ambil data SPP dari Supabase
        $spp = DB::table('tb_spp')->where('id_spp', $request->id_spp)->first();

        if (!$spp) {
            return response()->json(['error' => 'Tagihan tidak ditemukan'], 404);
        }

        $params = [
            'transaction_details' => [
                'order_id' => 'SPP-' . $spp->id_spp . '-' . time(),
                'gross_amount' => $spp->jumlah_tagihan,
            ],
            'customer_details' => [
                'first_name' => $request->nama,
                'email' => $request->email,
            ],
            'callbacks' => [
                'finish' => route('midtrans.finishSpp'), // Akan redirect ke frontend
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            return response()->json(['snap_token' => $snapToken]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

   public function finishSpp(Request $request)
{
    // Redirect ke halaman tagihanSPP di frontend (web)
    return redirect('https://ab01-114-122-70-45.ngrok-free.app/tagihanSPP');
}
}
