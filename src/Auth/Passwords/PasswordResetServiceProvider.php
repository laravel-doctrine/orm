<?php

namespace Brouwers\LaravelDoctrine\Auth\Passwords;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Support\ServiceProvider;

class PasswordResetServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     * @return void
     */
    public function register()
    {
        $this->registerPasswordBroker();

        $this->registerTokenRepository();
    }

    /**
     * Register the password broker instance.
     * @return void
     */
    protected function registerPasswordBroker()
    {
        $this->app->singleton('auth.password', function ($app) {
            // The password token repository is responsible for storing the email addresses
            // and password reset tokens. It will be used to verify the tokens are valid
            // for the given e-mail addresses. We will resolve an implementation here.
            $tokens = $app['auth.password.tokens'];

            $users = $app['auth']->driver()->getProvider();

            $view = $app['config']['auth.password.email'];

            // The password broker uses a token repository to validate tokens and send user
            // password e-mails, as well as validating that password reset process as an
            // aggregate service of sorts providing a convenient interface for resets.
            return new PasswordBroker(
                $tokens, $users, $app['mailer'], $view
            );
        });
    }

    /**
     * Register the token repository implementation.
     * @return void
     */
    protected function registerTokenRepository()
    {
        $this->app->singleton('auth.password.tokens', function ($app) {
            $key = $app['config']['app.key'];

            $expire = $app['config']->get('auth.reminder.expire', 60);

            return new DoctrineTokenRepository($this->app->make(EntityManagerInterface::class), $key, $expire);
        });
    }

    /**
     * Get the services provided by the provider.
     * @return array
     */
    public function provides()
    {
        return ['auth.password', 'auth.password.tokens'];
    }
}
