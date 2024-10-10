<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Driver\Middleware;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\DefaultSchemaManagerFactory;
use Doctrine\ORM\Cache\DefaultCacheFactory;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use LaravelDoctrine\ORM\Configuration\Cache\CacheManager;
use LaravelDoctrine\ORM\Configuration\Connections\ConnectionManager;
use LaravelDoctrine\ORM\Configuration\Connections\PrimaryReadReplicaConnection;
use LaravelDoctrine\ORM\Configuration\LaravelNamingStrategy;
use LaravelDoctrine\ORM\Configuration\MetaData\MetaData;
use LaravelDoctrine\ORM\Configuration\MetaData\MetaDataManager;
use LaravelDoctrine\ORM\Extensions\MappingDriverChain;
use LaravelDoctrine\ORM\Resolvers\EntityListenerResolver;
use LogicException;
use Psr\Cache\CacheItemPoolInterface;
use ReflectionException;

use function array_map;
use function array_search;
use function class_exists;
use function in_array;
use function is_array;

class EntityManagerFactory
{
    public function __construct(
        protected Container $container,
        private ORMSetupResolver $setup,
        protected MetaDataManager $meta,
        protected ConnectionManager $connection,
        protected CacheManager $cache,
        protected Repository $config,
        private EntityListenerResolver $resolver,
    ) {
    }

    /** @param mixed[] $settings */
    public function create(array $settings = []): EntityManagerInterface
    {
        $defaultDriver = $this->config->get('doctrine.cache.default', 'array');

        $configuration = $this->setup->createConfiguration(
            Arr::get($settings, 'dev', false),
            Arr::get($settings, 'proxies.path'),
        );

        $configuration->setSchemaManagerFactory(new DefaultSchemaManagerFactory());

        $this->setMetadataDriver($settings, $configuration);
        $this->setMiddlewares($settings, $configuration);

        $eventManager = $this->createEventManager($settings);

        $driver = $this->getConnectionDriver($settings);

        $connectionConfiguration = $this->connection->driver(
            $driver['driver'],
            $driver,
        );

        if ($this->isPrimaryReadReplicaConfigured($driver)) {
            if ($this->hasValidPrimaryReadReplicaConfig($driver)) {
                $connectionConfiguration = (new PrimaryReadReplicaConnection($this->config, $connectionConfiguration))->resolve($driver);
            }
        }

        $connection = DriverManager::getConnection(
            $connectionConfiguration,
            $configuration,
        );

        $this->setNamingStrategy($settings, $configuration);
        $this->setQuoteStrategy($settings, $configuration);
        $this->setCustomFunctions($configuration);
        $this->setCustomHydrationModes($configuration);
        $this->setCacheSettings($configuration);
        $this->configureProxies($settings, $configuration);
        $this->setCustomMappingDriverChain($settings, $configuration);
        $this->registerPaths($settings, $configuration);
        $this->setRepositoryFactory($settings, $configuration);

        $configuration->setDefaultRepositoryClassName(
            Arr::get($settings, 'repository', EntityRepository::class),
        );

        $configuration->setEntityListenerResolver($this->resolver);

        $manager = new EntityManager(
            $connection,
            $configuration,
            $eventManager,
        );

        $manager = $this->decorateManager($settings, $manager);

        $this->registerListeners($settings, $manager);
        $this->registerSubscribers($settings, $manager);
        $this->registerFilters($settings, $configuration, $manager);
        $this->registerMappingTypes($settings, $manager);

        return $manager;
    }

    /** @param mixed[] $settings */
    private function setMetadataDriver(array $settings, Configuration $configuration): void
    {
        $metadata = $this->meta->driver(
            Arr::get($settings, 'meta'),
            $settings,
            false,
        );

        if ($metadata instanceof MetaData) {
            $configuration->setMetadataDriverImpl($metadata->resolve($settings));
            $configuration->setClassMetadataFactoryName($metadata->getClassMetadataFactoryName());
        } else {
            $configuration->setMetadataDriverImpl($metadata);
        }
    }

    /** @param array{middlewares?: class-string[]} $settings */
    private function setMiddlewares(array $settings, Configuration $configuration): void
    {
        $middlewares = [];

        foreach ($settings['middlewares'] ?? [] as $middlewareClass) {
            $middleware = $this->container->make($middlewareClass);

            if (! ($middleware instanceof Middleware)) {
                throw new LogicException($middlewareClass . 'does not implement ' . Middleware::class);
            }

            $middlewares[] = $middleware;
        }

        $configuration->setMiddlewares($middlewares);
    }

