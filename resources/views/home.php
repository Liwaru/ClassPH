<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(255, 159, 0, 0.18), transparent 30%),
                radial-gradient(circle at bottom right, rgba(255, 131, 0, 0.16), transparent 24%),
                #fff8f1;
        }

        .dashboard-card {
            max-width: 680px;
            border: none;
            border-radius: 24px;
            background: white;
            box-shadow: 0 24px 60px rgba(170, 90, 0, 0.12);
        }

        .dashboard-badge {
            display: inline-block;
            padding: 0.45rem 0.85rem;
            border-radius: 999px;
            background: #fff1da;
            color: #b85b00;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container min-vh-100 d-flex align-items-center justify-content-center py-5">
        <div class="dashboard-card p-4 p-md-5 w-100">
            <span class="dashboard-badge mb-3">Dashboard</span>
            <h1 class="fw-bold mb-3">Selamat datang, {{ auth()->user()->name }}!</h1>
            <p class="text-secondary mb-2">Email Anda sudah terverifikasi dan akun sudah aktif.</p>
            <p class="text-secondary mb-0">Sekarang Anda bisa melanjutkan menggunakan aplikasi ClassPH.</p>
        </div>
    </div>
</body>
</html>
