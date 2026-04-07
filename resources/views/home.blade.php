<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: white;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .home-card {
            width: 100%;
            max-width: 420px;
            padding: 2rem;
            border-radius: 18px;
            background: linear-gradient(180deg, #ff9f00 0%, #ff8300 100%);
            color: white;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
        }

        .home-card h1 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .home-card p {
            margin-bottom: 1rem;
            opacity: 0.95;
        }

        .home-link {
            color: white;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="home-card text-center">
        <h1>Home</h1>
        @auth
            <p>Selamat datang, {{ auth()->user()->name }}!</p>
            <p>Data registrasi Anda sudah tersimpan ke tabel users.</p>
        @else
            <p>Selamat datang! Data registrasi Anda sudah tersimpan ke tabel users.</p>
        @endauth
        <p>Silakan lanjutkan ke halaman lain atau logout jika sudah selesai.</p>
        <a href="{{ route('login') }}" class="home-link">Kembali ke Login</a>
    </div>
</body>
</html>