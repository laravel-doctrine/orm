<?php

use Mockery as m;

abstract class AbstractCacheTest
{
    abstract public function getStore();
    abstract public function getCache();

    public function test_can_fetch()
    {
        $this->getStore()->shouldReceive('get')
                    ->with('DoctrineNamespaceCacheKey[]')
                    ->once()->andReturn('cacheKey');

        $this->getStore()->shouldReceive('get')
                    ->with('[1][cacheKey]')
                    ->once()->andReturn('fetched');

        $this->assertEquals('fetched', $this->getCache()->fetch(1));
    }

    public function test_can_fetch_multiple()
    {
        $this->getStore()->shouldReceive('get')
                    ->with('DoctrineNamespaceCacheKey[]')
                    ->once()->andReturn('cacheKey');

        $this->getStore()->shouldReceive('get')
                    ->with('[1][cacheKey]')
                    ->once()->andReturn('fetched1');

        $this->getStore()->shouldReceive('get')
                    ->with('[2][cacheKey]')
                    ->once()->andReturn('fetched2');

        $result = $this->getCache()->fetchMultiple([1, 2]);

        $this->assertContains('fetched1', $result);
        $this->assertContains('fetched2', $result);
    }

    public function test_can_test_if_cache_contains()
    {
        $this->getStore()->shouldReceive('get')
                    ->with('DoctrineNamespaceCacheKey[]')
                    ->once()->andReturn('cacheKey');

        $this->getStore()->shouldReceive('get')
                    ->with('[1][cacheKey]')
                    ->once()->andReturn('fetched');

        $this->assertTrue($this->getCache()->contains(1));
    }

    public function test_can_save()
    {
        $this->getStore()->shouldReceive('get')
                    ->with('DoctrineNamespaceCacheKey[]')
                    ->once()->andReturn('cacheKey');

        $this->getStore()->shouldReceive('put')
                    ->with('[1][cacheKey]', 'data', 60)
                    ->once()->andReturn(true);

        $this->assertTrue($this->getCache()->save(1, 'data', 60));
    }

    public function test_can_delete()
    {
        $this->getStore()->shouldReceive('get')
                    ->with('DoctrineNamespaceCacheKey[]')
                    ->once()->andReturn('cacheKey');

        $this->getStore()->shouldReceive('forget')
                    ->with('[1][cacheKey]')
                    ->once()->andReturn(true);

        $this->assertTrue($this->getCache()->delete(1));
    }

    public function test_can_delete_all()
    {
        $this->getStore()->shouldReceive('get')
                    ->with('DoctrineNamespaceCacheKey[]')
                    ->once()->andReturn('cacheKey');

        $this->getStore()->shouldReceive('put')
                    ->with("DoctrineNamespaceCacheKey[]", 1, false)
                    ->once()->andReturn(true);

        $this->assertTrue($this->getCache()->deleteAll());
    }

    public function test_can_flush_all()
    {
        $this->getStore()->shouldReceive('flush')
                    ->once()->andReturn(true);

        $this->assertTrue($this->getCache()->flushAll());
    }

    public function test_can_namespace_cache()
    {
        $this->getCache()->setNamespace('namespace');
        $this->assertEquals('namespace', $this->getCache()->getNamespace());

        $this->getStore()->shouldReceive('get')
                    ->with('DoctrineNamespaceCacheKey[namespace]')
                    ->once()->andReturn('cacheKey');

        $this->getStore()->shouldReceive('get')
                    ->with('namespace[1][cacheKey]')
                    ->once()->andReturn('fetched');

        $this->assertEquals('fetched', $this->getCache()->fetch(1));
    }

    protected function tearDown()
    {
        m::close();
    }
}
