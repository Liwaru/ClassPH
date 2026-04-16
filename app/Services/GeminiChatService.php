<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiChatService
{
    private ?string $lastFailureReason = null;

    public function isConfigured(): bool
    {
        return filled(config('services.gemini.api_key'));
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

        $contents = [];

        foreach (array_slice($history, -8) as $item) {
            if (! in_array($item['role'] ?? '', ['user', 'assistant'], true) || blank($item['message'] ?? '')) {
                continue;
            }

            $contents[] = [
                'role' => $item['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [
                    ['text' => $item['message']],
                ],
            ];
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [
                ['text' => $this->userPrompt($message, $intent, $groundedAnswer, $context)],
            ],
        ];

        $model = (string) config('services.gemini.model', 'gemini-2.0-flash');
        $endpoint = rtrim((string) config('services.gemini.base_url'), '/').'/models/'.$model.':generateContent';
        $originalProxyEnv = $this->suspendProxyEnvironment();

        try {
            $response = Http::timeout((int) config('services.gemini.timeout', 20))
                ->withOptions([
                    'proxy' => '',
                ])
                ->acceptJson()
                ->withHeaders([
                    'x-goog-api-key' => (string) config('services.gemini.api_key'),
                ])
                ->post($endpoint, [
                    'systemInstruction' => [
                        'parts' => [
                            ['text' => $this->systemPrompt($context)],
                        ],
                    ],
                    'contents' => $contents,
                    'generationConfig' => [
                        'temperature' => 0.2,
                        'maxOutputTokens' => 512,
                    ],
                ]);

            if (! $response->successful()) {
                $this->lastFailureReason = $response->status() === 429 ? 'quota_exceeded' : 'request_failed';
                Log::warning('Gemini chatbot request failed.', [
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);

                return null;
            }

            return $this->extractText($response->json());
        } catch (\Throwable $exception) {
            $this->lastFailureReason = 'connection_error';
            Log::warning('Gemini chatbot request exception.', [
                'message' => $exception->getMessage(),
            ]);

            return null;
        } finally {
            $this->restoreProxyEnvironment($originalProxyEnv);
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
            'Kamu adalah AI assistant untuk aplikasi InfraSPH.',
            'Jawab dalam Bahasa Indonesia yang natural, hangat, singkat, dan membantu.',
            'Bersikap seperti asisten website yang profesional dan cepat tanggap, bukan seperti bot kaku.',
            'Kamu wajib mematuhi RBAC dan privasi data.',
            'Untuk pertanyaan tentang data, user, kelas, inventaris, ruangan, pengajuan, atau sistem internal InfraSPH, gunakan hanya fakta aman yang diberikan dan jangan pernah memberi akses di luar fakta itu.',
            'Jangan pernah mengarang data internal, jangan menampilkan query/database mentah, dan jangan membocorkan akses di luar role user.',
            'Jika fakta internal terbatas, katakan dengan jujur bahwa informasi yang tersedia terbatas pada akses user.',
            'Untuk sapaan ringan, ucapan terima kasih, typo, atau penutupan percakapan, balas secara natural tanpa berlebihan.',
            'Untuk balasan pendek seperti "tidak", "tidak ada", "sudah", atau "cukup", tanggapi singkat dan natural tanpa mengulang penawaran bantuan yang panjang.',
            'Untuk small talk seperti "bosan", "iseng", "bingung", "gajadi", "oke", atau teks acak, pahami dulu maksud user lalu beri respons singkat yang manusiawi.',
            'Jika input user tampak acak atau typo, bantu klarifikasi secara sopan.',
            'Jika user meminta bantuan, arahkan ke tindakan yang bisa dilakukan di dashboard secara jelas.',
            'Untuk pertanyaan umum di luar website, kamu boleh menjawab dengan pengetahuan umum secara natural selama tidak menyentuh data internal InfraSPH.',
            'Jika user meminta pembuatan konten seperti foto, gambar, logo, atau hal yang memang tidak didukung chatbot ini, jawab dengan sopan bahwa kamu tidak bisa membantu untuk itu.',
            'Hindari paragraf panjang. Utamakan 1-3 kalimat yang jelas.',
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
            'Tugasmu: jika pertanyaan terkait InfraSPH, jawab dengan ramah berdasarkan fakta aman di atas. Jika pertanyaan bersifat umum dan tidak meminta data internal, kamu boleh menjawab dengan pengetahuan umum secara natural. Jika user meminta data atau aksi di luar akses, tolak dengan sopan. Jika pertanyaan sederhana seperti sapaan atau ucapan terima kasih, balas secara natural namun tetap singkat.',
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function extractText(array $payload): ?string
    {
        foreach (($payload['candidates'] ?? []) as $candidate) {
            foreach (($candidate['content']['parts'] ?? []) as $part) {
                $text = $part['text'] ?? null;

                if (filled($text)) {
                    return trim((string) $text);
                }
            }
        }

        return null;
    }

    /**
     * @return array<string, string|false>
     */
    private function suspendProxyEnvironment(): array
    {
        $keys = ['HTTP_PROXY', 'HTTPS_PROXY', 'ALL_PROXY', 'http_proxy', 'https_proxy', 'all_proxy'];
        $original = [];

        foreach ($keys as $key) {
            $original[$key] = getenv($key);
            putenv($key);
            unset($_ENV[$key], $_SERVER[$key]);
        }

        return $original;
    }

    /**
     * @param  array<string, string|false>  $original
     */
    private function restoreProxyEnvironment(array $original): void
    {
        foreach ($original as $key => $value) {
            if ($value === false) {
                putenv($key);
                unset($_ENV[$key], $_SERVER[$key]);
                continue;
            }

            putenv($key.'='.$value);
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}
