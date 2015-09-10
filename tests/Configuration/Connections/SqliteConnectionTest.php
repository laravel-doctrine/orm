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
        $this->config->shouldReceive('get')->with('database.connections.sqlite.database')->times(3)
                     ->andReturn('path');
        $this->config->shouldReceive('get')->with('database.connections.sqlite.username')->once()
                     ->andReturn('username');
        $this->config->shouldReceive('get')->with('database.connections.sqlite.password')->once()
                     ->andReturn('password');
        $this->config->shouldReceive('get')->with('database.connections.sqlite.prefix')->once()
                     ->andReturn('prefix');

        $resolved = $this->connection->resolve();

        $this->assertEquals('pdo_sqlite', $resolved['driver']);
        $this->assertEquals('username', $resolved['user']);
        $this->assertEquals('password', $resolved['password']);
        $this->assertEquals('prefix', $resolved['prefix']);
        $this->assertFalse($resolved['memory']);
        $this->assertEquals('path', $resolved['path']);
    }

    public function test_can_resolve_with_in_memory_database()
    {
        $this->config->shouldReceive('get')->with('database.connections.sqlite.database')->times(2)
                     ->andReturn(':memory');
        $this->config->shouldReceive('get')->with('database.connections.sqlite.username')->once()
                     ->andReturn('username');
        $this->config->shouldReceive('get')->with('database.connections.sqlite.password')->once()
                     ->andReturn('password');
        $this->config->shouldReceive('get')->with('database.connections.sqlite.prefix')->once()
                     ->andReturn('prefix');

        $resolved = $this->connection->resolve();

        $this->assertEquals('pdo_sqlite', $resolved['driver']);
        $this->assertEquals('username', $resolved['user']);
        $this->assertEquals('password', $resolved['password']);
        $this->assertEquals('prefix', $resolved['prefix']);
        $this->assertTrue($resolved['memory']);
        $this->assertNull($resolved['path']);
    }
}
