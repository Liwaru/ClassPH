<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ChatbotAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RoleChatbotOptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_chatbot_options_and_responses_work(): void
    {
        $actors = $this->seedRoleChatbotData();
        $service = app(ChatbotAccessService::class);
        $sessionUser = $this->sessionUserPayload($actors['user']);

        $context = $service->buildContext($sessionUser);
        $this->assertSame(
            ['kelas_saya', 'inventaris_saya', 'pengajuan_saya', 'bantuan_dashboard', 'akun_saya', 'masalah_data'],
            collect($context['assistant']['initial_options'] ?? [])->pluck('id')->all()
        );

        $cases = [
            ['branch' => 'kelas_saya', 'label' => 'Kelas Saya', 'options' => ['kelas_saya_info', 'kelas_saya_barang', 'kelas_saya_akses', '__root']],
            ['branch' => 'inventaris_saya', 'label' => 'Inventaris Kelas', 'options' => ['inventaris_saya_ringkasan', 'inventaris_saya_detail', '__root']],
            ['branch' => 'pengajuan_saya', 'label' => 'Pengajuan', 'options' => ['pengajuan_saya_status', 'pengajuan_saya_ajukan', 'pengajuan_saya_riwayat', '__root']],
            ['branch' => 'bantuan_dashboard', 'label' => 'Penggunaan Dashboard', 'options' => ['dashboard_menu', 'dashboard_cara', '__root']],
            ['branch' => 'akun_saya', 'label' => 'Akun', 'options' => ['akun_saya_lupa_password', 'akun_saya_lainnya', '__root']],
            ['branch' => 'masalah_data', 'label' => 'Masalah Data', 'options' => ['masalah_data_jumlah', 'masalah_data_tidak_muncul', 'masalah_data_salah', 'masalah_data_barang_tidak_tercatat', '__root']],
        ];

        foreach ($cases as $case) {
            $branch = $service->respond($sessionUser, $case['label'], [], $case['branch']);
            $this->assertSame('guided_branch', $branch['intent']);
            $this->assertSame($case['options'], collect($branch['options'] ?? [])->pluck('id')->all());
        }

        $leafAssertions = [
            ['id' => 'kelas_saya_info', 'label' => 'Data kelas saya', 'contains' => 'ruangan'],
            ['id' => 'kelas_saya_barang', 'label' => 'Lihat barang di kelas saya', 'contains' => 'barang'],
            ['id' => 'kelas_saya_akses', 'label' => 'Apa saja akses saya', 'contains' => 'akses terbatas'],
            ['id' => 'inventaris_saya_ringkasan', 'label' => 'Ringkasan inventaris', 'contains' => 'inventaris'],
            ['id' => 'inventaris_saya_detail', 'label' => 'Semua barang di kelas saya', 'contains' => 'kursi'],
            ['id' => 'pengajuan_saya_status', 'label' => 'Status pengajuan saya', 'contains' => 'pengajuan aktif'],
            ['id' => 'pengajuan_saya_ajukan', 'label' => 'Cara ajukan barang', 'contains' => 'Ajukan Permintaan'],
            ['id' => 'pengajuan_saya_riwayat', 'label' => 'Cara lihat riwayat pengajuan', 'contains' => 'Riwayat Pengajuan'],
            ['id' => 'dashboard_menu', 'label' => 'Menu yang tersedia', 'contains' => 'Kelas Saya'],
            ['id' => 'dashboard_cara', 'label' => 'Cara memakai dashboard', 'contains' => 'bantu'],
            ['id' => 'akun_saya_lupa_password', 'label' => 'Lupa password', 'contains' => 'wali kelas'],
            ['id' => 'masalah_data_jumlah', 'label' => 'Jumlah barang tidak sesuai', 'contains' => 'jumlah barang tidak sesuai'],
            ['id' => 'masalah_data_tidak_muncul', 'label' => 'Data tidak muncul', 'contains' => 'data tidak muncul'],
            ['id' => 'masalah_data_salah', 'label' => 'Data salah', 'contains' => 'data yang salah'],
            ['id' => 'masalah_data_barang_tidak_tercatat', 'label' => 'Barang tidak tercatat', 'contains' => 'belum tercatat'],
        ];

        foreach ($leafAssertions as $leaf) {
            $response = $service->respond($sessionUser, $leaf['label'], [], $leaf['id']);
            $this->assertSame('guided_leaf', $response['intent']);
            $this->assertStringContainsStringIgnoringCase($leaf['contains'], $response['message']);
        }

        $pending = $service->respond($sessionUser, 'Lainnya', [], 'akun_saya_lainnya');
        $this->assertSame('guided_leaf', $pending['intent']);
        $this->assertSame('issue_report', $pending['pending_input']['mode'] ?? null);
        $this->assertStringContainsStringIgnoringCase('tulis masalah akunmu', $pending['message']);
    }

    public function test_admin_chatbot_options_and_responses_work(): void
    {
        $actors = $this->seedRoleChatbotData();
        $service = app(ChatbotAccessService::class);
        $sessionUser = $this->sessionUserPayload($actors['admin']);

        $context = $service->buildContext($sessionUser);
        $this->assertSame(
            ['kelas_binaan', 'inventaris_kelas', 'pengajuan_kelas', 'verifikasi', 'akses_akun', 'lainnya'],
            collect($context['assistant']['initial_options'] ?? [])->pluck('id')->all()
        );

        $cases = [
            ['branch' => 'kelas_binaan', 'label' => 'Kelas Binaan', 'options' => ['kelas_binaan_info', 'kelas_binaan_barang', 'kelas_binaan_akses', '__root']],
            ['branch' => 'inventaris_kelas', 'label' => 'Inventaris Kelas', 'options' => ['inventaris_kelas_ringkasan', 'inventaris_kelas_detail', '__root']],
            ['branch' => 'pengajuan_kelas', 'label' => 'Pengajuan Masuk', 'options' => ['pengajuan_kelas_status', 'pengajuan_kelas_riwayat', '__root']],
            ['branch' => 'verifikasi', 'label' => 'Verifikasi', 'options' => ['verifikasi_cara', 'verifikasi_batas', '__root']],
            ['branch' => 'akses_akun', 'label' => 'Batas Akses', 'options' => ['akses_akun_info', 'akses_akun_larangan', '__root']],
            ['branch' => 'lainnya', 'label' => 'Lainnya', 'options' => ['lainnya_akses', 'lainnya_rules', '__root']],
        ];

        foreach ($cases as $case) {
            $branch = $service->respond($sessionUser, $case['label'], [], $case['branch']);
            $this->assertSame('guided_branch', $branch['intent']);
            $this->assertSame($case['options'], collect($branch['options'] ?? [])->pluck('id')->all());
        }

        $leafAssertions = [
            ['id' => 'kelas_binaan_info', 'label' => 'Data kelas binaan saya', 'contains' => 'kelas binaan'],
            ['id' => 'kelas_binaan_barang', 'label' => 'Semua barang di kelas binaan', 'contains' => 'kursi'],
            ['id' => 'kelas_binaan_akses', 'label' => 'Batas akses saya', 'contains' => 'wali kelas'],
            ['id' => 'inventaris_kelas_ringkasan', 'label' => 'Ringkasan inventaris kelas', 'contains' => 'inventaris'],
            ['id' => 'inventaris_kelas_detail', 'label' => 'Detail barang kelas', 'contains' => 'kursi'],
            ['id' => 'pengajuan_kelas_status', 'label' => 'Ringkasan pengajuan masuk', 'contains' => 'pengajuan'],
            ['id' => 'pengajuan_kelas_riwayat', 'label' => 'Cara lihat riwayat verifikasi', 'contains' => 'Riwayat Verifikasi'],
            ['id' => 'verifikasi_cara', 'label' => 'Cara verifikasi pengajuan', 'contains' => 'Pengajuan Masuk'],
            ['id' => 'verifikasi_batas', 'label' => 'Yang bisa saya verifikasi', 'contains' => 'tidak dapat mengakses kelas lain'],
            ['id' => 'akses_akun_info', 'label' => 'Batas akses saya', 'contains' => 'wali kelas'],
            ['id' => 'akses_akun_larangan', 'label' => 'Yang tidak bisa saya buka', 'contains' => 'tidak dapat'],
            ['id' => 'lainnya_akses', 'label' => 'Jika akses belum sesuai', 'contains' => 'pengelola sistem'],
            ['id' => 'lainnya_rules', 'label' => 'Aturan chatbot ini', 'contains' => 'bertingkat'],
        ];

        foreach ($leafAssertions as $leaf) {
            $response = $service->respond($sessionUser, $leaf['label'], [], $leaf['id']);
            $this->assertSame('guided_leaf', $response['intent']);
            $this->assertStringContainsStringIgnoringCase($leaf['contains'], $response['message']);
        }
    }

    public function test_owner_chatbot_options_and_responses_work(): void
    {
        $actors = $this->seedRoleChatbotData();
        $service = app(ChatbotAccessService::class);
        $sessionUser = $this->sessionUserPayload($actors['owner']);

        $context = $service->buildContext($sessionUser);
        $this->assertSame(
            ['ruangan_owner', 'inventaris_owner', 'persetujuan_owner', 'laporan_owner', 'akses_owner', 'lainnya'],
            collect($context['assistant']['initial_options'] ?? [])->pluck('id')->all()
        );

        $cases = [
            ['branch' => 'ruangan_owner', 'label' => 'Semua Ruangan', 'options' => ['ruangan_owner_ringkasan', '__root']],
            ['branch' => 'inventaris_owner', 'label' => 'Inventaris Sekolah', 'options' => ['inventaris_owner_ringkasan', '__root']],
            ['branch' => 'persetujuan_owner', 'label' => 'Persetujuan Pengajuan', 'options' => ['persetujuan_owner_ringkasan', 'persetujuan_owner_batas', '__root']],
            ['branch' => 'laporan_owner', 'label' => 'Laporan', 'options' => ['laporan_owner_ringkasan', '__root']],
            ['branch' => 'akses_owner', 'label' => 'Batas Akses', 'options' => ['akses_owner_info', 'akses_owner_larangan', '__root']],
            ['branch' => 'lainnya', 'label' => 'Lainnya', 'options' => ['lainnya_rules', '__root']],
        ];

        foreach ($cases as $case) {
            $branch = $service->respond($sessionUser, $case['label'], [], $case['branch']);
            $this->assertSame('guided_branch', $branch['intent']);
            $this->assertSame($case['options'], collect($branch['options'] ?? [])->pluck('id')->all());
        }

        $leafAssertions = [
            ['id' => 'ruangan_owner_ringkasan', 'label' => 'Ringkasan semua ruangan', 'contains' => 'ruangan'],
            ['id' => 'inventaris_owner_ringkasan', 'label' => 'Ringkasan inventaris sekolah', 'contains' => 'inventaris'],
            ['id' => 'persetujuan_owner_ringkasan', 'label' => 'Ringkasan pengajuan', 'contains' => 'pengajuan'],
            ['id' => 'persetujuan_owner_batas', 'label' => 'Batas akses owner', 'contains' => 'tidak dapat menambah'],
            ['id' => 'laporan_owner_ringkasan', 'label' => 'Ringkasan laporan sekolah', 'contains' => 'laporan'],
            ['id' => 'akses_owner_info', 'label' => 'Yang bisa saya lihat', 'contains' => 'ringkasan seluruh data sekolah'],
            ['id' => 'akses_owner_larangan', 'label' => 'Yang tidak bisa saya ubah', 'contains' => 'tidak dapat'],
            ['id' => 'lainnya_rules', 'label' => 'Aturan chatbot ini', 'contains' => 'bertingkat'],
        ];

        foreach ($leafAssertions as $leaf) {
            $response = $service->respond($sessionUser, $leaf['label'], [], $leaf['id']);
            $this->assertSame('guided_leaf', $response['intent']);
            $this->assertStringContainsStringIgnoringCase($leaf['contains'], $response['message']);
        }
    }

    private function seedRoleChatbotData(): array
    {
        $user = User::create([
            'nis' => '1001',
            'nama' => 'Ketua Kelas',
            'email' => 'ketua@example.test',
            'otp_enabled' => false,
            'password' => 'secret123',
            'level' => 1,
            'kelas' => 'RPL XI',
        ]);

        $admin = User::create([
            'nis' => '2001',
            'nama' => 'Wali Kelas',
            'email' => 'wali@example.test',
            'otp_enabled' => false,
            'password' => 'secret123',
            'level' => 2,
            'kelas' => 'RPL XI',
        ]);

        $superadmin = User::create([
            'nis' => '3001',
            'nama' => 'Superadmin',
            'email' => 'superadmin@example.test',
            'otp_enabled' => false,
            'password' => 'secret123',
            'level' => 3,
            'kelas' => null,
        ]);

        $owner = User::create([
            'nis' => '4001',
            'nama' => 'Owner',
            'email' => 'owner@example.test',
            'otp_enabled' => false,
            'password' => 'secret123',
            'level' => 4,
            'kelas' => null,
        ]);

        $roomId = DB::table('ruangan')->insertGetId([
            'kode_ruangan' => 'RPL-XI',
            'nama_ruangan' => 'RPL XI',
            'jenis_ruangan' => 'kelas',
            'unit' => 'SMK',
            'lokasi' => 'Lantai 2',
            'keterangan' => 'Kelas utama',
            'status' => 'aktif',
        ]);

        DB::table('penugasan_ruangan')->insert([
            [
                'id_user' => $user->id_user,
                'id_ruangan' => $roomId,
                'peran_ruangan' => 'ketua_kelas',
                'tanggal_mulai' => '2026-01-01',
                'tanggal_selesai' => null,
                'status' => 'aktif',
            ],
            [
                'id_user' => $admin->id_user,
                'id_ruangan' => $roomId,
                'peran_ruangan' => 'wali_kelas',
                'tanggal_mulai' => '2026-01-01',
                'tanggal_selesai' => null,
                'status' => 'aktif',
            ],
        ]);

        $categoryId = DB::table('kategori_barang')->insertGetId([
            'nama_kategori' => 'Furniture',
            'keterangan' => 'Perabot kelas',
            'status' => 'aktif',
        ]);

        $itemId = DB::table('barang')->insertGetId([
            'id_kategori_barang' => $categoryId,
            'nama_barang' => 'kursi',
            'satuan' => 'unit',
            'keterangan' => 'Kursi siswa',
            'status' => 'aktif',
        ]);

        DB::table('inventaris_ruangan')->insert([
            'id_ruangan' => $roomId,
            'id_barang' => $itemId,
            'jumlah_baik' => 12,
            'jumlah_rusak' => 2,
            'keterangan' => 'Inventaris kelas',
            'id_user_pengubah' => $superadmin->id_user,
        ]);

        $requestId = DB::table('permintaan')->insertGetId([
            'kode_permintaan' => 'PMT-ROLE-001',
            'id_ruangan' => $roomId,
            'id_user_peminta' => $user->id_user,
            'jenis_permintaan' => 'penambahan',
            'status_permintaan' => 'disetujui_owner',
            'catatan_peminta' => 'Butuh kursi tambahan',
            'tanggal_permintaan' => now()->toDateString(),
        ]);

        DB::table('detail_permintaan')->insert([
            'id_permintaan' => $requestId,
            'id_barang' => $itemId,
            'jumlah_diminta' => 3,
            'jumlah_disetujui' => 3,
            'jumlah_diberikan' => 0,
            'keterangan' => 'Tambahan kursi',
        ]);

        return compact('user', 'admin', 'superadmin', 'owner');
    }

    private function sessionUserPayload(User $user): array
    {
        return [
            'id_user' => $user->id_user,
            'nis' => $user->nis,
            'nama' => $user->nama,
            'email' => $user->email,
            'otp_enabled' => false,
            'level' => $user->level,
            'role_label' => match ((int) $user->level) {
                1 => 'Ketua Kelas',
                2 => 'Wali Kelas',
                3 => 'Superadmin',
                4 => 'Kepala Sekolah',
            },
        ];
    }
}
