<?php

namespace Brouwers\LaravelDoctrine\Configuration\Cache;

class CustomCacheProvider extends AbstractCacheProvider
{
    /**
     * @var
     */
    protected $cache;

    /**
     * @var
     */
    protected $name;

    /**
     * @param $cache
     * @param $name
     */
    public function __construct($cache, $name)
    {
        $this->cache = $cache;
        $this->name  = $name;
    }

    /**
     * @param array $config
     *
     * @return CustomCacheProvider
     */
    public function configure($config = [])
    {
        return $this;
    }

    /**
     * @return mixed
     */
    public function resolve()
    {
        return $this->cache;
    }
}
