<?php

namespace Brouwers\LaravelDoctrine\Configuration\Connections;

class PgsqlConnection extends AbstractConnection
{
    /**
     * @var string
     */
    protected $name = 'pgsql';

    /**
     * @param array $config
     *
     * @return PgsqlConnection
     */
    public function configure($config = [])
    {
        return new static ([
            'driver'   => 'pdo_pgsql',
            'host'     => array_get($config, 'host'),
            'dbname'   => array_get($config, 'database'),
            'user'     => array_get($config, 'username'),
            'password' => array_get($config, 'password'),
            'charset'  => array_get($config, 'charset'),
            'port'     => array_get($config, 'port'),
            'sslmode'  => array_get($config, 'sslmode'),
            'prefix'   => array_get($config, 'prefix'),
        ]);
    }
}
