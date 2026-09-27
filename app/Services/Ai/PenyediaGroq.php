<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

/** Groq — memakai endpoint chat completions yang kompatibel dengan OpenAI. */
class PenyediaGroq implements PenyediaAi
{
    private const ENDPOINT = 'https://api.groq.com/openai/v1/chat/completions';

    public static function label(): string
    {
        return 'Groq';
    }

    public static function modelBawaan(): string
    {
        return 'llama-3.3-70b-versatile';
    }

    public static function contohModel(): string
    {
        return 'llama-3.3-70b-versatile, openai/gpt-oss-120b, llama-3.1-8b-instant';
    }

    public static function alamatKunci(): string
    {
        return 'https://console.groq.com/keys';
    }

    public function kirim(string $apiKey, string $model, string $sistem, string $permintaan, array $skema): string
    {
        // Groq memakai "JSON mode": skema tidak dikirim sebagai parameter,
        // melainkan dijelaskan di dalam instruksi supaya modelnya patuh.
        $sistem .= "\n\nJawab HANYA dengan satu objek JSON yang valid tanpa penjelasan apa pun,"
            ." mengikuti skema berikut:\n".json_encode($skema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        try {
            $response = Http::withToken($apiKey)
                ->timeout(180)
                ->acceptJson()
                ->post(self::ENDPOINT, [
                    'model' => $model,
                    'response_format' => ['type' => 'json_object'],
                    'temperature' => 0.4,
                    'messages' => [
                        ['role' => 'system', 'content' => $sistem],
                        ['role' => 'user', 'content' => $permintaan],
                    ],
                ]);
        } catch (Throwable $e) {
            throw new RuntimeException('Server tidak bisa terhubung ke Groq. Periksa koneksi internet server.', 0, $e);
        }

        if ($response->status() === 401 || $response->status() === 403) {
            throw new RuntimeException('Kunci API Groq ditolak. Periksa kembali kunci di Setting Profil.');
        }

        if ($response->status() === 429) {
            throw new RuntimeException('Kuota Groq sedang penuh atau habis. Coba lagi beberapa saat lagi.');
        }

        if ($response->failed()) {
            $pesan = $response->json('error.message');
            throw new RuntimeException('Groq menolak permintaan (kode '.$response->status().')'
                .($pesan ? ': '.$pesan : '.'));
        }

        $isi = $response->json('choices.0.message.content');

        if (! is_string($isi) || trim($isi) === '') {
            throw new RuntimeException('Groq tidak mengembalikan jawaban teks.');
        }

        return $isi;
    }
}
