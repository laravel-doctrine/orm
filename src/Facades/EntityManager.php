<?php

namespace LaravelDoctrine\ORM\Facades;

use Illuminate\Support\Facades\Facade;

class EntityManager extends Facade
{
    /**
     * Get the registered name of the component.
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'em';
    }
}
