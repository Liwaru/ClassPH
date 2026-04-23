<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Data Ruangan | InfraSPH</title>
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

        .superadmin-rooms-page {
            margin-left: 320px;
            width: calc(100% - 320px);
            min-height: 100vh;
            padding: 2rem 1.6rem 2.5rem;
            transition: margin-left 0.28s ease, width 0.28s ease, padding 0.28s ease;
        }

        .app-shell.sidebar-collapsed .superadmin-rooms-page {
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
            max-width: 820px;
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
            min-width: 108px;
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
            grid-template-columns: minmax(0, 1.6fr) minmax(220px, 0.8fr) minmax(220px, 0.8fr) auto;
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
        .field-group select,
        .field-group textarea {
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

        .field-group textarea {
            min-height: 110px;
            resize: vertical;
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
            overflow-x: visible;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            padding: 0.82rem 0.62rem;
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
            word-break: break-word;
        }

        tbody tr:hover {
            background: #fffaf7;
        }

        .room-name {
            font-size: 0.96rem;
            font-weight: 800;
            color: #172033;
            margin-bottom: 0.1rem;
        }

        .room-meta {
            color: #7b8794;
            font-size: 0.78rem;
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
        .pill.muted { background: #f4f4f5; color: #52525b; }
        .pill.kelas { background: #fff0e8; color: #c2410c; }
        .pill.lab { background: #e8f0ff; color: #1d4ed8; }
        .pill.other { background: #f5ecff; color: #7c3aed; }

        .inventory-summary {
            display: grid;
            gap: 0.28rem;
        }

        .condition-copy {
            color: #7b8794;
            font-size: 0.76rem;
            margin-top: 0.28rem;
            line-height: 1.5;
        }

        .row-action {
            padding: 0.54rem 0.66rem;
            border-radius: 12px;
            font-size: 0.74rem;
        }

        .row-action.danger {
            background: #fff3f0;
            color: #c2410c;
            border-color: #ffd7c7;
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
            width: min(760px, 100%);
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

        @media (max-width: 1220px) {
            .summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .filter-form { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 860px) {
            .superadmin-rooms-page,
            .app-shell.sidebar-collapsed .superadmin-rooms-page {
                width: 100%;
                margin-left: 0;
                padding: 5.3rem 1rem 1.8rem;
            }

            .hero-card,
            .field-grid,
            .summary-grid,
            .filter-form {
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

        <main class="superadmin-rooms-page">
            <div class="page-shell">
                <section class="hero-card">
                    <div>
                        <div class="eyebrow">{{ $dashboard['role_name'] ?? 'Pengelola Sistem' }}</div>
                        <h1 class="hero-title">Data Ruangan</h1>
                        <p class="hero-subtitle">Kelola identitas ruangan, penanggung jawab, dan ringkasan inventaris ruangan dari satu halaman superadmin yang lebih rapi.</p>
                    </div>
                    <div class="hero-actions">
                        <button type="button" class="action-btn primary js-open-modal" data-modal="create-room">
                            <i class="bi bi-building-add"></i>
                            <span>Tambah Ruangan</span>
                        </button>
                    </div>
                </section>

                <section class="summary-grid">
                    <article class="summary-card">
                        <div class="summary-label">Total Ruangan</div>
                        <div class="summary-value">{{ number_format($summary['total_ruangan']) }}</div>
                        <div class="summary-note">Semua ruangan yang tercatat di sistem.</div>
                    </article>
                    <article class="summary-card is-accent">
                        <div class="summary-label">Total Kelas</div>
                        <div class="summary-value">{{ number_format($summary['total_kelas']) }}</div>
                        <div class="summary-note">Ruangan dengan jenis kelas.</div>
                    </article>
                    <article class="summary-card">
                        <div class="summary-label">Total Laboratorium</div>
                        <div class="summary-value">{{ number_format($summary['total_laboratorium']) }}</div>
                        <div class="summary-note">Laboratorium dan ruang praktik sejenis.</div>
                    </article>
                    <article class="summary-card">
                        <div class="summary-label">Total Inventaris</div>
                        <div class="summary-value">{{ number_format($summary['total_inventaris']) }}</div>
                        <div class="summary-note">Akumulasi unit barang dari seluruh ruangan.</div>
                    </article>
                </section>

                <section class="filter-card">
                    <form method="GET" action="{{ route('superadmin.rooms') }}" class="filter-form">
                        <div class="filter-field">
                            <label for="roomSearch">Nama Ruangan</label>
                            <input id="roomSearch" type="text" name="q" value="{{ $filters['q'] }}" placeholder="Cari nama ruangan">
                        </div>
                        <div class="filter-field">
                            <label for="roomType">Jenis Ruangan</label>
                            <select id="roomType" name="type">
                                <option value="semua" @selected($filters['type'] === 'semua')>Semua Jenis</option>
                                @foreach ($typeOptions as $option)
                                    <option value="{{ $option }}" @selected($filters['type'] === $option)>{{ ucfirst($option) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-field">
                            <label for="roomUnit">Kelas</label>
                            <select id="roomUnit" name="unit">
                                <option value="semua" @selected($filters['unit'] === 'semua')>Semua Unit</option>
                                @foreach ($unitOptions as $option)
                                    <option value="{{ $option }}" @selected($filters['unit'] === $option)>{{ strtoupper($option) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-actions">
                            <button type="submit" class="filter-btn">
                                <i class="bi bi-funnel-fill"></i>
                                <span>Terapkan</span>
                            </button>
                            <a href="{{ route('superadmin.rooms') }}" class="filter-link">
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
                            <div class="table-title">Daftar Ruangan</div>
                            <div class="table-subtitle">Menampilkan {{ number_format($rooms->total()) }} ruangan berdasarkan filter aktif.</div>
                        </div>
                        <div class="table-header-actions">
                            <button type="button" class="action-btn primary js-open-modal" data-modal="create-room">
                                <i class="bi bi-building-add"></i>
                                <span>Tambah Ruangan</span>
                            </button>
                        </div>
                    </div>

                    @if (count($roomRows) === 0)
                        <div class="empty-state">Belum ada data ruangan yang cocok dengan filter saat ini.</div>
                    @else
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Ruangan</th>
                                        <th>Kode</th>
                                        <th>Jenis</th>
                                        <th>Kelas</th>
                                        <th>Wali / Penanggung Jawab</th>
                                        <th>Ketua Kelas</th>
                                        <th>Total Inventaris</th>
                                        <th>Kondisi</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($roomRows as $index => $room)
                                        @php
                                            $number = ($rooms->firstItem() ?? 1) + $index;
                                            $typeClass = $room['jenis_ruangan_raw'] === 'kelas'
                                                ? 'kelas'
                                                : (str_contains($room['jenis_ruangan_raw'], 'lab') ? 'lab' : 'other');
                                        @endphp
                                        <tr>
                                            <td>{{ $number }}</td>
                                            <td>
                                                <div class="room-name">{{ $room['nama_ruangan'] }}</div>
                                                <div class="room-meta">{{ $room['lokasi'] !== '-' ? $room['lokasi'] : 'Lokasi belum diisi' }}</div>
                                            </td>
                                            <td>{{ $room['kode_ruangan'] }}</td>
                                            <td><span class="pill {{ $typeClass }}">{{ $room['jenis_ruangan'] }}</span></td>
                                            <td>{{ $room['unit'] }}</td>
                                            <td>{{ $room['wali_kelas'] }}</td>
                                            <td>{{ $room['ketua_kelas'] }}</td>
                                            <td>
                                                <div class="inventory-summary">
                                                    <strong>{{ number_format($room['total_inventaris']) }} item</strong>
                                                    <span class="room-meta">{{ number_format($room['barang_baik']) }} baik, {{ number_format($room['barang_rusak']) }} rusak</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="pill {{ $room['kondisi_class'] }}">{{ $room['kondisi_label'] }}</span>
                                                <div class="condition-copy">{{ $room['kondisi_ringkas'] }}</div>
                                            </td>
                                            <td>
                                                <div class="action-group">
                                                    <button type="button" class="row-action js-open-modal" data-modal="edit-room-{{ $room['id_ruangan'] }}">
                                                        <i class="bi bi-pencil-square"></i>
                                                        <span>Edit</span>
                                                    </button>
                                                    <form method="POST" action="{{ route('superadmin.rooms.delete', $room['id_ruangan']) }}" onsubmit="return confirm('Hapus ruangan ini?');">
                                                        @csrf
                                                        <input type="hidden" name="q" value="{{ $filters['q'] }}">
                                                        <input type="hidden" name="type_filter" value="{{ $filters['type'] }}">
                                                        <input type="hidden" name="unit_filter" value="{{ $filters['unit'] }}">
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

                        @if ($rooms->hasPages())
                            <div class="pagination-wrap">
                                <div class="pagination">
                                    <span class="pagination-info">Halaman {{ $rooms->currentPage() }} dari {{ $rooms->lastPage() }}</span>

                                    @if ($rooms->onFirstPage())
                                        <span class="pagination-link disabled"><i class="bi bi-chevron-left"></i></span>
                                    @else
                                        <a href="{{ $rooms->previousPageUrl() }}" class="pagination-link" aria-label="Halaman sebelumnya">
                                            <i class="bi bi-chevron-left"></i>
                                        </a>
                                    @endif

                                    @foreach ($rooms->getUrlRange(1, $rooms->lastPage()) as $page => $url)
                                        @if ($page === $rooms->currentPage())
                                            <span class="pagination-current">{{ $page }}</span>
                                        @else
                                            <a href="{{ $url }}" class="pagination-link">{{ $page }}</a>
                                        @endif
                                    @endforeach

                                    @if ($rooms->hasMorePages())
                                        <a href="{{ $rooms->nextPageUrl() }}" class="pagination-link" aria-label="Halaman berikutnya">
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

    <div class="modal-shell" id="modal-create-room" aria-hidden="true">
        <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="modalCreateRoomTitle">
            <div class="modal-header">
                <div>
                    <div class="modal-title" id="modalCreateRoomTitle">Tambah Ruangan</div>
                    <div class="modal-subtitle">Tambahkan identitas ruangan baru beserta unit, jenis, lokasi, dan status penggunaannya.</div>
                </div>
                <button type="button" class="modal-close js-close-modal" aria-label="Tutup modal">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('superadmin.rooms.store') }}" class="modal-form">
                @csrf
                <input type="hidden" name="q" value="{{ $filters['q'] }}">
                <input type="hidden" name="type_filter" value="{{ $filters['type'] }}">
                <input type="hidden" name="unit_filter" value="{{ $filters['unit'] }}">

                    <div class="field-grid">
                        <div class="field-group">
                            <label for="createRoomName">Nama Ruangan</label>
                            <input id="createRoomName" type="text" name="nama_ruangan" value="{{ session('modal') === 'create-room' ? old('nama_ruangan') : '' }}" required>
                        </div>
                        <div class="field-group">
                            <label for="createRoomCode">Kode Ruangan</label>
                            <input id="createRoomCode" type="text" name="kode_ruangan_preview" value="{{ session('modal') === 'create-room' ? old('kode_ruangan_preview', old('nama_ruangan')) : '' }}" placeholder="Otomatis dari nama ruangan" readonly>
                        </div>
                        <div class="field-group">
                            <label for="createRoomType">Jenis Ruangan</label>
                            <select id="createRoomType" name="jenis_ruangan" required>
                                <option value="">Pilih jenis ruangan</option>
                                <option value="kelas" @selected(session('modal') === 'create-room' && old('jenis_ruangan') === 'kelas')>Kelas</option>
                                <option value="lab" @selected(session('modal') === 'create-room' && old('jenis_ruangan') === 'lab')>Lab</option>
                                <option value="kantor_guru" @selected(session('modal') === 'create-room' && old('jenis_ruangan') === 'kantor_guru')>Kantor Guru</option>
                            </select>
                        </div>
                        <div class="field-group">
                            <label for="createRoomUnit">Kelas</label>
                            <input id="createRoomUnit" type="text" name="unit" value="{{ session('modal') === 'create-room' ? old('unit') : '' }}" placeholder="Contoh: XIA" required>
                        </div>
                        <div class="field-group">
                            <label for="createRoomLocation">Lantai</label>
                            <select id="createRoomLocation" name="lokasi" required>
                                <option value="">Pilih lantai</option>
                                <option value="Lantai 1" @selected(session('modal') === 'create-room' && old('lokasi') === 'Lantai 1')>Lantai 1</option>
                                <option value="Lantai 2" @selected(session('modal') === 'create-room' && old('lokasi') === 'Lantai 2')>Lantai 2</option>
                                <option value="Lantai 3" @selected(session('modal') === 'create-room' && old('lokasi') === 'Lantai 3')>Lantai 3</option>
                                <option value="Lantai 4" @selected(session('modal') === 'create-room' && old('lokasi') === 'Lantai 4')>Lantai 4</option>
                            </select>
                        </div>
                    </div>

                <div class="modal-actions">
                    <button type="button" class="ghost-btn js-close-modal">Batal</button>
                    <button type="submit" class="submit-btn">
                        <i class="bi bi-check2-circle"></i>
                        <span>Simpan Ruangan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @foreach ($roomRows as $room)
        @php
            $editModalId = 'edit-room-'.$room['id_ruangan'];
            $isEditModalOpen = session('modal') === $editModalId;
        @endphp

        <div class="modal-shell" id="modal-{{ $editModalId }}" aria-hidden="true">
            <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="modalEditRoomTitle-{{ $room['id_ruangan'] }}">
                <div class="modal-header">
                    <div>
                        <div class="modal-title" id="modalEditRoomTitle-{{ $room['id_ruangan'] }}">Edit Ruangan</div>
                        <div class="modal-subtitle">Perbarui identitas dan informasi penggunaan untuk {{ $room['nama_ruangan'] }}.</div>
                    </div>
                    <button type="button" class="modal-close js-close-modal" aria-label="Tutup modal">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('superadmin.rooms.update', $room['id_ruangan']) }}" class="modal-form">
                    @csrf
                    <input type="hidden" name="q" value="{{ $filters['q'] }}">
                    <input type="hidden" name="type_filter" value="{{ $filters['type'] }}">
                    <input type="hidden" name="unit_filter" value="{{ $filters['unit'] }}">

                    <div class="field-grid">
                        <div class="field-group">
                            <label for="editRoomName{{ $room['id_ruangan'] }}">Nama Ruangan</label>
                            <input id="editRoomName{{ $room['id_ruangan'] }}" type="text" name="nama_ruangan" value="{{ $isEditModalOpen ? old('nama_ruangan', $room['nama_ruangan']) : $room['nama_ruangan'] }}" required>
                        </div>
                        <div class="field-group">
                            <label for="editRoomCode{{ $room['id_ruangan'] }}">Kode Ruangan</label>
                            <input id="editRoomCode{{ $room['id_ruangan'] }}" type="text" name="kode_ruangan_preview" value="{{ $isEditModalOpen ? old('kode_ruangan_preview', $room['kode_ruangan']) : $room['kode_ruangan'] }}" placeholder="Otomatis dari nama ruangan" readonly>
                        </div>
                        <div class="field-group">
                            <label for="editRoomType{{ $room['id_ruangan'] }}">Jenis Ruangan</label>
                            @php
                                $selectedRoomType = $isEditModalOpen ? old('jenis_ruangan', $room['jenis_ruangan_raw']) : $room['jenis_ruangan_raw'];
                            @endphp
                            <select id="editRoomType{{ $room['id_ruangan'] }}" name="jenis_ruangan" required>
                                <option value="kelas" @selected($selectedRoomType === 'kelas')>Kelas</option>
                                <option value="lab" @selected($selectedRoomType === 'lab')>Lab</option>
                                <option value="kantor_guru" @selected($selectedRoomType === 'kantor_guru')>Kantor Guru</option>
                            </select>
                        </div>
                        <div class="field-group">
                            <label for="editRoomUnit{{ $room['id_ruangan'] }}">Kelas</label>
                            <input id="editRoomUnit{{ $room['id_ruangan'] }}" type="text" name="unit" value="{{ $isEditModalOpen ? old('unit', $room['unit']) : $room['unit'] }}" required>
                        </div>
                        <div class="field-group">
                            <label for="editRoomLocation{{ $room['id_ruangan'] }}">Lantai</label>
                            @php
                                $selectedFloor = $isEditModalOpen ? old('lokasi', $room['lokasi'] === '-' ? '' : $room['lokasi']) : ($room['lokasi'] === '-' ? '' : $room['lokasi']);
                            @endphp
                            <select id="editRoomLocation{{ $room['id_ruangan'] }}" name="lokasi" required>
                                <option value="Lantai 1" @selected($selectedFloor === 'Lantai 1')>Lantai 1</option>
                                <option value="Lantai 2" @selected($selectedFloor === 'Lantai 2')>Lantai 2</option>
                                <option value="Lantai 3" @selected($selectedFloor === 'Lantai 3')>Lantai 3</option>
                                <option value="Lantai 4" @selected($selectedFloor === 'Lantai 4')>Lantai 4</option>
                            </select>
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
    @endforeach

    <script>
        (function () {
            const page = document.body;
            const modalTriggers = document.querySelectorAll('.js-open-modal');
            const closeButtons = document.querySelectorAll('.js-close-modal');
            const modalShells = document.querySelectorAll('.modal-shell');
            const codeRules = [
                { nameId: 'createRoomName', codeId: 'createRoomCode' },
                @foreach ($roomRows as $room)
                { nameId: 'editRoomName{{ $room['id_ruangan'] }}', codeId: 'editRoomCode{{ $room['id_ruangan'] }}' },
                @endforeach
            ];

            function generateRoomCode(value) {
                return (value || '')
                    .toUpperCase()
                    .trim()
                    .replace(/[^A-Z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }

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

            codeRules.forEach((rule) => {
                const nameInput = document.getElementById(rule.nameId);
                const codeInput = document.getElementById(rule.codeId);

                if (!nameInput || !codeInput) {
                    return;
                }

                const syncCode = () => {
                    codeInput.value = generateRoomCode(nameInput.value);
                };

                syncCode();
                nameInput.addEventListener('input', syncCode);
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
