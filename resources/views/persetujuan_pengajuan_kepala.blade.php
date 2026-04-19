<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Persetujuan Pengajuan | InfraSPH</title>
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

        .owner-approval-page {
            margin-left: 320px;
            width: calc(100% - 320px);
            min-height: 100vh;
            padding: 2rem 1.6rem 2.5rem;
            transition: margin-left 0.28s ease, width 0.28s ease, padding 0.28s ease;
        }

        .app-shell.sidebar-collapsed .owner-approval-page {
            margin-left: 88px;
            width: calc(100% - 88px);
        }

        .page-shell { width: 100%; max-width: none; }

        .hero-card,
        .summary-card,
        .filter-card,
        .request-card,
        .empty-card,
        .success-banner,
        .error-banner {
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

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
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
            margin-bottom: 1rem;
        }

        .filter-row {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .filter-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            min-width: 132px;
            padding: 0.9rem 1rem;
            border-radius: 18px;
            border: 1px solid #ead9d0;
            background: #ffffff;
            color: #344054;
            font-size: 0.96rem;
            font-weight: 700;
            text-decoration: none;
        }

        .filter-pill.active {
            color: #ffffff;
            border-color: transparent;
            background: linear-gradient(135deg, var(--brand-orange), #ff7b2f);
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

        .request-stack {
            display: grid;
            gap: 1rem;
        }

        .request-card {
            padding: 1.2rem 1.2rem 1.1rem;
        }

        .request-top {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .request-title {
            font-size: 1.2rem;
            font-weight: 800;
            color: #172033;
            margin-bottom: 0.15rem;
        }

        .request-subtitle {
            color: #7b8794;
            font-size: 0.88rem;
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

        .request-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.9rem;
            margin-bottom: 1rem;
        }

        .info-item {
            padding: 0.85rem 0.9rem;
            border-radius: 18px;
            background: #fff8f4;
            border: 1px solid #f6e4da;
        }

        .info-label {
            color: #6b7280;
            font-size: 0.78rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .info-value {
            color: #172033;
            font-size: 0.95rem;
            font-weight: 700;
            line-height: 1.5;
        }

        .reason-card {
            padding: 0.95rem 1rem;
            border-radius: 20px;
            background: #fffdfb;
            border: 1px solid #f3e3db;
            margin-bottom: 1rem;
        }

        .reason-title {
            font-size: 0.82rem;
            color: #6b7280;
            font-weight: 700;
            margin-bottom: 0.35rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .reason-copy {
            color: #4b5563;
            line-height: 1.7;
        }

        .flow-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
            margin-bottom: 1rem;
        }

        .flow-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.55rem 0.8rem;
            border-radius: 999px;
            border: 1px solid #ead9d0;
            background: #ffffff;
            color: #667085;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .flow-chip.done {
            background: #eaf8ef;
            border-color: #cdeed5;
            color: #15803d;
        }

        .flow-chip.current {
            background: #fff6db;
            border-color: #f2dfa7;
            color: #a16207;
        }

        .flow-chip.rejected {
            background: #fff0eb;
            border-color: #ffd7c7;
            color: #c2410c;
        }

        .actions-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.85rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            border: none;
            border-radius: 16px;
            padding: 0.85rem 1rem;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
        }

        .btn-approve {
            background: linear-gradient(135deg, var(--brand-orange), #ff7b2f);
            color: #ffffff;
        }

        .btn-reject {
            background: #ffffff;
            color: #c2410c;
            border: 1px solid #ffd7c7;
        }

        .status-note {
            color: #667085;
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .reject-form {
            display: none;
            gap: 0.7rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px dashed #f0d7ca;
        }

        .reject-form.active {
            display: grid;
        }

        .reject-form textarea {
            width: 100%;
            min-height: 110px;
            border: 1px solid #ead9d0;
            border-radius: 18px;
            padding: 0.95rem 1rem;
            font: inherit;
            resize: vertical;
            outline: none;
            background: #fffdfa;
        }

        .reject-form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.7rem;
        }

        .btn-submit-reject {
            background: #c2410c;
            color: #ffffff;
        }

        .btn-cancel {
            background: #ffffff;
            color: #667085;
            border: 1px solid #ead9d0;
        }

        .empty-card {
            padding: 1.25rem 1.35rem;
            color: #667085;
            line-height: 1.65;
        }

        .pagination-wrap {
            display: flex;
            justify-content: center;
            margin-top: 1rem;
        }

        .pagination-wrap nav > div:first-child {
            display: none;
        }

        .pagination-wrap svg {
            width: 1rem;
            height: 1rem;
        }

        .pagination-wrap nav > div:last-child > span,
        .pagination-wrap nav > div:last-child a {
            border-radius: 14px;
            border: 1px solid #ead9d0;
            padding: 0.7rem 0.9rem;
            color: #344054;
            text-decoration: none;
            background: #ffffff;
            font-weight: 700;
        }

        .pagination-wrap nav > div:last-child > span[aria-current="page"] {
            background: linear-gradient(135deg, var(--brand-orange), #ff7b2f);
            border-color: transparent;
            color: #ffffff;
        }

        @media (max-width: 1120px) {
            .summary-grid,
            .request-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 860px) {
            .owner-approval-page {
                margin-left: 0;
                width: 100%;
                padding: 1.2rem 1rem 2rem;
            }

            .summary-grid,
            .request-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="app-shell" id="appShell">
        @include('header')

        <main class="owner-approval-page">
            <div class="page-shell">
            <section class="hero-card">
                <div class="eyebrow">Kepala Sekolah</div>
                <h1 class="hero-title">Persetujuan Pengajuan</h1>
                <p class="hero-subtitle">Tinjau dan berikan keputusan atas pengajuan dari seluruh kelas yang sudah disetujui wali kelas.</p>
            </section>

            <section class="summary-grid">
                <article class="summary-card is-accent">
                    <div class="summary-label">Menunggu Persetujuan</div>
                    <div class="summary-value">{{ number_format($summary['waiting']) }}</div>
                    <div class="summary-note">Pengajuan yang masih menunggu keputusan kepala sekolah</div>
                </article>
                <article class="summary-card">
                    <div class="summary-label">Disetujui Hari Ini</div>
                    <div class="summary-value">{{ number_format($summary['approved_today']) }}</div>
                    <div class="summary-note">Pengajuan yang disetujui kepala sekolah hari ini</div>
                </article>
                <article class="summary-card">
                    <div class="summary-label">Ditolak Hari Ini</div>
                    <div class="summary-value">{{ number_format($summary['rejected_today']) }}</div>
                    <div class="summary-note">Pengajuan yang ditolak kepala sekolah hari ini</div>
                </article>
            </section>

            <section class="filter-card">
                <div class="filter-row">
                    <a href="{{ route('owner.requests.approval', ['status' => 'menunggu']) }}" @class(['filter-pill', 'active' => $activeStatus === 'menunggu'])>Menunggu</a>
                    <a href="{{ route('owner.requests.approval', ['status' => 'disetujui']) }}" @class(['filter-pill', 'active' => $activeStatus === 'disetujui'])>Disetujui</a>
                    <a href="{{ route('owner.requests.approval', ['status' => 'ditolak']) }}" @class(['filter-pill', 'active' => $activeStatus === 'ditolak'])>Ditolak</a>
                </div>
            </section>

            @if (session('success') || $errors->any())
                <div class="feedback-stack">
                    @if (session('success'))
                        <div class="success-banner">{{ session('success') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="error-banner">
                            {{ $errors->first() }}
                        </div>
                    @endif
                </div>
            @endif

            @if ($requests->isEmpty())
                <section class="empty-card">Tidak ada pengajuan yang perlu disetujui saat ini.</section>
            @else
                <section class="request-stack">
                    @foreach ($requests as $requestItem)
                        <article class="request-card">
                            <div class="request-top">
                                <div>
                                    <div class="request-title">{{ $requestItem['barang_ringkas'] }} - {{ $requestItem['jumlah_ringkas'] }} unit</div>
                                    <div class="request-subtitle">{{ $requestItem['ruangan'] }} | {{ $requestItem['tanggal_label'] }}</div>
                                </div>
                                <span class="badge {{ $requestItem['status_class'] }}">{{ $requestItem['status'] }}</span>
                            </div>

                            <div class="request-grid">
                                <div class="info-item">
                                    <div class="info-label">Jenis</div>
                                    <div class="info-value">{{ $requestItem['jenis'] }}</div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Kelas</div>
                                    <div class="info-value">{{ $requestItem['ruangan'] }}</div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Diajukan Oleh</div>
                                    <div class="info-value">{{ $requestItem['peminta'] }}</div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Status Wali Kelas</div>
                                    <div class="info-value">{{ $requestItem['wali_status'] }}</div>
                                </div>
                            </div>

                            <div class="reason-card">
                                <div class="reason-title">Alasan Pengajuan</div>
                                <div class="reason-copy">{{ $requestItem['alasan'] }}</div>
                            </div>

                            <div class="flow-row">
                                @foreach ($requestItem['flow'] as $flow)
                                    <span class="flow-chip {{ $flow['status'] }}">
                                        @if ($flow['status'] === 'done')
                                            <i class="bi bi-check-circle-fill"></i>
                                        @elseif ($flow['status'] === 'current')
                                            <i class="bi bi-hourglass-split"></i>
                                        @elseif ($flow['status'] === 'rejected')
                                            <i class="bi bi-x-circle-fill"></i>
                                        @else
                                            <i class="bi bi-circle"></i>
                                        @endif
                                        {{ $flow['label'] }}
                                    </span>
                                @endforeach
                            </div>

                            @if ($requestItem['can_action'])
                                <div class="actions-row">
                                    <form method="POST" action="{{ route('owner.requests.approve', $requestItem['id_permintaan']) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-approve">
                                            <i class="bi bi-check2-circle"></i>
                                            Setujui
                                        </button>
                                    </form>

                                    <button type="button" class="btn btn-reject js-reject-toggle" data-target="reject-form-{{ $requestItem['id_permintaan'] }}">
                                        <i class="bi bi-x-circle"></i>
                                        Tolak
                                    </button>
                                </div>

                                <form method="POST" action="{{ route('owner.requests.reject', $requestItem['id_permintaan']) }}" class="reject-form" id="reject-form-{{ $requestItem['id_permintaan'] }}">
                                    @csrf
                                    <textarea name="rejection_reason" placeholder="Tuliskan alasan penolakan..." required></textarea>
                                    <div class="reject-form-actions">
                                        <button type="button" class="btn btn-cancel js-reject-cancel" data-target="reject-form-{{ $requestItem['id_permintaan'] }}">Batal</button>
                                        <button type="submit" class="btn btn-submit-reject">Kirim</button>
                                    </div>
                                </form>
                            @else
                                <div class="status-note">
                                    @if ($requestItem['status_key'] === 'disetujui')
                                        Pengajuan ini sudah disetujui kepala sekolah.
                                    @elseif ($requestItem['status_key'] === 'ditolak')
                                        Pengajuan ini sudah ditolak pada tahap kepala sekolah.
                                    @endif
                                </div>
                            @endif
                        </article>
                    @endforeach
                </section>

                @if ($requests->hasPages())
                    <div class="pagination-wrap">
                        {{ $requests->links() }}
                    </div>
                @endif
            @endif
            </div>
        </main>

        @include('chatbot')
    </div>

    <script>
        document.querySelectorAll('.js-reject-toggle').forEach((button) => {
            button.addEventListener('click', () => {
                const target = document.getElementById(button.dataset.target);
                if (!target) {
                    return;
                }

                target.classList.add('active');
            });
        });

        document.querySelectorAll('.js-reject-cancel').forEach((button) => {
            button.addEventListener('click', () => {
                const target = document.getElementById(button.dataset.target);
                if (!target) {
                    return;
                }

                target.classList.remove('active');
            });
        });
    </script>
</body>
</html>
