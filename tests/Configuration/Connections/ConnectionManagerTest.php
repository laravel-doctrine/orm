<?php

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use LaravelDoctrine\ORM\Configuration\Connections\ConnectionManager;
use LaravelDoctrine\ORM\Configuration\Connections\MysqlConnection;
use LaravelDoctrine\ORM\Configuration\Connections\SqliteConnection;
use LaravelDoctrine\ORM\Exceptions\DriverNotFound;
use Mockery as m;

class ConnectionManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ConnectionManager
     */
    protected $manager;

    /**
     * @var Container
     */
    protected $app;

    /**
     * @var Repository
     */
    protected $config;

    protected function setUp()
    {
        $this->app = m::mock(Container::class);
        $this->app->shouldReceive('make')->andReturn(m::self());

        $this->config = m::mock(Repository::class);
        $this->config->shouldReceive('get');

        $this->manager = new ConnectionManager(
            $this->app
        );
    }

    public function test_driver_returns_the_default_driver()
    {
        $this->app->shouldReceive('resolve')->andReturn(
            (new MysqlConnection($this->config))->resolve()
        );

        $this->assertTrue(is_array($this->manager->driver()));
        $this->assertContains('pdo_mysql', $this->manager->driver());
    }

    public function test_driver_can_return_a_given_driver()
    {
        $this->app->shouldReceive('resolve')->andReturn(
            (new SqliteConnection($this->config))->resolve()
        );

        $this->assertTrue(is_array($this->manager->driver('sqlite')));
        $this->assertContains('pdo_sqlite', $this->manager->driver());
    }

    public function test_cant_resolve_unsupported_drivers()
    {
        $this->setExpectedException(DriverNotFound::class);
        $this->manager->driver('non-existing');
    }

    public function test_can_create_custom_drivers()
    {
        $this->manager->extend('new', function () {
            return 'connection';
        });

        $this->assertEquals('connection', $this->manager->driver('new'));
    }

    public function test_can_use_application_when_extending()
    {
        $this->manager->extend('new', function ($app) {
            $this->assertInstanceOf(Container::class, $app);
        });
    }

    public function test_can_replace_an_existing_driver()
    {
        $this->manager->extend('oci8', function () {
            return 'connection';
        });

        $this->assertEquals('connection', $this->manager->driver('oci8'));
    }

    protected function tearDown()
    {
        m::close();
    }
}
