<?php

namespace LaravelDoctrine\ORM\Configuration\MetaData;

use Doctrine\ORM\Tools\Setup;
use LaravelDoctrine\ORM\Configuration\Cache\CacheManager;

class Xml extends AbstractMetaData
{
    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * @param CacheManager $cacheManager
     */
    public function __construct(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * @return \Doctrine\ORM\Configuration|mixed
     */
    public function resolve()
    {
        return Setup::createXMLMetadataConfiguration(
            array_get($this->settings, 'paths'),
            array_get($this->settings, 'dev'),
            array_get($this->settings, 'proxies.path'),
            $this->cacheManager->driver()
        );
    }
}
