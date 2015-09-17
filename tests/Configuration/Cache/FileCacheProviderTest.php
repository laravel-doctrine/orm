<?php

use Doctrine\Common\Cache\FilesystemCache;
use Illuminate\Contracts\Config\Repository;
use LaravelDoctrine\ORM\Configuration\Cache\FileCacheProvider;
use Mockery as m;

class FileCacheProviderTest extends AbstractCacheProviderTest
{
    public function getProvider()
    {
        $config = m::mock(Repository::class);
        $config->shouldReceive('get')
               ->with('cache.stores.file.path', storage_path('framework/cache'))
               ->once()
               ->andReturn('/tmp');

        return new FileCacheProvider(
            $config
        );
    }

    public function getExpectedInstance()
    {
        return FilesystemCache::class;
    }
}

function storage_path($path = null)
{
    $storage = __DIR__ . DIRECTORY_SEPARATOR . '../../Stubs/storage';

    return is_null($path) ? $storage : $storage . DIRECTORY_SEPARATOR . $path;
}
