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
            'host'     => array_get($settings, 'host'),
            'dbname'   => array_get($settings, 'database'),
            'user'     => array_get($settings, 'username'),
            'password' => array_get($settings, 'password'),
            'charset'  => array_get($settings, 'charset'),
            'port'     => array_get($settings, 'port'),
            'prefix'   => array_get($settings, 'prefix'),
        ];
    }
}
