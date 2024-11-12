<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Configuration\Connections;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SqliteConnection extends Connection
{
    /**
     * @param mixed[] $settings
     *
     * @return mixed[]
     */
    public function resolve(array $settings = []): array
    {
        return [
            'driver'              => 'pdo_sqlite',
            'user'                => Arr::get($settings, 'username'),
            'password'            => Arr::get($settings, 'password'),
            'prefix'              => Arr::get($settings, 'prefix'),
            'memory'              => $this->isMemory($settings),
            'path'                => Arr::get($settings, 'database'),
            'defaultTableOptions' => Arr::get($settings, 'defaultTableOptions', []),
            'driverOptions'       => Arr::get($settings, 'options', []),
            'wrapperClass'        => Arr::get($settings, 'wrapperClass'),
        ];
    }

    /** @param mixed[] $settings */
    protected function isMemory(array $settings = []): bool
    {
        return Str::startsWith(Arr::get($settings, 'database', ''), ':memory');
    }
}
