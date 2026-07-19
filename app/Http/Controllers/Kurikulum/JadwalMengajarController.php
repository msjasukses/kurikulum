<?php

namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Concerns\FilterPenugasanGuru;
use App\Http\Controllers\Crud\BaseCrudController;
use App\Models\JadwalMengajar;

class JadwalMengajarController extends BaseCrudController
{
    use FilterPenugasanGuru;

    /** Guru hanya melihat jadwal mengajar miliknya sendiri. */
    protected function baseQuery()
    {
        $user = auth()->user();

        return parent::baseQuery()
            ->when($user && $user->isGuru() && $user->guru_id, fn ($q) => $q->where('guru_id', $user->guru_id));
    }

    protected string $model = JadwalMengajar::class;
    protected string $routeName = 'kurikulum.jadwal';
    protected string $title = 'Jadwal Mengajar Guru';

    protected function fields(): array
    {
        return [
            ['name' => 'hari', 'label' => 'Hari', 'type' => 'select', 'rules' => 'required|string', 'options' => ['Senin' => 'Senin', 'Selasa' => 'Selasa', 'Rabu' => 'Rabu', 'Kamis' => 'Kamis', 'Jumat' => 'Jumat', 'Sabtu' => 'Sabtu']],
            ['name' => 'guru_id', 'label' => 'Guru', 'type' => 'select', 'rules' => 'required|integer', 'relation' => ['method' => 'guru', 'model' => \App\Models\Guru::class, 'display' => 'nama_ptk'] + $this->idsFilter(auth()->user()?->isGuru() && auth()->user()->guru_id ? [auth()->user()->guru_id] : null)],
            ['name' => 'mata_pelajaran_id', 'label' => 'Mata Pelajaran', 'type' => 'select', 'rules' => 'required|integer', 'relation' => ['method' => 'mataPelajaran', 'model' => \App\Models\MataPelajaran::class, 'display' => 'nama_mapel'] + $this->idsFilter($this->mapelIdsGuru())],
            ['name' => 'kelas_id', 'label' => 'Kelas', 'type' => 'select', 'rules' => 'required|integer', 'relation' => ['method' => 'kelas', 'model' => \App\Models\Kelas::class, 'display' => 'nama_rombel'] + $this->idsFilter($this->kelasIdsGuru())],
            ['name' => 'jam_mengajar_id', 'label' => 'Jam Mengajar', 'type' => 'select', 'rules' => 'required|integer', 'relation' => ['method' => 'jamMengajar', 'model' => \App\Models\JamMengajar::class, 'display' => 'label', 'orderBy' => 'jam_ke']],
            ['name' => 'ruangan', 'label' => 'Ruangan', 'type' => 'text', 'rules' => 'nullable|string|max:50', 'list' => false],
            ['name' => 'tahun_ajaran', 'label' => 'Tahun Ajaran', 'type' => 'select', 'rules' => 'nullable|string|max:20', 'optionsFrom' => ['model' => \App\Models\TahunAjaran::class, 'column' => 'nama_tahun_ajaran']],
        ];
    }
}
