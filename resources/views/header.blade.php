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
        gap: 0.8rem;
        width: 100%;
        margin: 0;
        padding: 0.82rem 0.9rem;
        background: rgba(255, 255, 255, 0.13);
        border: 1px solid rgba(255, 255, 255, 0.16);
        border-radius: 18px;
        color: #ffffff;
        cursor: pointer;
        text-align: left;
        transition: background 0.2s ease, transform 0.2s ease;
    }

    .sidebar-user-info:hover,
    .sidebar-user-info[aria-expanded="true"] {
        background: rgba(255, 255, 255, 0.2);
        transform: translateY(-1px);
    }

    .sidebar-avatar {
        width: 42px;
        height: 42px;
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
        display: flex;
        flex-direction: column;
        justify-content: center;
        line-height: 1.2;
    }

    .sidebar-user-name {
        color: #ffffff;
        font-size: 0.9rem;
        font-weight: 800;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .sidebar-user-role {
        color: rgba(255, 255, 255, 0.85);
        font-size: 0.72rem;
        font-weight: 700;
        margin-top: 0.18rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .sidebar-nav {
        list-style: none;
        display: grid;
        gap: 0.35rem;
        margin: 0 0 1rem;
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
    .account-menu-link i,
    .account-menu-btn i {
        font-size: 1.05rem;
        width: 1.2rem;
        text-align: center;
        flex-shrink: 0;
    }

    .sidebar-account {
        margin-top: auto;
        position: relative;
        padding-top: 1rem;
    }

    .account-menu {
        position: fixed;
        left: 332px;
        bottom: 1rem;
        width: 228px;
        display: none;
        gap: 0.45rem;
        padding: 0.85rem;
        border: 1px solid rgba(255, 89, 0, 0.16);
        border-radius: 20px;
        background: #ffffff;
        color: #1f2937;
        box-shadow: 0 22px 48px -24px rgba(31, 41, 55, 0.5);
        z-index: 1100;
    }

    .sidebar-account.open .account-menu {
        display: grid;
    }

    .account-menu-head {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.55rem 0.55rem 0.75rem;
        border-bottom: 1px solid #f2e7df;
        margin-bottom: 0.25rem;
    }

    .account-menu-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff1e8;
        color: #ff5900;
        font-weight: 800;
        border: 1px solid #ffd2bb;
        flex-shrink: 0;
    }

    .account-menu-name {
        font-size: 0.92rem;
        font-weight: 800;
        color: #111827;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .account-menu-role {
        margin-top: 0.12rem;
        font-size: 0.76rem;
        font-weight: 700;
        color: #64748b;
    }

    .account-menu-link,
    .account-menu-btn {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 0.7rem;
        border: 0;
        background: transparent;
        color: #334155;
        border-radius: 14px;
        padding: 0.75rem 0.7rem;
        font: inherit;
        font-size: 0.9rem;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
    }

    .account-menu-link:hover,
    .account-menu-btn:hover {
        background: #fff4ec;
        color: #e14f00;
    }

    .account-menu-logout {
        margin: 0;
    }

    .account-menu-btn.danger {
        color: #b91c1c;
    }

    .account-menu-main {
        display: grid;
        gap: 0.45rem;
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

    .app-shell.sidebar-collapsed .sidebar-user-info {
        justify-content: center;
        padding: 0.62rem 0;
        border-radius: 18px;
    }

    .app-shell.sidebar-collapsed .sidebar-user-details {
        display: none;
    }

    .app-shell.sidebar-collapsed .sidebar-avatar {
        width: 42px;
        height: 42px;
    }

    .app-shell.sidebar-collapsed .account-menu {
        left: 100px;
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

        .account-menu {
            left: 0.75rem;
            right: 0.75rem;
            bottom: 0.85rem;
            width: auto;
        }
    }

    html {
        overflow-x: hidden;
    }

    body,
    .app-shell {
        max-width: 100%;
        overflow-x: hidden;
    }

    img,
    svg,
    video,
    canvas,
    iframe {
        max-width: 100%;
    }

    .page-shell,
    .content-area,
    .dashboard-content,
    .activity-page,
    .superadmin-users-page,
    .superadmin-rooms-page,
    .superadmin-inventory-page,
    .superadmin-followup-page,
    .superadmin-reports-page,
    .request-page,
    .password-page,
    .profile-security-page,
    .owner-rooms-page,
    .owner-inventory-page,
    .owner-approvals-page,
    .teacher-requests-page,
    .teacher-history-page,
    .student-class-page,
    .student-history-page,
    .hak-akses-page,
    .history-page,
    .owner-reports-page,
    .owner-approval-page,
    .inbox-page,
    .wali-page,
    .kelas-page {
        max-width: 100%;
        min-width: 0;
    }

    .hero-card,
    .summary-card,
    .filter-card,
    .table-card,
    .tab-card,
    .empty-card,
    .profile-card,
    .security-card,
    .modal-dialog,
    .password-modal {
        max-width: 100%;
        min-width: 0;
    }

    .summary-grid,
    .stat-grid,
    .dashboard-grid,
    .quick-grid,
    .metrics-grid,
    .content-grid,
    .field-grid,
    .form-grid,
    .filter-form {
        min-width: 0;
    }

    .table-wrap,
    .table-scroll,
    .inventory-table-wrap,
    .responsive-table,
    .table-responsive {
        width: 100%;
        max-width: 100%;
        overflow-x: auto !important;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: rgba(255, 89, 0, 0.55) rgba(255, 89, 0, 0.08);
    }

    .table-wrap::-webkit-scrollbar,
    .table-scroll::-webkit-scrollbar,
    .inventory-table-wrap::-webkit-scrollbar,
    .responsive-table::-webkit-scrollbar,
    .table-responsive::-webkit-scrollbar {
        height: 8px;
    }

    .table-wrap::-webkit-scrollbar-track,
    .table-scroll::-webkit-scrollbar-track,
    .inventory-table-wrap::-webkit-scrollbar-track,
    .responsive-table::-webkit-scrollbar-track,
    .table-responsive::-webkit-scrollbar-track {
        background: rgba(255, 89, 0, 0.08);
        border-radius: 999px;
    }

    .table-wrap::-webkit-scrollbar-thumb,
    .table-scroll::-webkit-scrollbar-thumb,
    .inventory-table-wrap::-webkit-scrollbar-thumb,
    .responsive-table::-webkit-scrollbar-thumb,
    .table-responsive::-webkit-scrollbar-thumb {
        background: rgba(255, 89, 0, 0.55);
        border-radius: 999px;
    }

    .table-wrap table,
    .table-scroll table,
    .inventory-table-wrap table,
    .responsive-table table,
    .table-responsive table {
        width: max-content !important;
        min-width: 100% !important;
        table-layout: auto !important;
    }

    .table-wrap th,
    .table-scroll th,
    .inventory-table-wrap th,
    .responsive-table th,
    .table-responsive th {
        white-space: nowrap;
    }

    .table-wrap td,
    .table-scroll td,
    .inventory-table-wrap td,
    .responsive-table td,
    .table-responsive td {
        max-width: 24rem;
        overflow-wrap: anywhere;
        vertical-align: top;
    }

    .table-header,
    .table-head,
    .hero-card,
    .filter-actions,
    .table-header-actions,
    .modal-actions,
    .action-group {
        min-width: 0;
        flex-wrap: wrap;
    }

    .filter-field,
    .field-group {
        min-width: 0;
    }

    .filter-field input,
    .filter-field select,
    .field-group input,
    .field-group select,
    .field-group textarea,
    input,
    select,
    textarea {
        max-width: 100%;
    }

    .modal-backdrop,
    .activity-modal-backdrop {
        overflow-y: auto;
    }

    .modal-dialog,
    .password-modal,
    .activity-modal {
        max-height: calc(100vh - 2rem);
        overflow-y: auto;
    }

    @media (max-width: 1180px) {
        .summary-grid,
        .stat-grid,
        .metrics-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }

        .filter-form,
        .form-grid,
        .field-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }
    }

    @media (max-width: 860px) {
        .content-area,
        .dashboard-content,
        .activity-page,
        .superadmin-users-page,
        .superadmin-rooms-page,
        .superadmin-inventory-page,
        .superadmin-followup-page,
        .superadmin-reports-page,
        .request-page,
        .password-page,
        .profile-security-page,
        .owner-rooms-page,
        .owner-inventory-page,
        .owner-approvals-page,
        .teacher-requests-page,
        .teacher-history-page,
        .student-class-page,
        .student-history-page,
        .hak-akses-page,
        .history-page,
        .owner-reports-page,
        .owner-approval-page,
        .inbox-page,
        .wali-page,
        .kelas-page,
        .app-shell.sidebar-collapsed .content-area,
        .app-shell.sidebar-collapsed .dashboard-content,
        .app-shell.sidebar-collapsed .activity-page,
        .app-shell.sidebar-collapsed .superadmin-users-page,
        .app-shell.sidebar-collapsed .superadmin-rooms-page,
        .app-shell.sidebar-collapsed .superadmin-inventory-page,
        .app-shell.sidebar-collapsed .superadmin-followup-page,
        .app-shell.sidebar-collapsed .superadmin-reports-page,
        .app-shell.sidebar-collapsed .request-page,
        .app-shell.sidebar-collapsed .password-page,
        .app-shell.sidebar-collapsed .profile-security-page,
        .app-shell.sidebar-collapsed .owner-rooms-page,
        .app-shell.sidebar-collapsed .owner-inventory-page,
        .app-shell.sidebar-collapsed .owner-approvals-page,
        .app-shell.sidebar-collapsed .teacher-requests-page,
        .app-shell.sidebar-collapsed .teacher-history-page,
        .app-shell.sidebar-collapsed .student-class-page,
        .app-shell.sidebar-collapsed .student-history-page,
        .app-shell.sidebar-collapsed .hak-akses-page,
        .app-shell.sidebar-collapsed .history-page,
        .app-shell.sidebar-collapsed .owner-reports-page,
        .app-shell.sidebar-collapsed .owner-approval-page,
        .app-shell.sidebar-collapsed .inbox-page,
        .app-shell.sidebar-collapsed .wali-page,
        .app-shell.sidebar-collapsed .kelas-page {
            width: 100% !important;
            margin-left: 0 !important;
        }

        .modal-backdrop,
        .activity-modal-backdrop,
        .app-shell.sidebar-collapsed .modal-backdrop,
        .app-shell.sidebar-collapsed .activity-modal-backdrop {
            inset: 0 !important;
        }
    }

    @media (max-width: 640px) {
        .hero-card,
        .table-header,
        .table-head,
        .filter-form,
        .filter-left,
        .filter-actions,
        .table-header-actions,
        .modal-actions,
        .action-group {
            align-items: stretch !important;
        }

        .summary-grid,
        .stat-grid,
        .metrics-grid,
        .dashboard-grid,
        .quick-grid,
        .content-grid,
        .filter-form,
        .form-grid,
        .field-grid {
            grid-template-columns: 1fr !important;
        }

        .action-btn,
        .filter-btn,
        .filter-link,
        .submit-btn,
        .ghost-btn,
        .tab-pill {
            width: 100%;
            min-width: 0;
        }

        .table-wrap td,
        .table-scroll td,
        .inventory-table-wrap td,
        .responsive-table td,
        .table-responsive td {
            max-width: 18rem;
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

    @php
        $level = (int) ($user['level'] ?? 0);
        $safeRoute = static function (string $name): string {
            return \Illuminate\Support\Facades\Route::has($name) ? route($name) : '#';
        };
        $menuService = app(\App\Services\MenuAccessService::class);
        $menus = collect($menuService->sidebarMenusForLevel($level))
            ->map(function (array $menu) use ($safeRoute) {
                return [
                    'label' => $menu['label'],
                    'icon' => $menu['icon'],
                    'url' => $safeRoute($menu['route']),
                ];
            })
            ->values()
            ->all();

        if ($menus === []) {
            $menus = [
                ['label' => 'Dashboard', 'icon' => 'bi bi-grid-1x2-fill', 'url' => route('dashboard')],
            ];
        }
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

    <div class="sidebar-account" id="sidebarAccount">
        <button type="button" class="sidebar-user-info" id="accountMenuToggle" aria-label="Buka menu akun" aria-expanded="false" aria-controls="accountMenu">
            <span class="sidebar-avatar">
                {{ strtoupper(substr($user['nama'] ?? 'U', 0, 1)) }}
            </span>
            <span class="sidebar-user-details">
                <span class="sidebar-user-name">{{ $user['nama'] ?? 'Pengguna' }}</span>
                <span class="sidebar-user-role">{{ $dashboard['role_name'] ?? 'Role' }}</span>
            </span>
        </button>

        <div class="account-menu" id="accountMenu">
            <div class="account-menu-head">
                <div class="account-menu-avatar">{{ strtoupper(substr($user['nama'] ?? 'U', 0, 1)) }}</div>
                <div class="sidebar-user-details">
                    <div class="account-menu-name">{{ $user['nama'] ?? 'Pengguna' }}</div>
                    <div class="account-menu-role">{{ $dashboard['role_name'] ?? 'Role' }}</div>
                </div>
            </div>
            <div class="account-menu-main" id="accountMenuMain">
                <a class="account-menu-link" href="{{ route('profile.show') }}">
                    <i class="bi bi-person"></i>
                    <span>Profil</span>
                </a>
                <a class="account-menu-link" href="{{ route('security.show') }}">
                    <i class="bi bi-shield-lock"></i>
                    <span>Keamanan</span>
                </a>
                <form action="{{ route('logout') }}" method="POST" class="account-menu-logout">
                    @csrf
                    <button type="submit" class="account-menu-btn danger">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</aside>

<script>
    (function () {
        function initSidebarToggle() {
            const appShell = document.getElementById('appShell');
            const toggleButton = document.getElementById('sidebarToggle');
            const account = document.getElementById('sidebarAccount');
            const accountToggle = document.getElementById('accountMenuToggle');

            function closeAccountMenu() {
                if (!account || !accountToggle) {
                    return;
                }

                account.classList.remove('open');
                accountToggle.setAttribute('aria-expanded', 'false');
            }

            if (account && accountToggle) {
                accountToggle.addEventListener('click', function () {
                    const isOpen = account.classList.toggle('open');
                    accountToggle.setAttribute('aria-expanded', String(isOpen));
                });

                document.addEventListener('click', function (event) {
                    if (!account.contains(event.target)) {
                        closeAccountMenu();
                    }
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape') {
                        closeAccountMenu();
                    }
                });
            }

            if (!appShell || !toggleButton || window.innerWidth <= 860) {
                return;
            }

            toggleButton.addEventListener('click', function () {
                appShell.classList.toggle('sidebar-collapsed');
                const expanded = !appShell.classList.contains('sidebar-collapsed');
                toggleButton.setAttribute('aria-expanded', String(expanded));
                closeAccountMenu();
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initSidebarToggle, { once: true });
        } else {
            initSidebarToggle();
        }
    })();
</script>
