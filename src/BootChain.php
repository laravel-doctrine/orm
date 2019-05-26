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
     * @var ManagerRegistry[]
     */
    protected static $registries = [];

    /**
     * @param callable $callback
     */
    public static function add(callable  $callback)
    {
        if (empty(static::$registries)) {
            static::$resolveCallbacks[] = $callback;
        } else {
            foreach (static::$registries as $registry) {
                if (is_callable($callback)) {
                    call_user_func($callback, $registry);
                }
            }
        }
    }

    /**
     * @param ManagerRegistry $registry
     */
    public static function boot(ManagerRegistry $registry)
    {
        static::$registries[] = $registry;
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
