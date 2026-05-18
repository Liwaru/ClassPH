<?php

namespace Database\Seeders;

use App\Services\MenuAccessService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HakAksesMenuSeeder extends Seeder
{
    /**
     * Seed the application's default menu permissions.
     */
    public function run(): void
    {
        $permissions = app(MenuAccessService::class)->defaultPermissions();
        $rows = [];

        foreach ($permissions as $level => $menus) {
            foreach ($menus as $menuKey => $allowed) {
                if (! $allowed) {
                    continue;
                }

                $rows[] = [
                    'level' => $level,
                    'menu_key' => $menuKey,
                ];
            }
        }

        DB::table('hak_akses_menu')->insert($rows);
    }
}
