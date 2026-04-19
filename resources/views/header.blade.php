<style>
    .sidebar {
        position: fixed;
        inset: 0 auto 0 0;
        width: 320px;
        max-width: 100%;
        display: flex;
        flex-direction: column;
        background: linear-gradient(180deg, #ff7a21, #ff5900);
        color: #fff6f1;
        padding: 1.25rem 1rem 1rem;
        overflow-y: auto;
        z-index: 1000;
        box-shadow: 14px 0 34px rgba(225, 79, 0, 0.18);
        transition: transform 0.28s ease, width 0.28s ease, box-shadow 0.28s ease;
    }

    .sidebar-brand {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.7rem 0.55rem 1rem;
        margin-bottom: 0.85rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.18);
        min-width: 0;
        justify-content: space-between;
    }

    .sidebar-brand-link {
        min-width: 0;
        flex: 1 1 auto;
        text-decoration: none;
    }

    .sidebar-brand-logo {
        width: 62px;
        height: 62px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        overflow: visible;
    }

    .sidebar-brand img {
        width: 62px;
        height: auto;
        object-fit: contain;
        filter: none;
        transform: scale(1.18);
        transform-origin: center;
    }

    .sidebar-brand-text {
        font-size: 1.6rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: #ffffff;
        min-width: 0;
        flex: 1 1 auto;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .sidebar-brand-main {
        display: flex;
        align-items: center;
        gap: 1.25rem;
        min-width: 0;
        flex: 1 1 auto;
    }

    .sidebar-toggle {
        margin-left: auto;
        width: 44px;
        height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.14);
        color: #ffffff;
        cursor: pointer;
        transition: background 0.2s ease, transform 0.2s ease;
        flex-shrink: 0;
    }

    .sidebar-toggle:hover {
        background: rgba(255, 255, 255, 0.22);
        transform: translateY(-1px);
    }

    .sidebar-toggle i {
        font-size: 1.5rem;
        line-height: 1;
    }

    .sidebar-user-info {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        margin: 0.2rem 0 1rem;
        padding: 0.9rem 0.85rem;
        background: rgba(255, 255, 255, 0.13);
        border: 1px solid rgba(255, 255, 255, 0.16);
        border-radius: 18px;
    }

    .sidebar-avatar {
        width: 46px;
        height: 46px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.24);
        color: #ffffff;
        font-weight: 800;
        border: 2px solid rgba(255, 255, 255, 0.35);
        flex-shrink: 0;
    }

    .sidebar-user-details {
        min-width: 0;
        flex: 1 1 auto;
    }

    .sidebar-user-name {
        color: #ffffff;
        font-size: 0.95rem;
        font-weight: 700;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .sidebar-user-role {
        color: rgba(255, 255, 255, 0.85);
        font-size: 0.78rem;
        margin-top: 0.15rem;
    }

    .sidebar-nav {
        list-style: none;
        display: grid;
        gap: 0.35rem;
        margin: 0;
        padding: 0;
    }

    .sidebar-nav a {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        padding: 0.9rem 0.95rem;
        border-radius: 16px;
        color: rgba(255, 255, 255, 0.94);
        text-decoration: none;
        font-size: 0.95rem;
        font-weight: 600;
        transition: background 0.2s ease, transform 0.2s ease, color 0.2s ease;
    }

    .sidebar-nav a:hover {
        background: rgba(255, 255, 255, 0.14);
        transform: translateX(3px);
    }

    .sidebar-nav a.active {
        background: rgba(255, 255, 255, 0.92);
        color: #e14f00;
        box-shadow: 0 12px 24px -18px rgba(75, 26, 0, 0.55);
    }

    .sidebar-nav i,
    .sidebar-logout-btn i {
        font-size: 1.05rem;
        width: 1.2rem;
        text-align: center;
        flex-shrink: 0;
    }

    .sidebar-logout {
        margin-top: auto;
        padding-top: 1rem;
    }

    .sidebar-logout-btn {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        border: 1px solid rgba(255, 255, 255, 0.2);
        background: rgba(255, 255, 255, 0.12);
        color: #ffffff;
        border-radius: 18px;
        padding: 0.95rem 1rem;
        font-size: 0.95rem;
        font-weight: 700;
        cursor: pointer;
    }

    .app-shell.sidebar-collapsed .sidebar {
        width: 88px;
        padding-inline: 0.7rem;
        box-shadow: 10px 0 24px rgba(225, 79, 0, 0.14);
    }

    .app-shell.sidebar-collapsed .sidebar-brand {
        justify-content: center;
        padding: 0.55rem 0 0.9rem;
    }

    .app-shell.sidebar-collapsed .sidebar-user-info,
    .app-shell.sidebar-collapsed .sidebar-logout {
        display: none;
    }

    .app-shell.sidebar-collapsed .sidebar-brand-link {
        display: none;
    }

    .app-shell.sidebar-collapsed .sidebar-toggle {
        margin-left: 0;
    }

    .app-shell.sidebar-collapsed .sidebar-nav {
        margin-top: 0.4rem;
    }

    .app-shell.sidebar-collapsed .sidebar-nav a {
        justify-content: center;
        padding: 0.9rem 0;
        border-radius: 18px;
    }

    .app-shell.sidebar-collapsed .sidebar-nav span {
        display: none;
    }

    .app-shell.sidebar-collapsed .sidebar-nav i {
        width: auto;
        font-size: 1.15rem;
    }

    @media (max-width: 860px) {
        .sidebar {
            position: static;
            width: 100%;
            height: auto;
            min-height: auto;
            border-radius: 0 0 24px 24px;
            transform: none !important;
            padding: 1rem 0.85rem 0.9rem;
            overflow-x: hidden;
        }

        .sidebar-toggle {
            display: none;
        }

        .sidebar-brand img {
            width: 44px;
            transform: scale(1.12);
        }

        .sidebar-brand-logo {
            width: 44px;
            height: 44px;
        }

        .sidebar-brand-text {
            font-size: 1.32rem;
        }
    }

    @media (max-width: 640px) {
        .sidebar {
            padding: 0.9rem 0.7rem 0.85rem;
        }

        .sidebar-brand {
            gap: 0.6rem;
            padding: 0.45rem 0.2rem 0.9rem;
        }

        .sidebar-brand-main {
            gap: 0.6rem;
            min-width: 0;
            max-width: calc(100% - 2.9rem);
        }

        .sidebar-brand-logo {
            width: 40px;
            height: 40px;
        }

        .sidebar-brand img {
            width: 40px;
            transform: scale(1.1);
        }

        .sidebar-brand-text {
            font-size: 1.05rem;
        }

        .sidebar-toggle {
            width: 36px;
            height: 36px;
            border-radius: 12px;
            margin-left: 0.2rem;
            flex: 0 0 36px;
        }

        .sidebar-user-info {
            padding: 0.8rem 0.7rem;
        }

        .sidebar-nav a {
            padding: 0.82rem 0.8rem;
            font-size: 0.92rem;
        }

        .sidebar-logout-btn {
            padding: 0.85rem 0.9rem;
        }
    }
</style>

<aside class="sidebar">
    <div class="sidebar-brand">
        <a class="sidebar-brand-link" href="{{ route('dashboard') }}">
            <span class="sidebar-brand-main">
                <span class="sidebar-brand-logo">
                    <img src="{{ asset('images/Infrasph.png') }}" alt="Logo InfraSPH">
                </span>
                <span class="sidebar-brand-text">InfraSPH</span>
            </span>
        </a>
        <button type="button" class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar" aria-expanded="true">
            <i class="bi bi-list"></i>
        </button>
    </div>

    <div class="sidebar-user-info">
        <div class="sidebar-avatar">
            {{ strtoupper(substr($user['nama'] ?? 'U', 0, 1)) }}
        </div>
        <div class="sidebar-user-details">
            <div class="sidebar-user-name">{{ $user['nama'] ?? 'Pengguna' }}</div>
            <div class="sidebar-user-role">{{ $dashboard['role_name'] ?? 'Role' }}</div>
        </div>
    </div>

    @php
        $level = (int) ($user['level'] ?? 0);
        $safeRoute = static function (string $name): string {
            return \Illuminate\Support\Facades\Route::has($name) ? route($name) : '#';
        };
        $menuByLevel = [
            1 => [
                ['label' => 'Kelas Saya', 'icon' => 'bi bi-door-open-fill', 'url' => route('class.inventory')],
                ['label' => 'Ajukan Permintaan', 'icon' => 'bi bi-send-plus-fill', 'url' => route('requests.create')],
                ['label' => 'Riwayat Pengajuan', 'icon' => 'bi bi-clock-history', 'url' => route('requests.history')],
            ],
            2 => [
                ['label' => 'Kelas Saya', 'icon' => 'bi bi-building', 'url' => $safeRoute('admin.class.inventory')],
                ['label' => 'Pengajuan Kelas', 'icon' => 'bi bi-inbox-fill', 'url' => $safeRoute('admin.requests.inbox')],
                ['label' => 'Riwayat Pengajuan', 'icon' => 'bi bi-patch-check-fill', 'url' => $safeRoute('admin.requests.history')],
            ],
            3 => [
                ['label' => 'Data User', 'icon' => 'bi bi-people-fill', 'url' => $safeRoute('superadmin.users')],
                ['label' => 'Data Ruangan', 'icon' => 'bi bi-building-fill-gear', 'url' => $safeRoute('superadmin.rooms')],
                ['label' => 'Data Barang', 'icon' => 'bi bi-grid-fill', 'url' => $safeRoute('superadmin.items')],
                ['label' => 'Data Inventaris', 'icon' => 'bi bi-box-seam-fill', 'url' => $safeRoute('superadmin.inventories')],
                ['label' => 'Realisasi Pengajuan', 'icon' => 'bi bi-list-check', 'url' => $safeRoute('superadmin.requests.realization')],
                ['label' => 'Laporan', 'icon' => 'bi bi-bar-chart-line-fill', 'url' => $safeRoute('superadmin.reports')],
            ],
            4 => [
                ['label' => 'Semua Ruangan', 'icon' => 'bi bi-buildings-fill', 'url' => $safeRoute('owner.rooms')],
                ['label' => 'Inventaris Sekolah', 'icon' => 'bi bi-boxes', 'url' => $safeRoute('owner.inventories')],
                ['label' => 'Persetujuan Pengajuan', 'icon' => 'bi bi-clipboard2-check-fill', 'url' => $safeRoute('owner.requests.approval')],
                ['label' => 'Laporan', 'icon' => 'bi bi-bar-chart-fill', 'url' => $safeRoute('owner.reports')],
            ],
        ];
        $menus = $menuByLevel[$level] ?? [
            ['label' => 'Dashboard', 'icon' => 'bi bi-grid-1x2-fill', 'url' => route('dashboard')],
        ];
    @endphp

    <ul class="sidebar-nav">
        @foreach ($menus as $menu)
            <li>
                <a href="{{ $menu['url'] }}" @class(['active' => request()->url() === $menu['url']])>
                    <i class="{{ $menu['icon'] }}"></i>
                    <span>{{ $menu['label'] }}</span>
                </a>
            </li>
        @endforeach
    </ul>

    <form action="{{ route('logout') }}" method="POST" class="sidebar-logout">
        @csrf
        <button type="submit" class="sidebar-logout-btn">
            <i class="bi bi-box-arrow-right"></i>
            <span>Log Out</span>
        </button>
    </form>
</aside>

<script>
    (function () {
        function initSidebarToggle() {
            const appShell = document.getElementById('appShell');
            const toggleButton = document.getElementById('sidebarToggle');

            if (!appShell || !toggleButton || window.innerWidth <= 860) {
                return;
            }

            toggleButton.addEventListener('click', function () {
                appShell.classList.toggle('sidebar-collapsed');
                const expanded = !appShell.classList.contains('sidebar-collapsed');
                toggleButton.setAttribute('aria-expanded', String(expanded));
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initSidebarToggle, { once: true });
        } else {
            initSidebarToggle();
        }
    })();
</script>
