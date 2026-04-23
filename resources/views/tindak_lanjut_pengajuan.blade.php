<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tindak Lanjut Pengajuan | InfraSPH</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --brand-orange: #ff5900;
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

        .superadmin-realization-page {
            margin-left: 320px;
            width: calc(100% - 320px);
            min-height: 100vh;
            padding: 2rem 1.6rem 2.5rem;
            transition: margin-left 0.28s ease, width 0.28s ease, padding 0.28s ease;
        }

        .app-shell.sidebar-collapsed .superadmin-realization-page {
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
            padding: 1.6rem 1.7rem;
            margin-bottom: 1.15rem;
            background: linear-gradient(135deg, rgba(255, 89, 0, 0.09), rgba(255, 89, 0, 0.03));
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
            margin-bottom: 0.65rem;
            letter-spacing: -0.04em;
        }

        .hero-subtitle {
            color: #5b6472;
            line-height: 1.7;
            max-width: 860px;
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
            grid-template-columns: minmax(0, 1.3fr) repeat(3, minmax(170px, 0.85fr)) auto;
            gap: 0.85rem;
            align-items: end;
        }

        .filter-field label,
        .field-group label,
        .detail-label {
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
        .field-group input {
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

        .filter-actions,
        .action-group,
        .modal-actions {
            display: flex;
            gap: 0.6rem;
            flex-wrap: wrap;
        }

        .filter-btn,
        .filter-link,
        .row-action,
        .submit-btn,
        .ghost-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            min-width: 110px;
            padding: 0.9rem 1rem;
            border-radius: 16px;
            font: inherit;
            font-size: 0.88rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .filter-btn,
        .submit-btn {
            border: none;
            background: linear-gradient(135deg, #ff5900, #ff7b2f);
            color: #ffffff;
        }

        .filter-link,
        .ghost-btn,
        .row-action {
            border: 1px solid #ecd8cb;
            background: #fffdfa;
            color: #4b5563;
        }

        .row-action.info {
            background: #eef6ff;
            color: #1d4ed8;
            border-color: #d8e7ff;
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
            align-items: center;
            gap: 1rem;
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

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1120px;
        }

        th,
        td {
            padding: 0.82rem 0.72rem;
            border-bottom: 1px solid #f6e7df;
            vertical-align: top;
            text-align: left;
        }

        th {
            color: #7b8794;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            background: #fffaf7;
        }

        td {
            color: #172033;
            font-size: 0.85rem;
        }

        tbody tr:hover {
            background: #fffaf7;
        }

        .primary-text {
            font-size: 0.96rem;
            font-weight: 800;
            color: #172033;
            margin-bottom: 0.12rem;
        }

        .muted-text {
            color: #7b8794;
            font-size: 0.78rem;
            line-height: 1.5;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            white-space: nowrap;
            font-weight: 700;
            padding: 0.38rem 0.68rem;
            font-size: 0.72rem;
        }

        .pill.approved { background: #eaf8ef; color: #15803d; }
        .pill.rejected { background: #fff1f2; color: #be123c; }
        .pill.process { background: #fff4db; color: #a16207; }
        .pill.info { background: #eef6ff; color: #1d4ed8; }

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
            padding: 1.2rem 1.2rem 1.2rem calc(320px + 1.2rem);
            background: rgba(15, 23, 42, 0.55);
            z-index: 90;
        }

        body:has(.app-shell.sidebar-collapsed) .modal-shell {
            padding-left: calc(88px + 1.2rem);
        }

        .modal-shell.is-open {
            display: flex;
        }

        .modal-dialog {
            width: min(780px, 100%);
            max-height: calc(100vh - 2.4rem);
            overflow-y: auto;
            padding: 1.2rem 1.2rem 1.15rem;
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

        .field-grid,
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.9rem;
        }

        .detail-card {
            padding: 0.95rem 1rem;
            border: 1px solid #f1dfd4;
            border-radius: 18px;
            background: #fffdfa;
        }

        .detail-value {
            color: #172033;
            font-size: 0.96rem;
            font-weight: 700;
            line-height: 1.5;
        }

        .detail-value.muted {
            color: #7b8794;
            font-size: 0.82rem;
            font-weight: 500;
        }

        .realization-list {
            display: grid;
            gap: 0.8rem;
        }

        .realization-row {
            padding: 0.95rem 1rem;
            border: 1px solid #f1dfd4;
            border-radius: 18px;
            background: #fffdfa;
        }

        .realization-row-title {
            font-size: 0.96rem;
            font-weight: 800;
            margin-bottom: 0.18rem;
        }

        @media (max-width: 1260px) {
            .summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .filter-form { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 860px) {
            .superadmin-realization-page,
            .app-shell.sidebar-collapsed .superadmin-realization-page {
                width: 100%;
                margin-left: 0;
                padding: 5.3rem 1rem 1.8rem;
            }

            .summary-grid,
            .filter-form,
            .field-grid,
            .detail-grid {
                grid-template-columns: 1fr;
            }

            .table-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .modal-shell,
            body:has(.app-shell.sidebar-collapsed) .modal-shell {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="app-shell" id="appShell">
        @include('header')

        <main class="superadmin-realization-page">
            <div class="page-shell">
                <section class="hero-card">
                    <div class="eyebrow">{{ $dashboard['role_name'] ?? 'Pengelola Sistem' }}</div>
                    <h1 class="hero-title">Tindak Lanjut Pengajuan</h1>
                    <p class="hero-subtitle">Lanjutkan pengajuan yang sudah disetujui kepala sekolah menjadi inventaris nyata. Di tahap ini superadmin mengeksekusi, bukan melakukan approval lagi.</p>
                </section>

                <section class="summary-grid">
                    <article class="summary-card">
                        <div class="summary-label">Menunggu Pengajuan</div>
                        <div class="summary-value">{{ number_format($summary['waiting']) }}</div>
                        <div class="summary-note">Pengajuan yang sudah approved owner dan siap dieksekusi.</div>
                    </article>
                    <article class="summary-card is-accent">
                        <div class="summary-label">Sudah Di Ajuin</div>
                        <div class="summary-value">{{ number_format($summary['realized']) }}</div>
                        <div class="summary-note">Pengajuan yang sudah masuk ke inventaris.</div>
                    </article>
                    <article class="summary-card">
                        <div class="summary-label">Ditolak</div>
                        <div class="summary-value">{{ number_format($summary['rejected']) }}</div>
                        <div class="summary-note">Pengajuan yang ditolak di tahap kepala sekolah.</div>
                    </article>
                    <article class="summary-card">
                        <div class="summary-label">Total Pengajuan</div>
                        <div class="summary-value">{{ number_format($summary['total']) }}</div>
                        <div class="summary-note">Seluruh pengajuan yang masuk ke tahap tindak lanjut.</div>
                    </article>
                </section>

                <section class="filter-card">
                    <form method="GET" action="{{ route('superadmin.requests.realization') }}" class="filter-form">
                        <div class="filter-field">
                            <label for="searchRequest">Search Nama Barang</label>
                            <input id="searchRequest" type="text" name="q" value="{{ $filters['q'] }}" placeholder="Cari nama barang, pengaju, atau ruangan">
                        </div>
                        <div class="filter-field">
                            <label for="statusFilter">Status</label>
                            <select id="statusFilter" name="status">
                                <option value="menunggu" @selected($filters['status'] === 'menunggu')>Menunggu Realisasi</option>
                                <option value="selesai" @selected($filters['status'] === 'selesai')>Sudah Direalisasi</option>
                                <option value="ditolak" @selected($filters['status'] === 'ditolak')>Ditolak</option>
                                <option value="semua" @selected($filters['status'] === 'semua')>Semua Status</option>
                            </select>
                        </div>
                        <div class="filter-field">
                            <label for="roomFilter">Ruangan</label>
                            <select id="roomFilter" name="room">
                                <option value="semua" @selected($filters['room'] === 'semua')>Semua Ruangan</option>
                                @foreach ($roomOptions as $option)
                                    <option value="{{ $option->id_ruangan }}" @selected($filters['room'] === (string) $option->id_ruangan)>{{ $option->nama_ruangan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-field">
                            <label for="dateFilter">Tanggal</label>
                            <input id="dateFilter" type="date" name="date" value="{{ $filters['date'] }}">
                        </div>
                        <div class="filter-actions">
                            <button type="submit" class="filter-btn">
                                <i class="bi bi-funnel-fill"></i>
                                <span>Terapkan</span>
                            </button>
                            <a href="{{ route('superadmin.requests.realization') }}" class="filter-link">
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
                            <div class="table-title">Daftar Tindak Lanjut Pengajuan</div>
                            <div class="table-subtitle">Menampilkan {{ number_format($requests->total()) }} pengajuan berdasarkan filter aktif.</div>
                        </div>
                    </div>

                    @if (count($requestRows) === 0)
                        <div class="empty-state">Belum ada pengajuan yang cocok dengan filter saat ini.</div>
                    @else
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Pengaju</th>
                                        <th>Ruangan</th>
                                        <th>Barang</th>
                                        <th>Jumlah</th>
                                        <th>Status Approval</th>
                                        <th>Tanggal Pengajuan</th>
                                        <th>Status Realisasi</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($requestRows as $index => $requestRow)
                                        <tr>
                                            <td>{{ ($requests->firstItem() ?? 1) + $index }}</td>
                                            <td>
                                                <div class="primary-text">{{ $requestRow['pengaju'] }}</div>
                                                <div class="muted-text">{{ $requestRow['kode_permintaan'] }}</div>
                                            </td>
                                            <td>
                                                <div class="primary-text">{{ $requestRow['ruangan'] }}</div>
                                                <div class="muted-text">{{ $requestRow['kode_ruangan'] }}</div>
                                            </td>
                                            <td>{{ $requestRow['barang'] !== '' ? $requestRow['barang'] : '-' }}</td>
                                            <td>{{ number_format($requestRow['jumlah']) }} item</td>
                                            <td><span class="pill {{ $requestRow['approval_class'] }}">{{ $requestRow['approval_label'] }}</span></td>
                                            <td>{{ $requestRow['tanggal_label'] }}</td>
                                            <td><span class="pill {{ $requestRow['realisasi_class'] }}">{{ $requestRow['realisasi_label'] }}</span></td>
                                            <td>
                                                <div class="action-group">
                                                    <button type="button" class="row-action js-open-modal" data-modal="detail-request-{{ $requestRow['id_permintaan'] }}">
                                                        <i class="bi bi-eye"></i>
                                                        <span>Detail</span>
                                                    </button>
                                                    @if ($requestRow['can_realize'])
                                                        <button type="button" class="row-action info js-open-modal" data-modal="realize-request-{{ $requestRow['id_permintaan'] }}">
                                                            <i class="bi bi-box-seam"></i>
                                                            <span>Realisasikan</span>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if ($requests->hasPages())
                            <div class="pagination-wrap">
                                <div class="pagination">
                                    <span class="pagination-info">Halaman {{ $requests->currentPage() }} dari {{ $requests->lastPage() }}</span>

                                    @if ($requests->onFirstPage())
                                        <span class="pagination-link disabled"><i class="bi bi-chevron-left"></i></span>
                                    @else
                                        <a href="{{ $requests->previousPageUrl() }}" class="pagination-link" aria-label="Halaman sebelumnya">
                                            <i class="bi bi-chevron-left"></i>
                                        </a>
                                    @endif

                                    @foreach ($requests->getUrlRange(1, $requests->lastPage()) as $page => $url)
                                        @if ($page === $requests->currentPage())
                                            <span class="pagination-current">{{ $page }}</span>
                                        @else
                                            <a href="{{ $url }}" class="pagination-link">{{ $page }}</a>
                                        @endif
                                    @endforeach

                                    @if ($requests->hasMorePages())
                                        <a href="{{ $requests->nextPageUrl() }}" class="pagination-link" aria-label="Halaman berikutnya">
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

    @foreach ($requestRows as $requestRow)
        <div class="modal-shell" id="modal-detail-request-{{ $requestRow['id_permintaan'] }}" aria-hidden="true">
            <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="detailRequestTitle-{{ $requestRow['id_permintaan'] }}">
                <div class="modal-header">
                    <div>
                        <div class="modal-title" id="detailRequestTitle-{{ $requestRow['id_permintaan'] }}">Detail Pengajuan</div>
                        <div class="modal-subtitle">Ringkasan approval dan tindak lanjut untuk pengajuan {{ $requestRow['kode_permintaan'] }}.</div>
                    </div>
                    <button type="button" class="modal-close js-close-modal" aria-label="Tutup modal">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="detail-grid">
                    <div class="detail-card">
                        <div class="detail-label">Nama Pengaju</div>
                        <div class="detail-value">{{ $requestRow['pengaju'] }}</div>
                    </div>
                    <div class="detail-card">
                        <div class="detail-label">Ruangan</div>
                        <div class="detail-value">{{ $requestRow['ruangan'] }}</div>
                        <div class="detail-value muted">{{ $requestRow['kode_ruangan'] }}</div>
                    </div>
                    <div class="detail-card">
                        <div class="detail-label">Status Approval</div>
                        <div class="detail-value">{{ $requestRow['approval_label'] }}</div>
                    </div>
                    <div class="detail-card">
                        <div class="detail-label">Status Realisasi</div>
                        <div class="detail-value">{{ $requestRow['realisasi_label'] }}</div>
                        <div class="detail-value muted">
                            @if ($requestRow['realized_at'])
                                Direalisasikan pada {{ $requestRow['realized_at'] }}
                            @else
                                Belum ada riwayat realisasi tersimpan.
                            @endif
                        </div>
                    </div>
                    <div class="detail-card">
                        <div class="detail-label">Tanggal Pengajuan</div>
                        <div class="detail-value">{{ $requestRow['tanggal_label'] }}</div>
                    </div>
                    <div class="detail-card">
                        <div class="detail-label">Sumber</div>
                        <div class="detail-value">{{ $requestRow['source'] }}</div>
                    </div>
                </div>

                <div class="realization-list" style="margin-top: 1rem;">
                    @foreach ($requestRow['details'] as $detail)
                        <div class="realization-row">
                            <div class="realization-row-title">{{ $detail['nama_barang'] }}</div>
                            <div class="muted-text">
                                Diminta: {{ number_format($detail['jumlah_diminta']) }} item |
                                Disetujui: {{ number_format($detail['jumlah_disetujui']) }} item |
                                Direalisasi: {{ number_format($detail['jumlah_diberikan']) }} item
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        @if ($requestRow['can_realize'])
            <div class="modal-shell" id="modal-realize-request-{{ $requestRow['id_permintaan'] }}" aria-hidden="true">
                <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="realizeRequestTitle-{{ $requestRow['id_permintaan'] }}">
                    <div class="modal-header">
                        <div>
                            <div class="modal-title" id="realizeRequestTitle-{{ $requestRow['id_permintaan'] }}">Realisasikan Pengajuan</div>
                            <div class="modal-subtitle">Barang akan ditambahkan ke inventaris ruangan sebagai kondisi awal baik dengan sumber pengajuan.</div>
                        </div>
                        <button type="button" class="modal-close js-close-modal" aria-label="Tutup modal">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('superadmin.requests.realization.store', $requestRow['id_permintaan']) }}" class="modal-form">
                        @csrf
                        <input type="hidden" name="status_filter" value="{{ $filters['status'] }}">
                        <input type="hidden" name="room_filter" value="{{ $filters['room'] }}">
                        <input type="hidden" name="date_filter" value="{{ $filters['date'] }}">
                        <input type="hidden" name="q" value="{{ $filters['q'] }}">

                        <div class="field-grid">
                            <div class="field-group">
                                <label>Ruangan</label>
                                <input type="text" value="{{ $requestRow['ruangan'] }}" readonly>
                            </div>
                            <div class="field-group">
                                <label for="realizationDate-{{ $requestRow['id_permintaan'] }}">Tanggal Realisasi</label>
                                <input id="realizationDate-{{ $requestRow['id_permintaan'] }}" type="date" name="tanggal_realisasi" value="{{ old('tanggal_realisasi', now()->toDateString()) }}" required>
                            </div>
                        </div>

                        <div class="realization-list">
                            @foreach ($requestRow['details'] as $detail)
                                <div class="realization-row">
                                    <div class="realization-row-title">{{ $detail['nama_barang'] }}</div>
                                    <div class="muted-text">Jumlah disetujui/diminta: {{ number_format($detail['jumlah_disetujui']) }} item | Kondisi awal: Baik | Sumber: Pengajuan</div>
                                    <div class="field-group" style="margin-top: 0.75rem;">
                                        <label for="qty-{{ $requestRow['id_permintaan'] }}-{{ $detail['id_detail_permintaan'] }}">Jumlah Direalisasikan</label>
                                        <input
                                            id="qty-{{ $requestRow['id_permintaan'] }}-{{ $detail['id_detail_permintaan'] }}"
                                            type="number"
                                            min="0"
                                            max="{{ $detail['jumlah_disetujui'] }}"
                                            name="qty_{{ $detail['id_detail_permintaan'] }}"
                                            value="{{ old('qty_'.$detail['id_detail_permintaan'], $detail['jumlah_disetujui']) }}"
                                            required
                                        >
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="modal-actions">
                            <button type="button" class="ghost-btn js-close-modal">Batal</button>
                            <button type="submit" class="submit-btn">
                                <i class="bi bi-check-circle"></i>
                                <span>Simpan Realisasi</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endforeach

    <script>
        (function () {
            const modalShells = document.querySelectorAll('.modal-shell');
            const openButtons = document.querySelectorAll('.js-open-modal');
            const closeButtons = document.querySelectorAll('.js-close-modal');

            function closeAllModals() {
                modalShells.forEach((modal) => {
                    modal.classList.remove('is-open');
                    modal.setAttribute('aria-hidden', 'true');
                });
                document.body.style.overflow = '';
            }

            function openModal(modalName) {
                const target = document.getElementById('modal-' + modalName);
                if (!target) {
                    return;
                }

                closeAllModals();
                target.classList.add('is-open');
                target.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }

            openButtons.forEach((button) => {
                button.addEventListener('click', function () {
                    openModal(this.dataset.modal);
                });
            });

            closeButtons.forEach((button) => {
                button.addEventListener('click', closeAllModals);
            });

            modalShells.forEach((modal) => {
                modal.addEventListener('click', function (event) {
                    if (event.target === modal) {
                        closeAllModals();
                    }
                });
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeAllModals();
                }
            });

            @if (session('modal'))
                openModal(@json(session('modal')));
            @endif
        })();
    </script>
</body>
</html>
