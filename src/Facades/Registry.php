<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Facades;

use Doctrine\Persistence\ManagerRegistry;
use Illuminate\Support\Facades\Facade;

class Registry extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return ManagerRegistry::class;
    }
}
