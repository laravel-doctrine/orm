<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Configuration\Cache;

class ApcCacheProvider extends IlluminateCacheProvider
{
    protected string|null $store = 'apc';
}
