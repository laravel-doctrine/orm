<?php

namespace LaravelDoctrine\ORM;

use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\Cache\DefaultCacheFactory;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Setup;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;
use LaravelDoctrine\ORM\Configuration\Cache\CacheManager;
use LaravelDoctrine\ORM\Configuration\Connections\ConnectionManager;
use LaravelDoctrine\ORM\Configuration\Connections\MasterSlaveConnection;
use LaravelDoctrine\ORM\Configuration\LaravelNamingStrategy;
use LaravelDoctrine\ORM\Configuration\MetaData\MetaData;
use LaravelDoctrine\ORM\Configuration\MetaData\MetaDataManager;
use LaravelDoctrine\ORM\Extensions\MappingDriverChain;
use LaravelDoctrine\ORM\Resolvers\EntityListenerResolver;
use ReflectionException;

class EntityManagerFactory
{
    /**
     * @var MetaDataManager
     */
    protected $meta;

    /**
     * @var ConnectionManager
     */
    protected $connection;

    /**
     * @var Repository
     */
    protected $config;

    /**
     * @var CacheManager
     */
    protected $cache;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var Setup
     */
    private $setup;

    /**
     * @var EntityListenerResolver
     */
    private $resolver;

    /**
     * @param Container              $container
     * @param Setup                  $setup
     * @param MetaDataManager        $meta
     * @param ConnectionManager      $connection
     * @param CacheManager           $cache
     * @param Repository             $config
     * @param EntityListenerResolver $resolver
     */
    public function __construct(
        Container $container,
        Setup $setup,
        MetaDataManager $meta,
        ConnectionManager $connection,
        CacheManager $cache,
        Repository $config,
        EntityListenerResolver $resolver
    ) {
        $this->meta       = $meta;
        $this->connection = $connection;
        $this->config     = $config;
        $this->cache      = $cache;
        $this->container  = $container;
        $this->setup      = $setup;
        $this->resolver   = $resolver;
    }

    /**
     * @param array $settings
     *
     * @return EntityManagerInterface
     */
    public function create(array $settings = [])
    {
        $defaultDriver = $this->config->get('doctrine.cache.default', 'array');

        $configuration = $this->setup->createConfiguration(
            array_get($settings, 'dev', false),
            array_get($settings, 'proxies.path'),
            $this->cache->driver($defaultDriver)
        );

        $this->setMetadataDriver($settings, $configuration);

        $driver = $this->getConnectionDriver($settings);

        $connection = $this->connection->driver(
            $driver['driver'],
            $driver
        );

        if ($this->isMasterSlaveConfigured($driver)) {
            $this->hasValidMasterSlaveConfig($driver);
            $connection = (new MasterSlaveConnection($this->config, $connection))->resolve($driver);
        }

        $this->setNamingStrategy($settings, $configuration);
        $this->setCustomFunctions($configuration);
        $this->setCustomHydrationModes($configuration);
        $this->setCacheSettings($configuration);
        $this->configureProxies($settings, $configuration);
        $this->setCustomMappingDriverChain($settings, $configuration);
        $this->registerPaths($settings, $configuration);
        $this->setRepositoryFactory($settings, $configuration);

        $configuration->setDefaultRepositoryClassName(
            array_get($settings, 'repository', EntityRepository::class)
        );

        $configuration->setEntityListenerResolver($this->resolver);

        $manager = EntityManager::create(
            $connection,
            $configuration
        );

        $manager = $this->decorateManager($settings, $manager);

        $this->setLogger($manager, $configuration);
        $this->registerListeners($settings, $manager);
        $this->registerSubscribers($settings, $manager);
        $this->registerFilters($settings, $configuration, $manager);
        $this->registerMappingTypes($settings, $manager);

        return $manager;
    }

    /**
     * @param array $settings
     * @param       $configuration
     */
    private function setMetadataDriver(array $settings, Configuration $configuration)
    {
        $metadata = $this->meta->driver(
            array_get($settings, 'meta'),
            $settings,
            false
        );

        if ($metadata instanceof MetaData) {
            $configuration->setMetadataDriverImpl($metadata->resolve($settings));
            $configuration->setClassMetadataFactoryName($metadata->getClassMetadataFactoryName());
        } else {
            $configuration->setMetadataDriverImpl($metadata);
        }
    }

    /**
     * @param array                  $settings
     * @param EntityManagerInterface $manager
     */
    protected function registerListeners(array $settings = [], EntityManagerInterface $manager)
    {
        if (isset($settings['events']['listeners'])) {
            foreach ($settings['events']['listeners'] as $event => $listener) {
                $this->registerListener($event, $listener, $manager);
            }
        }
    }

