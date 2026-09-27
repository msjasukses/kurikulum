<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

/** Google Gemini — memakai Interactions API (generativelanguage.googleapis.com). */
class PenyediaGemini implements PenyediaAi
{
    private const ENDPOINT = 'https://generativelanguage.googleapis.com/v1beta/interactions';

    public static function label(): string
    {
        return 'Google Gemini';
    }

    public static function modelBawaan(): string
    {
        return 'gemini-3.7-flash';
    }

    public static function contohModel(): string
    {
        return 'gemini-3.7-flash';
    }

    public static function alamatKunci(): string
    {
        return 'https://aistudio.google.com/apikey';
    }

    public function kirim(string $apiKey, string $model, string $sistem, string $permintaan, array $skema): string
    {
        try {
            $response = Http::withHeaders(['x-goog-api-key' => $apiKey])
                ->timeout(180)
                ->acceptJson()
                ->post(self::ENDPOINT, [
                    'model' => $model,
                    'system_instruction' => $sistem,
                    'input' => $permintaan,
                    'response_format' => [
                        'type' => 'text',
                        'mime_type' => 'application/json',
                        'schema' => $skema,
                    ],
                ]);
        } catch (Throwable $e) {
            throw new RuntimeException('Server tidak bisa terhubung ke Gemini. Periksa koneksi internet server.', 0, $e);
        }

        if ($response->failed()) {
            $pesan = $this->pesanError($response->json());

            // Kunci yang salah dijawab Google dengan kode 400 (API_KEY_INVALID),
            // bukan 401, jadi dikenali dari isi pesannya.
            if (in_array($response->status(), [401, 403], true) || str_contains(mb_strtolower($pesan), 'api key')) {
                throw new RuntimeException('Kunci API Gemini ditolak. Periksa kembali kunci di Setting Profil.');
            }

            if ($response->status() === 429) {
                throw new RuntimeException('Kuota Gemini sedang penuh atau habis. Coba lagi beberapa saat lagi.');
            }

            throw new RuntimeException('Gemini menolak permintaan (kode '.$response->status().')'
                .($pesan !== '' ? ': '.$pesan : '.'));
        }

        // Jawaban ada di langkah "model_output" pada array steps.
        $teks = '';
        foreach ((array) $response->json('steps', []) as $langkah) {
            if (($langkah['type'] ?? null) !== 'model_output') {
                continue;
            }
            foreach ((array) ($langkah['content'] ?? []) as $bagian) {
                if (($bagian['type'] ?? null) === 'text') {
                    $teks .= $bagian['text'] ?? '';
                }
            }
        }

        if (trim($teks) === '') {
            throw new RuntimeException('Gemini tidak mengembalikan jawaban teks.');
        }

        return $teks;
    }

    /** Pesan error Google bisa berbentuk objek atau larik berisi satu objek. */
    private function pesanError(mixed $body): string
    {
        if (! is_array($body)) {
            return '';
        }

        $error = $body['error'] ?? ($body[0]['error'] ?? null);

        return is_array($error) ? trim((string) ($error['message'] ?? '')) : '';
    }
}
