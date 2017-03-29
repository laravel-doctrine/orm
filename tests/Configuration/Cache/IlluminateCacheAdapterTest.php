<?php

use Illuminate\Contracts\Cache\Repository;
use LaravelDoctrine\ORM\Configuration\Cache\IlluminateCacheAdapter;
use Mockery as m;

class IlluminateCacheAdapterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var IlluminateCacheAdapter
     */
    protected $cache;

    /**
     * @var \Mockery\Mock
     */
    protected $repository;

    public function setUp()
    {
        $this->repository = m::mock(Repository::class);
        $this->cache      = new IlluminateCacheAdapter(
            $this->repository
        );
    }

    public function test_can_fetch()
    {
        $this->repository->shouldReceive('get')
                         ->with('DoctrineNamespaceCacheKey[]')
                         ->once()->andReturn('cacheKey');

        $this->repository->shouldReceive('get')
                         ->with('[1][cacheKey]')
                         ->once()->andReturn('fetched');

        $this->assertEquals('fetched', $this->cache->fetch(1));
    }

    public function test_can_fetch_multiple()
    {
        $this->repository->shouldReceive('get')
                         ->with('DoctrineNamespaceCacheKey[]')
                         ->once()->andReturn('cacheKey');

        $this->repository->shouldReceive('get')
                         ->with('[1][cacheKey]')
                         ->once()->andReturn('fetched1');

        $this->repository->shouldReceive('get')
                         ->with('[2][cacheKey]')
                         ->once()->andReturn('fetched2');

        $result = $this->cache->fetchMultiple([1, 2]);

        $this->assertContains('fetched1', $result);
        $this->assertContains('fetched2', $result);
    }

    public function test_can_test_if_cache_contains()
    {
        $this->repository->shouldReceive('get')
                         ->with('DoctrineNamespaceCacheKey[]')
                         ->once()->andReturn('cacheKey');

        $this->repository->shouldReceive('has')
                         ->with('[1][cacheKey]')
                         ->once()->andReturn(true);

        $this->assertTrue($this->cache->contains(1));
    }

    public function test_can_save()
    {
        $this->repository->shouldReceive('get')
                         ->with('DoctrineNamespaceCacheKey[]')
                         ->once()->andReturn('cacheKey');

        $this->repository->shouldReceive('put')
                         ->with('[1][cacheKey]', 'data', 1)
                         ->once()->andReturn(true);

        $this->assertTrue($this->cache->save(1, 'data', 60));
    }

    public function test_can_delete()
    {
        $this->repository->shouldReceive('get')
                         ->with('DoctrineNamespaceCacheKey[]')
                         ->once()->andReturn('cacheKey');

        $this->repository->shouldReceive('forget')
                         ->with('[1][cacheKey]')
                         ->once()->andReturn(true);

        $this->assertTrue($this->cache->delete(1));
    }

    public function test_can_delete_all()
    {
        $this->repository->shouldReceive('get')
                         ->with('DoctrineNamespaceCacheKey[]')
                         ->once()->andReturn('cacheKey');

        $this->repository->shouldReceive('forever')
                         ->with("DoctrineNamespaceCacheKey[]", 1)
                         ->once()->andReturn(true);

        $this->assertTrue($this->cache->deleteAll());
    }

    public function test_can_flush_all()
    {
        $this->repository->shouldReceive('flush')
                         ->once()->andReturn(true);

        $this->assertTrue($this->cache->flushAll());
    }

    public function test_can_namespace_cache()
    {
        $this->cache->setNamespace('namespace');
        $this->assertEquals('namespace', $this->cache->getNamespace());

        $this->repository->shouldReceive('get')
                         ->with('DoctrineNamespaceCacheKey[namespace]')
                         ->once()->andReturn('cacheKey');

        $this->repository->shouldReceive('get')
                         ->with('namespace[1][cacheKey]')
                         ->once()->andReturn('fetched');

        $this->assertEquals('fetched', $this->cache->fetch(1));
    }

    protected function tearDown()
    {
        m::close();
    }
}
