<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('admin', fn (User $user) => $user->role === User::ROLE_ADMIN);
        Gate::define('guru', fn (User $user) => $user->role === User::ROLE_GURU);
        Gate::define('siswa', fn (User $user) => $user->role === User::ROLE_SISWA);
    }
}
