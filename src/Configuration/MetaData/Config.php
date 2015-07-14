<?php

namespace Brouwers\LaravelDoctrine\Configuration\MetaData;

use Brouwers\LaravelDoctrine\Configuration\MetaData\Config\ConfigDriver;
use Doctrine\ORM\Tools\Setup;

class Config extends AbstractMetaData
{
    /**
     * @var string
     */
    protected $name = 'config';

    /**
     * @param array $settings
     * @param bool  $dev
     *
     * @return static
     */
    public function configure(array $settings = [], $dev = false)
    {
        $this->settings = [
            'dev'          => $dev,
            'proxy_path'   => array_get($settings, 'proxies.path'),
            'mapping_file' => array_get($settings, 'mapping_file')
        ];

        return $this;
    }

    /**
     * @return \Doctrine\ORM\Configuration|mixed
     */
    public function resolve()
    {
        $configuration = Setup::createConfiguration(
            array_get($this->settings, 'dev'),
            array_get($this->settings, 'proxy_path'),
            $this->getCache()
        );

        $configuration->setMetadataDriverImpl(
            new ConfigDriver(
                config(array_get($this->settings, 'mapping_file'), [])
            )
        );

        return $configuration;
    }
}
