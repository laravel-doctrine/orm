<?php


namespace LaravelDoctrine\ORM\Auth\Passwords;

use Closure;
use Illuminate\Auth\Passwords\PasswordBrokerManager as LaravelPasswordBrokerManager;
use Illuminate\Contracts\Auth\PasswordBrokerFactory as FactoryContract;

class PasswordBrokerManager extends LaravelPasswordBrokerManager implements FactoryContract
{
    /**
     * @var Closure[]
     */
    protected $customTokenCreators = [];

    /**
     * Register a custom provider creator Closure.
     *
     * @param  string  $name
     * @param  Closure $callback
     * @return $this
     */
    public function provider($name, Closure $callback)
    {
        $this->customProviderCreators[$name] = $callback;
    }

    public function tokenRepository($driver, Closure $callback)
    {
        $this->customTokenCreators[$driver] = $callback;
    }

    public function createTokenRepository(array $config)
    {
        $providerConfig = $this->app['config']['auth.providers.' . $config['provider']];

        if (isset($this->customTokenCreators[$providerConfig['driver']])) {
            return call_user_func(
                $this->customTokenCreators[$providerConfig['driver']], $this->app, $config
            );
        }

        return parent::createTokenRepository($config);
    }
}
