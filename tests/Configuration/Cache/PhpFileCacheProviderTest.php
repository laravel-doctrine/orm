<?php

use Doctrine\Common\Cache\PhpFileCache;
use Illuminate\Contracts\Config\Repository;
use LaravelDoctrine\ORM\Configuration\Cache\PhpFileCacheProvider;
use Mockery as m;

class PhpFileCacheProviderTest extends AbstractCacheProviderTest
{
    public function getProvider()
    {
        $config = m::mock(Repository::class);
        $config->shouldReceive('get')
            ->with('cache.stores.file.path', __DIR__ . DIRECTORY_SEPARATOR . '../../Stubs/storage/framework/cache')
            ->once()
            ->andReturn('/tmp');

        return new PhpFileCacheProvider(
            $config
        );
    }

    public function getExpectedInstance()
    {
        return PhpFileCache::class;
    }
}
