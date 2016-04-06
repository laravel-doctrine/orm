<?php

namespace LaravelDoctrine\ORM\Configuration\MetaData;

use Illuminate\Contracts\Config\Repository;
use LaravelDoctrine\ORM\Configuration\MetaData\Config\ConfigDriver;

class Config extends MetaData
{
    /**
     * @var Repository
     */
    protected $config;

    /**
     * @param Repository $config
     */
    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    /**
     * @param array $settings
     *
     * @return \Doctrine\Common\Persistence\Mapping\Driver\MappingDriver
     */
    public function resolve(array $settings = [])
    {
        return new ConfigDriver(
            $this->config->get(array_get($settings, 'mapping_file'), [])
        );
    }
}
