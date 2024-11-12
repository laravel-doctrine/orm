<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\ORM\Proxy\Autoloader;
use Doctrine\Persistence\ManagerRegistry;
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
use LaravelDoctrine\ORM\Console\DumpDatabaseCommand;
use LaravelDoctrine\ORM\Console\GenerateProxiesCommand;
use LaravelDoctrine\ORM\Console\InfoCommand;
use LaravelDoctrine\ORM\Console\SchemaCreateCommand;
use LaravelDoctrine\ORM\Console\SchemaDropCommand;
use LaravelDoctrine\ORM\Console\SchemaUpdateCommand;
use LaravelDoctrine\ORM\Console\SchemaValidateCommand;
use LaravelDoctrine\ORM\Exceptions\ExtensionNotFound;
use LaravelDoctrine\ORM\Extensions\ExtensionManager;
use LaravelDoctrine\ORM\Notifications\DoctrineChannel;
use LaravelDoctrine\ORM\Validation\PresenceVerifierProvider;

use function assert;
use function class_exists;
use function config;
use function config_path;

class DoctrineServiceProvider extends ServiceProvider
{
    /**
     * Boot service provider.
     */
    public function boot(): void
    {
        $this->extendAuthManager();
        $this->extendNotificationChannel();

        $this->publishes([
            $this->getConfigPath() => config_path('doctrine.php'),
        ], 'config');
    }

    /**
     * Register the service provider.
     */
    public function register(): void
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
        $this->registerProxyAutoloader();

        if (! $this->shouldRegisterDoctrinePresenceValidator()) {
            return;
        }

        $this->registerPresenceVerifierProvider();
    }

    /**
     * Merge config
     */
    protected function mergeConfig(): void
    {
        $this->mergeConfigFrom(
            $this->getConfigPath(),
            'doctrine',
        );
    }

    /**
     * Setup the entity manager
     */
    protected function registerEntityManager(): void
    {
        // Bind the default Entity Manager
        $this->app->singleton('em', static function ($app) {
            return $app->make('registry')->getManager();
        });

        $this->app->alias('em', EntityManager::class);
        $this->app->alias('em', EntityManagerInterface::class);
    }

    /**
     * Register the manager registry
     */
    protected function registerManagerRegistry(): void
    {
        $this->app->singleton('registry', static function ($app) {
            $registry = new IlluminateRegistry($app, $app->make(EntityManagerFactory::class));

            // Add all managers into the registry
            foreach ($app->make('config')->get('doctrine.managers', []) as $manager => $settings) {
                $registry->addManager($manager, $settings);
            }

            return $registry;
        });

        // Once the registry get's resolved, we will call the resolve callbacks which were waiting for the registry
        $this->app->afterResolving('registry', function (ManagerRegistry $registry, Container $container): void {
            $this->bootExtensionManager();

            BootChain::boot($registry);
        });

        $this->app->alias('registry', ManagerRegistry::class);
        $this->app->alias('registry', IlluminateRegistry::class);

        // This namespace has been deprecated in doctrine/persistence and we have
        // stopped referring to it. Alias is necessary to let other use it until
        // its removed.
        $this->app->alias('registry', ManagerRegistry::class);
    }

    /**
     * Register the connections
     */
    protected function setupConnection(): void
    {
        $this->app->singleton(ConnectionManager::class);
    }

    /**
     * Register the meta data drivers
     */
    protected function setupMetaData(): void
    {
        $this->app->singleton(MetaDataManager::class);
    }

    /**
     * Register the cache drivers
     */
    protected function setupCache(): void
    {
        $this->app->singleton(CacheManager::class);
    }

    /**
     * Setup the Class metadata factory
     */
    protected function registerClassMetaDataFactory(): void
    {
        $this->app->singleton(ClassMetadataFactory::class, static function ($app) {
            return $app->make('em')->getMetadataFactory();
        });
    }

    /**
     * Register doctrine extensions
     */
    protected function registerExtensions(): void
    {
        // Bind extension manager as singleton,
        // so user can call it and add own extensions
        $this->app->singleton(ExtensionManager::class, function ($app) {
            $manager = new ExtensionManager($app);

            // Register the extensions
            foreach ($this->app->make('config')->get('doctrine.extensions', []) as $extension) {
                if (! class_exists($extension)) {
                    throw new ExtensionNotFound('Extension ' . $extension . ' not found');
                }

                $manager->register($extension);
            }

            return $manager;
        });
    }

    /**
     * Register the deferred service provider for the validation presence verifier
     */
    protected function registerPresenceVerifierProvider(): void
    {
        $this->app->register(PresenceVerifierProvider::class);
    }

    /**
     * Register custom types
     */
    protected function registerCustomTypes(): void
    {
        (new CustomTypeManager())->addCustomTypes($this->app->make('config')->get('doctrine.custom_types', []));
    }

    /**
     * Extend the auth manager
     */
    protected function extendAuthManager(): void
    {
        if (! $this->app->bound('auth')) {
            return;
        }

        $this->app->make('auth')->provider('doctrine', static function ($app, $config) {
            $entity = $config['model'];

            $em = $app['registry']->getManagerForClass($entity);

            if (! $em) {
                throw new InvalidArgumentException('No EntityManager is set-up for ' . $entity);
            }

            return new DoctrineUserProvider(
                $app['hash'],
                $em,
                $entity,
            );
        });
    }

    /**
     * Boots the extension manager at the appropriate time depending on if the app
     * is running as Laravel HTTP or in a console environment
     */
    protected function bootExtensionManager(): void
    {
        $manager = $this->app->make(ExtensionManager::class);

        if (! $manager->needsBooting()) {
            return;
        }

        $this->app['events']->dispatch('doctrine.extensions.booting');

        $this->app->make(ExtensionManager::class)->boot(
            $this->app['registry'],
        );

        $this->app['events']->dispatch('doctrine.extensions.booted');
    }

    /**
     * Extend the database channel
     */
    public function extendNotificationChannel(): void
    {
        if (! $this->app->bound(ChannelManager::class)) {
            return;
        }

        $channel = $this->app['config']->get('doctrine.notifications.channel', 'database');

        $this->app->make(ChannelManager::class)->extend($channel, static function ($app) {
            return new DoctrineChannel($app['registry']);
        });
    }

    /**
     * Register proxy autoloader
     */
    public function registerProxyAutoloader(): void
    {
        $this->app->afterResolving(ManagerRegistry::class, static function (ManagerRegistry $registry): void {
            foreach ($registry->getManagers() as $manager) {
                assert($manager instanceof EntityManagerInterface);
                Autoloader::register(
                    $manager->getConfiguration()->getProxyDir(),
                    $manager->getConfiguration()->getProxyNamespace(),
                );
            }
        });
    }

    protected function getConfigPath(): string
    {
        return __DIR__ . '/../config/doctrine.php';
    }

    /**
     * Register console commands
     */
    protected function registerConsoleCommands(): void
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
            GenerateProxiesCommand::class,
            DumpDatabaseCommand::class,
        ]);
    }

    protected function shouldRegisterDoctrinePresenceValidator(): bool
    {
        return config('doctrine.doctrine_presence_verifier', true);
    }
}
