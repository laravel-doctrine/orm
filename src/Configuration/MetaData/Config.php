<?php

namespace LaravelDoctrine\ORM\Configuration\MetaData;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Arr;
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
     * @return \Doctrine\Persistence\Mapping\Driver\MappingDriver
     */
    public function resolve(array $settings = [])
    {
        return new ConfigDriver(
            $this->config->get(Arr::get($settings, 'mapping_file'), [])
        );
    }
}
