<?php

namespace LaravelDoctrine\ORM;

use Doctrine\Common\Persistence\ManagerRegistry;

class BootChain
{
    /**
     * @var callable[]
     */
    protected static $resolveCallbacks = [];

    /**
     * @param callable $callback
     */
    public static function add(callable  $callback)
    {
        static::$resolveCallbacks[] = $callback;
    }

    /**
     * @param ManagerRegistry $registry
     */
    public static function boot(ManagerRegistry $registry)
    {
        foreach (static::$resolveCallbacks as $callback) {
            if (is_callable($callback)) {
                call_user_func($callback, $registry);
            }
        }

        static::flush();
    }

    /**
     * Flush the boot chain
     */
    public static function flush()
    {
        static::$resolveCallbacks = [];
    }
}
