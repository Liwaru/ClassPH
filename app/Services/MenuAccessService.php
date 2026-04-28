<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MenuAccessService
{
    private const LEVELS = [
        1 => 'User',
        2 => 'Admin',
        3 => 'Superadmin',
        4 => 'Owner',
    ];

    private const MENU_DEFINITIONS = [
        'kelas_saya_user' => [
            'label' => 'Kelas Saya (User)',
            'icon' => 'bi bi-door-open-fill',
            'route' => 'class.inventory',
            'default_levels' => [1],
        ],
        'ajukan_permintaan' => [
            'label' => 'Ajukan Permintaan',
            'icon' => 'bi bi-send-plus-fill',
            'route' => 'requests.create',
            'default_levels' => [1],
        ],
        'riwayat_pengajuan_user' => [
            'label' => 'Riwayat Pengajuan (User)',
            'icon' => 'bi bi-clock-history',
            'route' => 'requests.history',
            'default_levels' => [1],
        ],
        'kelas_saya_admin' => [
            'label' => 'Kelas Saya (Admin)',
            'icon' => 'bi bi-building',
            'route' => 'admin.class.inventory',
            'default_levels' => [2],
        ],
        'pengajuan_kelas' => [
            'label' => 'Pengajuan Kelas',
            'icon' => 'bi bi-inbox-fill',
            'route' => 'admin.requests.inbox',
            'default_levels' => [2],
        ],
        'riwayat_pengajuan_admin' => [
            'label' => 'Riwayat Pengajuan (Admin)',
            'icon' => 'bi bi-patch-check-fill',
            'route' => 'admin.requests.history',
            'default_levels' => [2],
        ],
        'data_user' => [
            'label' => 'Data User',
            'icon' => 'bi bi-people-fill',
            'route' => 'superadmin.users',
            'default_levels' => [3],
        ],
        'data_ruangan' => [
            'label' => 'Data Ruangan',
            'icon' => 'bi bi-building-fill-gear',
            'route' => 'superadmin.rooms',
            'default_levels' => [3],
        ],
        'data_inventaris' => [
            'label' => 'Data Inventaris',
            'icon' => 'bi bi-grid-fill',
            'route' => 'superadmin.items',
            'default_levels' => [3],
        ],
        'chatbot_superadmin' => [
            'label' => 'Chatbot',
            'icon' => 'bi bi-chat-dots-fill',
            'route' => 'superadmin.chatbot',
            'default_levels' => [3],
        ],
        'tindak_lanjut_pengajuan' => [
            'label' => 'Tindak Lanjut Pengajuan',
            'icon' => 'bi bi-list-check',
            'route' => 'superadmin.requests.realization',
            'default_levels' => [3],
        ],
        'hak_akses' => [
            'label' => 'Hak Akses',
            'icon' => 'bi bi-shield-lock-fill',
            'route' => 'hak_akses.index',
            'default_levels' => [3],
        ],
        'laporan_superadmin' => [
            'label' => 'Laporan (Superadmin)',
            'icon' => 'bi bi-bar-chart-line-fill',
            'route' => 'superadmin.reports',
            'default_levels' => [3],
        ],
        'catatan_aktivitas' => [
            'label' => 'Catatan Aktivitas',
            'icon' => 'bi bi-activity',
            'route' => 'activity.logs',
            'default_levels' => [3, 4],
        ],
        'semua_ruangan' => [
            'label' => 'Semua Ruangan',
            'icon' => 'bi bi-buildings-fill',
            'route' => 'owner.rooms',
            'default_levels' => [4],
        ],
        'inventaris_sekolah' => [
            'label' => 'Inventaris Sekolah',
            'icon' => 'bi bi-boxes',
            'route' => 'owner.inventories',
            'default_levels' => [4],
        ],
        'persetujuan_pengajuan' => [
            'label' => 'Persetujuan Pengajuan',
            'icon' => 'bi bi-clipboard2-check-fill',
            'route' => 'owner.requests.approval',
            'default_levels' => [4],
        ],
        'laporan_owner' => [
            'label' => 'Laporan (Owner)',
            'icon' => 'bi bi-bar-chart-fill',
            'route' => 'owner.reports',
            'default_levels' => [4],
        ],
    ];

    public function levels(): array
    {
        return self::LEVELS;
    }

    public function menuDefinitions(): array
    {
        return self::MENU_DEFINITIONS;
    }

    public function menuKeys(): array
    {
        return array_keys(self::MENU_DEFINITIONS);
    }

    public function menuLabels(): array
    {
        $labels = [];

        foreach (self::MENU_DEFINITIONS as $key => $definition) {
            $labels[$key] = $definition['label'];
        }

        return $labels;
    }

    public function defaultPermissions(): array
    {
        $matrix = [];

        foreach (self::LEVELS as $levelId => $levelName) {
            $matrix[$levelId] = [];

            foreach (self::MENU_DEFINITIONS as $menuKey => $definition) {
                $matrix[$levelId][$menuKey] = in_array($levelId, $definition['default_levels'], true);
            }
        }

        $matrix[3]['hak_akses'] = true;
        $matrix[3]['chatbot_superadmin'] = true;

        return $matrix;
    }

    public function permissionMatrix(): array
    {
        $defaultMatrix = $this->defaultPermissions();

        if (! Schema::hasTable('hak_akses_menu')) {
            return $defaultMatrix;
        }

        $rows = DB::table('hak_akses_menu')->get(['level', 'menu_key']);

        if ($rows->isEmpty()) {
            return $defaultMatrix;
        }

        $matrix = [];

        foreach (self::LEVELS as $levelId => $levelName) {
            $matrix[$levelId] = [];

            foreach (self::MENU_DEFINITIONS as $menuKey => $definition) {
                $matrix[$levelId][$menuKey] = false;
            }
        }

        foreach ($rows as $row) {
            $level = (int) $row->level;
            $menuKey = (string) $row->menu_key;

            if (isset($matrix[$level][$menuKey])) {
                $matrix[$level][$menuKey] = true;
            }
        }

        $matrix[3]['hak_akses'] = true;
        $matrix[3]['chatbot_superadmin'] = true;

        return $matrix;
    }

    public function sidebarMenusForLevel(int $level): array
    {
        $permissions = $this->permissionMatrix();
        $menus = [];

        foreach (self::MENU_DEFINITIONS as $menuKey => $definition) {
            if (($permissions[$level][$menuKey] ?? false) !== true) {
                continue;
            }

            $menus[] = [
                'key' => $menuKey,
                'label' => $this->sidebarLabel($menuKey),
                'icon' => $definition['icon'],
                'route' => $definition['route'],
            ];
        }

        if (in_array($level, [3, 4], true) && ! collect($menus)->contains('key', 'catatan_aktivitas')) {
            $definition = self::MENU_DEFINITIONS['catatan_aktivitas'];
            $menus[] = [
                'key' => 'catatan_aktivitas',
                'label' => $this->sidebarLabel('catatan_aktivitas'),
                'icon' => $definition['icon'],
                'route' => $definition['route'],
            ];
        }

        return $menus;
    }

    public function userCanAccessMenu(int $level, string $menuKey): bool
    {
        if ($menuKey === 'profil_keamanan') {
            return true;
        }

        if ($level === 3 && $menuKey === 'hak_akses') {
            return true;
        }

        if ($level === 3 && $menuKey === 'chatbot_superadmin') {
            return true;
        }

        if ($menuKey === 'catatan_aktivitas' && in_array($level, [3, 4], true)) {
            return true;
        }

        return (bool) ($this->permissionMatrix()[$level][$menuKey] ?? false);
    }

    public function savePermissions(array $submittedPermissions): void
    {
        if (! Schema::hasTable('hak_akses_menu')) {
            return;
        }

        $rows = [];

        foreach (self::LEVELS as $levelId => $levelName) {
            $selectedMenus = collect($submittedPermissions[$levelId] ?? [])
                ->map(fn ($value) => (string) $value)
                ->filter(fn ($menuKey) => array_key_exists($menuKey, self::MENU_DEFINITIONS))
                ->unique()
                ->values()
                ->all();

            if ($levelId === 3 && ! in_array('hak_akses', $selectedMenus, true)) {
                $selectedMenus[] = 'hak_akses';
            }

            if ($levelId === 3 && ! in_array('chatbot_superadmin', $selectedMenus, true)) {
                $selectedMenus[] = 'chatbot_superadmin';
            }

            foreach ($selectedMenus as $menuKey) {
                $rows[] = [
                    'level' => $levelId,
                    'menu_key' => $menuKey,
                ];
            }
        }

        DB::transaction(function () use ($rows) {
            DB::table('hak_akses_menu')->delete();

            if ($rows !== []) {
                DB::table('hak_akses_menu')->insert($rows);
            }
        });
    }

    private function sidebarLabel(string $menuKey): string
    {
        return match ($menuKey) {
            'kelas_saya_user', 'kelas_saya_admin' => 'Kelas Saya',
            'riwayat_pengajuan_user', 'riwayat_pengajuan_admin' => 'Riwayat Pengajuan',
            'laporan_superadmin', 'laporan_owner' => 'Laporan',
            default => self::MENU_DEFINITIONS[$menuKey]['label'],
        };
    }
}
