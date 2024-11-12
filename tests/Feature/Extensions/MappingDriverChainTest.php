<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Feature\Extensions;

use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\Persistence\Mapping\Driver\DefaultFileLocator;
use Doctrine\Persistence\Mapping\Driver\SymfonyFileLocator;
use LaravelDoctrine\ORM\Extensions\MappingDriverChain;
use LaravelDoctrineTest\ORM\TestCase;
use Mockery as m;

/**
 * NOTE:  This test was degraded while refactoring for ORM 3.
 */
class MappingDriverChainTest extends TestCase
{
    protected XmlDriver $driver;

    protected MappingDriverChain $chain;

    protected function setUp(): void
    {
        $this->driver = m::mock(XmlDriver::class);
        $this->chain  = new MappingDriverChain($this->driver, 'Namespace');

        parent::setUp();
    }

    public function testGetDefaultDriver(): void
    {
        $this->assertEquals($this->driver, $this->chain->getDefaultDriver());
    }

    public function testCanAddPaths(): void
    {
        $this->driver = m::mock(XmlDriver::class);
        $this->chain  = new MappingDriverChain($this->driver, 'Namespace');

        $this->driver->shouldReceive('addPaths')->with(['paths']);
        $this->driver->shouldReceive('addPaths')->with(['paths2']);

        $this->chain->addPaths(['paths']);
        $this->chain->addPaths(['paths2']);

        $this->assertTrue(true);
    }

    public function testCanAddPathsToFiledriver(): void
    {
        $driver  = m::mock(XmlDriver::class);
        $locator = m::mock(DefaultFileLocator::class);
        $chain   = new MappingDriverChain($driver, 'Namespace');

        $locator->shouldReceive('addPaths')->with(['paths']);
        $locator->shouldReceive('addPaths')->with(['paths2']);

        $chain->addPaths(['paths']);
        $chain->addPaths(['paths2']);

        $this->assertTrue(true);
    }

    public function testCanAddMappingsToFiledriver(): void
    {
        $driver  = m::mock(XmlDriver::class);
        $locator = m::mock(DefaultFileLocator::class);
        $chain   = new MappingDriverChain($driver, 'Namespace');

        $locator->shouldReceive('addMappings')->with(['paths']);
        $locator->shouldReceive('addMappings')->with(['paths2']);

        $chain->addMappings(['paths']);
        $chain->addMappings(['paths2']);

        $this->assertTrue(true);
    }

    public function testCanAddPathsToSimplifiedFiledriver(): void
    {
        $driver  = m::mock(SimplifiedXmlDriver::class);
        $locator = m::mock(SymfonyFileLocator::class);
        $chain   = new MappingDriverChain($driver, 'Namespace');

        $locator->shouldReceive('addNamespacePrefixes')->with(['paths']);
        $locator->shouldReceive('addNamespacePrefixes')->with(['paths2']);

        $chain->addPaths(['paths']);
        $chain->addPaths(['paths2']);

        $this->assertTrue(true);
    }

    protected function tearDown(): void
    {
        m::close();

        parent::tearDown();
    }
}
