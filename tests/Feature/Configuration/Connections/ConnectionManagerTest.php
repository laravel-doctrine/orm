<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Feature\Configuration\Connections;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use LaravelDoctrine\ORM\Configuration\Connections\ConnectionManager;
use LaravelDoctrine\ORM\Configuration\Connections\MysqlConnection;
use LaravelDoctrine\ORM\Configuration\Connections\SqliteConnection;
use LaravelDoctrine\ORM\Exceptions\DriverNotFound;
use LaravelDoctrineTest\ORM\TestCase;
use Mockery as m;

use function is_array;

class ConnectionManagerTest extends TestCase
{
    protected ConnectionManager $manager;

    protected Container $testApp;

    protected Repository $config;

    protected function setUp(): void
    {
        $this->testApp = m::mock(Container::class);
        $this->testApp->shouldReceive('make')->andReturn(m::self());

        $this->config = m::mock(Repository::class);
        $this->config->shouldReceive('get');

        $this->manager = new ConnectionManager(
            $this->testApp,
        );

        parent::setUp();
    }

    public function testDriverReturnsTheDefaultDriver(): void
    {
        $this->testApp->shouldReceive('resolve')->andReturn(
            (new MysqlConnection($this->config))->resolve(),
        );

        $this->assertTrue(is_array($this->manager->driver()));
        $this->assertContains('pdo_mysql', $this->manager->driver());
    }

    public function testDriverCanReturnAGivenDriver(): void
    {
        $this->testApp->shouldReceive('resolve')->andReturn(
            (new SqliteConnection($this->config))->resolve(),
        );

        $this->assertTrue(is_array($this->manager->driver('sqlite')));
        $this->assertContains('pdo_sqlite', $this->manager->driver());
    }

    public function testCantResolveUnsupportedDrivers(): void
    {
        $this->expectException(DriverNotFound::class);
        $this->manager->driver('non-existing');
    }

    public function testCanCreateCustomDriver(): void
    {
        $this->manager->extend('new', static function () {
            return 'connection';
        });

        $drivers = $this->manager->getDrivers();

        $this->assertEquals('connection', $this->manager->driver('new'));
    }

    public function testCanUseApplicationWhenExtending(): void
    {
        $this->manager->extend('new', function ($app): void {
            $this->assertInstanceOf(Container::class, $app);
        });

        $this->assertTrue(true);
    }

    public function testCanReplaceAnExistingDriver(): void
    {
        $this->manager->extend('oci8', static function () {
            return 'connection';
        });

        $this->assertEquals('connection', $this->manager->driver('oci8'));
    }

    protected function tearDown(): void
    {
        m::close();

        parent::tearDown();
    }
}
