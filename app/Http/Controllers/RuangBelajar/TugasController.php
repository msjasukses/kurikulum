<?php

namespace App\Http\Controllers\RuangBelajar;

use App\Http\Controllers\Concerns\FilterPenugasanGuru;
use App\Http\Controllers\Crud\BaseCrudController;
use App\Models\PengumpulanTugas;
use App\Models\SiswaRombel;
use App\Models\Tugas;

class TugasController extends BaseCrudController
{
    use FilterPenugasanGuru;

    protected string $model = Tugas::class;
    protected string $routeName = 'ruangbelajar.tugas';
    protected string $title = 'Tugas';

    protected function canManage(): bool
    {
        return auth()->user() && ! auth()->user()->isSiswa();
    }

    /**
     * Guru hanya melihat tugas buatannya sendiri; siswa hanya melihat tugas
     * untuk kelas tempat dirinya terdaftar.
     */
    protected function baseQuery()
    {
        $user = auth()->user();

        return parent::baseQuery()
            ->when($user && $user->isGuru() && $user->guru_id, fn ($q) => $q->where('pegawai_id', $user->guru_id))
            ->when($user && $user->isSiswa(), fn ($q) => $q->whereIn('kelas_id', $this->kelasIdsSiswa($user->siswa_id)));
    }

    /** Kelas siswa pada tahun ajaran yang dipilih di topbar. */
    private function kelasIdsSiswa(?int $siswaId): array
    {
        return SiswaRombel::kelasIdsSiswa($siswaId, $this->tahunAjaranTerpilih()->id());
    }

    /**
     * Tombol per baris: siswa mengumpulkan jawaban, guru/admin melihat
     * daftar pengumpulan siswa untuk tugas tersebut.
     */
    protected function rowActions(): array
    {
        $user = auth()->user();

        if ($user && $user->isSiswa()) {
            $sudah = PengumpulanTugas::where('siswa_id', $user->siswa_id)
                ->pluck('dikumpulkan_pada', 'tugas_id');

            return [
                fn ($tugas) => [
                    'label' => $sudah->has($tugas->id) ? 'Sudah Dikumpulkan' : 'Kumpulkan',
                    'url' => route('ruangbelajar.pengumpulan.form', $tugas->id),
                    'icon' => $sudah->has($tugas->id) ? 'bi-check2-circle' : 'bi-upload',
                    'class' => $sudah->has($tugas->id) ? 'btn-outline-success' : 'btn-primary',
                ],
            ];
        }

        $jumlah = PengumpulanTugas::selectRaw('tugas_id, count(*) as jumlah')
            ->groupBy('tugas_id')->pluck('jumlah', 'tugas_id');

        return [
            fn ($tugas) => [
                'label' => 'Pengumpulan ('.($jumlah[$tugas->id] ?? 0).')',
                'url' => route('ruangbelajar.pengumpulan.daftar', $tugas->id),
                'icon' => 'bi-inboxes',
                'class' => 'btn-outline-primary',
            ],
        ];
    }

    /**
     * Ringkasan pengumpulan per tugas untuk kolom penanda di daftar guru:
     * berapa siswa sudah mengumpulkan, berapa yang belum dinilai, dan berapa
     * jumlah siswa di kelas tugas tersebut. Dihitung sekali per permintaan.
     */
    private ?array $statistik = null;

    private function statistik(): array
    {
        if ($this->statistik !== null) {
            return $this->statistik;
        }

        $pengumpulan = PengumpulanTugas::selectRaw(
            'tugas_id, count(*) as masuk, count(case when nilai is null then 1 end) as belum_dinilai'
        )->groupBy('tugas_id')->get()->keyBy('tugas_id');

        $siswaPerKelas = SiswaRombel::selectRaw('rombongan_belajar_id, count(distinct siswa_id) as jumlah')
            ->groupBy('rombongan_belajar_id')->pluck('jumlah', 'rombongan_belajar_id');

        return $this->statistik = [
            'pengumpulan' => $pengumpulan,
            'siswa' => $siswaPerKelas,
        ];
    }

