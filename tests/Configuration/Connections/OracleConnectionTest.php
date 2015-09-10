<?php

use Illuminate\Contracts\Config\Repository;
use LaravelDoctrine\ORM\Configuration\Connections\OracleConnection;
use Mockery as m;
use Mockery\Mock;

class OracleConnectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mock
     */
    protected $config;

    /**
     * @var OracleConnection
     */
    protected $connection;

    protected function setUp()
    {
        $this->config = m::mock(Repository::class);

        $this->connection = new OracleConnection($this->config);
    }

    public function test_can_resolve()
    {
        $this->config->shouldReceive('get')->with('database.connections.oracle.host')->once()->andReturn('host');
        $this->config->shouldReceive('get')->with('database.connections.oracle.database')->once()->andReturn('database');
        $this->config->shouldReceive('get')->with('database.connections.oracle.username')->once()->andReturn('username');
        $this->config->shouldReceive('get')->with('database.connections.oracle.password')->once()->andReturn('password');
        $this->config->shouldReceive('get')->with('database.connections.oracle.charset')->once()->andReturn('charset');
        $this->config->shouldReceive('get')->with('database.connections.oracle.port')->once()->andReturn('port');
        $this->config->shouldReceive('get')->with('database.connections.oracle.prefix')->once()->andReturn('prefix');

        $resolved = $this->connection->resolve();

        $this->assertEquals('oci8',         $resolved['driver']);
        $this->assertEquals('host',         $resolved['host']);
        $this->assertEquals('database',     $resolved['dbname']);
        $this->assertEquals('username',     $resolved['user']);
        $this->assertEquals('password',     $resolved['password']);
        $this->assertEquals('charset',      $resolved['charset']);
        $this->assertEquals('port',         $resolved['port']);
        $this->assertEquals('prefix',       $resolved['prefix']);
    }
}
