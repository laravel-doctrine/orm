<?php

namespace LaravelDoctrine\ORM\Utilities;

use UnexpectedValueException;

class MasterSlaveConfigParser
{
    /**
     * @param array $config
     *
     * @throws UnexpectedValueException If config not contains valid read / write configuration.
     *
     * @return array If config is not valid for parse.
     *
     */
    public static function parseConfig(array $config)
    {
        if ( ! self::hasValidConfig($config)) {
            throw new UnexpectedValueException('Config not contains configuration for master/slave setup.');
        }

        $masterSlave = [
            'write' => [],
            'read'  => [],
        ];

        $masterSlave['write'] = [
            'user'     => array_get($config, 'write.username', $config['username']),
            'password' => array_get($config, 'write.password', $config['password']),
            'host'     => array_get($config, 'write.host', $config['host']),
            'dbname'   => array_get($config, 'write.database', $config['database']),
            'port'     => array_get($config, 'write.port', $config['port']),
        ];

        foreach (array_get($config, 'read', []) as $slaveConfig) {
            $config   = [
                'user'     => array_get($slaveConfig, 'username', $config['username']),
                'password' => array_get($slaveConfig, 'password', $config['password']),
                'host'     => array_get($slaveConfig, 'host', $config['host']),
                'dbname'   => array_get($slaveConfig, 'database', $config['database']),
                'port'     => array_get($config, 'write.port', $config['port']),
            ];

            $masterSlave['read'][] = $config;
        }

        return $masterSlave;
    }

    /**
     * @param array $config
     *
     * @return bool
     */
    public static function hasValidConfig(array $config)
    {
        if (
            array_key_exists('read', $config)
            && ! empty($config['read'])
            && is_array($config['read'])
            //All value in $config['read'] should be an array
            && ! in_array(false, array_map(function ($readConfigValue) {
                return is_array($readConfigValue);
            }, $config['read']))
            //All value in $config['read'] should have at least one config difference
            && ! in_array(false, array_map(function (array $readConfig) {
                return count($readConfig) > 0;
            }, $config['read']))
        ) {
            return true;
        }

        return false;
    }
}
