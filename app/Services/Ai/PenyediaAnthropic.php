<?php

namespace App\Services\Ai;

use Anthropic\Client;
use Anthropic\Core\Exceptions\APIConnectionException;
use Anthropic\Core\Exceptions\APIStatusException;
use Anthropic\Core\Exceptions\APITimeoutException;
use Anthropic\Core\Exceptions\AuthenticationException;
use Anthropic\Core\Exceptions\RateLimitException;
use RuntimeException;
use Throwable;

/** Claude (Anthropic) lewat SDK resmi anthropic-ai/sdk. */
class PenyediaAnthropic implements PenyediaAi
{
    public static function label(): string
    {
        return 'Anthropic (Claude)';
    }

    public static function modelBawaan(): string
    {
        return 'claude-opus-5';
    }

    public static function contohModel(): string
    {
        return 'claude-opus-5, claude-sonnet-5, claude-haiku-4-5';
    }

    public static function alamatKunci(): string
    {
        return 'https://console.anthropic.com/settings/keys';
    }

    public function kirim(string $apiKey, string $model, string $sistem, string $permintaan, array $skema): string
    {
        $client = new Client(apiKey: $apiKey);

        try {
            $message = $client->messages->create(
                model: $model,
                maxTokens: 16000,
                thinking: ['type' => 'adaptive'],
                outputConfig: [
                    'effort' => 'medium',
                    'format' => ['type' => 'json_schema', 'schema' => $skema],
                ],
                system: [['type' => 'text', 'text' => $sistem]],
                messages: [['role' => 'user', 'content' => $permintaan]],
            );
        } catch (AuthenticationException $e) {
            throw new RuntimeException('Kunci API Anthropic ditolak. Periksa kembali kunci di Setting Profil.', 0, $e);
        } catch (RateLimitException $e) {
            throw new RuntimeException('Kuota Anthropic sedang penuh atau habis. Coba lagi beberapa saat lagi.', 0, $e);
        } catch (APITimeoutException $e) {
            throw new RuntimeException('Penyusunan modul terlalu lama sehingga dihentikan. Coba ulangi.', 0, $e);
        } catch (APIConnectionException $e) {
            throw new RuntimeException('Server tidak bisa terhubung ke Anthropic. Periksa koneksi internet server.', 0, $e);
        } catch (APIStatusException $e) {
            throw new RuntimeException('Anthropic menolak permintaan (kode '.$e->status.'). Rinciannya tercatat di log aplikasi.', 0, $e);
        } catch (Throwable $e) {
            throw new RuntimeException('Gagal menghubungi Anthropic. Rinciannya tercatat di log aplikasi.', 0, $e);
        }

        foreach ($message->content as $block) {
            if (($block->type ?? null) === 'text') {
                return $block->text;
            }
        }

        throw new RuntimeException('Anthropic tidak mengembalikan jawaban teks.');
    }
}
