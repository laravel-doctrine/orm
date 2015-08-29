<?php

namespace LaravelDoctrine\ORM;

use DebugBar\Bridge\DoctrineCollector;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\ORM\Cache\DefaultCacheFactory;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Str;
use LaravelDoctrine\ORM\Configuration\Cache\CacheManager;
use LaravelDoctrine\ORM\Configuration\Connections\ConnectionManager;
use LaravelDoctrine\ORM\Configuration\LaravelNamingStrategy;
use LaravelDoctrine\ORM\Configuration\MetaData\MetaDataManager;
use LaravelDoctrine\ORM\Exceptions\ClassNotFound;
use LaravelDoctrine\ORM\Extensions\MappingDriverChain;

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
     * @param Container         $container
     * @param MetaDataManager   $meta
     * @param ConnectionManager $connection
     * @param CacheManager      $cache
     * @param Repository        $config
     */
    public function __construct(
        Container $container,
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
    }

    /**
     * @param array $settings
     *
     * @return EntityManager
     */
    public function create($settings = [])
    {
        $configuration = $this->meta->driver(
            array_get($settings, 'meta'),
            $settings
        );

        $connection = $this->connection->driver(
            array_get($settings, 'connection')
        );

        $this->setLogger($configuration);
        $this->setNamingStrategy($configuration);
        $this->setCustomFunctions($configuration);
        $this->setSecondLevelCaching($configuration);
        $this->registerPaths($settings, $configuration);
        $this->configureProxies($settings, $configuration);
        $this->setCustomMappingDriverChain($settings, $configuration);

        $configuration->setDefaultRepositoryClassName(
            array_get($settings, 'repository', EntityRepository::class)
        );

        $manager = EntityManager::create(
            $connection,
            $configuration
        );

        $this->registerListeners($settings, $manager);
        $this->registerSubscribers($settings, $manager);
        $this->registerFilters($settings, $configuration, $manager);

        return $manager;
    }

    /**
     * @param array         $settings
     * @param EntityManager $manager
     */
    protected function registerListeners($settings = [], EntityManager $manager)
    {
        if (isset($settings['events']['listeners'])) {
            foreach ($settings['events']['listeners'] as $event => $listener) {
                if (class_exists($listener, false)) {
                    $manager->getEventManager()->addEventListener($event, new $listener);
                } else {
                    throw new ClassNotFound($listener);
                }
            }
        }
    }

    /**
     * @param array         $settings
     * @param EntityManager $manager
     */
    protected function registerSubscribers($settings = [], EntityManager $manager)
    {
        if (isset($settings['events']['subscribers'])) {
            foreach ($settings['events']['subscribers'] as $subscriber) {
                if (class_exists($subscriber, false)) {
                    $manager->getEventManager()->addEventSubscriber(new $subscriber);
                } else {
                    throw new ClassNotFound($subscriber);
                }
            }
        }
    }

    /**
     * @param array         $settings
     * @param Configuration $configuration
     * @param EntityManager $manager
     */
    protected function registerFilters($settings = [], Configuration $configuration, EntityManager $manager = null)
    {
        if (isset($settings['filters'])) {
            foreach ($settings['filters'] as $name => $filter) {
                $configuration->getMetadataDriverImpl()->addFilter($name, $filter);
                $manager->getFilters()->enable($name);
            }
        }
    }

    /**
     * @param array         $settings
     * @param Configuration $configuration
     */
    protected function registerPaths($settings = [], Configuration $configuration)
    {
        $paths = array_get($settings, 'paths', []);
        $meta  = $configuration->getMetadataDriverImpl();

        if (method_exists($meta, 'addPaths')) {
            $meta->addPaths($paths);
        } elseif (method_exists($meta, 'getLocator')) {
            $meta->getLocator()->addPaths($paths);
        }
    }

    /**
     * @param array         $settings
     * @param Configuration $configuration
     */
    protected function configureProxies($settings = [], Configuration $configuration)
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
     * @param Configuration $configuration
     */
    protected function setLogger(Configuration $configuration)
    {
        if ($this->config->get('doctrine.debugbar', false) === true) {
            $debugStack = new DebugStack();
            $configuration->setSQLLogger($debugStack);
            $this->container->make('debugbar')->addCollector(
                new DoctrineCollector($debugStack)
            );
        }
    }

    /**
     * @param Configuration $configuration
     */
    protected function setNamingStrategy(Configuration $configuration)
    {
        $configuration->setNamingStrategy(
            new LaravelNamingStrategy(new Str)
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
    protected function setSecondLevelCaching(Configuration $configuration)
    {
        if ($this->config->get('cache.second_level', false)) {
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
    protected function setCustomMappingDriverChain($settings = [], Configuration $configuration)
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
}
