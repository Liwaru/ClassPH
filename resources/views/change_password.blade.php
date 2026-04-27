<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ubah Password | InfraSPH</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --brand-orange: #ff5900;
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

        .password-card {
            width: min(100%, 460px);
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: 22px;
            padding: 1.2rem;
            box-shadow: 0 18px 38px -28px rgba(31, 41, 55, 0.24);
        }

        .panel-title {
            color: var(--brand-orange);
            font-size: 1.04rem;
            font-weight: 800;
            margin-bottom: 0.8rem;
        }

        .panel-copy {
            color: var(--muted);
            line-height: 1.6;
            font-size: 0.92rem;
            margin-bottom: 1rem;
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

        .field-grid {
            display: grid;
            gap: 0.9rem;
        }

        label {
            display: block;
            color: #0f172a;
            font-size: 0.84rem;
            font-weight: 800;
            margin-bottom: 0.45rem;
        }

        input {
            width: 100%;
            min-height: 43px;
            border: 1px solid #ead2c4;
            border-radius: 15px;
            padding: 0.82rem 0.9rem;
            background: #fff;
            color: var(--text-dark);
            font: inherit;
            outline: none;
        }

        input:focus {
            border-color: rgba(255, 89, 0, 0.72);
            box-shadow: 0 0 0 3px rgba(255, 89, 0, 0.13);
        }

        .form-actions {
            margin-top: 1rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border: 0;
            border-radius: 16px;
            background: linear-gradient(100deg, #f97316, #fd7010);
            color: #ffffff;
            padding: 0.85rem 1rem;
            font: inherit;
            font-weight: 800;
            cursor: pointer;
        }

        @media (max-width: 860px) {
            .content-area,
            .app-shell.sidebar-collapsed .content-area {
                margin-left: 0;
                min-height: auto;
                padding: 1.2rem 1rem 2rem;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
<div class="app-shell" id="appShell">
    @include('header')

    <main class="content-area">
        <section class="password-card">
            <h1 class="panel-title">Ubah Password</h1>
            <p class="panel-copy">
                Gunakan password baru minimal 6 karakter. Nama dan email tidak ikut berubah saat password diperbarui.
            </p>

            @if (session('success'))
                <div class="alert success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert error">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('profile.password.update') }}" method="POST">
                @csrf
                <div class="field-grid">
                    <div>
                        <label for="current_password">Password Lama</label>
                        <input id="current_password" name="current_password" type="password" autocomplete="current-password" required>
                    </div>
                    <div>
                        <label for="password">Password Baru</label>
                        <input id="password" name="password" type="password" autocomplete="new-password" required>
                    </div>
                    <div>
                        <label for="password_confirmation">Konfirmasi Password Baru</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn"><i class="bi bi-shield-check"></i> Ubah Password</button>
                </div>
            </form>
        </section>
    </main>
</div>
</body>
</html>
