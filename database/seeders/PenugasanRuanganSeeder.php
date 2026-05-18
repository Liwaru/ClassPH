<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PenugasanRuanganSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $ruangan7A = DB::table('ruangan')->where('kode_ruangan', 'KLS-7A')->value('id_ruangan');
        $guru = DB::table('users')->where('nama', 'guru')->value('id_user');
        $siswa = DB::table('users')->where('nama', 'siswa')->value('id_user');

        DB::table('penugasan_ruangan')->insert([
            [
                'id_user' => $guru,
                'id_ruangan' => $ruangan7A,
                'peran_ruangan' => 'wali_kelas',
                'tanggal_mulai' => '2026-04-14',
                'tanggal_selesai' => null,
                'status' => 'aktif',
            ],
            [
                'id_user' => $siswa,
                'id_ruangan' => $ruangan7A,
                'peran_ruangan' => 'ketua_kelas',
                'tanggal_mulai' => '2026-04-14',
                'tanggal_selesai' => null,
                'status' => 'aktif',
            ],
        ]);
    }
}
