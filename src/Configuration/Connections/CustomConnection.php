<?php

namespace Brouwers\LaravelDoctrine\Configuration\Connections;

class CustomConnection extends AbstractConnection
{
    /**
     * @param array $settings
     * @param null  $name
     */
    public function __construct($settings, $name)
    {
        $this->settings = $settings;
        $this->name     = $name;
    }

    /**
     * @param array $config
     *
     * @return CustomConnection
     */
    public function configure($config = [])
    {
        return $this;
    }
}
