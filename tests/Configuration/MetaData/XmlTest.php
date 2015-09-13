<?php

use Doctrine\ORM\Configuration;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use LaravelDoctrine\ORM\Configuration\Cache\CacheManager;
use LaravelDoctrine\ORM\Configuration\MetaData\Xml;
use Mockery as m;
use Mockery\Mock;

class XmlTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mock
     */
    protected $cache;

    /**
     * @var Xml
     */
    protected $meta;

    protected function setUp()
    {
        $this->cache = m::mock(CacheManager::class);
        $this->cache->shouldReceive('driver')->once();

        $this->meta = new Xml($this->cache);
    }

    public function test_can_resolve()
    {
        $resolved = $this->meta->resolve([
            'paths'   => ['entities'],
            'dev'     => true,
            'proxies' => ['path' => 'path']
        ]);

        $this->assertInstanceOf(Configuration::class, $resolved);

        $this->assertEquals('path', $resolved->getProxyDir());
        $this->assertInstanceOf(XmlDriver::class, $resolved->getMetadataDriverImpl());
        $this->assertContains('entities', $resolved->getMetadataDriverImpl()->getLocator()->getPaths());
    }
}
