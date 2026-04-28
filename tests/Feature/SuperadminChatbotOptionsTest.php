<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ChatbotAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SuperadminChatbotOptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_chatbot_root_options_are_available(): void
    {
        $user = $this->seedSuperadminChatbotData();

        $context = app(ChatbotAccessService::class)->buildContext($this->sessionUserPayload($user));
        $optionIds = collect($context['assistant']['initial_options'] ?? [])->pluck('id')->all();

        $this->assertSame(
            ['data_user', 'data_ruangan', 'data_barang', 'inventaris_global', 'pengajuan_global', 'sistem'],
            $optionIds
        );
    }

    public function test_superadmin_chatbot_branch_and_leaf_options_work(): void
    {
        $user = $this->seedSuperadminChatbotData();
        $service = app(ChatbotAccessService::class);
        $sessionUser = $this->sessionUserPayload($user);

        $cases = [
            [
                'branch_id' => 'data_user',
                'branch_label' => 'Data User',
                'branch_option_ids' => ['data_user_ringkasan', 'data_user_aksi', '__root'],
                'leaf_id' => 'data_user_ringkasan',
                'leaf_label' => 'Ringkasan data user',
                'leaf_text_contains' => 'akun',
            ],
            [
                'branch_id' => 'data_ruangan',
                'branch_label' => 'Data Ruangan',
                'branch_option_ids' => ['data_ruangan_ringkasan', '__root'],
                'leaf_id' => 'data_ruangan_ringkasan',
                'leaf_label' => 'Ringkasan ruangan',
                'leaf_text_contains' => 'ruangan',
            ],
            [
                'branch_id' => 'data_barang',
                'branch_label' => 'Data Barang',
                'branch_option_ids' => ['data_barang_ringkasan', '__root'],
                'leaf_id' => 'data_barang_ringkasan',
                'leaf_label' => 'Ringkasan data barang',
                'leaf_text_contains' => 'barang',
            ],
            [
                'branch_id' => 'inventaris_global',
                'branch_label' => 'Inventaris',
                'branch_option_ids' => ['inventaris_global_ringkasan', '__root'],
                'leaf_id' => 'inventaris_global_ringkasan',
                'leaf_label' => 'Ringkasan inventaris sekolah',
                'leaf_text_contains' => 'inventaris',
            ],
            [
                'branch_id' => 'pengajuan_global',
                'branch_label' => 'Pengajuan',
                'branch_option_ids' => ['pengajuan_global_ringkasan', '__root'],
                'leaf_id' => 'pengajuan_global_ringkasan',
                'leaf_label' => 'Ringkasan pengajuan',
                'leaf_text_contains' => 'pengajuan',
            ],
            [
                'branch_id' => 'sistem',
                'branch_label' => 'Sistem',
                'branch_option_ids' => ['sistem_hak', 'sistem_rules', '__root'],
                'leaf_id' => 'sistem_hak',
                'leaf_label' => 'Hak akses pengelola sistem',
                'leaf_text_contains' => 'pengelola sistem',
            ],
            [
                'branch_id' => 'sistem',
                'branch_label' => 'Sistem',
                'branch_option_ids' => ['sistem_hak', 'sistem_rules', '__root'],
                'leaf_id' => 'sistem_rules',
                'leaf_label' => 'Aturan chatbot ini',
                'leaf_text_contains' => 'bertingkat',
            ],
            [
                'branch_id' => 'data_user',
                'branch_label' => 'Data User',
                'branch_option_ids' => ['data_user_ringkasan', 'data_user_aksi', '__root'],
                'leaf_id' => 'data_user_aksi',
                'leaf_label' => 'Perintah kelola user',
                'leaf_text_contains' => 'tambah user',
            ],
        ];

        foreach ($cases as $case) {
            $branch = $service->respond($sessionUser, $case['branch_label'], [], $case['branch_id']);
            $branchOptionIds = collect($branch['options'] ?? [])->pluck('id')->all();

            $this->assertSame('guided_branch', $branch['intent']);
            $this->assertTrue($branch['allowed']);
            $this->assertNotEmpty($branch['message']);
            $this->assertSame($case['branch_option_ids'], $branchOptionIds, 'Branch options mismatch for '.$case['branch_id']);

            $leaf = $service->respond($sessionUser, $case['leaf_label'], [], $case['leaf_id']);
            $leafOptionIds = collect($leaf['options'] ?? [])->pluck('id')->all();

            $this->assertSame('guided_leaf', $leaf['intent']);
            $this->assertTrue($leaf['allowed']);
            $this->assertStringContainsStringIgnoringCase($case['leaf_text_contains'], $leaf['message']);
            $this->assertContains('__root', $leafOptionIds, 'Leaf should contain back-to-root option for '.$case['leaf_id']);
        }
    }

    private function seedSuperadminChatbotData(): User
    {
        $superadmin = User::create([
            'nis' => '3001',
            'nama' => 'Superadmin Chatbot',
            'email' => 'superadmin-chatbot@example.test',
            'otp_enabled' => false,
            'password' => 'secret123',
            'level' => 3,
            'kelas' => null,
        ]);

        User::create([
            'nis' => '1001',
            'nama' => 'Ketua Kelas',
            'email' => 'ketua@example.test',
            'otp_enabled' => false,
            'password' => 'secret123',
            'level' => 1,
            'kelas' => 'RPL XI',
        ]);

        User::create([
            'nis' => '2001',
            'nama' => 'Wali Kelas',
            'email' => 'wali@example.test',
            'otp_enabled' => false,
            'password' => 'secret123',
            'level' => 2,
            'kelas' => 'RPL XI',
        ]);

        User::create([
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
            'kode_permintaan' => 'PMT-TEST-001',
            'id_ruangan' => $roomId,
            'id_user_peminta' => 2,
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

        return $superadmin;
    }

    private function sessionUserPayload(User $user): array
    {
        return [
            'id_user' => $user->id_user,
            'nis' => $user->nis,
            'nama' => $user->nama,
            'email' => $user->email,
            'otp_enabled' => false,
            'level' => 3,
            'role_label' => 'Superadmin',
        ];
    }
}
