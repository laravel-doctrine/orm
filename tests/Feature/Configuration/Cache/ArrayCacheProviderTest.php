<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Feature\Configuration\Cache;

use LaravelDoctrine\ORM\Configuration\Cache\ArrayCacheProvider;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class ArrayCacheProviderTest extends CacheProvider
{
    public function getProvider(): mixed
    {
        return new ArrayCacheProvider();
    }

    public function getExpectedInstance(): mixed
    {
        return ArrayAdapter::class;
    }
}
