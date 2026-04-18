<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kelas Saya | InfraSPH</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --brand-orange: #ff5900;
            --text-dark: #1f2937;
            --page-bg: #fff8f4;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            background: var(--page-bg);
            color: var(--text-dark);
        }

        .wali-page {
            margin-left: 320px;
            min-height: 100vh;
            padding: 2rem 1.6rem 2.5rem;
            width: calc(100% - 320px);
            transition: margin-left 0.28s ease, width 0.28s ease, padding 0.28s ease;
        }

        .app-shell.sidebar-collapsed .wali-page {
            margin-left: 88px;
            width: calc(100% - 88px);
        }

        .page-shell { width: 100%; max-width: none; }

        .hero-card,
        .room-card,
        .summary-card {
            background: #ffffff;
            border: 1px solid #f3e3db;
            border-radius: 28px;
            box-shadow: 0 18px 38px -28px rgba(31, 41, 55, 0.24);
        }

        .hero-card {
            padding: 1.6rem 1.7rem;
            margin-bottom: 1.4rem;
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

        .room-stack { display: grid; gap: 1.2rem; }
        .room-card { padding: 1.35rem 1.3rem; }
        .room-shell { display: grid; gap: 1rem; }
        .room-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .room-title {
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--brand-orange);
        }

        .room-meta {
            color: #667085;
            font-size: 0.92rem;
            line-height: 1.6;
        }

        .room-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.55rem 0.8rem;
            border-radius: 999px;
            background: #fff3eb;
            color: #e14f00;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.95rem;
        }

        .summary-card {
            padding: 1.05rem 1.1rem;
            border-radius: 22px;
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
        }

        .summary-card.is-accent {
            background: linear-gradient(135deg, #ff6a17, #ff5900);
            border-color: transparent;
        }

        .summary-card.is-accent .summary-label,
        .summary-card.is-accent .summary-value,
        .summary-card.is-accent .summary-note {
            color: #fffaf6;
        }

        .room-section-title {
            font-size: 1.02rem;
            font-weight: 800;
            color: #172033;
            margin-bottom: 0.85rem;
        }

        .room-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.6fr) minmax(320px, 0.9fr);
            gap: 1rem;
            align-items: start;
        }

        .insight-stack {
            display: grid;
            gap: 1rem;
        }

        .insight-card {
            background: #ffffff;
            border: 1px solid #f3e3db;
            border-radius: 22px;
            padding: 1rem 1.05rem;
        }

        .insight-list {
            display: grid;
            gap: 0.7rem;
        }

        .insight-item {
            display: flex;
            align-items: flex-start;
            gap: 0.65rem;
            color: #4b5563;
            line-height: 1.6;
        }

        .insight-dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: #ff7b2f;
            flex-shrink: 0;
            margin-top: 0.45rem;
        }

        .latest-list {
            display: grid;
            gap: 0.75rem;
        }

        .latest-item {
            padding: 0.9rem 0.95rem;
            border-radius: 18px;
            background: #fff8f4;
            border: 1px solid #f6e4da;
        }

        .latest-title {
            font-weight: 700;
            color: #172033;
            margin-bottom: 0.2rem;
        }

        .latest-meta {
            color: #7b8794;
            font-size: 0.84rem;
            line-height: 1.5;
        }

        .quick-links {
            display: grid;
            gap: 0.75rem;
        }

        .quick-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.8rem;
            padding: 0.95rem 1rem;
            border-radius: 18px;
            border: 1px solid #f3e3db;
            background: #fffdfa;
            color: #172033;
            text-decoration: none;
            font-weight: 700;
        }

        .quick-link i:last-child {
            color: #ff5900;
        }

        .inventory-table-wrap {
            overflow-x: hidden;
            border: 1px solid #f3e3db;
            border-radius: 22px;
            background: linear-gradient(180deg, #fffdfa, #ffffff);
        }

        .inventory-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            table-layout: fixed;
        }

        .inventory-table th,
        .inventory-table td {
            text-align: left;
            padding: 1rem 1rem;
            border-bottom: 1px solid #f5e7de;
            vertical-align: top;
            word-break: break-word;
        }

        .inventory-table th {
            color: #6b7280;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            background: #fff8f4;
        }

        .inventory-table td {
            color: #344054;
            font-size: 0.95rem;
            background: #ffffff;
        }

        .inventory-table tbody tr:last-child td { border-bottom: none; }
        .inventory-table th:nth-child(1),
        .inventory-table td:nth-child(1) { width: 28%; }
        .inventory-table th:nth-child(2),
        .inventory-table td:nth-child(2) { width: 14%; }
        .inventory-table th:nth-child(3),
        .inventory-table td:nth-child(3) { width: 18%; }
        .inventory-table th:nth-child(4),
        .inventory-table td:nth-child(4) { width: 40%; }
        .inventory-name { font-weight: 700; color: #172033; }
        .inventory-count { font-weight: 700; }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.45rem 0.7rem;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .status-badge.good { background: #eaf8ef; color: #15803d; }
        .status-badge.bad { background: #fff0eb; color: #c2410c; }

        .empty-state {
            padding: 1rem 0.25rem 0.25rem;
            color: #667085;
        }

        @media (max-width: 1180px) {
            .summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .room-layout { grid-template-columns: 1fr; }
        }

        @media (max-width: 860px) {
            .wali-page {
                margin-left: 0;
                width: 100%;
                padding: 1.2rem 1rem 2rem;
            }

            .summary-grid { grid-template-columns: 1fr; }
            .inventory-table-wrap { overflow-x: auto; }
            .inventory-table { min-width: 720px; table-layout: auto; }
        }
    </style>
</head>
<body>
    <div class="app-shell" id="appShell">
        @include('header')

        <main class="wali-page">
            <div class="page-shell">
                <section class="hero-card">
                    <div class="eyebrow">{{ $dashboard['role_name'] ?? 'Pengguna' }}</div>
                    <h1 class="hero-title">Kelas Saya</h1>
                    <p class="hero-subtitle">
                        Halaman ini menampilkan kelas yang Anda pegang sebagai wali kelas, lengkap dengan ringkasan inventaris dan pengajuan yang masuk dari kelas tersebut.
                    </p>
                </section>

                <section class="room-stack">
                    @forelse ($roomOverviews as $overview)
                        @php
                            $assignment = $overview['assignment'];
                            $inventoryRows = $overview['inventory_rows'];
                            $summary = $overview['summary'];
                            $latestRequests = $overview['latest_requests'];
                            $inboxUrl = \Illuminate\Support\Facades\Route::has('admin.requests.inbox') ? route('admin.requests.inbox') : '#';
                            $historyUrl = route('admin.requests.history');
                        @endphp
                        <article class="room-card">
                            <div class="room-shell">
                                <div class="room-header">
                                    <div>
                                        <div class="room-title">{{ $assignment->nama_ruangan }}</div>
                                        <div class="room-meta">
                                            Kode ruangan: {{ $assignment->kode_ruangan }}<br>
                                            Jenis ruangan: {{ ucfirst($assignment->jenis_ruangan) }}<br>
                                            Wali kelas: {{ $overview['wali_kelas'] }}
                                        </div>
                                    </div>
                                    <div class="room-badge">
                                        <i class="bi bi-person-badge-fill"></i>
                                        {{ str_replace('_', ' ', ucfirst($assignment->peran_ruangan)) }}
                                    </div>
                                </div>

                                <div class="summary-grid">
                                    <div class="summary-card">
                                        <div class="summary-label">Total Barang</div>
                                        <div class="summary-value">{{ number_format($summary['total_barang']) }}</div>
                                        <div class="summary-note">Inventaris tercatat untuk kelas ini</div>
                                    </div>
                                    <div class="summary-card is-accent">
                                        <div class="summary-label">Pengajuan Kelas</div>
                                        <div class="summary-value">{{ number_format($summary['menunggu_review']) }}</div>
                                        <div class="summary-note">Perlu ditinjau oleh wali kelas</div>
                                    </div>
                                    <div class="summary-card">
                                        <div class="summary-label">Total Pengajuan</div>
                                        <div class="summary-value">{{ number_format($summary['total_pengajuan']) }}</div>
                                        <div class="summary-note">Riwayat permintaan dari kelas ini</div>
                                    </div>
                                </div>

                                <div class="room-layout">
                                    <div>
                                        <div class="room-section-title">Daftar Inventaris</div>
                                        @if ($inventoryRows->isEmpty())
                                            <div class="empty-state">Belum ada data barang yang tercatat untuk kelas ini.</div>
                                        @else
                                            <div class="inventory-table-wrap">
                                                <table class="inventory-table">
                                                    <thead>
                                                        <tr>
                                                            <th>Nama Barang</th>
                                                            <th>Jumlah</th>
                                                            <th>Kondisi</th>
                                                            <th>Keterangan</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($inventoryRows as $row)
                                                            @php
                                                                $totalJumlah = (int) $row->jumlah_baik + (int) $row->jumlah_rusak;
                                                                $kondisiLabel = (int) $row->jumlah_rusak > 0 ? 'Perlu dicek' : 'Baik';
                                                                $kondisiClass = (int) $row->jumlah_rusak > 0 ? 'bad' : 'good';
                                                            @endphp
                                                            <tr>
                                                                <td><div class="inventory-name">{{ ucfirst($row->nama_barang) }}</div></td>
                                                                <td><span class="inventory-count">{{ $totalJumlah }}</span></td>
                                                                <td><span class="status-badge {{ $kondisiClass }}">{{ $kondisiLabel }}</span></td>
                                                                <td>{{ $row->keterangan ?: '-' }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    </div>

                                    <aside class="insight-stack">
                                        <div class="insight-card">
                                            <div class="room-section-title">Status Kelas</div>
                                            <div class="insight-list">
                                                <div class="insight-item">
                                                    <span class="insight-dot"></span>
                                                    <span>{{ number_format($summary['barang_baik']) }} barang dalam kondisi baik</span>
                                                </div>
                                                <div class="insight-item">
                                                    <span class="insight-dot"></span>
                                                    <span>{{ number_format($summary['barang_rusak']) }} barang perlu perhatian</span>
                                                </div>
                                                <div class="insight-item">
                                                    <span class="insight-dot"></span>
                                                    <span>{{ number_format($summary['pengajuan_aktif']) }} pengajuan masih aktif dari kelas ini</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="insight-card">
                                            <div class="room-section-title">Pengajuan Terbaru</div>
                                            @if ($latestRequests === [])
                                                <div class="empty-state">Belum ada pengajuan terbaru dari kelas ini.</div>
                                            @else
                                                <div class="latest-list">
                                                    @foreach ($latestRequests as $request)
                                                        <div class="latest-item">
                                                            <div class="latest-title">{{ $request['jenis'] }}</div>
                                                            <div class="latest-meta">{{ $request['status'] }} • {{ $request['tanggal'] }}</div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>

                                        <div class="insight-card">
                                            <div class="room-section-title">Akses Cepat</div>
                                            <div class="quick-links">
                                                <a href="{{ $inboxUrl }}" class="quick-link">
                                                    <span><i class="bi bi-inbox-fill"></i> Lihat Pengajuan Kelas</span>
                                                    <i class="bi bi-arrow-right"></i>
                                                </a>
                                                <a href="{{ $historyUrl }}" class="quick-link">
                                                    <span><i class="bi bi-clock-history"></i> Lihat Riwayat Pengajuan</span>
                                                    <i class="bi bi-arrow-right"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </aside>
                                </div>
                            </div>
                        </article>
                    @empty
                        <article class="room-card">
                            <div class="empty-state">Akun wali kelas ini belum memiliki penugasan kelas aktif.</div>
                        </article>
                    @endforelse
                </section>
            </div>
        </main>
    </div>
    @include('chatbot')
</body>
</html>
