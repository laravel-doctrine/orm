<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Feature\Configuration\Cache;

use Illuminate\Contracts\Config\Repository;
use LaravelDoctrine\ORM\Configuration\Cache\PhpFileCacheProvider;
use Mockery as m;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

class PhpFileCacheProviderTest extends CacheProvider
{
    public function getProvider(): mixed
    {
        $config = m::mock(Repository::class);
        $config->shouldReceive('get')
            ->with(
                'cache.stores.file.path',
                $this->applicationBasePath() . '/storage/framework/cache',
            )
            ->once()
            ->andReturn('/tmp');

        $config->shouldReceive('get')
            ->with('doctrine.cache.namespace', 'doctrine-cache')
            ->once()
            ->andReturn('doctrine-cache');

        return new PhpFileCacheProvider(
            $config,
        );
    }

    public function getExpectedInstance(): mixed
    {
        return PhpFilesAdapter::class;
    }
}
