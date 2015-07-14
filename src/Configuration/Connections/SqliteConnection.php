<?php

namespace Brouwers\LaravelDoctrine\Configuration\Connections;

class SqliteConnection extends AbstractConnection
{
    /**
     * @var string
     */
    protected $name = 'sqlite';

    /**
     * @param array $config
     *
     * @return SqliteConnection
     */
    public function configure($config = [])
    {
        return new static ([
            'driver'   => 'pdo_sqlite',
            'user'     => array_get($config, 'username'),
            'password' => array_get($config, 'password'),
            'prefix'   => array_get($config, 'prefix'),
            'memory'   => $config['database'] == ':memory' ? true : false,
            'path'     => $config['database'] == ':memory' ? null : $config['database']
        ]);
    }
}
