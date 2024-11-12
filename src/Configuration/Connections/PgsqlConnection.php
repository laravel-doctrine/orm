<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Configuration\Connections;

use Illuminate\Support\Arr;

class PgsqlConnection extends Connection
{
    /**
     * @param mixed[] $settings
     *
     * @return mixed[]
     */
    public function resolve(array $settings = []): array
    {
        return [
            'driver'              => 'pdo_pgsql',
            'host'                => Arr::get($settings, 'host'),
            'dbname'              => Arr::get($settings, 'database'),
            'user'                => Arr::get($settings, 'username'),
            'password'            => Arr::get($settings, 'password'),
            'charset'             => Arr::get($settings, 'charset'),
            'port'                => Arr::get($settings, 'port'),
            'sslmode'             => Arr::get($settings, 'sslmode'),
            'sslkey'              => Arr::get($settings, 'sslkey'),
            'sslcert'             => Arr::get($settings, 'sslcert'),
            'sslrootcert'         => Arr::get($settings, 'sslrootcert'),
            'sslcrl'              => Arr::get($settings, 'sslcrl'),
            'gssencmode'          => Arr::get($settings, 'gssencmode'),
            'prefix'              => Arr::get($settings, 'prefix'),
            'defaultTableOptions' => Arr::get($settings, 'defaultTableOptions', []),
            'serverVersion'       => Arr::get($settings, 'serverVersion'),
            'wrapperClass'        => Arr::get($settings, 'wrapperClass'),
            'driverOptions'       => Arr::get($settings, 'options', []),
        ];
    }
}
