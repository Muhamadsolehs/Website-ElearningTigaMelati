<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Selesai</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            text-align: center;
            padding: 40px 20px;
            background-color: #f4f4f9;
        }

        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            display: inline-block;
        }

        h1 {
            color: #28a745;
        }

        p {
            color: #555;
        }

        .order-id {
            font-size: 0.9em;
            color: #777;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Pembayaran Selesai</h1>
        <p>Anda bisa menutup halaman ini dan kembali ke aplikasi.</p>
        @if(isset($order_id))
        <p class="order-id">Order ID: {{ $order_id }}</p>
        @endif
    </div>
</body>

</html>