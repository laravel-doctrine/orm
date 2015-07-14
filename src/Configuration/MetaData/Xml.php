<?php

namespace Brouwers\LaravelDoctrine\Configuration\MetaData;

use Doctrine\ORM\Tools\Setup;

class Xml extends AbstractMetaData
{
    /**
     * @var string
     */
    protected $name = 'xml';

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
            'paths'      => array_get($settings, 'paths', []),
            'proxy_path' => array_get($settings, 'proxies.path'),
        ];

        return $this;
    }

    /**
     * @return \Doctrine\ORM\Configuration|mixed
     */
    public function resolve()
    {
        return Setup::createXMLMetadataConfiguration(
            array_get($this->settings, 'paths'),
            array_get($this->settings, 'dev'),
            array_get($this->settings, 'proxy_path'),
            $this->getCache()
        );
    }
}
