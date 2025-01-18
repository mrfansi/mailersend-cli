<?php

namespace App\Providers;

use App\Contracts\MailersendFactoryInterface;
use App\Factories\MailersendFactory;
use Illuminate\Support\ServiceProvider;

class MailersendServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(MailersendFactoryInterface::class, function ($app) {
            return new MailersendFactory($app->make('cache.store'));
        });
    }
}
