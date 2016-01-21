<?php

use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Auth\Passwords\PasswordBrokerManager as LaravelBrokerManager;
use Illuminate\Auth\Passwords\TokenRepositoryInterface;
use Illuminate\Contracts\Auth\PasswordBrokerFactory as FactoryContract;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Foundation\Application;
use LaravelDoctrine\ORM\Auth\Passwords\PasswordBrokerManager;
use Mockery as m;

class PasswordBrokerManagerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->app = m::mock(Application::class);
    }

    public function test_init_broker_manager()
    {
        $manager = new PasswordBrokerManager($this->app);

        $this->assertInstanceOf(FactoryContract::class, $manager);
        $this->assertInstanceOf(LaravelBrokerManager::class, $manager);
    }

    public function test_can_add_custom_provider()
    {
        $this->app                                  = [];
        $this->app['config']['auth.providers.test'] = ['driver' => 'test'];

        $manager = new PasswordBrokerManager($this->app);

        $manager->provider('test', function ($app, $config) {
            return new TestUserProvider;
        });

        $provider = $manager->createUserProvider('test');

        $this->assertInstanceOf(TestUserProvider::class, $provider, 'The manager should use provided closure to make a provider');
    }

    public function test_can_add_custom_token_repository()
    {
        $this->app                                  = [];
        $this->app['config']['auth.providers.test'] = ['driver' => 'test'];
        $this->app['config']['auth.passwords.test'] = [
            'provider' => 'test',
            'email'    => 'email',
            'table'    => 'table',
            'expire'   => 60
        ];
        $this->app['mailer'] = m::mock(Illuminate\Contracts\Mail\Mailer::class);

        $manager = new PasswordBrokerManager($this->app);

        $manager->provider('test', function ($app, $config) {
            return new TestUserProvider;
        });

        $manager->tokenRepository('test', function ($app, $config) {
            return new TestTokenRepository;
        });

        $broker = $manager->broker('test');
        $tokens = $this->extractTokens($broker);

        $this->assertInstanceOf(PasswordBroker::class, $broker);
        $this->assertInstanceOf(TestTokenRepository::class, $tokens);
    }

    private function extractTokens($broker)
    {
        $reflectionClass = new ReflectionClass(get_class($broker));
        $tokens          = $reflectionClass->getProperty('tokens');
        $tokens->setAccessible(true);

        return $tokens->getValue($broker);
    }
}

class TestTokenRepository implements TokenRepositoryInterface
{
    /**
     * Create a new token.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword $user
     * @return string
     */
    public function create(\Illuminate\Contracts\Auth\CanResetPassword $user)
    {
        //
    }

    /**
     * Determine if a token record exists and is valid.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword $user
     * @param  string                                      $token
     * @return bool
     */
    public function exists(\Illuminate\Contracts\Auth\CanResetPassword $user, $token)
    {
        //
    }

    /**
     * Delete a token record.
     *
     * @param  string $token
     * @return void
     */
    public function delete($token)
    {
        //
    }

    /**
     * Delete expired tokens.
     *
     * @return void
     */
    public function deleteExpired()
    {
        //
    }
}

class TestUserProvider implements UserProvider
{
    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed                                           $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        //
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed                                           $identifier
     * @param  string                                          $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        //
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  string                                     $token
     * @return void
     */
    public function updateRememberToken(\Illuminate\Contracts\Auth\Authenticatable $user, $token)
    {
        //
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array                                           $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        //
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  array                                      $credentials
     * @return bool
     */
    public function validateCredentials(\Illuminate\Contracts\Auth\Authenticatable $user, array $credentials)
    {
        //
    }
}
