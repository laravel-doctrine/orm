<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Facades;

use Illuminate\Support\Facades\Facade;
use LaravelDoctrine\ORM\DoctrineManager;

class Doctrine extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return DoctrineManager::class;
    }
}
