<?php

namespace Brouwers\LaravelDoctrine\Configuration;

use Brouwers\LaravelDoctrine\Exceptions\DriverNotRegistered;
use Closure;

trait ExtendableTrait
{
    /**
     * @var static
     */
    protected static $instance;

    /**
     * @var array
     */
    protected $drivers = [];

    /**
     * @var array
     */
    protected static $resolved = [];

    /**
     * @param $name
     *
     * @throws DriverNotRegistered
     * @return mixed
     */
    public static function resolve($name)
    {
        // Only resolve once
        if (isset(static::$resolved[$name])) {
            return static::$resolved[$name];
        }

        if ($driver = self::getInstance()->get($name)) {
            event(get_class(self::getInstance()) . ':resolved', $driver);

            return $driver;
        }

        throw new DriverNotRegistered("Driver {$name} not registered");
    }

    /**
     * @param Closure $callback
     */
    public static function resolved(Closure $callback)
    {
        app('events')->listen(get_class(self::getInstance()) . ':resolved', $callback);
    }

    /**
     * @param      $driver
     * @param null $default
     *
     * @return Driver
     */
    public function get($driver, $default = null)
    {
        if (isset($this->drivers[$driver])) {
            return $this->drivers[$driver]->resolve();
        }

        return $default;
    }

    /**
     * @return static
     */
    public static function getInstance()
    {
        return static::$instance = static::$instance ?: new static();
    }

    /**
     * @param          $driver
     * @param callable $callback
     */
    public static function extend($driver, $callback = null)
    {
        $class   = null;
        $manager = self::getInstance();

        if (!is_callable($callback)) {
            $class    = $callback;
            $callback = null;
        }

        $manager->register(
            $manager->transformToDriver($driver, $callback, $class)
        );
    }

    /**
     * @param $driver
     */
    public function register(Driver $driver)
    {
        $this->drivers[$driver->getName()] = $driver;
    }

    /**
     * @return array
     */
    public static function getDrivers()
    {
        return self::getInstance()->drivers;
    }

    /**
     * @param          $driver
     * @param  Closure $callback
     * @param  null    $class
     * @return Driver
     */
    abstract public function transformToDriver($driver, Closure $callback = null, $class = null);
}
