<?php

namespace App\Http\Controllers;

use App\Models\GuruMapel;
use App\Models\Kelas;
use App\Models\User;
use App\Support\TahunAjaranTerpilih;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Setting Profil — halaman "profil saya" yang bisa diakses semua role.
 * Isi halaman menyesuaikan hak akses:
 *  - admin  : data akun lokal + data pegawai yang tertaut (bila ada).
 *  - guru   : data PTK dari datacenter + mapel yang diampu & rombel perwalian.
 *  - siswa  : data siswa dari datacenter + kelas terkini & data orang tua.
 *
 * Akun yang datanya bersumber dari datacenter (punya guru_id/siswa_id)
 * bersifat read-only di sini: nama, email, dan kata sandinya disinkronkan
 * ulang dari datacenter setiap kali login (lihat Auth\LoginController),
 * jadi perubahan dari halaman ini akan tertimpa. Hanya akun lokal
 * (dibuat lewat Manajemen User) yang boleh mengubah akun & kata sandi.
 */
class ProfilController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('profil.edit', [
            'user' => $user,
            'akunLokal' => $this->akunLokal($user),
            'detail' => $this->detailProfil($user),
        ]);
    }

    /** Ubah data akun (nama & email) — hanya untuk akun lokal. */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $this->pastikanAkunLokal($user);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($user->id)],
        ], [], [
            'name' => 'Nama',
            'email' => 'Email',
        ]);

        $user->fill($data)->save();

        return redirect()->route('profil.edit')->with('success', 'Data profil berhasil diperbarui.');
    }

    /** Ganti kata sandi akun sendiri — hanya untuk akun lokal. */
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();
        $this->pastikanAkunLokal($user);

        $data = $request->validate([
            'password_lama' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [], [
            'password_lama' => 'Kata Sandi Lama',
            'password' => 'Kata Sandi Baru',
        ]);

        if (! Hash::check($data['password_lama'], $user->password)) {
            return back()->withErrors(['password_lama' => 'Kata sandi lama tidak sesuai.']);
        }

        $user->password = $data['password']; // di-hash otomatis oleh cast "hashed"
        $user->save();

        return redirect()->route('profil.edit')->with('success', 'Kata sandi berhasil diubah.');
    }

    /**
     * Akun lokal = dibuat lewat Manajemen User, bukan hasil sinkronisasi
     * login datacenter (guru via NIP / siswa via NISN).
     */
    private function akunLokal(User $user): bool
    {
        return $user->guru_id === null && $user->siswa_id === null;
    }

    private function pastikanAkunLokal(User $user): void
    {
        abort_if(! $this->akunLokal($user), 403, 'Data akun Anda dikelola aplikasi Datacenter dan tidak dapat diubah di sini.');
    }

    /**
     * Daftar baris detail profil (label => nilai) sesuai role user.
     *
     * @return array<int, array{0:string,1:?string}>
     */
    private function detailProfil(User $user): array
    {
        if ($user->isSiswa()) {
            return $this->detailSiswa($user);
        }

        if ($user->isGuru()) {
            return $this->detailGuru($user);
        }

        return $this->detailAdmin($user);
    }

    private function detailAdmin(User $user): array
    {
        $pegawai = $user->pegawai;

        if (! $pegawai) {
            return [
                ['Hak Akses', 'Administrator — akses penuh ke seluruh menu.'],
                ['Data Pegawai', null],
            ];
        }

        return [
            ['NIP', $pegawai->nip],
            ['Nama', $pegawai->nama],
            ['Jabatan', $pegawai->jabatan],
            ['Status Kepegawaian', $pegawai->status_kepegawaian],
            ['Pendidikan Terakhir', $pegawai->pendidikan_terakhir],
            ['Jenis Kelamin', $this->jenisKelamin($pegawai->jenis_kelamin)],
            ['Tempat, Tanggal Lahir', $this->tempatTanggalLahir($pegawai->tempat_lahir, $pegawai->tanggal_lahir)],
            ['Nomor HP', $pegawai->no_hp],
            ['Email', $pegawai->email],
            ['Alamat', $pegawai->alamat],
        ];
    }

    private function detailGuru(User $user): array
    {
        // Guru bisa berasal dari datacenter (guru_id) atau akun lokal yang
        // ditautkan ke data pegawai (pegawai_id).
        $guru = $user->guru;

        if (! $guru) {
            return $user->pegawai ? $this->detailAdmin($user) : [
                ['Hak Akses', 'Guru — akses Kurikulum, Ruang Belajar, dan Absensi.'],
                ['Data Guru', null],
            ];
        }

        // Penugasan yang ditampilkan mengikuti tahun ajaran yang dipilih di
        // topbar, supaya tidak bercampur dengan penugasan tahun lain.
        $tahunAjaranId = app(TahunAjaranTerpilih::class)->id();

        $mapel = GuruMapel::with('mataPelajaran')
            ->where('guru_id', $guru->id)
            ->when($tahunAjaranId, fn ($q) => $q->where('tahun_ajaran_id', $tahunAjaranId))
            ->get()
            ->pluck('mataPelajaran.nama_mapel')
            ->filter()
            ->unique()
            ->implode(', ');

        $perwalian = Kelas::where('wali_kelas_id', $guru->id)
            ->when($tahunAjaranId, fn ($q) => $q->where('tahun_ajaran_id', $tahunAjaranId))
            ->pluck('nama_rombel')
            ->implode(', ');

        return [
            ['NIP', $guru->nip],
            ['Nama PTK', $guru->nama_ptk],
            ['Jabatan', $guru->jabatan],
            ['Status Kepegawaian', $guru->status_kepegawaian],
            ['Jenis Kelamin', $this->jenisKelamin($guru->jenis_kelamin)],
            ['Tempat, Tanggal Lahir', $this->tempatTanggalLahir($guru->tempat_lahir, $guru->tanggal_lahir)],
            ['Nomor HP', $guru->nomor_hp],
            ['Email', $guru->email],
            ['Alamat', $guru->alamat],
            ['Mata Pelajaran Diampu', $mapel ?: null],
            ['Wali Kelas', $perwalian ?: null],
        ];
    }

    private function detailSiswa(User $user): array
    {
        $siswa = $user->siswa;

        if (! $siswa) {
            return [
                ['Hak Akses', 'Siswa — akses Ruang Belajar dan rekap absensi pribadi.'],
                ['Data Siswa', null],
            ];
        }

        $rombel = $siswa->rombelSaatIni()->with(['kelas', 'tahunAjaran'])->first();

        return [
            ['NISN', $siswa->nisn],
            ['NIS', $siswa->nis],
            ['Nama Siswa', $siswa->nama_siswa],
            ['Kelas Saat Ini', optional(optional($rombel)->kelas)->nama_rombel],
            ['Tahun Ajaran', optional(optional($rombel)->tahunAjaran)->nama_tahun_ajaran],
            ['Status Siswa', $siswa->status_siswa],
            ['Jenis Kelamin', $this->jenisKelamin($siswa->jenis_kelamin)],
            ['Tempat, Tanggal Lahir', $this->tempatTanggalLahir($siswa->tempat_lahir, $siswa->tanggal_lahir)],
            ['Agama', $siswa->agama],
            ['Nomor HP', $siswa->nomor_hp],
            ['Email', $siswa->email],
            ['Alamat', $siswa->alamat],
            ['Nama Ayah', $siswa->nama_ayah],
            ['Nama Ibu', $siswa->nama_ibu],
            ['Nomor HP Orang Tua', $siswa->nomor_hp_ortu],
        ];
    }

    private function jenisKelamin(?string $kode): ?string
    {
        return match ($kode) {
            'L' => 'Laki-laki',
            'P' => 'Perempuan',
            default => $kode,
        };
    }

    private function tempatTanggalLahir(?string $tempat, $tanggal): ?string
    {
        $tanggal = $tanggal ? $tanggal->format('d-m-Y') : null;

        return trim(implode(', ', array_filter([$tempat, $tanggal]))) ?: null;
    }
}
