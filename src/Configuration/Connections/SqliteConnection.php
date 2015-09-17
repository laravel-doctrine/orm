<?php

namespace LaravelDoctrine\ORM\Configuration\Connections;

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
            'driver'   => 'pdo_sqlite',
            'user'     => array_get($settings, 'username'),
            'password' => array_get($settings, 'password'),
            'prefix'   => array_get($settings, 'prefix'),
            'memory'   => $this->getMemory($settings),
            'path'     => $this->getPath($settings)
        ];
    }

    /**
     * @return bool
     */
    protected function getMemory(array $settings = [])
    {
        return array_get($settings, 'database') == ':memory' ? true : false;
    }

    /**
     * @return string
     */
    protected function getPath(array $settings = [])
    {
        return array_get($settings, 'database') == ':memory'
            ? null
            : array_get($settings, 'database');
    }
}
