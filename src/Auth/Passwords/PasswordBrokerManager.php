<?php

namespace LaravelDoctrine\ORM\Auth\Passwords;

use Doctrine\Common\Persistence\ManagerRegistry;

class PasswordBrokerManager extends \Illuminate\Auth\Passwords\PasswordBrokerManager
{
    /**
     * Create a token repository instance based on the given configuration.
     *
     * @param array $config
     *
     * @return \Illuminate\Auth\Passwords\TokenRepositoryInterface
     */
    protected function createTokenRepository(array $config)
    {
        return new DoctrineTokenRepository(
            $this->app->make(ManagerRegistry::class)->getManagerForClass(PasswordReminder::class),
            $this->app['config']['app.key'],
            $config['expire']
        );
    }
}
