<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction; // Pastikan model Transaction ada dan benar
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // Tambahkan ini untuk logging error
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Mengatur konfigurasi Midtrans saat controller diinisialisasi.
     */

    public function finish(Request $request)
    {
        // Misal redirect ke halaman React Native WebView atau mobile screen
        return redirect('https://ab01-114-122-70-45.ngrok-free.app/bayarDaftarSucces'); // halaman sukses di React Native
    }
    public function __construct()
    {
        // Mengambil konfigurasi dari config/midtrans.php (yang mengambil dari .env)
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * Membuat transaksi baru dan mendapatkan Snap Token dari Midtrans.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createTransaction(Request $request)
    {
        // 1. Validasi input dari frontend. Jika gagal, Laravel otomatis mengirim JSON error.
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1000',
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        // 2. Membungkus semua logika utama dalam try-catch
        try {
            // Buat order ID yang unik untuk setiap transaksi
            $orderId = 'ORDER-' . uniqid();

            // Simpan data transaksi awal ke database
            $transaction = Transaction::create([
                'order_id' => $orderId,
                'amount' => $validated['amount'],
                'customer_name' => $validated['nama_lengkap'],
                'status' => 'pending', // Status awal
            ]);

            // Siapkan parameter yang akan dikirim ke Midtrans
            $params = [
                'transaction_details' => [
                    'order_id' => $transaction->order_id,
                    'gross_amount' => $transaction->amount,
                ],
                'customer_details' => [
                    'first_name' => $validated['nama_lengkap'],
                    'email' => $validated['email'],
                ],
                'callbacks' => [
                    'finish' => route('midtrans.finish'), // halaman HTML biasa
                ],

            ];

            // Minta Snap Token dari Midtrans
            $snapToken = Snap::getSnapToken($params);

            // Simpan Snap Token ke database untuk referensi
            $transaction->payment_token = $snapToken;
            $transaction->save();

            // Kirim respons sukses ke frontend
            return response()->json([
                'status' => 'success',
                'snap_token' => $snapToken,
                'order_id' => $transaction->order_id
            ]);
        } catch (\Exception $e) {
            // Jika terjadi error (dari database atau Midtrans), catat error dan kirim respons gagal
            Log::error('Payment creation failed: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memproses pembayaran. Silakan coba lagi.'
                // 'detail' => $e->getMessage() // Jangan kirim detail error ke frontend di production
            ], 500);
        }
    }

    /**
     * Menangani notifikasi webhook dari Midtrans.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleWebhook(Request $request)
    {
        try {
            // Terima notifikasi dari Midtrans
            $notification = new Notification();

            // Lakukan verifikasi signature key (SANGAT PENTING untuk keamanan)
            $orderId = $notification->order_id;
            $statusCode = $notification->status_code;
            $grossAmount = $notification->gross_amount;
            $serverKey = config('midtrans.server_key');
            $signatureKey = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

            if ($notification->signature_key !== $signatureKey) {
                // Jika signature tidak cocok, ini mungkin request palsu.
                return response()->json(['message' => 'Invalid signature.'], 403);
            }

            // Cari transaksi di database berdasarkan order_id
            if (strpos($orderId, 'SPP-') === 0) {
                // Ini pembayaran SPP, update tb_spp
                $id_spp = intval(explode('-', $orderId)[1]);
                $transactionStatus = $notification->transaction_status;
                $fraudStatus = $notification->fraud_status;
                if (($transactionStatus == 'capture' || $transactionStatus == 'settlement') && $fraudStatus == 'accept') {
                    DB::table('tb_spp')->where('id_spp', $id_spp)->update(['status_bayar' => 'Lunas']);
                }
                // Tidak perlu return error jika bukan transaksi utama
                return response()->json(['message' => 'SPP status updated.']);
            }
            $transaction = Transaction::where('order_id', $orderId)->first();
            if (!$transaction) {
                return response()->json(['message' => 'Transaction not found.'], 440);
            }

            // Update status transaksi berdasarkan notifikasi
            $transactionStatus = $notification->transaction_status;
            $fraudStatus = $notification->fraud_status;

            if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
                if ($fraudStatus == 'accept') {
                    // Pembayaran berhasil dan dana sudah diterima
                    $transaction->status = 'success';
                }
            } else if ($transactionStatus == 'pending') {
                // Pembayaran masih menunggu (misal: transfer bank belum dibayar)
                $transaction->status = 'pending';
            } else {
                // Status lainnya dianggap gagal (expire, cancel, deny)
                $transaction->status = 'failed';
            }

            $transaction->save();

            // Beri respons 200 OK ke Midtrans agar tidak mengirim notifikasi berulang
            return response()->json(['message' => 'Webhook successfully handled.']);
        } catch (\Exception $e) {
            Log::error('Webhook handling failed: ' . $e->getMessage());
            return response()->json(['message' => 'Internal server error during webhook processing.'], 500);
        }
    }
}
