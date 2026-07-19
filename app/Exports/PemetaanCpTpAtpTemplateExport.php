<?php

namespace App\Exports;

use App\Models\MataPelajaran;
use App\Models\TingkatKelas;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Template Excel untuk import Pemetaan CP-TP-ATP. Berisi header kolom
 * dan satu baris contoh sebagai panduan pengisian. Daftar Kode Mapel dan
 * Tingkat Kelas yang valid ditampilkan di halaman import (bukan di file
 * Excel ini), supaya selalu mengikuti data terbaru dari database datacenter.
 */
class PemetaanCpTpAtpTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'Kode Mapel',
            'Tingkat Kelas',
            'Fase',
            'Capaian Pembelajaran',
            'Tujuan Pembelajaran',
            'Alur Tujuan Pembelajaran',
            'Tahun Ajaran',
        ];
    }

    public function array(): array
    {
        $contohMapel = MataPelajaran::orderBy('kode_mapel')->first();
        $contohTingkat = TingkatKelas::orderBy('urutan')->first();

        return [
            [
                $contohMapel->kode_mapel ?? 'MTK',
                $contohTingkat->nama ?? 'X',
                'E',
                'Contoh isi Capaian Pembelajaran...',
                'Contoh isi Tujuan Pembelajaran...',
                'Contoh isi Alur Tujuan Pembelajaran...',
                '2025/2026',
            ],
        ];
    }
}
