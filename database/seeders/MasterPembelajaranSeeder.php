<?php

namespace Database\Seeders;

use App\Models\KarakterDpl;
use App\Models\ModelPembelajaran;
use App\Models\Semester;
use App\Models\SumberBelajar;
use Illuminate\Database\Seeder;

/**
 * Isi awal master pendukung Pemetaan CP-TP-ATP: Semester, Model
 * Pembelajaran, Sumber Belajar, dan Karakter 7 KAIH/DPL. Semua memakai
 * firstOrCreate supaya aman dijalankan berulang dan tidak menimpa data
 * yang sudah diubah sekolah.
 */
class MasterPembelajaranSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['1', 'Ganjil', 1], ['2', 'Genap', 2]] as [$kode, $nama, $urutan]) {
            Semester::firstOrCreate(['nama' => $nama], ['kode' => $kode, 'urutan' => $urutan, 'is_aktif' => true]);
        }

        $model = [
            'Problem Based Learning (PBL)',
            'Project Based Learning (PjBL)',
            'Discovery Learning',
            'Inquiry Learning',
            'Cooperative Learning',
            'Contextual Teaching and Learning (CTL)',
            'Direct Instruction',
            'Teaching at The Right Level (TaRL)',
            'Culturally Responsive Teaching (CRT)',
            'Deep Learning',
        ];
        foreach ($model as $nama) {
            ModelPembelajaran::firstOrCreate(['nama' => $nama], ['is_aktif' => true]);
        }

        $sumber = [
            ['Buku Siswa', 'Cetak'],
            ['Buku Guru', 'Cetak'],
            ['Modul Ajar', 'Cetak'],
            ['LKPD', 'Cetak'],
            ['Internet / Website', 'Digital'],
            ['Video Pembelajaran', 'Digital'],
            ['Power Point', 'Digital'],
            ['Perpustakaan', 'Lingkungan'],
            ['Laboratorium', 'Lingkungan'],
            ['Lingkungan Sekitar', 'Lingkungan'],
            ['Narasumber', 'Lainnya'],
        ];
        foreach ($sumber as [$nama, $jenis]) {
            SumberBelajar::firstOrCreate(['nama' => $nama], ['jenis' => $jenis, 'is_aktif' => true]);
        }

        $kaih = [
            'Bangun Pagi',
            'Beribadah',
            'Berolahraga',
            'Makan Sehat dan Bergizi',
            'Gemar Belajar',
            'Bermasyarakat',
            'Tidur Cepat',
        ];
        foreach ($kaih as $i => $nama) {
            KarakterDpl::firstOrCreate(['nama' => $nama], ['kategori' => '7 KAIH', 'urutan' => $i + 1, 'is_aktif' => true]);
        }

        $dpl = [
            'Keimanan dan Ketakwaan terhadap Tuhan YME',
            'Kewargaan',
            'Penalaran Kritis',
            'Kreativitas',
            'Kolaborasi',
            'Kemandirian',
            'Kesehatan',
            'Komunikasi',
        ];
        foreach ($dpl as $i => $nama) {
            KarakterDpl::firstOrCreate(['nama' => $nama], ['kategori' => 'DPL', 'urutan' => $i + 11, 'is_aktif' => true]);
        }
    }
}
