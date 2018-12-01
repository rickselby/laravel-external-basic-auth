<?php

namespace RickSelby\Laravel\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class ExternalBasicAuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Auth::extend('external', function ($app, $name, array $config) {
            return new ExternalBasicAuthGuard(
                Auth::createUserProvider($config['provider']),
                $app
            );
        });
        config();
    }

    public function register()
    {
        // This function intentionally left blank. (Nothing to register)
    }
}
