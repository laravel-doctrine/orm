<?php

namespace LaravelDoctrine\ORM\Auth\Passwords;

use Illuminate\Support\ServiceProvider;
use LaravelDoctrine\ORM\DoctrineManager;

class PasswordResetServiceProvider extends ServiceProvider
{
    /**
     * @param DoctrineManager $manager
     */
    public function boot(DoctrineManager $manager)
    {
        // The path to PasswordReminder should be added, so the entity can be found
        $manager->addPaths([
            __DIR__
        ]);
    }

    /**
     * Register the service provider.
     * @return void
     */
    public function register()
    {
        $this->registerPasswordBroker();
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
