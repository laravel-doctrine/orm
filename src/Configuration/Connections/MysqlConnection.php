<?php

namespace LaravelDoctrine\ORM\Configuration\Connections;

class MysqlConnection extends Connection
{
    /**
     * @param array $settings
     *
     * @return array
     */
    public function resolve(array $settings = [])
    {
        return [
            'driver'                => 'pdo_mysql',
            'host'                  => array_get($settings, 'host'),
            'dbname'                => array_get($settings, 'database'),
            'user'                  => array_get($settings, 'username'),
            'password'              => array_get($settings, 'password'),
            'charset'               => array_get($settings, 'charset'),
            'port'                  => array_get($settings, 'port'),
            'unix_socket'           => array_get($settings, 'unix_socket'),
            'prefix'                => array_get($settings, 'prefix'),
            'defaultTableOptions'   => array_get($settings, 'defaultTableOptions', []),
            'driverOptions'         => array_get($settings, 'driverOptions', []),
        ];
    }
}
