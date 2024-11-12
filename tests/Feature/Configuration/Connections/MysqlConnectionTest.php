<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Feature\Configuration\Connections;

use Illuminate\Contracts\Config\Repository;
use LaravelDoctrine\ORM\Configuration\Connections\MysqlConnection;
use LaravelDoctrineTest\ORM\TestCase;
use Mockery as m;

class MysqlConnectionTest extends TestCase
{
    protected Repository $config;

    protected MysqlConnection $connection;

    protected function setUp(): void
    {
        $this->config = m::mock(Repository::class);

        $this->connection = new MysqlConnection($this->config);

        parent::setUp();
    }

    public function testCanResolve(): void
    {
        $resolved = $this->connection->resolve([
            'driver'              => 'pdo_mysql',
            'host'                => 'host',
            'database'            => 'database',
            'username'            => 'username',
            'password'            => 'password',
            'charset'             => 'charset',
            'port'                => 'port',
            'unix_socket'         => 'unix_socket',
            'ssl_key'             => 'ssl_key',
            'ssl_cert'            => 'ssl_cert',
            'ssl_ca'              => 'ssl_ca',
            'ssl_capath'          => 'ssl_capath',
            'ssl_cipher'          => 'ssl_cipher',
            'prefix'              => 'prefix',
            'defaultTableOptions' => [],
            'driverOptions'       => [],
        ]);

        $this->assertEquals('pdo_mysql', $resolved['driver']);
        $this->assertEquals('host', $resolved['host']);
        $this->assertEquals('database', $resolved['dbname']);
        $this->assertEquals('username', $resolved['user']);
        $this->assertEquals('password', $resolved['password']);
        $this->assertEquals('charset', $resolved['charset']);
        $this->assertEquals('port', $resolved['port']);
        $this->assertEquals('unix_socket', $resolved['unix_socket']);
        $this->assertEquals('ssl_key', $resolved['ssl_key']);
        $this->assertEquals('ssl_cert', $resolved['ssl_cert']);
        $this->assertEquals('ssl_ca', $resolved['ssl_ca']);
        $this->assertEquals('ssl_capath', $resolved['ssl_capath']);
        $this->assertEquals('ssl_cipher', $resolved['ssl_cipher']);
        $this->assertEquals('prefix', $resolved['prefix']);
        $this->assertCount(0, $resolved['defaultTableOptions']);
        $this->assertCount(0, $resolved['driverOptions']);
    }

    protected function tearDown(): void
    {
        m::close();

        parent::tearDown();
    }
}
