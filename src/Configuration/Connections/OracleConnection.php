<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Configuration\Connections;

use Illuminate\Support\Arr;

class OracleConnection extends Connection
{
    /**
     * @param mixed[] $settings
     *
     * @return mixed[]
     */
    public function resolve(array $settings = []): array
    {
        return [
            'driver'              => 'oci8',
            'host'                => Arr::get($settings, 'host'),
            'dbname'              => Arr::get($settings, 'database'),
            'servicename'         => Arr::get($settings, 'service_name'),
            'service'             => Arr::get($settings, 'service'),
            'user'                => Arr::get($settings, 'username'),
            'password'            => Arr::get($settings, 'password'),
            'charset'             => Arr::get($settings, 'charset'),
            'port'                => Arr::get($settings, 'port'),
            'prefix'              => Arr::get($settings, 'prefix'),
            'defaultTableOptions' => Arr::get($settings, 'defaultTableOptions', []),
            'persistent'          => Arr::get($settings, 'persistent'),
            'wrapperClass'        => Arr::get($settings, 'wrapperClass'),
            'connectstring'       => Arr::get($settings, 'connectstring'),
        ];
    }
}
