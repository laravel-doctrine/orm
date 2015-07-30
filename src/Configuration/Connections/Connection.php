<?php

namespace LaravelDoctrine\ORM\Configuration\Connections;

use LaravelDoctrine\ORM\Configuration\Driver;

interface Connection extends Driver
{
    /**
     * @param array $config
     *
     * @return Connection
     */
    public function configure($config = []);
}
