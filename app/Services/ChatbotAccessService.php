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
        ];
    }

    /**
     * Process a chatbot message with role-based access control.
     *
     * @param  array<string, mixed>  $sessionUser
     * @param  array<int, array{role: string, message: string}>  $history
     * @return array<string, mixed>
     */
    public function respond(array $sessionUser, string $message, array $history = []): array
    {
        $context = $this->buildContext($sessionUser);
        $intent = $this->detectIntent($message);
        $decision = $this->authorizeIntent($context, $intent, $message);

        if (! $decision['allowed']) {
            return [
                'message' => $decision['message'],
                'intent' => $intent,
                'allowed' => false,
                'context' => $context,
                'grounded_message' => $decision['message'],
                'ai' => [
                    'enabled' => $this->aiEnabled(),
                    'used' => false,
                    'fallback_reason' => 'access_denied',
                ],
            ];
        }

        $groundedAnswer = $this->buildAnswer($context, $intent, $message);
        $finalMessage = $groundedAnswer;
        $aiUsed = false;
        $fallbackReason = null;

        if ($this->aiEnabled()) {
            $aiReply = $this->geminiChatService->generateReply($message, $context, $intent, $groundedAnswer, $history);

            if (filled($aiReply)) {
                $finalMessage = $aiReply;
                $aiUsed = true;
            } else {
                $fallbackReason = $this->geminiChatService->lastFailureReason() ?? 'ai_unavailable';
                $finalMessage = $this->buildFallbackMessage($groundedAnswer, $fallbackReason);
            }
        } else {
            $fallbackReason = 'ai_not_configured';
            $finalMessage = $this->buildFallbackMessage($groundedAnswer, $fallbackReason);
        }

        return [
            'message' => $finalMessage,
            'intent' => $intent,
            'allowed' => true,
            'context' => $context,
            'grounded_message' => $groundedAnswer,
            'ai' => [
                'enabled' => $this->aiEnabled(),
                'used' => $aiUsed,
                'fallback_reason' => $fallbackReason,
            ],
        ];
    }

    public function aiEnabled(): bool
    {
        return $this->geminiChatService->isConfigured();
    }

    private function buildFallbackMessage(string $groundedAnswer, ?string $fallbackReason): string
    {
        return $groundedAnswer;
    }

    /**
     * @return array<string, mixed>
     */
    private function mapRole(int $level): array
    {
        return match ($level) {
            1 => ['level' => 1, 'name' => 'Ketua Kelas'],
            2 => ['level' => 2, 'name' => 'Admin / Wali Kelas'],
            3 => ['level' => 3, 'name' => 'Superadmin'],
            4 => ['level' => 4, 'name' => 'Owner'],
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
            4 => 'Akses baca ke seluruh data sekolah tanpa izin tambah, ubah, atau hapus.',
            default => 'Akses belum dikenali.',
        };
    }

    private function detectIntent(string $message): string
    {
        $text = mb_strtolower($message);

        if ($this->containsAny($text, ['apa kabar', 'gimana kabar', 'bagaimana kabar', 'kabarmu'])) {
            return 'wellbeing';
        }

        if ($this->containsAny($text, ['halo', 'hallo', 'hai', 'hi', 'selamat pagi', 'selamat siang', 'selamat sore', 'selamat malam'])) {
            return 'greeting';
        }

        if ($this->containsAny($text, ['hmm', 'hm', 'bingung', 'kurang paham', 'ga paham', 'gak paham', 'nggak paham'])) {
            return 'confused';
        }

        if ($this->containsAny($text, ['bosan', 'gabut', 'gabut nih', 'iseng', 'lagi iseng'])) {
            return 'small_talk';
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

        if (preg_match('/^[a-z]{4,}$/', trim($text)) && ! str_contains($text, ' ')) {
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
                'message' => 'Maaf, akunmu belum memiliki penugasan kelas atau ruangan aktif. Hubungi superadmin untuk mengatur akses kelasmu.',
            ];
        }

        if (in_array($intent, ['create_user', 'update_user_level', 'delete_user'], true)) {
            if ((int) ($context['role']['level'] ?? 0) !== 3) {
                return [
                    'allowed' => false,
                    'message' => 'Maaf, aksi pengelolaan data hanya tersedia untuk akun superadmin.',
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
            'greeting' => $this->buildGreetingAnswer($context),
            'confused' => $this->buildConfusedAnswer($context),
            'small_talk' => $this->buildSmallTalkAnswer($context),
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
            'room_lookup' => $this->buildRoomAnswer($context),
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
    private function buildGreetingAnswer(array $context): string
    {
        return 'Halo, '.$context['user']['nama'].'. Saya siap membantu. Kalau ada yang ingin ditanyakan soal kelas, inventaris, atau pengajuan, langsung saja.';
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function buildConfusedAnswer(array $context): string
    {
        return 'Tidak apa-apa, '.$context['user']['nama'].'. Kalau masih bingung, coba tulis pertanyaannya pelan-pelan atau sebutkan topiknya, nanti saya bantu jelaskan.';
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function buildSmallTalkAnswer(array $context): string
    {
        return 'Santai saja, '.$context['user']['nama'].'. Kalau nanti ada yang ingin dicek di InfraSPH, tinggal tanya saya.';
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
        return 'Sama-sama, '.$context['user']['nama'].'. Kalau masih ada yang ingin dicek, saya siap bantu lagi.';
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function buildIdentityAnswer(array $context): string
    {
        $roleName = $context['role']['name'] ?? 'Pengguna';

        return 'Saya adalah asisten AI InfraSPH. Saya membantu menjawab pertanyaan umum, mengarahkan penggunaan fitur, dan menampilkan informasi sistem sesuai akses akun '.$roleName.' kamu.';
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
            3 => $base.' Untuk superadmin, saya juga bisa membantu ringkasan global sistem dan konteks operasional yang lebih luas.',
            4 => $base.' Untuk owner, saya bisa membantu akses baca seluruh data sekolah tanpa aksi tambah, ubah, atau hapus.',
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
        return 'Sepertinya ada salah ketik atau pesannya belum jelas. Bisa tulis ulang pertanyaannya?';
    }

    /**
     * @return string
     */
    private function buildOutOfScopeAnswer(): string
    {
        return 'Maaf, saya tidak bisa membantu untuk permintaan itu. Saya fokus membantu penggunaan sistem InfraSPH, seperti inventaris, ruangan, pengajuan, data kelas yang sesuai akses, dan navigasi dashboard.';
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function buildHelpAnswer(array $context): string
    {
        return 'Saya siap membantu soal inventaris, ruangan, pengajuan, dan penggunaan dashboard sesuai akses akunmu.';
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function buildRoomAnswer(array $context): string
    {
        $level = (int) ($context['role']['level'] ?? 0);

        if (in_array($level, [3, 4], true)) {
            $totalRooms = (int) DB::table('ruangan')->count();

            return 'Saat ini sistem memiliki '.$totalRooms.' ruangan terdaftar. Saya bisa bantu arahkan ke data kelas, laboratorium, atau kantor sesuai kebutuhan baca Anda.';
        }

        $roomNames = $context['scope']['assigned_room_names'] ?? [];

        if ($roomNames === []) {
            return 'Akun ini belum memiliki penugasan ruangan aktif. Silakan hubungi superadmin jika ruangan seharusnya sudah ditetapkan.';
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

        if (in_array($level, [3, 4], true)) {
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

            return 'Status pengajuanmu saat ini: '.(int) ($requests->aktif ?? 0).' pengajuan aktif dari total '.(int) ($requests->total ?? 0).' pengajuan.';
        }

        if ($level === 2) {
            $roomIds = $context['scope']['assigned_room_ids'] ?? [];

            $requests = DB::table('permintaan')
                ->whereIn('id_ruangan', $roomIds)
                ->selectRaw('COUNT(*) as total, SUM(CASE WHEN status_permintaan = "diajukan" THEN 1 ELSE 0 END) as menunggu')
                ->first();

            return 'Dalam lingkup wali kelasmu terdapat '.(int) ($requests->total ?? 0).' pengajuan, dengan '.(int) ($requests->menunggu ?? 0).' yang masih menunggu tindak lanjut.';
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
        preg_match_all('/\b(?:kelas\s*)?([7-9][a-c]|rpl\s*xii?a?|rpl\s*xiib|bdp\s*xii?|akl\s*xii?)\b/i', $text, $matches);

        return collect($matches[1] ?? [])
            ->map(fn ($value) => strtoupper(str_replace(' ', '', trim($value))))
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
}
