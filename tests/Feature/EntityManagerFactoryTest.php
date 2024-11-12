<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Feature;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Connections\PrimaryReadReplicaConnection;
use Doctrine\DBAL\Schema\DefaultSchemaManagerFactory;
use Doctrine\ORM\Cache;
use Doctrine\ORM\Cache\CacheConfiguration;
use Doctrine\ORM\Cache\CacheFactory;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Decorator\EntityManagerDecorator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\FilterCollection;
use Doctrine\ORM\Repository\RepositoryFactory;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Illuminate\Contracts\Cache\Factory;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;
use LaravelDoctrine\ORM\Configuration\Cache\CacheManager;
use LaravelDoctrine\ORM\Configuration\Connections\ConnectionManager;
use LaravelDoctrine\ORM\Configuration\LaravelNamingStrategy;
use LaravelDoctrine\ORM\Configuration\MetaData\MetaDataManager;
use LaravelDoctrine\ORM\EntityManagerFactory;
use LaravelDoctrine\ORM\ORMSetupResolver;
use LaravelDoctrine\ORM\Resolvers\EntityListenerResolver;
use LaravelDoctrine\ORM\Testing\ConfigRepository;
use LaravelDoctrineTest\ORM\Assets\AnotherListenerStub;
use LaravelDoctrineTest\ORM\Assets\Decorator;
use LaravelDoctrineTest\ORM\Assets\FakeConnection;
use LaravelDoctrineTest\ORM\Assets\FakeEventManager;
use LaravelDoctrineTest\ORM\Assets\FilterStub;
use LaravelDoctrineTest\ORM\Assets\ListenerStub;
use LaravelDoctrineTest\ORM\Assets\SubscriberStub;
use LaravelDoctrineTest\ORM\TestCase;
use Mockery as m;
use Mockery\Mock;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Cache\CacheItemPoolInterface;
use ReflectionException;
use ReflectionObject;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

use function array_key_exists;
use function count;
use function rmdir;

class EntityManagerFactoryTest extends TestCase
{
    protected CacheManager $cache;
    protected Repository|Mock $config;
    protected ConnectionManager $connection;
    protected MetaDataManager $meta;
    protected Container $container;
    protected EntityManagerFactory $factory;
    protected Configuration|Mock $configuration;
    protected EntityListenerResolver $listenerResolver;
    protected MappingDriver $mappingDriver;

    protected mixed $setup;

    /** @var string[]  */
    protected array $caches = ['query', 'result', 'metadata'];

    /** @var mixed[]  */
    protected array $settings = [
        'meta'       => 'xml',
        'connection' => 'mysql',
        'paths'      => ['Entities'],
        'proxies'    => [
            'path'          => 'dir',
            'auto_generate' => false,
            'namespace'     => 'namespace',
        ],
        'repository' => 'Repo',
    ];

    protected function setUp(): void
    {
        $this->mockApp();
        $this->mockMeta();
        $this->mockConnection();
        $this->mockCache();
        $this->mockResolver();
        $this->mockConfig();

        $this->setup = m::mock(ORMSetupResolver::class);
        $this->setup->shouldReceive('createConfiguration')->once()->andReturn($this->configuration);

        $this->factory = new EntityManagerFactory(
            $this->container,
            $this->setup,
            $this->meta,
            $this->connection,
            $this->cache,
            $this->config,
            $this->listenerResolver,
        );

        parent::setUp();
    }

    protected function assertEntityManager(EntityManagerInterface $manager): void
    {
        $this->assertInstanceOf(EntityManagerInterface::class, $manager);
        $this->assertInstanceOf(Connection::class, $manager->getConnection());
        $this->assertInstanceOf(Configuration::class, $manager->getConfiguration());
    }

    public function testEntityManagerGetsInstantiatedCorrectly(): void
    {
        $this->disableDebugbar();
        $this->disableSecondLevelCaching();
        $this->disableCustomCacheNamespace();
        $this->disableCustomFunctions();
        $this->enableLaravelNamingStrategy();

        $manager = $this->factory->create($this->settings);

        $this->assertEntityManager($manager);
    }

    public function testDebugbarLoggerCanBeEnabled(): void
    {
        $this->disableSecondLevelCaching();
        $this->disableCustomCacheNamespace();
        $this->disableCustomFunctions();
        $this->enableLaravelNamingStrategy();

        $manager = $this->factory->create($this->settings);

        $this->assertEntityManager($manager);
    }

