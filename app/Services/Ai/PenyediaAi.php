<?php

namespace App\Services\Ai;

/**
 * Kontrak satu penyedia layanan AI. Semua penyedia menerima instruksi
 * sistem + permintaan, lalu mengembalikan jawaban berupa teks JSON yang
 * mengikuti skema yang diberikan.
 */
interface PenyediaAi
{
    /** Nama penyedia untuk ditampilkan ke pengguna. */
    public static function label(): string;

    /** Model bawaan bila pengguna tidak mengisi nama model. */
    public static function modelBawaan(): string;

    /** Contoh nama model lain, ditampilkan sebagai petunjuk di halaman profil. */
    public static function contohModel(): string;

    /** Alamat halaman untuk membuat kunci API. */
    public static function alamatKunci(): string;

    /**
     * Kirim permintaan dan kembalikan jawaban mentah berupa teks JSON.
     *
     * @param  array  $skema  JSON Schema yang harus dipatuhi jawaban
     */
    public function kirim(string $apiKey, string $model, string $sistem, string $permintaan, array $skema): string;
}
