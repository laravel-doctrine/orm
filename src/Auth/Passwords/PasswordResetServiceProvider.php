<?php

namespace LaravelDoctrine\ORM\Auth\Passwords;

use Illuminate\Support\ServiceProvider;
use LaravelDoctrine\ORM\DoctrineManager;

class PasswordResetServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     * @return void
     */
    public function register()
    {
        $this->registerPasswordBroker();

        $this->app->make(DoctrineManager::class)->addPaths([
            __DIR__
        ]);
    }

    /**
     * Register the password broker instance.
     * @return void
     */
    protected function registerPasswordBroker()
    {
        $this->app->singleton('auth.password', function ($app) {
            return new PasswordBrokerManager($app);
        });

        $this->app->bind('auth.password.broker', function ($app) {
            return $app->make('auth.password')->broker();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['auth.password', 'auth.password.broker'];
    }
}
