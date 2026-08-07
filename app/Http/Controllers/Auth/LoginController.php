<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\IdentitasSekolah;
use App\Models\Siswa;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        // Nama sekolah diambil dari Master Data > Identitas Sekolah; bila
        // belum diisi, pakai nama aplikasi sebagai cadangan.
        $namaSekolah = IdentitasSekolah::value('nama_sekolah') ?: config('app.name');

        return view('auth.login', compact('namaSekolah'));
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required'],
        ], [], [
            'login' => 'Email / NIP',
            'password' => 'Kata Sandi',
        ]);

        $remember = $request->boolean('remember');

        // 1) Coba akun lokal (email) — admin/guru/siswa yang dibuat di Manajemen User.
        $lokalBerhasil = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL)
            && Auth::attempt(['email' => $credentials['login'], 'password' => $credentials['password']], $remember);

        // 2) Kalau bukan akun lokal, coba login guru (NIP) lalu siswa (NISN)
        //    memakai password dari database datacenter.
        if (! $lokalBerhasil
            && ! $this->loginGuruDatacenter($credentials['login'], $credentials['password'], $remember)
            && ! $this->loginSiswaDatacenter($credentials['login'], $credentials['password'], $remember)) {
            return back()->withErrors([
                'login' => 'Email/NIP/NISN atau kata sandi yang Anda masukkan salah.',
            ])->onlyInput('login');
        }

        $request->session()->regenerate();

        if (! $request->user()->aktif) {
            Auth::logout();
            return back()->withErrors([
                'login' => 'Akun Anda tidak aktif. Hubungi administrator.',
            ]);
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Autentikasi guru terhadap tabel "guru" database datacenter (NIP +
     * password yang sama dengan login di aplikasi datacenter). Bila cocok,
     * akun lokal role "guru" dibuat/disinkronkan otomatis lalu di-login-kan.
     */
    private function loginGuruDatacenter(string $login, string $password, bool $remember): bool
    {
        $guru = Guru::where('nip', $login)
            ->orWhere('email', $login)
            ->first();

        if (! $guru || ! $guru->is_aktif || ! $guru->password || ! Hash::check($password, $guru->password)) {
            return false;
        }

        $user = User::firstOrNew(['guru_id' => $guru->id]);
        $user->name = $guru->nama_ptk;
        $user->role = User::ROLE_GURU;
        $user->aktif = true;
        $user->password = $password; // di-hash otomatis oleh cast "hashed"

        // Email lokal harus unik; pakai email guru bila ada dan belum dipakai
        // akun lain, selain itu pakai email sintetis berbasis NIP.
        $emailGuru = $guru->email;
        $emailDipakai = $emailGuru && User::where('email', $emailGuru)
            ->where(fn ($q) => $q->whereNull('guru_id')->orWhere('guru_id', '!=', $guru->id))
            ->exists();
        $user->email = ($emailGuru && ! $emailDipakai) ? $emailGuru : $guru->nip.'@guru.datacenter';

        $user->save();

        Auth::login($user, $remember);

        return true;
    }

    /**
     * Autentikasi siswa terhadap tabel "siswa" database datacenter (NISN +
     * password yang sama dengan login di aplikasi datacenter). Bila cocok,
     * akun lokal role "siswa" dibuat/disinkronkan otomatis lalu di-login-kan.
     */
    private function loginSiswaDatacenter(string $login, string $password, bool $remember): bool
    {
        $siswa = Siswa::where('nisn', $login)
            ->orWhere('email', $login)
            ->first();

        if (! $siswa || ! $siswa->is_aktif || ! $siswa->password || ! Hash::check($password, $siswa->password)) {
            return false;
        }

        $user = User::firstOrNew(['siswa_id' => $siswa->id]);
        $user->name = $siswa->nama_siswa;
        $user->role = User::ROLE_SISWA;
        $user->aktif = true;
        $user->password = $password; // di-hash otomatis oleh cast "hashed"

        $emailSiswa = $siswa->email;
        $emailDipakai = $emailSiswa && User::where('email', $emailSiswa)
            ->where(fn ($q) => $q->whereNull('siswa_id')->orWhere('siswa_id', '!=', $siswa->id))
            ->exists();
        $user->email = ($emailSiswa && ! $emailDipakai) ? $emailSiswa : $siswa->nisn.'@siswa.datacenter';

        $user->save();

        Auth::login($user, $remember);

        return true;
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
