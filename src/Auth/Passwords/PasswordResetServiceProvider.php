<?php

namespace LaravelDoctrine\ORM\Auth\Passwords;

use Doctrine\Common\Persistence\ManagerRegistry;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Support\ServiceProvider;
use LaravelDoctrine\ORM\DoctrineManager;

class PasswordResetServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     * @var bool
     */
    protected $defer = true;

    /**
     * @param DoctrineManager $manager
     */
    public function boot(DoctrineManager $manager)
    {
        // The path to PasswordReminder should be added, so the entity can be found
        $manager->addPaths([
            __DIR__
        ], $manager->getDefaultManagerName());
    }

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
            return new DoctrineTokenRepository(
                $this->app->make(ManagerRegistry::class)->getManagerForClass(PasswordReminder::class),
                $app['config']['app.key'],
                $app['config']->get('auth.password.expire', 60)
            );
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
