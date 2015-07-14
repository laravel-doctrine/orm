<?php

namespace Brouwers\LaravelDoctrine\Configuration\Connections;

class SqlsrvConnection extends AbstractConnection
{
    /**
     * @var string
     */
    protected $name = 'sqlsrv';

    /**
     * @param array $config
     *
     * @return SqlsrvConnection
     */
    public function configure($config = [])
    {
        return new static ([
            'driver'   => 'pdo_sqlsrv',
            'host'     => array_get($config, 'host'),
            'dbname'   => array_get($config, 'database'),
            'user'     => array_get($config, 'username'),
            'password' => array_get($config, 'password'),
            'prefix'   => array_get($config, 'prefix'),
            'port'     => array_get($config, 'port'),
        ]);
    }
}
