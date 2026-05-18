<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'nis' => null,
                'nama' => 'kepala sekolah',
                'email' => 'kepalasekolah@infrasph.test',
                'password' => Hash::make('kepala sekolah'),
                'level' => 4,
            ],
            [
                'nis' => null,
                'nama' => 'superadmin',
                'email' => 'superadmin@infrasph.test',
                'password' => Hash::make('superadmin'),
                'level' => 3,
            ],
            [
                'nis' => null,
                'nama' => 'guru',
                'email' => 'guru@infrasph.test',
                'password' => Hash::make('guru'),
                'level' => 2,
            ],
            [
                'nis' => '24161033',
                'nama' => 'siswa',
                'email' => 'siswa@infrasph.test',
                'password' => Hash::make('siswa'),
                'level' => 1,
            ],
        ]);

        $this->call([
            RuanganSeeder::class,
            KategoriBarangSeeder::class,
            BarangSeeder::class,
            HakAksesMenuSeeder::class,
            PenugasanRuanganSeeder::class,
            InventarisRuanganSeeder::class,
            PermintaanSeeder::class,
            DetailPermintaanSeeder::class,
            PersetujuanPermintaanSeeder::class,
            RiwayatInventarisSeeder::class,
        ]);
    }
}
