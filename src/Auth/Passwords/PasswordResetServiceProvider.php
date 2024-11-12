<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Auth\Passwords;

use Illuminate\Support\ServiceProvider;

class PasswordResetServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->registerPasswordBroker();
    }

    /**
     * Register the password broker instance.
     */
    protected function registerPasswordBroker(): void
    {
        $this->app->singleton('auth.password', static function ($app) {
            return new PasswordBrokerManager($app);
        });

        $this->app->bind('auth.password.broker', static function ($app) {
            return $app->make('auth.password')->broker();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return mixed[]
     */
    public function provides(): array
    {
        return ['auth.password', 'auth.password.broker'];
    }
}
