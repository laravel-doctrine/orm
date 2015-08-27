<?php

namespace LaravelDoctrine\ORM\Configuration\Connections;

use Illuminate\Contracts\Config\Repository;
use LaravelDoctrine\ORM\Configuration\Driver;

class OracleConnection implements Driver
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
            'driver'      => 'oci8',
            'host'        => $this->config->get('database.connections.oracle.host'),
            'dbname'      => $this->config->get('database.connections.oracle.database'),
            'user'        => $this->config->get('database.connections.oracle.username'),
            'password'    => $this->config->get('database.connections.oracle.password'),
            'charset'     => $this->config->get('database.connections.oracle.charset'),
            'port'        => $this->config->get('database.connections.oracle.port'),
            'prefix'      => $this->config->get('database.connections.oracle.prefix'),
        ];
    }
}
