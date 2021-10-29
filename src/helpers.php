<?php

if (!function_exists('entity')) {
    /**
     * Create a model factory builder for a given class, name, and amount.
     *
     * @param mixed ...$arguments class|class,name|class,amount|class,name,amount
     *
     * @return \LaravelDoctrine\ORM\Testing\FactoryBuilder
     */
    function entity(...$arguments)
    {
        $factory = app('LaravelDoctrine\ORM\Testing\Factory');

        if (isset($arguments[1]) && is_string($arguments[1])) {
            return $factory->of($arguments[0], $arguments[1])->times(isset($arguments[2]) ? $arguments[2] : 1);
        } elseif (isset($arguments[1])) {
            return $factory->of($arguments[0])->times($arguments[1]);
        } else {
            return $factory->of($arguments[0]);
        }
    }
}
