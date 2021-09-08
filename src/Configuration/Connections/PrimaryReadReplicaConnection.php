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

        $writeReplicas = $this->getReplicasConfiguration(isset($settings['write']) ? $settings['write'] : [], $driver);

        if (count($writeReplicas) !== 1) {
            throw new \InvalidArgumentException(
                "There should be exactly 1 write replica. " . count($writeReplicas) . " found."
            );
        }

        $resolvedSettings = [
            'wrapperClass'  => $settings['wrapperClass'] ?? PrimaryReadReplicaDoctrineWrapper::class,
            'driver'        => $driver,
            'primary'       => $writeReplicas[0],
            'replica'       => $this->getReadReplicasConfig($settings['read'], $driver),
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
     */
    public function getReadReplicasConfig(array $replicas, string $driver): array
    {
        // Handle undocumented laravel read/write config,
        // which allows multiple replica configs to be specified in 'read' config option
        // Example: 'read' => [['host' => 'host1'], ['host' => 'host2']
        if (isset($replicas[0])) {
            // Treat $replicas as an array of configs
            $handledReplicas = [];

            foreach ($replicas as $replicaConfig) {
                $handledReplicas[] = $this->getReplicasConfiguration($replicaConfig, $driver);
            }

            return array_merge(...$handledReplicas);
        }

        // Or handle documented laravel configuration format
        // Example 1: 'read' => ['host' => 'host1']
        // Example 2: 'read' => ['host' => ['host1', 'host2']]
        return $this->getReplicasConfiguration($replicas, $driver);
    }

    /**
     * Creates a configuration for replica based on standard documented Laravel format:
     * Compatible with Laravel 5.5 and Laravel 5.6+ config
     * @see https://laravel.com/docs/8.x/database#read-and-write-connections
     * @see https://laravel.com/docs/5.6/database#read-and-write-connections
     */
    private function getReplicasConfiguration(array $replicaConfig, string $driver): array
    {
        $handledReplicas = [];

        // Handle Laravel 5.6 config with 'host' as an array
        if (isset($replicaConfig['host']) && is_array($replicaConfig['host'])) {
            foreach ($replicaConfig['host'] as $host) {
                $replica           = $this->getConnectionData($replicaConfig, $driver);
                $replica['host']   = $host;
                $handledReplicas[] = $replica;
            }

            return $handledReplicas;
        }

        // Handle plain text single value in 'host' key and configuration array without 'host' key at all
        $replica           = $this->getConnectionData($replicaConfig, $driver);
        $handledReplicas[] = $replica;

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
