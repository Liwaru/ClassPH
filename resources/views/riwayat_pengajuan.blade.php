<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Riwayat Pengajuan | InfraSPH</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --brand-orange: #ff5900;
            --brand-orange-dark: #e14f00;
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

        .history-page {
            margin-left: 320px;
            width: calc(100% - 320px);
            min-height: 100vh;
            padding: 2rem 1.6rem 2.5rem;
            transition: margin-left 0.28s ease, width 0.28s ease, padding 0.28s ease;
        }

        .app-shell.sidebar-collapsed .history-page {
            margin-left: 88px;
            width: calc(100% - 88px);
        }

        .page-shell {
            width: 100%;
            max-width: none;
        }

        .hero-card,
        .filter-card,
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

        .hero-title {
            font-size: clamp(1.8rem, 2.6vw, 2.4rem);
            color: var(--brand-orange);
            margin-bottom: 0.7rem;
            letter-spacing: -0.04em;
        }

        .hero-subtitle {
            color: #5b6472;
            line-height: 1.7;
            max-width: 760px;
        }

        .filter-card {
            padding: 1rem 1.05rem;
            margin-bottom: 1rem;
        }

        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .filter-chip {
            border: 1px solid #ead9d0;
            background: #ffffff;
            color: #344054;
            border-radius: 999px;
            padding: 0.75rem 1rem;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
        }

        .filter-chip.active {
            background: linear-gradient(135deg, var(--brand-orange), #ff7b2f);
            border-color: transparent;
            color: #ffffff;
        }

        .table-card {
            overflow: hidden;
        }

        .table-wrap { overflow-x: hidden; }

        .request-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            table-layout: fixed;
        }

        .request-table th,
        .request-table td {
            text-align: left;
            padding: 1rem 1rem;
            border-bottom: 1px solid #f4e6dc;
            vertical-align: top;
        }

        .request-table th {
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6b7280;
            background: #fff8f4;
        }

        .request-table td {
            color: #344054;
            background: #ffffff;
        }

        .request-table th:nth-child(1),
        .request-table td:nth-child(1) { width: 16%; }
        .request-table th:nth-child(2),
        .request-table td:nth-child(2) { width: 14%; }
        .request-table th:nth-child(3),
        .request-table td:nth-child(3) { width: 24%; }
        .request-table th:nth-child(4),
        .request-table td:nth-child(4) { width: 10%; }
        .request-table th:nth-child(5),
        .request-table td:nth-child(5) { width: 12%; }
        .request-table th:nth-child(6),
        .request-table td:nth-child(6) { width: 12%; }
        .request-table th:nth-child(7),
        .request-table td:nth-child(7) { width: 12%; }

        .request-code {
            font-size: 0.82rem;
            color: #7b8794;
            margin-top: 0.25rem;
        }

        .request-item {
            font-weight: 700;
            color: #172033;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: 0.45rem 0.8rem;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .badge.process {
            background: #fff6db;
            color: #a16207;
        }

        .badge.approved {
            background: #eaf8ef;
            color: #15803d;
        }

        .badge.rejected {
            background: #fff0eb;
            color: #c2410c;
        }

        .action-delete {
            border: 1px solid #ead9d0;
            background: #ffffff;
            color: #c2410c;
            border-radius: 14px;
            padding: 0.7rem 0.9rem;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
        }

        .empty-card {
            padding: 2rem 1.3rem;
            text-align: center;
        }

        .empty-title {
            font-size: 1.15rem;
            font-weight: 800;
            color: #172033;
            margin-bottom: 0.4rem;
        }

        .empty-copy {
            color: #667085;
            line-height: 1.7;
        }

        @media (max-width: 860px) {
            .history-page {
                margin-left: 0;
                width: 100%;
                padding: 1.2rem 1rem 2rem;
            }

            .table-wrap { overflow-x: auto; }
            .request-table { min-width: 900px; table-layout: auto; }
        }
    </style>
</head>
<body>
    <div class="app-shell" id="appShell">
        @include('header')

        <main class="history-page">
            <div class="page-shell">
                <section class="hero-card">
                    <div class="eyebrow">{{ $dashboard['role_name'] ?? 'Pengguna' }}</div>
                    <h1 class="hero-title">Riwayat Pengajuan</h1>
                    <p class="hero-subtitle">
                        Pantau semua permintaan yang pernah kamu ajukan, lihat status terbarunya, dan buka detail untuk mengetahui progres persetujuan.
                    </p>
                </section>

                @if (session('success'))
                    <section class="filter-card" style="margin-bottom: 1rem; color: #166534; background: #f3fff5; border-color: #cdeed5;">
                        {{ session('success') }}
                    </section>
                @endif

                @if (session('error'))
                    <section class="filter-card" style="margin-bottom: 1rem; color: #c2410c; background: #fff7f4; border-color: #ffd7c7;">
                        {{ session('error') }}
                    </section>
                @endif

                <section class="filter-card">
                    <div class="filter-row">
                        <button type="button" class="filter-chip active" data-filter="all">Semua ({{ $statusCounts['all'] }})</button>
                        <button type="button" class="filter-chip" data-filter="process">Diproses ({{ $statusCounts['process'] }})</button>
                        <button type="button" class="filter-chip" data-filter="approved">Disetujui ({{ $statusCounts['approved'] }})</button>
                        <button type="button" class="filter-chip" data-filter="rejected">Ditolak ({{ $statusCounts['rejected'] }})</button>
                    </div>
                </section>

                @if ($requests->isEmpty())
                    <section class="empty-card">
                        <div class="empty-title">Belum ada pengajuan</div>
                        <div class="empty-copy">Silakan ajukan permintaan terlebih dahulu melalui menu <strong>Ajukan Permintaan</strong>.</div>
                    </section>
                @else
                    <section class="table-card">
                        <div class="table-wrap">
                            <table class="request-table">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Jenis</th>
                                        <th>Barang</th>
                                        <th>Jumlah</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($requests as $request)
                                        <tr class="request-record" data-status="{{ $request['status_key'] }}">
                                            <td>
                                                <div>{{ $request['tanggal_label'] }}</div>
                                                <div class="request-code">{{ $request['kode_permintaan'] }}</div>
                                            </td>
                                            <td>{{ $request['jenis'] }}</td>
                                            <td class="request-item">{{ $request['barang_ringkas'] }}</td>
                                            <td>{{ $request['jumlah_ringkas'] }}</td>
                                            <td><span class="badge {{ $request['status_class'] }}">{{ $request['status'] }}</span></td>
                                            <td>
                                                <form method="POST" action="{{ route('requests.destroy', $request['id_permintaan']) }}" class="delete-request-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="action-delete">Hapus</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif
            </div>
        </main>
    </div>
    @include('chatbot')

    <script>
        (function () {
            const filterButtons = document.querySelectorAll('.filter-chip');
            const records = document.querySelectorAll('.request-record');
            const deleteForms = document.querySelectorAll('.delete-request-form');

            filterButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    const filter = button.getAttribute('data-filter');

                    filterButtons.forEach(function (item) {
                        item.classList.toggle('active', item === button);
                    });

                    records.forEach(function (row) {
                        const status = row.getAttribute('data-status');
                        const visible = filter === 'all' || status === filter;

                        row.style.display = visible ? '' : 'none';
                    });
                });
            });

            deleteForms.forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    const confirmed = window.confirm('Yakin ingin menghapus pengajuan ini?');

                    if (!confirmed) {
                        event.preventDefault();
                    }
                });
            });
        })();
    </script>
</body>
</html>
