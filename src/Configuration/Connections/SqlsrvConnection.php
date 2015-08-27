<?php

namespace LaravelDoctrine\ORM\Configuration\Connections;

use Illuminate\Contracts\Config\Repository;
use LaravelDoctrine\ORM\Configuration\Driver;

class SqlsrvConnection implements Driver
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
