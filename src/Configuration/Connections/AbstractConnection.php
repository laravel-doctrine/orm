<?php

namespace Brouwers\LaravelDoctrine\Configuration\Connections;

abstract class AbstractConnection implements Connection
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
     * @param array $settings
     * @param null  $name
     */
    public function __construct($settings = [], $name = null)
    {
        $this->settings = $settings;

        if ($name) {
            $this->name = $name;
        }
    }

    /**
     * @return array
     */
    public function resolve()
    {
        return $this->settings;
    }

    /**
     * @return string
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
}
