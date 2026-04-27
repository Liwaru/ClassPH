<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Keamanan | InfraSPH</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --brand-orange: #ff5900;
            --brand-orange-dark: #c2410c;
            --page-bg: #fff8f4;
            --text-dark: #1f2937;
            --muted: #53627a;
            --border: #f1ddd1;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            background: var(--page-bg);
            color: var(--text-dark);
        }

        .content-area {
            position: relative;
            margin-left: 320px;
            min-height: 100vh;
            padding: 2rem 1.6rem 2.8rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: margin-left 0.28s ease;
        }

        .app-shell.sidebar-collapsed .content-area {
            margin-left: 88px;
        }

        .back-button {
            position: absolute;
            top: 2rem;
            left: 1.6rem;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            border: 1px solid #ffd2bb;
            border-radius: 999px;
            background: #fff7ed;
            color: var(--brand-orange);
            padding: 0.62rem 0.9rem;
            font-size: 0.88rem;
            font-weight: 800;
            text-decoration: none;
        }

        .back-button:hover {
            background: #fff1e8;
        }

        .security-card {
            width: min(100%, 860px);
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 1.85rem;
            box-shadow: 0 18px 38px -28px rgba(31, 41, 55, 0.24);
        }

        .panel-title {
            color: var(--brand-orange);
            font-size: 1.15rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }

        .panel-copy {
            color: var(--muted);
            line-height: 1.75;
            font-size: 1rem;
            margin-bottom: 1.25rem;
        }

        .alert {
            border-radius: 16px;
            padding: 0.85rem 0.95rem;
            border: 1px solid;
            font-size: 0.88rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .alert.success {
            background: #ecfdf3;
            border-color: #bbf7d0;
            color: #166534;
        }

        .alert.error {
            background: #fff1f0;
            border-color: #fecaca;
            color: #b91c1c;
        }

        .status-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem;
            border-radius: 18px;
            background: #fffaf7;
            border: 1px solid var(--border);
            margin-bottom: 1rem;
        }

        .status-title {
            color: #0f172a;
            font-weight: 800;
            margin-bottom: 0.35rem;
        }

        .status-copy {
            color: var(--muted);
            font-size: 0.9rem;
            line-height: 1.45;
        }

        .badge {
            flex-shrink: 0;
            border-radius: 999px;
            padding: 0.45rem 0.72rem;
            font-weight: 800;
            font-size: 0.8rem;
            white-space: nowrap;
        }

        .badge.on {
            background: #dcfce7;
            color: #166534;
        }

        .badge.off {
            background: #eef2f7;
            color: #334155;
        }

        .switch-line {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            color: #0f172a;
            font-weight: 800;
            font-size: 0.92rem;
            margin-bottom: 1rem;
        }

        .switch-line input {
            width: 18px;
            height: 18px;
            accent-color: var(--brand-orange);
        }

        .actions {
            display: grid;
            justify-items: start;
            gap: 0.9rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border-radius: 16px;
            border: 1px solid #fed7aa;
            background: #fff7ed;
            color: var(--brand-orange-dark);
            padding: 0.85rem 1rem;
            font: inherit;
            font-weight: 800;
            cursor: pointer;
            text-decoration: none;
        }

        .btn.google {
            color: #b83200;
        }

        .btn.google i {
            font-size: 1.08rem;
        }

        @media (max-width: 860px) {
            .content-area,
            .app-shell.sidebar-collapsed .content-area {
                margin-left: 0;
                min-height: auto;
                padding: 1.2rem 1rem 2rem;
                flex-direction: column;
                align-items: flex-start;
            }

            .back-button {
                position: static;
                margin-bottom: 1rem;
            }
        }

        @media (max-width: 560px) {
            .security-card {
                border-radius: 20px;
                padding: 1rem;
            }

            .status-row {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
<div class="app-shell" id="appShell">
    @include('header')

    <main class="content-area">
        <a class="back-button" href="{{ route('dashboard') }}" onclick="if (window.history.length > 1) { event.preventDefault(); window.history.back(); }">
            <i class="bi bi-arrow-left"></i>
            Kembali
        </a>

        <section class="security-card">
            <h1 class="panel-title">Keamanan</h1>
            <p class="panel-copy">
                OTP email berada di bagian keamanan karena berpengaruh langsung ke proses login. Saat aktif, login password akan meminta kode OTP sebelum masuk dashboard.
            </p>

            @if (session('success'))
                <div class="alert success">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="alert error">{{ session('error') }}</div>
            @endif

            <div class="status-row">
                <div>
                    <div class="status-title">Verifikasi Dua Langkah (OTP)</div>
                    <div class="status-copy">Kode OTP dikirim ke email akun saat login.</div>
                </div>
                <span class="badge {{ ($user['otp_enabled'] ?? false) ? 'on' : 'off' }}">
                    {{ ($user['otp_enabled'] ?? false) ? 'Aktif' : 'Tidak Aktif' }}
                </span>
            </div>

            <form action="{{ route('profile.otp.update') }}" method="POST">
                @csrf
                <input type="hidden" name="otp_context" value="security_page">
                <label class="switch-line">
                    <input type="checkbox" name="otp_enabled" value="1" {{ ($user['otp_enabled'] ?? false) ? 'checked' : '' }}>
                    Aktifkan OTP email saat login
                </label>
                <div class="actions">
                    <button type="submit" class="btn">Simpan Keamanan</button>
                    <a href="{{ route('login.google.redirect') }}" class="btn google">
                        <i class="bi bi-google"></i>
                        Hubungkan Login Google
                    </a>
                </div>
            </form>
        </section>
    </main>
</div>
</body>
</html>
