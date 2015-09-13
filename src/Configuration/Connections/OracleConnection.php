<?php

namespace LaravelDoctrine\ORM\Configuration\Connections;

class OracleConnection extends Connection
{
    /**
     * @param array $settings
     *
     * @return array
     */
    public function resolve(array $settings = [])
    {
        return [
            'driver'   => 'oci8',
            'host'     => $this->config->get('database.connections.oracle.host'),
            'dbname'   => $this->config->get('database.connections.oracle.database'),
            'user'     => $this->config->get('database.connections.oracle.username'),
            'password' => $this->config->get('database.connections.oracle.password'),
            'charset'  => $this->config->get('database.connections.oracle.charset'),
            'port'     => $this->config->get('database.connections.oracle.port'),
            'prefix'   => $this->config->get('database.connections.oracle.prefix'),
        ];
    }
}
