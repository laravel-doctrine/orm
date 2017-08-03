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
            'driver'                => 'oci8',
            'host'                  => array_get($settings, 'host'),
            'dbname'                => array_get($settings, 'database'),
            'servicename'           => array_get($settings, 'service_name'),
            'service'               => array_get($settings, 'service'),
            'user'                  => array_get($settings, 'username'),
            'password'              => array_get($settings, 'password'),
            'charset'               => array_get($settings, 'charset'),
            'port'                  => array_get($settings, 'port'),
            'prefix'                => array_get($settings, 'prefix'),
            'defaultTableOptions'   => array_get($settings, 'defaultTableOptions', []),
        ];
    }
}