    public function testCustomFunctionsCanBeEnabled(): void
    {
        $this->disableDebugbar();
        $this->disableSecondLevelCaching();
        $this->disableCustomCacheNamespace();
        $this->enableLaravelNamingStrategy();

        $this->configuration->shouldReceive('setCustomDatetimeFunctions')
                            ->once()->with(['datetime']);
        $this->configuration->shouldReceive('setCustomNumericFunctions')
                            ->once()->with(['numeric']);
        $this->configuration->shouldReceive('setCustomStringFunctions')
                            ->once()->with(['string']);

        $manager = $this->factory->create($this->settings);

        $this->assertEntityManager($manager);
    }

    public function testSecondLevelCachingCanBeEnabled(): void
    {
        $this->disableDebugbar();
        $this->disableCustomFunctions();
        $this->enableLaravelNamingStrategy();
        $this->disableCustomCacheNamespace();

        $this->config->shouldReceive('get')
                     ->with('doctrine.cache.second_level', false)->once()
                     ->andReturn(false);

        $cacheConfig = m::mock(CacheConfiguration::class);

        $cacheFactory = m::mock(CacheFactory::class);
        $cacheFactory->shouldReceive('createCache')->atLeast()->once();
        $cacheConfig->shouldReceive('getCacheFactory')
                    ->atLeast()->once()
                    ->andReturn($cacheFactory);

        $this->configuration->shouldReceive('getSecondLevelCacheConfiguration')
                            ->atLeast()->once()->andReturn($cacheConfig);

        $cacheImpl = m::mock(Cache::class);

        $this->configuration->shouldReceive('isSecondLevelCacheEnabled')
                            ->atLeast()->once()
                            ->andReturn(true);

        $manager = $this->factory->create($this->settings);

        $this->assertEntityManager($manager);
    }

    public function testCustomCacheNamespaceCanBeSet(): void
    {
        $this->disableDebugbar();
        $this->disableCustomFunctions();
        $this->enableLaravelNamingStrategy();
        $this->disableSecondLevelCaching();

        $this->config->shouldReceive('get')
                     ->with('doctrine.cache.namespace')
                     ->andReturn('namespace');

        foreach ($this->caches as $cache) {
            $this->config->shouldNotReceive('get')
                         ->with('doctrine.cache.' . $cache, [])
                         ->once()
                         ->andReturn(['namespace' => $cache])->byDefault();
        }

        $cache = m::mock(Cache::class);

        $this->cache->shouldReceive('driver')->andReturn($cache);

        $cache->shouldReceive('setNamespace')->with('namespace');

        $manager = $this->factory->create($this->settings);

        $this->assertEntityManager($manager);
    }

    public function testCanRegisterPaths(): void
    {
        $this->disableDebugbar();
        $this->disableSecondLevelCaching();
        $this->disableCustomCacheNamespace();
        $this->disableCustomFunctions();
        $this->enableLaravelNamingStrategy();

        $manager = $this->factory->create($this->settings);

        $this->assertEntityManager($manager);
    }

    public function testCanSetFilters(): void
    {
        $this->disableDebugbar();
        $this->disableSecondLevelCaching();
        $this->disableCustomCacheNamespace();
        $this->disableCustomFunctions();
        $this->enableLaravelNamingStrategy();

        $this->settings['filters'] = [
            'name' => FilterStub::class,
        ];

        $this->configuration->shouldReceive('addFilter')
                            ->with('name', FilterStub::class)
                            ->once();

        $this->configuration->shouldReceive('getFilterClassName')
                            ->atLeast()->once()->andReturn(FilterStub::class);

        $manager = $this->factory->create($this->settings);

        $this->assertEntityManager($manager);
        $this->assertInstanceOf(FilterCollection::class, $manager->getFilters());
        $this->assertTrue(array_key_exists('name', $manager->getFilters()->getEnabledFilters()));
    }

    public function testCanSetListeners(): void
    {
        $this->container->shouldReceive('make')
                ->with(ListenerStub::class)
                ->once()
                ->andReturn(new ListenerStub());

        $this->disableDebugbar();
        $this->disableSecondLevelCaching();
        $this->disableCustomCacheNamespace();
        $this->disableCustomFunctions();
        $this->enableLaravelNamingStrategy();

        $this->settings['events']['listeners'] = [
            'name' => ListenerStub::class,
        ];

        $manager = $this->factory->create($this->settings);

        $this->assertEntityManager($manager);
        $this->assertCount(1, $manager->getEventManager()->getAllListeners());
        $this->assertTrue(array_key_exists('name', $manager->getEventManager()->getAllListeners()));
    }