    /**
     * @param string                 $event
     * @param string|string[]        $listener
     * @param EntityManagerInterface $manager
     */
    private function registerListener($event, $listener, EntityManagerInterface $manager)
    {
        if (is_array($listener)) {
            foreach ($listener as $individualListener) {
                $this->registerListener($event, $individualListener, $manager);
            }

            return;
        }

        try {
            $resolvedListener = $this->container->make($listener);
        } catch (ReflectionException $e) {
            throw new InvalidArgumentException(
                "Listener {$listener} could not be resolved: {$e->getMessage()}",
                0,
                $e
            );
        }

        $manager->getEventManager()->addEventListener($event, $resolvedListener);
    }

    /**
     * @param array                  $settings
     * @param EntityManagerInterface $manager
     */
    protected function registerSubscribers(array $settings = [], EntityManagerInterface $manager)
    {
        if (isset($settings['events']['subscribers'])) {
            foreach ($settings['events']['subscribers'] as $subscriber) {
                try {
                    $resolvedSubscriber = $this->container->make($subscriber);
                } catch (ReflectionException $e) {
                    throw new InvalidArgumentException("Listener {$subscriber} could not be resolved: {$e->getMessage()}");
                }

                $manager->getEventManager()->addEventSubscriber($resolvedSubscriber);
            }
        }
    }

    /**
     * @param array                  $settings
     * @param Configuration          $configuration
     * @param EntityManagerInterface $manager
     */
    protected function registerFilters(
        array $settings = [],
        Configuration $configuration,
        EntityManagerInterface $manager
    ) {
        if (isset($settings['filters'])) {
            foreach ($settings['filters'] as $name => $filter) {
                $configuration->addFilter($name, $filter);
                $manager->getFilters()->enable($name);
            }
        }
    }

    /**
     * @param array         $settings
     * @param Configuration $configuration
     */
    protected function registerPaths(array $settings = [], Configuration $configuration)
    {
        $configuration->getMetadataDriverImpl()->addPaths(
            array_get($settings, 'paths', [])
        );
    }

    /**
     * @param array         $settings
     * @param Configuration $configuration
     */
    protected function setRepositoryFactory($settings, Configuration $configuration)
    {
        if (array_get($settings, 'repository_factory', false)) {
            $configuration->setRepositoryFactory(
                $this->container->make(array_get($settings, 'repository_factory', false))
            );
        }
    }

    /**
     * @param array         $settings
     * @param Configuration $configuration
     */
    protected function configureProxies(array $settings = [], Configuration $configuration)
    {
        $configuration->setProxyDir(
            array_get($settings, 'proxies.path')
        );

        $configuration->setAutoGenerateProxyClasses(
            array_get($settings, 'proxies.auto_generate', false)
        );

        if ($namespace = array_get($settings, 'proxies.namespace', false)) {
            $configuration->setProxyNamespace($namespace);
        }
    }

    /**
     * @param EntityManagerInterface $em
     * @param Configuration          $configuration
     */
    protected function setLogger(EntityManagerInterface $em, Configuration $configuration)
    {
        if ($this->config->get('doctrine.logger', false)) {
            $this->container->make(
                $this->config->get('doctrine.logger', false)
            )->register($em, $configuration);
        }
    }

    /**
     * @param array         $settings
     * @param Configuration $configuration
     */
    protected function setNamingStrategy(array $settings = [], Configuration $configuration)
    {
        $strategy = array_get($settings, 'naming_strategy', LaravelNamingStrategy::class);

        $configuration->setNamingStrategy(
            $this->container->make($strategy)
        );
    }

    /**
     * @param Configuration $configuration
     */
    protected function setCustomFunctions(Configuration $configuration)
    {
        $configuration->setCustomDatetimeFunctions($this->config->get('doctrine.custom_datetime_functions'));
        $configuration->setCustomNumericFunctions($this->config->get('doctrine.custom_numeric_functions'));
        $configuration->setCustomStringFunctions($this->config->get('doctrine.custom_string_functions'));
    }

    /**
     * @param Configuration $configuration
     */
    protected function setCustomHydrationModes(Configuration $configuration)
    {
        $hydratorConfig = $this->config->get('doctrine.custom_hydration_modes', []);
        foreach ($hydratorConfig as $hydrationModeName => $customHydratorClass) {
            $configuration->addCustomHydrationMode($hydrationModeName, $customHydratorClass);
        }
    }

