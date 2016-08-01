<?php

namespace LaravelDoctrine\ORM\Auth\Passwords;

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
        $connection = isset($config['connection']) ? $config['connection'] : null;

        return new DoctrineTokenRepository(
            $this->app->make('registry')->getConnection($connection),
            $config['table'],
            $this->app['config']['app.key'],
            $config['expire']
        );
    }
}