    public function testCanSetMultipleListeners(): void
    {
        $this->container->shouldReceive('make')
                        ->with(ListenerStub::class)
                        ->once()
                        ->andReturn(new ListenerStub())
                        ->shouldReceive('make')
                        ->with(AnotherListenerStub::class)
                        ->once()
                        ->andReturn(new AnotherListenerStub());

        $this->disableDebugbar();
        $this->disableSecondLevelCaching();
        $this->disableCustomCacheNamespace();
        $this->disableCustomFunctions();
        $this->enableLaravelNamingStrategy();

        $this->settings['events']['listeners'] = [
            'name' => [
                ListenerStub::class,
                AnotherListenerStub::class,
            ],
        ];

        $manager = $this->factory->create($this->settings);

        $this->assertEntityManager($manager);
        $this->assertCount(1, $manager->getEventManager()->getAllListeners());
        $this->assertTrue(array_key_exists('name', $manager->getEventManager()->getAllListeners()));
        $this->assertCount(2, $manager->getEventManager()->getListeners('name'));
    }

    public function testSettingNonExistentListenerThrowsException(): void
    {
        $reflectionException = new ReflectionException();

        $this->container->shouldReceive('make')
                ->with('ClassDoesNotExist')
                ->once()
                ->andThrow($reflectionException);

        $this->expectException(InvalidArgumentException::class);

        $this->disableDebugbar();
        $this->disableSecondLevelCaching();
        $this->disableCustomCacheNamespace();
        $this->disableCustomFunctions();
        $this->enableLaravelNamingStrategy();

        $this->settings['events']['listeners'] = ['name' => 'ClassDoesNotExist'];

        $this->factory->create($this->settings);
    }

    public function testCanSetSubscribers(): void
    {
        $this->container->shouldReceive('make')
                ->with(SubscriberStub::class)
                ->once()
                ->andReturn(new SubscriberStub());

        $this->disableDebugbar();
        $this->disableSecondLevelCaching();
        $this->disableCustomCacheNamespace();
        $this->disableCustomFunctions();
        $this->enableLaravelNamingStrategy();

        $this->settings['events']['subscribers'] = [
            'name' => SubscriberStub::class,
        ];

        $manager = $this->factory->create($this->settings);

        $this->assertEntityManager($manager);
        $this->assertCount(1, $manager->getEventManager()->getAllListeners());
        $this->assertTrue(array_key_exists('onFlush', $manager->getEventManager()->getAllListeners()));
    }

    public function testSettingNonExistentSubscriberThrowsException(): void
    {
        $reflectionException = new ReflectionException();

        $this->container->shouldReceive('make')
                        ->with('ClassDoesNotExist')
                        ->once()
                        ->andThrow($reflectionException);

        $this->expectException(InvalidArgumentException::class);

        $this->disableDebugbar();
        $this->disableSecondLevelCaching();
        $this->disableCustomCacheNamespace();
        $this->disableCustomFunctions();
        $this->enableLaravelNamingStrategy();

        $this->settings['events']['subscribers'] = ['name' => 'ClassDoesNotExist'];

        $this->factory->create($this->settings);
    }

    public function testCanSetCustomNamingStrategy(): void
    {
        $this->disableDebugbar();
        $this->disableSecondLevelCaching();
        $this->disableCustomCacheNamespace();
        $this->disableCustomFunctions();

        $this->settings['naming_strategy'] = 'Doctrine\ORM\Mapping\DefaultNamingStrategy';

        $strategy = m::mock('Doctrine\ORM\Mapping\DefaultNamingStrategy');

        $this->container->shouldReceive('make')
                        ->with('Doctrine\ORM\Mapping\DefaultNamingStrategy')
                        ->once()->andReturn($strategy);

        $this->configuration->shouldReceive('setNamingStrategy')->once()->with($strategy);

        $manager = $this->factory->create($this->settings);

        $this->assertEntityManager($manager);
    }

    public function testCanSetCustomQuoteStrategy(): void
    {
        $this->disableDebugbar();
        $this->disableSecondLevelCaching();
        $this->disableCustomCacheNamespace();
        $this->disableCustomFunctions();
        $this->enableLaravelNamingStrategy();

        $this->settings['quote_strategy'] = 'Doctrine\ORM\Mapping\AnsiQuoteStrategy';

        $strategy = m::mock('Doctrine\ORM\Mapping\AnsiQuoteStrategy');

        $this->container->shouldReceive('make')
            ->with('Doctrine\ORM\Mapping\AnsiQuoteStrategy')
            ->once()->andReturn($strategy);

        $this->configuration->shouldReceive('setQuoteStrategy')->once()->with($strategy);

        $manager = $this->factory->create($this->settings);

        $this->assertEntityManager($manager);
    }

