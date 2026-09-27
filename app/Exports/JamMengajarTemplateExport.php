<?php

namespace App\Exports;

use App\Models\TingkatKelas;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Template Excel untuk import Jam Mengajar. Berisi header kolom dan beberapa
 * baris contoh sebagai panduan pengisian. Daftar Tingkat Kelas yang valid
 * ditampilkan di halaman import (bukan di file ini), supaya selalu mengikuti
 * data terbaru dari database datacenter.
 */
class JamMengajarTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'Jam Ke',
            'Tingkat Kelas',
            'Hari',
            'Nama',
            'Jam Mulai',
            'Jam Selesai',
            'Keterangan',
        ];
    }

    public function array(): array
    {
        $contohTingkat = TingkatKelas::orderBy('urutan')->value('nama') ?? 'Kelas 7';

        return [
            [1, $contohTingkat, 'Senin', 'Jam ke-1', '07:00', '07:45', ''],
            [2, $contohTingkat, 'Senin', 'Jam ke-2', '07:45', '08:30', ''],
            [3, $contohTingkat, 'Senin', 'Istirahat', '08:30', '08:45', 'Istirahat pertama'],
        ];
    }
}
