<?php

use Illuminate\Contracts\Config\Repository;
use LaravelDoctrine\ORM\Configuration\Connections\SqlsrvConnection;
use Mockery as m;
use Mockery\Mock;

class SqlsrvConnectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mock
     */
    protected $config;

    /**
     * @var SqlsrvConnection
     */
    protected $connection;

    protected function setUp()
    {
        $this->config = m::mock(Repository::class);

        $this->connection = new SqlsrvConnection($this->config);
    }

    public function test_can_resolve()
    {
        $this->config->shouldReceive('get')->with('database.connections.sqlsrv.host')->once()->andReturn('host');
        $this->config->shouldReceive('get')->with('database.connections.sqlsrv.database')->once()->andReturn('database');
        $this->config->shouldReceive('get')->with('database.connections.sqlsrv.username')->once()->andReturn('username');
        $this->config->shouldReceive('get')->with('database.connections.sqlsrv.password')->once()->andReturn('password');
        $this->config->shouldReceive('get')->with('database.connections.sqlsrv.port')->once()->andReturn('port');
        $this->config->shouldReceive('get')->with('database.connections.sqlsrv.prefix')->once()->andReturn('prefix');

        $resolved = $this->connection->resolve();

        $this->assertEquals('pdo_sqlsrv', $resolved['driver']);
        $this->assertEquals('host', $resolved['host']);
        $this->assertEquals('database', $resolved['dbname']);
        $this->assertEquals('username', $resolved['user']);
        $this->assertEquals('password', $resolved['password']);
        $this->assertEquals('port', $resolved['port']);
        $this->assertEquals('prefix', $resolved['prefix']);
    }

    protected function tearDown()
    {
        m::close();
    }
}
