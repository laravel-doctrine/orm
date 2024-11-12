<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Feature\Configuration\MetaData;

use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Illuminate\Contracts\Container\Container;
use LaravelDoctrine\ORM\Configuration\MetaData\MetaDataManager;
use LaravelDoctrine\ORM\Exceptions\DriverNotFound;
use LaravelDoctrineTest\ORM\TestCase;
use Mockery as m;

class MetaDataManagerTest extends TestCase
{
    protected MetaDataManager $manager;

    protected Container $testApp;

    protected function setUp(): void
    {
        $this->testApp = m::mock(Container::class);
        $this->testApp->shouldReceive('make')->andReturn(m::self());

        $this->manager = new MetaDataManager(
            $this->testApp,
        );

        parent::setUp();
    }

    public function testDriverReturnsTheDefaultDriver(): void
    {
        $this->testApp->shouldReceive('resolve')->andReturn(new XmlDriver('locator', '.xml'));

        $this->assertInstanceOf(XmlDriver::class, $this->manager->driver());
    }

    public function testCantResolveUnsupportedDrivers(): void
    {
        $this->expectException(DriverNotFound::class);
        $this->manager->driver('non-existing');
    }

    public function tetsCanCreateCustomDrivers(): void
    {
        $this->manager->extend('new', static function () {
            return 'configuration';
        });

        $this->assertEquals('configuration', $this->manager->driver('new'));
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
        $this->manager->extend('xml', static function () {
            return 'configuration';
        });

        $this->assertEquals('configuration', $this->manager->driver('xml'));
    }

    protected function tearDown(): void
    {
        m::close();

        parent::tearDown();
    }
}
