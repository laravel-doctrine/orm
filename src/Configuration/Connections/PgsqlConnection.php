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
            'host'     => $this->config->get('database.connections.pgsql.host'),
            'dbname'   => $this->config->get('database.connections.pgsql.database'),
            'user'     => $this->config->get('database.connections.pgsql.username'),
            'password' => $this->config->get('database.connections.pgsql.password'),
            'charset'  => $this->config->get('database.connections.pgsql.charset'),
            'port'     => $this->config->get('database.connections.pgsql.port'),
            'sslmode'  => $this->config->get('database.connections.pgsql.sslmode'),
            'prefix'   => $this->config->get('database.connections.pgsql.prefix'),
        ];
    }
}
