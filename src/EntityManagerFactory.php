<?php

namespace LaravelDoctrine\ORM;

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
use LaravelDoctrine\ORM\Configuration\LaravelNamingStrategy;
use LaravelDoctrine\ORM\Configuration\MetaData\MetaDataManager;
use LaravelDoctrine\ORM\Extensions\MappingDriverChain;
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
     * @param Container         $container
     * @param Setup             $setup
     * @param MetaDataManager   $meta
     * @param ConnectionManager $connection
     * @param CacheManager      $cache
     * @param Repository        $config
     */
    public function __construct(
        Container $container,
        Setup $setup,
        MetaDataManager $meta,
        ConnectionManager $connection,
        CacheManager $cache,
        Repository $config
    ) {
        $this->meta       = $meta;
        $this->connection = $connection;
        $this->config     = $config;
        $this->cache      = $cache;
        $this->container  = $container;
        $this->setup      = $setup;
    }

    /**
     * @param array $settings
     *
     * @return EntityManagerInterface
     */
    public function create(array $settings = [])
    {
        $configuration = $this->setup->createConfiguration(
            array_get($settings, 'dev', false),
            array_get($settings, 'proxies.path'),
            $this->cache->driver()
        );

        $configuration->setMetadataDriverImpl($this->meta->driver(
            array_get($settings, 'meta'),
            $settings
        ));

        $driver = $this->getConnectionDriver($settings);

        $connection = $this->connection->driver(
            $driver['driver'],
            $driver
        );

        $this->setNamingStrategy($settings, $configuration);
        $this->setCustomFunctions($configuration);
        $this->setCacheSettings($configuration);
        $this->configureProxies($settings, $configuration);
        $this->setCustomMappingDriverChain($settings, $configuration);
        $this->registerPaths($settings, $configuration);
        $this->setRepositoryFactory($settings, $configuration);

        $configuration->setDefaultRepositoryClassName(
            array_get($settings, 'repository', EntityRepository::class)
        );

        $manager = $this->getEntityManager($settings, $connection, $configuration);

        $manager = $this->decorateManager($settings, $manager);

        $this->setLogger($manager, $configuration);
        $this->registerListeners($settings, $manager);
        $this->registerSubscribers($settings, $manager);
        $this->registerFilters($settings, $configuration, $manager);

        return $manager;
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
    protected function setCacheSettings(Configuration $configuration)
    {
        if ($namespace = $this->config->get('doctrine.cache.namespace', null)) {
            $this->cache->driver()->setNamespace($namespace);
        }

        $this->setSecondLevelCaching($configuration);
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
     * @return string|null
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
     * @param array $settings
     * @param       $connection
     * @param       $configuration
     *
     * @return EntityManager
     * @throws \Doctrine\ORM\ORMException
     */
    protected function getEntityManager(array $settings = [], $connection, $configuration)
    {
        if ($manager = array_get($settings, 'entity_manager', false)) {
            if (!class_exists($manager)) {
                throw new InvalidArgumentException("EntityManager {$manager} does not exist");
            }

            return $manager::create($connection, $configuration);
        }

        return EntityManager::create($connection, $configuration);
    }
}
