<?php

namespace App\Http\Controllers\RuangBelajar;

use App\Http\Controllers\Concerns\FilterPenugasanGuru;
use App\Http\Controllers\Crud\BaseCrudController;
use App\Models\MateriOnline;
use App\Models\SiswaRombel;
use Illuminate\Support\Facades\Storage;

class MateriOnlineController extends BaseCrudController
{
    use FilterPenugasanGuru;

    protected string $model = MateriOnline::class;
    protected string $routeName = 'ruangbelajar.materi';
    protected string $title = 'Materi Online';

    protected function canManage(): bool
    {
        return auth()->user() && ! auth()->user()->isSiswa();
    }

    /**
     * Guru hanya melihat materi yang diunggahnya sendiri; siswa hanya melihat
     * materi untuk kelas tempat dirinya terdaftar pada tahun ajaran terpilih.
     */
    protected function baseQuery()
    {
        $user = auth()->user();

        return parent::baseQuery()
            ->when($user && $user->isGuru() && $user->guru_id, fn ($q) => $q->where('pegawai_id', $user->guru_id))
            ->when($user && $user->isSiswa(), fn ($q) => $q->whereIn(
                'kelas_id',
                SiswaRombel::kelasIdsSiswa($user->siswa_id, $this->tahunAjaranTerpilih()->id()) ?: [0]
            ));
    }

    /**
     * Penanda isi materi: bentuk lampirannya apa saja, apakah masih baru,
     * dan peringatan bila materi belum punya file maupun tautan video.
     */
    private function penandaMateri(MateriOnline $materi): array
    {
        $penanda = [];

        if ($materi->file) {
            $penanda[] = ['teks' => 'File', 'kelas' => 'bg-primary'];
        }

        if ($materi->link_video) {
            $penanda[] = ['teks' => 'Video', 'kelas' => 'bg-danger'];
        }

        if (filled($materi->deskripsi)) {
            $penanda[] = ['teks' => 'Deskripsi', 'kelas' => 'bg-secondary'];
        }

        if (! $materi->file && ! $materi->link_video) {
            $penanda[] = ['teks' => 'Belum ada lampiran', 'kelas' => 'bg-warning text-dark'];
        }

        // Materi seminggu terakhir ditandai baru supaya mudah dikenali siswa.
        if ($materi->created_at && $materi->created_at->gt(now()->subDays(7))) {
            $penanda[] = ['teks' => 'Baru', 'kelas' => 'bg-success'];
        }

        return $penanda;
    }

    /** Tombol membuka file atau video materi langsung dari daftar. */
    protected function rowActions(): array
    {
        return [
            fn ($materi) => $materi->file ? [
                'label' => 'File',
                'url' => Storage::url($materi->file),
                'icon' => 'bi-file-earmark-arrow-down',
                'class' => 'btn-outline-primary',
                'target' => '_blank',
            ] : null,
            fn ($materi) => $materi->link_video ? [
                'label' => 'Video',
                'url' => $materi->link_video,
                'icon' => 'bi-play-btn',
                'class' => 'btn-outline-danger',
                'target' => '_blank',
            ] : null,
        ];
    }

    protected function fields(): array
    {
        return [
            ['name' => 'judul', 'label' => 'Judul Materi', 'type' => 'text', 'rules' => 'required|string|max:150'],
            ['name' => 'mata_pelajaran_id', 'label' => 'Mata Pelajaran', 'type' => 'select', 'rules' => 'required|integer', 'relation' => ['method' => 'mataPelajaran', 'model' => \App\Models\MataPelajaran::class, 'display' => 'nama_mapel'] + $this->idsFilter($this->mapelIdsGuru())],
            ['name' => 'kelas_id', 'label' => 'Kelas', 'type' => 'select', 'rules' => 'required|integer', 'relation' => ['method' => 'kelas', 'model' => \App\Models\Kelas::class, 'display' => 'nama_rombel'] + $this->idsFilter($this->kelasIdsGuru()) + $this->filterTahunAjaranId()],
            ['name' => 'pegawai_id', 'label' => 'Guru Pengunggah', 'type' => 'select', 'rules' => 'nullable|integer', 'relation' => ['method' => 'pegawai', 'model' => \App\Models\Guru::class, 'display' => 'nama_ptk'] + $this->idsFilter(auth()->user()?->isGuru() && auth()->user()->guru_id ? [auth()->user()->guru_id] : null)],
            ['name' => 'file', 'label' => 'File Materi', 'type' => 'file', 'rules' => 'nullable|file|max:10240', 'list' => false],
            ['name' => 'link_video', 'label' => 'Link Video (opsional)', 'type' => 'text', 'rules' => 'nullable|url|max:255', 'list' => false],
            ['name' => 'deskripsi', 'label' => 'Deskripsi', 'type' => 'textarea', 'rules' => 'nullable|string', 'list' => false, 'editor' => true],
            [
                'name' => 'isi_materi',
                'label' => 'Isi Materi',
                'type' => 'computed',
                'render' => fn ($materi) => $this->penandaMateri($materi),
            ],
        ];
    }
}
