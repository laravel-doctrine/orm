<?php

namespace Brouwers\LaravelDoctrine\Configuration\MetaData;

use Brouwers\LaravelDoctrine\Configuration\Cache\CacheManager;

abstract class AbstractMetaData implements MetaData
{
    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @var string
     */
    protected $name;

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getCache()
    {
        if (config('doctrine.cache.default')) {
            return CacheManager::resolve(
                config('doctrine.cache.default')
            );
        }
    }
}
