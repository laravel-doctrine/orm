<?php

namespace LaravelDoctrine\ORM\Configuration;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use LaravelDoctrine\ORM\Exceptions\DriverNotFound;

abstract class Manager
{
    /**
     * The application instance.
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

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
     * @param \Illuminate\Contracts\Foundation\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
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
     *
     * @return mixed
     */
    public function driver($driver = null, array $settings = [])
    {
        $driver = $driver ?: $this->getDefaultDriver();

        // If the given driver has not been created before, we will create the instances
        // here and cache it so we can return it next time very quickly. If there is
        // already a driver created by this name, we'll just return that instance.
        if (!isset($this->drivers[$driver])) {
            $this->drivers[$driver] = $this->createDriver($driver, $settings);
        }

        return $this->drivers[$driver];
    }

    /**
     * Create a new driver instance.
     *
     * @param string $driver
     * @param array  $settings
     *
     * @return mixed
     */
    protected function createDriver($driver, array $settings = [])
    {
        $class = $this->getNamespace() . '\\' . studly_case($driver) . $this->getClassSuffix();

        // We'll check to see if a creator method exists for the given driver. If not we
        // will check for a custom driver creator, which allows developers to create
        // drivers using their own customized driver creator Closure to create it.
        if (isset($this->customCreators[$driver])) {
            return $this->callCustomCreator($driver);
        } elseif (class_exists($class)) {
            return $this->app->make($class)->resolve($settings);
        }

        throw new DriverNotFound("Driver [$driver] not supported.");
    }

    /**
     * Call a custom driver creator.
     *
     * @param string $driver
     *
     * @return mixed
     */
    protected function callCustomCreator($driver)
    {
        return $this->customCreators[$driver]($this->app);
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
