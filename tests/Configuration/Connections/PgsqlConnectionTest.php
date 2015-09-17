<?php

use Illuminate\Contracts\Config\Repository;
use LaravelDoctrine\ORM\Configuration\Connections\PgsqlConnection;
use Mockery as m;
use Mockery\Mock;

class PgsqlConnectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mock
     */
    protected $config;

    /**
     * @var PgsqlConnection
     */
    protected $connection;

    protected function setUp()
    {
        $this->config = m::mock(Repository::class);

        $this->connection = new PgsqlConnection($this->config);
    }

    public function test_can_resolve()
    {
        $this->config->shouldReceive('get')->with('database.connections.pgsql.host')->once()->andReturn('host');
        $this->config->shouldReceive('get')->with('database.connections.pgsql.database')->once()->andReturn('database');
        $this->config->shouldReceive('get')->with('database.connections.pgsql.username')->once()->andReturn('username');
        $this->config->shouldReceive('get')->with('database.connections.pgsql.password')->once()->andReturn('password');
        $this->config->shouldReceive('get')->with('database.connections.pgsql.charset')->once()->andReturn('charset');
        $this->config->shouldReceive('get')->with('database.connections.pgsql.port')->once()->andReturn('port');
        $this->config->shouldReceive('get')->with('database.connections.pgsql.sslmode')->once()->andReturn('sslmode');
        $this->config->shouldReceive('get')->with('database.connections.pgsql.prefix')->once()->andReturn('prefix');

        $resolved = $this->connection->resolve();

        $this->assertEquals('pdo_pgsql', $resolved['driver']);
        $this->assertEquals('host', $resolved['host']);
        $this->assertEquals('database', $resolved['dbname']);
        $this->assertEquals('username', $resolved['user']);
        $this->assertEquals('password', $resolved['password']);
        $this->assertEquals('charset', $resolved['charset']);
        $this->assertEquals('port', $resolved['port']);
        $this->assertEquals('sslmode', $resolved['sslmode']);
        $this->assertEquals('prefix', $resolved['prefix']);
    }

    protected function tearDown()
    {
        m::close();
    }
}
