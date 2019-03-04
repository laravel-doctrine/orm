<?php

namespace LaravelDoctrine\ORM\Configuration\Connections;

use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class SqliteConnection extends Connection
{
    /**
     * @param array $settings
     *
     * @return array
     */
    public function resolve(array $settings = [])
    {
        return [
            'driver'              => 'pdo_sqlite',
            'user'                => Arr::get($settings, 'username'),
            'password'            => Arr::get($settings, 'password'),
            'prefix'              => Arr::get($settings, 'prefix'),
            'memory'              => $this->isMemory($settings),
            'path'                => Arr::get($settings, 'database'),
            'defaultTableOptions' => Arr::get($settings, 'defaultTableOptions', []),
        ];
    }

    /**
     * @param array $settings
     *
     * @return bool
     */
    protected function isMemory(array $settings = [])
    {
        return Str::startsWith(Arr::get($settings, 'database'), ':memory');
    }
}
