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
            --text-dark: #1f2937;
            --page-bg: #fff8f4;
            --panel-border: #f3e3db;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            background: var(--page-bg);
            color: var(--text-dark);
        }

        .owner-reports-page {
            margin-left: 320px;
            width: calc(100% - 320px);
            min-height: 100vh;
            padding: 2rem 1.6rem 2.5rem;
            transition: margin-left 0.28s ease, width 0.28s ease, padding 0.28s ease;
        }

        .app-shell.sidebar-collapsed .owner-reports-page {
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
            max-width: 820px;
        }

        .tab-card,
        .filter-card {
            padding: 1rem 1.05rem;
            margin-bottom: 1rem;
        }

        .tab-row,
        .filter-row {
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

        .filter-controls {
            display: flex;
            gap: 0.9rem;
            flex-wrap: wrap;
            align-items: end;
        }

        .filter-actions {
            display: flex;
            gap: 0.75rem;
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

        .filter-field select {
            min-width: 180px;
            border: 1px solid #ecd8cb;
            border-radius: 16px;
            padding: 0.92rem 1rem;
            font: inherit;
            font-size: 0.94rem;
            color: #172033;
            background: #fffdfa;
            outline: none;
        }

        .filter-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            border-radius: 16px;
            padding: 0.92rem 1rem;
            font-size: 0.9rem;
            font-weight: 700;
            background: linear-gradient(135deg, #ff5900, #ff7b2f);
            color: #ffffff;
            cursor: pointer;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            padding: 0.92rem 1rem;
            border-radius: 16px;
            border: 1px solid #ead9d0;
            background: #ffffff;
            color: #344054;
            font-size: 0.9rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .action-btn:hover {
            border-color: rgba(255, 89, 0, 0.28);
            color: #ff5900;
            background: #fff8f4;
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
            font-size: 1.75rem;
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

        .table-wrap {
            width: 100%;
            overflow-x: hidden;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 0;
            table-layout: fixed;
        }

        .report-table th,
        .report-table td {
            padding: 1rem 1.05rem;
            text-align: left;
            border-bottom: 1px solid #f3e3db;
            vertical-align: top;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .report-table th {
            color: #6b7280;
            font-size: 0.8rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            background: #fff8f4;
        }

        .report-table tbody tr:last-child td {
            border-bottom: none;
        }

        .cell-primary {
            color: #172033;
            font-size: 1rem;
            font-weight: 800;
            margin-bottom: 0.2rem;
        }

        .cell-secondary {
            color: #7b8794;
            font-size: 0.84rem;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: 0.45rem 0.8rem;
            font-size: 0.78rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .badge.process { background: #fff6db; color: #a16207; }
        .badge.approved { background: #eaf8ef; color: #15803d; }
        .badge.rejected { background: #fff0eb; color: #c2410c; }

        .empty-card {
            padding: 1.25rem 1.35rem;
            color: #667085;
            line-height: 1.65;
        }

        .report-print-area {
            display: grid;
            gap: 1rem;
        }

        @media print {
            @page {
                size: auto;
                margin: 14mm;
            }

            html,
            body {
                background: #ffffff;
                min-height: auto !important;
                height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .app-shell > :not(.owner-reports-page) {
                display: none !important;
            }

            .owner-reports-page > .page-shell > :not(.report-print-area) {
                display: none !important;
            }

            .report-print-area {
                width: 100%;
                display: block;
                position: static !important;
                margin: 0 !important;
                padding: 0 !important;
                max-width: 860px;
                margin-left: auto !important;
                margin-right: auto !important;
            }

            .app-shell,
            .owner-reports-page,
            .page-shell {
                min-height: auto !important;
                height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }

            .owner-reports-page {
                box-shadow: none !important;
                overflow: visible !important;
            }

            .summary-card,
            .table-card,
            .empty-card {
                box-shadow: none;
                border-color: #d9d9d9;
                break-inside: avoid;
            }

            .summary-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }

            .report-print-area .summary-grid {
                display: none !important;
            }

            .table-wrap {
                overflow: visible;
            }

            .report-table {
                min-width: 0;
            }
        }

        @media (max-width: 1180px) {
            .summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 860px) {
            .owner-reports-page {
                margin-left: 0;
                width: 100%;
                padding: 1.2rem 1rem 2rem;
            }

            .filter-form {
                justify-content: flex-start;
            }

            .filter-controls,
            .filter-actions {
                width: 100%;
            }

            .summary-grid {
                grid-template-columns: 1fr;
            }

            .table-wrap {
                overflow-x: auto;
            }

            .report-table {
                min-width: 820px;
                table-layout: auto;
            }
        }
    </style>
</head>
<body>
    <div class="app-shell" id="appShell">
        @include('header')

        <main class="owner-reports-page">
            <div class="page-shell">
            <section class="hero-card">
                <div class="eyebrow">Kepala Sekolah</div>
                <h1 class="hero-title">Laporan</h1>
                <p class="hero-subtitle">Lihat ringkasan data inventaris dan pengajuan di seluruh sekolah untuk membantu monitoring dan pengambilan keputusan.</p>
            </section>

            <section class="tab-card">
                <div class="tab-row">
                    <a href="{{ route('owner.reports', ['section' => 'inventory', 'month' => $month, 'year' => $year]) }}" @class(['tab-pill', 'active' => $section === 'inventory'])>Laporan Inventaris</a>
                    <a href="{{ route('owner.reports', ['section' => 'requests', 'month' => $month, 'year' => $year]) }}" @class(['tab-pill', 'active' => $section === 'requests'])>Laporan Pengajuan</a>
                    <a href="{{ route('owner.reports', ['section' => 'classes', 'month' => $month, 'year' => $year]) }}" @class(['tab-pill', 'active' => $section === 'classes'])>Laporan Per Kelas</a>
                </div>
            </section>

            <section class="filter-card">
                <form method="GET" class="filter-form">
                    <input type="hidden" name="section" value="{{ $section }}">
                    <div class="filter-controls">
                        <div class="filter-field">
                            <label for="month">Bulan</label>
                            <select id="month" name="month">
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" @selected($month === $i)>{{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="filter-field">
                            <label for="year">Tahun</label>
                            <select id="year" name="year">
                                @foreach ($yearOptions as $option)
                                    <option value="{{ $option }}" @selected($year === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="filter-btn">Terapkan</button>
                        <button type="button" class="action-btn" onclick="window.print()">
                            <i class="bi bi-printer"></i>
                            Print
                        </button>
                        <a href="{{ route('owner.reports.export', ['section' => $section, 'month' => $month, 'year' => $year, 'format' => 'excel']) }}" class="action-btn">
                            <i class="bi bi-file-earmark-excel"></i>
                            Export Excel
                        </a>
                        <a href="{{ route('owner.reports.export', ['section' => $section, 'month' => $month, 'year' => $year, 'format' => 'word']) }}" class="action-btn">
                            <i class="bi bi-file-earmark-word"></i>
                            Export Word
                        </a>
                    </div>
                </form>
            </section>

            <div class="report-print-area">
            @if ($section === 'inventory')
                <section class="summary-grid">
                    <article class="summary-card">
                        <div class="summary-label">Total Jenis Barang</div>
                        <div class="summary-value">{{ number_format($inventorySummary['total_jenis']) }}</div>
                        <div class="summary-note">Jenis barang yang tercatat di seluruh sekolah</div>
                    </article>
                    <article class="summary-card is-accent">
                        <div class="summary-label">Total Barang</div>
                        <div class="summary-value">{{ number_format($inventorySummary['total_barang']) }}</div>
                        <div class="summary-note">Jumlah barang dari seluruh ruangan</div>
                    </article>
                    <article class="summary-card">
                        <div class="summary-label">Barang Baik</div>
                        <div class="summary-value">{{ number_format($inventorySummary['barang_baik']) }}</div>
                        <div class="summary-note">Barang dalam kondisi baik</div>
                    </article>
                    <article class="summary-card">
                        <div class="summary-label">Perlu Perhatian</div>
                        <div class="summary-value">{{ number_format($inventorySummary['barang_rusak']) }}</div>
                        <div class="summary-note">Barang yang perlu diperhatikan</div>
                    </article>
                </section>

                @if ($inventoryRows->isEmpty())
                    <section class="empty-card">Belum ada data inventaris sekolah.</section>
                @else
                    <section class="table-card">
                        <div class="table-wrap">
                            <table class="report-table">
                                <thead>
                                    <tr>
                                        <th>Barang</th>
                                        <th>Total</th>
                                        <th>Baik</th>
                                        <th>Rusak</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($inventoryRows as $row)
                                        <tr>
                                            <td><div class="cell-primary">{{ $row['nama_barang'] }}</div></td>
                                            <td>{{ number_format($row['total']) }}</td>
                                            <td>{{ number_format($row['baik']) }}</td>
                                            <td>{{ number_format($row['rusak']) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif
            @elseif ($section === 'requests')
                <section class="summary-grid">
                    <article class="summary-card">
                        <div class="summary-label">Total Pengajuan</div>
                        <div class="summary-value">{{ number_format($requestSummary['total']) }}</div>
                        <div class="summary-note">Pengajuan pada periode yang dipilih</div>
                    </article>
                    <article class="summary-card is-accent">
                        <div class="summary-label">Diproses</div>
                        <div class="summary-value">{{ number_format($requestSummary['process']) }}</div>
                        <div class="summary-note">Pengajuan yang masih dalam proses</div>
                    </article>
                    <article class="summary-card">
                        <div class="summary-label">Disetujui</div>
                        <div class="summary-value">{{ number_format($requestSummary['approved']) }}</div>
                        <div class="summary-note">Pengajuan yang disetujui</div>
                    </article>
                    <article class="summary-card">
                        <div class="summary-label">Ditolak</div>
                        <div class="summary-value">{{ number_format($requestSummary['rejected']) }}</div>
                        <div class="summary-note">Pengajuan yang ditolak</div>
                    </article>
                </section>

                @if ($requestRows->isEmpty())
                    <section class="empty-card">Belum ada data pengajuan pada periode yang dipilih.</section>
                @else
                    <section class="table-card">
                        <div class="table-wrap">
                            <table class="report-table">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Barang</th>
                                        <th>Kelas</th>
                                        <th>Peminta</th>
                                        <th>Jenis</th>
                                        <th>Jumlah</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($requestRows as $row)
                                        <tr>
                                            <td>{{ $row['tanggal'] }}</td>
                                            <td><div class="cell-primary">{{ $row['barang'] }}</div></td>
                                            <td>{{ $row['kelas'] }}</td>
                                            <td>{{ $row['peminta'] }}</td>
                                            <td>{{ $row['jenis'] }}</td>
                                            <td>{{ number_format($row['jumlah']) }}</td>
                                            <td><span class="badge {{ $row['status_class'] }}">{{ $row['status'] }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif
            @else
                <section class="summary-grid">
                    <article class="summary-card">
                        <div class="summary-label">Total Kelas</div>
                        <div class="summary-value">{{ number_format($classSummary['total_kelas']) }}</div>
                        <div class="summary-note">Jumlah kelas yang tercatat dalam laporan</div>
                    </article>
                    <article class="summary-card is-accent">
                        <div class="summary-label">Total Barang</div>
                        <div class="summary-value">{{ number_format($classSummary['total_barang']) }}</div>
                        <div class="summary-note">Total inventaris pada seluruh kelas</div>
                    </article>
                    <article class="summary-card">
                        <div class="summary-label">Barang Rusak</div>
                        <div class="summary-value">{{ number_format($classSummary['barang_rusak']) }}</div>
                        <div class="summary-note">Barang yang perlu perhatian</div>
                    </article>
                    <article class="summary-card">
                        <div class="summary-label">Total Pengajuan</div>
                        <div class="summary-value">{{ number_format($classSummary['total_pengajuan']) }}</div>
                        <div class="summary-note">Pengajuan seluruh kelas pada periode ini</div>
                    </article>
                </section>

                @if ($classRows->isEmpty())
                    <section class="empty-card">Belum ada data kelas yang dapat ditampilkan.</section>
                @else
                    <section class="table-card">
                        <div class="table-wrap">
                            <table class="report-table">
                                <thead>
                                    <tr>
                                        <th>Kelas</th>
                                        <th>Total Barang</th>
                                        <th>Baik</th>
                                        <th>Rusak</th>
                                        <th>Pengajuan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($classRows as $row)
                                        <tr>
                                            <td>
                                                <div class="cell-primary">{{ $row['kelas'] }}</div>
                                                <div class="cell-secondary">{{ $row['kode'] }}</div>
                                            </td>
                                            <td>{{ number_format($row['total_barang']) }}</td>
                                            <td>{{ number_format($row['baik']) }}</td>
                                            <td>{{ number_format($row['rusak']) }}</td>
                                            <td>{{ number_format($row['pengajuan']) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif
            @endif
            </div>
            </div>
        </main>

        @include('chatbot')
    </div>
</body>
</html>
