<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Layout aplikasi memakai Bootstrap 5, jadi paginator harus
        // memakai template Bootstrap juga (default Laravel: Tailwind).
        Paginator::useBootstrapFive();
    }
}
