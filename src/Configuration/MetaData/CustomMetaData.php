<?php

namespace Brouwers\LaravelDoctrine\Configuration\MetaData;

class CustomMetaData extends AbstractMetaData
{
    /**
     * @var
     */
    protected $meta;

    /**
     * @param $meta
     * @param $name
     */
    public function __construct($meta, $name)
    {
        $this->meta = $meta;
        $this->name = $name;
    }

    /**
     * @param array $settings
     * @param bool  $dev
     *
     * @return static
     */
    public function configure(array $settings = [], $dev = false)
    {
        return $this;
    }

    /**
     * @return mixed
     */
    public function resolve()
    {
        return $this->meta;
    }
}
