@extends('layouts.app')
@section('title', ($item ? 'Edit' : 'Isi').' Agenda Mengajar')
@section('content')

@php($isEdit = $item !== null)

<div class="card shadow-sm" style="max-width:760px;">
    <div class="card-header bg-white">
        <i class="bi bi-journal-check me-2"></i>{{ $isEdit ? 'Ubah' : 'Isi' }} Agenda Mengajar — Form Pengisian Jurnal Kelas
    </div>
    <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" enctype="multipart/form-data"
                      action="{{ $isEdit ? route('kurikulum.agenda.update', $item->id) : route('kurikulum.agenda.store') }}">
                    @csrf
                    @if($isEdit) @method('PUT') @endif

                    <div class="mb-3">
                        <label class="form-label">Waktu Pengisian Agenda</label>
                        <input type="text" class="form-control bg-light" readonly
                               value="{{ ($isEdit ? $item->waktu_pengisian : now())->locale('id')->translatedFormat('l, d F Y \p\u\k\u\l H:i:s') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Guru <span class="text-danger">*</span></label>
                        <select name="guru_id" id="guru_id" class="form-select" required>
                            <option value="">-- Pilih Guru --</option>
                            @foreach($guruList as $g)
                                <option value="{{ $g->id }}" @selected((string) old('guru_id', optional($item)->guru_id) === (string) $g->id)>
                                    {{ $g->nama_ptk }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mengajar Sebagai</label>
                        <select name="mengajar_sebagai" class="form-select">
                            <option value="normal" @selected(old('mengajar_sebagai', optional($item)->mengajar_sebagai ?? 'normal') === 'normal')>Diri Sendiri (Jadwal Normal)</option>
                            <option value="pengganti" @selected(old('mengajar_sebagai', optional($item)->mengajar_sebagai) === 'pengganti')>Guru Pengganti</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pilih Jam Mengajar (Hari {{ $hariIni }})</label>
                        <select id="jadwal_hari_ini" class="form-select">
                            <option value="">-- Pilih dari jadwal hari ini --</option>
                            @foreach($jadwalHariIni as $j)
                                <option value="{{ $j->id }}"
                                        data-guru="{{ $j->guru_id }}"
                                        data-jam-ke="{{ optional($j->jamMengajar)->jam_ke }}"
                                        data-kelas="{{ $j->kelas_id }}"
                                        data-mapel="{{ $j->mata_pelajaran_id }}">
                                    {{ optional($j->guru)->nama_ptk }} — {{ optional($j->jamMengajar)->label ?? 'Jam ?' }} — {{ optional($j->kelas)->nama_rombel }} — {{ optional($j->mataPelajaran)->nama_mapel }}
                                </option>
                            @endforeach
                        </select>
                        @if($jadwalHariIni->isEmpty())
                            <div class="alert alert-warning py-2 mt-2 mb-0 small">Tidak ada jadwal ditemukan untuk hari ini. Isi jam, kelas, dan mapel secara manual.</div>
                        @endif
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label">Jam Ke</label>
                            <input type="text" name="jam_ke" id="jam_ke" class="form-control" placeholder="mis. 1-3"
                                   value="{{ old('jam_ke', optional($item)->jam_ke) }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Jumlah Jam (JP) <span class="text-danger">*</span></label>
                            <input type="number" name="jumlah_jam" id="jumlah_jam" class="form-control" min="1" max="12" required
                                   value="{{ old('jumlah_jam', optional($item)->jumlah_jam ?? 1) }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Kelas <span class="text-danger">*</span></label>
                            <select name="kelas_id" id="kelas_id" class="form-select" required>
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($kelasList as $k)
                                    <option value="{{ $k->id }}" data-siswa="{{ $siswaPerKelas[$k->id] ?? 0 }}"
                                        @selected((string) old('kelas_id', optional($item)->kelas_id) === (string) $k->id)>
                                        {{ $k->nama_rombel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Paralel</label>
                            <input type="text" name="paralel" class="form-control" placeholder="1"
                                   value="{{ old('paralel', optional($item)->paralel ?? '1') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Mata Pelajaran <span class="text-danger">*</span></label>
                            <select name="mata_pelajaran_id" id="mata_pelajaran_id" class="form-select" required>
                                <option value="">Terisi otomatis dari jam mengajar...</option>
                                @foreach($mapelList as $m)
                                    <option value="{{ $m->id }}" @selected((string) old('mata_pelajaran_id', optional($item)->mata_pelajaran_id) === (string) $m->id)>
                                        {{ $m->nama_mapel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-2 text-center mb-3">
                        <div class="col-4">
                            <div class="border rounded p-2 bg-light">
                                <div class="small text-muted">Total Murid</div>
                                <input type="number" name="jumlah_siswa" id="jumlah_siswa" min="0" required
                                       class="form-control form-control-sm text-center fw-bold border-0 bg-light"
                                       value="{{ old('jumlah_siswa', optional($item)->jumlah_siswa ?? 0) }}">
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2 bg-light">
                                <div class="small text-muted">Hadir</div>
                                <input type="number" name="hadir" id="hadir" min="0" required readonly
                                       class="form-control form-control-sm text-center fw-bold border-0 bg-light text-success"
                                       value="{{ old('hadir', optional($item)->hadir ?? 0) }}">
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2 bg-light">
                                <div class="small text-muted">Tidak Hadir</div>
                                <input type="number" name="absen" id="absen" min="0" required readonly
                                       class="form-control form-control-sm text-center fw-bold border-0 bg-light text-danger"
                                       value="{{ old('absen', optional($item)->absen ?? 0) }}">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Siswa (Daftar Tidak Hadir)</label>
                        <div id="daftar_siswa" class="border rounded p-2" style="max-height:180px; overflow-y:auto;">
                            <div class="text-muted small">Pilih kelas dulu untuk menampilkan daftar siswa.</div>
                        </div>
                        <div class="form-text">Centang siswa yang tidak hadir; jumlah Hadir/Tidak Hadir dihitung otomatis.</div>
                        <input type="hidden" name="siswa_absen" id="siswa_absen" value="{{ old('siswa_absen', optional($item)->siswa_absen) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pilih Modul Ajar (Rencana Pembelajaran)</label>
                        <select name="modul_ajar_id" id="modul_ajar_id" class="form-select">
                            <option value="">-- Pilih Modul Ajar --</option>
                            @foreach($modulList as $mod)
                                <option value="{{ $mod->id }}" data-mapel="{{ $mod->mata_pelajaran_id }}" data-judul="{{ $mod->judul }}"
                                    @selected((string) old('modul_ajar_id', optional($item)->modul_ajar_id) === (string) $mod->id)>
                                    {{ $mod->judul }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Pilihan modul akan muncul setelah Anda memilih Jam Mengajar / mapel.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Materi Pembelajaran</label>
                        <textarea name="materi" id="materi" rows="2" class="form-control"
                                  placeholder="Akan terisi otomatis dari modul...">{{ old('materi', optional($item)->materi) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan Guru</label>
                        <textarea name="catatan" rows="2" class="form-control"
                                  placeholder="Misal: Situasi tertib, kendala, dll.">{{ old('catatan', optional($item)->catatan) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label d-block">Photo Kegiatan</label>

                        <div class="border border-2 border-dashed rounded p-3 text-center">
                            <div class="d-flex gap-2 justify-content-center">
                                <label class="btn btn-outline-primary mb-0">
                                    <i class="bi bi-camera me-1"></i>Kamera
                                    <input type="file" name="photo" id="photo_kamera" accept="image/*" capture="environment" hidden>
                                </label>
                                <label class="btn btn-outline-success mb-0">
                                    <i class="bi bi-image me-1"></i>Galeri
                                    <input type="file" id="photo_galeri" accept="image/*" hidden>
                                </label>
                            </div>
                            <div id="photo_nama" class="small text-muted mt-2">
                                {{ $isEdit && $item->photo ? 'Photo tersimpan: '.basename($item->photo) : 'Belum ada file dipilih.' }}
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 pt-2">
                        <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Agenda</button>
                        <a href="{{ route('kurikulum.agenda.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
    </div>
</div>

<script>
(function () {
    // Nama siswa per kelas (tahun ajaran aktif) untuk checkbox tidak hadir.
    const siswaPerKelas = @json($siswaPerKelasNama);

    const jadwal = document.getElementById('jadwal_hari_ini');
    const guru = document.getElementById('guru_id');
    const jamKe = document.getElementById('jam_ke');
    const jumlahJam = document.getElementById('jumlah_jam');
    const kelas = document.getElementById('kelas_id');
    const mapel = document.getElementById('mata_pelajaran_id');
    const jumlahSiswa = document.getElementById('jumlah_siswa');
    const hadir = document.getElementById('hadir');
    const absen = document.getElementById('absen');
    const daftarSiswa = document.getElementById('daftar_siswa');
    const siswaAbsen = document.getElementById('siswa_absen');
    const modul = document.getElementById('modul_ajar_id');
    const materi = document.getElementById('materi');

    // Nilai tersimpan (mode edit / old input) supaya checkbox ikut tercentang.
    const absenTersimpan = (siswaAbsen.value || '').split(',').map(s => s.replace(/\s*\(Alpa\)\s*$/i, '').trim()).filter(Boolean);

    function hitung() {
        const dicentang = daftarSiswa.querySelectorAll('input[type=checkbox]:checked');
        absen.value = dicentang.length;
        hadir.value = Math.max((parseInt(jumlahSiswa.value) || 0) - dicentang.length, 0);
        siswaAbsen.value = Array.from(dicentang).map(c => c.value + ' (Alpa)').join(', ');
    }

    function tampilkanSiswa() {
        const namaList = siswaPerKelas[kelas.value] || [];
        if (!kelas.value || namaList.length === 0) {
            daftarSiswa.innerHTML = '<div class="text-muted small">' + (kelas.value ? 'Belum ada data siswa untuk kelas ini.' : 'Pilih kelas dulu untuk menampilkan daftar siswa.') + '</div>';
            hitung();
            return;
        }
        daftarSiswa.innerHTML = namaList.map(function (nama, i) {
            const checked = absenTersimpan.includes(nama) ? 'checked' : '';
            return '<div class="form-check"><input class="form-check-input" type="checkbox" id="s' + i + '" value="' + nama.replace(/"/g, '&quot;') + '" ' + checked + '>' +
                   '<label class="form-check-label small" for="s' + i + '">' + nama + '</label></div>';
        }).join('');
        daftarSiswa.querySelectorAll('input').forEach(c => c.addEventListener('change', hitung));
        hitung();
    }

    function isiJumlahSiswa() {
        const opt = kelas.selectedOptions[0];
        if (opt && opt.dataset.siswa !== undefined) jumlahSiswa.value = opt.dataset.siswa;
    }

    // Filter dropdown jadwal sesuai guru terpilih.
    function filterJadwal() {
        Array.from(jadwal.options).forEach(function (opt) {
            if (!opt.value) return;
            opt.hidden = !!guru.value && opt.dataset.guru !== guru.value;
        });
    }

    jadwal.addEventListener('change', function () {
        const opt = jadwal.selectedOptions[0];
        if (!opt || !opt.value) return;
        if (opt.dataset.guru && !guru.value) guru.value = opt.dataset.guru;
        if (opt.dataset.jamKe) jamKe.value = opt.dataset.jamKe;
        if (opt.dataset.kelas) kelas.value = opt.dataset.kelas;
        if (opt.dataset.mapel) mapel.value = opt.dataset.mapel;
        jumlahJam.value = 1;
        isiJumlahSiswa();
        tampilkanSiswa();
    });

    guru.addEventListener('change', filterJadwal);
    kelas.addEventListener('change', function () { isiJumlahSiswa(); tampilkanSiswa(); });
    jumlahSiswa.addEventListener('input', hitung);

    modul.addEventListener('change', function () {
        const opt = modul.selectedOptions[0];
        if (opt && opt.dataset.judul && !materi.value.trim()) materi.value = opt.dataset.judul;
    });

    // Photo: dua tombol (Kamera/Galeri) memakai satu name="photo" — sinkronkan.
    const fotoKamera = document.getElementById('photo_kamera');
    const fotoGaleri = document.getElementById('photo_galeri');
    const fotoNama = document.getElementById('photo_nama');
    fotoGaleri.addEventListener('change', function () {
        if (fotoGaleri.files.length) {
            fotoKamera.files = fotoGaleri.files;
            fotoNama.textContent = fotoGaleri.files[0].name;
        }
    });
    fotoKamera.addEventListener('change', function () {
        if (fotoKamera.files.length) fotoNama.textContent = fotoKamera.files[0].name;
    });

    // Inisialisasi saat halaman dibuka (mode edit / gagal validasi).
    filterJadwal();
    if (kelas.value) tampilkanSiswa();
})();
</script>

@endsection
