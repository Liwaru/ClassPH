<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Catatan Aktivitas | InfraSPH</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --brand-orange: #ff5900;
            --brand-orange-dark: #e14f00;
            --page-bg: #fff8f4;
            --text-dark: #1f2937;
            --muted: #64748b;
            --border: #f1ddd1;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { min-height: 100vh; font-family: 'Inter', sans-serif; background: var(--page-bg); color: var(--text-dark); }
        .activity-page { margin-left: 320px; min-height: 100vh; padding: 2rem 1.6rem 2.8rem; transition: margin-left 0.28s ease; }
        .app-shell.sidebar-collapsed .activity-page { margin-left: 88px; }
        .page-shell { display: grid; gap: 1.15rem; }
        .hero-card { background: linear-gradient(135deg, rgba(255, 89, 0, 0.1), rgba(255, 89, 0, 0.03)); border: 1px solid rgba(255, 89, 0, 0.14); border-radius: 28px; padding: 1.55rem; }
        .eyebrow { display: inline-flex; gap: 0.45rem; align-items: center; padding: 0.42rem 0.75rem; border-radius: 999px; background: rgba(255, 89, 0, 0.12); color: var(--brand-orange); font-size: 0.8rem; font-weight: 800; margin-bottom: 0.9rem; }
        .hero-title { color: var(--brand-orange); font-size: clamp(1.7rem, 2.5vw, 2.35rem); line-height: 1.1; margin-bottom: 0.55rem; }
        .hero-subtitle { color: #566477; line-height: 1.65; max-width: 780px; }
        .summary-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 0.85rem; }
        .summary-card, .filter-card, .table-card { background: #fff; border: 1px solid var(--border); border-radius: 22px; box-shadow: 0 18px 38px -28px rgba(31, 41, 55, 0.24); }
        .summary-card { padding: 1rem; }
        .summary-label { color: var(--muted); font-size: 0.8rem; font-weight: 800; margin-bottom: 0.35rem; }
        .summary-value { color: #111827; font-size: 1.45rem; font-weight: 800; }
        .filter-card { padding: 1rem; }
        .filter-form { display: grid; grid-template-columns: minmax(170px, 1.2fr) minmax(150px, 0.9fr) repeat(2, minmax(145px, 0.85fr)) auto auto; gap: 0.8rem; align-items: end; }
        label { display: block; color: #334155; font-size: 0.78rem; font-weight: 800; margin-bottom: 0.45rem; }
        input, select { width: 100%; border: 1px solid #ead2c4; border-radius: 15px; padding: 0.78rem 0.85rem; background: #fff; color: var(--text-dark); font: inherit; outline: none; }
        input:focus, select:focus { border-color: rgba(255, 89, 0, 0.72); box-shadow: 0 0 0 3px rgba(255, 89, 0, 0.13); }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem; border: 0; border-radius: 15px; background: linear-gradient(100deg, #f97316, #fd7010); color: #fff; padding: 0.82rem 1rem; font: inherit; font-weight: 800; cursor: pointer; text-decoration: none; white-space: nowrap; }
        .btn.secondary { background: #fff7ed; color: #9a3412; border: 1px solid #fed7aa; }
        .table-card { overflow: hidden; }
        .table-head { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 1rem 1.1rem; border-bottom: 1px solid var(--border); }
        .table-title { color: var(--brand-orange); font-size: 1.05rem; font-weight: 800; }
        .table-subtitle { color: var(--muted); font-size: 0.86rem; margin-top: 0.2rem; }
        .table-scroll { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 1080px; }
        th, td { padding: 0.9rem 0.85rem; text-align: left; border-bottom: 1px solid #f2e7df; vertical-align: top; font-size: 0.86rem; }
        th { color: #9a3412; background: #fff7ed; font-size: 0.76rem; text-transform: uppercase; letter-spacing: 0.02em; }
        td { color: #263241; }
        .cell-strong { font-weight: 800; color: #0f172a; }
        .cell-muted { color: var(--muted); font-size: 0.78rem; margin-top: 0.18rem; }
        .badge { display: inline-flex; align-items: center; border-radius: 999px; padding: 0.36rem 0.58rem; font-size: 0.76rem; font-weight: 800; white-space: nowrap; }
        .badge.role-1 { background: #e0f2fe; color: #075985; }
        .badge.role-2 { background: #ffedd5; color: #9a3412; }
        .badge.role-3 { background: #fee2e2; color: #991b1b; }
        .badge.role-4 { background: #ede9fe; color: #5b21b6; }
        .badge.action-create, .badge.action-approve, .badge.action-login { background: #dcfce7; color: #166534; }
        .badge.action-update, .badge.action-realize { background: #dbeafe; color: #1d4ed8; }
        .badge.action-delete, .badge.action-reject, .badge.action-logout { background: #fee2e2; color: #b91c1c; }
        .empty-state { padding: 2.2rem 1.2rem; text-align: center; color: var(--muted); font-weight: 700; line-height: 1.6; }
        .pagination-wrap { padding: 1rem; }
        .pagination-wrap nav { display: flex; justify-content: flex-end; }
        .row-actions { display: flex; align-items: center; gap: 0.45rem; flex-wrap: wrap; }
        .detail-btn, .restore-btn { display: inline-flex; align-items: center; gap: 0.35rem; border: 1px solid #fed7aa; border-radius: 999px; background: #fff7ed; color: #9a3412; padding: 0.42rem 0.62rem; font: inherit; font-size: 0.76rem; font-weight: 800; cursor: pointer; }
        .restore-btn { background: #ecfdf3; border-color: #bbf7d0; color: #166534; }
        .restore-btn:disabled { opacity: 0.58; cursor: not-allowed; }
        .modal-backdrop { position: fixed; inset: 0; display: none; align-items: center; justify-content: center; padding: 1rem; background: rgba(15, 23, 42, 0.42); z-index: 1400; }
        .modal-backdrop.open { display: flex; }
        .detail-modal { width: min(100%, 520px); background: #fff; border: 1px solid var(--border); border-radius: 22px; box-shadow: 0 24px 60px -28px rgba(15, 23, 42, 0.55); padding: 1.1rem; }
        .modal-head { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 0.8rem; }
        .modal-title { color: var(--brand-orange); font-weight: 800; font-size: 1.05rem; }
        .modal-close { width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #ffd2bb; border-radius: 50%; background: #fff7ed; color: var(--brand-orange); cursor: pointer; }
        .detail-grid { display: grid; gap: 0.7rem; }
        .detail-item { border: 1px solid #f2e7df; border-radius: 16px; background: #fffaf7; padding: 0.8rem; }
        .detail-label { color: var(--muted); font-size: 0.74rem; font-weight: 800; text-transform: uppercase; margin-bottom: 0.28rem; }
        .detail-value { color: #0f172a; font-weight: 800; line-height: 1.45; overflow-wrap: anywhere; }
        @media (max-width: 1160px) { .summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } .filter-form { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 860px) { .activity-page, .app-shell.sidebar-collapsed .activity-page { margin-left: 0; padding: 1.2rem 1rem 2rem; } }
        @media (max-width: 640px) { .summary-grid, .filter-form { grid-template-columns: 1fr; } .hero-card, .summary-card, .filter-card, .table-card { border-radius: 20px; } }
    </style>
</head>
<body>
<div class="app-shell" id="appShell">
    @include('header')

    <main class="activity-page">
        <div class="page-shell">
            <section class="hero-card">
                <div class="eyebrow"><i class="bi bi-activity"></i> Catatan Aktivitas</div>
                <h1 class="hero-title">Pantau semua aktivitas sistem.</h1>
                <p class="hero-subtitle">Lihat siapa melakukan apa, kapan aktivitas terjadi, modul yang terdampak, target data, dan konteks ruangan yang berkaitan.</p>
            </section>

            <section class="summary-grid">
                <div class="summary-card"><div class="summary-label">Total Aktivitas</div><div class="summary-value">{{ number_format($summary['total']) }}</div></div>
                <div class="summary-card"><div class="summary-label">Aktivitas Hari Ini</div><div class="summary-value">{{ number_format($summary['today']) }}</div></div>
                <div class="summary-card"><div class="summary-label">Login / Logout</div><div class="summary-value">{{ number_format($summary['login']) }}</div></div>
                <div class="summary-card"><div class="summary-label">Perubahan Data</div><div class="summary-value">{{ number_format($summary['data_changes']) }}</div></div>
            </section>

            <section class="filter-card">
                <form method="GET" action="{{ route('activity.logs') }}" class="filter-form">
                    <div>
                        <label for="name">Nama User</label>
                        <input id="name" type="text" name="name" value="{{ $filters['name'] }}" placeholder="Cari nama user">
                    </div>
                    <div>
                        <label for="role">Role</label>
                        <select id="role" name="role">
                            <option value="">Semua Role</option>
                            @foreach ($roles as $level => $role)
                                <option value="{{ $level }}" @selected($filters['role'] === (string) $level)>{{ $role }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="date_start">Tanggal Awal</label>
                        <input id="date_start" type="date" name="date_start" value="{{ $filters['date_start'] }}">
                    </div>
                    <div>
                        <label for="date_end">Tanggal Akhir</label>
                        <input id="date_end" type="date" name="date_end" value="{{ $filters['date_end'] }}">
                    </div>
                    <button type="submit" class="btn"><i class="bi bi-search"></i> Cari</button>
                    <a href="{{ route('activity.logs') }}" class="btn secondary"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>
                </form>
            </section>

            <section class="table-card">
                <div class="table-head">
                    <div>
                        <div class="table-title">Daftar Aktivitas</div>
                        <div class="table-subtitle">Aktivitas terbaru tampil paling atas.</div>
                    </div>
                </div>

                @if (! $activityTableReady)
                    <div class="empty-state">Tabel activity_logs belum tersedia. Jalankan migrasi database agar catatan aktivitas bisa mulai tersimpan.</div>
                @elseif ($logs->isEmpty())
                    <div class="empty-state">Belum ada aktivitas yang cocok dengan filter saat ini.</div>
                @else
                    <div class="table-scroll">
                        <table>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal & Waktu</th>
                                    <th>Nama User</th>
                                    <th>Role</th>
                                    <th>Aksi</th>
                                    <th>Modul</th>
                                    <th>Target / Data</th>
                                    <th>Detail Aktivitas</th>
                                    <th>Kelas / Ruangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($logs as $log)
                                    @php
                                        $actionClass = match ($log->action) {
                                            'Menambah' => 'action-create',
                                            'Mengubah' => 'action-update',
                                            'Menghapus' => 'action-delete',
                                            'Menyetujui' => 'action-approve',
                                            'Menolak' => 'action-reject',
                                            'Merealisasikan' => 'action-realize',
                                            'Login' => 'action-login',
                                            'Logout' => 'action-logout',
                                            default => 'action-update',
                                        };
                                    @endphp
                                    <tr>
                                        <td>{{ $logs->firstItem() + $loop->index }}</td>
                                        <td>
                                            <div class="cell-strong">{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y') }}</div>
                                            <div class="cell-muted">{{ \Carbon\Carbon::parse($log->created_at)->format('H:i') }}</div>
                                        </td>
                                        <td class="cell-strong">{{ $log->user_name ?? '-' }}</td>
                                        <td><span class="badge role-{{ $log->user_level ?? 0 }}">{{ $log->role_name ?? '-' }}</span></td>
                                        <td><span class="badge {{ $actionClass }}">{{ $log->action }}</span></td>
                                        <td>{{ $log->module }}</td>
                                        <td>{{ $log->target ?? '-' }}</td>
                                        <td>{{ $log->detail ?? '-' }}</td>
                                        <td>{{ $log->room_context ?? '-' }}</td>
                                        <td>
                                            <div class="row-actions">
                                                <button
                                                    type="button"
                                                    class="detail-btn"
                                                    data-detail-time="{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y, H:i') }}"
                                                    data-detail-user="{{ $log->user_name ?? '-' }}"
                                                    data-detail-role="{{ $log->role_name ?? '-' }}"
                                                    data-detail-action="{{ $log->action }}"
                                                    data-detail-module="{{ $log->module }}"
                                                    data-detail-target="{{ $log->target ?? '-' }}"
                                                    data-detail-room="{{ $log->room_context ?? '-' }}"
                                                    data-detail-text="{{ $log->detail ?? 'Tidak ada detail tambahan.' }}"
                                                >
                                                    <i class="bi bi-eye"></i>
                                                    Detail
                                                </button>
                                                @if ((int) ($log->user_level ?? 0) === 3 && $log->action === 'Menghapus')
                                                    <button type="button" class="restore-btn" disabled title="Pemulihan membutuhkan arsip data terhapus.">
                                                        <i class="bi bi-arrow-counterclockwise"></i>
                                                        Pulihkan
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination-wrap">{{ $logs->links() }}</div>
                @endif
            </section>
        </div>
    </main>

    <div class="modal-backdrop" id="activityDetailModal" aria-hidden="true">
        <section class="detail-modal" role="dialog" aria-modal="true" aria-labelledby="activityDetailTitle">
            <div class="modal-head">
                <div class="modal-title" id="activityDetailTitle">Detail Aktivitas</div>
                <button type="button" class="modal-close" id="activityDetailClose" aria-label="Tutup detail">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Waktu</div>
                    <div class="detail-value" id="detailTime">-</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">User</div>
                    <div class="detail-value" id="detailUser">-</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Role</div>
                    <div class="detail-value" id="detailRole">-</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Aksi & Modul</div>
                    <div class="detail-value" id="detailActionModule">-</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Target / Data</div>
                    <div class="detail-value" id="detailTarget">-</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Kelas / Ruangan</div>
                    <div class="detail-value" id="detailRoom">-</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Detail Aktivitas</div>
                    <div class="detail-value" id="detailText">-</div>
                </div>
            </div>
        </section>
    </div>
</div>
<script>
    (function () {
        const modal = document.getElementById('activityDetailModal');
        const closeButton = document.getElementById('activityDetailClose');
        const fields = {
            time: document.getElementById('detailTime'),
            user: document.getElementById('detailUser'),
            role: document.getElementById('detailRole'),
            actionModule: document.getElementById('detailActionModule'),
            target: document.getElementById('detailTarget'),
            room: document.getElementById('detailRoom'),
            text: document.getElementById('detailText'),
        };

        function openModal(button) {
            fields.time.textContent = button.dataset.detailTime || '-';
            fields.user.textContent = button.dataset.detailUser || '-';
            fields.role.textContent = button.dataset.detailRole || '-';
            fields.actionModule.textContent = (button.dataset.detailAction || '-') + ' - ' + (button.dataset.detailModule || '-');
            fields.target.textContent = button.dataset.detailTarget || '-';
            fields.room.textContent = button.dataset.detailRoom || '-';
            fields.text.textContent = button.dataset.detailText || '-';
            modal.classList.add('open');
            modal.setAttribute('aria-hidden', 'false');
        }

        function closeModal() {
            modal.classList.remove('open');
            modal.setAttribute('aria-hidden', 'true');
        }

        document.querySelectorAll('.detail-btn').forEach(function (button) {
            button.addEventListener('click', function () {
                openModal(button);
            });
        });

        closeButton?.addEventListener('click', closeModal);
        modal?.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal?.classList.contains('open')) {
                closeModal();
            }
        });
    })();
</script>
</body>
</html>
