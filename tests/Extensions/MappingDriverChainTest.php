<?php

use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use LaravelDoctrine\ORM\Extensions\MappingDriverChain;
use Mockery as m;
use Mockery\Mock;

class MappingDriverChainTest extends PHPUnit_Framework_TestCase
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

        $this->assertArrayHasKey('Namespace',      $this->chain->getDrivers());
        $this->assertArrayHasKey('NewNamespace',   $this->chain->getDrivers());
        $this->assertArrayHasKey('NewNamespace2',  $this->chain->getDrivers());
        $this->assertArrayHasKey('NewNamespace3',  $this->chain->getDrivers());
        $this->assertArrayNotHasKey('NonExisting', $this->chain->getDrivers());
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
