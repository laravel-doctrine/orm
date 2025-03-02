<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM;

use Doctrine\ORM\Exception\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Doctrine\Persistence\Proxy;
use Exception;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;
use ReflectionClass;
use RuntimeException;
use Throwable;

use function explode;
use function head;
use function sprintf;
use function strpos;

final class IlluminateRegistry implements ManagerRegistry
{
    /** @const */
    public const MANAGER_BINDING_PREFIX = 'doctrine.managers.';

    /** @const */
    public const CONNECTION_BINDING_PREFIX = 'doctrine.connections.';

    protected string $defaultManager = 'default';

    protected string $defaultConnection = 'default';

    /** @var mixed[] */
    protected array $managers = [];

    /** @var mixed[] */
    protected array $connections = [];

    /** @var mixed[] */
    protected array $managersMap = [];

    /** @var mixed[] */
    protected array $connectionsMap = [];

    public function __construct(protected Container $container, protected EntityManagerFactory $factory)
    {
    }

    /** @param mixed[] $settings */
    public function addManager(string $manager, array $settings = []): void
    {
        $this->container->singleton($this->getManagerBindingName($manager), function () use ($settings) {
            return $this->factory->create($settings);
        });

        $this->managers[$manager] = $manager;

        $this->addConnection($manager, $settings);
    }

    /** @param mixed[] $settings */
    public function addConnection(string $connection, array $settings = []): void
    {
        $this->container->singleton($this->getConnectionBindingName($connection), function () use ($connection) {
            return $this->getManager($connection)->getConnection();
        });

        $this->connections[$connection] = $connection;
    }

    /**
     * Gets the default connection name.
     *
     * @return string The default connection name.
     */
    public function getDefaultConnectionName(): string
    {
        if (isset($this->connections[$this->defaultConnection])) {
            return $this->defaultConnection;
        }

        return head($this->connections);
    }

    /**
     * Gets the named connection.
     *
     * @param string $name The connection name (null for the default one).
     */
    public function getConnection(string|null $name = null): object
    {
        $name = $name ?: $this->getDefaultConnectionName();

        if (! $this->connectionExists($name)) {
            throw new InvalidArgumentException(sprintf('Doctrine Connection named "%s" does not exist.', $name));
        }

        if (isset($this->connectionsMap[$name])) {
            return $this->connectionsMap[$name];
        }

        return $this->connectionsMap[$name] = $this->getService(
            $this->getConnectionBindingName($this->connections[$name]),
        );
    }

    public function connectionExists(string $name): bool
    {
        return isset($this->connections[$name]);
    }

    /**
     * Gets an array of all registered connections.
     *
     * @return mixed[] An array of Connection instances.
     */
    public function getConnections(): array
    {
        $connections = [];
        foreach ($this->getConnectionNames() as $name) {
            $connections[$name] = $this->getConnection($name);
        }

        return $connections;
    }

    /**
     * Gets all connection names.
     *
     * @return mixed[] An array of connection names.
     */
    public function getConnectionNames(): array
    {
        return $this->connections;
    }

    /**
     * Gets the default object manager name.
     *
     * @return string The default object manager name.
     */
    public function getDefaultManagerName(): string
    {
        if (isset($this->managers[$this->defaultManager])) {
            return $this->defaultManager;
        }

        return head($this->managers);
    }

    /**
     * Gets a named object manager.
     *
     * @param string $name The object manager name (null for the default one).
     */
    public function getManager(string|null $name = null): ObjectManager
    {
        $name ??= $this->getDefaultManagerName();

        if (! $this->managerExists($name)) {
            throw new InvalidArgumentException(sprintf('Doctrine Manager named "%s" does not exist.', $name));
        }

        if (isset($this->managersMap[$name])) {
            return $this->managersMap[$name];
        }

        return $this->managersMap[$name] = $this->getService(
            $this->getManagerBindingName($this->managers[$name]),
        );
    }

    public function managerExists(string $name): bool
    {
        return isset($this->managers[$name]);
    }

    /**
     * Gets all connection names.
     *
     * @return mixed[] An array of connection names.
     */
    public function getManagerNames(): array
    {
        return $this->managers;
    }

    /**
     * Gets an array of all registered object managers.
     *
     * @return ObjectManager[] An array of ObjectManager instances
     */
    public function getManagers(): array
    {
        $managers = [];
        foreach ($this->getManagerNames() as $name) {
            $managers[$name] = $this->getManager($name);
        }

        return $managers;
    }

