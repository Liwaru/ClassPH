<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Inventaris Sekolah | InfraSPH</title>
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

        .owner-inventory-page {
            margin-left: 320px;
            min-height: 100vh;
            padding: 2rem 1.6rem 2.5rem;
            width: calc(100% - 320px);
            transition: margin-left 0.28s ease, width 0.28s ease, padding 0.28s ease;
        }

        .app-shell.sidebar-collapsed .owner-inventory-page {
            margin-left: 88px;
            width: calc(100% - 88px);
        }

        .page-shell { width: 100%; }

        .hero-card,
        .summary-card,
        .filter-card,
        .table-card,
        .empty-card {
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
            font-size: clamp(1.9rem, 2.8vw, 2.6rem);
            color: var(--brand-orange);
            margin-bottom: 0.7rem;
            letter-spacing: -0.04em;
        }

        .hero-subtitle {
            color: #5b6472;
            line-height: 1.7;
            max-width: 900px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.95rem;
            margin-bottom: 1.2rem;
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

        .filter-card {
            padding: 1rem 1.05rem;
            margin-bottom: 1.2rem;
        }

        .filter-form {
            display: grid;
            grid-template-columns: minmax(0, 1.7fr) minmax(240px, 0.9fr) auto;
            gap: 0.9rem;
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
            padding: 0.9rem 1rem;
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

        .table-card {
            overflow: hidden;
        }

        .table-wrap {
            width: 100%;
            overflow-x: hidden;
        }

        .inventory-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 0;
            table-layout: fixed;
        }

        .inventory-table th,
        .inventory-table td {
            padding: 1rem 1.05rem;
            text-align: left;
            border-bottom: 1px solid #f3e3db;
            vertical-align: top;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .inventory-table th {
            color: #6b7280;
            font-size: 0.8rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            background: #fff8f4;
        }

        .inventory-table tbody tr:last-child td {
            border-bottom: none;
        }

        .inventory-name {
            color: #172033;
            font-size: 1rem;
            font-weight: 800;
            margin-bottom: 0.2rem;
        }

        .inventory-unit {
            color: #7b8794;
            font-size: 0.85rem;
        }

        .inventory-number {
            color: #172033;
            font-weight: 800;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.45rem 0.7rem;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .status-badge.good {
            background: #eaf8ef;
            color: #15803d;
        }

        .status-badge.warning {
            background: #fff0eb;
            color: #c2410c;
        }

        .detail-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            border: 1px solid #ecd8cb;
            border-radius: 14px;
            padding: 0.72rem 0.9rem;
            background: #ffffff;
            color: #ff5900;
            font-size: 0.88rem;
            font-weight: 700;
            cursor: pointer;
        }

        .detail-btn:hover {
            background: #fff8f4;
            border-color: rgba(255, 89, 0, 0.3);
        }

        .empty-card {
            padding: 1.25rem 1.35rem;
            color: #667085;
            line-height: 1.65;
        }

        .pagination-wrap {
            margin-top: 1.25rem;
            display: flex;
            justify-content: center;
        }

        .pagination {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .pagination-info {
            color: #6b7280;
            font-size: 0.88rem;
            margin-right: 0.35rem;
        }

        .pagination-link,
        .pagination-current {
            min-width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            border: 1px solid #ecd8cb;
            background: #ffffff;
            color: #4b5563;
            font-size: 0.9rem;
            font-weight: 700;
            line-height: 1;
            text-decoration: none;
        }

        .pagination-link:hover {
            border-color: rgba(255, 89, 0, 0.32);
            color: #ff5900;
        }

        .pagination-current {
            border-color: transparent;
            background: linear-gradient(135deg, #ff5900, #ff7b2f);
            color: #ffffff;
        }

        .pagination-link.disabled {
            opacity: 0.45;
            pointer-events: none;
        }

        .inventory-modal {
            position: fixed;
            inset: 0;
            z-index: 1600;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1.2rem;
            background: rgba(17, 24, 39, 0.42);
        }

        .inventory-modal.open {
            display: flex;
        }

        .inventory-modal-dialog {
            width: min(760px, 100%);
            max-height: calc(100vh - 2.4rem);
            overflow-y: auto;
            background: #ffffff;
            border: 1px solid #f3e3db;
            border-radius: 28px;
            box-shadow: 0 28px 48px -28px rgba(31, 41, 55, 0.35);
        }

        .inventory-modal-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.25rem 1.3rem 1rem;
            border-bottom: 1px solid #f3e3db;
            background: linear-gradient(135deg, rgba(255, 89, 0, 0.08), rgba(255, 89, 0, 0.02));
        }

        .inventory-modal-title {
            color: var(--brand-orange);
            font-size: 1.45rem;
            font-weight: 800;
            margin-bottom: 0.25rem;
        }

        .inventory-modal-meta {
            color: #667085;
            font-size: 0.92rem;
            line-height: 1.65;
        }

        .inventory-modal-close {
            width: 42px;
            height: 42px;
            border: none;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.88);
            color: #e14f00;
            cursor: pointer;
            flex-shrink: 0;
        }

        .inventory-modal-body {
            padding: 1.15rem 1.3rem 1.25rem;
            display: grid;
            gap: 1rem;
        }

        .inventory-modal-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.85rem;
        }

        .inventory-modal-card {
            padding: 0.95rem 1rem;
            border-radius: 20px;
            background: #fff8f4;
            border: 1px solid #f3e3db;
        }

        .inventory-modal-label {
            color: #6b7280;
            font-size: 0.82rem;
            font-weight: 700;
            margin-bottom: 0.2rem;
        }

        .inventory-modal-value {
            color: #172033;
            font-size: 1.15rem;
            font-weight: 800;
        }

        .inventory-modal-section {
            border: 1px solid #f3e3db;
            border-radius: 22px;
            padding: 1rem 1.05rem;
            background: #fffdfa;
        }

        .distribution-list {
            display: grid;
            gap: 0.75rem;
        }

        .distribution-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.9rem 0.95rem;
            border-radius: 18px;
            background: #fff8f4;
            border: 1px solid #f5e3d8;
        }

        .distribution-room {
            color: #172033;
            font-weight: 700;
            margin-bottom: 0.2rem;
        }

        .distribution-code {
            color: #7b8794;
            font-size: 0.82rem;
        }

        .distribution-stats {
            display: flex;
            gap: 0.9rem;
            flex-wrap: wrap;
            color: #4b5563;
            font-size: 0.85rem;
        }

        .distribution-empty {
            color: #7b8794;
            font-size: 0.9rem;
            line-height: 1.55;
        }

        @media (max-width: 1320px) {
            .summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 1040px) {
            .filter-form {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 860px) {
            .owner-inventory-page {
                margin-left: 0;
                width: 100%;
                padding: 1.2rem 1rem 2rem;
            }

            .summary-grid,
            .inventory-modal-grid {
                grid-template-columns: 1fr;
            }

            .table-wrap {
                overflow-x: auto;
            }

            .inventory-table {
                min-width: 860px;
                table-layout: auto;
            }
        }
    </style>
</head>
<body>
    <div class="app-shell" id="appShell">
        @include('header')

        <main class="owner-inventory-page">
            <div class="page-shell">
            <section class="hero-card">
                <div class="eyebrow">Kepala Sekolah</div>
                <h1 class="hero-title">Inventaris Sekolah</h1>
                <p class="hero-subtitle">Lihat dan pantau seluruh data barang di sekolah dalam bentuk rekap gabungan dari semua ruangan, lengkap dengan kondisi dan distribusinya.</p>
            </section>

            <section class="summary-grid">
                <article class="summary-card">
                    <div class="summary-label">Total Jenis Barang</div>
                    <div class="summary-value">{{ number_format($summary['total_jenis_barang']) }}</div>
                    <div class="summary-note">Jenis barang aktif yang tercatat di sekolah</div>
                </article>
                <article class="summary-card is-accent">
                    <div class="summary-label">Total Barang</div>
                    <div class="summary-value">{{ number_format($summary['total_barang']) }}</div>
                    <div class="summary-note">Jumlah barang dari seluruh ruangan</div>
                </article>
                <article class="summary-card">
                    <div class="summary-label">Barang Baik</div>
                    <div class="summary-value">{{ number_format($summary['barang_baik']) }}</div>
                    <div class="summary-note">Barang dalam kondisi baik dan siap digunakan</div>
                </article>
                <article class="summary-card">
                    <div class="summary-label">Perlu Perhatian</div>
                    <div class="summary-value">{{ number_format($summary['perlu_perhatian']) }}</div>
                    <div class="summary-note">Barang yang perlu dicek atau ditindaklanjuti</div>
                </article>
            </section>

            <section class="filter-card">
                <form method="GET" class="filter-form">
                    <div class="filter-field">
                        <label for="inventory-search">Cari Barang</label>
                        <input id="inventory-search" type="text" name="q" value="{{ $filters['q'] }}" placeholder="Cari nama barang...">
                    </div>
                    <div class="filter-field">
                        <label for="inventory-status">Filter Kondisi</label>
                        <select id="inventory-status" name="status">
                            <option value="semua" @selected($filters['status'] === 'semua')>Semua</option>
                            <option value="baik" @selected($filters['status'] === 'baik')>Baik</option>
                            <option value="perlu_perhatian" @selected($filters['status'] === 'perlu_perhatian')>Perlu Perhatian</option>
                        </select>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="filter-btn">Terapkan</button>
                        <a href="{{ route('owner.inventories') }}" class="filter-link">Reset</a>
                    </div>
                </form>
            </section>

            @if ($inventoryRows->isEmpty())
                <section class="empty-card">Belum ada data inventaris yang sesuai dengan filter saat ini.</section>
            @else
                <section class="table-card">
                    <div class="table-wrap">
                        <table class="inventory-table">
                            <thead>
                                <tr>
                                    <th style="width: 28%;">Nama Barang</th>
                                    <th style="width: 12%;">Total</th>
                                    <th style="width: 12%;">Baik</th>
                                    <th style="width: 12%;">Rusak</th>
                                    <th style="width: 12%;">Satuan</th>
                                    <th style="width: 12%;">Status</th>
                                    <th style="width: 12%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($inventoryRows as $row)
                                    <tr>
                                        <td>
                                            <div class="inventory-name">{{ $row['nama_barang'] }}</div>
                                            <div class="inventory-unit">Rekap inventaris seluruh sekolah</div>
                                        </td>
                                        <td><span class="inventory-number">{{ number_format($row['total_barang']) }}</span></td>
                                        <td><span class="inventory-number">{{ number_format($row['total_baik']) }}</span></td>
                                        <td><span class="inventory-number">{{ number_format($row['total_rusak']) }}</span></td>
                                        <td>{{ $row['satuan'] }}</td>
                                        <td><span class="status-badge {{ $row['status_class'] }}">{{ $row['status_label'] }}</span></td>
                                        <td>
                                            <button
                                                type="button"
                                                class="detail-btn js-inventory-detail"
                                                data-inventory='@json($row)'
                                            >
                                                Detail
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

                @if ($inventories->hasPages())
                    <div class="pagination-wrap">
                        <nav class="pagination" aria-label="Pagination inventaris sekolah">
                            <span class="pagination-info">
                                Showing {{ $inventories->firstItem() }} to {{ $inventories->lastItem() }} of {{ $inventories->total() }} results
                            </span>

                            @if ($inventories->onFirstPage())
                                <span class="pagination-link disabled"><i class="bi bi-chevron-left"></i></span>
                            @else
                                <a class="pagination-link" href="{{ $inventories->previousPageUrl() }}" aria-label="Halaman sebelumnya">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            @endif

                            @foreach ($inventories->getUrlRange(1, $inventories->lastPage()) as $page => $url)
                                @if ($page === $inventories->currentPage())
                                    <span class="pagination-current">{{ $page }}</span>
                                @else
                                    <a class="pagination-link" href="{{ $url }}">{{ $page }}</a>
                                @endif
                            @endforeach

                            @if ($inventories->hasMorePages())
                                <a class="pagination-link" href="{{ $inventories->nextPageUrl() }}" aria-label="Halaman berikutnya">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            @else
                                <span class="pagination-link disabled"><i class="bi bi-chevron-right"></i></span>
                            @endif
                        </nav>
                    </div>
                @endif
            @endif
            </div>
        </main>

        <div class="inventory-modal" id="inventory-modal" aria-hidden="true">
            <div class="inventory-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="inventory-modal-title">
                <div class="inventory-modal-header">
                    <div>
                        <div class="inventory-modal-title" id="inventory-modal-title">Detail Inventaris</div>
                        <div class="inventory-modal-meta" id="inventory-modal-meta">Distribusi inventaris seluruh sekolah</div>
                    </div>
                    <button type="button" class="inventory-modal-close" id="inventory-modal-close" aria-label="Tutup detail inventaris">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="inventory-modal-body">
                    <div class="inventory-modal-grid">
                        <div class="inventory-modal-card">
                            <div class="inventory-modal-label">Total</div>
                            <div class="inventory-modal-value" id="inventory-modal-total">0</div>
                        </div>
                        <div class="inventory-modal-card">
                            <div class="inventory-modal-label">Baik</div>
                            <div class="inventory-modal-value" id="inventory-modal-good">0</div>
                        </div>
                        <div class="inventory-modal-card">
                            <div class="inventory-modal-label">Rusak</div>
                            <div class="inventory-modal-value" id="inventory-modal-bad">0</div>
                        </div>
                        <div class="inventory-modal-card">
                            <div class="inventory-modal-label">Satuan</div>
                            <div class="inventory-modal-value" id="inventory-modal-unit">-</div>
                        </div>
                    </div>

                    <section class="inventory-modal-section">
                        <div class="inventory-modal-label" style="margin-bottom: 0.7rem;">Distribusi Per Ruangan</div>
                        <div class="distribution-list" id="inventory-modal-distribution"></div>
                    </section>
                </div>
            </div>
        </div>

        @include('chatbot')
    </div>

    <script>
        (() => {
            const modal = document.getElementById('inventory-modal');
            const closeBtn = document.getElementById('inventory-modal-close');
            const titleEl = document.getElementById('inventory-modal-title');
            const metaEl = document.getElementById('inventory-modal-meta');
            const totalEl = document.getElementById('inventory-modal-total');
            const goodEl = document.getElementById('inventory-modal-good');
            const badEl = document.getElementById('inventory-modal-bad');
            const unitEl = document.getElementById('inventory-modal-unit');
            const distributionEl = document.getElementById('inventory-modal-distribution');

            const openModal = (inventory) => {
                titleEl.textContent = inventory.nama_barang;
                metaEl.textContent = `Rekap distribusi ${inventory.nama_barang.toLowerCase()} di seluruh sekolah`;
                totalEl.textContent = inventory.total_barang;
                goodEl.textContent = inventory.total_baik;
                badEl.textContent = inventory.total_rusak;
                unitEl.textContent = inventory.satuan || '-';

                distributionEl.innerHTML = '';

                if (!Array.isArray(inventory.distribution) || inventory.distribution.length === 0) {
                    distributionEl.innerHTML = '<div class="distribution-empty">Belum ada distribusi ruangan yang tercatat untuk barang ini.</div>';
                } else {
                    inventory.distribution.forEach((item) => {
                        const row = document.createElement('div');
                        row.className = 'distribution-item';
                        row.innerHTML = `
                            <div>
                                <div class="distribution-room">${item.ruangan}</div>
                                <div class="distribution-code">${item.kode}</div>
                            </div>
                            <div class="distribution-stats">
                                <span>Total: ${item.total}</span>
                                <span>Baik: ${item.baik}</span>
                                <span>Rusak: ${item.rusak}</span>
                            </div>
                        `;
                        distributionEl.appendChild(row);
                    });
                }

                modal.classList.add('open');
                modal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            };

            const closeModal = () => {
                modal.classList.remove('open');
                modal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            };

            document.querySelectorAll('.js-inventory-detail').forEach((button) => {
                button.addEventListener('click', () => {
                    const raw = button.getAttribute('data-inventory');

                    if (!raw) {
                        return;
                    }

                    try {
                        openModal(JSON.parse(raw));
                    } catch (error) {
                        console.error('Gagal membaca detail inventaris.', error);
                    }
                });
            });

            closeBtn?.addEventListener('click', closeModal);
            modal?.addEventListener('click', (event) => {
                if (event.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && modal?.classList.contains('open')) {
                    closeModal();
                }
            });
        })();
    </script>
</body>
</html>
