<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM;

use Doctrine\Persistence\ManagerRegistry;

use function call_user_func;
use function is_callable;

class BootChain
{
    /** @var callable[] */
    protected static array $resolveCallbacks = [];

    public static function add(callable $callback): void
    {
        static::$resolveCallbacks[] = $callback;
    }

    public static function boot(ManagerRegistry $registry): void
    {
        foreach (static::$resolveCallbacks as $callback) {
            if (! is_callable($callback)) {
                continue;
            }

            call_user_func($callback, $registry);
        }

        static::flush();
    }

    /**
     * Flush the boot chain
     */
    public static function flush(): void
    {
        static::$resolveCallbacks = [];
    }
}