    public function testCanDecorateTheEntityManager(): void
    {
        $this->disableDebugbar();
        $this->disableSecondLevelCaching();
        $this->disableCustomCacheNamespace();
        $this->disableCustomFunctions();
        $this->enableLaravelNamingStrategy();

        $this->settings['decorator'] = Decorator::class;

        $manager = $this->factory->create($this->settings);

        $this->assertEntityManager($manager);
        $this->assertInstanceOf(Decorator::class, $manager);
        $this->assertInstanceOf(EntityManagerDecorator::class, $manager);
    }

    public function testCanSetRepositoryFactory(): void
    {
        $this->disableDebugbar();
        $this->disableSecondLevelCaching();
        $this->disableCustomCacheNamespace();
        $this->disableCustomFunctions();
        $this->enableLaravelNamingStrategy();

        $this->settings['repository_factory'] = 'RepositoryFactory';

        $repositoryFactory = m::mock(RepositoryFactory::class);

        $this->container->shouldReceive('make')
            ->with('RepositoryFactory')
            ->once()->andReturn($repositoryFactory);

        $this->configuration->shouldReceive('setRepositoryFactory')
            ->once()
            ->with($repositoryFactory);

        $manager = $this->factory->create($this->settings);

        $this->assertEntityManager($manager);
    }

    public function testIlluminateCacheProviderCustomStore(): void
    {
        m::resetContainer();

        $config = new ConfigRepository([
            'database.connections.mysql' => ['driver' => 'mysql'],
            'doctrine' => [
                'meta'       => 'xml',
                'connection' => 'mysql',
                'paths'      => ['Entities'],
                'proxies'    => [
                    'path'          => 'dir',
                    'auto_generate' => false,
                    'namespace'     => 'namespace',
                ],

                'cache' => [
                    'metadata' => [
                        'driver' => 'illuminate',
                        'store'  => 'myStoreName',
                    ],
                ],
            ],
            'doctrine.custom_datetime_functions' => [],
            'doctrine.custom_numeric_functions'  => [],
            'doctrine.custom_string_functions'   => [],
        ]);

        $container = new \Illuminate\Container\Container();
        $container->singleton(Repository::class, static function () use ($config) {
            return $config;
        });

        $cache = M::mock(\Illuminate\Contracts\Cache\Repository::class);

        $factory = M::mock(Factory::class);
        $factory->shouldReceive('store')->with('myStoreName')->andReturn($cache);

        $container->singleton(Factory::class, static function () use ($factory) {
            return $factory;
        });

        $factory = new EntityManagerFactory(
            $container,
            new ORMSetupResolver(),
            new MetaDataManager($container),
            new ConnectionManager($container),
            new CacheManager($container),
            $config,
            new EntityListenerResolver($container),
        );

        $manager = $factory->create($config->get('doctrine'));

        $this->assertInstanceOf(CacheItemPoolInterface::class, $manager->getConfiguration()->getMetadataCache());
    }

    public function testIlluminateCacheProviderRedis(): void
    {
        m::resetContainer();

        $config = new ConfigRepository([
            'database.connections.mysql' => ['driver' => 'mysql'],
            'doctrine' => [
                'meta'       => 'xml',
                'connection' => 'mysql',
                'paths'      => ['Entities'],
                'proxies'    => [
                    'path'          => 'dir',
                    'auto_generate' => false,
                    'namespace'     => 'namespace',
                ],

                'cache' => [
                    'metadata' => ['driver' => 'redis'],
                ],
            ],
            'doctrine.custom_datetime_functions' => [],
            'doctrine.custom_numeric_functions'  => [],
            'doctrine.custom_string_functions'   => [],
        ]);

        $container = new \Illuminate\Container\Container();
        $container->singleton(Repository::class, static function () use ($config) {
            return $config;
        });

        $cache = M::mock(\Illuminate\Contracts\Cache\Repository::class);

        $factory = M::mock(Factory::class);
        $factory->shouldReceive('store')->with('redis')->andReturn($cache);

        $container->singleton(Factory::class, static function () use ($factory) {
            return $factory;
        });

        $factory = new EntityManagerFactory(
            $container,
            new ORMSetupResolver(),
            new MetaDataManager($container),
            new ConnectionManager($container),
            new CacheManager($container),
            $config,
            new EntityListenerResolver($container),
        );

        $manager = $factory->create($config->get('doctrine'));

        $this->assertInstanceOf(CacheItemPoolInterface::class, $manager->getConfiguration()->getMetadataCache());
    }

