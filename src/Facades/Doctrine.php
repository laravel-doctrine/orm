<?php

namespace LaravelDoctrine\ORM\Facades;

use Illuminate\Support\Facades\Facade;
use LaravelDoctrine\ORM\DoctrineManager;

class Doctrine extends Facade
{
    /**
     * Get the registered name of the component.
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return DoctrineManager::class;
    }
}
