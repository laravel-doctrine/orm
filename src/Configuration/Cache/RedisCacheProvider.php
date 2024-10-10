<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Configuration\Cache;

class RedisCacheProvider extends IlluminateCacheProvider
{
    protected string|null $store = 'redis';
}
