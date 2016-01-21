<?php

namespace LaravelDoctrine\ORM\Auth\Passwords;

use Doctrine\Common\Persistence\ManagerRegistry;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use LaravelDoctrine\ORM\Auth\DoctrineUserProvider;
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
        $this->registerPasswordBrokerManager();
    }

    /**
     * Register the password broker instance.
     * @return void
     */
    protected function registerPasswordBrokerManager()
    {
        $this->app->singleton('auth.password', function ($app) {

            $manager = new PasswordBrokerManager($app);

            // This is a repeat of extendAuthManager from the main package service provider
            // We need to do this here as well because the PasswordBrokerManager keep its
            // own list of custom user providers definitions. See CreateUserProviders.
            $manager->provider('doctrine', function ($app, $config) {

                // We could use the AuthManager to retrieve the UserProvider already declared by
                // the main package service provider, but I've avoided doing that here so as
                // to avoid further indirection and a deeper stack.

                $entity = $config['model'];

                $em = $app['registry']->getManagerForClass($entity);

                if (!$em) {
                    throw new InvalidArgumentException("No EntityManager is set-up for {$entity}");
                }

                return new DoctrineUserProvider(
                    $app['hash'],
                    $em,
                    $entity
                );
            });

            // We have extended the Laravel PasswordBrokerManager to allow us to register a
            // custom TokenRepository definition. The token repository stores the tokens
            // and is used to verify they are valid when users reset their credentials
            $manager->tokenRepository('doctrine', function ($app, $config) {
                return new DoctrineTokenRepository(
                    $app->make(ManagerRegistry::class)->getManagerForClass(PasswordReminder::class),
                    $app['config']['app.key'],
                    $config['expire']
                );
            });

            return $manager;
        });
    }

    /**
     * Get the services provided by the provider.
     * @return array
     */
    public function provides()
    {
        return ['auth.password'];
    }
}
