<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Selesai</title>
    <script>
        // Coba kirim event ke WebView
        window.onload = function() {
            window.ReactNativeWebView?.postMessage(JSON.stringify({
                event: 'onSuccess',
                data: {
                    message: 'Selesai dari redirect Midtrans'
                }
            }));
        };
    </script>
</head>

<body>
    <h3>Pembayaran selesai.</h3>
    <p>Silakan kembali ke aplikasi.</p>
</body>

</html>