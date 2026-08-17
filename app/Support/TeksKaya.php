<?php

namespace App\Support;

/**
 * Bantuan untuk isian yang diketik lewat editor teks kaya (TinyMCE):
 * membersihkan HTML sebelum disimpan, menyiapkannya untuk ditampilkan, dan
 * mengubahnya kembali jadi teks polos untuk daftar/dropdown.
 */
class TeksKaya
{
    /**
     * Tag HTML yang boleh tersimpan dari editor. Selain ini dibuang, termasuk
     * <script>/<iframe>, supaya isinya aman ditampilkan apa adanya.
     */
    public const TAG_DIIZINKAN = '<p><br><hr><b><strong><i><em><u><s><strike><span><div>'
        .'<ol><ul><li><h1><h2><h3><h4><h5><h6><blockquote><pre><code>'
        .'<table><thead><tbody><tfoot><tr><th><td><caption><sub><sup><a>';

    /** Bersihkan HTML dari editor sebelum disimpan. */
    public static function bersihkan(?string $nilai): ?string
    {
        if ($nilai === null) {
            return null;
        }

        // Blok script/style dibuang berikut isinya, bukan hanya tag-nya.
        $bersih = preg_replace('#<(script|style)\b[^>]*>.*?</\1\s*>#is', '', $nilai);
        $bersih = strip_tags($bersih, self::TAG_DIIZINKAN);
        // Buang atribut event (onclick, onerror, ...) dan tautan javascript:.
        $bersih = preg_replace('/\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $bersih);
        $bersih = preg_replace('/(href|src)\s*=\s*(["\'])\s*javascript:[^"\']*/i', '$1=$2#', $bersih);

        // Editor kosong tetap mengirim paragraf kosong; anggap tidak diisi.
        return trim(strip_tags($bersih)) === '' && ! str_contains($bersih, '<table') ? null : $bersih;
    }

    /**
     * Isi siap tampil. Data lama yang berupa teks polos (mis. hasil import
     * Excel) tetap terjaga pergantian barisnya, data dari editor tampil
     * sebagai HTML.
     */
    public static function html(?string $nilai): string
    {
        $nilai = (string) $nilai;

        if (trim($nilai) === '') {
            return '';
        }

        return $nilai === strip_tags($nilai) ? nl2br(e($nilai)) : $nilai;
    }

    /** Teks polos satu baris untuk daftar, dropdown, dan file Excel. */
    public static function polos(?string $nilai): string
    {
        $teks = preg_replace('#<(script|style)\b[^>]*>.*?</\1\s*>#is', '', (string) $nilai);
        // Tag penutup blok/baris diganti spasi supaya kata tidak menempel.
        $teks = preg_replace('#<(/p|/li|/h[1-6]|/tr|/td|/th|br\s*/?)\s*>#i', ' ', $teks);
        $teks = html_entity_decode(strip_tags($teks), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $teks));
    }
}
