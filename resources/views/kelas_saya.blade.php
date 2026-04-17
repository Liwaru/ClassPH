<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelas Saya | InfraSPH</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --brand-orange: #ff5900;
            --brand-orange-dark: #e14f00;
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

        .content-area {
            margin-left: 320px;
            min-height: 100vh;
            padding: 2rem 1.6rem 2.5rem;
        }

        .page-shell { max-width: 1280px; }
        .hero-card, .room-card, .summary-card, .action-card {
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
            display:inline-flex;
            align-items:center;
            gap:0.45rem;
            padding:0.45rem 0.8rem;
            border-radius:999px;
            background: rgba(255, 89, 0, 0.12);
            color: var(--brand-orange);
            font-size:0.82rem;
            font-weight:700;
            margin-bottom:0.95rem;
        }

        .hero-title { font-size: clamp(1.8rem, 2.6vw, 2.4rem); color: var(--brand-orange); margin-bottom: 0.7rem; letter-spacing: -0.04em; }
        .hero-subtitle { color:#5b6472; line-height:1.7; max-width:760px; }
        .room-stack { display:grid; gap:1.2rem; }
        .room-card { padding: 1.35rem 1.3rem; }
        .room-shell { display:grid; gap:1rem; }
        .room-header { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; margin-bottom:1rem; }
        .room-title { font-size:1.15rem; font-weight:800; color: var(--brand-orange); }
        .room-meta { color:#667085; font-size:0.92rem; line-height:1.6; }
        .room-badge { display:inline-flex; align-items:center; gap:0.45rem; padding:0.55rem 0.8rem; border-radius:999px; background:#fff3eb; color:var(--brand-orange-dark); font-size:0.82rem; font-weight:700; }
        .summary-grid { display:grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap:0.95rem; }
        .summary-card { padding:1.05rem 1.1rem; border-radius:22px; }
        .summary-label { color:#6b7280; font-size:0.84rem; font-weight:700; margin-bottom:0.35rem; }
        .summary-value { color:#172033; font-size:1.75rem; font-weight:800; letter-spacing:-0.04em; }
        .summary-note { color:#7b8794; font-size:0.84rem; margin-top:0.3rem; }
        .summary-card.is-accent { background:linear-gradient(135deg, #ff6a17, #ff5900); border-color:transparent; }
        .summary-card.is-accent .summary-label,
        .summary-card.is-accent .summary-value,
        .summary-card.is-accent .summary-note { color:#fffaf6; }
        .room-layout { display:grid; grid-template-columns: 1.8fr 1fr; gap:1rem; }
        .room-section-title { font-size:1.02rem; font-weight:800; color:#172033; margin-bottom:0.85rem; }
        .action-grid { display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap:0.85rem; }
        .action-card { display:flex; align-items:center; gap:0.8rem; padding:1rem 1.05rem; border-radius:22px; text-decoration:none; color:#172033; }
        .action-icon { width:44px; height:44px; border-radius:16px; display:inline-flex; align-items:center; justify-content:center; background:#fff3eb; color:var(--brand-orange); font-size:1.1rem; flex-shrink:0; }
        .action-label { font-weight:700; }
        .action-copy { color:#7b8794; font-size:0.85rem; line-height:1.45; margin-top:0.15rem; }
        .latest-list { display:grid; gap:0.75rem; }
        .latest-item { padding:0.9rem 0.95rem; border-radius:20px; background:#fff8f4; border:1px solid #f6e4da; }
        .latest-title { font-weight:700; color:#172033; margin-bottom:0.2rem; }
        .latest-meta { color:#7b8794; font-size:0.84rem; }
        .inventory-table-wrap { overflow-x:auto; }
        .inventory-table { width:100%; border-collapse:collapse; min-width:720px; }
        .inventory-table th, .inventory-table td { text-align:left; padding:0.9rem 0.85rem; border-bottom:1px solid #f5e7de; vertical-align:top; }
        .inventory-table th { color:#6b7280; font-size:0.82rem; text-transform:uppercase; letter-spacing:0.06em; }
        .inventory-table td { color:#344054; font-size:0.95rem; }
        .inventory-table tbody tr:last-child td { border-bottom:none; }
        .inventory-name { font-weight:700; color:#172033; }
        .inventory-count { font-weight:700; }
        .status-badge { display:inline-flex; align-items:center; gap:0.35rem; padding:0.45rem 0.7rem; border-radius:999px; font-size:0.78rem; font-weight:700; }
        .status-badge.good { background:#eaf8ef; color:#15803d; }
        .status-badge.bad { background:#fff0eb; color:#c2410c; }
        .empty-state { padding: 1rem 0.25rem 0.25rem; color:#667085; }

        @media (max-width: 1120px) {
            .summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .room-layout { grid-template-columns: 1fr; }
        }

        @media (max-width: 860px) {
            .content-area { margin-left: 0; padding:1.2rem 1rem 2rem; }
            .summary-grid,
            .action-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    @include('header')

    <main class="content-area">
        <div class="page-shell">
            <section class="hero-card">
                <div class="eyebrow">{{ $dashboard['role_name'] ?? 'Pengguna' }}</div>
                <h1 class="hero-title">Kelas Saya</h1>
                <p class="hero-subtitle">
                    Halaman ini menampilkan gambaran lengkap kelas atau ruangan yang terhubung ke akunmu, mulai dari info ruangan, ringkasan inventaris, aksi cepat, hingga daftar barang yang tercatat.
                </p>
            </section>

            <section class="room-stack">
                @forelse ($roomOverviews as $overview)
                    @php
                        $assignment = $overview['assignment'];
                        $inventoryRows = $overview['inventory_rows'];
                        $summary = $overview['summary'];
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
                                    <i class="bi bi-door-open-fill"></i>
                                    {{ str_replace('_', ' ', ucfirst($assignment->peran_ruangan)) }}
                                </div>
                            </div>

                            <div class="summary-grid">
                                <div class="summary-card">
                                    <div class="summary-label">Total Barang</div>
                                    <div class="summary-value">{{ number_format($summary['total_barang']) }}</div>
                                    <div class="summary-note">{{ number_format($summary['barang_baik']) }} baik dan {{ number_format($summary['barang_rusak']) }} perlu perhatian</div>
                                </div>
                                <div class="summary-card is-accent">
                                    <div class="summary-label">Pengajuan</div>
                                    <div class="summary-value">{{ number_format($summary['pengajuan_aktif']) }}</div>
                                    <div class="summary-note">Pengajuan aktif dari ruangan ini</div>
                                </div>
                                <div class="summary-card">
                                    <div class="summary-label">Penanggung Jawab</div>
                                    <div class="summary-value" style="font-size:1.25rem;">{{ $overview['wali_kelas'] }}</div>
                                    <div class="summary-note">Kontak utama untuk tindak lanjut kelas</div>
                                </div>
                            </div>

                            <div class="room-layout">
                                <div class="room-main">
                                    <div class="room-section-title">Daftar Inventaris</div>
                                    @if ($inventoryRows->isEmpty())
                                        <div class="empty-state">Belum ada data barang yang tercatat untuk ruangan ini.</div>
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
                                                            <td>
                                                                <div class="inventory-name">{{ ucfirst($row->nama_barang) }}</div>
                                                                <div class="latest-meta">{{ $row->satuan }}</div>
                                                            </td>
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

                                <aside class="room-side">
                                    <div class="room-section-title">Aksi Cepat</div>
                                    <div class="action-grid">
                                        <a href="#" class="action-card">
                                            <span class="action-icon"><i class="bi bi-send-plus-fill"></i></span>
                                            <span>
                                                <div class="action-label">Ajukan Barang</div>
                                                <div class="action-copy">Buat permintaan barang baru untuk kebutuhan kelas.</div>
                                            </span>
                                        </a>
                                        <a href="#" class="action-card">
                                            <span class="action-icon"><i class="bi bi-tools"></i></span>
                                            <span>
                                                <div class="action-label">Ajukan Perbaikan</div>
                                                <div class="action-copy">Laporkan barang rusak agar bisa ditindaklanjuti.</div>
                                            </span>
                                        </a>
                                    </div>

                                    <div class="room-section-title" style="margin-top:1.1rem;">Pengajuan Terbaru</div>
                                    @if ($overview['latest_requests'] === [])
                                        <div class="empty-state">Belum ada pengajuan terbaru untuk ruangan ini.</div>
                                    @else
                                        <div class="latest-list">
                                            @foreach ($overview['latest_requests'] as $request)
                                                <div class="latest-item">
                                                    <div class="latest-title">{{ $request['jenis'] }}</div>
                                                    <div class="latest-meta">{{ $request['status'] }} • {{ $request['tanggal'] }}</div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </aside>
                            </div>
                        </div>
                    </article>
                @empty
                    <article class="room-card">
                        <div class="empty-state">Akunmu belum memiliki penugasan kelas atau ruangan aktif. Hubungi wali kelas atau pengelola sistem untuk menambahkan akses.</div>
                    </article>
                @endforelse
            </section>
        </div>
    </main>
</body>
</html>
