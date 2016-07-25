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
        $resolved = $this->connection->resolve([
            'driver'   => 'pdo_sqlsrv',
            'host'     => 'host',
            'database' => 'database',
            'username' => 'username',
            'password' => 'password',
            'port'     => 'port',
            'prefix'   => 'prefix',
            'charset'  => 'charset'
        ]);

        $this->assertEquals('pdo_sqlsrv', $resolved['driver']);
        $this->assertEquals('host', $resolved['host']);
        $this->assertEquals('database', $resolved['dbname']);
        $this->assertEquals('username', $resolved['user']);
        $this->assertEquals('password', $resolved['password']);
        $this->assertEquals('port', $resolved['port']);
        $this->assertEquals('prefix', $resolved['prefix']);
        $this->assertEquals('charset', $resolved['charset']);
    }

    protected function tearDown()
    {
        m::close();
    }
}
