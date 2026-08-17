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
            'Semester',
            'Elemen',
            'Capaian Pembelajaran',
            'Tujuan Pembelajaran',
            'Alur Tujuan Pembelajaran',
            'Indikator KKTP',
            'Model Pembelajaran',
            'Sumber Belajar',
            'Karakter DPL',
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
                'Ganjil',
                'Contoh isi Elemen...',
                'Contoh isi Capaian Pembelajaran...',
                'Contoh isi Tujuan Pembelajaran...',
                'Contoh isi Alur Tujuan Pembelajaran...',
                'Contoh isi Indikator KKTP...',
                'Problem Based Learning (PBL), Discovery Learning',
                'Buku Siswa, Video Pembelajaran',
                'Gemar Belajar, Penalaran Kritis',
                '2025/2026',
            ],
        ];
    }
}