    public function testIlluminateCacheProviderInvalidStore(): void
    {
        m::resetContainer();

        $config = new ConfigRepository([
            'database.connections.mysql' => ['driver' => 'mysql'],
            'doctrine' => [
                'meta'       => 'xml',
                'connection' => 'mysql',
                'paths'      => ['Entities'],
                'proxies'    => [
                    'path'          => 'dir',
                    'auto_generate' => false,
                    'namespace'     => 'namespace',
                ],

                'cache' => [
                    'metadata' => ['driver' => 'illuminate'],
                ],
            ],
            'doctrine.custom_datetime_functions' => [],
            'doctrine.custom_numeric_functions'  => [],
            'doctrine.custom_string_functions'   => [],
        ]);

        $container = new \Illuminate\Container\Container();
        $container->singleton(Repository::class, static function () use ($config) {
            return $config;
        });

        $cache = M::mock(\Illuminate\Contracts\Cache\Repository::class);

        $factory = M::mock(Factory::class);
        $factory->shouldReceive('store')->with('myStoreName')->andReturn($cache);

        $container->singleton(Factory::class, static function () use ($factory) {
            return $factory;
        });

        $factory = new EntityManagerFactory(
            $container,
            new ORMSetupResolver(),
            new MetaDataManager($container),
            new ConnectionManager($container),
            new CacheManager($container),
            $config,
            new EntityListenerResolver($container),
        );

        $this->expectException(InvalidArgumentException::class);

        $this->expectExceptionMessage('Please specify the `store` when using the "illuminate" cache driver.');
        $factory->create($config->get('doctrine'));
    }

    public function testPhpFileCacheCustomPath(): void
    {
        m::resetContainer();

        $config = new ConfigRepository([
            'database.connections.mysql' => ['driver' => 'mysql'],
            'doctrine' => [
                'meta'       => 'xml',
                'connection' => 'mysql',
                'paths'      => ['Entities'],
                'proxies'    => [
                    'path'          => 'dir',
                    'auto_generate' => false,
                    'namespace'     => 'namespace',
                ],

                'cache' => [
                    'metadata' => [
                        'driver' => 'php_file',
                        'path'   => 'tests/cache',
                    ],
                ],
            ],
            'doctrine.custom_datetime_functions' => [],
            'doctrine.custom_numeric_functions'  => [],
            'doctrine.custom_string_functions'   => [],
        ]);

        $container = new \Illuminate\Container\Container();
        $container->singleton(Repository::class, static function () use ($config) {
            return $config;
        });

        $cache = M::mock(Illuminate\Contracts\Cache\Repository::class);

        $factory = M::mock(Factory::class);
        $factory->shouldReceive('store')->with('myStoreName')->andReturn($cache);

        $container->singleton(Illuminate\Contracts\Cache\Factory::class, static function () use ($factory) {
            return $factory;
        });

        $factory = new EntityManagerFactory(
            $container,
            new ORMSetupResolver(),
            new MetaDataManager($container),
            new ConnectionManager($container),
            new CacheManager($container),
            $config,
            new EntityListenerResolver($container),
        );

        $manager = $factory->create($config->get('doctrine'));

        $metadataCache = $manager->getConfiguration()->getMetadataCache();
        $this->assertInstanceOf(PhpFilesAdapter::class, $metadataCache);

        $reflectionCache   = new ReflectionObject($metadataCache);
        $directoryProperty = $reflectionCache->getProperty('directory');
        $directoryProperty->setAccessible(true);

        $this->assertStringContainsString('tests/cache', $directoryProperty->getValue($metadataCache));
        rmdir(__DIR__ . '/../cache/doctrine-cache');
    }

