<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Configuration\Connections;

use Illuminate\Support\Arr;

class MysqlConnection extends Connection
{
    /**
     * @param mixed[] $settings
     *
     * @return mixed[]
     */
    public function resolve(array $settings = []): array
    {
        return [
            'driver'              => 'pdo_mysql',
            'host'                => Arr::get($settings, 'host'),
            'dbname'              => Arr::get($settings, 'database'),
            'user'                => Arr::get($settings, 'username'),
            'password'            => Arr::get($settings, 'password'),
            'charset'             => Arr::get($settings, 'charset'),
            'port'                => Arr::get($settings, 'port'),
            'unix_socket'         => Arr::get($settings, 'unix_socket'),
            'ssl_key'             => Arr::get($settings, 'ssl_key'),
            'ssl_cert'            => Arr::get($settings, 'ssl_cert'),
            'ssl_ca'              => Arr::get($settings, 'ssl_ca'),
            'ssl_capath'          => Arr::get($settings, 'ssl_capath'),
            'ssl_cipher'          => Arr::get($settings, 'ssl_cipher'),
            'prefix'              => Arr::get($settings, 'prefix'),
            'defaultTableOptions' => Arr::get($settings, 'defaultTableOptions', []),
            'serverVersion'       => Arr::get($settings, 'serverVersion'),
            'wrapperClass'        => Arr::get($settings, 'wrapperClass'),
            'driverOptions'       => Arr::get($settings, 'options', []),
        ];
    }
}
