<?php

namespace LaravelDoctrine\ORM\Configuration\Connections;

use Illuminate\Contracts\Config\Repository;
use LaravelDoctrine\ORM\Configuration\Driver;

abstract class Connection implements Driver
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
}
