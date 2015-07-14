<?php

namespace Brouwers\LaravelDoctrine\Configuration\MetaData;

use Doctrine\Common\Persistence\Mapping\Driver\StaticPHPDriver;
use Doctrine\ORM\Tools\Setup;

class StaticPhp extends AbstractMetaData
{
    /**
     * @var string
     */
    protected $name = 'static_php';

    /**
     * @param array $settings
     * @param bool  $dev
     *
     * @return static
     */
    public function configure(array $settings = [], $dev = false)
    {
        $this->settings = [
            'dev'        => $dev,
            'paths'      => array_get($settings, 'paths'),
            'proxy_path' => array_get($settings, 'proxies.path')
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
            new StaticPHPDriver(
                array_get($this->settings, 'paths')
            )
        );

        return $configuration;
    }
}
