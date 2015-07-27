<?php

namespace LaravelDoctrine\ORM;

use DebugBar\Bridge\DoctrineCollector;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Proxy;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Cache\DefaultCacheFactory;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\ServiceProvider;
use LaravelDoctrine\ORM\Auth\DoctrineUserProvider;
use LaravelDoctrine\ORM\Configuration\Cache\CacheManager;
use LaravelDoctrine\ORM\Configuration\Connections\ConnectionManager;
use LaravelDoctrine\ORM\Configuration\CustomTypeManager;
use LaravelDoctrine\ORM\Configuration\LaravelNamingStrategy;
use LaravelDoctrine\ORM\Configuration\MetaData\MetaDataManager;
use LaravelDoctrine\ORM\Console\ClearMetadataCacheCommand;
use LaravelDoctrine\ORM\Console\ClearQueryCacheCommand;
use LaravelDoctrine\ORM\Console\ClearResultCacheCommand;
use LaravelDoctrine\ORM\Console\ConvertConfigCommand;
use LaravelDoctrine\ORM\Console\EnsureProductionSettingsCommand;
use LaravelDoctrine\ORM\Console\GenerateProxiesCommand;
use LaravelDoctrine\ORM\Console\InfoCommand;
use LaravelDoctrine\ORM\Console\SchemaCreateCommand;
use LaravelDoctrine\ORM\Console\SchemaDropCommand;
use LaravelDoctrine\ORM\Console\SchemaUpdateCommand;
use LaravelDoctrine\ORM\Console\SchemaValidateCommand;
use LaravelDoctrine\ORM\Exceptions\ExtensionNotFound;
use LaravelDoctrine\ORM\Extensions\DriverChain;
use LaravelDoctrine\ORM\Extensions\ExtensionManager;
use LaravelDoctrine\ORM\Validation\DoctrinePresenceVerifier;

class DoctrineServiceProvider extends ServiceProvider
{
    /**
     * @var array
     */
    protected $config;

    /**
     * Indicates if loading of the provider is deferred.
     * @var bool
     */
    protected $defer = true;

    /**
     * Boot service provider.
     */
    public function boot(CustomTypeManager $typeManager)
    {
        $typeManager->addCustomTypes(config('doctrine.custom_types', []));

        // Boot the extension manager
        $this->app->make(ExtensionManager::class)->boot();

        $this->publishes([
            $this->getConfigPath() => config_path('doctrine.php'),
        ], 'config');
    }

    /**
     * Register the service provider.
     * @return void
     */
    public function register()
    {
        $this->setupCache();
        $this->mergeConfig();
        $this->setupMetaData();
        $this->setupConnection();
        $this->registerManagerRegistry();
        $this->registerEntityManager();
        $this->registerClassMetaDataFactory();
        $this->registerDriverChain();
        $this->registerExtensions();
        $this->registerPresenceVerifier();
        $this->registerConsoleCommands();
        $this->extendAuthManager();
    }

    /**
     * Merge config
     */
    protected function mergeConfig()
    {
        $this->mergeConfigFrom(
            $this->getConfigPath(), 'doctrine'
        );
    }

