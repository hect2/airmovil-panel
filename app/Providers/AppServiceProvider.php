<?php

namespace App\Providers;

use App\Services\FirebaseAuthService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        If (env('APP_SSL')??false) {
            $this->app['request']->server->set('HTTPS', true);
        }
        Schema::defaultStringLength(191);

        $this->app->singleton(FirebaseAuthService::class, function () {
            return new FirebaseAuthService();
        });

    }
}
