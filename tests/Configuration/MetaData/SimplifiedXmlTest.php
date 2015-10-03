<?php

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use LaravelDoctrine\ORM\Configuration\MetaData\SimplifiedXml;
use Mockery as m;

class SimplifiedXmlTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SimplifiedXml
     */
    protected $meta;

    protected function setUp()
    {
        $this->meta = new SimplifiedXml();
    }

    public function test_can_resolve()
    {
        $resolved = $this->meta->resolve([
            'paths'     => ['entities' => 'App\Entities'],
            'dev'       => true,
            'extension' => '.xml',
            'proxies'   => ['path' => 'path']
        ]);

        $this->assertInstanceOf(MappingDriver::class, $resolved);
        $this->assertInstanceOf(SimplifiedXmlDriver::class, $resolved);
    }

    protected function tearDown()
    {
        m::close();
    }
}
