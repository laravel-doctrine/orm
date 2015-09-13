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
            'user'     => $this->config->get('database.connections.sqlite.username'),
            'password' => $this->config->get('database.connections.sqlite.password'),
            'prefix'   => $this->config->get('database.connections.sqlite.prefix'),
            'memory'   => $this->getMemory(),
            'path'     => $this->getPath()
        ];
    }

    /**
     * @return bool
     */
    protected function getMemory()
    {
        return $this->config->get('database.connections.sqlite.database') == ':memory' ? true : false;
    }

    /**
     * @return string
     */
    protected function getPath()
    {
        return $this->config->get('database.connections.sqlite.database') == ':memory'
            ? null
            : $this->config->get('database.connections.sqlite.database');
    }
}
