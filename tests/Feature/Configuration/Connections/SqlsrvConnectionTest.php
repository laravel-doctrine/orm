<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Feature\Configuration\Connections;

use Illuminate\Contracts\Config\Repository;
use LaravelDoctrine\ORM\Configuration\Connections\SqlsrvConnection;
use LaravelDoctrineTest\ORM\TestCase;
use Mockery as m;

class SqlsrvConnectionTest extends TestCase
{
    protected Repository $config;

    protected SqlsrvConnection $connection;

    protected function setUp(): void
    {
        $this->config = m::mock(Repository::class);

        $this->connection = new SqlsrvConnection($this->config);

        parent::setUp();
    }

    public function testCanResolve(): void
    {
        $resolved = $this->connection->resolve([
            'driver'              => 'pdo_sqlsrv',
            'host'                => 'host',
            'database'            => 'database',
            'username'            => 'username',
            'password'            => 'password',
            'port'                => 'port',
            'prefix'              => 'prefix',
            'charset'             => 'charset',
            'defaultTableOptions' => [],
            'driverOptions'       => [],
        ]);

        $this->assertEquals('pdo_sqlsrv', $resolved['driver']);
        $this->assertEquals('host', $resolved['host']);
        $this->assertEquals('database', $resolved['dbname']);
        $this->assertEquals('username', $resolved['user']);
        $this->assertEquals('password', $resolved['password']);
        $this->assertEquals('port', $resolved['port']);
        $this->assertEquals('prefix', $resolved['prefix']);
        $this->assertEquals('charset', $resolved['charset']);
        $this->assertCount(0, $resolved['defaultTableOptions']);
        $this->assertCount(0, $resolved['driverOptions']);
    }

    protected function tearDown(): void
    {
        m::close();

        parent::tearDown();
    }
}
