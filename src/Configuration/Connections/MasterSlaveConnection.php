<?php

namespace LaravelDoctrine\ORM\Configuration\Connections;

use Doctrine\DBAL\Connections\MasterSlaveConnection as MasterSlaveDoctrineWrapper;
use Illuminate\Contracts\Config\Repository;

/**
 * Handles master slave connection settings.
 */
class MasterSlaveConnection extends Connection
{
    /**
     * @var array|Connection
     */
    private $resolvedBaseSettings;

    /**
     * @var array Ignored configuration fields for master slave configuration.
     */
    private $masterSlaveConfigIgnored = ['driver'];

    /**
     * MasterSlaveConnection constructor.
     *
     * @param Repository       $config
     * @param array|Connection $resolvedBaseSettings
     */
    public function __construct(Repository $config, $resolvedBaseSettings)
    {
        parent::__construct($config);

        $this->resolvedBaseSettings = $resolvedBaseSettings;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(array $settings = [])
    {
        $driver = $this->resolvedBaseSettings['driver'];

        return [
            'wrapperClass' => MasterSlaveDoctrineWrapper::class,
            'driver'       => $driver,
            'master'       => $this->getConnectionData(isset($settings['write']) ? $settings['write'] : [], $driver),
            'slaves'       => $this->getSlavesConfig($settings['read'], $driver),
        ];
    }

    /**
     * Returns config for slave connections.
     *
     * @param array  $slaves
     * @param string $driver
     *
     * @return array
     */
    public function getSlavesConfig(array $slaves, $driver)
    {
        $handledSlaves = [];
        foreach ($slaves as $slave) {
            $handledSlaves[] = $this->getConnectionData($slave, $driver);
        }

        return $handledSlaves;
    }

    /**
     * Returns single connection (slave or master) config.
     *
     * @param array  $connection
     * @param string $driver
     *
     * @return array
     */
    private function getConnectionData(array $connection, $driver)
    {
        $connection = $this->replaceKeyIfExists($connection, 'database', $driver === 'pdo_sqlite' ? 'path' : 'dbname');
        $connection = $this->replaceKeyIfExists($connection, 'username', 'user');

        return array_merge($this->getFilteredConfig(), $connection);
    }

    /**
     * Returns filtered configuration to use in slaves/masters.
     *
     * @return array
     */
    private function getFilteredConfig()
    {
        return array_diff_key($this->resolvedBaseSettings, array_flip($this->masterSlaveConfigIgnored));
    }

    /**
     * Replaces key in array if it exists.
     *
     * @param array  $array
     * @param string $oldKey
     * @param string $newKey
     *
     * @return array
     */
    private function replaceKeyIfExists(array $array, $oldKey, $newKey)
    {
        if (!isset($array[$oldKey])) {
            return $array;
        }

        $array[$newKey] = $array[$oldKey];
        unset($array[$oldKey]);

        return $array;
    }
}
