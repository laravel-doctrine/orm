<?php

use Doctrine\DBAL\Connection;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\ConnectionResolverInterface;
use LaravelDoctrine\ORM\Configuration\Connections\ConnectionManager;
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
    protected $resolver;

    /**
     * @var Repository
     */
    protected $config;

    protected function setUp()
    {
        $this->resolver = m::mock(ConnectionResolverInterface::class);
        $this->resolver->shouldReceive('make')->andReturnSelf();

        $this->config = m::mock(Repository::class);
        $this->config->shouldReceive('get');

        $this->manager = new ConnectionManager(
            $this->resolver
        );
    }

    public function test_driver_returns_the_default_driver()
    {
        $driver = m::mock(\Illuminate\Database\Connection::class);
        $driver->shouldReceive('getDoctrineConnection')->once()->andReturn(
            $conn = m::mock(Connection::class)
        );

        $this->resolver->shouldReceive('connection')->once()->with('mysql')->andReturn($driver);

        $this->assertInstanceOf(Connection::class, $this->manager->driver());
    }

    public function test_driver_can_return_a_given_driver()
    {
        $driver = m::mock(\Illuminate\Database\Connection::class);
        $driver->shouldReceive('getDoctrineConnection')->once()->andReturn(
            $conn = m::mock(Connection::class)
        );

        $this->resolver->shouldReceive('connection')->once()->with('sqlite')->andReturn($driver);

        $this->assertInstanceOf(Connection::class, $this->manager->driver('sqlite'));
    }

    public function test_cant_resolve_unsupported_drivers()
    {
        $this->setExpectedException(BadMethodCallException::class);
        $this->manager->driver('non-existing');
    }

    public function test_can_create_custom_drivers()
    {
        $this->manager->extend('new', function () {
            return 'connection';
        });

        $this->assertEquals('connection', $this->manager->driver('new'));
    }

    public function test_can_use_resolverlication_when_extending()
    {
        $this->manager->extend('new', function ($resolver) {
            $this->assertInstanceOf(Container::class, $resolver);
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