    /**
     * Setup the entity managers
     * @return array
     */
    protected function setUpEntityManagers()
    {
        $managers    = [];
        $connections = [];

        foreach ($this->app->config->get('doctrine.managers', []) as $manager => $settings) {
            $managerName    = IlluminateRegistry::getManagerNamePrefix() . $manager;
            $connectionName = IlluminateRegistry::getConnectionNamePrefix() . $manager;

            // Bind manager
            $this->app->singleton($managerName, function () use ($settings) {

                $manager = EntityManager::create(
                    ConnectionManager::resolve(array_get($settings, 'connection')),
                    MetaDataManager::resolve(array_get($settings, 'meta'))
                );

                $configuration = $manager->getConfiguration();

                // Listeners
                if (isset($settings['events']['listeners'])) {
                    foreach ($settings['events']['listeners'] as $event => $listener) {
                        $manager->getEventManager()->addEventListener($event, $listener);
                    }
                }

                // Subscribers
                if (isset($settings['events']['subscribers'])) {
                    foreach ($settings['events']['subscribers'] as $subscriber) {
                        $manager->getEventManager()->addEventSubscriber($subscriber);
                    }
                }

                // Filters
                if (isset($settings['filters'])) {
                    foreach ($settings['filters'] as $name => $filter) {
                        $configuration->getMetadataDriverImpl()->addFilter($name, $filter);
                        $manager->getFilters()->enable($name);
                    }
                }

                // Paths
                $paths = array_get($settings, 'paths', []);
                $meta = $configuration->getMetadataDriverImpl();

                if (method_exists($meta, 'addPaths')) {
                    $meta->addPaths($paths);
                } elseif (method_exists($meta, 'getLocator')) {
                    $meta->getLocator()->addPaths($paths);
                }

                // Repository
                $configuration->setDefaultRepositoryClassName(
                    array_get($settings, 'repository', EntityRepository::class)
                );

                // Proxies
                $configuration->setProxyDir(
                    array_get($settings, 'proxies.path', storage_path('proxies'))
                );

                $configuration->setAutoGenerateProxyClasses(
                    array_get($settings, 'proxies.auto_generate', false)
                );

                if ($namespace = array_get($settings, 'proxies.namespace', false)) {
                    $configuration->setProxyNamespace($namespace);
                }

                return $manager;
            });

            // Bind connection
            $this->app->singleton($connectionName, function ($app) use ($manager) {
                $app->make(IlluminateRegistry::getManagerNamePrefix() . $manager)->getConnection();
            });

            $managers[$manager]    = $manager;
            $connections[$manager] = $manager;
        }

        return [$managers, $connections];
    }

    /**
     * Setup the entity manager
     */
    protected function registerEntityManager()
    {
        // Bind the default Entity Manager
        $this->app->singleton('em', function ($app) {
            return $app->make(ManagerRegistry::class)->getManager();
        });

        $this->app->alias('em', EntityManager::class);
        $this->app->alias('em', EntityManagerInterface::class);
    }

    /**
     * Register the manager registry
     */
    protected function registerManagerRegistry()
    {
        $this->app->singleton(IlluminateRegistry::class, function ($app) {

            list($managers, $connections) = $this->setUpEntityManagers();

            return new IlluminateRegistry(
                isset($managers['default']) ? $managers['default'] : head($managers),
                $connections,
                $managers,
                isset($connections['default']) ? $connections['default'] : head($connections),
                isset($managers['default']) ? $managers['default'] : head($managers),
                Proxy::class,
                $app
            );
        });

        $this->app->alias(IlluminateRegistry::class, ManagerRegistry::class);
    }

    /**
     * Register the connections
     * @return array
     */
    protected function setupConnection()
    {
        ConnectionManager::registerConnections(
            $this->app->config->get('database.connections', [])
        );
    }

    /**
     * Register the meta data drivers
     */
    protected function setupMetaData()
    {
        MetaDataManager::registerDrivers(
            $this->app->config->get('doctrine.meta.drivers', []),
            $this->app->config->get('doctrine.dev', false)
        );

        MetaDataManager::resolved(function (Configuration $configuration) {

            // Debugbar
            if ($this->app->config->get('doctrine.debugbar', false) === true) {
                $debugStack = new DebugStack();
                $configuration->setSQLLogger($debugStack);
                $this->app['debugbar']->addCollector(
                    new DoctrineCollector($debugStack)
                );
            }

            // Automatically make table, column names, etc. like Laravel
            $configuration->setNamingStrategy(
                $this->app->make(LaravelNamingStrategy::class)
            );

            // Custom functions
            $configuration->setCustomDatetimeFunctions($this->app->config->get('doctrine.custom_datetime_functions'));
            $configuration->setCustomNumericFunctions($this->app->config->get('doctrine.custom_numeric_functions'));
            $configuration->setCustomStringFunctions($this->app->config->get('doctrine.custom_string_functions'));

            // Second level caching
            if ($this->app->config->get('cache.second_level', false)) {
                $configuration->setSecondLevelCacheEnabled(true);

                $cacheConfig = $configuration->getSecondLevelCacheConfiguration();
                $cacheConfig->setCacheFactory(
                    new DefaultCacheFactory(
                        $cacheConfig->getRegionsConfiguration(),
                        CacheManager::resolve(
                            $this->app->config->get('cache.default')
                        )
                    )
                );
            }
        });
    }