    public function testWrapperConnection(): void
    {
        m::resetContainer();

        $config = new ConfigRepository([
            'database.connections.mysql' => [
                'wrapperClass' => FakeConnection::class,
                'driver'       => 'mysql',
            ],
            'doctrine' => [
                'meta'       => 'xml',
                'connection' => 'mysql',
                'paths'      => ['Entities'],
                'proxies'    => [
                    'path'          => 'dir',
                    'auto_generate' => false,
                    'namespace'     => 'namespace',
                ],
            ],
            'doctrine.custom_datetime_functions' => [],
            'doctrine.custom_numeric_functions'  => [],
            'doctrine.custom_string_functions'   => [],
        ]);

        $container = new \Illuminate\Container\Container();
        $container->singleton(Repository::class, static function () use ($config) {
            return $config;
        });

        $factory = new EntityManagerFactory(
            $container,
            new ORMSetupResolver(),
            new MetaDataManager($container),
            new ConnectionManager($container),
            new CacheManager($container),
            $config,
            new EntityListenerResolver($container),
        );

        $manager = $factory->create($config->get('doctrine'));

        $this->assertInstanceOf(FakeConnection::class, $manager->getConnection());
    }

    public function testCustomEventManager(): void
    {
        m::resetContainer();

        $config = new ConfigRepository([
            'database.connections.mysql' => ['driver' => 'mysql'],
            'doctrine' => [
                'meta'       => 'xml',
                'connection' => 'mysql',
                'paths'      => ['Entities'],
                'proxies'    => [
                    'path'          => 'dir',
                    'auto_generate' => false,
                    'namespace'     => 'namespace',
                ],
                'event_manager' => 'my_event_manager',
            ],
            'doctrine.custom_datetime_functions' => [],
            'doctrine.custom_numeric_functions'  => [],
            'doctrine.custom_string_functions'   => [],
        ]);

        $container = new \Illuminate\Container\Container();
        $container->singleton(Repository::class, static function () use ($config) {
            return $config;
        });

        $container->alias(FakeEventManager::class, 'my_event_manager');

        $factory = new EntityManagerFactory(
            $container,
            new ORMSetupResolver(),
            new MetaDataManager($container),
            new ConnectionManager($container),
            new CacheManager($container),
            $config,
            new EntityListenerResolver($container),
        );

        $manager = $factory->create($config->get('doctrine'));

        $this->assertInstanceOf(FakeEventManager::class, $manager->getEventManager());
    }

    /**
     * MOCKS
     *
     * @param mixed[] $driverConfig
     */
    protected function mockConfig(array $driverConfig = ['driver' => 'mysql'], bool $strictCallCountChecking = true): void
    {
        $this->config = m::mock(Repository::class);

        $this->config->shouldReceive('get')
                     ->with('doctrine.cache.default', 'array')
                     ->atLeast()->once()
                     ->andReturn('array');

        foreach ($this->caches as $cache) {
            $expectation = $this->config->shouldReceive('get')
                         ->with('doctrine.cache.' . $cache, [])
                         ->andReturn(['driver' => 'array']);

            $strictCallCountChecking ? $expectation->once() : $expectation->never();
        }

        $this->config->shouldReceive('has')
                     ->with('database.connections.mysql')
                     ->once()
                     ->andReturn(true);

        $this->config->shouldReceive('get')
                     ->with('database.connections.mysql')
                     ->once()
                     ->andReturn($driverConfig);

        $expectation = $this->config->shouldReceive('get')
                     ->with('doctrine.custom_datetime_functions')
                     ->andReturn(['datetime']);

        $strictCallCountChecking ? $expectation->once() : $expectation->never();

        $expectation = $this->config->shouldReceive('get')
                     ->with('doctrine.custom_numeric_functions')
                     ->andReturn(['numeric']);

        $strictCallCountChecking ? $expectation->once() : $expectation->never();

        $expectation = $this->config->shouldReceive('get')
                     ->with('doctrine.custom_string_functions')
                     ->andReturn(['string']);

        $strictCallCountChecking ? $expectation->once() : $expectation->never();

        $expectation = $this->config->shouldReceive('get')
                     ->with('doctrine.custom_hydration_modes', [])
                     ->andReturn([]);

        $strictCallCountChecking ? $expectation->once() : $expectation->never();
    }

    protected function mockCache(): void
    {
        $this->cache = m::mock(CacheManager::class);

        $this->cache->shouldReceive('driver')
                    ->times(count($this->caches)) // one for each cache driver
                    ->andReturn(new ArrayAdapter());
    }

    protected function mockConnection(): void
    {
        $this->connection = m::mock(ConnectionManager::class);
        $this->connection->shouldReceive('driver')
                         ->once()
                         ->with('mysql', ['driver' => 'mysql'])
                         ->andReturn(['driver' => 'pdo_mysql']);
    }

