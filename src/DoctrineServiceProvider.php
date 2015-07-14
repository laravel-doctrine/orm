<?php

namespace Brouwers\LaravelDoctrine;

use Brouwers\LaravelDoctrine\Auth\DoctrineUserProvider;
use Brouwers\LaravelDoctrine\Configuration\Cache\CacheManager;
use Brouwers\LaravelDoctrine\Configuration\Connections\ConnectionManager;
use Brouwers\LaravelDoctrine\Configuration\LaravelNamingStrategy;
use Brouwers\LaravelDoctrine\Configuration\MetaData\MetaDataManager;
use Brouwers\LaravelDoctrine\Console\ClearMetadataCacheCommand;
use Brouwers\LaravelDoctrine\Console\ClearQueryCacheCommand;
use Brouwers\LaravelDoctrine\Console\ClearResultCacheCommand;
use Brouwers\LaravelDoctrine\Console\EnsureProductionSettingsCommand;
use Brouwers\LaravelDoctrine\Console\GenerateProxiesCommand;
use Brouwers\LaravelDoctrine\Console\InfoCommand;
use Brouwers\LaravelDoctrine\Console\SchemaCreateCommand;
use Brouwers\LaravelDoctrine\Console\SchemaDropCommand;
use Brouwers\LaravelDoctrine\Console\SchemaUpdateCommand;
use Brouwers\LaravelDoctrine\Console\SchemaValidateCommand;
use Brouwers\LaravelDoctrine\Exceptions\ExtensionNotFound;
use Brouwers\LaravelDoctrine\Extensions\ExtensionManager;
use Brouwers\LaravelDoctrine\Migrations\DoctrineMigrationRepository;
use Brouwers\LaravelDoctrine\Validation\DoctrinePresenceVerifier;
use DebugBar\Bridge\DoctrineCollector;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Proxy;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Cache\DefaultCacheFactory;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\ServiceProvider;

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
    public function boot()
    {
        $this->extendAuthManager();
        $this->extendMigrator();

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
        $this->registerExtensions();
        $this->registerCustomTypes();
        $this->registerPresenceVerifier();
        $this->registerConsoleCommands();
    }

    /**
     * Merge config
     */
    protected function mergeConfig()
    {
        $this->mergeConfigFrom(
            $this->getConfigPath(), 'doctrine'
        );

        $this->config = $this->app['config']['doctrine'];
    }

    /**
     * Setup the entity managers
     * @return array
     */
    protected function setUpEntityManagers()
    {
        $managers    = [];
        $connections = [];

        foreach ($this->config['managers'] as $manager => $settings) {
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
                $configuration->getMetadataDriverImpl()->addPaths(
                    array_get($settings, 'paths', [])
                );

                // Repository
                $configuration->setDefaultRepositoryClassName(
                    array_get($settings, 'repository', \Doctrine\ORM\EntityRepository::class)
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
                head($managers),
                $connections,
                $managers,
                head($connections),
                head($managers),
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
            $this->app['config']['database']['connections']
        );
    }

    /**
     * Register the meta data drivers
     */
    protected function setupMetaData()
    {
        MetaDataManager::registerDrivers(
            $this->config['meta']['drivers'],
            $this->config['dev']
        );

        MetaDataManager::resolved(function (Configuration $configuration) {

            // Debugbar
            if ($this->config['debugbar'] === true) {
                $debugStack = new DebugStack();
                $configuration->setSQLLogger($debugStack);
                $this->app['debugbar']->addCollector(
                    new DoctrineCollector($debugStack)
                );
            }

            $configuration->getMetadataDriverImpl()->addPaths([
                __DIR__ . '/Migrations',
                __DIR__ . '/Auth/Passwords'
            ]);

            // Automatically make table, column names, etc. like Laravel
            $configuration->setNamingStrategy(
                $this->app->make(LaravelNamingStrategy::class)
            );

            // Custom functions
            $configuration->setCustomDatetimeFunctions($this->config['custom_datetime_functions']);
            $configuration->setCustomNumericFunctions($this->config['custom_numeric_functions']);
            $configuration->setCustomStringFunctions($this->config['custom_string_functions']);

            // Second level caching
            if ($this->config['cache']['second_level']) {
                $configuration->setSecondLevelCacheEnabled(true);

                $cacheConfig = $configuration->getSecondLevelCacheConfiguration();
                $cacheConfig->setCacheFactory(
                    new DefaultCacheFactory(
                        $cacheConfig->getRegionsConfiguration(),
                        CacheManager::resolve(
                            $this->config['cache']['default']
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
            $this->app['config']['cache']['stores']
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
     * Register doctrine extensions
     */
    protected function registerExtensions()
    {
        // Bind extension manager as singleton,
        // so user can call it and add own extensions
        $this->app->singleton(ExtensionManager::class, function ($app) {

            $manager = new ExtensionManager(
                $this->app[ManagerRegistry::class],
                $this->app[Dispatcher::class]
            );

            if ($this->config['gedmo_extensions']['enabled']) {
                $manager->enableGedmoExtensions(
                    $this->config['meta']['namespaces'],
                    $this->config['gedmo_extensions']['all_mappings']
                );
            }

            // Register the extensions
            foreach ($this->config['extensions'] as $extension) {
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
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function registerCustomTypes()
    {
        foreach ($this->config['custom_types'] as $name => $class) {
            if (!Type::hasType($name)) {
                Type::addType($name, $class);
            } else {
                Type::overrideType($name, $class);
            }
        }
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
            GenerateProxiesCommand::class
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
     * Extend the migrator
     */
    protected function extendMigrator()
    {
        $this->app->bind('migration.repository', function ($app) {
            return $app->make(DoctrineMigrationRepository::class);
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
            'em',
            'validation.presence',
            'migration.repository',
            AuthManager::class,
            EntityManager::class,
            ClassMetadataFactory::class,
            EntityManagerInterface::class,
            ExtensionManager::class,
            ManagerRegistry::class
        ];
    }
}
