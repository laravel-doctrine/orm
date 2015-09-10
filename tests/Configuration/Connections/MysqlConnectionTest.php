<?php

use Illuminate\Contracts\Config\Repository;
use LaravelDoctrine\ORM\Configuration\Connections\MysqlConnection;
use Mockery as m;
use Mockery\Mock;

class MysqlConnectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mock
     */
    protected $config;

    /**
     * @var MysqlConnection
     */
    protected $connection;

    protected function setUp()
    {
        $this->config = m::mock(Repository::class);

        $this->connection = new MysqlConnection($this->config);
    }

    public function test_can_resolve()
    {
        $this->config->shouldReceive('get')->with('database.connections.mysql.host')->once()->andReturn('host');
        $this->config->shouldReceive('get')->with('database.connections.mysql.database')->once()->andReturn('database');
        $this->config->shouldReceive('get')->with('database.connections.mysql.username')->once()->andReturn('username');
        $this->config->shouldReceive('get')->with('database.connections.mysql.password')->once()->andReturn('password');
        $this->config->shouldReceive('get')->with('database.connections.mysql.charset')->once()->andReturn('charset');
        $this->config->shouldReceive('get')->with('database.connections.mysql.port')->once()->andReturn('port');
        $this->config->shouldReceive('get')->with('database.connections.mysql.unix_socket')->once()->andReturn('unix_socket');
        $this->config->shouldReceive('get')->with('database.connections.mysql.prefix')->once()->andReturn('prefix');

        $resolved = $this->connection->resolve();

        $this->assertEquals('pdo_mysql',    $resolved['driver']);
        $this->assertEquals('host',         $resolved['host']);
        $this->assertEquals('database',     $resolved['dbname']);
        $this->assertEquals('username',     $resolved['user']);
        $this->assertEquals('password',     $resolved['password']);
        $this->assertEquals('charset',      $resolved['charset']);
        $this->assertEquals('port',         $resolved['port']);
        $this->assertEquals('unix_socket',  $resolved['unix_socket']);
        $this->assertEquals('prefix',       $resolved['prefix']);
    }

    protected function tearDown()
    {
        m::close();
    }
}
