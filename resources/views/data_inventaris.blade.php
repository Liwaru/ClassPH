<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Data Barang | InfraSPH</title>
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

        .superadmin-items-page {
            margin-left: 320px;
            width: calc(100% - 320px);
            min-height: 100vh;
            padding: 2rem 1.6rem 2.5rem;
            transition: margin-left 0.28s ease, width 0.28s ease, padding 0.28s ease;
        }

        .app-shell.sidebar-collapsed .superadmin-items-page {
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
            margin-bottom: 0.65rem;
            letter-spacing: -0.04em;
        }

        .hero-subtitle {
            color: #5b6472;
            line-height: 1.7;
            max-width: 860px;
        }

        .hero-actions,
        .filter-actions,
        .table-header-actions,
        .action-group,
        .modal-actions {
            display: flex;
            gap: 0.6rem;
            flex-wrap: wrap;
        }

        .action-btn,
        .filter-btn,
        .filter-link,
        .row-action,
        .submit-btn,
        .ghost-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
        }

        .action-btn,
        .filter-btn,
        .filter-link,
        .submit-btn,
        .ghost-btn {
            min-width: 110px;
            padding: 0.92rem 1rem;
            border-radius: 16px;
            font-size: 0.88rem;
        }

        .action-btn.primary,
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

        .row-action.danger {
            background: #fff3f0;
            color: #c2410c;
            border-color: #ffd7c7;
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
            grid-template-columns: minmax(0, 1.35fr) repeat(4, minmax(170px, 0.85fr)) auto;
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

        .item-name {
            font-size: 0.96rem;
            font-weight: 800;
            color: #172033;
            margin-bottom: 0.12rem;
        }

        .item-meta,
        .detail-value.muted {
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
        .pill.warning { background: #fff4db; color: #a16207; }
        .pill.danger { background: #fff1f2; color: #be123c; }
        .pill.muted { background: #f4f4f5; color: #52525b; }
        .pill.kelas { background: #fff0e8; color: #c2410c; }
        .pill.lab { background: #e8f0ff; color: #1d4ed8; }
        .pill.kantor { background: #eefcf4; color: #166534; }

        .qty-stack {
            display: grid;
            gap: 0.2rem;
        }

        .qty-stack strong {
            font-size: 0.95rem;
        }

        .condition-copy {
            color: #7b8794;
            font-size: 0.76rem;
            margin-top: 0.28rem;
            line-height: 1.5;
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

        @media (max-width: 1380px) {
            .filter-form {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 1180px) {
            .summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .filter-form { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 860px) {
            .superadmin-items-page,
            .app-shell.sidebar-collapsed .superadmin-items-page {
                width: 100%;
                margin-left: 0;
                padding: 5.3rem 1rem 1.8rem;
            }

            .hero-card,
            .summary-grid,
            .filter-form,
            .field-grid,
            .detail-grid {
                grid-template-columns: 1fr;
                flex-direction: column;
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

        <main class="superadmin-items-page">
            <div class="page-shell">
                <section class="hero-card">
                    <div>
                        <div class="eyebrow">{{ $dashboard['role_name'] ?? 'Pengelola Sistem' }}</div>
                        <h1 class="hero-title">Data Barang</h1>
                        <p class="hero-subtitle">Pantau seluruh barang dari semua kelas, lab, dan kantor guru dalam satu tampilan global inventaris yang lebih cepat dibaca.</p>
                    </div>
                </section>

                <section class="summary-grid">
                    <article class="summary-card">
                        <div class="summary-label">Total Barang</div>
                        <div class="summary-value">{{ number_format($summary['total_barang']) }}</div>
                        <div class="summary-note">Total unit barang dari seluruh ruangan.</div>
                    </article>
                    <article class="summary-card is-accent">
                        <div class="summary-label">Barang Baik</div>
                        <div class="summary-value">{{ number_format($summary['barang_baik']) }}</div>
                        <div class="summary-note">Unit barang yang masih dalam kondisi baik.</div>
                    </article>
                    <article class="summary-card">
                        <div class="summary-label">Barang Rusak</div>
                        <div class="summary-value">{{ number_format($summary['barang_rusak']) }}</div>
                        <div class="summary-note">Unit barang yang saat ini tercatat rusak.</div>
                    </article>
                    <article class="summary-card">
                        <div class="summary-label">Perlu Perbaikan</div>
                        <div class="summary-value">{{ number_format($summary['barang_perlu_perbaikan']) }}</div>
                        <div class="summary-note">Entri inventaris dengan stok baik dan rusak sekaligus.</div>
                    </article>
                </section>

                <section class="filter-card">
                    <form method="GET" action="{{ route('superadmin.items') }}" class="filter-form">
                        <div class="filter-field">
                            <label for="itemSearch">Nama Barang</label>
                            <input id="itemSearch" type="text" name="q" value="{{ $filters['q'] }}" placeholder="Cari nama barang">
                        </div>
                        <div class="filter-field">
                            <label for="itemCategory">Kategori</label>
                            <select id="itemCategory" name="category">
                                <option value="semua" @selected($filters['category'] === 'semua')>Semua Kategori</option>
                                @foreach ($categoryOptions as $option)
                                    <option value="{{ $option->id_kategori_barang }}" @selected($filters['category'] === (string) $option->id_kategori_barang)>{{ ucfirst($option->nama_kategori) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-field">
                            <label for="itemRoom">Ruangan</label>
                            <select id="itemRoom" name="room">
                                <option value="semua" @selected($filters['room'] === 'semua')>Semua Ruangan</option>
                                @foreach ($roomOptions as $option)
                                    <option value="{{ $option->id_ruangan }}" @selected($filters['room'] === (string) $option->id_ruangan)>{{ $option->nama_ruangan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-field">
                            <label for="itemRoomType">Jenis Ruangan</label>
                            <select id="itemRoomType" name="room_type">
                                <option value="semua" @selected($filters['room_type'] === 'semua')>Semua Jenis</option>
                                @foreach ($roomTypeOptions as $option)
                                    <option value="{{ $option }}" @selected($filters['room_type'] === $option)>{{ $option === 'kantor_guru' ? 'Kantor Guru' : ucfirst($option) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-field">
                            <label for="itemCondition">Kondisi</label>
                            <select id="itemCondition" name="condition">
                                <option value="semua" @selected($filters['condition'] === 'semua')>Semua Kondisi</option>
                                <option value="baik" @selected($filters['condition'] === 'baik')>Baik</option>
                                <option value="rusak" @selected($filters['condition'] === 'rusak')>Rusak</option>
                                <option value="perlu_perbaikan" @selected($filters['condition'] === 'perlu_perbaikan')>Perlu Perbaikan</option>
                            </select>
                        </div>
                        <div class="filter-actions">
                            <button type="submit" class="filter-btn">
                                <i class="bi bi-funnel-fill"></i>
                                <span>Terapkan</span>
                            </button>
                            <a href="{{ route('superadmin.items') }}" class="filter-link">
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
                            <div class="table-title">Daftar Barang Global</div>
                            <div class="table-subtitle">Menampilkan {{ number_format($items->total()) }} data barang dari seluruh ruangan berdasarkan filter aktif.</div>
                        </div>
                        <div class="table-header-actions">
                            <button type="button" class="action-btn primary js-open-modal" data-modal="create-item">
                                <i class="bi bi-plus-square"></i>
                                <span>Tambah Barang</span>
                            </button>
                        </div>
                    </div>

                    @if (count($itemRows) === 0)
                        <div class="empty-state">Belum ada data barang yang cocok dengan filter saat ini.</div>
                    @else
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Barang</th>
                                        <th>Kategori</th>
                                        <th>Ruangan</th>
                                        <th>Jenis Ruangan</th>
                                        <th>Jumlah</th>
                                        <th>Kondisi</th>
                                        <th>Tanggal Masuk</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($itemRows as $index => $item)
                                        @php
                                            $number = ($items->firstItem() ?? 1) + $index;
                                            $roomTypeClass = $item['jenis_ruangan_raw'] === 'kelas'
                                                ? 'kelas'
                                                : ($item['jenis_ruangan_raw'] === 'kantor_guru' ? 'kantor' : 'lab');
                                        @endphp
                                        <tr>
                                            <td>{{ $number }}</td>
                                            <td>
                                                <div class="item-name">{{ $item['nama_barang'] }}</div>
                                                <div class="item-meta">ID Inventaris: {{ $item['id_inventaris_ruangan'] }}</div>
                                            </td>
                                            <td>{{ $item['nama_kategori'] }}</td>
                                            <td>
                                                <div class="item-name">{{ $item['nama_ruangan'] }}</div>
                                                <div class="item-meta">{{ $item['kode_ruangan'] }}</div>
                                            </td>
                                            <td><span class="pill {{ $roomTypeClass }}">{{ $item['jenis_ruangan'] }}</span></td>
                                            <td>
                                                <div class="qty-stack">
                                                    <strong>{{ number_format($item['jumlah_total']) }} unit</strong>
                                                    <span class="item-meta">{{ number_format($item['jumlah_baik']) }} baik, {{ number_format($item['jumlah_rusak']) }} rusak</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="pill {{ $item['kondisi_class'] }}">{{ $item['kondisi_label'] }}</span>
                                                <div class="condition-copy">{{ $item['kondisi_note'] }}</div>
                                            </td>
                                            <td>
                                                <div class="item-meta">{{ $item['tanggal_masuk'] }}</div>
                                            </td>
                                            <td>
                                                <div class="action-group">
                                                    <button type="button" class="row-action js-open-modal" data-modal="detail-item-{{ $item['id_inventaris_ruangan'] }}">
                                                        <i class="bi bi-eye"></i>
                                                        <span>Detail</span>
                                                    </button>
                                                    <button type="button" class="row-action js-open-modal" data-modal="edit-item-{{ $item['id_inventaris_ruangan'] }}">
                                                        <i class="bi bi-pencil-square"></i>
                                                        <span>Edit</span>
                                                    </button>
                                                    <form method="POST" action="{{ route('superadmin.items.delete', $item['id_inventaris_ruangan']) }}" onsubmit="return confirm('Hapus data barang ini?');">
                                                        @csrf
                                                        <input type="hidden" name="q" value="{{ $filters['q'] }}">
                                                        <input type="hidden" name="category_filter" value="{{ $filters['category'] }}">
                                                        <input type="hidden" name="room_filter" value="{{ $filters['room'] }}">
                                                        <input type="hidden" name="room_type_filter" value="{{ $filters['room_type'] }}">
                                                        <input type="hidden" name="condition_filter" value="{{ $filters['condition'] }}">
                                                        <button type="submit" class="row-action danger">
                                                            <i class="bi bi-trash3"></i>
                                                            <span>Hapus</span>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if ($items->hasPages())
                            <div class="pagination-wrap">
                                <div class="pagination">
                                    <span class="pagination-info">Halaman {{ $items->currentPage() }} dari {{ $items->lastPage() }}</span>

                                    @if ($items->onFirstPage())
                                        <span class="pagination-link disabled"><i class="bi bi-chevron-left"></i></span>
                                    @else
                                        <a href="{{ $items->previousPageUrl() }}" class="pagination-link" aria-label="Halaman sebelumnya">
                                            <i class="bi bi-chevron-left"></i>
                                        </a>
                                    @endif

                                    @foreach ($items->getUrlRange(1, $items->lastPage()) as $page => $url)
                                        @if ($page === $items->currentPage())
                                            <span class="pagination-current">{{ $page }}</span>
                                        @else
                                            <a href="{{ $url }}" class="pagination-link">{{ $page }}</a>
                                        @endif
                                    @endforeach

                                    @if ($items->hasMorePages())
                                        <a href="{{ $items->nextPageUrl() }}" class="pagination-link" aria-label="Halaman berikutnya">
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

    <div class="modal-shell" id="modal-create-item" aria-hidden="true">
        <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="modalCreateItemTitle">
            <div class="modal-header">
                <div>
                    <div class="modal-title" id="modalCreateItemTitle">Tambah Barang</div>
                    <div class="modal-subtitle">Tambahkan barang baru ke ruangan tertentu. Nama barang yang sama bisa dipakai di ruangan lain sebagai entri terpisah.</div>
                </div>
                <button type="button" class="modal-close js-close-modal" aria-label="Tutup modal">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('superadmin.items.store') }}" class="modal-form">
                @csrf
                <input type="hidden" name="q" value="{{ $filters['q'] }}">
                <input type="hidden" name="category_filter" value="{{ $filters['category'] }}">
                <input type="hidden" name="room_filter" value="{{ $filters['room'] }}">
                <input type="hidden" name="room_type_filter" value="{{ $filters['room_type'] }}">
                <input type="hidden" name="condition_filter" value="{{ $filters['condition'] }}">

                <div class="field-grid">
                    <div class="field-group">
                        <label for="createItemName">Nama Barang</label>
                        <input id="createItemName" type="text" name="nama_barang" value="{{ old('nama_barang') }}" placeholder="Contoh: Kursi Siswa" required>
                    </div>
                    <div class="field-group">
                        <label for="createItemCategory">Kategori</label>
                        <select id="createItemCategory" name="id_kategori_barang" required>
                            <option value="">Pilih kategori</option>
                            @foreach ($categoryOptions as $option)
                                <option value="{{ $option->id_kategori_barang }}" @selected((string) old('id_kategori_barang') === (string) $option->id_kategori_barang)>{{ ucfirst($option->nama_kategori) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="field-grid">
                    <div class="field-group">
                        <label for="createItemRoom">Ruangan</label>
                        <select id="createItemRoom" name="id_ruangan" required>
                            <option value="">Pilih ruangan</option>
                            @foreach ($roomOptions as $option)
                                <option value="{{ $option->id_ruangan }}" @selected((string) old('id_ruangan') === (string) $option->id_ruangan)>{{ $option->nama_ruangan }} ({{ $option->jenis_ruangan === 'kantor_guru' ? 'Kantor Guru' : ucfirst($option->jenis_ruangan) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field-group">
                        <label for="createItemGood">Jumlah Baik</label>
                        <input id="createItemGood" type="number" min="0" name="jumlah_baik" value="{{ old('jumlah_baik', 0) }}" required>
                    </div>
                </div>

                <div class="field-grid">
                    <div class="field-group">
                        <label for="createItemDamaged">Jumlah Rusak</label>
                        <input id="createItemDamaged" type="number" min="0" name="jumlah_rusak" value="{{ old('jumlah_rusak', 0) }}" required>
                    </div>
                    <div class="field-group">
                        <label>Catatan</label>
                        <input type="text" value="Tanggal masuk belum tersedia di database inventaris." disabled>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="ghost-btn js-close-modal">Batal</button>
                    <button type="submit" class="submit-btn">
                        <i class="bi bi-check-circle"></i>
                        <span>Simpan Barang</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @foreach ($itemRows as $item)
        <div class="modal-shell" id="modal-detail-item-{{ $item['id_inventaris_ruangan'] }}" aria-hidden="true">
            <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="modalDetailItemTitle-{{ $item['id_inventaris_ruangan'] }}">
                <div class="modal-header">
                    <div>
                        <div class="modal-title" id="modalDetailItemTitle-{{ $item['id_inventaris_ruangan'] }}">Detail Barang</div>
                        <div class="modal-subtitle">Ringkasan data barang dan lokasi inventaris berdasarkan entri yang dipilih.</div>
                    </div>
                    <button type="button" class="modal-close js-close-modal" aria-label="Tutup modal">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="detail-grid">
                    <div class="detail-card">
                        <div class="detail-label">Nama Barang</div>
                        <div class="detail-value">{{ $item['nama_barang'] }}</div>
                    </div>
                    <div class="detail-card">
                        <div class="detail-label">Kategori</div>
                        <div class="detail-value">{{ $item['nama_kategori'] }}</div>
                    </div>
                    <div class="detail-card">
                        <div class="detail-label">Ruangan</div>
                        <div class="detail-value">{{ $item['nama_ruangan'] }}</div>
                        <div class="detail-value muted">{{ $item['kode_ruangan'] }}</div>
                    </div>
                    <div class="detail-card">
                        <div class="detail-label">Jenis Ruangan</div>
                        <div class="detail-value">{{ $item['jenis_ruangan'] }}</div>
                    </div>
                    <div class="detail-card">
                        <div class="detail-label">Jumlah Baik</div>
                        <div class="detail-value">{{ number_format($item['jumlah_baik']) }} unit</div>
                    </div>
                    <div class="detail-card">
                        <div class="detail-label">Jumlah Rusak</div>
                        <div class="detail-value">{{ number_format($item['jumlah_rusak']) }} unit</div>
                    </div>
                    <div class="detail-card">
                        <div class="detail-label">Kondisi</div>
                        <div class="detail-value">{{ $item['kondisi_label'] }}</div>
                        <div class="detail-value muted">{{ $item['kondisi_note'] }}</div>
                    </div>
                    <div class="detail-card">
                        <div class="detail-label">Tanggal Masuk</div>
                        <div class="detail-value">{{ $item['tanggal_masuk'] }}</div>
                        <div class="detail-value muted">Kolom tanggal belum tersedia pada tabel inventaris saat ini.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-shell" id="modal-edit-item-{{ $item['id_inventaris_ruangan'] }}" aria-hidden="true">
            <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="modalEditItemTitle-{{ $item['id_inventaris_ruangan'] }}">
                <div class="modal-header">
                    <div>
                        <div class="modal-title" id="modalEditItemTitle-{{ $item['id_inventaris_ruangan'] }}">Edit Barang</div>
                        <div class="modal-subtitle">Perbarui nama, kategori, ruangan, dan jumlah barang untuk entri inventaris ini.</div>
                    </div>
                    <button type="button" class="modal-close js-close-modal" aria-label="Tutup modal">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('superadmin.items.update', $item['id_inventaris_ruangan']) }}" class="modal-form">
                    @csrf
                    <input type="hidden" name="q" value="{{ $filters['q'] }}">
                    <input type="hidden" name="category_filter" value="{{ $filters['category'] }}">
                    <input type="hidden" name="room_filter" value="{{ $filters['room'] }}">
                    <input type="hidden" name="room_type_filter" value="{{ $filters['room_type'] }}">
                    <input type="hidden" name="condition_filter" value="{{ $filters['condition'] }}">

                    <div class="field-grid">
                        <div class="field-group">
                            <label for="editItemName-{{ $item['id_inventaris_ruangan'] }}">Nama Barang</label>
                            <input id="editItemName-{{ $item['id_inventaris_ruangan'] }}" type="text" name="nama_barang" value="{{ old('nama_barang', $item['nama_barang']) }}" required>
                        </div>
                        <div class="field-group">
                            <label for="editItemCategory-{{ $item['id_inventaris_ruangan'] }}">Kategori</label>
                            <select id="editItemCategory-{{ $item['id_inventaris_ruangan'] }}" name="id_kategori_barang" required>
                                @foreach ($categoryOptions as $option)
                                    <option value="{{ $option->id_kategori_barang }}" @selected((string) old('id_kategori_barang', $item['id_kategori_barang']) === (string) $option->id_kategori_barang)>{{ ucfirst($option->nama_kategori) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="field-grid">
                        <div class="field-group">
                            <label for="editItemRoom-{{ $item['id_inventaris_ruangan'] }}">Ruangan</label>
                            <select id="editItemRoom-{{ $item['id_inventaris_ruangan'] }}" name="id_ruangan" required>
                                @foreach ($roomOptions as $option)
                                    <option value="{{ $option->id_ruangan }}" @selected((string) old('id_ruangan', $item['id_ruangan']) === (string) $option->id_ruangan)>{{ $option->nama_ruangan }} ({{ $option->jenis_ruangan === 'kantor_guru' ? 'Kantor Guru' : ucfirst($option->jenis_ruangan) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field-group">
                            <label for="editItemGood-{{ $item['id_inventaris_ruangan'] }}">Jumlah Baik</label>
                            <input id="editItemGood-{{ $item['id_inventaris_ruangan'] }}" type="number" min="0" name="jumlah_baik" value="{{ old('jumlah_baik', $item['jumlah_baik']) }}" required>
                        </div>
                    </div>

                    <div class="field-grid">
                        <div class="field-group">
                            <label for="editItemDamaged-{{ $item['id_inventaris_ruangan'] }}">Jumlah Rusak</label>
                            <input id="editItemDamaged-{{ $item['id_inventaris_ruangan'] }}" type="number" min="0" name="jumlah_rusak" value="{{ old('jumlah_rusak', $item['jumlah_rusak']) }}" required>
                        </div>
                        <div class="field-group">
                            <label>Catatan</label>
                            <input type="text" value="Jika nama sama dan ruangan sama, data akan diperbarui pada entri yang cocok." disabled>
                        </div>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="ghost-btn js-close-modal">Batal</button>
                        <button type="submit" class="submit-btn">
                            <i class="bi bi-check-circle"></i>
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
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
