<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laporan | InfraSPH</title>
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

        .superadmin-reports-page {
            margin-left: 320px;
            width: calc(100% - 320px);
            min-height: 100vh;
            padding: 2rem 1.6rem 2.5rem;
            transition: margin-left 0.28s ease, width 0.28s ease, padding 0.28s ease;
        }

        .app-shell.sidebar-collapsed .superadmin-reports-page {
            margin-left: 88px;
            width: calc(100% - 88px);
        }

        .page-shell { width: 100%; max-width: none; }

        .hero-card,
        .tab-card,
        .filter-card,
        .summary-card,
        .table-card,
        .empty-card {
            background: #ffffff;
            border: 1px solid var(--panel-border);
            border-radius: 28px;
            box-shadow: 0 18px 38px -28px rgba(31, 41, 55, 0.24);
        }

        .hero-card {
            padding: 1.6rem 1.7rem;
            margin-bottom: 1.2rem;
            background: linear-gradient(135deg, rgba(255, 89, 0, 0.08), rgba(255, 89, 0, 0.02));
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
            font-size: clamp(1.8rem, 2.6vw, 2.4rem);
            color: var(--brand-orange);
            margin-bottom: 0.7rem;
            letter-spacing: -0.04em;
        }

        .hero-subtitle {
            color: #5b6472;
            line-height: 1.7;
            max-width: 860px;
        }

        .tab-card,
        .filter-card {
            padding: 1rem 1.05rem;
            margin-bottom: 1rem;
        }

        .tab-row,
        .filter-controls,
        .filter-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .tab-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            min-width: 170px;
            padding: 0.9rem 1rem;
            border-radius: 18px;
            border: 1px solid #ead9d0;
            background: #ffffff;
            color: #344054;
            font-size: 0.96rem;
            font-weight: 700;
            text-decoration: none;
        }

        .tab-pill.active {
            color: #ffffff;
            border-color: transparent;
            background: linear-gradient(135deg, var(--brand-orange), #ff7b2f);
        }

        .filter-form {
            display: flex;
            gap: 0.9rem;
            flex-wrap: wrap;
            align-items: end;
            justify-content: space-between;
        }

        .filter-left {
            display: flex;
            gap: 0.9rem;
            flex-wrap: wrap;
            align-items: end;
        }

        .filter-field label {
            display: block;
            color: #6b7280;
            font-size: 0.8rem;
            font-weight: 700;
            margin-bottom: 0.45rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .filter-field input,
        .filter-field select {
            min-width: 170px;
            border: 1px solid #ecd8cb;
            border-radius: 16px;
            padding: 0.92rem 1rem;
            font: inherit;
            font-size: 0.94rem;
            color: #172033;
            background: #fffdfa;
            outline: none;
        }

        .filter-btn,
        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            padding: 0.92rem 1rem;
            border-radius: 16px;
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

        .action-btn {
            border: 1px solid #ead9d0;
            background: #ffffff;
            color: #344054;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.95rem;
            margin-bottom: 1rem;
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
            min-width: 1040px;
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
        .pill.warning, .pill.process { background: #fff4db; color: #a16207; }
        .pill.danger, .pill.rejected { background: #fff1f2; color: #be123c; }
        .pill.info { background: #eef6ff; color: #1d4ed8; }

        .empty-card {
            padding: 2.4rem 1.4rem;
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

        @media (max-width: 1200px) {
            .summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 860px) {
            .superadmin-reports-page,
            .app-shell.sidebar-collapsed .superadmin-reports-page {
                width: 100%;
                margin-left: 0;
                padding: 5.3rem 1rem 1.8rem;
            }

            .summary-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="app-shell" id="appShell">
        @include('header')

        <main class="superadmin-reports-page">
            <div class="page-shell">
                <section class="hero-card">
                    <div class="eyebrow">{{ $dashboard['role_name'] ?? 'Pengelola Sistem' }}</div>
                    <h1 class="hero-title">Laporan</h1>
                    <p class="hero-subtitle">Pantau inventaris sekolah, barang masuk, kondisi barang, dan realisasi pengajuan dari satu halaman rekap yang siap difilter dan diexport.</p>
                </section>

                <section class="filter-card">
                    <form method="GET" action="{{ route('superadmin.reports') }}" class="filter-form">
                        <input type="hidden" name="section" value="{{ $section }}">
                        <div class="filter-left">
                            <div class="filter-field">
                                <label for="dateFrom">Periode Dari</label>
                                <input id="dateFrom" type="date" name="date_from" value="{{ $filters['date_from'] }}">
                            </div>
                            <div class="filter-field">
                                <label for="dateTo">Periode Sampai</label>
                                <input id="dateTo" type="date" name="date_to" value="{{ $filters['date_to'] }}">
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
                                <label for="typeFilter">Jenis Ruangan</label>
                                <select id="typeFilter" name="room_type">
                                    <option value="semua" @selected($filters['room_type'] === 'semua')>Semua Jenis</option>
                                    @foreach ($roomTypeOptions as $option)
                                        <option value="{{ $option }}" @selected($filters['room_type'] === $option)>{{ $option === 'kantor_guru' ? 'Kantor Guru' : ucfirst($option) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="filter-field">
                                <label for="categoryFilter">Kategori Barang</label>
                                <select id="categoryFilter" name="category">
                                    <option value="semua" @selected($filters['category'] === 'semua')>Semua Kategori</option>
                                    @foreach ($categoryOptions as $option)
                                        <option value="{{ $option->id_kategori_barang }}" @selected($filters['category'] === (string) $option->id_kategori_barang)>{{ ucfirst($option->nama_kategori) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="filter-field">
                                <label for="conditionFilter">Kondisi Barang</label>
                                <select id="conditionFilter" name="condition">
                                    <option value="semua" @selected($filters['condition'] === 'semua')>Semua Kondisi</option>
                                    <option value="baik" @selected($filters['condition'] === 'baik')>Baik</option>
                                    <option value="rusak" @selected($filters['condition'] === 'rusak')>Rusak</option>
                                    <option value="perlu_perbaikan" @selected($filters['condition'] === 'perlu_perbaikan')>Perlu Perbaikan</option>
                                </select>
                            </div>
                            <div class="filter-field">
                                <label for="requestStatusFilter">Status Pengajuan / Realisasi</label>
                                <select id="requestStatusFilter" name="request_status">
                                    <option value="semua" @selected($filters['request_status'] === 'semua')>Semua Status</option>
                                    <option value="direalisasi" @selected($filters['request_status'] === 'direalisasi')>Direalisasi</option>
                                    <option value="menunggu" @selected($filters['request_status'] === 'menunggu')>Menunggu</option>
                                    <option value="ditolak" @selected($filters['request_status'] === 'ditolak')>Ditolak</option>
                                </select>
                            </div>
                        </div>
                        <div class="filter-actions">
                            <button type="submit" class="filter-btn">
                                <i class="bi bi-funnel-fill"></i>
                                <span>Terapkan</span>
                            </button>
                            <a href="{{ route('superadmin.reports', ['section' => $section]) }}" class="action-btn">
                                <i class="bi bi-arrow-counterclockwise"></i>
                                <span>Reset</span>
                            </a>
                            <a href="{{ route('superadmin.reports.export', array_merge(['section' => $section, 'format' => 'excel'], $filters)) }}" class="action-btn">
                                <i class="bi bi-file-earmark-excel"></i>
                                <span>Export Excel</span>
                            </a>
                            <a href="{{ route('superadmin.reports.export', array_merge(['section' => $section, 'format' => 'word'], $filters)) }}" class="action-btn">
                                <i class="bi bi-file-earmark-word"></i>
                                <span>Export Word</span>
                            </a>
                            <a href="{{ route('superadmin.reports.export', array_merge(['section' => $section, 'format' => 'print'], $filters)) }}" class="action-btn" target="_blank" rel="noopener noreferrer">
                                <i class="bi bi-printer"></i>
                                <span>Cetak / PDF</span>
                            </a>
                        </div>
                    </form>
                </section>

                <section class="summary-grid">
                    <article class="summary-card">
                        <div class="summary-label">Total Inventaris</div>
                        <div class="summary-value">{{ number_format($summary['total_inventaris']) }}</div>
                        <div class="summary-note">Total unit barang dari seluruh inventaris sekolah.</div>
                    </article>
                    <article class="summary-card is-accent">
                        <div class="summary-label">Total Barang Masuk</div>
                        <div class="summary-value">{{ number_format($summary['total_barang_masuk']) }}</div>
                        <div class="summary-note">Akumulasi barang masuk dari data realisasi yang tercatat.</div>
                    </article>
                    <article class="summary-card">
                        <div class="summary-label">Total Barang Rusak</div>
                        <div class="summary-value">{{ number_format($summary['total_barang_rusak']) }}</div>
                        <div class="summary-note">Unit barang rusak di seluruh ruangan.</div>
                    </article>
                    <article class="summary-card">
                        <div class="summary-label">Pengajuan Direalisasi</div>
                        <div class="summary-value">{{ number_format($summary['total_pengajuan_direalisasi']) }}</div>
                        <div class="summary-note">Jumlah pengajuan yang sudah masuk inventaris.</div>
                    </article>
                </section>

                <section class="tab-card">
                    <div class="tab-row">
                        <a href="{{ route('superadmin.reports', array_merge($filters, ['section' => 'inventory'])) }}" @class(['tab-pill', 'active' => $section === 'inventory'])>
                            <i class="bi bi-box-seam"></i>
                            <span>Inventaris</span>
                        </a>
                        <a href="{{ route('superadmin.reports', array_merge($filters, ['section' => 'incoming'])) }}" @class(['tab-pill', 'active' => $section === 'incoming'])>
                            <i class="bi bi-box-arrow-in-down"></i>
                            <span>Barang Masuk</span>
                        </a>
                        <a href="{{ route('superadmin.reports', array_merge($filters, ['section' => 'condition'])) }}" @class(['tab-pill', 'active' => $section === 'condition'])>
                            <i class="bi bi-clipboard2-pulse"></i>
                            <span>Kondisi Barang</span>
                        </a>
                        <a href="{{ route('superadmin.reports', array_merge($filters, ['section' => 'requests'])) }}" @class(['tab-pill', 'active' => $section === 'requests'])>
                            <i class="bi bi-journal-check"></i>
                            <span>Pengajuan</span>
                        </a>
                    </div>
                </section>

                <section class="table-card">
                    <div class="table-header">
                        <div>
                            <div class="table-title">
                                @if ($section === 'incoming')
                                    Laporan Barang Masuk ke Ruangan
                                @elseif ($section === 'condition')
                                    Laporan Kondisi Barang
                                @elseif ($section === 'requests')
                                    Laporan Pengajuan Direalisasi
                                @else
                                    Laporan Inventaris per Ruangan
                                @endif
                            </div>
                            <div class="table-subtitle">Menampilkan {{ number_format($rows->total()) }} data berdasarkan filter aktif.</div>
                        </div>
                    </div>

                    @if ($rows->count() === 0)
                        <div class="empty-card">Belum ada data laporan yang cocok dengan filter saat ini.</div>
                    @else
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    @if ($section === 'incoming')
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Nama Barang</th>
                                            <th>Ruangan</th>
                                            <th>Jenis Ruangan</th>
                                            <th>Jumlah</th>
                                            <th>Sumber</th>
                                            <th>Ditambahkan Oleh</th>
                                        </tr>
                                    @elseif ($section === 'condition')
                                        <tr>
                                            <th>Nama Barang</th>
                                            <th>Ruangan</th>
                                            <th>Jumlah Baik</th>
                                            <th>Jumlah Rusak</th>
                                            <th>Kondisi</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    @elseif ($section === 'requests')
                                        <tr>
                                            <th>Pengaju</th>
                                            <th>Barang</th>
                                            <th>Ruangan</th>
                                            <th>Jumlah</th>
                                            <th>Tanggal Pengajuan</th>
                                            <th>Tanggal Realisasi</th>
                                            <th>Status Admin</th>
                                            <th>Status Owner</th>
                                            <th>Status Realisasi</th>
                                        </tr>
                                    @else
                                        <tr>
                                            <th>Ruangan</th>
                                            <th>Barang</th>
                                            <th>Kategori</th>
                                            <th>Jumlah</th>
                                            <th>Kondisi</th>
                                            <th>Tanggal Masuk</th>
                                        </tr>
                                    @endif
                                </thead>
                                <tbody>
                                    @foreach ($rows as $row)
                                        @if ($section === 'incoming')
                                            <tr>
                                                <td>{{ $row['tanggal'] }}</td>
                                                <td>{{ $row['barang'] }}</td>
                                                <td>{{ $row['ruangan'] }}</td>
                                                <td>{{ $row['jenis_ruangan'] }}</td>
                                                <td>{{ number_format($row['jumlah']) }}</td>
                                                <td>{{ $row['sumber'] }}</td>
                                                <td>{{ $row['ditambahkan_oleh'] }}</td>
                                            </tr>
                                        @elseif ($section === 'condition')
                                            <tr>
                                                <td>{{ $row['barang'] }}</td>
                                                <td>{{ $row['ruangan'] }}</td>
                                                <td>{{ number_format($row['jumlah_baik']) }}</td>
                                                <td>{{ number_format($row['jumlah_rusak']) }}</td>
                                                <td><span class="pill {{ $row['kondisi_class'] }}">{{ $row['kondisi'] }}</span></td>
                                                <td>{{ $row['keterangan'] }}</td>
                                            </tr>
                                        @elseif ($section === 'requests')
                                            <tr>
                                                <td>{{ $row['pengaju'] }}</td>
                                                <td>{{ $row['barang'] }}</td>
                                                <td>{{ $row['ruangan'] }}</td>
                                                <td>{{ number_format($row['jumlah']) }}</td>
                                                <td>{{ $row['tanggal_pengajuan'] }}</td>
                                                <td>{{ $row['tanggal_realisasi'] }}</td>
                                                <td>{{ $row['status_admin'] }}</td>
                                                <td>{{ $row['status_owner'] }}</td>
                                                <td><span class="pill {{ $row['status_realisasi_class'] }}">{{ $row['status_realisasi'] }}</span></td>
                                            </tr>
                                        @else
                                            <tr>
                                                <td>{{ $row['ruangan'] }}</td>
                                                <td>{{ $row['barang'] }}</td>
                                                <td>{{ $row['kategori'] }}</td>
                                                <td>{{ number_format($row['jumlah']) }}</td>
                                                <td><span class="pill {{ $row['kondisi_class'] }}">{{ $row['kondisi'] }}</span></td>
                                                <td>{{ $row['tanggal_masuk'] }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if ($rows->hasPages())
                            <div class="pagination-wrap">
                                <div class="pagination">
                                    <span class="pagination-info">Halaman {{ $rows->currentPage() }} dari {{ $rows->lastPage() }}</span>

                                    @if ($rows->onFirstPage())
                                        <span class="pagination-link disabled"><i class="bi bi-chevron-left"></i></span>
                                    @else
                                        <a href="{{ $rows->previousPageUrl() }}" class="pagination-link" aria-label="Halaman sebelumnya">
                                            <i class="bi bi-chevron-left"></i>
                                        </a>
                                    @endif

                                    @foreach ($rows->getUrlRange(1, $rows->lastPage()) as $page => $url)
                                        @if ($page === $rows->currentPage())
                                            <span class="pagination-current">{{ $page }}</span>
                                        @else
                                            <a href="{{ $url }}" class="pagination-link">{{ $page }}</a>
                                        @endif
                                    @endforeach

                                    @if ($rows->hasMorePages())
                                        <a href="{{ $rows->nextPageUrl() }}" class="pagination-link" aria-label="Halaman berikutnya">
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
</body>
</html>
