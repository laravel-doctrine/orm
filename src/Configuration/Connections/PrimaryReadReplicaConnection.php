<?php

namespace LaravelDoctrine\ORM\Configuration\Connections;

use Doctrine\DBAL\Connections\PrimaryReadReplicaConnection as PrimaryReadReplicaDoctrineWrapper;
use Illuminate\Contracts\Config\Repository;

/**
 * Handles primary read replica connection settings.
 */
class PrimaryReadReplicaConnection extends Connection
{
    /**
     * @var array|Connection
     */
    private $resolvedBaseSettings;

    /**
     * @var array Ignored configuration fields for master slave configuration.
     */
    private $primaryReadReplicaConfigIgnored = ['driver'];

    /**
     * PrimaryReadReplicaConnection constructor.
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

        $resolvedSettings = [
            'wrapperClass'  => $settings['wrapperClass'] ?? PrimaryReadReplicaDoctrineWrapper::class,
            'driver'        => $driver,
            'primary'       => $this->getConnectionData(isset($settings['write']) ? $settings['write'] : [], $driver),
            'replica'       => $this->getReplicasConfig($settings['read'], $driver),
        ];

        if (!empty($settings['serverVersion'])) {
            $resolvedSettings['serverVersion'] = $settings['serverVersion'];
        }

        if (!empty($settings['defaultTableOptions'])) {
            $resolvedSettings['defaultTableOptions'] = $settings['defaultTableOptions'];
        }

        return $resolvedSettings;
    }

    /**
     * Returns config for read replicas connections.
     *
     * @param array  $replicas
     * @param string $driver
     *
     * @return array
     */
    public function getReplicasConfig(array $replicas, $driver)
    {
        $handledReplicas = [];
        foreach ($replicas as $replica) {
            $handledReplicas[] = $this->getConnectionData($replica, $driver);
        }

        return $handledReplicas;
    }

    /**
     * Returns single connection (replica or primary) config.
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
        return array_diff_key($this->resolvedBaseSettings, array_flip($this->primaryReadReplicaConfigIgnored));
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