    /**
     * @param Configuration $configuration
     */
    protected function setCacheSettings(Configuration $configuration)
    {
        $configuration->setQueryCacheImpl($this->applyNamedCacheConfiguration('query'));
        $configuration->setResultCacheImpl($this->applyNamedCacheConfiguration('result'));
        $configuration->setMetadataCacheImpl($this->applyNamedCacheConfiguration('metadata'));

        $this->setSecondLevelCaching($configuration);
    }

    /**
     * @param  string $cacheName
     * @return Cache
     */
    private function applyNamedCacheConfiguration($cacheName)
    {
        $defaultDriver    = $this->config->get('doctrine.cache.default', 'array');
        $defaultNamespace = $this->config->get('doctrine.cache.namespace');

        $driver = $this->config->get('doctrine.cache.' . $cacheName . '.driver', $defaultDriver);

        $cache = $this->cache->driver($driver);

        if ($namespace = $this->config->get('doctrine.cache.' . $cacheName . '.namespace', $defaultNamespace)) {
            $cache->setNamespace($namespace);
        }

        return $cache;
    }

    /**
     * @param Configuration $configuration
     */
    protected function setSecondLevelCaching(Configuration $configuration)
    {
        if ($this->config->get('doctrine.cache.second_level', false)) {
            $configuration->setSecondLevelCacheEnabled(true);

            $cacheConfig = $configuration->getSecondLevelCacheConfiguration();
            $cacheConfig->setCacheFactory(
                new DefaultCacheFactory(
                    $cacheConfig->getRegionsConfiguration(),
                    $this->cache->driver()
                )
            );
        }
    }

    /**
     * @param array         $settings
     * @param Configuration $configuration
     */
    protected function setCustomMappingDriverChain(array $settings = [], Configuration $configuration)
    {
        $chain = new MappingDriverChain(
            $configuration->getMetadataDriverImpl(),
            'LaravelDoctrine'
        );

        foreach (array_get($settings, 'namespaces', []) as $alias => $namespace) {
            // Add an alias for the namespace using the key
            if (is_string($alias)) {
                $configuration->addEntityNamespace($alias, $namespace);
            }

            $chain->addNamespace($namespace);
        }

        $configuration->setMetadataDriverImpl(
            $chain
        );
    }

    /**
     * @param                        $settings
     * @param EntityManagerInterface $manager
     *
     * @return mixed
     */
    protected function decorateManager(array $settings = [], EntityManagerInterface $manager)
    {
        if ($decorator = array_get($settings, 'decorator', false)) {
            if (!class_exists($decorator)) {
                throw new InvalidArgumentException("EntityManagerDecorator {$decorator} does not exist");
            }

            $manager = new $decorator($manager);
        }

        return $manager;
    }

    /**
     * @param array $settings
     *
     * @return array
     */
    protected function getConnectionDriver(array $settings = [])
    {
        $connection = array_get($settings, 'connection');
        $key        = 'database.connections.' . $connection;

        if (!$this->config->has($key)) {
            throw new InvalidArgumentException("Connection [{$connection}] has no configuration in [{$key}]");
        }

        return $this->config->get($key);
    }

    /**
     * @param                        $settings
     * @param EntityManagerInterface $manager
     *
     * @throws \Doctrine\DBAL\DBALException If Database Type or Doctrine Type is not found.
     */
    protected function registerMappingTypes(array $settings = [], EntityManagerInterface $manager)
    {
        foreach (array_get($settings, 'mapping_types', []) as $dbType => $doctrineType) {
            // Throw DBALException if Doctrine Type is not found.
            $manager->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping($dbType, $doctrineType);
        }
    }

    /**
     * Check if master slave connection was being configured.
     *
     * @param array $driverConfig
     *
     * @return bool
     */
    private function isMasterSlaveConfigured(array $driverConfig)
    {
        // Setting read is mandatory for master/slave configuration. Setting write is optional.
        // But if write was set and read wasn't, it means configuration is incorrect and we must inform the user.
        return isset($driverConfig['read']) || isset($driverConfig['write']);
    }

    /**
     * Check if slave configuration is valid.
     *
     * @param array $driverConfig
     */
    private function hasValidMasterSlaveConfig(array $driverConfig)
    {
        if (!isset($driverConfig['read'])) {
            throw new \InvalidArgumentException("Parameter 'read' must be set for read/write config.");
        }

        $slaves = $driverConfig['read'];

        if (!is_array($slaves) || in_array(false, array_map('is_array', $slaves))) {
            throw new \InvalidArgumentException("Parameter 'read' must be an array containing multiple arrays.");
        }

        if (($key = array_search(0, array_map('count', $slaves))) !== false) {
            throw new \InvalidArgumentException("Parameter 'read' config no. {$key} is empty.");
        }
    }
}
