<?php

namespace LaravelDoctrine\ORM\Configuration\Connections;

use Illuminate\Support\Arr;

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
            'host'                  => Arr::get($settings, 'host'),
            'dbname'                => Arr::get($settings, 'database'),
            'user'                  => Arr::get($settings, 'username'),
            'password'              => Arr::get($settings, 'password'),
            'charset'               => Arr::get($settings, 'charset'),
            'port'                  => Arr::get($settings, 'port'),
            'unix_socket'           => Arr::get($settings, 'unix_socket'),
            'prefix'                => Arr::get($settings, 'prefix'),
            'defaultTableOptions'   => Arr::get($settings, 'defaultTableOptions', []),
            'driverOptions'         => Arr::get($settings, 'driverOptions', []),
            'serverVersion'         => Arr::get($settings, 'serverVersion'),
            'wrapperClass'          => Arr::get($settings, 'wrapperClass')
        ];
    }
}
