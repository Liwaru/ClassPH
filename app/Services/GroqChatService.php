<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqChatService
{
    private ?string $lastFailureReason = null;

    public function isConfigured(): bool
    {
        return filled(config('services.groq.api_key'));
    }

    public function lastFailureReason(): ?string
    {
        return $this->lastFailureReason;
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<int, array{role: string, message: string}>  $history
     */
    public function generateReply(
        string $message,
        array $context,
        string $intent,
        string $groundedAnswer,
        array $history = []
    ): ?string {
        if (! $this->isConfigured()) {
            $this->lastFailureReason = 'not_configured';

            return null;
        }

        $this->lastFailureReason = null;

        $messages = [
            [
                'role' => 'system',
                'content' => $this->systemPrompt($context),
            ],
        ];

        foreach (array_slice($history, -8) as $item) {
            if (! in_array($item['role'] ?? '', ['user', 'assistant'], true) || blank($item['message'] ?? '')) {
                continue;
            }

            $messages[] = [
                'role' => $item['role'],
                'content' => $item['message'],
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $this->userPrompt($message, $intent, $groundedAnswer, $context),
        ];

        try {
            $response = Http::timeout((int) config('services.groq.timeout', 20))
                ->withToken((string) config('services.groq.api_key'))
                ->acceptJson()
                ->post(rtrim((string) config('services.groq.base_url'), '/').'/chat/completions', [
                    'model' => (string) config('services.groq.model', 'llama-3.1-8b-instant'),
                    'messages' => $messages,
                    'temperature' => 0.2,
                ]);

            if (! $response->successful()) {
                $this->lastFailureReason = $response->status() === 429 ? 'quota_exceeded' : 'request_failed';
                Log::warning('Groq chatbot request failed.', [
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);

                return null;
            }

            return $this->extractText($response->json());
        } catch (\Throwable $exception) {
            $this->lastFailureReason = 'connection_error';
            Log::warning('Groq chatbot request exception.', [
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function systemPrompt(array $context): string
    {
        $roleName = $context['role']['name'] ?? 'Pengguna';
        $scopeSummary = $context['scope']['scope_summary'] ?? 'Akses dibatasi sesuai role.';
        $permissions = $context['permissions'] ?? [];

        return implode("\n", [
            'Kamu adalah tutor AI dan asisten sekolah di aplikasi InfraSPH.',
            'Jawab dalam Bahasa Indonesia yang natural, hangat, jelas, dan membantu.',
            'Bersikap seperti guru pendamping belajar yang profesional, sabar, dan cepat tanggap, bukan bot kaku.',
            'Kamu wajib mematuhi RBAC dan privasi data.',
            'Untuk pertanyaan tentang data, user, kelas, inventaris, ruangan, pengajuan, atau sistem internal InfraSPH, gunakan hanya fakta aman yang diberikan dan jangan pernah memberi akses di luar fakta itu.',
            'Jangan pernah mengarang data internal, jangan menampilkan query/database mentah, dan jangan membocorkan akses di luar role user.',
            'Jika fakta internal terbatas, katakan dengan jujur bahwa informasi yang tersedia terbatas pada akses user.',
            'Untuk sapaan ringan, ucapan terima kasih, typo, atau penutupan percakapan, balas secara natural tanpa berlebihan.',
            'Untuk balasan pendek seperti "tidak", "tidak ada", "sudah", atau "cukup", tanggapi singkat dan natural tanpa mengulang penawaran bantuan yang panjang.',
            'Untuk small talk seperti "bosan", "iseng", "bingung", "gajadi", "oke", atau teks acak, pahami dulu maksud user lalu beri respons singkat yang manusiawi.',
            'Jika input user tampak acak atau typo, bantu klarifikasi secara sopan.',
            'Untuk pesan singkat, candaan, slang, typo ringan, atau percakapan santai, analisis sendiri konteks chat lalu jawab secara natural tanpa bergantung pada template kaku.',
            'Jika user meminta bantuan, arahkan ke tindakan yang bisa dilakukan di dashboard secara jelas.',
            'Untuk pertanyaan umum di luar website, kamu boleh menjawab dengan pengetahuan umum secara natural selama tidak menyentuh data internal InfraSPH.',
            'Kamu boleh membantu materi pembelajaran sekolah seperti matematika, sejarah, IPA, IPS, Bahasa Indonesia, Bahasa Inggris, PPKn, seni, dan topik sekolah lainnya.',
            'Jawab pertanyaan pelajaran dengan gaya yang natural, jelas, dan mudah dipahami.',
            'Jika user meminta penjelasan pelajaran, sesuaikan tingkat bahasa dengan level siswa sekolah.',
            'Jika user meminta soal atau latihan, kamu boleh memberi contoh, pembahasan, dan ringkasan singkat.',
            'Jika user meminta pembuatan konten seperti foto, gambar, logo, atau hal yang memang tidak didukung chatbot ini, jawab dengan sopan bahwa kamu tidak bisa membantu untuk itu.',
            'Untuk pertanyaan pelajaran, kamu boleh memberi jawaban lebih panjang bila diperlukan, tetapi tetap ringkas dan terstruktur.',
            'Konteks role saat ini: '.$roleName.'.',
            'Ruang lingkup akses: '.$scopeSummary,
            'Izin penting: '.json_encode($permissions, JSON_UNESCAPED_UNICODE),
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function userPrompt(string $message, string $intent, string $groundedAnswer, array $context): string
    {
        return implode("\n\n", [
            'Pertanyaan user: '.$message,
            'Intent terdeteksi: '.$intent,
            'Fakta aman dari backend: '.$groundedAnswer,
            'Ringkasan konteks aman: '.json_encode([
                'user' => $context['user'] ?? [],
                'role' => $context['role'] ?? [],
                'scope' => [
                    'assigned_room_names' => $context['scope']['assigned_room_names'] ?? [],
                    'scope_summary' => $context['scope']['scope_summary'] ?? null,
                ],
            ], JSON_UNESCAPED_UNICODE),
            'Tugasmu: jika pertanyaan terkait InfraSPH, jawab dengan ramah berdasarkan fakta aman di atas. Jika pertanyaan bersifat umum dan tidak meminta data internal, kamu boleh menjawab dengan pengetahuan umum secara natural, termasuk materi pelajaran sekolah. Jika user meminta data atau aksi di luar akses, tolak dengan sopan. Jika pertanyaan sederhana seperti sapaan atau ucapan terima kasih, balas secara natural namun tetap singkat.',
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function extractText(array $payload): ?string
    {
        $text = $payload['choices'][0]['message']['content'] ?? null;

        return filled($text) ? trim((string) $text) : null;
    }
}
