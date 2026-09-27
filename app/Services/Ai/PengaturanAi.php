<?php

namespace App\Services\Ai;

use App\Models\User;

/**
 * Menentukan penyedia AI, kunci, dan model yang dipakai satu akun.
 *
 * Urutan yang dipakai:
 *  1. Kunci milik akun sendiri (diisi di Setting Profil).
 *  2. Kunci aplikasi di file .env (ANTHROPIC_API_KEY) sebagai cadangan.
 *  3. Bila keduanya kosong: AI tidak tersedia, halaman modul ajar memakai
 *     kerangka bawaan aplikasi.
 */
class PengaturanAi
{
    /** Daftar penyedia yang didukung: kode => kelas penyedia. */
    public const PENYEDIA = [
        'anthropic' => PenyediaAnthropic::class,
        'groq' => PenyediaGroq::class,
        'gemini' => PenyediaGemini::class,
    ];

    public function __construct(
        public readonly string $penyedia,
        public readonly string $apiKey,
        public readonly string $model,
        public readonly bool $milikAkun,
    ) {}

    /** Pilihan penyedia untuk dropdown di halaman profil. */
    public static function pilihan(): array
    {
        $pilihan = [];
        foreach (self::PENYEDIA as $kode => $kelas) {
            $pilihan[$kode] = [
                'label' => $kelas::label(),
                'model_bawaan' => $kelas::modelBawaan(),
                'contoh_model' => $kelas::contohModel(),
                'alamat_kunci' => $kelas::alamatKunci(),
            ];
        }

        return $pilihan;
    }

    public static function penyediaValid(?string $kode): bool
    {
        return $kode !== null && array_key_exists($kode, self::PENYEDIA);
    }

    /** Konfigurasi yang berlaku untuk user tertentu; null bila belum ada kunci. */
    public static function untuk(?User $user): ?self
    {
        if ($user && filled($user->ai_api_key) && self::penyediaValid($user->ai_provider)) {
            $kelas = self::PENYEDIA[$user->ai_provider];

            return new self(
                penyedia: $user->ai_provider,
                apiKey: (string) $user->ai_api_key,
                model: filled($user->ai_model) ? $user->ai_model : $kelas::modelBawaan(),
                milikAkun: true,
            );
        }

        $kunciAplikasi = (string) config('services.anthropic.key');

        if ($kunciAplikasi !== '') {
            return new self(
                penyedia: 'anthropic',
                apiKey: $kunciAplikasi,
                model: (string) config('services.anthropic.model', PenyediaAnthropic::modelBawaan()),
                milikAkun: false,
            );
        }

        return null;
    }

    public function label(): string
    {
        return self::PENYEDIA[$this->penyedia]::label();
    }

    public function buatPenyedia(): PenyediaAi
    {
        $kelas = self::PENYEDIA[$this->penyedia];

        return new $kelas();
    }
}
