<?php

declare(strict_types=1);

use LaravelDoctrine\ORM\Testing\FactoryBuilder;

if (! function_exists('entity')) {
    /**
     * Create a model factory builder for a given class, name, and amount.
     *
     * @param mixed ...$arguments class|class,name|class,amount|class,name,amount
     */
    function entity(mixed ...$arguments): FactoryBuilder
    {
        $factory = app('LaravelDoctrine\ORM\Testing\Factory');

        if (isset($arguments[1]) && is_string($arguments[1])) {
            return $factory->of($arguments[0], $arguments[1])->times($arguments[2] ?? 1);
        }

        if (isset($arguments[1])) {
            return $factory->of($arguments[0])->times($arguments[1]);
        }

        return $factory->of($arguments[0]);
    }
}
