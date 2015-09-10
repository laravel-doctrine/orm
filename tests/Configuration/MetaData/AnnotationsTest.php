<?php

use Doctrine\ORM\Configuration;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use LaravelDoctrine\ORM\Configuration\Cache\CacheManager;
use LaravelDoctrine\ORM\Configuration\MetaData\Annotations;
use Mockery as m;
use Mockery\Mock;

class AnnotationsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mock
     */
    protected $cache;

    /**
     * @var Annotations
     */
    protected $meta;

    protected function setUp()
    {
        $this->cache = m::mock(CacheManager::class);
        $this->cache->shouldReceive('driver')->once();

        $this->meta = new Annotations($this->cache);
    }

    public function test_can_resolve()
    {
        $resolved = $this->meta->resolve([
            'paths'   => ['entities'],
            'dev'     => true,
            'proxies' => ['path' => 'path'],
            'simple'  => false
        ]);

        $this->assertInstanceOf(Configuration::class, $resolved);

        $this->assertEquals('path', $resolved->getProxyDir());
        $this->assertInstanceOf(AnnotationDriver::class, $resolved->getMetadataDriverImpl());
        $this->assertContains('entities', $resolved->getMetadataDriverImpl()->getPaths());
    }
}
