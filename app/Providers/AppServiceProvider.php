<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        // Добавляем заголовок Accept-Ranges для всех ответов
        if (!headers_sent()) {
            header('Accept-Ranges: bytes');
        }
    }
}