    /** Badge status pengumpulan untuk satu baris tugas. */
    private function penandaPengumpulan(Tugas $tugas): array
    {
        $statistik = $this->statistik();
        $baris = $statistik['pengumpulan']->get($tugas->id);
        $masuk = (int) ($baris->masuk ?? 0);
        $belumDinilai = (int) ($baris->belum_dinilai ?? 0);
        $jumlahSiswa = (int) ($statistik['siswa'][$tugas->kelas_id] ?? 0);

        $penanda = [[
            'teks' => $masuk.($jumlahSiswa ? '/'.$jumlahSiswa : '').' masuk',
            'kelas' => $masuk === 0 ? 'bg-secondary' : ($jumlahSiswa && $masuk >= $jumlahSiswa ? 'bg-success' : 'bg-primary'),
        ]];

        if ($jumlahSiswa > $masuk) {
            $penanda[] = ['teks' => ($jumlahSiswa - $masuk).' belum mengumpulkan', 'kelas' => 'bg-light text-dark border'];
        }

        if ($belumDinilai > 0) {
            $penanda[] = ['teks' => $belumDinilai.' perlu dinilai', 'kelas' => 'bg-warning text-dark'];
        }

        if ($tugas->tanggal_selesai && now()->gt($tugas->tanggal_selesai->endOfDay())) {
            $penanda[] = ['teks' => 'Batas lewat', 'kelas' => 'bg-danger'];
        }

        return $penanda;
    }
    protected function fields(): array
    {
        return [
            ['name' => 'judul', 'label' => 'Judul Tugas', 'type' => 'text', 'rules' => 'required|string|max:150'],
            ['name' => 'mata_pelajaran_id', 'label' => 'Mata Pelajaran', 'type' => 'select', 'rules' => 'required|integer', 'relation' => ['method' => 'mataPelajaran', 'model' => \App\Models\MataPelajaran::class, 'display' => 'nama_mapel'] + $this->idsFilter($this->mapelIdsGuru())],
            ['name' => 'kelas_id', 'label' => 'Kelas', 'type' => 'select', 'rules' => 'required|integer', 'relation' => ['method' => 'kelas', 'model' => \App\Models\Kelas::class, 'display' => 'nama_rombel'] + $this->idsFilter($this->kelasIdsGuru()) + $this->filterTahunAjaranId()],
            ['name' => 'pegawai_id', 'label' => 'Guru Pemberi Tugas', 'type' => 'select', 'rules' => 'nullable|integer', 'relation' => ['method' => 'pegawai', 'model' => \App\Models\Guru::class, 'display' => 'nama_ptk'] + $this->idsFilter(auth()->user()?->isGuru() && auth()->user()->guru_id ? [auth()->user()->guru_id] : null)],
            ['name' => 'deskripsi', 'label' => 'Deskripsi / Instruksi', 'type' => 'textarea', 'rules' => 'nullable|string', 'list' => false, 'editor' => true],
            ['name' => 'file_lampiran', 'label' => 'File Lampiran', 'type' => 'file', 'rules' => 'nullable|file|max:10240', 'list' => false],
            ['name' => 'tanggal_mulai', 'label' => 'Tanggal Mulai', 'type' => 'date', 'rules' => 'nullable|date'],
            ['name' => 'tanggal_selesai', 'label' => 'Batas Akhir Pengumpulan', 'type' => 'date', 'rules' => 'nullable|date'],
            ...(auth()->user()?->isSiswa() ? [] : [[
                'name' => 'pengumpulan',
                'label' => 'Pengumpulan',
                'type' => 'computed',
                'render' => fn ($tugas) => $this->penandaPengumpulan($tugas),
            ]]),
        ];
    }
}