    protected function mockMeta(): void
    {
        $this->mappingDriver = m::mock(MappingDriver::class);
        $this->mappingDriver->shouldReceive('addPaths')->with($this->settings['paths']);

        $this->mockORMConfiguration();

        $this->meta = m::mock(MetaDataManager::class);
        $this->meta->shouldReceive('driver')
                   ->once()
                   ->andReturn($this->mappingDriver);
    }

    protected function mockApp(): void
    {
        $this->container = m::mock(Container::class);
    }

    protected function mockResolver(): void
    {
        $this->listenerResolver = m::mock(EntityListenerResolver::class);
    }

    protected function disableDebugbar(): void
    {
    }

    protected function disableSecondLevelCaching(): void
    {
        $this->config->shouldReceive('get')
                     ->with('doctrine.cache.second_level', false)->atLeast()->once()
                     ->andReturn(false);

        $this->configuration->shouldReceive('isSecondLevelCacheEnabled')
                            ->atLeast()->once()
                            ->andReturn(false);
    }

    protected function disableCustomCacheNamespace(): void
    {
        $this->config->shouldReceive('get')
                     ->with('doctrine.cache.namespace')
                     ->atLeast()->once()
                     ->andReturn(null);
    }

    protected function disableCustomFunctions(): void
    {
        $this->configuration->shouldReceive('setCustomDatetimeFunctions');
        $this->configuration->shouldReceive('setCustomNumericFunctions');
        $this->configuration->shouldReceive('setCustomStringFunctions');
    }

    protected function mockORMConfiguration(): void
    {
        $this->configuration = m::mock(Configuration::class);
        $this->configuration->shouldReceive('setSQLLogger');

        $this->configuration->shouldReceive('getMetadataDriverImpl')
                            ->andReturn($this->mappingDriver);

        $this->configuration->shouldReceive('setMetadataDriverImpl')
                            ->atLeast()->once();
        $this->configuration->shouldReceive('setMiddlewares')
            ->atLeast()->once();

        $this->configuration->shouldReceive('getAutoCommit')
                            ->atLeast()->once()
                            ->andReturn(true);

        $this->configuration->shouldReceive('getClassMetadataFactoryName')
                            ->atLeast()->once()
                            ->andReturn('Doctrine\ORM\Mapping\ClassMetadataFactory');

        $this->configuration->shouldReceive('setMetadataCache')->once();
        $this->configuration->shouldReceive('setQueryCache')->once();
        $this->configuration->shouldReceive('setResultCache')->once();

        $cache = m::mock(CacheItemPoolInterface::class);
        $this->configuration->shouldReceive('getMetadataCache')
                            ->atLeast()->once()
                            ->andReturn($cache);

        $repoFactory = m::mock(RepositoryFactory::class);
        $this->configuration->shouldReceive('getRepositoryFactory')
                            ->atLeast()->once()
                            ->andReturn($repoFactory);

        $entityListenerResolver = m::mock(EntityListenerResolver::class);
        $this->configuration->shouldReceive('getEntityListenerResolver')
                            ->atLeast()->once()
                            ->andReturn($entityListenerResolver);

        $this->configuration->shouldReceive('setEntityListenerResolver')
                            ->atLeast()->once()
                            ->with(m::type(EntityListenerResolver::class));

        $this->configuration->shouldReceive('getProxyDir')
                            ->atLeast()->once()
                            ->andReturn('dir');

        $this->configuration->shouldReceive('setProxyDir')
                            ->atLeast()->once()
                            ->with('dir');

        $this->configuration->shouldReceive('getProxyNamespace')
                            ->atLeast()->once()
                            ->andReturn('namespace');

        $this->configuration->shouldReceive('setProxyNamespace')
                            ->atLeast()->once()
                            ->with('namespace');

        $this->configuration->shouldReceive('getAutoGenerateProxyClasses')
                            ->atLeast()->once()
                            ->andReturn(false);

        $this->configuration->shouldReceive('setAutoGenerateProxyClasses')
                            ->atLeast()->once()
                            ->with(false);

        $this->configuration->shouldReceive('setDefaultRepositoryClassName')
                            ->once()
                            ->with('Repo');

        $this->configuration->shouldReceive('getMiddlewares')->once()->andReturn([]);

        $schemaManagerFactory = new DefaultSchemaManagerFactory();
        $this->configuration->shouldReceive('setSchemaManagerFactory')->once();
        $this->configuration->shouldReceive('getSchemaManagerFactory')->once()->andReturn($schemaManagerFactory);
    }

