<?php

use Doctrine\ORM\Configuration;
use Doctrine\ORM\Mapping\Driver\YamlDriver;
use LaravelDoctrine\ORM\Configuration\Cache\CacheManager;
use LaravelDoctrine\ORM\Configuration\MetaData\Yaml;
use Mockery as m;
use Mockery\Mock;

class YamlTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mock
     */
    protected $cache;

    /**
     * @var Yaml
     */
    protected $meta;

    protected function setUp()
    {
        $this->cache = m::mock(CacheManager::class);
        $this->cache->shouldReceive('driver')->once();

        $this->meta = new Yaml($this->cache);
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
        $this->assertInstanceOf(YamlDriver::class, $resolved->getMetadataDriverImpl());
        $this->assertContains('entities', $resolved->getMetadataDriverImpl()->getLocator()->getPaths());
    }
}
