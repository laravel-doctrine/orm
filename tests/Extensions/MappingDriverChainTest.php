<?php

use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\Persistence\Mapping\Driver\DefaultFileLocator;
use Doctrine\Persistence\Mapping\Driver\SymfonyFileLocator;
use LaravelDoctrine\ORM\Extensions\MappingDriverChain;
use Mockery as m;
use Mockery\Mock;
use PHPUnit\Framework\TestCase;

class MappingDriverChainTest extends TestCase
{
    /**
     * @var Mock
     */
    protected $driver;

    /**
     * @var MappingDriverChain
     */
    protected $chain;

    protected function setUp()
    {
        $this->driver = m::mock(AnnotationDriver::class);
        $this->chain  = new MappingDriverChain($this->driver, 'Namespace');
    }

    public function test_get_default_driver()
    {
        $this->assertEquals($this->driver, $this->chain->getDefaultDriver());
    }

    public function test_can_add_namespace()
    {
        $this->chain->addNamespace('NewNamespace');
        $this->chain->addNamespace('NewNamespace2');
        $this->chain->addNamespace('NewNamespace3');

        $this->assertArrayHasKey('Namespace', $this->chain->getDrivers());
        $this->assertArrayHasKey('NewNamespace', $this->chain->getDrivers());
        $this->assertArrayHasKey('NewNamespace2', $this->chain->getDrivers());
        $this->assertArrayHasKey('NewNamespace3', $this->chain->getDrivers());
        $this->assertArrayNotHasKey('NonExisting', $this->chain->getDrivers());
    }

    public function test_can_add_paths()
    {
        $this->driver->shouldReceive('addPaths')->with(['paths'])->once();
        $this->driver->shouldReceive('addPaths')->with(['paths2'])->once();

        $this->chain->addPaths(['paths']);
        $this->chain->addPaths(['paths2']);

        $this->assertTrue(true);
    }

    public function test_can_add_paths_to_filedriver()
    {
        $driver  = m::mock(XmlDriver::class);
        $locator = m::mock(DefaultFileLocator::class);
        $chain   = new MappingDriverChain($driver, 'Namespace');

        $driver->shouldReceive('getLocator')->andReturn($locator);
        $locator->shouldReceive('addPaths')->with(['paths'])->once();
        $locator->shouldReceive('addPaths')->with(['paths2'])->once();

        $chain->addPaths(['paths']);
        $chain->addPaths(['paths2']);

        $this->assertTrue(true);
    }

    public function test_can_add_paths_to_simplified_filedriver()
    {
        $driver  = m::mock(SimplifiedXmlDriver::class);
        $locator = m::mock(SymfonyFileLocator::class);
        $chain   = new MappingDriverChain($driver, 'Namespace');

        $driver->shouldReceive('getLocator')->andReturn($locator);
        $locator->shouldReceive('addNamespacePrefixes')->with(['paths'])->once();
        $locator->shouldReceive('addNamespacePrefixes')->with(['paths2'])->once();

        $chain->addPaths(['paths']);
        $chain->addPaths(['paths2']);

        $this->assertTrue(true);
    }

    public function test_can_get_annotation_reader()
    {
        $this->driver->shouldReceive('getReader')
                     ->once()
                     ->andReturn('reader');

        $this->assertEquals('reader', $this->chain->getReader());
    }

    protected function tearDown()
    {
        m::close();
    }
}