    /** @param mixed[] $settings */
    protected function registerListeners(array $settings, EntityManagerInterface $manager): void
    {
        if (! isset($settings['events']['listeners'])) {
            return;
        }

        foreach ($settings['events']['listeners'] as $event => $listener) {
            $this->registerListener($event, $listener, $manager);
        }
    }

    /** @param string|string[] $listener */
    private function registerListener(string $event, string|array $listener, EntityManagerInterface $manager): void
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
                'Listener ' . $listener . ' could not be resolved: ' . $e->getMessage(),
                0,
                $e,
            );
        }

        $manager->getEventManager()->addEventListener($event, $resolvedListener);
    }

    /** @param mixed[] $settings */
    protected function registerSubscribers(array $settings, EntityManagerInterface $manager): void
    {
        if (! isset($settings['events']['subscribers'])) {
            return;
        }

        foreach ($settings['events']['subscribers'] as $subscriber) {
            try {
                $resolvedSubscriber = $this->container->make($subscriber);
            } catch (ReflectionException $e) {
                throw new InvalidArgumentException('Listener ' . $subscriber . ' could not be resolved: ' . $e->getMessage());
            }

            $manager->getEventManager()->addEventSubscriber($resolvedSubscriber);
        }
    }

    /** @param mixed[] $settings */
    protected function registerFilters(
        array $settings,
        Configuration $configuration,
        EntityManagerInterface $manager,
    ): void {
        if (! isset($settings['filters'])) {
            return;
        }

        foreach ($settings['filters'] as $name => $filter) {
            $configuration->addFilter($name, $filter);
            $manager->getFilters()->enable($name);
        }
    }

    /** @param mixed[] $settings */
    protected function registerPaths(array $settings, Configuration $configuration): void
    {
        $configuration->getMetadataDriverImpl()->addPaths(
            Arr::get($settings, 'paths', []),
        );
    }

    /** @param mixed[] $settings */
    protected function setRepositoryFactory(array $settings, Configuration $configuration): void
    {
        if (! Arr::get($settings, 'repository_factory', false)) {
            return;
        }

        $configuration->setRepositoryFactory(
            $this->container->make(Arr::get($settings, 'repository_factory', false)),
        );
    }

    /** @param mixed[] $settings */
    protected function configureProxies(array $settings, Configuration $configuration): void
    {
        $configuration->setProxyDir(
            Arr::get($settings, 'proxies.path'),
        );

        $configuration->setAutoGenerateProxyClasses(
            Arr::get($settings, 'proxies.auto_generate', false),
        );

        $namespace = Arr::get($settings, 'proxies.namespace', false);
        if (! $namespace) {
            return;
        }

        $configuration->setProxyNamespace($namespace);
    }

    /** @param mixed[] $settings */
    protected function setNamingStrategy(array $settings, Configuration $configuration): void
    {
        $strategy = Arr::get($settings, 'naming_strategy', LaravelNamingStrategy::class);

        $configuration->setNamingStrategy(
            $this->container->make($strategy),
        );
    }

    /** @param mixed[] $settings */
    protected function setQuoteStrategy(array $settings, Configuration $configuration): void
    {
        $strategy = Arr::get($settings, 'quote_strategy', null);
        if ($strategy === null) {
            return;
        }

        $configuration->setQuoteStrategy(
            $this->container->make($strategy),
        );
    }

    protected function setCustomFunctions(Configuration $configuration): void
    {
        $configuration->setCustomDatetimeFunctions($this->config->get('doctrine.custom_datetime_functions'));
        $configuration->setCustomNumericFunctions($this->config->get('doctrine.custom_numeric_functions'));
        $configuration->setCustomStringFunctions($this->config->get('doctrine.custom_string_functions'));
    }

    protected function setCustomHydrationModes(Configuration $configuration): void
    {
        $hydratorConfig = $this->config->get('doctrine.custom_hydration_modes', []);
        foreach ($hydratorConfig as $hydrationModeName => $customHydratorClass) {
            $configuration->addCustomHydrationMode($hydrationModeName, $customHydratorClass);
        }
    }

    protected function setCacheSettings(Configuration $configuration): void
    {
        $configuration->setQueryCache($this->applyNamedCacheConfiguration('query'));
        $configuration->setResultCache($this->applyNamedCacheConfiguration('result'));
        $configuration->setMetadataCache($this->applyNamedCacheConfiguration('metadata'));

        $this->setSecondLevelCaching($configuration);
    }

    private function applyNamedCacheConfiguration(string $cacheName): CacheItemPoolInterface
    {
        $defaultDriver    = $this->config->get('doctrine.cache.default', 'array');
        $defaultNamespace = $this->config->get('doctrine.cache.namespace');

        $settings = $this->config->get('doctrine.cache.' . $cacheName, []);
        if (! isset($settings['namespace'])) {
            $settings['namespace'] = $defaultNamespace;
        }

        $driver = $settings['driver'] ?? $defaultDriver;

        return $this->cache->driver($driver, $settings);
    }

    protected function setSecondLevelCaching(Configuration $configuration): void
    {
        if (! $this->config->get('doctrine.cache.second_level', false)) {
            return;
        }

        $configuration->setSecondLevelCacheEnabled(true);

        $cacheConfig = $configuration->getSecondLevelCacheConfiguration();
        $cacheConfig->setCacheFactory(
            new DefaultCacheFactory(
                $cacheConfig->getRegionsConfiguration(),
                $this->cache->driver(),
            ),
        );
    }

    /** @param mixed[] $settings */
    protected function setCustomMappingDriverChain(array $settings, Configuration $configuration): void
    {
        $chain = new MappingDriverChain(
            $configuration->getMetadataDriverImpl(),
            'LaravelDoctrine',
        );

        $configuration->setMetadataDriverImpl(
            $chain,
        );
    }

    /** @param mixed[] $settings */
    protected function decorateManager(array $settings, EntityManagerInterface $manager): mixed
    {
        $decorator = Arr::get($settings, 'decorator', false);
        if ($decorator) {
            if (! class_exists($decorator)) {
                throw new InvalidArgumentException('EntityManagerDecorator ' . $decorator . ' does not exist');
            }

            $manager = new $decorator($manager);
        }

        return $manager;
    }

    /**
     * @param mixed[] $settings
     *
     * @return mixed[]
     */
    protected function getConnectionDriver(array $settings = []): array
    {
        $connection = Arr::get($settings, 'connection');
        $key        = 'database.connections.' . $connection;

        if (! $this->config->has($key)) {
            throw new InvalidArgumentException('Connection [' . $connection . '] has no configuration in [' . $key . ']');
        }

        return $this->config->get($key);
    }

    /**
     * @param mixed[] $settings
     *
     * @throws Exception If Database Type or Doctrine Type is not found.
     */
    protected function registerMappingTypes(array $settings, EntityManagerInterface $manager): void
    {
        foreach (Arr::get($settings, 'mapping_types', []) as $dbType => $doctrineType) {
            // Throw \Doctrine\DBAL\Exception if Doctrine Type is not found.
            $manager->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping($dbType, $doctrineType);
        }
    }

    /**
     * @param mixed[] $settings
     *
     * @throws BindingResolutionException
     */
    private function createEventManager(array $settings = []): EventManager|null
    {
        $customEventManager = Arr::get($settings, 'event_manager');

        if (! $customEventManager) {
            return null;
        }

        return $this->container->make($customEventManager);
    }

    /**
     * Check if master slave connection was being configured.
     *
     * @param mixed[] $driverConfig
     */
    private function isPrimaryReadReplicaConfigured(array $driverConfig): bool
    {
        // Setting read is mandatory for master/slave configuration. Setting write is optional.
        // But if write was set and read wasn't, it means configuration is incorrect and we must inform the user.
        return isset($driverConfig['read']) || isset($driverConfig['write']);
    }

    /**
     * Check if slave configuration is valid.
     *
     * @param mixed[] $driverConfig
     */
    private function hasValidPrimaryReadReplicaConfig(array $driverConfig): bool
    {
        if (! isset($driverConfig['read'])) {
            throw new InvalidArgumentException("Parameter 'read' must be set for read/write config.");
        }

        $slaves = $driverConfig['read'];

        if (! is_array($slaves) || in_array(false, array_map('is_array', $slaves))) {
            throw new InvalidArgumentException("Parameter 'read' must be an array containing multiple arrays.");
        }

        $key = array_search(0, array_map('count', $slaves));
        if ($key !== false) {
            throw new InvalidArgumentException('Parameter \'read\' config no. ' . $key . ' is empty.');
        }

        return true;
    }
}
