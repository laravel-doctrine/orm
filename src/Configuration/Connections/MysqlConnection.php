<?php

namespace Brouwers\LaravelDoctrine\Configuration\Connections;

class MysqlConnection extends AbstractConnection
{
    /**
     * @var string
     */
    protected $name = 'mysql';

    /**
     * @param array $config
     *
     * @return MysqlConnection
     */
    public function configure($config = [])
    {
        return new static ([
            'driver'      => 'pdo_mysql',
            'host'        => array_get($config, 'host'),
            'dbname'      => array_get($config, 'database'),
            'user'        => array_get($config, 'username'),
            'password'    => array_get($config, 'password'),
            'charset'     => array_get($config, 'charset'),
            'port'        => array_get($config, 'port'),
            'unix_socket' => array_get($config, 'unix_socket'),
            'prefix'      => array_get($config, 'prefix'),
        ]);
    }
}
