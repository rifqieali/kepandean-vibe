<?php

namespace App\Providers;

use App\Http\Controllers\SitemapController;
use App\Models\Berita;
use App\Models\Kegiatan;
use App\Models\Pengumuman;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        // Issue #20: HTTPS wajib di production agar cookie dan login aman.
        if (app()->isProduction()) {
            URL::forceScheme('https');
        }

        // Issue #20: sitemap di-cache 1 jam dan diregenerasi tiap ada
        // konten terbitan yang berubah (bukan rebuild manual).
        foreach ([Berita::class, Pengumuman::class, Kegiatan::class] as $model) {
            $model::saved(static function (): void {
                SitemapController::forgetCache();
            });
            $model::deleted(static function (): void {
                SitemapController::forgetCache();
            });
        }

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
