<?php

namespace Marcos\MailbitLibraryLaravel;

use Illuminate\Support\ServiceProvider;

class MailbitServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Mailbit::class, function ($app) {
            return new Mailbit(config('services.mailbit.api_key'));
        });
    }
}
