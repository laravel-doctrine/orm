<?php

use Illuminate\Contracts\Config\Repository;
use LaravelDoctrine\ORM\Configuration\Cache\PhpFileCacheProvider;
use Mockery as m;

class PhpFileCacheProviderTest extends AbstractCacheProviderTest
{
    public function getProvider()
    {
        $config = m::mock(Repository::class);
        $config->shouldReceive('get')
            ->with('doctrine.cache.namespace', 'doctrine-cache')
            ->once()
            ->andReturn('doctrine-cache');

        return new PhpFileCacheProvider(
            $config
        );
    }

    public function getExpectedInstance()
    {
        return \Symfony\Component\Cache\Adapter\PhpFilesAdapter::class;
    }
}
