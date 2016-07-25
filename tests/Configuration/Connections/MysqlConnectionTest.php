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
        $resolved = $this->connection->resolve([
            'driver'      => 'pdo_mysql',
            'host'        => 'host',
            'database'    => 'database',
            'username'    => 'username',
            'password'    => 'password',
            'charset'     => 'charset',
            'port'        => 'port',
            'unix_socket' => 'unix_socket',
            'prefix'      => 'prefix'
        ]);

        $this->assertEquals('pdo_mysql', $resolved['driver']);
        $this->assertEquals('host', $resolved['host']);
        $this->assertEquals('database', $resolved['dbname']);
        $this->assertEquals('username', $resolved['user']);
        $this->assertEquals('password', $resolved['password']);
        $this->assertEquals('charset', $resolved['charset']);
        $this->assertEquals('port', $resolved['port']);
        $this->assertEquals('unix_socket', $resolved['unix_socket']);
        $this->assertEquals('prefix', $resolved['prefix']);
    }

    protected function tearDown()
    {
        m::close();
    }
}
