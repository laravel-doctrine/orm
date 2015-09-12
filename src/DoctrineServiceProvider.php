<?php

namespace LaravelDoctrine\ORM;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Illuminate\Auth\AuthManager;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use LaravelDoctrine\ORM\Auth\DoctrineUserProvider;
use LaravelDoctrine\ORM\Configuration\Cache\CacheManager;
use LaravelDoctrine\ORM\Configuration\Connections\ConnectionManager;
use LaravelDoctrine\ORM\Configuration\CustomTypeManager;
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
use LaravelDoctrine\ORM\Extensions\ExtensionManager;
use LaravelDoctrine\ORM\Validation\DoctrinePresenceVerifier;

class DoctrineServiceProvider extends ServiceProvider
{
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
        $this->app->make(ExtensionManager::class)->boot();

        $this->extendAuthManager();

        if (!$this->isLumen()) {
            $this->publishes([
                $this->getConfigPath() => config_path('doctrine.php'),
            ], 'config');
        }
    }

    /**
     * Register the service provider.
     * @return void
     */
    public function register()
    {
        $this->mergeConfig();
        $this->setupCache();
        $this->setupMetaData();
        $this->setupConnection();
        $this->registerManagerRegistry();
        $this->registerEntityManager();
        $this->registerClassMetaDataFactory();
        $this->registerExtensions();
        $this->registerPresenceVerifier();
        $this->registerConsoleCommands();
        $this->registerCustomTypes();
    }

    /**
     * Merge config
     */
    protected function mergeConfig()
    {
        $this->mergeConfigFrom(
            $this->getConfigPath(), 'doctrine'
        );

        if ($this->isLumen()) {
            $this->app->configure('cache');
            $this->app->configure('database');
            $this->app->configure('doctrine');
        }
    }

    /**
     * Setup the entity manager
     */
    protected function registerEntityManager()
    {
        $registry = $this->app->make('registry');

        // Add all managers into the registry
        foreach ($this->app->make('config')->get('doctrine.managers', []) as $manager => $settings) {
            $registry->addManager($manager, $settings);
            $registry->addConnection($manager);
        }

        // Bind the default Entity Manager
        $this->app->singleton('em', function () use ($registry) {
            return $registry->getManager();
        });

        $this->app->alias('em', EntityManager::class);
        $this->app->alias('em', EntityManagerInterface::class);
    }

    /**
     * Register the manager registry
     */
    protected function registerManagerRegistry()
    {
        $this->app->singleton('registry', IlluminateRegistry::class);
        $this->app->alias('registry', ManagerRegistry::class);
        $this->app->alias('registry', ManagerRegistry::class);
    }

    /**
     * Register the connections
     * @return array
     */
    protected function setupConnection()
    {
        $this->app->singleton(ConnectionManager::class);
    }

    /**
     * Register the meta data drivers
     */
    protected function setupMetaData()
    {
        $this->app->singleton(MetaDataManager::class);
    }

    /**
     * Register the cache drivers
     */
    protected function setupCache()
    {
        $this->app->singleton(CacheManager::class);
    }

    /**
     * Setup the Class metadata factory
     */
    protected function registerClassMetaDataFactory()
    {
        $this->app->singleton(ClassMetadataFactory::class, function ($app) {
            return $app->make('em')->getMetadataFactory();
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
                $this->app->make(ManagerRegistry::class)
            );

            // Register the extensions
            foreach ($this->app->make('config')->get('doctrine.extensions', []) as $extension) {
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
     * Register custom types
     */
    protected function registerCustomTypes()
    {
        (new CustomTypeManager)->addCustomTypes($this->app->make('config')->get('doctrine.custom_types', []));
    }

    /**
     * Extend the auth manager
     */
    protected function extendAuthManager()
    {
        $this->app->make('auth')->extend('doctrine', function ($app) {
            $entity = $app->make('config')->get('auth.model');

            $em = $app['registry']->getManagerForClass($entity);

            if (!$em) {
                throw new InvalidArgumentException("No EntityManager is set-up for {$entity}");
            }

            return new DoctrineUserProvider(
                $app['hash'],
                $em,
                $entity
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
     * Get the services provided by the provider.
     * @return string[]
     */
    public function provides()
    {
        return [
            'auth',
            'em',
            'registry',
            'validation.presence',
            AuthManager::class,
            EntityManager::class,
            DoctrineManager::class,
            ClassMetadataFactory::class,
            EntityManagerInterface::class,
            ExtensionManager::class,
            ManagerRegistry::class
        ];
    }

    /**
     * @return bool
     */
    protected function isLumen()
    {
        return !function_exists('config_path');
    }
}
