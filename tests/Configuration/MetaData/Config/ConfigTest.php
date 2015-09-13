<?php

use Doctrine\ORM\Configuration;
use Illuminate\Contracts\Config\Repository;
use LaravelDoctrine\ORM\Configuration\Cache\CacheManager;
use LaravelDoctrine\ORM\Configuration\MetaData\Annotations;
use LaravelDoctrine\ORM\Configuration\MetaData\Config;
use LaravelDoctrine\ORM\Configuration\MetaData\Config\ConfigDriver;
use Mockery as m;
use Mockery\Mock;

class ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mock
     */
    protected $cache;

    /**
     * @var Mock
     */
    protected $config;

    /**
     * @var Annotations
     */
    protected $meta;

    protected function setUp()
    {
        $this->cache = m::mock(CacheManager::class);
        $this->cache->shouldReceive('driver')->once();

        $this->config = m::mock(Repository::class);
        $this->config->shouldReceive('get')->with('mappings', [])->once()->andReturn([
            'App\User' => []
        ]);

        $this->meta = new Config($this->cache, $this->config);
    }

    public function test_can_resolve()
    {
        $resolved = $this->meta->resolve([
            'paths'        => ['entities'],
            'dev'          => true,
            'proxies'      => ['path' => 'path'],
            'mapping_file' => 'mappings'
        ]);

        $this->assertInstanceOf(Configuration::class, $resolved);

        $this->assertEquals('path', $resolved->getProxyDir());
        $this->assertInstanceOf(ConfigDriver::class, $resolved->getMetadataDriverImpl());
    }
}
