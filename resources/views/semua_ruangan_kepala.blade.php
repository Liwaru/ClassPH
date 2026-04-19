<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Semua Ruangan | InfraSPH</title>
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

        .owner-rooms-page {
            margin-left: 320px;
            min-height: 100vh;
            padding: 2rem 1.6rem 2.5rem;
            width: calc(100% - 320px);
            transition: margin-left 0.28s ease, width 0.28s ease, padding 0.28s ease;
        }

        .app-shell.sidebar-collapsed .owner-rooms-page {
            margin-left: 88px;
            width: calc(100% - 88px);
        }

        .page-shell { width: 100%; max-width: none; }

        .hero-card,
        .summary-card,
        .filter-card,
        .room-card,
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
            grid-template-columns: repeat(5, minmax(0, 1fr));
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
            grid-template-columns: minmax(0, 1.8fr) minmax(220px, 0.9fr) auto;
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

        .room-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }

        .room-card {
            padding: 1.1rem 1.1rem 1rem;
            border-radius: 24px;
            display: grid;
            gap: 1rem;
        }

        .room-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.9rem;
        }

        .room-name {
            color: #172033;
            font-size: 1.12rem;
            font-weight: 800;
            margin-bottom: 0.25rem;
        }

        .room-code,
        .room-type {
            color: #6b7280;
            font-size: 0.9rem;
            line-height: 1.55;
        }

        .room-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.55rem 0.8rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .room-badge.normal {
            background: #eaf8ef;
            color: #15803d;
        }

        .room-badge.active {
            background: #fff3eb;
            color: #e14f00;
        }

        .room-badge.warning {
            background: #fff0eb;
            color: #c2410c;
        }

        .room-metrics {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.8rem;
        }

        .metric-box {
            padding: 0.9rem 0.95rem;
            border-radius: 18px;
            background: #fff8f4;
            border: 1px solid #f5e3d8;
        }

        .metric-label {
            color: #6b7280;
            font-size: 0.8rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .metric-value {
            color: #172033;
            font-size: 1.3rem;
            font-weight: 800;
        }

        .metric-note {
            color: #7b8794;
            font-size: 0.82rem;
            margin-top: 0.25rem;
            line-height: 1.45;
        }

        .detail-section-title {
            color: #172033;
            font-size: 0.92rem;
            font-weight: 800;
            margin-bottom: 0.45rem;
        }

        .detail-list {
            display: grid;
            gap: 0.55rem;
        }

        .detail-item {
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
            color: #4b5563;
            line-height: 1.55;
            font-size: 0.9rem;
        }

        .detail-dot {
            width: 9px;
            height: 9px;
            border-radius: 999px;
            background: #ff7b2f;
            flex-shrink: 0;
            margin-top: 0.45rem;
        }

        .detail-empty {
            color: #7b8794;
            font-size: 0.9rem;
            line-height: 1.55;
        }

        .room-link-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: 1px solid #ecd8cb;
            background: #ffffff;
            color: #ff5900;
            border-radius: 16px;
            padding: 0.85rem 0.95rem;
            font-size: 0.9rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .room-link-btn:hover {
            border-color: rgba(255, 89, 0, 0.28);
            background: #fff8f4;
        }

        .room-modal {
            position: fixed;
            inset: 0;
            z-index: 1600;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1.2rem;
            background: rgba(17, 24, 39, 0.42);
        }

        .room-modal.open {
            display: flex;
        }

        .room-modal-dialog {
            width: min(760px, 100%);
            max-height: calc(100vh - 2.4rem);
            overflow-y: auto;
            background: #ffffff;
            border: 1px solid #f3e3db;
            border-radius: 28px;
            box-shadow: 0 28px 48px -28px rgba(31, 41, 55, 0.35);
        }

        .room-modal-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.25rem 1.3rem 1rem;
            border-bottom: 1px solid #f3e3db;
            background: linear-gradient(135deg, rgba(255, 89, 0, 0.08), rgba(255, 89, 0, 0.02));
        }

        .room-modal-title {
            color: var(--brand-orange);
            font-size: 1.45rem;
            font-weight: 800;
            margin-bottom: 0.25rem;
        }

        .room-modal-meta {
            color: #667085;
            font-size: 0.92rem;
            line-height: 1.65;
        }

        .room-modal-close {
            width: 42px;
            height: 42px;
            border: none;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.88);
            color: #e14f00;
            cursor: pointer;
            flex-shrink: 0;
        }

        .room-modal-body {
            padding: 1.15rem 1.3rem 1.25rem;
            display: grid;
            gap: 1rem;
        }

        .room-modal-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.85rem;
        }

        .room-modal-card {
            padding: 0.95rem 1rem;
            border-radius: 20px;
            background: #fff8f4;
            border: 1px solid #f3e3db;
        }

        .room-modal-label {
            color: #6b7280;
            font-size: 0.82rem;
            font-weight: 700;
            margin-bottom: 0.2rem;
        }

        .room-modal-value {
            color: #172033;
            font-size: 1.15rem;
            font-weight: 800;
        }

        .room-modal-section {
            border: 1px solid #f3e3db;
            border-radius: 22px;
            padding: 1rem 1.05rem;
            background: #fffdfa;
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

        @media (max-width: 1320px) {
            .summary-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .room-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 1040px) {
            .filter-form {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 860px) {
            .owner-rooms-page {
                margin-left: 0;
                width: 100%;
                padding: 1.2rem 1rem 2rem;
            }

            .summary-grid,
            .room-grid,
            .room-metrics,
            .room-modal-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="app-shell" id="appShell">
        @include('header')

        <main class="owner-rooms-page">
            <div class="page-shell">
                <section class="hero-card">
                    <div class="eyebrow">{{ $dashboard['role_name'] ?? 'Pengguna' }}</div>
                    <h1 class="hero-title">Semua Ruangan</h1>
                    <p class="hero-subtitle">
                        Pantau data ruangan dan inventaris di seluruh sekolah. Halaman ini membantu kepala sekolah melihat kondisi umum setiap ruangan tanpa melakukan perubahan data.
                    </p>
                </section>

                <section class="summary-grid">
                    <article class="summary-card">
                        <div class="summary-label">Total Ruangan</div>
                        <div class="summary-value">{{ number_format($summary['total_ruangan']) }}</div>
                        <div class="summary-note">Semua ruangan yang tercatat di sistem sekolah.</div>
                    </article>
                    <article class="summary-card is-accent">
                        <div class="summary-label">Total Barang</div>
                        <div class="summary-value">{{ number_format($summary['total_barang']) }}</div>
                        <div class="summary-note">Akumulasi inventaris seluruh ruangan sekolah.</div>
                    </article>
                    <article class="summary-card">
                        <div class="summary-label">Ruangan Aktif</div>
                        <div class="summary-value">{{ number_format($summary['ruangan_aktif']) }}</div>
                        <div class="summary-note">Ruangan yang sudah memiliki inventaris tercatat.</div>
                    </article>
                    <article class="summary-card">
                        <div class="summary-label">Ruangan Dengan Pengajuan Aktif</div>
                        <div class="summary-value">{{ number_format($summary['ruangan_dengan_pengajuan_aktif']) }}</div>
                        <div class="summary-note">Masih ada pengajuan yang berjalan di ruangan ini.</div>
                    </article>
                    <article class="summary-card">
                        <div class="summary-label">Barang Bermasalah</div>
                        <div class="summary-value">{{ number_format($summary['ruangan_dengan_barang_bermasalah']) }}</div>
                        <div class="summary-note">Ruangan yang memiliki inventaris perlu perhatian.</div>
                    </article>
                </section>

                <section class="filter-card">
                    <form method="GET" action="{{ route('owner.rooms') }}" class="filter-form">
                        <div class="filter-field">
                            <label for="roomSearch">Cari Ruangan</label>
                            <input
                                type="text"
                                id="roomSearch"
                                name="q"
                                value="{{ $filters['q'] }}"
                                placeholder="Cari nama ruangan atau kode..."
                            >
                        </div>

                        <div class="filter-field">
                            <label for="roomType">Jenis Ruangan</label>
                            <select id="roomType" name="type">
                                <option value="semua" @selected($filters['type'] === 'semua')>Semua jenis ruangan</option>
                                <option value="kelas" @selected($filters['type'] === 'kelas')>Kelas</option>
                                <option value="laboratorium" @selected($filters['type'] === 'laboratorium')>Lab</option>
                                <option value="kantor" @selected($filters['type'] === 'kantor')>Kantor</option>
                            </select>
                        </div>

                        <div class="filter-actions">
                            <button type="submit" class="filter-btn">
                                <i class="bi bi-search"></i>
                                <span>Terapkan</span>
                            </button>
                            <a href="{{ route('owner.rooms') }}" class="filter-link">
                                <i class="bi bi-arrow-counterclockwise"></i>
                                <span>Reset</span>
                            </a>
                        </div>
                    </form>
                </section>

                @if ($roomCards->isEmpty())
                    <section class="empty-card">
                        Belum ada data ruangan yang sesuai dengan pencarian atau filter yang dipilih.
                    </section>
                @else
                    <section class="room-grid">
                        @foreach ($roomCards as $room)
                            <article class="room-card">
                                <div class="room-top">
                                    <div>
                                        <div class="room-name">{{ $room['nama_ruangan'] }}</div>
                                        <div class="room-code">Kode: {{ $room['kode_ruangan'] }}</div>
                                        <div class="room-type">Jenis: {{ $room['jenis_ruangan'] }}</div>
                                    </div>
                                    <span class="room-badge {{ $room['status_class'] }}">
                                        {{ $room['status_label'] }}
                                    </span>
                                </div>

                                <div class="room-metrics">
                                    <div class="metric-box">
                                        <div class="metric-label">Total Barang</div>
                                        <div class="metric-value">{{ number_format($room['total_barang']) }}</div>
                                        <div class="metric-note">{{ number_format($room['barang_baik']) }} baik, {{ number_format($room['barang_rusak']) }} perlu perhatian</div>
                                    </div>
                                    <div class="metric-box">
                                        <div class="metric-label">Pengajuan Aktif</div>
                                        <div class="metric-value">{{ number_format($room['pengajuan_aktif']) }}</div>
                                        <div class="metric-note">Permintaan yang masih berjalan di ruangan ini</div>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    class="room-link-btn room-detail-trigger"
                                    data-room='@json($room)'
                                >
                                    <span>Lihat Detail</span>
                                    <i class="bi bi-arrow-right"></i>
                                </button>
                            </article>
                        @endforeach
                    </section>

                    @if ($rooms->hasPages())
                        <div class="pagination-wrap">
                            <div class="pagination">
                                <span class="pagination-info">Halaman {{ $rooms->currentPage() }} dari {{ $rooms->lastPage() }}</span>

                                @if ($rooms->onFirstPage())
                                    <span class="pagination-link disabled">
                                        <i class="bi bi-chevron-left"></i>
                                    </span>
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
                                    <span class="pagination-link disabled">
                                        <i class="bi bi-chevron-right"></i>
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </main>
    </div>

    <div class="room-modal" id="roomDetailModal" aria-hidden="true">
        <div class="room-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="roomModalTitle">
            <div class="room-modal-header">
                <div>
                    <div class="room-modal-title" id="roomModalTitle">Detail Ruangan</div>
                    <div class="room-modal-meta" id="roomModalMeta"></div>
                </div>
                <button type="button" class="room-modal-close" id="roomModalClose" aria-label="Tutup detail ruangan">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="room-modal-body">
                <div class="room-modal-grid">
                    <div class="room-modal-card">
                        <div class="room-modal-label">Total Barang</div>
                        <div class="room-modal-value" id="roomModalTotalBarang">0</div>
                    </div>
                    <div class="room-modal-card">
                        <div class="room-modal-label">Pengajuan Aktif</div>
                        <div class="room-modal-value" id="roomModalPengajuan">0</div>
                    </div>
                    <div class="room-modal-card">
                        <div class="room-modal-label">Status</div>
                        <div class="room-modal-value" id="roomModalStatus">Normal</div>
                    </div>
                </div>

                <div class="room-modal-section">
                    <div class="detail-section-title">Kondisi Umum</div>
                    <div class="detail-list" id="roomModalConditionList"></div>
                </div>

                <div class="room-modal-section">
                    <div class="detail-section-title">Inventaris Singkat</div>
                    <div id="roomModalInventory"></div>
                </div>

                <div class="room-modal-section">
                    <div class="detail-section-title">Pengajuan Terkini</div>
                    <div id="roomModalLatestRequest"></div>
                </div>
            </div>
        </div>
    </div>

    @include('chatbot')
    <script>
        (function () {
            const modal = document.getElementById('roomDetailModal');
            const closeButton = document.getElementById('roomModalClose');
            const title = document.getElementById('roomModalTitle');
            const meta = document.getElementById('roomModalMeta');
            const totalBarang = document.getElementById('roomModalTotalBarang');
            const pengajuan = document.getElementById('roomModalPengajuan');
            const status = document.getElementById('roomModalStatus');
            const conditionList = document.getElementById('roomModalConditionList');
            const inventory = document.getElementById('roomModalInventory');
            const latestRequest = document.getElementById('roomModalLatestRequest');

            if (!modal) {
                return;
            }

            function renderList(target, items, emptyText) {
                target.innerHTML = '';

                if (!items.length) {
                    const empty = document.createElement('div');
                    empty.className = 'detail-empty';
                    empty.textContent = emptyText;
                    target.appendChild(empty);
                    return;
                }

                const list = document.createElement('div');
                list.className = 'detail-list';

                items.forEach(function (text) {
                    const item = document.createElement('div');
                    item.className = 'detail-item';
                    item.innerHTML = '<span class="detail-dot"></span><span>' + text + '</span>';
                    list.appendChild(item);
                });

                target.appendChild(list);
            }

            function openModal(room) {
                title.textContent = room.nama_ruangan || 'Detail Ruangan';
                meta.innerHTML = 'Kode: ' + (room.kode_ruangan || '-') + '<br>Jenis: ' + (room.jenis_ruangan || '-') ;
                totalBarang.textContent = String(room.total_barang ?? 0);
                pengajuan.textContent = String(room.pengajuan_aktif ?? 0);
                status.textContent = room.status_label || 'Normal';

                renderList(conditionList, [
                    (room.barang_baik ?? 0) + ' barang dalam kondisi baik',
                    (room.barang_rusak ?? 0) + ' barang perlu perhatian',
                    (room.pengajuan_aktif ?? 0) + ' pengajuan aktif pada ruangan ini'
                ], 'Belum ada ringkasan kondisi.');

                const inventoryItems = Array.isArray(room.detail_items)
                    ? room.detail_items.map(function (item) {
                        return item.nama_barang + ' | ' + item.jumlah + ' | ' + item.kondisi;
                    })
                    : [];

                renderList(inventory, inventoryItems, 'Belum ada inventaris yang tercatat untuk ruangan ini.');

                const latestItems = room.latest_request
                    ? [room.latest_request.barang + ' | ' + room.latest_request.status + ' | ' + room.latest_request.tanggal]
                    : [];

                renderList(latestRequest, latestItems, 'Belum ada pengajuan terbaru untuk ruangan ini.');

                modal.classList.add('open');
                modal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }

            function closeModal() {
                modal.classList.remove('open');
                modal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            }

            document.querySelectorAll('.room-detail-trigger').forEach(function (button) {
                button.addEventListener('click', function () {
                    const room = JSON.parse(button.dataset.room || '{}');
                    openModal(room);
                });
            });

            closeButton?.addEventListener('click', closeModal);

            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && modal.classList.contains('open')) {
                    closeModal();
                }
            });
        })();
    </script>
</body>
</html>
