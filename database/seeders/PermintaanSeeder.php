<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermintaanSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $ruangan7A = DB::table('ruangan')->where('kode_ruangan', 'KLS-7A')->value('id_ruangan');
        $siswa = DB::table('users')->where('nama', 'siswa')->value('id_user');

        DB::table('permintaan')->insert([
            [
                'kode_permintaan' => 'PMT-20260414-001',
                'id_ruangan' => $ruangan7A,
                'id_user_peminta' => $siswa,
                'jenis_permintaan' => 'penambahan',
                'status_permintaan' => 'selesai',
                'catatan_peminta' => 'Meminta tambahan meja dan kursi untuk kelas 7A.',
                'tanggal_permintaan' => '2026-04-14',
            ],
            [
                'kode_permintaan' => 'PMT-20260414-002',
                'id_ruangan' => $ruangan7A,
                'id_user_peminta' => $siswa,
                'jenis_permintaan' => 'penambahan',
                'status_permintaan' => 'disetujui_admin',
                'catatan_peminta' => 'Meminta tambahan proyektor untuk kelas 7A.',
                'tanggal_permintaan' => '2026-04-14',
            ],
            [
                'kode_permintaan' => 'PMT-20260414-003',
                'id_ruangan' => $ruangan7A,
                'id_user_peminta' => $siswa,
                'jenis_permintaan' => 'penambahan',
                'status_permintaan' => 'ditolak_admin',
                'catatan_peminta' => 'Meminta tambahan komputer untuk kelas 7A.',
                'tanggal_permintaan' => '2026-04-14',
            ],
        ]);
    }
}
