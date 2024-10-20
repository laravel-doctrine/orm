<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Configuration;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Str;
use LaravelDoctrine\ORM\Exceptions\DriverNotFound;

use function class_exists;

abstract class Manager
{
    /**
     * The application instance.
     */
    protected Container $container;

    /**
     * The registered custom driver creators.
     *
     * @var mixed[]
     */
    protected array $customCreators = [];

    /**
     * The array of created "drivers".
     *
     * @var mixed[]
     */
    protected array $drivers = [];

    /**
     * Create a new manager instance.
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Get the default driver name.
     */
    abstract public function getDefaultDriver(): string;

    abstract public function getNamespace(): string;

    abstract public function getClassSuffix(): string;

    /**
     * Get a driver instance.
     *
     * @param mixed[] $settings
     */
    public function driver(string|null $driver = null, array $settings = [], bool $resolve = true): mixed
    {
        $driver = $driver ?: $this->getDefaultDriver();

        return $this->createDriver($driver, $settings, $resolve);
    }

    /**
     * Create a new driver instance.
     *
     * @param mixed[] $settings
     */
    protected function createDriver(string $driver, array $settings = [], bool $resolve = true): mixed
    {
        $class = $this->getNamespace() . '\\' . Str::studly($driver) . $this->getClassSuffix();

        // We'll check to see if a creator method exists for the given driver. If not we
        // will check for a custom driver creator, which allows developers to create
        // drivers using their own customized driver creator Closure to create it.
        if (isset($this->customCreators[$driver])) {
            return $this->callCustomCreator($driver, $settings);
        }

        if (class_exists($class)) {
            $instance = $this->container->make($class);

            if ($resolve) {
                return $instance->resolve($settings);
            }

            return $instance;
        }

        throw new DriverNotFound('Driver [' . $driver . '] not supported.');
    }

    /**
     * Call a custom driver creator.
     *
     * @param mixed[] $settings
     */
    protected function callCustomCreator(string $driver, array $settings = []): mixed
    {
        return $this->customCreators[$driver]($settings, $this->container);
    }

    /**
     * Register a custom driver creator Closure.
     *
     * @return $this
     */
    public function extend(string $driver, callable $callback): self
    {
        $this->customCreators[$driver] = $callback;

        return $this;
    }

    /**
     * Get all of the created "drivers".
     *
     * @return mixed[]
     */
    public function getDrivers(): array
    {
        return $this->drivers;
    }
}