    /**
     * Register the cache drivers
     */
    protected function setupCache()
    {
        CacheManager::registerDrivers(
            $this->app->config->get('cache.stores', [])
        );
    }

    /**
     * Setup the Class metadata factory
     */
    protected function registerClassMetaDataFactory()
    {
        $this->app->singleton(ClassMetadataFactory::class, function ($app) {
            return $app['em']->getMetadataFactory();
        });
    }

    /**
     * Register the driver chain
     */
    protected function registerDriverChain()
    {
        $this->app->singleton(DriverChain::class, function ($app) {

            $configuration = $app['em']->getConfiguration();

            $chain = new DriverChain(
                $configuration->getMetadataDriverImpl()
            );

            // Register namespaces
            $namespaces = array_merge($app->config->get('doctrine.meta.namespaces', ['App']), ['LaravelDoctrine']);
            foreach ($namespaces as $namespace) {
                $chain->addNamespace($namespace);
            }

            // Register default paths
            $chain->addPaths(array_merge(
                $app->config->get('doctrine.meta.paths', []),
                [__DIR__ . '/Auth/Passwords']
            ));

            $configuration->setMetadataDriverImpl($chain->getChain());

            return $chain;
        });
    }

    /**
     * Register doctrine extensions
     */
    protected function registerExtensions()
    {
        // Bind extension manager as singleton,
        // so user can call it and add own extensions
        $this->app->singleton(ExtensionManager::class, function ($app) {

            $manager = new ExtensionManager(
                $this->app[ManagerRegistry::class],
                $this->app[DriverChain::class]
            );

            // Register the extensions
            foreach ($this->app->config->get('doctrine.extensions', []) as $extension) {
                if (!class_exists($extension)) {
                    throw new ExtensionNotFound("Extension {$extension} not found");
                }

                $manager->register(
                    $app->make($extension)
                );
            }

            return $manager;
        });
    }


    /**
     * Register the validation presence verifier
     */
    protected function registerPresenceVerifier()
    {
        $this->app->singleton('validation.presence', DoctrinePresenceVerifier::class);
    }

    /**
     * Register console commands
     */
    protected function registerConsoleCommands()
    {
        $this->commands([
            InfoCommand::class,
            SchemaCreateCommand::class,
            SchemaUpdateCommand::class,
            SchemaDropCommand::class,
            SchemaValidateCommand::class,
            ClearMetadataCacheCommand::class,
            ClearResultCacheCommand::class,
            ClearQueryCacheCommand::class,
            EnsureProductionSettingsCommand::class,
            GenerateProxiesCommand::class,
            ConvertConfigCommand::class
        ]);
    }

    /**
     * Extend the auth manager
     */
    protected function extendAuthManager()
    {
        $this->app[AuthManager::class]->extend('doctrine', function ($app) {
            return new DoctrineUserProvider(
                $app[Hasher::class],
                $app['em'],
                $app['config']['auth.model']
            );
        });
    }

    /**
     * @return string
     */
    protected function getConfigPath()
    {
        return __DIR__ . '/../config/doctrine.php';
    }

    /**
     * Get the services provided by the provider.
     * @return string[]
     */
    public function provides()
    {
        return [
            'auth',
            'em',
            'validation.presence',
            'migration.repository',
            DriverChain::class,
            AuthManager::class,
            EntityManager::class,
            ClassMetadataFactory::class,
            EntityManagerInterface::class,
            ExtensionManager::class,
            ManagerRegistry::class
        ];
    }
}
