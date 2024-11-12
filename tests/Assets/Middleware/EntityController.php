<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Assets\Middleware;

use Illuminate\Http\Request;

class EntityController
{
    public function index(BindableEntity $entity): string
    {
        return $entity->getName();
    }

    public function interfacer(BindableEntityWithInterface $entity): int
    {
        return $entity->getId();
    }

    public function returnValue(string $value): string
    {
        return $value;
    }

    public function returnEntity(BindableEntity|null $entity = null): BindableEntity|null
    {
        return $entity;
    }

    public function returnEntityName(BindableEntity $entity): string
    {
        return $entity->getName();
    }

    public function checkRequest(Request $request): string
    {
        return $request instanceof Request ? 'request' : 'something else';
    }
}
