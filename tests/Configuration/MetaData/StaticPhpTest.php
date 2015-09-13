<?php

use Doctrine\Common\Persistence\Mapping\Driver\StaticPHPDriver;
use Doctrine\ORM\Configuration;
use LaravelDoctrine\ORM\Configuration\Cache\CacheManager;
use LaravelDoctrine\ORM\Configuration\MetaData\StaticPhp;
use Mockery as m;
use Mockery\Mock;

class StaticPhpTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mock
     */
    protected $cache;

    /**
     * @var StaticPhp
     */
    protected $meta;

    protected function setUp()
    {
        $this->cache = m::mock(CacheManager::class);
        $this->cache->shouldReceive('driver')->once();

        $this->meta = new StaticPhp($this->cache);
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
        $this->assertInstanceOf(StaticPHPDriver::class, $resolved->getMetadataDriverImpl());
    }
}
