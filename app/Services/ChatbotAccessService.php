<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class ChatbotAccessService
{
    public function __construct(
        private GroqChatService $groqChatService,
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
            $aiReply = null;

            if ($this->groqChatService->isConfigured()) {
                $aiReply = $this->groqChatService->generateReply($message, $context, $intent, $groundedAnswer, $history);

                if (filled($aiReply)) {
                    $finalMessage = $aiReply;
                    $aiUsed = true;
                } else {
                    $fallbackReason = $this->groqChatService->lastFailureReason() ?? 'ai_unavailable';
                }
            }

            if (! $aiUsed && $this->geminiChatService->isConfigured()) {
                $aiReply = $this->geminiChatService->generateReply($message, $context, $intent, $groundedAnswer, $history);

                if (filled($aiReply)) {
                    $finalMessage = $aiReply;
                    $aiUsed = true;
                    $fallbackReason = null;
                } else {
                    $fallbackReason = $this->geminiChatService->lastFailureReason() ?? $fallbackReason ?? 'ai_unavailable';
                }
            }

            if (! $aiUsed) {
                $finalMessage = $this->buildFallbackMessage($groundedAnswer, $intent, $fallbackReason);
            }
        } else {
            $fallbackReason = 'ai_not_configured';
            $finalMessage = $this->buildFallbackMessage($groundedAnswer, $intent, $fallbackReason);
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
        return $this->groqChatService->isConfigured() || $this->geminiChatService->isConfigured();
    }

    private function buildFallbackMessage(string $groundedAnswer, string $intent, ?string $fallbackReason): string
    {
        if ($fallbackReason === 'quota_exceeded') {
            return 'Layanan AI sedang tidak bisa dipakai karena kuota Gemini API habis. Percakapan masih tersimpan di sesi ini, tetapi jawaban pintar baru akan normal lagi setelah API key atau kuota Gemini diperbaiki.';
        }

        if ($fallbackReason === 'ai_not_configured' || $fallbackReason === 'not_configured') {
            return 'Layanan AI belum terkonfigurasi dengan benar. Setelah API key valid dipasang, chatbot bisa menjawab lebih natural dan mengingat konteks percakapan sesi ini.';
        }

        if (in_array($fallbackReason, ['connection_error', 'request_failed'], true)) {
            return 'Layanan AI sedang bermasalah saat dihubungi. Coba lagi sebentar lagi.';
        }

        if (in_array($intent, ['conversation', 'general_help', 'unclear_text'], true)) {
            return 'Saya siap menanggapi pesanmu. Coba lanjutkan atau tulis sedikit lebih jelas, nanti saya jawab dengan lebih pas.';
        }

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

        if ($this->looksLikeConversationMessage($text)) {
            return 'conversation';
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

        if ($this->looksLikeMathQuestion($text)) {
            return 'math_question';
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
            'conversation' => $this->buildConversationAnswer(),
            'cancel' => $this->buildCancelAnswer($context),
            'gratitude' => $this->buildGratitudeAnswer($context),
            'identity' => $this->buildIdentityAnswer($context),
            'capabilities' => $this->buildCapabilitiesAnswer($context),
            'goodbye' => $this->buildGoodbyeAnswer($context),
            'acknowledgement' => $this->buildAcknowledgementAnswer($context),
            'no_followup' => $this->buildNoFollowupAnswer($context),
            'unclear_text' => $this->buildUnclearTextAnswer(),
            'math_question' => $this->buildMathAnswer($message),
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
        return 'Halo, '.$context['user']['nama'].'. Saya baik, terima kasih. Kalau ada yang ingin kamu tanyakan, baik soal pelajaran sekolah maupun fitur InfraSPH, saya siap membantu.';
    }

    private function buildConversationAnswer(): string
    {
        return 'Tanggapi pesan user secara natural, santai, dan sesuai konteks percakapan.';
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
        return 'Sama-sama, '.$context['user']['nama'].'. Kalau masih ada soal pelajaran atau hal di InfraSPH yang ingin dicek, saya siap bantu lagi.';
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function buildIdentityAnswer(array $context): string
    {
        $roleName = $context['role']['name'] ?? 'Pengguna';

        return 'Saya adalah tutor AI dan asisten InfraSPH. Saya bisa membantu menjawab pertanyaan pelajaran sekolah, pertanyaan umum, serta penggunaan sistem sesuai akses akun '.$roleName.' kamu.';
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function buildCapabilitiesAnswer(array $context): string
    {
        $level = (int) ($context['role']['level'] ?? 0);
        $base = 'Saya bisa membantu menjawab materi pelajaran sekolah, menjelaskan langkah pengerjaan soal, serta membantu fitur dashboard dan ringkasan data sesuai hak akses akunmu.';

        return match ($level) {
            1 => $base.' Untuk akunmu, akses sistem saya fokus pada data diri sendiri, ruangan yang ditugaskan, dan pengajuan milikmu.',
            2 => $base.' Untuk akun wali kelas, akses sistem saya mencakup data kelas sendiri dan lingkup penugasan wali kelas.',
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
        return 'Jika maksud user belum jelas, minta klarifikasi secara santai dan singkat.';
    }

    private function buildMathAnswer(string $message): string
    {
        $expression = $this->extractMathExpression($message);

        if ($expression === null) {
            return 'Saya siap bantu matematika. Coba kirim soalnya lebih jelas, misalnya `12 + 8` atau `berapa 24 dibagi 6`.';
        }

        $result = $this->evaluateMathExpression($expression);

        if ($result === null) {
            return 'Saya belum bisa menghitung bentuk soal itu secara otomatis. Coba kirim ekspresi yang lebih sederhana, misalnya `1+1`, `12 x 8`, atau `24 : 6`.';
        }

        $formattedResult = fmod($result, 1.0) === 0.0
            ? number_format($result, 0, ',', '.')
            : rtrim(rtrim(number_format($result, 10, '.', ''), '0'), '.');

        return 'Jawabannya '.$formattedResult.'.';
    }

    /**
     * @return string
     */
    private function buildOutOfScopeAnswer(): string
    {
        return 'Maaf, saya belum bisa membantu untuk permintaan itu. Saya paling siap membantu materi pelajaran sekolah, pertanyaan umum, dan penggunaan InfraSPH sesuai akses akun.';
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function buildHelpAnswer(array $context): string
    {
        return 'Saya bisa membantu materi pelajaran sekolah, pertanyaan umum, dan fitur InfraSPH sesuai akses akunmu.';
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

    private function looksLikeMathQuestion(string $text): bool
    {
        if ($this->containsAny($text, ['matematika', 'hitung', 'berapa hasil', 'hasil dari', 'jumlahkan', 'kurangkan', 'kali', 'dibagi'])) {
            return true;
        }

        return preg_match('/^\s*[-+()0-9xX*\/:.,\s]+\s*=?\s*$/', $text) === 1
            && preg_match('/\d/', $text) === 1;
    }

    private function looksLikeConversationMessage(string $text): bool
    {
        $trimmed = trim($text);

        if ($trimmed === '') {
            return false;
        }

        if (mb_strlen($trimmed) > 80) {
            return false;
        }

        if ($this->looksLikeMathQuestion($trimmed)) {
            return false;
        }

        return ! $this->containsAny($trimmed, [
            'database', 'sql', 'query', 'tabel', 'schema',
            'pengajuan', 'permintaan', 'inventaris', 'barang',
            'kelas', 'ruangan', 'tambah', 'tambahkan', 'ubah',
            'edit', 'hapus', 'delete', 'perbarui', 'update',
            'menu', 'fitur',
        ]);
    }

    private function extractMathExpression(string $message): ?string
    {
        $expression = mb_strtolower(trim($message));
        $expression = str_replace(['×', 'x', ':'], ['*', '*', '/'], $expression);
        $expression = str_replace(',', '.', $expression);
        $expression = preg_replace('/\b(berapa|hasil|dari|adalah|hitung|berapa hasil)\b/u', ' ', $expression);
        $expression = preg_replace('/[^0-9\.\+\-\*\/\(\)\s]/', ' ', $expression);
        $expression = preg_replace('/\s+/', ' ', (string) $expression);
        $expression = trim((string) $expression);

        if ($expression === '' || preg_match('/\d/', $expression) !== 1) {
            return null;
        }

        return $expression;
    }

    private function evaluateMathExpression(string $expression): ?float
    {
        preg_match_all('/\d+(?:\.\d+)?|[()+\-*\/]/', $expression, $matches);
        $tokens = $matches[0] ?? [];

        if ($tokens === []) {
            return null;
        }

        $index = 0;
        $value = $this->parseMathExpression($tokens, $index);

        if ($value === null || $index !== count($tokens)) {
            return null;
        }

        return $value;
    }

    private function parseMathExpression(array $tokens, int &$index): ?float
    {
        $value = $this->parseMathTerm($tokens, $index);

        if ($value === null) {
            return null;
        }

        while ($index < count($tokens) && in_array($tokens[$index], ['+', '-'], true)) {
            $operator = $tokens[$index++];
            $right = $this->parseMathTerm($tokens, $index);

            if ($right === null) {
                return null;
            }

            $value = $operator === '+' ? $value + $right : $value - $right;
        }

        return $value;
    }

    private function parseMathTerm(array $tokens, int &$index): ?float
    {
        $value = $this->parseMathFactor($tokens, $index);

        if ($value === null) {
            return null;
        }

        while ($index < count($tokens) && in_array($tokens[$index], ['*', '/'], true)) {
            $operator = $tokens[$index++];
            $right = $this->parseMathFactor($tokens, $index);

            if ($right === null) {
                return null;
            }

            if ($operator === '/') {
                if ((float) $right === 0.0) {
                    return null;
                }

                $value /= $right;
                continue;
            }

            $value *= $right;
        }

        return $value;
    }

    private function parseMathFactor(array $tokens, int &$index): ?float
    {
        if ($index >= count($tokens)) {
            return null;
        }

        $token = $tokens[$index];

        if ($token === '-') {
            $index++;
            $value = $this->parseMathFactor($tokens, $index);

            return $value === null ? null : -$value;
        }

        if ($token === '+') {
            $index++;

            return $this->parseMathFactor($tokens, $index);
        }

        if ($token === '(') {
            $index++;
            $value = $this->parseMathExpression($tokens, $index);

            if ($value === null || ($tokens[$index] ?? null) !== ')') {
                return null;
            }

            $index++;

            return $value;
        }

        if (is_numeric($token)) {
            $index++;

            return (float) $token;
        }

        return null;
    }
}
