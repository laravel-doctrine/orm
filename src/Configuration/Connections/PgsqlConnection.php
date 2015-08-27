<?php

namespace LaravelDoctrine\ORM\Configuration\Connections;

use Illuminate\Contracts\Config\Repository;
use LaravelDoctrine\ORM\Configuration\Driver;

class PgsqlConnection implements Driver
{
    /**
     * @var Repository
     */
    protected $config;

    /**
     * @param Repository $config
     */
    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    /**
     * @param array $settings
     *
     * @return array
     */
    public function resolve(array $settings = [])
    {
        return [
            'driver'   => 'pdo_pgsql',
            'host'     => $this->config->get('database.connections.mysql.host'),
            'dbname'   => $this->config->get('database.connections.mysql.database'),
            'user'     => $this->config->get('database.connections.mysql.username'),
            'password' => $this->config->get('database.connections.mysql.password'),
            'charset'  => $this->config->get('database.connections.mysql.charset'),
            'port'     => $this->config->get('database.connections.mysql.port'),
            'sslmode'  => $this->config->get('database.connections.mysql.sslmode'),
            'prefix'   => $this->config->get('database.connections.mysql.prefix'),
        ];
    }
}