    protected function enableLaravelNamingStrategy(): void
    {
        $strategy = m::mock(LaravelNamingStrategy::class);

        $this->container->shouldReceive('make')
                        ->with(LaravelNamingStrategy::class)
                        ->once()->andReturn($strategy);

        $this->configuration->shouldReceive('setNamingStrategy')->once()->with($strategy);
    }

    /**
     * Data provider for testPrimaryReadReplicaConnection.
     *
     * @return mixed[]
     */
    public static function getTestPrimaryReadReplicaConnectionData(): array
    {
        $out = [];

        // Case #0. Simple valid configuration, everything should go well.
        $out[] = [self::getDummyBaseInputConfig()];

        //Case #1. No read DBs set.
        $inputConfig = self::getDummyBaseInputConfig();
        unset($inputConfig['read']);

        $out[] = [
            $inputConfig,
            InvalidArgumentException::class,
            "Parameter 'read' must be set for read/write config.",
        ];

        //Case #2. 'read' isn't an array
        $inputConfig         = self::getDummyBaseInputConfig();
        $inputConfig['read'] = 'test';

        $out[] = [
            $inputConfig,
            InvalidArgumentException::class,
            "Parameter 'read' must be an array containing multiple arrays.",
        ];

        //Case #3. 'read' has non array entries.
        $inputConfig           = self::getDummyBaseInputConfig();
        $inputConfig['read'][] = 'test';

        $out[] = [
            $inputConfig,
            InvalidArgumentException::class,
            "Parameter 'read' must be an array containing multiple arrays.",
        ];

        //Case #4. 'read' has empty entries.
        $inputConfig           = self::getDummyBaseInputConfig();
        $inputConfig['read'][] = [];

        $out[] = [
            $inputConfig,
            InvalidArgumentException::class,
            "Parameter 'read' config no. 2 is empty.",
        ];

        //Case #5. 'read' has empty first entry. (reported by maxbrokman.)
        $inputConfig            = self::getDummyBaseInputConfig();
        $inputConfig['read'][0] = [];

        $out[] = [
            $inputConfig,
            InvalidArgumentException::class,
            "Parameter 'read' config no. 0 is empty.",
        ];

        return $out;
    }

    /**
     * Check if config is handled correctly.
     *
     * @param mixed[] $inputConfig
     */
    #[DataProvider('getTestPrimaryReadReplicaConnectionData')]
    public function testPrimaryReadReplicaConnection(
        array $inputConfig,
        string $expectedException = '',
        string $msg = '',
    ): void {
        m::resetContainer();

        $this->mockApp();
        $this->mockResolver();
        $this->mockConfig($inputConfig, empty($expectedException));

        $this->setup = m::mock(ORMSetupResolver::class);
        $this->setup->shouldReceive('createConfiguration')->once()->andReturn($this->configuration);

        $this->connection = m::mock(ConnectionManager::class);
        $this->connection->shouldReceive('driver')
            ->once()
            ->with('mysql', $inputConfig)
            ->andReturn(['driver' => 'pdo_mysql']);

        $factory = new EntityManagerFactory(
            $this->container,
            $this->setup,
            $this->meta,
            $this->connection,
            $this->cache,
            $this->config,
            $this->listenerResolver,
        );

        if (! empty($expectedException)) {
            $this->expectException($expectedException);
            $this->expectExceptionMessage($msg);
        } else {
            $this->disableDebugbar();
            $this->disableCustomCacheNamespace();
            $this->disableSecondLevelCaching();
            $this->disableCustomFunctions();
            $this->enableLaravelNamingStrategy();
        }

        $this->settings['connection'] = 'mysql';
        $em                           = $factory->create($this->settings);

        $this->assertInstanceOf(PrimaryReadReplicaConnection::class, $em->getConnection());
    }

    protected function tearDown(): void
    {
        m::close();

        parent::tearDown();
    }

    /**
     * Returns dummy base config for testing.
     *
     * @return mixed[]
     */
    private static function getDummyBaseInputConfig(): array
    {
        return [
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'port'      => '3306',
            'database'  => 'test',
            'username'  => 'homestead',
            'password'  => 'secret',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'strict'    => false,
            'engine'    => null,
            'write'     => ['port' => 3307],
            'read' => [
                [
                    'port'     => 3308,
                    'database' => 'test2',
                ],
                [
                    'host' => 'localhost2',
                    'port' => 3309,
                ],
            ],
        ];
    }
}