    /**
     * Close an existing object manager.
     *
     * @param string|null $name The object manager name (null for the default one).
     */
    public function closeManager(string|null $name = null): void
    {
        $name = $name ?: $this->getDefaultManagerName();

        if (! $this->managerExists($name)) {
            throw new InvalidArgumentException(sprintf('Doctrine Manager named "%s" does not exist.', $name));
        }

        // force the creation of a new document manager
        // if the current one is closed
        $this->resetService(
            $this->getManagerBindingName($this->managers[$name]),
        );

        $this->resetService(
            $this->getConnectionBindingName($this->connections[$name]),
        );

        unset($this->managersMap[$name]);
        unset($this->connectionsMap[$name]);
    }

    /**
     * Purge a named object manager.
     *
     * This method can be used if you wish to close an object manager manually, without opening a new one.
     * This can be useful if you wish to open a new manager later (but maybe with different settings).
     *
     * @param string|null $name The object manager name (null for the default one).
     */
    public function purgeManager(string|null $name = null): void
    {
        $name = $name ?: $this->getDefaultManagerName();
        $this->closeManager($name);

        unset($this->managers[$name]);
        unset($this->connections[$name]);
    }

    /**
     * Resets a named object manager.
     * This method is useful when an object manager has been closed
     * because of a rollbacked transaction AND when you think that
     * it makes sense to get a new one to replace the closed one.
     * Be warned that you will get a brand new object manager as
     * the existing one is not useable anymore. This means that any
     * other object with a dependency on this object manager will
     * hold an obsolete reference. You can inject the registry instead
     * to avoid this problem.
     *
     * @param string|null $name The object manager name (null for the default one).
     */
    public function resetManager(string|null $name = null): ObjectManager
    {
        $this->closeManager($name);

        return $this->getManager($name);
    }

    /**
     * Resolves a registered namespace alias to the full namespace.
     * This method looks for the alias in all registered object managers.
     *
     * @param string $alias The alias.
     *
     * @return string       The full namespace.
     *
     * @throws ORMException
     */
    public function getAliasNamespace(string $alias): string
    {
        foreach ($this->getManagerNames() as $name) {
            return $this->getManager($name)->getConfiguration()->getEntityNamespace($alias);
        }

        throw new Exception('Namespace "' . $alias . '" not found');
    }

    /**
     * Gets the ObjectRepository for an persistent object.
     *
     * @param string $persistentObject      The name of the persistent object.
     * @param string $persistentManagerName The object manager name (null for the default one).
     */
    public function getRepository(string $persistentObject, string|null $persistentManagerName = null): ObjectRepository
    {
        return $this->getManager($persistentManagerName)->getRepository($persistentObject);
    }

    /**
     * Gets the object manager associated with a given class.
     *
     * @param class-string $className A persistent object class name.
     *
     * @return ($throwExceptionIfNotFound is true ? ObjectManager : ObjectManager|null)
     */
    public function getManagerForClass(string $className, bool $throwExceptionIfNotFound = false): ObjectManager|null
    {
        // Check for namespace alias
        if (strpos($className, ':') !== false) {
            [$namespaceAlias, $simpleClassName] = explode(':', $className, 2);
            $className                          = $this->getAliasNamespace($namespaceAlias) . '\\' . $simpleClassName;
        }

        // Check for proxy class
        try {
            $proxyClass = new ReflectionClass($className);

            if ($proxyClass->implementsInterface(Proxy::class)) {
                $className = $proxyClass->getParentClass()->getName();
            }
        } catch (Throwable) {
            throw new RuntimeException('Class ' . $className . ' is not a valid class name.');
        }

        foreach ($this->getManagers() as $entityManager) {
            if ($entityManager->getMetadataFactory()->isTransient($className)) {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }

            foreach ($entityManager->getMetadataFactory()->getAllMetadata() as $metadata) {
                if ($metadata->getName() === $className) {
                    return $entityManager;
                }
            }
        }

        if ($throwExceptionIfNotFound) {
            throw new RuntimeException('No manager found for class ' . $className);
        }

        return null;
    }

    /**
     * Fetches/creates the given services.
     * A service in this context is connection or a manager instance.
     *
     * @param string $name The name of the service.
     *
     * @return object The instance of the given service.
     */
    protected function getService(string $name): mixed
    {
        return $this->container->make($name);
    }

    /**
     * Resets the given services.
     * A service in this context is connection or a manager instance.
     *
     * @param string $name The name of the service.
     */
    protected function resetService(string $name): void
    {
        $this->container->forgetInstance($name);
    }

    protected function getManagerBindingName(string $manager): string
    {
        return self::MANAGER_BINDING_PREFIX . $manager;
    }

    protected function getConnectionBindingName(string $connection): string
    {
        return self::CONNECTION_BINDING_PREFIX . $connection;
    }

    public function setDefaultManager(string $defaultManager): self
    {
        $this->defaultManager = $defaultManager;

        return $this;
    }

    public function setDefaultConnection(string $defaultConnection): self
    {
        $this->defaultConnection = $defaultConnection;

        return $this;
    }
}
