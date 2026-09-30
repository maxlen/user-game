<?php

namespace App\Providers;

use App\Contracts\RandomNumberGenerator;
use App\Repositories\AccessLinkRepository;
use App\Support\SecureRandomNumberGenerator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(RandomNumberGenerator::class, SecureRandomNumberGenerator::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::bind('accessLink', fn (string $token) => app(AccessLinkRepository::class)->findByToken($token) ?? abort(404));
    }
}
