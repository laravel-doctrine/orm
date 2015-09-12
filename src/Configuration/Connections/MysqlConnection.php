<?php

namespace LaravelDoctrine\ORM\Configuration\Connections;

class MysqlConnection extends Connection
{
    /**
     * @param array $settings
     *
     * @return array
     */
    public function resolve(array $settings = [])
    {
        return [
            'driver'      => 'pdo_mysql',
            'host'        => $this->config->get('database.connections.mysql.host'),
            'dbname'      => $this->config->get('database.connections.mysql.database'),
            'user'        => $this->config->get('database.connections.mysql.username'),
            'password'    => $this->config->get('database.connections.mysql.password'),
            'charset'     => $this->config->get('database.connections.mysql.charset'),
            'port'        => $this->config->get('database.connections.mysql.port'),
            'unix_socket' => $this->config->get('database.connections.mysql.unix_socket'),
            'prefix'      => $this->config->get('database.connections.mysql.prefix'),
        ];
    }
}
