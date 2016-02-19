<?php

namespace LaravelDoctrine\ORM\Configuration;

use Illuminate\Contracts\Container\Container;
use LaravelDoctrine\ORM\Exceptions\DriverNotFound;

abstract class Manager
{
    /**
     * The application instance.
     * @var Container
     */
    protected $container;

    /**
     * The registered custom driver creators.
     * @var array
     */
    protected $customCreators = [];

    /**
     * The array of created "drivers".
     * @var array
     */
    protected $drivers = [];

    /**
     * Create a new manager instance.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Get the default driver name.
     * @return string
     */
    abstract public function getDefaultDriver();

    /**
     * @return string
     */
    abstract public function getNamespace();

    /**
     * @return string
     */
    abstract public function getClassSuffix();

    /**
     * Get a driver instance.
     *
     * @param string $driver
     * @param array  $settings
     *
     * @param  bool  $resolve
     * @return mixed
     */
    public function driver($driver = null, array $settings = [], $resolve = true)
    {
        $driver = $driver ?: $this->getDefaultDriver();

        return $this->createDriver($driver, $settings, $resolve);
    }

    /**
     * Create a new driver instance.
     *
     * @param string $driver
     * @param array  $settings
     *
     * @param  bool  $resolve
     * @return mixed
     */
    protected function createDriver($driver, array $settings = [], $resolve = true)
    {
        $class = $this->getNamespace() . '\\' . studly_case($driver) . $this->getClassSuffix();

        // We'll check to see if a creator method exists for the given driver. If not we
        // will check for a custom driver creator, which allows developers to create
        // drivers using their own customized driver creator Closure to create it.
        if (isset($this->customCreators[$driver])) {
            return $this->callCustomCreator($driver, $settings);
        } elseif (class_exists($class)) {
            $instance = $this->container->make($class);

            if ($resolve) {
                return $instance->resolve($settings);
            }

            return $instance;
        }

        throw new DriverNotFound("Driver [$driver] not supported.");
    }

    /**
     * Call a custom driver creator.
     *
     * @param string $driver
     * @param array  $settings
     *
     * @return mixed
     */
    protected function callCustomCreator($driver, array $settings = [])
    {
        return $this->customCreators[$driver]($settings, $this->container);
    }

    /**
     * Register a custom driver creator Closure.
     *
     * @param string   $driver
     * @param callable $callback
     *
     * @return $this
     */
    public function extend($driver, callable $callback)
    {
        $this->customCreators[$driver] = $callback;

        return $this;
    }

    /**
     * Get all of the created "drivers".
     * @return array
     */
    public function getDrivers()
    {
        return $this->drivers;
    }

    /**
     * Dynamically call the default driver instance.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->driver(), $method], $parameters);
    }
}
