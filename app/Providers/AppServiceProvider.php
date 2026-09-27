<?php

namespace App\Providers;

use App\Support\TahunAjaranTerpilih;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Satu instance per request supaya daftar tahun ajaran hanya
        // di-query sekali walau dipakai di banyak controller & view.
        $this->app->singleton(TahunAjaranTerpilih::class);
    }

    public function boot(): void
    {
        // Layout aplikasi memakai Bootstrap 5, jadi paginator harus
        // memakai template Bootstrap juga (default Laravel: Tailwind).
        Paginator::useBootstrapFive();

        // URL file upload (logo, lampiran, dll.) mengikuti host yang sedang
        // diakses, bukan APP_URL di .env — supaya gambar tetap tampil walau
        // APP_URL di server masih "http://localhost" atau beda domain.
        if (! $this->app->runningInConsole()) {
            config(['filesystems.disks.public.url' => url('storage')]);
        }

        // Dropdown tahun ajaran di topbar tersedia di seluruh halaman.
        View::composer('layouts.app', function ($view) {
            $view->with('tahunAjaranTerpilih', app(TahunAjaranTerpilih::class));
        });
    }
}
