<?php

namespace LaravelDoctrine\ORM;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Proxy\Autoloader;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use Illuminate\Contracts\Container\Container;
use Illuminate\Notifications\ChannelManager;
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
use LaravelDoctrine\ORM\Console\ConvertMappingCommand;
use LaravelDoctrine\ORM\Console\DumpDatabaseCommand;
use LaravelDoctrine\ORM\Console\EnsureProductionSettingsCommand;
use LaravelDoctrine\ORM\Console\GenerateEntitiesCommand;
use LaravelDoctrine\ORM\Console\GenerateProxiesCommand;
use LaravelDoctrine\ORM\Console\InfoCommand;
use LaravelDoctrine\ORM\Console\MappingImportCommand;
use LaravelDoctrine\ORM\Console\SchemaCreateCommand;
use LaravelDoctrine\ORM\Console\SchemaDropCommand;
use LaravelDoctrine\ORM\Console\SchemaUpdateCommand;
use LaravelDoctrine\ORM\Console\SchemaValidateCommand;
use LaravelDoctrine\ORM\Exceptions\ExtensionNotFound;
use LaravelDoctrine\ORM\Extensions\ExtensionManager;
use LaravelDoctrine\ORM\Notifications\DoctrineChannel;
use LaravelDoctrine\ORM\Testing\Factory as EntityFactory;
use LaravelDoctrine\ORM\Validation\PresenceVerifierProvider;

class DoctrineServiceProvider extends ServiceProvider
{
    /**
     * Boot service provider.
     */
    public function boot()
    {
        $this->extendAuthManager();
        $this->extendNotificationChannel();

        if (!$this->isLumen()) {
            $this->publishes([
                $this->getConfigPath() => config_path('doctrine.php'),
            ], 'config');
        }

        $this->ensureValidatorIsUsable();
    }

    /**
     * Register the service provider.
     *
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
        $this->registerConsoleCommands();
        $this->registerCustomTypes();
        $this->registerEntityFactory();
        $this->registerProxyAutoloader();

        if ($this->shouldRegisterDoctrinePresenceValidator()) {
            $this->registerPresenceVerifierProvider();
        }
    }

    protected function ensureValidatorIsUsable()
    {
        if (!$this->isLumen()) {
            return;
        }

        if ($this->shouldRegisterDoctrinePresenceValidator()) {
            // due to weirdness the default presence verifier overrides one set by a service provider
            // so remove them so we can re add our implementation later
            unset($this->app->availableBindings['validator']);
            unset($this->app->availableBindings['Illuminate\Contracts\Validation\Factory']);
        } else {
            // resolve the db,
            // this makes `isset($this->app['db']) == true`
            // which is required to set the presence verifier
            // in the default ValidationServiceProvider implementation
            $this->app['db'];
        }
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
        // Bind the default Entity Manager
        $this->app->singleton('em', function ($app) {
            return $app->make('registry')->getManager();
        });

        $this->app->alias('em', EntityManager::class);
        $this->app->alias('em', EntityManagerInterface::class);
    }

    /**
     * Register the manager registry
     */
    protected function registerManagerRegistry()
    {
        $this->app->singleton('registry', function ($app) {
            $registry = new IlluminateRegistry($app, $app->make(EntityManagerFactory::class));

            // Add all managers into the registry
            foreach ($app->make('config')->get('doctrine.managers', []) as $manager => $settings) {
                $registry->addManager($manager, $settings);
            }

            return $registry;
        });

        // Once the registry get's resolved, we will call the resolve callbacks which were waiting for the registry
        $this->app->afterResolving('registry', function (ManagerRegistry $registry, Container $container) {
            $this->bootExtensionManager();

            BootChain::boot($registry);
        });

        $this->app->alias('registry', ManagerRegistry::class);
        $this->app->alias('registry', IlluminateRegistry::class);
    }

    /**
     * Register the connections
     *
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
            $manager = new ExtensionManager($app);

            // Register the extensions
            foreach ($this->app->make('config')->get('doctrine.extensions', []) as $extension) {
                if (!class_exists($extension)) {
                    throw new ExtensionNotFound("Extension {$extension} not found");
                }

                $manager->register($extension);
            }

            return $manager;
        });
    }

    /**
     * Register the deferred service provider for the validation presence verifier
     */
    protected function registerPresenceVerifierProvider()
    {
        if ($this->isLumen()) {
            $this->app->singleton('validator', function () {
                $this->app->register(PresenceVerifierProvider::class);

                return $this->app->make('validator');
            });
        } else {
            $this->app->register(PresenceVerifierProvider::class);
        }
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
        $this->app->make('auth')->provider('doctrine', function ($app, $config) {
            $entity = $config['model'];

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
     * Boots the extension manager at the appropriate time depending on if the app
     * is running as Laravel HTTP, Lumen HTTP or in a console environment
     */
    protected function bootExtensionManager()
    {
        $manager = $this->app->make(ExtensionManager::class);

        if ($manager->needsBooting()) {
            $this->app['events']->fire('doctrine.extensions.booting');

            $this->app->make(ExtensionManager::class)->boot(
                $this->app['registry']
            );

            $this->app['events']->fire('doctrine.extensions.booted');
        }
    }

    /**
     * Extend the database channel
     */
    public function extendNotificationChannel()
    {
        if ($this->app->bound(ChannelManager::class)) {
            $channel = $this->app['config']->get('doctrine.notifications.channel', 'database');

            $this->app->make(ChannelManager::class)->extend($channel, function ($app) {
                return new DoctrineChannel($app['registry']);
            });
        }
    }

    /**
     * Register the Entity factory instance in the container.
     *
     * @return void
     */
    protected function registerEntityFactory()
    {
        $this->app->singleton(FakerGenerator::class, function () {
            return FakerFactory::create();
        });

        $this->app->singleton(EntityFactory::class, function ($app) {
            return EntityFactory::construct(
                $app->make(FakerGenerator::class),
                $app->make('registry'),
                database_path('factories')
            );
        });
    }

    /**
     * Register proxy autoloader
     *
     * @return void
     */
    public function registerProxyAutoloader()
    {
        $this->app->afterResolving(ManagerRegistry::class, function (ManagerRegistry $registry) {
            /** @var EntityManagerInterface $manager */
            foreach ($registry->getManagers() as $manager) {
                Autoloader::register(
                    $manager->getConfiguration()->getProxyDir(),
                    $manager->getConfiguration()->getProxyNamespace()
                );
            }
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
            ConvertConfigCommand::class,
            MappingImportCommand::class,
            GenerateEntitiesCommand::class,
            ConvertMappingCommand::class,
            DumpDatabaseCommand::class
        ]);
    }

    /**
     * @return bool
     */
    protected function isLumen()
    {
        return str_contains($this->app->version(), 'Lumen');
    }

    /**
     * @return bool
     */
    protected function shouldRegisterDoctrinePresenceValidator()
    {
        return $this->app['config']->get('doctrine.doctrine_presence_verifier', true);
    }
}
