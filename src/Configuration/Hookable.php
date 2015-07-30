<?php

namespace LaravelDoctrine\ORM\Configuration;

use Closure;

interface Hookable
{
    /**
     * @param callable $callback
     */
    public static function resolved(Closure $callback);
}
