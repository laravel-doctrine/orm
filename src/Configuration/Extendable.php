<?php

namespace Brouwers\LaravelDoctrine\Configuration;

use Closure;

interface Extendable
{
    /**
     * @param          $driver
     * @param callable $callback
     * @param null     $class
     */
    public function transformToDriver($driver, Closure $callback = null, $class = null);

    /**
     * @param $driver
     */
    public function register(Driver $driver);

    /**
     * @param $driver
     *
     * @return mixed
     */
    public static function resolve($driver);

    /**
     * @param          $driver
     * @param callable $callback
     */
    public static function extend($driver, $callback = null);

    /**
     * @return array
     */
    public static function getDrivers();
}
