<?php

namespace LaravelDoctrine\ORM\Configuration\Connections;

use Illuminate\Support\Str;

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
            'user'                => array_get($settings, 'username'),
            'password'            => array_get($settings, 'password'),
            'prefix'              => array_get($settings, 'prefix'),
            'memory'              => $this->isMemory($settings),
            'path'                => array_get($settings, 'database'),
            'defaultTableOptions' => array_get($settings, 'defaultTableOptions', []),
        ];
    }

    /**
     * @param array $settings
     *
     * @return bool
     */
    protected function isMemory(array $settings = [])
    {
        return Str::startsWith(array_get($settings, 'database'), ':memory');
    }
}
