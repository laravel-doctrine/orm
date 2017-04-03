<?php

namespace LaravelDoctrine\ORM\Configuration\Connections;

class SqlsrvConnection extends Connection
{
    /**
     * @param array $settings
     *
     * @return array
     */
    public function resolve(array $settings = [])
    {
        return [
            'driver'              => 'pdo_sqlsrv',
            'host'                => array_get($settings, 'host'),
            'dbname'              => array_get($settings, 'database'),
            'user'                => array_get($settings, 'username'),
            'password'            => array_get($settings, 'password'),
            'port'                => array_get($settings, 'port'),
            'prefix'              => array_get($settings, 'prefix'),
            'charset'             => array_get($settings, 'charset'),
            'defaultTableOptions' => array_get($settings, 'defaultTableOptions', []),
        ];
    }
}
