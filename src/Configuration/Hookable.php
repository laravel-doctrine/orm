<?php

namespace Brouwers\LaravelDoctrine\Configuration;

use Closure;

interface Hookable
{
    /**
     * @param callable $callback
     */
    public static function resolved(Closure $callback);
}
