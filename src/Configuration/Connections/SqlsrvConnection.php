<?php

namespace LaravelDoctrine\ORM\Configuration\Connections;

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
            'driver'   => 'pdo_sqlsrv',
            'host'     => $this->config->get('database.connections.sqlsrv.host'),
            'dbname'   => $this->config->get('database.connections.sqlsrv.database'),
            'user'     => $this->config->get('database.connections.sqlsrv.username'),
            'password' => $this->config->get('database.connections.sqlsrv.password'),
            'port'     => $this->config->get('database.connections.sqlsrv.port'),
            'prefix'   => $this->config->get('database.connections.sqlsrv.prefix'),
        ];
    }
}
