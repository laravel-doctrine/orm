<?php

namespace LaravelDoctrine\ORM\Configuration\Connections;

class OracleConnection extends AbstractConnection
{
    /**
     * @var string
     */
    protected $name = 'oracle';

    /**
     * @param array $config
     *
     * @return OracleConnection
     */
    public function configure($config = [])
    {
        return new static ([
            'driver'   => 'oci8',
            'host'     => array_get($config, 'host'),
            'dbname'   => array_get($config, 'database'),
            'user'     => array_get($config, 'username'),
            'password' => array_get($config, 'password'),
            'charset'  => array_get($config, 'charset'),
            'prefix'   => array_get($config, 'prefix'),
            'port'     => array_get($config, 'port'),
        ]);
    }
}
