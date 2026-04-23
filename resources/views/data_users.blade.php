<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Data User | InfraSPH</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --brand-orange: #ff5900;
            --brand-orange-dark: #e14f00;
            --page-bg: #fff8f4;
            --panel-border: #f3e3db;
            --text-dark: #1f2937;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            background: var(--page-bg);
            color: var(--text-dark);
        }

        .superadmin-users-page {
            margin-left: 320px;
            width: calc(100% - 320px);
            min-height: 100vh;
            padding: 2rem 1.6rem 2.5rem;
            transition: margin-left 0.28s ease, width 0.28s ease, padding 0.28s ease;
        }

        .app-shell.sidebar-collapsed .superadmin-users-page {
            margin-left: 88px;
            width: calc(100% - 88px);
        }

        .page-shell { width: 100%; max-width: none; }

        .hero-card,
        .summary-card,
        .filter-card,
        .table-card,
        .success-banner,
        .error-banner,
        .modal-dialog {
            background: #ffffff;
            border: 1px solid var(--panel-border);
            border-radius: 28px;
            box-shadow: 0 18px 38px -28px rgba(31, 41, 55, 0.24);
        }

        .hero-card {
            padding: 1.65rem 1.7rem;
            margin-bottom: 1.15rem;
            background: linear-gradient(135deg, rgba(255, 89, 0, 0.09), rgba(255, 89, 0, 0.03));
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: flex-start;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.45rem 0.8rem;
            border-radius: 999px;
            background: rgba(255, 89, 0, 0.12);
            color: var(--brand-orange);
            font-size: 0.82rem;
            font-weight: 700;
            margin-bottom: 0.95rem;
        }

        .hero-title {
            font-size: clamp(1.9rem, 2.8vw, 2.55rem);
            color: var(--brand-orange);
            margin-bottom: 0.7rem;
            letter-spacing: -0.04em;
        }

        .hero-subtitle {
            color: #5b6472;
            line-height: 1.7;
            max-width: 820px;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            padding: 0.95rem 1.15rem;
            border-radius: 18px;
            border: none;
            text-decoration: none;
            cursor: pointer;
            font: inherit;
            font-size: 0.92rem;
            font-weight: 700;
        }

        .action-btn.primary {
            background: linear-gradient(135deg, #ff5900, #ff7b2f);
            color: #ffffff;
        }

        .action-btn.soft {
            background: #fff5ee;
            color: #b45309;
            border: 1px solid #ffd9c3;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.95rem;
            margin-bottom: 1.1rem;
        }

        .summary-card {
            padding: 1.05rem 1.1rem;
            border-radius: 22px;
        }

        .summary-card.is-accent {
            background: linear-gradient(135deg, #ff6a17, #ff5900);
            border-color: transparent;
        }

        .summary-label {
            color: #6b7280;
            font-size: 0.84rem;
            font-weight: 700;
            margin-bottom: 0.35rem;
        }

        .summary-value {
            color: #172033;
            font-size: 1.72rem;
            font-weight: 800;
            letter-spacing: -0.04em;
        }

        .summary-note {
            color: #7b8794;
            font-size: 0.84rem;
            margin-top: 0.3rem;
            line-height: 1.5;
        }

        .summary-card.is-accent .summary-label,
        .summary-card.is-accent .summary-value,
        .summary-card.is-accent .summary-note {
            color: #fffaf6;
        }

        .filter-card {
            padding: 1rem 1.05rem;
            margin-bottom: 1rem;
        }

        .filter-form {
            display: grid;
            grid-template-columns: minmax(0, 1.8fr) repeat(2, minmax(190px, 0.95fr)) auto;
            gap: 0.85rem;
            align-items: end;
        }

        .filter-field label,
        .field-group label {
            display: block;
            color: #6b7280;
            font-size: 0.8rem;
            font-weight: 700;
            margin-bottom: 0.45rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .filter-field input,
        .filter-field select,
        .field-group input,
        .field-group select {
            width: 100%;
            border: 1px solid #ecd8cb;
            border-radius: 16px;
            padding: 0.92rem 1rem;
            font: inherit;
            font-size: 0.94rem;
            color: #172033;
            background: #fffdfa;
            outline: none;
        }

        .filter-actions {
            display: flex;
            gap: 0.7rem;
            flex-wrap: wrap;
        }

        .filter-btn,
        .filter-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            min-width: 116px;
            border-radius: 16px;
            padding: 0.92rem 1rem;
            font-size: 0.9rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .filter-btn {
            border: none;
            background: linear-gradient(135deg, #ff5900, #ff7b2f);
            color: #ffffff;
        }

        .filter-link {
            border: 1px solid #ecd8cb;
            background: #fffdfa;
            color: #4b5563;
        }

        .feedback-stack {
            display: grid;
            gap: 0.85rem;
            margin-bottom: 1rem;
        }

        .success-banner,
        .error-banner {
            padding: 1rem 1.1rem;
            border-radius: 22px;
        }

        .success-banner {
            background: #f3fff5;
            border-color: #cdeed5;
            color: #166534;
        }

        .error-banner {
            background: #fff7f4;
            border-color: #ffd7c7;
            color: #c2410c;
        }

        .table-card {
            overflow: hidden;
        }

        .table-header {
            padding: 1.2rem 1.2rem 0.9rem;
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: center;
            border-bottom: 1px solid #f4e8e1;
        }

        .table-title {
            font-size: 1.14rem;
            font-weight: 800;
            color: #172033;
        }

        .table-subtitle {
            color: #7b8794;
            font-size: 0.9rem;
            margin-top: 0.2rem;
        }

        .table-header-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            flex-shrink: 0;
        }

        .table-wrap {
            overflow-x: visible;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            padding: 0.85rem 0.7rem;
            border-bottom: 1px solid #f6e7df;
            vertical-align: top;
            text-align: left;
        }

        th {
            color: #7b8794;
            font-size: 0.78rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            background: #fffaf7;
        }

        td {
            color: #172033;
            font-size: 0.88rem;
            word-break: break-word;
        }

        tbody tr:hover {
            background: #fffaf7;
        }

        .user-name {
            font-weight: 800;
            color: #172033;
            margin-bottom: 0.12rem;
            font-size: 0.96rem;
        }

        .user-meta {
            color: #7b8794;
            font-size: 0.78rem;
        }

        .pill,
        .mini-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            font-weight: 700;
            white-space: nowrap;
        }

        .pill {
            padding: 0.38rem 0.68rem;
            font-size: 0.72rem;
        }

        .mini-pill {
            padding: 0.24rem 0.5rem;
            font-size: 0.66rem;
        }

        .pill.student { background: #fff0e8; color: #c2410c; }
        .pill.teacher { background: #e9f5ff; color: #1d4ed8; }
        .pill.system { background: #fff5db; color: #a16207; }
        .pill.owner { background: #eefcf2; color: #15803d; }
        .pill.approved,
        .mini-pill.approved { background: #eaf8ef; color: #15803d; }
        .pill.muted,
        .mini-pill.muted { background: #f4f4f5; color: #52525b; }

        .assignment-stack {
            display: grid;
            gap: 0.55rem;
        }

        .assignment-item {
            padding: 0.58rem 0.65rem;
            border-radius: 16px;
            background: #fff8f4;
            border: 1px solid #f5e2d8;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 54px;
        }

        .assignment-room {
            font-weight: 700;
            font-size: 0.85rem;
            text-align: center;
        }

        .muted-text {
            color: #8a94a6;
        }

        .action-group {
            display: flex;
            gap: 0.45rem;
            flex-wrap: wrap;
        }

        .row-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            padding: 0.58rem 0.72rem;
            border-radius: 12px;
            border: 1px solid #edd9cc;
            background: #ffffff;
            color: #374151;
            font-size: 0.76rem;
            font-weight: 700;
            cursor: pointer;
        }

        .row-action.primary {
            background: #fff4ec;
            color: #b45309;
            border-color: #ffd9c3;
        }

        .empty-state {
            padding: 2.2rem 1.2rem;
            text-align: center;
            color: #7b8794;
        }

        .pagination-wrap {
            padding: 1rem 1.2rem 1.25rem;
        }

        .pagination {
            display: flex;
            flex-wrap: wrap;
            gap: 0.55rem;
            align-items: center;
        }

        .pagination-info {
            color: #7b8794;
            font-size: 0.88rem;
            margin-right: 0.35rem;
        }

        .pagination-link,
        .pagination-current {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 42px;
            height: 42px;
            padding: 0 0.9rem;
            border-radius: 14px;
            font-weight: 700;
        }

        .pagination-link {
            border: 1px solid #ecd8cb;
            background: #fffdfa;
            color: #4b5563;
            text-decoration: none;
        }

        .pagination-link.disabled {
            opacity: 0.45;
            pointer-events: none;
        }

        .pagination-current {
            background: linear-gradient(135deg, #ff5900, #ff7b2f);
            color: #ffffff;
        }

        .modal-shell {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1.2rem;
            background: rgba(15, 23, 42, 0.55);
            z-index: 90;
        }

        .modal-shell.is-open {
            display: flex;
        }

        .modal-dialog {
            width: min(720px, 100%);
            max-height: calc(100vh - 2.4rem);
            overflow-y: auto;
            padding: 1.2rem 1.2rem 1.15rem;
            border-radius: 28px;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .modal-title {
            font-size: 1.2rem;
            font-weight: 800;
            color: #172033;
        }

        .modal-subtitle {
            color: #7b8794;
            font-size: 0.88rem;
            margin-top: 0.22rem;
            line-height: 1.6;
        }

        .modal-close {
            border: 1px solid #ecd8cb;
            background: #fffdfa;
            color: #4b5563;
            width: 44px;
            height: 44px;
            border-radius: 14px;
            cursor: pointer;
            font-size: 1rem;
        }

        .modal-form {
            display: grid;
            gap: 1rem;
        }

        .field-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.9rem;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .submit-btn,
        .ghost-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border-radius: 16px;
            padding: 0.9rem 1.05rem;
            font: inherit;
            font-size: 0.92rem;
            font-weight: 700;
            cursor: pointer;
        }

        .submit-btn {
            border: none;
            background: linear-gradient(135deg, #ff5900, #ff7b2f);
            color: #ffffff;
        }

        .ghost-btn {
            border: 1px solid #ecd8cb;
            background: #fffdfa;
            color: #4b5563;
        }

        .help-box {
            padding: 0.95rem 1rem;
            border-radius: 18px;
            background: #fff8f4;
            border: 1px solid #f5e2d8;
            color: #6b7280;
            font-size: 0.88rem;
            line-height: 1.7;
        }

        @media (max-width: 1180px) {
            .summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .filter-form { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 860px) {
            .superadmin-users-page,
            .app-shell.sidebar-collapsed .superadmin-users-page {
                width: 100%;
                margin-left: 0;
                padding: 5.3rem 1rem 1.8rem;
            }

            .summary-grid,
            .field-grid,
            .filter-form {
                grid-template-columns: 1fr;
            }

            .table-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .table-header-actions {
                width: 100%;
            }

            .table-header-actions .action-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="app-shell" id="appShell">
        @include('header')

        <main class="superadmin-users-page">
            <div class="page-shell">
                <section class="hero-card">
                    <div>
                        <div class="eyebrow">{{ $dashboard['role_name'] ?? 'Pengelola Sistem' }}</div>
                        <h1 class="hero-title">Data User</h1>
                        <p class="hero-subtitle">Kelola akun, lihat role user, dan pantau keterhubungan user dengan ruangan melalui penugasan aktif maupun histori penugasan dalam satu halaman.</p>
                    </div>
                </section>

                <section class="summary-grid">
                    <article class="summary-card">
                        <div class="summary-label">Total User</div>
                        <div class="summary-value">{{ number_format($summary['total_user']) }}</div>
                        <div class="summary-note">Semua akun yang terdaftar di sistem</div>
                    </article>
                    <article class="summary-card is-accent">
                        <div class="summary-label">Ketua Kelas</div>
                        <div class="summary-value">{{ number_format($summary['ketua_kelas']) }}</div>
                        <div class="summary-note">User dengan role ketua kelas</div>
                    </article>
                    <article class="summary-card">
                        <div class="summary-label">Wali Kelas</div>
                        <div class="summary-value">{{ number_format($summary['wali_kelas']) }}</div>
                        <div class="summary-note">Akun yang bertugas verifikasi kelas</div>
                    </article>
                    <article class="summary-card">
                        <div class="summary-label">Penugasan Aktif</div>
                        <div class="summary-value">{{ number_format($summary['penugasan_aktif']) }}</div>
                        <div class="summary-note">Koneksi user ke ruangan yang masih aktif</div>
                    </article>
                </section>

                <section class="filter-card">
                    <form method="GET" action="{{ route('superadmin.users') }}" class="filter-form">
                        <div class="filter-field">
                            <label for="searchUser">Search Nama / NIS</label>
                            <input id="searchUser" type="text" name="q" value="{{ $filters['q'] }}" placeholder="Cari nama user atau NIS">
                        </div>
                        <div class="filter-field">
                            <label for="roleFilter">Filter Role</label>
                            <select id="roleFilter" name="role">
                                <option value="semua" @selected($filters['role'] === 'semua')>Semua Role</option>
                                @foreach ($roleOptions as $level => $label)
                                    <option value="{{ $level }}" @selected($filters['role'] === (string) $level)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-field">
                            <label for="roomTypeFilter">Jenis Ruangan</label>
                            <select id="roomTypeFilter" name="room_type">
                                <option value="semua" @selected($filters['room_type'] === 'semua')>Semua Jenis</option>
                                @foreach ($roomTypeOptions as $type)
                                    <option value="{{ $type }}" @selected($filters['room_type'] === $type)>{{ ucfirst($type) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-actions">
                            <button type="submit" class="filter-btn">
                                <i class="bi bi-funnel-fill"></i>
                                <span>Terapkan</span>
                            </button>
                            <a href="{{ route('superadmin.users') }}" class="filter-link">
                                <i class="bi bi-arrow-counterclockwise"></i>
                                <span>Reset</span>
                            </a>
                        </div>
                    </form>
                </section>

                @if (session('success') || session('error') || $errors->any())
                    <div class="feedback-stack">
                        @if (session('success'))
                            <div class="success-banner">{{ session('success') }}</div>
                        @endif

                        @if (session('error'))
                            <div class="error-banner">{{ session('error') }}</div>
                        @endif

                        @if ($errors->any())
                            <div class="error-banner">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                <section class="table-card">
                    <div class="table-header">
                        <div>
                            <div class="table-title">Manajemen User</div>
                            <div class="table-subtitle">Menampilkan {{ number_format($users->total()) }} user berdasarkan filter aktif.</div>
                        </div>
                        <div class="table-header-actions">
                            <button type="button" class="action-btn primary js-open-modal" data-modal="create-user">
                                <i class="bi bi-person-plus-fill"></i>
                                <span>Tambah User</span>
                            </button>
                        </div>
                    </div>

                    @if (count($userRows) === 0)
                        <div class="empty-state">Belum ada data user yang cocok dengan filter saat ini.</div>
                    @else
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>NIS</th>
                                        <th>Role</th>
                                        <th>Ruangan/Kelas</th>
                                        <th>Peran Ruangan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($userRows as $row)
                                        <tr>
                                            <td>
                                                <div class="user-name">{{ $row['nama'] }}</div>
                                                <div class="user-meta">
                                                    ID User: {{ $row['id_user'] }}
                                                </div>
                                            </td>
                                            <td>{{ $row['nis'] }}</td>
                                            <td>
                                                <span class="pill {{ $row['role_class'] }}">{{ $row['role_label'] }}</span>
                                            </td>
                                            <td>
                                                @if ($row['assignment_count'] === 0)
                                                    <span class="muted-text">Belum ada penugasan</span>
                                                @else
                                                    <div class="assignment-stack">
                                                        @foreach (array_slice($row['assignments'], 0, 2) as $assignment)
                                                            <div class="assignment-item">
                                                                <div class="assignment-room">{{ $assignment['nama_ruangan'] }}</div>
                                                            </div>
                                                        @endforeach
                                                        @if ($row['assignment_count'] > 2)
                                                            <div class="user-meta">+{{ $row['assignment_count'] - 2 }} penugasan lain</div>
                                                        @endif
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($row['assignment_count'] === 0)
                                                    <span class="muted-text">Belum ditentukan</span>
                                                @else
                                                    <div class="assignment-stack">
                                                        @foreach (array_slice($row['assignments'], 0, 2) as $assignment)
                                                            <div>{{ $assignment['peran_label'] }}</div>
                                                        @endforeach
                                                        @if ($row['assignment_count'] > 2)
                                                            <div class="user-meta">dan lainnya</div>
                                                        @endif
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="action-group">
                                                    <button type="button" class="row-action js-open-modal" data-modal="edit-user-{{ $row['id_user'] }}">
                                                        <i class="bi bi-pencil-square"></i>
                                                        <span>Edit</span>
                                                    </button>
                                                    <button type="button" class="row-action primary js-open-modal" data-modal="assignment-user-{{ $row['id_user'] }}">
                                                        <i class="bi bi-diagram-3-fill"></i>
                                                        <span>Atur Penugasan</span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if ($users->hasPages())
                            <div class="pagination-wrap">
                                <div class="pagination">
                                    <span class="pagination-info">Halaman {{ $users->currentPage() }} dari {{ $users->lastPage() }}</span>

                                    @if ($users->onFirstPage())
                                        <span class="pagination-link disabled"><i class="bi bi-chevron-left"></i></span>
                                    @else
                                        <a href="{{ $users->previousPageUrl() }}" class="pagination-link" aria-label="Halaman sebelumnya">
                                            <i class="bi bi-chevron-left"></i>
                                        </a>
                                    @endif

                                    @foreach ($users->getUrlRange(1, $users->lastPage()) as $page => $url)
                                        @if ($page === $users->currentPage())
                                            <span class="pagination-current">{{ $page }}</span>
                                        @else
                                            <a href="{{ $url }}" class="pagination-link">{{ $page }}</a>
                                        @endif
                                    @endforeach

                                    @if ($users->hasMorePages())
                                        <a href="{{ $users->nextPageUrl() }}" class="pagination-link" aria-label="Halaman berikutnya">
                                            <i class="bi bi-chevron-right"></i>
                                        </a>
                                    @else
                                        <span class="pagination-link disabled"><i class="bi bi-chevron-right"></i></span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endif
                </section>
            </div>
        </main>
    </div>

    <div class="modal-shell" id="modal-create-user" aria-hidden="true">
        <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="modalCreateUserTitle">
            <div class="modal-header">
                <div>
                    <div class="modal-title" id="modalCreateUserTitle">Tambah User</div>
                    <div class="modal-subtitle">Buat akun baru untuk ketua kelas, wali kelas, pengelola sistem, atau kepala sekolah.</div>
                </div>
                <button type="button" class="modal-close js-close-modal" aria-label="Tutup modal">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('superadmin.users.store') }}" class="modal-form">
                @csrf
                <input type="hidden" name="q" value="{{ $filters['q'] }}">
                <input type="hidden" name="role_filter" value="{{ $filters['role'] }}">
                <input type="hidden" name="room_type_filter" value="{{ $filters['room_type'] }}">

                <div class="field-grid">
                    <div class="field-group">
                        <label for="createName">Nama</label>
                        <input id="createName" type="text" name="nama" value="{{ session('modal') === 'create-user' ? old('nama') : '' }}" required>
                    </div>
                    <div class="field-group">
                        <label for="createNis">NIS</label>
                        <input id="createNis" type="text" name="nis" value="{{ session('modal') === 'create-user' ? old('nis') : '' }}" placeholder="Opsional">
                    </div>
                    <div class="field-group">
                        <label for="createRole">Role</label>
                        <select id="createRole" name="level" required>
                            <option value="">Pilih role</option>
                            @foreach ($roleOptions as $level => $label)
                                <option value="{{ $level }}" @selected(session('modal') === 'create-user' && old('level') === (string) $level)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field-group">
                        <label for="createPassword">Password</label>
                        <input id="createPassword" type="password" name="password" required>
                    </div>
                </div>

                <div class="help-box">Saran struktur: menu ini fokus ke akun, sedangkan hubungan user ke ruangan diatur lewat form penugasan. Jadi identitas user dan penugasan tetap terpisah, tapi masih nyaman dikelola dari satu halaman.</div>

                <div class="modal-actions">
                    <button type="button" class="ghost-btn js-close-modal">Batal</button>
                    <button type="submit" class="submit-btn">
                        <i class="bi bi-check2-circle"></i>
                        <span>Simpan User</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @foreach ($userRows as $row)
        @php
            $editModalId = 'edit-user-'.$row['id_user'];
            $assignmentModalId = 'assignment-user-'.$row['id_user'];
            $primaryAssignment = $row['primary_assignment'];
            $isEditModalOpen = session('modal') === $editModalId;
            $isAssignmentModalOpen = session('modal') === $assignmentModalId;
        @endphp

        <div class="modal-shell" id="modal-{{ $editModalId }}" aria-hidden="true">
            <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="modalEditUserTitle-{{ $row['id_user'] }}">
                <div class="modal-header">
                    <div>
                        <div class="modal-title" id="modalEditUserTitle-{{ $row['id_user'] }}">Edit User</div>
                        <div class="modal-subtitle">Perbarui identitas dan role untuk {{ $row['nama'] }}.</div>
                    </div>
                    <button type="button" class="modal-close js-close-modal" aria-label="Tutup modal">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('superadmin.users.update', $row['id_user']) }}" class="modal-form">
                    @csrf
                    <input type="hidden" name="q" value="{{ $filters['q'] }}">
                    <input type="hidden" name="role_filter" value="{{ $filters['role'] }}">
                    <input type="hidden" name="room_type_filter" value="{{ $filters['room_type'] }}">

                    <div class="field-grid">
                        <div class="field-group">
                            <label for="editName{{ $row['id_user'] }}">Nama</label>
                            <input id="editName{{ $row['id_user'] }}" type="text" name="nama" value="{{ $isEditModalOpen ? old('nama', $row['nama']) : $row['nama'] }}" required>
                        </div>
                        <div class="field-group">
                            <label for="editNis{{ $row['id_user'] }}">NIS</label>
                            <input id="editNis{{ $row['id_user'] }}" type="text" name="nis" value="{{ $isEditModalOpen ? old('nis', $row['nis_raw']) : $row['nis_raw'] }}" placeholder="Opsional">
                        </div>
                        <div class="field-group">
                            <label for="editRole{{ $row['id_user'] }}">Role</label>
                            <select id="editRole{{ $row['id_user'] }}" name="level" required>
                                @foreach ($roleOptions as $level => $label)
                                    <option value="{{ $level }}" @selected((int) ($isEditModalOpen ? old('level', $row['level']) : $row['level']) === (int) $level)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field-group">
                            <label for="editPassword{{ $row['id_user'] }}">Password Baru</label>
                            <input id="editPassword{{ $row['id_user'] }}" type="password" name="password" placeholder="Kosongkan jika tidak diubah">
                        </div>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="ghost-btn js-close-modal">Batal</button>
                        <button type="submit" class="submit-btn">
                            <i class="bi bi-save2-fill"></i>
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal-shell" id="modal-{{ $assignmentModalId }}" aria-hidden="true">
            <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="modalAssignmentTitle-{{ $row['id_user'] }}">
                <div class="modal-header">
                    <div>
                        <div class="modal-title" id="modalAssignmentTitle-{{ $row['id_user'] }}">Atur Penugasan</div>
                        <div class="modal-subtitle">Kelola hubungan {{ $row['nama'] }} dengan ruangan dan tentukan peran serta status penugasannya.</div>
                    </div>
                    <button type="button" class="modal-close js-close-modal" aria-label="Tutup modal">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('superadmin.users.assignment', $row['id_user']) }}" class="modal-form">
                    @csrf
                    <input type="hidden" name="q" value="{{ $filters['q'] }}">
                    <input type="hidden" name="role_filter" value="{{ $filters['role'] }}">
                    <input type="hidden" name="room_type_filter" value="{{ $filters['room_type'] }}">
                    <input type="hidden" name="assignment_id" value="{{ $isAssignmentModalOpen ? old('assignment_id', $primaryAssignment['id_penugasan_ruangan'] ?? '') : ($primaryAssignment['id_penugasan_ruangan'] ?? '') }}">

                    @if ($row['assignment_count'] > 0)
                        <div class="help-box">
                            Penugasan saat ini:
                            @foreach ($row['assignments'] as $assignment)
                                <div>{{ $assignment['nama_ruangan'] }} • {{ $assignment['peran_label'] }} • {{ $assignment['status_label'] }}</div>
                            @endforeach
                        </div>
                    @endif

                    <div class="field-grid">
                        <div class="field-group">
                            <label for="assignmentRoom{{ $row['id_user'] }}">Ruangan / Kelas</label>
                            <select id="assignmentRoom{{ $row['id_user'] }}" name="id_ruangan" required>
                                <option value="">Pilih ruangan</option>
                                @foreach ($availableRooms as $room)
                                    @php
                                        $selectedRoom = $isAssignmentModalOpen
                                            ? old('id_ruangan', $primaryAssignment['id_ruangan'] ?? '')
                                            : ($primaryAssignment['id_ruangan'] ?? '');
                                    @endphp
                                    <option value="{{ $room->id_ruangan }}" @selected((string) $selectedRoom === (string) $room->id_ruangan)>
                                        {{ $room->nama_ruangan }} ({{ $room->kode_ruangan }} - {{ ucfirst($room->jenis_ruangan) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field-group">
                            <label for="assignmentRole{{ $row['id_user'] }}">Peran Ruangan</label>
                            <input id="assignmentRole{{ $row['id_user'] }}" type="text" name="peran_ruangan" value="{{ $isAssignmentModalOpen ? old('peran_ruangan', $primaryAssignment['peran_ruangan'] ?? '') : ($primaryAssignment['peran_ruangan'] ?? '') }}" placeholder="Contoh: wali_kelas atau ketua_kelas" required>
                        </div>
                        <div class="field-group">
                            <label for="assignmentStatus{{ $row['id_user'] }}">Status Penugasan</label>
                            <select id="assignmentStatus{{ $row['id_user'] }}" name="status" required>
                                @php
                                    $selectedAssignmentStatus = $isAssignmentModalOpen
                                        ? old('status', $primaryAssignment['status'] ?? 'aktif')
                                        : ($primaryAssignment['status'] ?? 'aktif');
                                @endphp
                                <option value="aktif" @selected($selectedAssignmentStatus === 'aktif')>Aktif</option>
                                <option value="nonaktif" @selected($selectedAssignmentStatus === 'nonaktif')>Nonaktif</option>
                            </select>
                        </div>
                        <div class="field-group">
                            <label for="assignmentStart{{ $row['id_user'] }}">Tanggal Mulai</label>
                            <input id="assignmentStart{{ $row['id_user'] }}" type="date" name="tanggal_mulai" value="{{ $isAssignmentModalOpen ? old('tanggal_mulai', $primaryAssignment['tanggal_mulai'] ?? '') : ($primaryAssignment['tanggal_mulai'] ?? '') }}">
                        </div>
                        <div class="field-group">
                            <label for="assignmentEnd{{ $row['id_user'] }}">Tanggal Selesai</label>
                            <input id="assignmentEnd{{ $row['id_user'] }}" type="date" name="tanggal_selesai" value="{{ $isAssignmentModalOpen ? old('tanggal_selesai', $primaryAssignment['tanggal_selesai'] ?? '') : ($primaryAssignment['tanggal_selesai'] ?? '') }}">
                        </div>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="ghost-btn js-close-modal">Batal</button>
                        <button type="submit" class="submit-btn">
                            <i class="bi bi-diagram-3-fill"></i>
                            <span>Simpan Penugasan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    <script>
        (function () {
            const page = document.body;
            const modalTriggers = document.querySelectorAll('.js-open-modal');
            const closeButtons = document.querySelectorAll('.js-close-modal');
            const modalShells = document.querySelectorAll('.modal-shell');

            function openModal(modalId) {
                const modal = document.getElementById('modal-' + modalId);

                if (!modal) {
                    return;
                }

                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
                page.style.overflow = 'hidden';
            }

            function closeModal(modal) {
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');

                if (!document.querySelector('.modal-shell.is-open')) {
                    page.style.overflow = '';
                }
            }

            modalTriggers.forEach((trigger) => {
                trigger.addEventListener('click', () => openModal(trigger.dataset.modal));
            });

            closeButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const modal = button.closest('.modal-shell');

                    if (modal) {
                        closeModal(modal);
                    }
                });
            });

            modalShells.forEach((modal) => {
                modal.addEventListener('click', (event) => {
                    if (event.target === modal) {
                        closeModal(modal);
                    }
                });
            });

            document.addEventListener('keydown', (event) => {
                if (event.key !== 'Escape') {
                    return;
                }

                const openedModal = document.querySelector('.modal-shell.is-open');

                if (openedModal) {
                    closeModal(openedModal);
                }
            });

            const autoOpenModal = @json(session('modal'));

            if (autoOpenModal) {
                openModal(autoOpenModal);
            }
        })();
    </script>
</body>
</html>
