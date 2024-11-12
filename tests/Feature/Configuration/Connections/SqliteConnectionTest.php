<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Feature\Configuration\Connections;

use Illuminate\Contracts\Config\Repository;
use LaravelDoctrine\ORM\Configuration\Connections\SqliteConnection;
use LaravelDoctrineTest\ORM\TestCase;
use Mockery as m;

class SqliteConnectionTest extends TestCase
{
    protected Repository $config;

    protected SqliteConnection $connection;

    protected function setUp(): void
    {
        $this->config = m::mock(Repository::class);

        $this->connection = new SqliteConnection($this->config);

        parent::setUp();
    }

    public function testCanResolve(): void
    {
        $resolved = $this->connection->resolve([
            'driver'              => 'pdo_sqlite',
            'database'            => 'path',
            'username'            => 'username',
            'password'            => 'password',
            'prefix'              => 'prefix',
            'defaultTableOptions' => [],
            'driverOptions'       => [],
        ]);

        $this->assertEquals('pdo_sqlite', $resolved['driver']);
        $this->assertEquals('username', $resolved['user']);
        $this->assertEquals('password', $resolved['password']);
        $this->assertEquals('prefix', $resolved['prefix']);
        $this->assertFalse($resolved['memory']);
        $this->assertEquals('path', $resolved['path']);
        $this->assertCount(0, $resolved['defaultTableOptions']);
        $this->assertCount(0, $resolved['driverOptions']);
    }

    public function testCanResolveWithInMemoryDatabase(): void
    {
        $resolved = $this->connection->resolve([
            'driver'   => 'pdo_sqlite',
            'database' => ':memory',
            'username' => 'username',
            'password' => 'password',
            'prefix'   => 'prefix',
        ]);

        $this->assertEquals('pdo_sqlite', $resolved['driver']);
        $this->assertEquals('username', $resolved['user']);
        $this->assertEquals('password', $resolved['password']);
        $this->assertEquals('prefix', $resolved['prefix']);
        $this->assertTrue($resolved['memory']);
        $this->assertEquals(':memory', $resolved['path']);
    }

    public function testCanResolveWithFullInMemoryDatabase(): void
    {
        $resolved = $this->connection->resolve(['database' => ':memory:']);

        $this->assertTrue($resolved['memory']);
        $this->assertEquals(':memory:', $resolved['path']);
    }

    protected function tearDown(): void
    {
        m::close();

        parent::tearDown();
    }
}
