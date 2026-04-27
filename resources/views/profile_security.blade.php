<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Profil & Keamanan | InfraSPH</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --brand-orange: #ff5900;
            --brand-orange-dark: #e14f00;
            --page-bg: #fff8f4;
            --text-dark: #1f2937;
            --muted: #64748b;
            --border: #f2e7df;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { min-height: 100vh; font-family: 'Inter', sans-serif; background: var(--page-bg); color: var(--text-dark); }
        .content-area { margin-left: 320px; min-height: 100vh; padding: 2rem 1.6rem 2.8rem; transition: margin-left 0.28s ease, width 0.28s ease; }
        .app-shell.sidebar-collapsed .content-area { margin-left: 88px; }
        .page-shell { display: grid; gap: 1.2rem; }
        .hero-card {
            background: linear-gradient(135deg, rgba(255, 89, 0, 0.1), rgba(255, 89, 0, 0.03));
            border: 1px solid rgba(255, 89, 0, 0.12);
            border-radius: 28px;
            padding: 1.65rem;
        }
        .eyebrow { display: inline-flex; gap: 0.45rem; align-items: center; padding: 0.45rem 0.8rem; border-radius: 999px; background: rgba(255, 89, 0, 0.12); color: var(--brand-orange); font-size: 0.82rem; font-weight: 800; margin-bottom: 0.95rem; }
        .hero-title { font-size: clamp(1.7rem, 2.8vw, 2.45rem); color: var(--brand-orange); line-height: 1.1; margin-bottom: 0.6rem; }
        .hero-subtitle { color: #5b6472; line-height: 1.7; max-width: 760px; }
        .alert { border-radius: 18px; padding: 0.95rem 1rem; border: 1px solid; font-weight: 700; }
        .alert.success { background: #ecfdf3; border-color: #bbf7d0; color: #166534; }
        .alert.error { background: #fff1f0; border-color: #fecaca; color: #b91c1c; }
        .profile-grid { display: grid; grid-template-columns: minmax(0, 1.08fr) minmax(320px, 0.92fr); gap: 1.2rem; align-items: start; }
        .panel-card { background: #fff; border: 1px solid var(--border); border-radius: 24px; padding: 1.25rem; box-shadow: 0 18px 38px -28px rgba(31, 41, 55, 0.24); }
        .panel-title { color: var(--brand-orange); font-size: 1.06rem; font-weight: 800; margin-bottom: 0.85rem; }
        .panel-copy { color: var(--muted); line-height: 1.65; font-size: 0.92rem; margin-bottom: 1rem; }
        .field-grid { display: grid; gap: 0.95rem; }
        .field-grid.two { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        label { display: block; font-size: 0.84rem; color: #334155; font-weight: 800; margin-bottom: 0.45rem; }
        input {
            width: 100%;
            border: 1px solid #eadbd2;
            border-radius: 15px;
            padding: 0.82rem 0.9rem;
            font: inherit;
            color: var(--text-dark);
            outline: none;
            background: #fff;
        }
        input[readonly] { background: #fff8f4; color: #64748b; }
        input:focus { border-color: rgba(255, 89, 0, 0.72); box-shadow: 0 0 0 3px rgba(255, 89, 0, 0.13); }
        .btn {
            border: 0;
            border-radius: 16px;
            background: linear-gradient(100deg, #f97316, #fd7010);
            color: #fff;
            padding: 0.85rem 1rem;
            font: inherit;
            font-weight: 800;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        .btn.secondary { background: #fff7ed; color: #9a3412; border: 1px solid #fed7aa; }
        .form-actions { margin-top: 1rem; display: flex; gap: 0.7rem; flex-wrap: wrap; }
        .readonly-list { display: grid; gap: 0.75rem; }
        .readonly-item { border: 1px solid #f1ddd1; border-radius: 18px; padding: 0.95rem; background: #fffaf7; }
        .readonly-label { color: var(--muted); font-size: 0.78rem; font-weight: 800; text-transform: uppercase; margin-bottom: 0.35rem; }
        .readonly-value { font-weight: 800; overflow-wrap: anywhere; }
        .assignment-list { display: grid; gap: 0.75rem; }
        .assignment-item { border: 1px solid #f1ddd1; border-radius: 18px; padding: 0.95rem; display: grid; gap: 0.35rem; }
        .assignment-name { font-weight: 800; color: #1f2937; }
        .assignment-meta { color: var(--muted); font-size: 0.86rem; line-height: 1.5; }
        .status-row { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 1rem; border-radius: 18px; background: #fff8f4; border: 1px solid #f1ddd1; }
        .badge { border-radius: 999px; padding: 0.4rem 0.7rem; font-weight: 800; font-size: 0.8rem; white-space: nowrap; }
        .badge.on { background: #dcfce7; color: #166534; }
        .badge.off { background: #f1f5f9; color: #475569; }
        .switch-line { display: flex; align-items: center; gap: 0.65rem; margin-top: 1rem; color: #334155; font-weight: 800; }
        .switch-line input { width: 20px; height: 20px; }
        @media (max-width: 980px) { .profile-grid { grid-template-columns: 1fr; } }
        @media (max-width: 860px) { .content-area { margin-left: 0; padding: 1.2rem 1rem 2rem; } .app-shell.sidebar-collapsed .content-area { margin-left: 0; } }
        @media (max-width: 640px) { .field-grid.two { grid-template-columns: 1fr; } .hero-card, .panel-card { border-radius: 20px; padding: 1.1rem; } }
    </style>
</head>
<body>
<div class="app-shell" id="appShell">
    @include('header')

    <main class="content-area">
        <div class="page-shell">
            <section class="hero-card">
                <div class="eyebrow"><i class="bi bi-person-gear"></i> Profil & Keamanan</div>
                <h1 class="hero-title">Kelola akun {{ $user['nama'] ?? 'pengguna' }}.</h1>
                <p class="hero-subtitle">Profil berisi identitas akun, sedangkan keamanan berisi perubahan password dan OTP email untuk login dua langkah.</p>
            </section>

            @if (session('success'))
                <div class="alert success">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="alert error">{{ session('error') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert error">{{ $errors->first() }}</div>
            @endif

            <section class="profile-grid">
                <div class="panel-card" id="profil">
                    <h2 class="panel-title">Profil</h2>

                    @if ($canEditIdentity)
                        <p class="panel-copy">Wali kelas dapat memperbarui nama dan email. Penugasan ruangan ditampilkan terpisah agar tidak tertukar dengan identitas akun.</p>
                        <form action="{{ route('profile.identity.update') }}" method="POST">
                            @csrf
                            <div class="field-grid two">
                                <div>
                                    <label for="nama">Nama</label>
                                    <input id="nama" name="nama" type="text" value="{{ old('nama', $user['nama'] ?? '') }}" required>
                                </div>
                                <div>
                                    <label for="email">Email</label>
                                    <input id="email" name="email" type="email" value="{{ old('email', $user['email'] ?? '') }}" required>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn"><i class="bi bi-check2-circle"></i> Simpan Profil</button>
                            </div>
                        </form>
                    @else
                        <p class="panel-copy">Untuk kepala sekolah dan superadmin, nama dan email ditampilkan sebagai informasi akun. Perubahan yang tersedia dari halaman ini hanya password dan OTP.</p>
                        <div class="readonly-list">
                            <div class="readonly-item">
                                <div class="readonly-label">Nama</div>
                                <div class="readonly-value">{{ $user['nama'] ?? '-' }}</div>
                            </div>
                            <div class="readonly-item">
                                <div class="readonly-label">Email</div>
                                <div class="readonly-value">{{ $user['email'] ?? 'Belum diisi' }}</div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="panel-card" id="ubah-password">
                    <h2 class="panel-title">Ubah Password</h2>
                    <p class="panel-copy">Gunakan password baru minimal 6 karakter. Nama dan email tidak ikut berubah saat password diperbarui.</p>
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
                </div>

                @if ($canEditIdentity)
                    <div class="panel-card">
                        <h2 class="panel-title">Penugasan Ruangan</h2>
                        <p class="panel-copy">Bagian ini menggantikan istilah “kelas yang diampu” agar cocok untuk kelas, laboratorium, atau ruangan lain.</p>
                        <div class="assignment-list">
                            @forelse ($assignments as $assignment)
                                <div class="assignment-item">
                                    <div class="assignment-name">{{ $assignment['nama_ruangan'] }} ({{ $assignment['kode_ruangan'] }})</div>
                                    <div class="assignment-meta">{{ $assignment['jenis_ruangan'] }} · {{ $assignment['peran_ruangan'] }}</div>
                                </div>
                            @empty
                                <div class="assignment-item">
                                    <div class="assignment-name">Belum ada penugasan ruangan aktif.</div>
                                    <div class="assignment-meta">Hubungi superadmin untuk menambahkan penugasan.</div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endif

                <div class="panel-card">
                    <h2 class="panel-title">Keamanan</h2>
                    <p class="panel-copy">OTP email berada di bagian keamanan karena berpengaruh langsung ke proses login. Saat aktif, login password akan meminta kode OTP sebelum masuk dashboard.</p>

                    <div class="status-row">
                        <div>
                            <strong>Verifikasi Dua Langkah (OTP)</strong>
                            <div class="panel-copy" style="margin: 0.35rem 0 0;">Kode OTP dikirim ke email akun saat login.</div>
                        </div>
                        <span class="badge {{ ($user['otp_enabled'] ?? false) ? 'on' : 'off' }}">
                            {{ ($user['otp_enabled'] ?? false) ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                    </div>

                    <form action="{{ route('profile.otp.update') }}" method="POST">
                        @csrf
                        <label class="switch-line">
                            <input type="checkbox" name="otp_enabled" value="1" {{ ($user['otp_enabled'] ?? false) ? 'checked' : '' }}>
                            Aktifkan OTP email saat login
                        </label>
                        <div class="form-actions">
                            <button type="submit" class="btn secondary"><i class="bi bi-envelope-lock"></i> Simpan Keamanan</button>
                        </div>
                    </form>

                    <div class="form-actions">
                        <a href="{{ route('login.google.redirect') }}" class="btn secondary"><i class="bi bi-google"></i> Hubungkan Login Google</a>
                    </div>
                </div>
            </section>
        </div>
    </main>
</div>
</body>
</html>
