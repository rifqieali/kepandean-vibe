<?php

namespace App\Providers;

use App\Models\Berita;
use App\Models\HeroSlide;
use App\Models\Kategori;
use App\Models\Pejabat;
use App\Models\Pengumuman;
use App\Models\Statistik;
use App\Models\User;
use App\Policies\BeritaPolicy;
use App\Policies\HeroSlidePolicy;
use App\Policies\KategoriPolicy;
use App\Policies\PejabatPolicy;
use App\Policies\PengumumanPolicy;
use App\Policies\StatistikPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => UserPolicy::class,
        Berita::class => BeritaPolicy::class,
        HeroSlide::class => HeroSlidePolicy::class,
        Kategori::class => KategoriPolicy::class,
        Pejabat::class => PejabatPolicy::class,
        Pengumuman::class => PengumumanPolicy::class,
        Statistik::class => StatistikPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('upload-media', function (User $user) {
            $role = $user->getAttribute('role');

            return $user->hasVerifiedEmail()
                && is_string($role)
                && in_array($role, ['developer', 'admin_desa', 'editor'], true);
        });

        Gate::define('publish-content', function (User $user) {
            return $user->canPublish();
        });

        Gate::define('manage-users', function (User $user) {
            return $user->canManageUsers();
        });
    }
}
