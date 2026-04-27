<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Profil | InfraSPH</title>
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

        .profile-card {
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

        .readonly-list {
            display: grid;
            gap: 0.9rem;
        }

        .readonly-item {
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 1.05rem 1.15rem;
            background: #fffaf7;
        }

        .readonly-label {
            color: var(--muted);
            font-size: 0.82rem;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 0.42rem;
        }

        .readonly-value {
            color: #0f172a;
            font-size: 1.02rem;
            font-weight: 800;
            overflow-wrap: anywhere;
        }

        .readonly-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .password-action {
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            border-radius: 999px;
            background: #fff1e8;
            border: 1px solid #ffd2bb;
            color: var(--brand-orange);
            padding: 0.55rem 0.8rem;
            font-size: 0.82rem;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
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

        .modal-backdrop {
            position: fixed;
            inset: 0 0 0 320px;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1.2rem;
            background: rgba(15, 23, 42, 0.36);
            z-index: 1300;
        }

        .app-shell.sidebar-collapsed .modal-backdrop {
            left: 88px;
        }

        .modal-backdrop.open {
            display: flex;
        }

        .password-modal {
            position: relative;
            width: min(100%, 460px);
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: 22px;
            padding: 1.2rem;
            box-shadow: 0 24px 60px -28px rgba(15, 23, 42, 0.55);
        }

        .modal-close {
            position: absolute;
            top: 0.9rem;
            right: 0.9rem;
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #ffd2bb;
            border-radius: 50%;
            background: #fff7ed;
            color: var(--brand-orange);
            cursor: pointer;
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
                flex-direction: column;
                align-items: flex-start;
            }

            .back-button {
                position: static;
                margin-bottom: 1rem;
            }

            .modal-backdrop,
            .app-shell.sidebar-collapsed .modal-backdrop {
                left: 0;
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

        <section class="profile-card">
            <h1 class="panel-title">Profil</h1>
            <p class="panel-copy">
                Untuk kepala sekolah dan superadmin, nama dan email ditampilkan sebagai informasi akun. Perubahan yang tersedia dari halaman ini hanya password dan OTP.
            </p>

            @if (session('success'))
                <div class="alert success">{{ session('success') }}</div>
            @endif

            <div class="readonly-list">
                <div class="readonly-item">
                    <div class="readonly-label">Nama</div>
                    <div class="readonly-value">{{ $user['nama'] ?? '-' }}</div>
                </div>
                <div class="readonly-item">
                    <div class="readonly-label">Email</div>
                    <div class="readonly-value">{{ $user['email'] ?? 'Belum diisi' }}</div>
                </div>
                <div class="readonly-item">
                    <div class="readonly-row">
                        <div>
                            <div class="readonly-label">Password</div>
                            <div class="readonly-value">Password tersimpan aman</div>
                        </div>
                        <button type="button" class="password-action" id="openPasswordModal">
                            <i class="bi bi-shield-lock"></i>
                            Ubah
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <div class="modal-backdrop" id="passwordModal" aria-hidden="true">
        <section class="password-modal" role="dialog" aria-modal="true" aria-labelledby="passwordModalTitle">
            <button type="button" class="modal-close" id="closePasswordModal" aria-label="Tutup popup">
                <i class="bi bi-x-lg"></i>
            </button>

            <h2 class="panel-title" id="passwordModalTitle">Ubah Password</h2>
            <p class="panel-copy">
                Gunakan password baru minimal 6 karakter. Nama dan email tidak ikut berubah saat password diperbarui.
            </p>

            @if ($errors->any())
                <div class="alert error">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('profile.password.update') }}" method="POST">
                @csrf
                <input type="hidden" name="password_context" value="profile_modal">
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
    </div>
</div>
<script>
    (function () {
        const modal = document.getElementById('passwordModal');
        const openButton = document.getElementById('openPasswordModal');
        const closeButton = document.getElementById('closePasswordModal');
        const firstInput = document.getElementById('current_password');
        const shouldOpen = new URLSearchParams(window.location.search).get('open') === 'password' || @json($errors->any());

        function openModal() {
            if (!modal) {
                return;
            }

            modal.classList.add('open');
            modal.setAttribute('aria-hidden', 'false');
            setTimeout(function () {
                firstInput?.focus();
            }, 50);
        }

        function closeModal() {
            if (!modal) {
                return;
            }

            modal.classList.remove('open');
            modal.setAttribute('aria-hidden', 'true');
        }

        openButton?.addEventListener('click', openModal);
        closeButton?.addEventListener('click', closeModal);

        modal?.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });

        if (shouldOpen) {
            openModal();
        }
    })();
</script>
</body>
</html>
