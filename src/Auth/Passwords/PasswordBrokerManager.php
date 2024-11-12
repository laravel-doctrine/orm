<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Auth\Passwords;

use Illuminate\Auth\Passwords\TokenRepositoryInterface;
use Illuminate\Support\Str;

use function base64_decode;
use function substr;

class PasswordBrokerManager extends \Illuminate\Auth\Passwords\PasswordBrokerManager
{
    /**
     * Create a token repository instance based on the given configuration.
     *
     * @param mixed[] $config
     */
    protected function createTokenRepository(array $config): TokenRepositoryInterface
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
            $config['throttle'] ?? 60,
        );
    }
}
