<?php

namespace LaravelDoctrine\ORM\Auth\Passwords;

use Illuminate\Support\Str;

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
        $hashKey = $this->app['config']['app.key'];

        if (Str::startsWith($hashKey, 'base64:')) {
            $hashKey = base64_decode(substr($hashKey, 7));
        }

        $connection = $config['connection'] ?? null;

        return new DoctrineTokenRepository(
            $this->app->make('registry')->getConnection($connection),
            $this->app->make('hash'),
            $config['table'],
            $hashKey,
            $config['expire'],
            $config['throttle'] ?? 60
        );
    }
}
