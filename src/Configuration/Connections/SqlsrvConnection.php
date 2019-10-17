<?php

namespace LaravelDoctrine\ORM\Configuration\Connections;

use Illuminate\Support\Arr;

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
            'host'                => Arr::get($settings, 'host'),
            'dbname'              => Arr::get($settings, 'database'),
            'user'                => Arr::get($settings, 'username'),
            'password'            => Arr::get($settings, 'password'),
            'port'                => Arr::get($settings, 'port'),
            'prefix'              => Arr::get($settings, 'prefix'),
            'charset'             => Arr::get($settings, 'charset'),
            'defaultTableOptions' => Arr::get($settings, 'defaultTableOptions', []),
            'serverVersion'       => Arr::get($settings, 'serverVersion'),
            'wrapperClass'        => Arr::get($settings, 'wrapperClass')
        ];
    }
}
