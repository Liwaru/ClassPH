<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Hak Akses | InfraSPH</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        :root {
            --sidebar-orange-top: #ff7a21;
            --sidebar-orange-bottom: #ff5900;
            --sidebar-orange-deep: #e14f00;
            --sidebar-orange-soft: #fff1e8;
            --sidebar-orange-border: #ffd2bb;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            background: linear-gradient(145deg, #e9eef3 0%, #dce2ea 100%);
            color: #1f2937;
        }

        .hak-akses-page {
            margin-left: 320px;
            width: calc(100% - 320px);
            min-height: 100vh;
            padding: 2rem 1.5rem;
            transition: margin-left 0.28s ease, width 0.28s ease, padding 0.28s ease;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        .app-shell.sidebar-collapsed .hak-akses-page {
            margin-left: 88px;
            width: calc(100% - 88px);
        }

        #hak-akses-content {
            width: 100%;
            max-width: min(900px, calc(100vw - 320px - 3rem));
        }

        .access-card {
            width: 100%;
            background: #ffffff;
            border-radius: 1rem;
            box-shadow: 0 10px 25px -8px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            border: 1px solid rgba(255, 121, 33, 0.18);
        }

        .access-header {
            background: linear-gradient(180deg, var(--sidebar-orange-top), var(--sidebar-orange-bottom));
            color: white;
            padding: 0.8rem 1.5rem;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .access-body {
            padding: 1rem 1.5rem 1.5rem;
            background: #ffffff;
        }

        .helper-copy {
            margin: 0 0 1rem;
            color: #64748b;
            line-height: 1.6;
        }

        .flash-success,
        .flash-error {
            margin-bottom: 1rem;
            padding: 0.85rem 1rem;
            border-radius: 0.85rem;
            font-size: 0.95rem;
            font-weight: 600;
        }

        .flash-success {
            background: #fff7f1;
            color: var(--sidebar-orange-deep);
            border: 1px solid var(--sidebar-orange-border);
        }

        .flash-error {
            background: #fff1eb;
            color: #c2410c;
            border: 1px solid #fec9ae;
        }

        .access-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 400px;
        }

        .access-table th,
        .access-table td {
            padding: 0.6rem 1rem;
            text-align: center;
            border-bottom: 1px solid #edf2f7;
            vertical-align: middle;
        }

        .access-table th:first-child,
        .access-table td:first-child {
            text-align: left;
            font-weight: 600;
            background-color: #fff7f1 !important;
            min-width: 250px;
            width: 40%;
        }

        .access-table th {
            background: linear-gradient(180deg, var(--sidebar-orange-top), var(--sidebar-orange-bottom)) !important;
            color: white !important;
            font-weight: 600;
        }

        .access-table th:first-child {
            background: linear-gradient(180deg, var(--sidebar-orange-top), var(--sidebar-orange-bottom)) !important;
            color: #ffffff !important;
        }

        .access-table tr:hover td {
            background-color: #fff3eb !important;
        }

        .checkbox-style {
            transform: scale(1.2);
            cursor: pointer;
            accent-color: var(--sidebar-orange-bottom);
        }

        .checkbox-style:disabled {
            cursor: not-allowed;
            opacity: 0.7;
        }

        .btn-save {
            margin-top: 1rem;
            background: linear-gradient(180deg, var(--sidebar-orange-top), var(--sidebar-orange-bottom)) !important;
            color: white !important;
            padding: 0.7rem 1.5rem;
            border: none !important;
            border-radius: 2rem;
            font-weight: 700;
            float: right;
            cursor: pointer;
            box-shadow: 0 14px 24px -18px rgba(225, 79, 0, 0.85);
        }

        .btn-save:hover {
            background: linear-gradient(180deg, #ff8b3c, #ff6a17) !important;
        }

        @media (max-width: 860px) {
            .hak-akses-page,
            .app-shell.sidebar-collapsed .hak-akses-page {
                width: 100%;
                margin-left: 0;
                padding: 5.3rem 1rem 1.5rem;
            }

            #hak-akses-content {
                max-width: 100%;
            }
        }

        @media (max-width: 768px) {
            .access-table th,
            .access-table td {
                padding: 0.4rem 0.5rem;
                font-size: 0.8rem;
            }

            .btn-save {
                float: none;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="app-shell" id="appShell">
        @include('header')

        <main class="hak-akses-page">
            <div id="hak-akses-content">
                <div class="access-card">
                    <div class="access-header">
                        <i class="bi bi-shield-lock"></i> Pengaturan Hak Akses Menu
                    </div>
                    <div class="access-body">
                        <p class="helper-copy">Centang menu yang ingin dimunculkan untuk tiap level. Saat centang dilepas, menu akan hilang dari sidebar dan akses ke halaman utamanya ikut ditutup.</p>

                        @if (session('success'))
                            <div class="flash-success">{{ session('success') }}</div>
                        @endif

                        @if (session('error'))
                            <div class="flash-error">{{ session('error') }}</div>
                        @endif

                        <form method="POST" action="{{ route('hak_akses.update') }}">
                            @csrf
                            <div style="overflow-x: auto;">
                                <table class="access-table">
                                    <thead>
                                        <tr>
                                            <th>Menu</th>
                                            @foreach ($levels as $levelId => $levelName)
                                                <th>{{ $levelName }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($menus as $menu)
                                            <tr>
                                                <td>{{ $menuLabels[$menu] ?? ucwords(str_replace('_', ' ', $menu)) }}</td>
                                                @foreach ($levels as $levelId => $levelName)
                                                    @php
                                                        $isHakAksesSuperadmin = $menu === 'hak_akses' && (int) $levelId === 3;
                                                    @endphp
                                                    <td>
                                                        <input
                                                            type="checkbox"
                                                            class="checkbox-style"
                                                            name="permissions[{{ $levelId }}][]"
                                                            value="{{ $menu }}"
                                                            {{ isset($permissions[$levelId][$menu]) && $permissions[$levelId][$menu] ? 'checked' : '' }}
                                                            @disabled($isHakAksesSuperadmin)
                                                        >
                                                        @if ($isHakAksesSuperadmin)
                                                            <input type="hidden" name="permissions[3][]" value="hak_akses">
                                                        @endif
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <button type="submit" class="btn-save">
                                Simpan Perubahan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
