<?php

use Illuminate\Contracts\Config\Repository;
use LaravelDoctrine\ORM\Configuration\Connections\PgsqlConnection;
use Mockery as m;
use Mockery\Mock;
use PHPUnit\Framework\TestCase;

class PgsqlConnectionTest extends TestCase
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
        $resolved = $this->connection->resolve([
            'driver'              => 'pdo_pgsql',
            'host'                => 'host',
            'database'            => 'database',
            'username'            => 'username',
            'password'            => 'password',
            'charset'             => 'charset',
            'port'                => 'port',
            'prefix'              => 'prefix',
            'sslmode'             => 'sslmode',
            'defaultTableOptions' => [],
        ]);

        $this->assertEquals('pdo_pgsql', $resolved['driver']);
        $this->assertEquals('host', $resolved['host']);
        $this->assertEquals('database', $resolved['dbname']);
        $this->assertEquals('username', $resolved['user']);
        $this->assertEquals('password', $resolved['password']);
        $this->assertEquals('charset', $resolved['charset']);
        $this->assertEquals('port', $resolved['port']);
        $this->assertEquals('sslmode', $resolved['sslmode']);
        $this->assertEquals('prefix', $resolved['prefix']);
        $this->assertCount(0, $resolved['defaultTableOptions']);
    }

    protected function tearDown()
    {
        m::close();
    }
}
