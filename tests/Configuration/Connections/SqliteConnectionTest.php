<?php

use Illuminate\Contracts\Config\Repository;
use LaravelDoctrine\ORM\Configuration\Connections\SqliteConnection;
use Mockery as m;
use Mockery\Mock;

class SqliteConnectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mock
     */
    protected $config;

    /**
     * @var SqliteConnection
     */
    protected $connection;

    protected function setUp()
    {
        $this->config = m::mock(Repository::class);

        $this->connection = new SqliteConnection($this->config);
    }

    public function test_can_resolve()
    {
        $resolved = $this->connection->resolve([
            'driver'   => 'pdo_sqlite',
            'database' => 'path',
            'username' => 'username',
            'password' => 'password',
            'prefix'   => 'prefix',
        ]);

        $this->assertEquals('pdo_sqlite', $resolved['driver']);
        $this->assertEquals('username', $resolved['user']);
        $this->assertEquals('password', $resolved['password']);
        $this->assertEquals('prefix', $resolved['prefix']);
        $this->assertFalse($resolved['memory']);
        $this->assertEquals('path', $resolved['path']);
    }

    public function test_can_resolve_with_in_memory_database()
    {
        $resolved = $this->connection->resolve([
            'driver'   => 'pdo_sqlite',
            'database' => ':memory',
            'username' => 'username',
            'password' => 'password',
            'prefix'   => 'prefix',
        ]);

        $this->assertEquals('pdo_sqlite', $resolved['driver']);
        $this->assertEquals('username', $resolved['user']);
        $this->assertEquals('password', $resolved['password']);
        $this->assertEquals('prefix', $resolved['prefix']);
        $this->assertTrue($resolved['memory']);
        $this->assertEquals(':memory', $resolved['path']);
    }

    public function test_can_resolve_with_full_in__memory_database()
    {
        $resolved = $this->connection->resolve([
            'database' => ':memory:',
        ]);

        $this->assertTrue($resolved['memory']);
        $this->assertEquals(':memory:', $resolved['path']);
    }

    protected function tearDown()
    {
        m::close();
    }
}
