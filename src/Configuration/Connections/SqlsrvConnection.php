<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Configuration\Connections;

use Illuminate\Support\Arr;

use function array_merge;

class SqlsrvConnection extends Connection
{
    /**
     * @param mixed[] $settings
     *
     * @return mixed[]
     */
    public function resolve(array $settings = []): array
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
            'wrapperClass'        => Arr::get($settings, 'wrapperClass'),
            'driverOptions'       => array_merge(
                Arr::get($settings, 'options', []),
                // @codeCoverageIgnoreStart
                isset($settings['encrypt'])
                    ? ['encrypt' => Arr::get($settings, 'encrypt')]
                    : [],
                isset($settings['trust_server_certificate'])
                    ? ['trustServerCertificate' => Arr::get($settings, 'trust_server_certificate')]
                    : [],
                // @codeCoverageIgnoreEnd
            ),
        ];
    }
}
