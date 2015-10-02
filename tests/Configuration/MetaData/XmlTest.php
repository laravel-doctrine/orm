<?php

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use LaravelDoctrine\ORM\Configuration\MetaData\Xml;
use Mockery as m;

class XmlTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Xml
     */
    protected $meta;

    protected function setUp()
    {
        $this->meta = new Xml();
    }

    public function test_can_resolve()
    {
        $resolved = $this->meta->resolve([
            'paths'   => ['entities'],
            'dev'     => true,
            'proxies' => ['path' => 'path']
        ]);

        $this->assertInstanceOf(MappingDriver::class, $resolved);
        $this->assertInstanceOf(XmlDriver::class, $resolved);
    }

    public function test_can_specify_extension_without_error()
    {
        $resolved = $this->meta->resolve([
            'paths'     => 'entities',
            'extension' => '.orm.xml'
        ]);

        $this->assertInstanceOf(XmlDriver::class, $resolved);
    }

    public function test_can_not_specify_extension_without_error()
    {
        $resolved = $this->meta->resolve([
            'paths'     => 'entities'
        ]);

        $this->assertInstanceOf(XmlDriver::class, $resolved);
    }

    protected function tearDown()
    {
        m::close();
    }
}
