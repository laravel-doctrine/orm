<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Assets\Middleware;

use LaravelDoctrine\ORM\Contracts\UrlRoutable;

use function strtolower;

class BindableEntityWithInterface implements UrlRoutable
{
    public int $id;

    public string $name;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return strtolower($this->name);
    }

    public static function getRouteKeyNameStatic(): string
    {
        return 'name';
    }
}
