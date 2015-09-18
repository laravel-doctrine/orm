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

    protected function tearDown()
    {
        m::close();
    }
}
