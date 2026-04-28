<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class ChatbotAccessService
{
    public function __construct(
        private GeminiChatService $geminiChatService
    ) {
    }

    /**
     * Build chatbot access context for the logged-in user.
     *
     * @param  array<string, mixed>  $sessionUser
     * @return array<string, mixed>
     */
    public function buildContext(array $sessionUser): array
    {
        $userColumns = ['id_user', 'nis', 'nama', 'level'];

        if (Schema::hasColumn('users', 'kelas')) {
            $userColumns[] = 'kelas';
        }

        $user = DB::table('users')
            ->where('id_user', $sessionUser['id_user'] ?? 0)
            ->select($userColumns)
            ->first();

        if (! $user) {
            return [
                'user' => null,
                'role' => [
                    'level' => 0,
                    'name' => 'Tidak dikenal',
                ],
                'permissions' => [],
                'scope' => [],
            ];
        }

        $assignments = DB::table('penugasan_ruangan as pr')
            ->join('ruangan as r', 'r.id_ruangan', '=', 'pr.id_ruangan')
            ->where('pr.id_user', $user->id_user)
            ->where('pr.status', 'aktif')
            ->orderBy('r.nama_ruangan')
            ->select('pr.id_ruangan', 'pr.peran_ruangan', 'r.kode_ruangan', 'r.nama_ruangan', 'r.jenis_ruangan')
            ->get();

        $roomIds = $assignments->pluck('id_ruangan')->map(fn ($value) => (int) $value)->values()->all();
        $roomNames = $assignments->pluck('nama_ruangan')->values()->all();
        $roomCodes = $assignments->pluck('kode_ruangan')->values()->all();
        $role = $this->mapRole((int) $user->level);

        return [
            'user' => [
                'id_user' => (int) $user->id_user,
                'nis' => $user->nis,
                'nama' => $user->nama,
                'kelas' => $user->kelas ?? null,
            ],
            'role' => $role,
            'permissions' => $this->permissionsForLevel((int) $user->level),
            'ai_enabled' => $this->aiEnabled(),
            'scope' => [
                'assigned_room_ids' => $roomIds,
                'assigned_room_names' => $roomNames,
                'assigned_room_codes' => $roomCodes,
                'assigned_rooms' => $assignments->map(fn ($assignment) => [
                    'id_ruangan' => (int) $assignment->id_ruangan,
                    'kode_ruangan' => $assignment->kode_ruangan,
                    'nama_ruangan' => $assignment->nama_ruangan,
                    'jenis_ruangan' => $assignment->jenis_ruangan,
                    'peran_ruangan' => $assignment->peran_ruangan,
                ])->all(),
                'scope_summary' => $this->scopeSummary((int) $user->level, $roomNames, $user->kelas ?? null),
            ],
            'assistant' => $this->buildAssistantBootstrap([
                'user' => [
                    'id_user' => (int) $user->id_user,
                    'nis' => $user->nis,
                    'nama' => $user->nama,
                    'kelas' => $user->kelas ?? null,
                ],
                'role' => $role,
                'permissions' => $this->permissionsForLevel((int) $user->level),
                'scope' => [
                    'assigned_room_ids' => $roomIds,
                    'assigned_room_names' => $roomNames,
                    'assigned_room_codes' => $roomCodes,
                    'assigned_rooms' => $assignments->map(fn ($assignment) => [
                        'id_ruangan' => (int) $assignment->id_ruangan,
                        'kode_ruangan' => $assignment->kode_ruangan,
                        'nama_ruangan' => $assignment->nama_ruangan,
                        'jenis_ruangan' => $assignment->jenis_ruangan,
                        'peran_ruangan' => $assignment->peran_ruangan,
                    ])->all(),
                    'scope_summary' => $this->scopeSummary((int) $user->level, $roomNames, $user->kelas ?? null),
                ],
            ]),
        ];
    }

    /**
     * Process a chatbot message with role-based access control.
     *
     * @param  array<string, mixed>  $sessionUser
     * @param  array<int, array{role: string, message: string}>  $history
     * @return array<string, mixed>
     */
    public function respond(array $sessionUser, string $message, array $history = [], ?string $optionId = null): array
    {
        $context = $this->buildContext($sessionUser);

        if (filled($optionId)) {
            return $this->respondToOption($context, (string) $optionId);
        }

        $matchedOption = $this->matchOptionFromText($context, $message);

        if ($matchedOption !== null) {
            return $this->respondToOption($context, $matchedOption);
        }

        $intent = $this->detectIntent($message);
        $decision = $this->authorizeIntent($context, $intent, $message);

        if (! $decision['allowed']) {
            return [
                'message' => $decision['message'],
                'intent' => $intent,
                'allowed' => false,
                'context' => $context,
                'grounded_message' => $decision['message'],
                'options' => $this->rootOptionsForContext($context),
                'option_style' => 'grid',
                'ai' => [
                    'enabled' => false,
                    'used' => false,
                    'fallback_reason' => 'access_denied',
                ],
            ];
        }

        $groundedAnswer = $this->buildAnswer($context, $intent, $message);
        $menuSuggestion = $this->menuSuggestionForIntent($context, $intent);

        return [
            'message' => $groundedAnswer,
            'intent' => $intent,
            'allowed' => true,
            'context' => $context,
            'grounded_message' => $groundedAnswer,
            'options' => $menuSuggestion['options'],
            'option_style' => $menuSuggestion['style'],
            'ai' => [
                'enabled' => false,
                'used' => false,
                'fallback_reason' => 'guided_mode',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $sessionUser
     * @param  array<string, mixed>  $pendingInput
     * @return array<string, mixed>
     */
    public function submitPendingInput(array $sessionUser, string $message, array $pendingInput): array
    {
        $context = $this->buildContext($sessionUser);
        $trimmedMessage = trim($message);

        if (($pendingInput['mode'] ?? null) !== 'issue_report') {
            return $this->respond($sessionUser, $message);
        }

        $targetUserId = DB::table('users')
            ->where('level', 3)
            ->orderBy('id_user')
            ->value('id_user');

        DB::table('laporan_chatbot')->insert([
            'id_user_pelapor' => (int) ($context['user']['id_user'] ?? 0),
            'id_user_tujuan' => $targetUserId ? (int) $targetUserId : null,
            'topik' => (string) ($pendingInput['topic'] ?? 'Lainnya'),
            'pesan' => $trimmedMessage,
            'status' => 'baru',
            'dibuat_pada' => now(),
        ]);

        $reply = 'Masalahmu sudah saya teruskan ke pengelola sistem. Mohon tunggu tindak lanjut berikutnya.';

        return [
            'message' => $reply,
            'intent' => 'issue_report_submitted',
            'allowed' => true,
            'context' => $context,
            'grounded_message' => $reply,
            'options' => $this->rootOptionsForContext($context),
            'option_style' => 'grid',
            'pending_input' => null,
            'ai' => [
                'enabled' => false,
                'used' => false,
                'fallback_reason' => 'guided_mode',
            ],
        ];
    }

    public function aiEnabled(): bool
    {
        return false;
    }

    private function buildFallbackMessage(string $groundedAnswer, ?string $fallbackReason): string
    {
        return $groundedAnswer;
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function buildAssistantBootstrap(array $context): array
    {
        return [
            'initial_message' => 'Halo, '.$context['user']['nama'].'. Saya siap membantu. Pilih jenis bantuan yang anda butuhkan.',
            'initial_options' => $this->rootOptionsForContext($context),
            'initial_option_style' => 'grid',
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<int, array<string, string>>
     */
    private function rootOptionsForContext(array $context): array
    {
        $level = (int) ($context['role']['level'] ?? 0);

        return match ($level) {
            1 => [
                $this->option('kelas_saya', 'Kelas Saya', 'bi bi-door-open-fill'),
                $this->option('inventaris_saya', 'Inventaris Kelas', 'bi bi-box-seam-fill'),
                $this->option('pengajuan_saya', 'Pengajuan', 'bi bi-send-check-fill'),
                $this->option('bantuan_dashboard', 'Penggunaan Dashboard', 'bi bi-grid-1x2-fill'),
                $this->option('akun_saya', 'Akun', 'bi bi-person-fill'),
                $this->option('masalah_data', 'Masalah Data', 'bi bi-exclamation-diamond-fill'),
            ],
            2 => [
                $this->option('kelas_binaan', 'Kelas Binaan', 'bi bi-building'),
                $this->option('inventaris_kelas', 'Inventaris Kelas', 'bi bi-box-seam-fill'),
                $this->option('pengajuan_kelas', 'Pengajuan Masuk', 'bi bi-inbox-fill'),
                $this->option('verifikasi', 'Verifikasi', 'bi bi-patch-check-fill'),
                $this->option('akses_akun', 'Batas Akses', 'bi bi-shield-lock-fill'),
                $this->option('lainnya', 'Lainnya', 'bi bi-three-dots'),
            ],
            3 => [
                $this->option('data_user', 'Data User', 'bi bi-people-fill'),
                $this->option('data_ruangan', 'Data Ruangan', 'bi bi-building-fill'),
                $this->option('data_barang', 'Data Barang', 'bi bi-grid-fill'),
                $this->option('inventaris_global', 'Inventaris', 'bi bi-box-seam-fill'),
                $this->option('pengajuan_global', 'Pengajuan', 'bi bi-list-check'),
                $this->option('sistem', 'Sistem', 'bi bi-robot'),
            ],
            4 => [
                $this->option('ruangan_owner', 'Semua Ruangan', 'bi bi-buildings-fill'),
                $this->option('inventaris_owner', 'Inventaris Sekolah', 'bi bi-boxes'),
                $this->option('persetujuan_owner', 'Persetujuan Pengajuan', 'bi bi-clipboard2-check-fill'),
                $this->option('laporan_owner', 'Laporan', 'bi bi-bar-chart-fill'),
                $this->option('akses_owner', 'Batas Akses', 'bi bi-shield-lock-fill'),
                $this->option('lainnya', 'Lainnya', 'bi bi-three-dots'),
            ],
            default => [
                $this->option('__root', 'Bantuan', 'bi bi-chat-dots-fill'),
            ],
        };
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function respondToOption(array $context, string $optionId): array
    {
        if ($optionId === '__root') {
            $bootstrap = $this->buildAssistantBootstrap($context);

            return [
                'message' => $bootstrap['initial_message'],
                'intent' => 'guided_root',
                'allowed' => true,
                'context' => $context,
                'grounded_message' => $bootstrap['initial_message'],
                'options' => $bootstrap['initial_options'],
                'option_style' => $bootstrap['initial_option_style'],
                'ai' => [
                    'enabled' => false,
                    'used' => false,
                    'fallback_reason' => 'guided_mode',
                ],
            ];
        }

        $tree = $this->guidedTreeForContext($context);
        $resolved = $this->resolveGuidedNode($tree, $optionId);

        if ($resolved === null) {
            $bootstrap = $this->buildAssistantBootstrap($context);

            return [
                'message' => 'Pilihan bantuan itu belum tersedia. Silakan pilih salah satu topik di bawah ini.',
                'intent' => 'guided_fallback',
                'allowed' => true,
                'context' => $context,
                'grounded_message' => 'Pilihan bantuan itu belum tersedia. Silakan pilih salah satu topik di bawah ini.',
                'options' => $bootstrap['initial_options'],
                'option_style' => $bootstrap['initial_option_style'],
                'ai' => [
                    'enabled' => false,
                    'used' => false,
                    'fallback_reason' => 'guided_mode',
                ],
            ];
        }

        $node = $resolved['node'];
        $children = $node['children'] ?? [];

        if ($children !== []) {
            $options = $this->formatOptions($children);
            $options[] = $this->option('__root', 'Kembali ke menu utama', 'bi bi-house-door-fill');

            return [
                'message' => $node['message'] ?? ('Pilih bantuan yang ingin kamu lanjutkan untuk "'.$node['label'].'".'),
                'intent' => 'guided_branch',
                'allowed' => true,
                'context' => $context,
                'grounded_message' => $node['message'] ?? ('Pilih bantuan yang ingin kamu lanjutkan untuk "'.$node['label'].'".'),
                'options' => $options,
                'option_style' => 'list',
                'ai' => [
                    'enabled' => false,
                    'used' => false,
                    'fallback_reason' => 'guided_mode',
                ],
            ];
        }

        $reply = $this->buildGuidedLeafAnswer($context, (string) ($node['answer_key'] ?? 'help_navigation'));
        $pendingInput = null;

        if (isset($node['input_mode'])) {
            $pendingInput = [
                'mode' => (string) $node['input_mode'],
                'topic' => (string) ($node['input_topic'] ?? $node['label']),
                'placeholder' => (string) ($node['input_placeholder'] ?? 'Tulis masalahmu di sini...'),
            ];
        }

        $siblingOptions = $pendingInput !== null
            ? []
            : ($resolved['siblings'] !== []
                ? $this->formatOptions($resolved['siblings'])
                : $this->rootOptionsForContext($context));

        $siblingOptions[] = $this->option('__root', 'Kembali ke menu utama', 'bi bi-house-door-fill');

        return [
            'message' => $reply,
            'intent' => 'guided_leaf',
            'allowed' => true,
            'context' => $context,
            'grounded_message' => $reply,
            'options' => $siblingOptions,
            'option_style' => 'list',
            'pending_input' => $pendingInput,
            'ai' => [
                'enabled' => false,
                'used' => false,
                'fallback_reason' => 'guided_mode',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<int, array<string, mixed>>
     */
    private function guidedTreeForContext(array $context): array
    {
        $level = (int) ($context['role']['level'] ?? 0);

        return match ($level) {
            1 => [
                [
                    'id' => 'kelas_saya',
                    'label' => 'Kelas Saya',
                    'message' => 'Berikut bantuan yang tersedia untuk kelas dan ruanganmu.',
                    'children' => [
                        ['id' => 'kelas_saya_info', 'label' => 'Data kelas saya', 'answer_key' => 'room_lookup'],
                        ['id' => 'kelas_saya_barang', 'label' => 'Lihat barang di kelas saya', 'answer_key' => 'inventory_detail'],
                        ['id' => 'kelas_saya_akses', 'label' => 'Apa saja akses saya', 'answer_key' => 'scope_info'],
                    ],
                ],
                [
                    'id' => 'inventaris_saya',
                    'label' => 'Inventaris Kelas',
                    'message' => 'Pilih bantuan inventaris yang ingin kamu lihat.',
                    'children' => [
                        ['id' => 'inventaris_saya_ringkasan', 'label' => 'Ringkasan inventaris', 'answer_key' => 'inventory_summary'],
                        ['id' => 'inventaris_saya_detail', 'label' => 'Semua barang di kelas saya', 'answer_key' => 'inventory_detail'],
                    ],
                ],
                [
                    'id' => 'pengajuan_saya',
                    'label' => 'Pengajuan',
                    'message' => 'Pilih bantuan terkait pengajuanmu.',
                    'children' => [
                        ['id' => 'pengajuan_saya_status', 'label' => 'Status pengajuan saya', 'answer_key' => 'request_lookup'],
                        ['id' => 'pengajuan_saya_ajukan', 'label' => 'Cara ajukan barang', 'answer_key' => 'request_create_help'],
                        ['id' => 'pengajuan_saya_riwayat', 'label' => 'Cara lihat riwayat pengajuan', 'answer_key' => 'request_history_help'],
                    ],
                ],
                [
                    'id' => 'bantuan_dashboard',
                    'label' => 'Penggunaan Dashboard',
                    'message' => 'Pilih panduan dashboard yang ingin kamu buka.',
                    'children' => [
                        ['id' => 'dashboard_menu', 'label' => 'Menu yang tersedia', 'answer_key' => 'available_menus'],
                        ['id' => 'dashboard_cara', 'label' => 'Cara memakai dashboard', 'answer_key' => 'help_navigation'],
                    ],
                ],
                [
                    'id' => 'akun_saya',
                    'label' => 'Akun',
                    'message' => 'Pilih bantuan terkait akunmu.',
                    'children' => [
                        ['id' => 'akun_saya_lupa_password', 'label' => 'Lupa password', 'answer_key' => 'forgot_password_help'],
                        [
                            'id' => 'akun_saya_lainnya',
                            'label' => 'Lainnya',
                            'answer_key' => 'custom_issue_prompt',
                            'input_mode' => 'issue_report',
                            'input_topic' => 'Akun',
                            'input_placeholder' => 'Tulis masalah akunmu di sini...',
                        ],
                    ],
                ],
                [
                    'id' => 'masalah_data',
                    'label' => 'Masalah Data',
                    'message' => 'Pilih jenis masalah data yang sedang kamu alami.',
                    'children' => [
                        ['id' => 'masalah_data_jumlah', 'label' => 'Jumlah barang tidak sesuai', 'answer_key' => 'inventory_count_issue_help'],
                        ['id' => 'masalah_data_tidak_muncul', 'label' => 'Data tidak muncul', 'answer_key' => 'data_missing_help'],
                        ['id' => 'masalah_data_salah', 'label' => 'Data salah', 'answer_key' => 'data_incorrect_help'],
                        ['id' => 'masalah_data_barang_tidak_tercatat', 'label' => 'Barang tidak tercatat', 'answer_key' => 'inventory_unlisted_help'],
                    ],
                ],
            ],
            2 => [
                [
                    'id' => 'kelas_binaan',
                    'label' => 'Kelas Binaan',
                    'message' => 'Berikut bantuan yang tersedia untuk kelas binaanmu.',
                    'children' => [
                        ['id' => 'kelas_binaan_info', 'label' => 'Data kelas binaan saya', 'answer_key' => 'room_lookup'],
                        ['id' => 'kelas_binaan_barang', 'label' => 'Semua barang di kelas binaan', 'answer_key' => 'inventory_detail'],
                        ['id' => 'kelas_binaan_akses', 'label' => 'Batas akses saya', 'answer_key' => 'scope_info'],
                    ],
                ],
                [
                    'id' => 'inventaris_kelas',
                    'label' => 'Inventaris Kelas',
                    'message' => 'Pilih bantuan inventaris kelas yang ingin dilihat.',
                    'children' => [
                        ['id' => 'inventaris_kelas_ringkasan', 'label' => 'Ringkasan inventaris kelas', 'answer_key' => 'inventory_summary'],
                        ['id' => 'inventaris_kelas_detail', 'label' => 'Detail barang kelas', 'answer_key' => 'inventory_detail'],
                    ],
                ],
                [
                    'id' => 'pengajuan_kelas',
                    'label' => 'Pengajuan Masuk',
                    'message' => 'Pilih informasi pengajuan yang ingin kamu lihat.',
                    'children' => [
                        ['id' => 'pengajuan_kelas_status', 'label' => 'Ringkasan pengajuan masuk', 'answer_key' => 'request_lookup'],
                        ['id' => 'pengajuan_kelas_riwayat', 'label' => 'Cara lihat riwayat verifikasi', 'answer_key' => 'verification_history_help'],
                    ],
                ],
                [
                    'id' => 'verifikasi',
                    'label' => 'Verifikasi',
                    'message' => 'Pilih panduan verifikasi yang ingin kamu buka.',
                    'children' => [
                        ['id' => 'verifikasi_cara', 'label' => 'Cara verifikasi pengajuan', 'answer_key' => 'verification_help'],
                        ['id' => 'verifikasi_batas', 'label' => 'Yang bisa saya verifikasi', 'answer_key' => 'restricted_scope'],
                    ],
                ],
                [
                    'id' => 'akses_akun',
                    'label' => 'Batas Akses',
                    'message' => 'Pilih informasi akses akun wali kelas.',
                    'children' => [
                        ['id' => 'akses_akun_info', 'label' => 'Batas akses saya', 'answer_key' => 'scope_info'],
                        ['id' => 'akses_akun_larangan', 'label' => 'Yang tidak bisa saya buka', 'answer_key' => 'restricted_scope'],
                    ],
                ],
                [
                    'id' => 'lainnya',
                    'label' => 'Lainnya',
                    'message' => 'Pilih bantuan tambahan yang tersedia.',
                    'children' => [
                        ['id' => 'lainnya_akses', 'label' => 'Jika akses belum sesuai', 'answer_key' => 'contact_support'],
                        ['id' => 'lainnya_rules', 'label' => 'Aturan chatbot ini', 'answer_key' => 'chatbot_rules'],
                    ],
                ],
            ],
            3 => [
                [
                    'id' => 'data_user',
                    'label' => 'Data User',
                    'message' => 'Pilih bantuan terkait data user.',
                    'children' => [
                        ['id' => 'data_user_ringkasan', 'label' => 'Ringkasan data user', 'answer_key' => 'global_users'],
                        ['id' => 'data_user_aksi', 'label' => 'Perintah kelola user', 'answer_key' => 'system_manager_user_help'],
                    ],
                ],
                [
                    'id' => 'data_ruangan',
                    'label' => 'Data Ruangan',
                    'message' => 'Pilih bantuan terkait data ruangan.',
                    'children' => [
                        ['id' => 'data_ruangan_ringkasan', 'label' => 'Ringkasan ruangan', 'answer_key' => 'global_rooms'],
                    ],
                ],
                [
                    'id' => 'data_barang',
                    'label' => 'Data Barang',
                    'message' => 'Pilih bantuan terkait data barang.',
                    'children' => [
                        ['id' => 'data_barang_ringkasan', 'label' => 'Ringkasan data barang', 'answer_key' => 'global_items'],
                    ],
                ],
                [
                    'id' => 'inventaris_global',
                    'label' => 'Inventaris',
                    'message' => 'Pilih bantuan terkait inventaris sekolah.',
                    'children' => [
                        ['id' => 'inventaris_global_ringkasan', 'label' => 'Ringkasan inventaris sekolah', 'answer_key' => 'global_inventory'],
                    ],
                ],
                [
                    'id' => 'pengajuan_global',
                    'label' => 'Pengajuan',
                    'message' => 'Pilih bantuan terkait pengajuan sistem.',
                    'children' => [
                        ['id' => 'pengajuan_global_ringkasan', 'label' => 'Ringkasan pengajuan', 'answer_key' => 'global_requests'],
                    ],
                ],
                [
                    'id' => 'sistem',
                    'label' => 'Sistem',
                    'message' => 'Pilih bantuan terkait sistem dan hak pengelola sistem.',
                    'children' => [
                        ['id' => 'sistem_hak', 'label' => 'Hak akses pengelola sistem', 'answer_key' => 'system_scope'],
                        ['id' => 'sistem_rules', 'label' => 'Aturan chatbot ini', 'answer_key' => 'chatbot_rules'],
                    ],
                ],
            ],
            4 => [
                [
                    'id' => 'ruangan_owner',
                    'label' => 'Semua Ruangan',
                    'message' => 'Pilih bantuan terkait data ruangan sekolah.',
                    'children' => [
                        ['id' => 'ruangan_owner_ringkasan', 'label' => 'Ringkasan semua ruangan', 'answer_key' => 'global_rooms'],
                    ],
                ],
                [
                    'id' => 'inventaris_owner',
                    'label' => 'Inventaris Sekolah',
                    'message' => 'Pilih bantuan terkait inventaris sekolah.',
                    'children' => [
                        ['id' => 'inventaris_owner_ringkasan', 'label' => 'Ringkasan inventaris sekolah', 'answer_key' => 'global_inventory'],
                    ],
                ],
                [
                    'id' => 'persetujuan_owner',
                    'label' => 'Persetujuan Pengajuan',
                    'message' => 'Pilih bantuan terkait persetujuan pengajuan.',
                    'children' => [
                        ['id' => 'persetujuan_owner_ringkasan', 'label' => 'Ringkasan pengajuan', 'answer_key' => 'global_requests'],
                        ['id' => 'persetujuan_owner_batas', 'label' => 'Batas akses owner', 'answer_key' => 'owner_scope'],
                    ],
                ],
                [
                    'id' => 'laporan_owner',
                    'label' => 'Laporan',
                    'message' => 'Pilih bantuan laporan yang ingin kamu lihat.',
                    'children' => [
                        ['id' => 'laporan_owner_ringkasan', 'label' => 'Ringkasan laporan sekolah', 'answer_key' => 'reports_help'],
                    ],
                ],
                [
                    'id' => 'akses_owner',
                    'label' => 'Batas Akses',
                    'message' => 'Pilih informasi batas akses owner.',
                    'children' => [
                        ['id' => 'akses_owner_info', 'label' => 'Yang bisa saya lihat', 'answer_key' => 'owner_scope'],
                        ['id' => 'akses_owner_larangan', 'label' => 'Yang tidak bisa saya ubah', 'answer_key' => 'restricted_scope'],
                    ],
                ],
                [
                    'id' => 'lainnya',
                    'label' => 'Lainnya',
                    'message' => 'Pilih bantuan tambahan yang tersedia.',
                    'children' => [
                        ['id' => 'lainnya_rules', 'label' => 'Aturan chatbot ini', 'answer_key' => 'chatbot_rules'],
                    ],
                ],
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>|null
     */
    private function resolveGuidedNode(array $tree, string $optionId, array $parentChildren = []): ?array
    {
        foreach ($tree as $node) {
            if (($node['id'] ?? null) === $optionId) {
                return [
                    'node' => $node,
                    'siblings' => array_values(array_filter($parentChildren, fn ($item) => ($item['id'] ?? null) !== $optionId)),
                ];
            }

            $children = $node['children'] ?? [];

            if ($children !== []) {
                $resolved = $this->resolveGuidedNode($children, $optionId, $children);

                if ($resolved !== null) {
                    return $resolved;
                }
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function matchOptionFromText(array $context, string $message): ?string
    {
        $text = mb_strtolower(trim($message));

        if ($text === '') {
            return null;
        }

        foreach ($this->flattenGuidedTree($this->guidedTreeForContext($context)) as $node) {
            $label = mb_strtolower((string) ($node['label'] ?? ''));

            if ($label !== '' && $text === $label) {
                return (string) $node['id'];
            }
        }

        if ($this->containsAny($text, [
            'tampilkan',
            'lihat',
            'berapa',
            'total',
            'data',
            'status',
            'tolong',
            'mohon',
            'cek',
        ])) {
            return null;
        }

        return match (true) {
            $this->containsAny($text, ['jumlah barang tidak sesuai', 'data tidak muncul', 'data salah', 'barang tidak tercatat', 'data beda', 'data kursi beda', 'tidak sesuai'])
                || (
                    $this->containsAny($text, ['beda', 'salah', 'tidak sesuai'])
                    && $this->containsAny($text, ['data', 'barang', 'kursi', 'inventaris'])
                ) => match ((int) ($context['role']['level'] ?? 0)) {
                1 => 'masalah_data',
                default => null,
            },
            $this->containsAny($text, ['kelas saya', 'kelas binaan']) => in_array((int) ($context['role']['level'] ?? 0), [1, 2], true)
                ? ((int) ($context['role']['level'] ?? 0) === 1 ? 'kelas_saya' : 'kelas_binaan')
                : null,
            $this->containsAny($text, ['akses akun', 'lupa password', 'akun']) => match ((int) ($context['role']['level'] ?? 0)) {
                1 => 'akun_saya',
                default => null,
            },
            $this->containsAny($text, ['inventaris', 'barang']) => match ((int) ($context['role']['level'] ?? 0)) {
                1 => 'inventaris_saya',
                2 => 'inventaris_kelas',
                3 => 'inventaris_global',
                4 => 'inventaris_owner',
                default => null,
            },
            $this->containsAny($text, ['pengajuan', 'permintaan']) => match ((int) ($context['role']['level'] ?? 0)) {
                1 => 'pengajuan_saya',
                2 => 'pengajuan_kelas',
                3 => 'pengajuan_global',
                4 => 'persetujuan_owner',
                default => null,
            },
            $this->containsAny($text, ['ajukan', 'pengajuan', 'permintaan', 'status pengajuan']) => match ((int) ($context['role']['level'] ?? 0)) {
                1 => 'pengajuan_saya',
                2 => 'pengajuan_kelas',
                3 => 'pengajuan_global',
                4 => 'persetujuan_owner',
                default => null,
            },
            $this->containsAny($text, ['masalah data']) => match ((int) ($context['role']['level'] ?? 0)) {
                1 => 'masalah_data',
                default => null,
            },
            $this->containsAny($text, ['menu', 'dashboard', 'bantuan']) => in_array((int) ($context['role']['level'] ?? 0), [1, 2], true)
                ? (((int) ($context['role']['level'] ?? 0) === 1) ? 'bantuan_dashboard' : 'verifikasi')
                : 'sistem',
            default => null,
        };
    }

    /**
     * @param  array<int, array<string, mixed>>  $tree
     * @return array<int, array<string, mixed>>
     */
    private function flattenGuidedTree(array $tree): array
    {
        $flat = [];

        foreach ($tree as $node) {
            $flat[] = $node;

            if (($node['children'] ?? []) !== []) {
                $flat = array_merge($flat, $this->flattenGuidedTree($node['children']));
            }
        }

        return $flat;
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array{options: array<int, array<string, string>>, style: string}
     */
    private function menuSuggestionForIntent(array $context, string $intent): array
    {
        return match ($intent) {
            'room_lookup' => [
                'options' => $this->formatOptionsFromIds($context, ['kelas_saya', 'kelas_binaan', '__root']),
                'style' => 'list',
            ],
            'inventory_lookup' => [
                'options' => $this->formatOptionsFromIds($context, ['inventaris_saya', 'inventaris_kelas', 'inventaris_global', 'inventaris_owner', '__root']),
                'style' => 'list',
            ],
            'request_lookup' => [
                'options' => $this->formatOptionsFromIds($context, ['pengajuan_saya', 'pengajuan_kelas', 'pengajuan_global', 'persetujuan_owner', '__root']),
                'style' => 'list',
            ],
            default => [
                'options' => $this->rootOptionsForContext($context),
                'style' => 'grid',
            ],
        };
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function buildGuidedLeafAnswer(array $context, string $answerKey): string
    {
        return match ($answerKey) {
            'room_lookup' => $this->buildRoomAnswer($context),
            'inventory_summary' => $this->buildInventoryAnswer($context, 'inventaris'),
            'inventory_detail' => $this->buildInventoryAnswer($context, 'semua data barang di kelas saya'),
            'request_lookup' => $this->buildRequestAnswer($context),
            'scope_info' => $context['scope']['scope_summary'] ?? 'Akses akun ini mengikuti role dan penugasan yang aktif.',
            'restricted_scope' => $this->buildRestrictedScopeAnswer($context),
            'request_create_help' => 'Untuk mengajukan barang baru, buka menu '.$this->formatMenuName('Ajukan Permintaan').', lalu isi jenis kebutuhan, jumlah, dan keterangan yang diperlukan.',
            'request_history_help' => 'Untuk melihat riwayat pengajuan, buka menu '.$this->formatMenuName('Riwayat Pengajuan').'. Di sana kamu bisa melihat status pengajuan yang pernah dibuat.',
            'forgot_password_help' => 'Jika kamu lupa password, silakan hubungi wali kelas agar password akunmu dapat direset dengan aman.',
            'custom_issue_prompt' => 'Silakan tulis masalah akunmu di kolom input di bawah. Jika kendalanya tidak ada di pilihan yang tersedia, saya akan teruskan ke pengelola sistem.',
            'custom_general_issue_prompt' => 'Silakan tulis masalahmu di kolom input di bawah. Pesanmu akan saya teruskan ke pengelola sistem untuk ditindaklanjuti.',
            'inventory_count_issue_help' => 'Jika jumlah barang tidak sesuai, cocokan dulu data inventaris dengan kondisi fisik di kelasmu. Setelah itu laporkan ke wali kelas agar datanya bisa diperiksa.',
            'data_missing_help' => 'Jika data tidak muncul, pastikan kamu membuka menu dan kelas yang sesuai dengan akunmu. Jika masih belum terlihat, hubungi wali kelas untuk pengecekan lanjutan.',
            'data_incorrect_help' => 'Jika ada data yang salah, catat bagian yang keliru lalu laporkan ke wali kelas agar bisa diperbarui sesuai kondisi yang benar.',
            'inventory_unlisted_help' => 'Jika ada barang yang belum tercatat, siapkan nama barang dan keterangan singkatnya lalu hubungi wali kelas agar data inventaris bisa diteruskan untuk ditambahkan.',
            'verification_help' => 'Untuk verifikasi pengajuan, buka menu '.$this->formatMenuName('Pengajuan Masuk').', cek detail permintaan, lalu lanjutkan proses sesuai status yang tersedia.',
            'verification_history_help' => 'Untuk melihat riwayat verifikasi, buka menu '.$this->formatMenuName('Riwayat Verifikasi').' atau halaman pengajuan yang sudah diproses.',
            'available_menus' => $this->buildAvailableMenuAnswer($context),
            'help_navigation' => $this->buildHelpAnswer($context),
            'contact_support' => 'Jika akses kelas atau data belum sesuai, silakan hubungi wali kelas atau pengelola sistem agar hak akses akunmu dapat diperiksa.',
            'chatbot_rules' => 'Chatbot ini memakai alur bantuan bertingkat. Kamu bisa memilih kategori yang tersedia atau mengetik pertanyaan sendiri, lalu saya akan cocokkan ke topik yang paling dekat.',
            'global_users' => $this->buildGlobalReadAnswer($context, 'semua user'),
            'global_rooms' => $this->buildGlobalReadAnswer($context, 'semua ruangan'),
            'global_items' => $this->buildGlobalItemAnswer(),
            'global_inventory' => $this->buildGlobalReadAnswer($context, 'semua inventaris'),
            'global_requests' => $this->buildRequestAnswer($context),
            'system_scope' => 'Sebagai pengelola sistem, akunmu dapat membaca seluruh data sistem dan menjalankan aksi tertentu yang memang sudah disediakan backend secara aman.',
            'system_manager_user_help' => 'Perintah user yang saat ini tersedia untuk pengelola sistem adalah tambah user, ubah level user, dan hapus user dengan format yang sudah ditentukan.',
            'reports_help' => 'Menu laporan menampilkan ringkasan data sekolah seperti inventaris, ruangan, dan pengajuan dalam bentuk baca saja sesuai role akunmu.',
            'owner_scope' => 'Sebagai owner, kamu dapat melihat ringkasan seluruh data sekolah, tetapi tidak dapat menambah, mengubah, atau menghapus data lewat chatbot.',
            default => $this->buildHelpAnswer($context),
        };
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function buildRestrictedScopeAnswer(array $context): string
    {
        $level = (int) ($context['role']['level'] ?? 0);

        return match ($level) {
            1 => 'Akun ketua kelas tidak dapat membuka data kelas lain, data seluruh sekolah, atau melakukan tambah, ubah, dan hapus data lewat chatbot.',
            2 => 'Akun wali kelas hanya dapat membuka data kelas binaannya sendiri dan tidak dapat mengakses kelas lain atau melakukan CRUD data lewat chatbot.',
            3 => 'Walau pengelola sistem memiliki akses luas, chatbot tetap tidak menampilkan database mentah dan hanya menjalankan aksi yang sudah diamankan backend.',
            4 => 'Akun owner bersifat baca saja. Kamu tidak dapat menambah, mengubah, atau menghapus data lewat chatbot.',
            default => 'Akses akun ini terbatas sesuai role yang sedang aktif.',
        };
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function buildAvailableMenuAnswer(array $context): string
    {
        $level = (int) ($context['role']['level'] ?? 0);

        return match ($level) {
            1 => 'Menu utama akunmu meliputi '.$this->formatMenuList(['Kelas Saya', 'Ajukan Permintaan', 'Riwayat Pengajuan']).'.',
            2 => 'Menu utama akunmu meliputi '.$this->formatMenuList(['Kelas Binaan', 'Pengajuan Masuk', 'Riwayat Verifikasi']).'.',
            3 => 'Menu utama akunmu meliputi '.$this->formatMenuList(['Data User', 'Data Ruangan', 'Data Barang', 'Data Inventaris', 'Realisasi Pengajuan', 'Asisten Sistem', 'Laporan']).'.',
            4 => 'Menu utama akunmu meliputi '.$this->formatMenuList(['Semua Ruangan', 'Inventaris Sekolah', 'Persetujuan Pengajuan', 'Asisten Sistem', 'Laporan']).'.',
            default => 'Menu dashboard akan mengikuti role akun yang sedang aktif.',
        };
    }

    private function buildGlobalItemAnswer(): string
    {
        $totalItems = (int) DB::table('barang')->count();

        return 'Saat ini terdapat '.$totalItems.' data barang yang terdaftar di sistem.';
    }

    /**
     * @param  array<int, array<string, mixed>>  $nodes
     * @return array<int, array<string, string>>
     */
    private function formatOptions(array $nodes): array
    {
        return array_map(function ($node) {
            return $this->option(
                (string) $node['id'],
                (string) $node['label'],
                (string) ($node['icon'] ?? 'bi bi-arrow-right')
            );
        }, $nodes);
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<int, string>  $ids
     * @return array<int, array<string, string>>
     */
    private function formatOptionsFromIds(array $context, array $ids): array
    {
        $catalog = [];

        foreach ($this->flattenGuidedTree($this->guidedTreeForContext($context)) as $node) {
            $catalog[$node['id']] = $node;
        }

        foreach ($this->rootOptionsForContext($context) as $option) {
            $catalog[$option['id']] = $option;
        }

        $result = [];

        foreach ($ids as $id) {
            if ($id === '__root') {
                $result[] = $this->option('__root', 'Kembali ke menu utama', 'bi bi-house-door-fill');
                continue;
            }

            if (! isset($catalog[$id])) {
                continue;
            }

            $item = $catalog[$id];
            $result[] = $this->option(
                (string) $item['id'],
                (string) $item['label'],
                (string) ($item['icon'] ?? 'bi bi-arrow-right')
            );
        }

        return $result;
    }

    /**
     * @return array<string, string>
     */
    private function option(string $id, string $label, string $icon = 'bi bi-arrow-right'): array
    {
        return [
            'id' => $id,
            'label' => $label,
            'icon' => $icon,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapRole(int $level): array
    {
        return match ($level) {
            1 => ['level' => 1, 'name' => 'Ketua Kelas'],
            2 => ['level' => 2, 'name' => 'Wali Kelas'],
            3 => ['level' => 3, 'name' => 'Superadmin'],
            4 => ['level' => 4, 'name' => 'Kepala Sekolah'],
            default => ['level' => $level, 'name' => 'Tidak dikenal'],
        };
    }

    /**
     * @return array<string, bool>
     */
    private function permissionsForLevel(int $level): array
    {
        return match ($level) {
            1 => [
                'help_navigation' => true,
                'read_own_data' => true,
                'read_assigned_scope' => false,
                'read_all_data' => false,
                'write_data' => false,
                'system_access' => false,
            ],
            2 => [
                'help_navigation' => true,
                'read_own_data' => true,
                'read_assigned_scope' => true,
                'read_all_data' => false,
                'write_data' => false,
                'system_access' => false,
            ],
            3 => [
                'help_navigation' => true,
                'read_own_data' => true,
                'read_assigned_scope' => true,
                'read_all_data' => true,
                'write_data' => true,
                'system_access' => true,
            ],
            4 => [
                'help_navigation' => true,
                'read_own_data' => true,
                'read_assigned_scope' => true,
                'read_all_data' => true,
                'write_data' => false,
                'system_access' => false,
            ],
            default => [
                'help_navigation' => false,
                'read_own_data' => false,
                'read_assigned_scope' => false,
                'read_all_data' => false,
                'write_data' => false,
                'system_access' => false,
            ],
        };
    }

    private function scopeSummary(int $level, array $roomNames, ?string $kelas): string
    {
        return match ($level) {
            1 => $roomNames !== []
                ? 'Akses terbatas ke data diri sendiri dan ruangan yang ditugaskan: '.implode(', ', $roomNames).'.'
                : 'Akses terbatas ke data diri sendiri. Belum ada ruangan aktif yang ditugaskan.',
            2 => $roomNames !== []
                ? 'Akses terbatas ke kelas sendiri dan lingkup wali kelas: '.implode(', ', $roomNames).'.'
                : 'Akses terbatas ke kelas sendiri dan data dalam lingkup wali kelas.',
            3 => 'Akses penuh ke seluruh data sistem melalui layer backend yang aman.',
            4 => 'Akses baca kepala sekolah ke seluruh data sekolah tanpa izin tambah, ubah, atau hapus.',
            default => 'Akses belum dikenali.',
        };
    }

    private function detectIntent(string $message): string
    {
        $text = mb_strtolower($message);

        if ($this->containsAny($text, ['apa kabar', 'gimana kabar', 'bagaimana kabar', 'kabarmu'])) {
            return 'wellbeing';
        }

        if ($this->containsAny($text, ['gajadi', 'ga jadi', 'gak jadi', 'nggak jadi', 'batal', 'tidak jadi'])) {
            return 'cancel';
        }

        if ($this->containsAny($text, ['terima kasih', 'makasih', 'thanks', 'thx'])) {
            return 'gratitude';
        }

        if ($this->containsAny($text, ['siapa kamu', 'kamu siapa', 'siapa anda', 'anda siapa'])) {
            return 'identity';
        }

        if ($this->containsAny($text, ['buatkan foto', 'buat foto', 'bikin foto', 'buatkan gambar', 'bikin gambar', 'generate gambar', 'generate image', 'buat logo', 'desainkan'])) {
            return 'out_of_scope';
        }

        if ($this->containsAny($text, ['bisa bantu apa', 'bisa apa', 'apa yang bisa kamu bantu', 'apa yang bisa anda bantu', 'fitur kamu apa'])) {
            return 'capabilities';
        }

        if ($this->containsAny($text, ['dadah', 'bye', 'selamat tinggal', 'sampai jumpa', 'jumpa lagi'])) {
            return 'goodbye';
        }

        if ($this->containsAny($text, ['oke', 'ok', 'sip', 'siap', 'baik'])) {
            return 'acknowledgement';
        }

        if ($this->containsAny($text, ['tidak ada', 'ga ada', 'gak ada', 'nggak ada', 'tidak', 'nggak', 'gak', 'sudah', 'udah', 'cukup'])) {
            return 'no_followup';
        }

        if (mb_strlen(trim($text)) <= 1) {
            return 'unclear_text';
        }

        if (preg_match('/^[a-z]{4,}$/', trim($text)) && ! str_contains($text, ' ')) {
            return 'unclear_text';
        }

        if (preg_match('/^[a-z\s]{2,}$/', trim($text)) === 1
            && ! str_contains($text, ' ')
            && ! $this->containsAny($text, ['ok', 'oke', 'sip', 'hai', 'halo', 'hi'])
        ) {
            return 'unclear_text';
        }

        if ($this->containsAny($text, ['database', 'sql', 'query', 'tabel', 'schema'])) {
            return 'database_access';
        }

        if ($this->matchesCreateUserIntent($text)) {
            return 'create_user';
        }

        if ($this->matchesUpdateUserLevelIntent($text)) {
            return 'update_user_level';
        }

        if ($this->matchesDeleteUserIntent($text)) {
            return 'delete_user';
        }

        if ($this->containsAny($text, ['tambah', 'tambahkan', 'ubah', 'edit', 'hapus', 'delete', 'perbarui', 'update'])) {
            return 'write_action';
        }

        if ($this->containsAny($text, ['cara', 'bagaimana', 'bantuan', 'menu', 'fitur'])) {
            return 'help_navigation';
        }

        if ($this->containsAny($text, ['pengajuan', 'permintaan', 'status pengajuan'])) {
            return 'request_lookup';
        }

        if ($this->containsAny($text, ['inventaris', 'barang'])) {
            return 'inventory_lookup';
        }

        if ($this->containsAny($text, ['kelas', 'ruangan'])) {
            return 'room_lookup';
        }

        if ($this->containsAny($text, ['semua data', 'seluruh data', 'semua kelas', 'kelas lain', 'ruangan lain', 'user lain', 'semua siswa'])) {
            return 'cross_scope_data';
        }

        if (! $this->isInfraSPHRelatedText($text)) {
            return 'unclear_text';
        }

        return 'general_help';
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array{allowed: bool, message: string}
     */
    private function authorizeIntent(array $context, string $intent, string $message): array
    {
        $permissions = $context['permissions'] ?? [];
        $roleName = $context['role']['name'] ?? 'Pengguna';
        $scopeSummary = $context['scope']['scope_summary'] ?? 'Akses dibatasi sesuai peran akun.';
        $level = (int) ($context['role']['level'] ?? 0);
        $assignedRoomIds = $context['scope']['assigned_room_ids'] ?? [];

        if ($intent === 'database_access') {
            return [
                'allowed' => false,
                'message' => 'Maaf, chatbot tidak menampilkan database mentah atau query langsung. '.$scopeSummary,
            ];
        }

        if (in_array($intent, ['room_lookup', 'inventory_lookup', 'request_lookup', 'cross_scope_data'], true)
            && in_array($level, [1, 2], true)
            && $assignedRoomIds === []) {
            return [
                'allowed' => false,
                'message' => 'Maaf, akunmu belum memiliki penugasan kelas atau ruangan aktif. Hubungi wali kelas atau pengelola sistem untuk mengatur akses kelasmu.',
            ];
        }

        if (in_array($intent, ['create_user', 'update_user_level', 'delete_user'], true)) {
            if ((int) ($context['role']['level'] ?? 0) !== 3) {
                return [
                    'allowed' => false,
                    'message' => 'Maaf, aksi pengelolaan data hanya tersedia untuk akun pengelola sistem.',
                ];
            }

            return [
                'allowed' => true,
                'message' => '',
            ];
        }

        if ($intent === 'write_action' && ! ($permissions['write_data'] ?? false)) {
            return [
                'allowed' => false,
                'message' => 'Maaf, akun '.$roleName.' tidak memiliki izin untuk menambah, mengubah, atau menghapus data lewat chatbot.',
            ];
        }

        $text = mb_strtolower($message);
        $asksOwnData = $this->containsAny($text, ['saya', 'milik saya', 'kelas saya', 'ruangan saya', 'kelas binaan saya']);
        $mentionedScope = $this->extractMentionedScopes($text);

        if ($intent === 'cross_scope_data' && ! ($permissions['read_all_data'] ?? false)) {
            if ($asksOwnData) {
                return [
                    'allowed' => true,
                    'message' => '',
                ];
            }

            return [
                'allowed' => false,
                'message' => 'Maaf, permintaan itu berada di luar akses akunmu. '.$scopeSummary,
            ];
        }

        if (in_array($intent, ['room_lookup', 'inventory_lookup', 'request_lookup'], true)) {
            $mentionsOutsideScope = $this->mentionsOutsideAssignedScope($context, $mentionedScope);
            $mentionsAssignedScope = $this->mentionsAssignedScope($context, $mentionedScope);

            if (in_array($level, [1, 2], true) && $mentionsOutsideScope) {
                return [
                    'allowed' => false,
                    'message' => 'Maaf, kamu hanya bisa mengakses data kelas yang sesuai dengan session dan penugasanmu. '.$scopeSummary,
                ];
            }

            if (($permissions['read_all_data'] ?? false) || ($permissions['read_assigned_scope'] ?? false) || ($permissions['read_own_data'] ?? false)) {
                if (($permissions['read_all_data'] ?? false) === false
                    && ! $asksOwnData
                    && ! $mentionsAssignedScope
                    && $this->containsAny($text, ['lain', 'semua', 'seluruh'])) {
                    return [
                        'allowed' => false,
                        'message' => 'Maaf, chatbot hanya boleh menampilkan data dalam lingkup akses akunmu. '.$scopeSummary,
                    ];
                }

                return [
                    'allowed' => true,
                    'message' => '',
                ];
            }
        }

        return [
            'allowed' => true,
            'message' => '',
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function buildAnswer(array $context, string $intent, string $message): string
    {
        return match ($intent) {
            'wellbeing' => $this->buildWellbeingAnswer($context),
            'cancel' => $this->buildCancelAnswer($context),
            'gratitude' => $this->buildGratitudeAnswer($context),
            'identity' => $this->buildIdentityAnswer($context),
            'capabilities' => $this->buildCapabilitiesAnswer($context),
            'goodbye' => $this->buildGoodbyeAnswer($context),
            'acknowledgement' => $this->buildAcknowledgementAnswer($context),
            'no_followup' => $this->buildNoFollowupAnswer($context),
            'unclear_text' => $this->buildUnclearTextAnswer(),
            'out_of_scope' => $this->buildOutOfScopeAnswer(),
            'create_user' => $this->buildCreateUserAnswer($message),
            'update_user_level' => $this->buildUpdateUserLevelAnswer($message),
            'delete_user' => $this->buildDeleteUserAnswer($message),
            'help_navigation', 'general_help' => $this->buildHelpAnswer($context),
            'room_lookup' => $this->buildRoomAnswer($context, $message),
            'inventory_lookup' => $this->buildInventoryAnswer($context, $message),
            'request_lookup' => $this->buildRequestAnswer($context),
            'cross_scope_data' => $this->buildGlobalReadAnswer($context, $message),
            'write_action' => $this->buildWriteScopeAnswer($context),
            default => $this->buildHelpAnswer($context),
        };
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function buildWellbeingAnswer(array $context): string
    {
        return 'Halo, '.$context['user']['nama'].'. Saya baik, terima kasih. Kalau ada yang ingin kamu tanyakan soal InfraSPH, saya siap membantu.';
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function buildCancelAnswer(array $context): string
    {
        return 'Baik, '.$context['user']['nama'].'. Kita batalkan dulu. Kalau nanti ingin lanjut lagi, saya siap membantu.';
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function buildGratitudeAnswer(array $context): string
    {
        return 'Sama-sama, '.$context['user']['nama'].'. Kalau masih ada hal di InfraSPH yang ingin dicek, saya siap bantu lagi.';
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function buildIdentityAnswer(array $context): string
    {
        $roleName = $context['role']['name'] ?? 'Pengguna';

        return 'Saya adalah customer service dan asisten InfraSPH. Saya membantu menjawab pertanyaan tentang menu, inventaris, pengajuan, akun, dan penggunaan sistem sesuai akses akun '.$roleName.' kamu.';
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function buildCapabilitiesAnswer(array $context): string
    {
        $level = (int) ($context['role']['level'] ?? 0);
        $base = 'Saya bisa membantu menjelaskan fitur dashboard, menampilkan ringkasan inventaris, ruangan, dan status pengajuan sesuai hak akses akunmu.';

        return match ($level) {
            1 => $base.' Untuk akunmu, saya fokus pada data diri sendiri, ruangan yang ditugaskan, dan pengajuan milikmu.',
            2 => $base.' Untuk akun wali kelas, saya bisa membantu data kelas sendiri dan lingkup penugasan wali kelas.',
            3 => $base.' Untuk akun superadmin, saya juga bisa membantu ringkasan global sistem dan konteks operasional yang lebih luas.',
            4 => $base.' Untuk akun kepala sekolah, saya bisa membantu menampilkan data seluruh kelas dan ringkasan sekolah secara baca saja.',
            default => $base,
        };
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function buildGoodbyeAnswer(array $context): string
    {
        return 'Siap, sampai jumpa lagi '.$context['user']['nama'].'. Kalau nanti butuh bantuan, tinggal panggil saya di panel chatbot ini.';
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function buildAcknowledgementAnswer(array $context): string
    {
        return 'Siap, '.$context['user']['nama'].'.';
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function buildNoFollowupAnswer(array $context): string
    {
        return 'Baik, '.$context['user']['nama'].'. Kalau nanti ada yang ingin ditanyakan, saya siap membantu.';
    }

    /**
     * @return string
     */
    private function buildUnclearTextAnswer(): string
    {
        return 'Maaf, saya belum bisa memahami maksud pesan Anda. Silakan kirim pertanyaan atau jelaskan masalah Anda terkait InfraSPH dengan lebih jelas, agar saya bisa membantu.';
    }

    /**
     * @return string
     */
    private function buildOutOfScopeAnswer(): string
    {
        return 'Maaf, saya belum bisa memahami maksud pesan Anda. Silakan kirim pertanyaan atau jelaskan masalah Anda terkait InfraSPH dengan lebih jelas, agar saya bisa membantu.';
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function buildHelpAnswer(array $context): string
    {
        $level = (int) ($context['role']['level'] ?? 0);

        return match ($level) {
            1 => 'Saya bisa membantu untuk '.$this->formatMenuList(['Kelas Saya', 'Inventaris Kelas', 'Pengajuan', 'Akun', 'Penggunaan Dashboard']).'. Kamu juga bisa memilih kategori bantuan yang tersedia di panel chat.',
            2 => 'Saya bisa membantu untuk '.$this->formatMenuList(['Kelas Binaan', 'Inventaris Kelas', 'Pengajuan Masuk', 'Riwayat Verifikasi']).' sesuai aksesmu.',
            3 => 'Saya bisa membantu untuk '.$this->formatMenuList(['Data User', 'Data Ruangan', 'Data Barang', 'Data Inventaris', 'Realisasi Pengajuan', 'Asisten Sistem', 'Laporan']).' sesuai akses akunmu.',
            4 => 'Saya bisa membantu untuk melihat data melalui '.$this->formatMenuList(['Semua Ruangan', 'Inventaris Sekolah', 'Persetujuan Pengajuan', 'Laporan']).'. Saya juga bisa menampilkan data kelas tertentu dan menghitung total barang dari semua kelas.',
            default => 'Saya siap membantu soal inventaris, ruangan, pengajuan, dan penggunaan dashboard sesuai akses akunmu.',
        };
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function buildRoomAnswer(array $context, string $message = ''): string
    {
        $level = (int) ($context['role']['level'] ?? 0);
        $text = mb_strtolower($message);

        if ($level === 4) {
            $mentionedLabels = $this->mentionedClassLabels($text);
            $mentionedRooms = $this->mentionedClassRooms($text);

            if ($this->containsAny($text, ['semua kelas', 'seluruh kelas', 'daftar kelas', 'kelas apa saja', 'kelas apa aja'])) {
                return $this->buildAllClassListAnswer();
            }

            if ($mentionedRooms->isNotEmpty()) {
                return $this->buildClassRoomDetailAnswer($mentionedRooms);
            }

            if ($mentionedLabels !== []) {
                return 'Data kelas yang Anda minta belum ditemukan di sistem. Silakan periksa kembali nama kelas yang ingin ditampilkan.';
            }
        }

        if (in_array($level, [3, 4], true)) {
            $totalRooms = (int) DB::table('ruangan')->count();

            return 'Saat ini sistem memiliki '.$totalRooms.' ruangan terdaftar. Saya bisa bantu arahkan ke data kelas, laboratorium, atau kantor sesuai kebutuhan baca Anda.';
        }

        $roomNames = $context['scope']['assigned_room_names'] ?? [];

        if ($roomNames === []) {
            return 'Akun ini belum memiliki penugasan ruangan aktif. Silakan hubungi wali kelas atau pengelola sistem jika ruangan seharusnya sudah ditetapkan.';
        }

        if ($level === 1) {
            return 'Kamu bisa membuka menu '.$this->formatMenuName('Kelas Saya').' untuk melihat data kelas dan ruangan yang tersedia. Saat ini kelas yang terhubung ke akunmu adalah '.implode(', ', $roomNames).'.';
        }

        if ($level === 2) {
            return 'Kamu bisa membuka menu '.$this->formatMenuName('Kelas Binaan').' untuk melihat data kelas yang tersedia. Saat ini kelas binaan yang terhubung ke akunmu adalah '.implode(', ', $roomNames).'.';
        }

        return 'Ruang lingkup yang dapat kamu akses saat ini: '.implode(', ', $roomNames).'.';
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function buildInventoryAnswer(array $context, string $message): string
    {
        $level = (int) ($context['role']['level'] ?? 0);
        $text = mb_strtolower($message);
        $wantsDetail = $this->containsAny($text, ['semua data barang', 'semua barang', 'detail barang', 'data barang', 'barang di kelas saya', 'inventaris kelas saya']);

        if ($level === 4) {
            $mentionedLabels = $this->mentionedClassLabels($text);
            $mentionedRooms = $this->mentionedClassRooms($text);

            if ($this->containsAny($text, [
                'total seluruh barang dari semua kelas',
                'total seluruh barang semua kelas',
                'total semua barang dari semua kelas',
                'total semua barang semua kelas',
                'total barang semua kelas',
                'total barang seluruh kelas',
                'total seluruh barang',
            ])) {
                return $this->buildAllClassInventoryTotalAnswer();
            }

            if ($mentionedRooms->isNotEmpty()) {
                return $this->buildClassInventoryDetailAnswer($mentionedRooms);
            }

            if ($mentionedLabels !== []) {
                return 'Inventaris untuk kelas yang Anda minta belum ditemukan di sistem. Silakan periksa kembali nama kelasnya.';
            }
        }

        if ($level === 3) {
            $summary = DB::table('inventaris_ruangan')
                ->selectRaw('COALESCE(SUM(jumlah_baik), 0) as total_baik, COALESCE(SUM(jumlah_rusak), 0) as total_rusak')
                ->first();

            return sprintf(
                'Ringkasan inventaris sekolah saat ini: %d barang kondisi baik dan %d barang kondisi rusak. Saya bisa bantu lanjutkan ke ringkasan per ruangan atau per kategori tanpa menampilkan database mentah.',
                (int) ($summary->total_baik ?? 0),
                (int) ($summary->total_rusak ?? 0)
            );
        }

        $roomIds = $context['scope']['assigned_room_ids'] ?? [];

        if ($roomIds === []) {
            return 'Belum ada inventaris yang bisa ditampilkan karena akun ini belum memiliki ruangan aktif.';
        }

        $summary = DB::table('inventaris_ruangan')
            ->join('ruangan as r', 'r.id_ruangan', '=', 'inventaris_ruangan.id_ruangan')
            ->whereIn('inventaris_ruangan.id_ruangan', $roomIds)
            ->selectRaw('r.nama_ruangan, COALESCE(SUM(jumlah_baik), 0) as total_baik, COALESCE(SUM(jumlah_rusak), 0) as total_rusak')
            ->groupBy('r.nama_ruangan')
            ->orderBy('r.nama_ruangan')
            ->get();

        if ($summary->isEmpty()) {
            return 'Belum ada data inventaris yang tercatat untuk lingkup ruanganmu.';
        }

        if ($level === 1 && ! $wantsDetail) {
            return 'Kamu bisa melihat inventaris kelasmu melalui menu '.$this->formatMenuName('Kelas Saya').' atau '.$this->formatMenuName('Inventaris Kelas').'. Dari sana tersedia ringkasan dan daftar barang yang bisa kamu akses.';
        }

        if ($level === 2 && ! $wantsDetail) {
            return 'Kamu bisa melihat inventaris kelas binaanmu melalui menu '.$this->formatMenuName('Inventaris Kelas').' untuk melihat ringkasan dan detail barang yang tersedia.';
        }

        if ($wantsDetail) {
            $details = DB::table('inventaris_ruangan as ir')
                ->join('barang as b', 'b.id_barang', '=', 'ir.id_barang')
                ->join('ruangan as r', 'r.id_ruangan', '=', 'ir.id_ruangan')
                ->whereIn('ir.id_ruangan', $roomIds)
                ->orderBy('r.nama_ruangan')
                ->orderBy('b.nama_barang')
                ->get([
                    'r.nama_ruangan',
                    'b.nama_barang',
                    'ir.jumlah_baik',
                    'ir.jumlah_rusak',
                ]);

            $lines = $details->map(fn ($row) => $row->nama_ruangan.': '.ucfirst($row->nama_barang).' (baik '.$row->jumlah_baik.', rusak '.$row->jumlah_rusak.')')->all();

            return 'Berikut data barang dalam kelasmu: '.implode(' | ', $lines).'.';
        }

        $lines = $summary->map(fn ($row) => $row->nama_ruangan.': '.$row->total_baik.' baik, '.$row->total_rusak.' rusak')->all();

        return 'Ringkasan inventaris dalam aksesmu: '.implode(' | ', $lines).'.';
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function buildRequestAnswer(array $context): string
    {
        $level = (int) ($context['role']['level'] ?? 0);
        $userId = (int) ($context['user']['id_user'] ?? 0);

        if ($level === 1) {
            $requests = DB::table('permintaan')
                ->where('id_user_peminta', $userId)
                ->selectRaw('COUNT(*) as total, SUM(CASE WHEN status_permintaan NOT IN ("selesai", "ditolak_admin", "ditolak_owner", "ditolak") THEN 1 ELSE 0 END) as aktif')
                ->first();

            return 'Kamu bisa membuka menu '.$this->formatMenuName('Pengajuan').' atau '.$this->formatMenuName('Riwayat Pengajuan').' untuk melihat status permintaanmu. Saat ini ada '.(int) ($requests->aktif ?? 0).' pengajuan aktif dari total '.(int) ($requests->total ?? 0).' pengajuan.';
        }

        if ($level === 2) {
            $roomIds = $context['scope']['assigned_room_ids'] ?? [];

            $requests = DB::table('permintaan')
                ->whereIn('id_ruangan', $roomIds)
                ->selectRaw('COUNT(*) as total, SUM(CASE WHEN status_permintaan = "diajukan" THEN 1 ELSE 0 END) as menunggu')
                ->first();

            return 'Kamu bisa cek menu '.$this->formatMenuName('Pengajuan Masuk').' untuk melihat permintaan dari kelas binaanmu. Saat ini terdapat '.(int) ($requests->total ?? 0).' pengajuan, dengan '.(int) ($requests->menunggu ?? 0).' yang masih menunggu tindak lanjut.';
        }

        $requests = DB::table('permintaan')
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN status_permintaan IN ("diajukan", "diverifikasi_admin", "disetujui_admin", "disetujui_owner") THEN 1 ELSE 0 END) as aktif')
            ->first();

        return 'Ringkasan pengajuan seluruh sistem: '.(int) ($requests->aktif ?? 0).' pengajuan aktif dari total '.(int) ($requests->total ?? 0).' data permintaan.';
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function buildGlobalReadAnswer(array $context, string $message): string
    {
        $level = (int) ($context['role']['level'] ?? 0);
        $text = mb_strtolower($message);

        if (! in_array($level, [3, 4], true)) {
            return 'Maaf, akunmu tidak memiliki akses untuk melihat data lintas kelas atau seluruh sekolah.';
        }

        if ($this->containsAny($text, ['siswa', 'user'])) {
            $students = (int) DB::table('users')->where('level', 1)->count();

            return 'Saat ini terdapat '.$students.' akun siswa/ketua kelas yang terdaftar. Saya bisa bantu lanjutkan ke ringkasan per level atau nama pengguna tanpa membuka data sensitif.';
        }

        if ($this->containsAny($text, ['barang', 'inventaris'])) {
            $items = DB::table('inventaris_ruangan')
                ->selectRaw('COALESCE(SUM(jumlah_baik + jumlah_rusak), 0) as total')
                ->first();

            return 'Total inventaris tercatat di seluruh sistem adalah '.(int) ($items->total ?? 0).' unit.';
        }

        if ($this->containsAny($text, ['ruangan', 'kelas'])) {
            $rooms = (int) DB::table('ruangan')->count();

            return 'Total ruangan yang tercatat saat ini adalah '.$rooms.' ruangan.';
        }

        return 'Saya bisa membantu menampilkan ringkasan global untuk siswa, inventaris, ruangan, dan pengajuan sesuai hak akses baca akun ini.';
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function buildWriteScopeAnswer(array $context): string
    {
        $level = (int) ($context['role']['level'] ?? 0);

        if ($level === 3) {
            return 'Akun superadmin memiliki hak aksi tertinggi. Saat ini chatbot sudah mulai mendukung aksi eksplisit untuk pengelolaan user, dan aksi sistem lain bisa ditambahkan bertahap dengan validasi backend.';
        }

        return 'Akun ini hanya memiliki akses baca atau bantuan terbatas, jadi aksi tambah, ubah, dan hapus tidak tersedia lewat chatbot.';
    }

    private function buildCreateUserAnswer(string $message): string
    {
        if (! preg_match('/tambah user\s+nama\s+([a-z0-9_\-\s]+)\s+level\s+([1-4])\s+password\s+([^\s]+)/i', $message, $matches)) {
            return 'Format tambah user belum sesuai. Gunakan: tambah user nama NAMA level 1-4 password PASSWORD';
        }

        $name = trim($matches[1]);
        $level = (int) $matches[2];
        $password = trim($matches[3]);

        if (DB::table('users')->whereRaw('LOWER(nama) = ?', [mb_strtolower($name)])->exists()) {
            return 'User dengan nama '.$name.' sudah ada.';
        }

        DB::table('users')->insert([
            'nis' => null,
            'nama' => $name,
            'password' => Hash::make($password),
            'level' => $level,
        ]);

        return 'User '.$name.' berhasil ditambahkan dengan level '.$level.'.';
    }

    private function buildUpdateUserLevelAnswer(string $message): string
    {
        if (! preg_match('/ubah level user\s+([a-z0-9_\-\s]+)\s+(jadi|menjadi)\s+([1-4])/i', $message, $matches)) {
            return 'Format ubah level user belum sesuai. Gunakan: ubah level user NAMA jadi 1-4';
        }

        $name = trim($matches[1]);
        $level = (int) $matches[3];
        $user = DB::table('users')->whereRaw('LOWER(nama) = ?', [mb_strtolower($name)])->first();

        if (! $user) {
            return 'User '.$name.' tidak ditemukan.';
        }

        DB::table('users')
            ->where('id_user', $user->id_user)
            ->update(['level' => $level]);

        return 'Level user '.$name.' berhasil diubah menjadi '.$level.'.';
    }

    private function buildDeleteUserAnswer(string $message): string
    {
        if (! preg_match('/hapus user\s+([a-z0-9_\-\s]+)/i', $message, $matches)) {
            return 'Format hapus user belum sesuai. Gunakan: hapus user NAMA';
        }

        $name = trim($matches[1]);
        $user = DB::table('users')->whereRaw('LOWER(nama) = ?', [mb_strtolower($name)])->first();

        if (! $user) {
            return 'User '.$name.' tidak ditemukan.';
        }

        if ((int) $user->level === 4) {
            return 'User owner tidak dapat dihapus lewat chatbot.';
        }

        DB::table('users')->where('id_user', $user->id_user)->delete();

        return 'User '.$name.' berhasil dihapus.';
    }

    private function matchesCreateUserIntent(string $text): bool
    {
        return preg_match('/tambah user\s+nama\s+.+\s+level\s+[1-4]\s+password\s+\S+/i', $text) === 1;
    }

    private function matchesUpdateUserLevelIntent(string $text): bool
    {
        return preg_match('/ubah level user\s+.+\s+(jadi|menjadi)\s+[1-4]/i', $text) === 1;
    }

    private function matchesDeleteUserIntent(string $text): bool
    {
        return preg_match('/hapus user\s+.+/i', $text) === 1;
    }

    /**
     * @return array<int, string>
     */
    private function extractMentionedScopes(string $text): array
    {
        return $this->mentionedClassRooms($text)
            ->map(fn ($room) => $this->normalizeScopeToken((string) $room->nama_ruangan))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<int, string>  $mentionedScope
     */
    private function mentionsOutsideAssignedScope(array $context, array $mentionedScope): bool
    {
        if ($mentionedScope === []) {
            return false;
        }

        $assignedNames = collect($context['scope']['assigned_room_names'] ?? [])
            ->map(fn ($value) => strtoupper(str_replace(' ', '', (string) $value)));
        $assignedCodes = collect($context['scope']['assigned_room_codes'] ?? [])
            ->map(fn ($value) => strtoupper(str_replace([' ', 'KLS-'], '', (string) $value)));

        foreach ($mentionedScope as $scope) {
            $normalized = strtoupper(str_replace(' ', '', $scope));

            if ($assignedNames->contains($normalized) || $assignedCodes->contains($normalized)) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<int, string>  $mentionedScope
     */
    private function mentionsAssignedScope(array $context, array $mentionedScope): bool
    {
        if ($mentionedScope === []) {
            return false;
        }

        $assignedNames = collect($context['scope']['assigned_room_names'] ?? [])
            ->map(fn ($value) => strtoupper(str_replace([' ', 'KELAS'], '', (string) $value)));
        $assignedCodes = collect($context['scope']['assigned_room_codes'] ?? [])
            ->map(fn ($value) => strtoupper(str_replace([' ', 'KLS-'], '', (string) $value)));

        foreach ($mentionedScope as $scope) {
            $normalized = strtoupper(str_replace([' ', 'KELAS'], '', $scope));

            if ($assignedNames->contains($normalized) || $assignedCodes->contains($normalized)) {
                return true;
            }
        }

        return false;
    }

    private function containsAny(string $text, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($text, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function isInfraSPHRelatedText(string $text): bool
    {
        return $this->containsAny($text, [
            'infrasph',
            'kelas',
            'ruangan',
            'inventaris',
            'barang',
            'pengajuan',
            'permintaan',
            'akun',
            'password',
            'dashboard',
            'menu',
            'fitur',
            'akses',
            'laporan',
            'verifikasi',
            'realisasi',
            'data',
            'wali kelas',
            'kepala sekolah',
            'owner',
            'superadmin',
            'pengelola sistem',
            'sistem',
            'login',
            'riwayat',
            'status',
            'ajukan',
            'kelas binaan',
            'kelas saya',
            'inventaris kelas',
            'penggunaan dashboard',
        ]);
    }

    /**
     * @return \Illuminate\Support\Collection<int, object>
     */
    private function classRoomCatalog()
    {
        return DB::table('ruangan')
            ->where('jenis_ruangan', 'kelas')
            ->orderBy('nama_ruangan')
            ->get(['id_ruangan', 'nama_ruangan', 'kode_ruangan', 'jenis_ruangan']);
    }

    private function normalizeScopeToken(string $value): string
    {
        $normalized = mb_strtoupper($value);
        $normalized = str_replace([' ', '-', '_'], '', $normalized);

        return preg_replace('/[^A-Z0-9]/', '', $normalized) ?? '';
    }

    /**
     * @return array<string, object>
     */
    private function classRoomAliasMap(): array
    {
        $aliasMap = [];

        foreach ($this->classRoomCatalog() as $room) {
            $nameAlias = $this->normalizeScopeToken((string) $room->nama_ruangan);
            $codeAlias = $this->normalizeScopeToken((string) $room->kode_ruangan);
            $aliases = [$nameAlias, $codeAlias];

            if (str_starts_with($nameAlias, 'KELAS')) {
                $aliases[] = substr($nameAlias, 5);
            }

            if (str_starts_with($codeAlias, 'KLS')) {
                $aliases[] = substr($codeAlias, 3);
            }

            $aliases = array_merge($aliases, $this->numericAliasesForClass((string) $room->nama_ruangan));

            foreach (array_filter(array_unique($aliases)) as $alias) {
                $aliasMap[$alias] = $room;
            }
        }

        uksort($aliasMap, fn ($left, $right) => strlen($right) <=> strlen($left));

        return $aliasMap;
    }

    /**
     * @return \Illuminate\Support\Collection<int, object>
     */
    private function mentionedClassRooms(string $message)
    {
        $matchedRooms = collect();
        $aliasMap = $this->classRoomAliasMap();

        foreach ($this->mentionedClassLabels($message) as $match) {
            $alias = $this->normalizeScopeToken($match);

            if (str_starts_with($alias, 'KELAS')) {
                $alias = substr($alias, 5);
            }

            if (isset($aliasMap[$alias])) {
                $matchedRooms->push($aliasMap[$alias]);
            }
        }

        return $matchedRooms
            ->unique(fn ($room) => (int) $room->id_ruangan)
            ->values();
    }

    /**
     * @return array<int, string>
     */
    private function mentionedClassLabels(string $message): array
    {
        preg_match_all(
            '/\b(?:kelas\s*[7-9][a-c]|[7-9][a-c]|rpl\s*xiib|rpl\s*xiia|rpl\s*xii|rpl\s*xi|rpl\s*x|rpl\s*12b|rpl\s*12a|rpl\s*12|rpl\s*11|rpl\s*10|bdp\s*xii|bdp\s*xi|bdp\s*x|bdp\s*12|bdp\s*11|bdp\s*10|akl\s*xiib|akl\s*xiia|akl\s*xii|akl\s*xi|akl\s*x|akl\s*12b|akl\s*12a|akl\s*12|akl\s*11|akl\s*10)\b/i',
            $message,
            $matches
        );

        return array_values(array_unique($matches[0] ?? []));
    }

    /**
     * @return array<int, string>
     */
    private function numericAliasesForClass(string $roomName): array
    {
        $normalized = $this->normalizeScopeToken($roomName);

        return match ($normalized) {
            'RPLX' => ['RPL10'],
            'RPLXI' => ['RPL11'],
            'RPLXIIA' => ['RPL12A', 'RPL12'],
            'RPLXIIB' => ['RPL12B'],
            'BDPX' => ['BDP10'],
            'BDPXI' => ['BDP11'],
            'BDPXII' => ['BDP12'],
            'AKLX' => ['AKL10'],
            'AKLXI' => ['AKL11'],
            'AKLXII' => ['AKL12'],
            default => [],
        };
    }

    private function buildAllClassListAnswer(): string
    {
        $classNames = $this->classRoomCatalog()
            ->pluck('nama_ruangan')
            ->map(fn ($name) => (string) $name)
            ->all();

        return 'Berikut daftar kelas yang saat ini tercatat di sistem: '.implode(', ', $classNames).'.';
    }

    /**
     * @param  \Illuminate\Support\Collection<int, object>  $rooms
     */
    private function buildClassRoomDetailAnswer($rooms): string
    {
        $inventorySummary = DB::table('inventaris_ruangan')
            ->whereIn('id_ruangan', $rooms->pluck('id_ruangan')->all())
            ->selectRaw('id_ruangan, COALESCE(SUM(jumlah_baik + jumlah_rusak), 0) as total_barang')
            ->selectRaw('COALESCE(SUM(jumlah_baik), 0) as total_baik')
            ->selectRaw('COALESCE(SUM(jumlah_rusak), 0) as total_rusak')
            ->groupBy('id_ruangan')
            ->get()
            ->keyBy('id_ruangan');

        $lines = $rooms->map(function ($room) use ($inventorySummary) {
            $summary = $inventorySummary->get($room->id_ruangan);

            return sprintf(
                '%s (%s): total %d barang, %d kondisi baik, %d perlu perhatian',
                $room->nama_ruangan,
                $room->kode_ruangan,
                (int) ($summary->total_barang ?? 0),
                (int) ($summary->total_baik ?? 0),
                (int) ($summary->total_rusak ?? 0)
            );
        })->all();

        return 'Data kelas yang Anda minta: '.implode(' | ', $lines).'.';
    }

    /**
     * @param  \Illuminate\Support\Collection<int, object>  $rooms
     */
    private function buildClassInventoryDetailAnswer($rooms): string
    {
        $details = DB::table('inventaris_ruangan as ir')
            ->join('barang as b', 'b.id_barang', '=', 'ir.id_barang')
            ->join('ruangan as r', 'r.id_ruangan', '=', 'ir.id_ruangan')
            ->whereIn('ir.id_ruangan', $rooms->pluck('id_ruangan')->all())
            ->orderBy('r.nama_ruangan')
            ->orderBy('b.nama_barang')
            ->get([
                'r.id_ruangan',
                'r.nama_ruangan',
                'b.nama_barang',
                'ir.jumlah_baik',
                'ir.jumlah_rusak',
            ])
            ->groupBy('id_ruangan');

        $lines = $rooms->map(function ($room) use ($details) {
            $items = collect($details->get($room->id_ruangan, []))
                ->map(function ($item) {
                    $total = (int) $item->jumlah_baik + (int) $item->jumlah_rusak;

                    return ucfirst((string) $item->nama_barang).' '.$total;
                })
                ->implode(', ');

            return $room->nama_ruangan.': '.($items !== '' ? $items : 'belum ada inventaris tercatat');
        })->all();

        return 'Berikut data inventaris kelas yang Anda minta: '.implode(' | ', $lines).'.';
    }

    private function buildAllClassInventoryTotalAnswer(): string
    {
        $summary = DB::table('inventaris_ruangan as ir')
            ->join('ruangan as r', 'r.id_ruangan', '=', 'ir.id_ruangan')
            ->where('r.jenis_ruangan', 'kelas')
            ->selectRaw('COALESCE(SUM(ir.jumlah_baik + ir.jumlah_rusak), 0) as total_barang')
            ->selectRaw('COALESCE(SUM(ir.jumlah_baik), 0) as total_baik')
            ->selectRaw('COALESCE(SUM(ir.jumlah_rusak), 0) as total_rusak')
            ->first();

        return sprintf(
            'Total seluruh barang dari semua kelas saat ini adalah %d barang, dengan %d kondisi baik dan %d perlu perhatian.',
            (int) ($summary->total_barang ?? 0),
            (int) ($summary->total_baik ?? 0),
            (int) ($summary->total_rusak ?? 0)
        );
    }

    private function formatMenuName(string $menuName): string
    {
        return '['.$menuName.']';
    }

    /**
     * @param  array<int, string>  $menuNames
     */
    private function formatMenuList(array $menuNames): string
    {
        $formatted = array_map(fn (string $menuName) => $this->formatMenuName($menuName), $menuNames);
        $count = count($formatted);

        if ($count === 0) {
            return '';
        }

        if ($count === 1) {
            return $formatted[0];
        }

        if ($count === 2) {
            return $formatted[0].' dan '.$formatted[1];
        }

        $last = array_pop($formatted);

        return implode(', ', $formatted).', dan '.$last;
    }
}
